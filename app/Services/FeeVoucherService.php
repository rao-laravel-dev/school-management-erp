<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\BankAccount;
use App\Models\BillingSetting;
use App\Models\StudentFeeFine;
use App\Models\StudentFees;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// Late fee + arrears: Collect Fees, mobile API and the fee voucher all use these methods
class FeeVoucherService
{
    private ?array $setting = null;

    /**
     * Current academic year's late fee amount and cutoff month (fees_from_month).
     * Missing row, amount 0 or no fees_from_month = no late fee at all.
     */
    public function lateFeeSetting(): array
    {
        if ($this->setting === null) {
            $yearId = AcademicYear::where('is_current', 1)->value('id');
            $row    = $yearId ? BillingSetting::where('academic_year_id', $yearId)->first() : null;

            $this->setting = [
                'amount'     => $row ? (float) $row->late_fee_amount : 0.0,
                'from_month' => $row ? $row->fees_from_month : null,
            ];
        }

        return $this->setting;
    }

    // Month (Y-m) a fee row belongs to: period, else the due date's month
    public function monthOf(StudentFees $fee): string
    {
        return $fee->period ?: Carbon::parse($fee->due_date)->format('Y-m');
    }

    /**
     * Pending late fees (runtime, nothing is stored until paid), one query for all students.
     * Returns [student_id => [month => {month, due_date, amount, student_fee_id}]]
     */
    public function pendingLateFees(int|array $studentIds): Collection
    {
        $ids = (array) $studentIds;
        $s   = $this->lateFeeSetting();

        if (!$ids || $s['amount'] <= 0 || !$s['from_month']) {
            return collect();
        }

        $rows = StudentFees::with('fines')
            ->whereIn('student_id', $ids)
            ->where('status', '!=', 'excluded')
            ->orderBy('id')
            ->get();

        $result = collect();
        foreach ($rows->groupBy('student_id') as $studentId => $studentRows) {
            $fined   = $studentRows->filter(fn($f) => $f->fines->isNotEmpty())->pluck('id')->all();
            $pending = $this->computePending($studentRows, $fined);

            if ($pending->isNotEmpty()) {
                $result[$studentId] = $pending;
            }
        }

        return $result;
    }

    /**
     * Arrears for month $month: unpaid balance of earlier months' rows + their pending late fees.
     * A row is either current or earlier, never both, so nothing is counted twice.
     */
    public function arrears(int $studentId, string $month): array
    {
        $balance = StudentFees::where('student_id', $studentId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->get()
            ->filter(fn($f) => $this->monthOf($f) < $month)
            ->sum(fn($f) => (float) $f->amount - (float) $f->discount - (float) $f->paid_amount);

        $lateFee = $this->pendingLateFees($studentId)
            ->get($studentId, collect())
            ->filter(fn($late, $m) => $m < $month)
            ->sum('amount');

        return [
            'balance'  => round($balance, 2),
            'late_fee' => round($lateFee, 2),
            'total'    => round($balance + $lateFee, 2),
        ];
    }

    /**
     * Charge the pending late fee of the given months. Call inside the caller's DB::transaction().
     * Locks the student's fee rows so a double click cannot charge the same month twice.
     * Each late fee = one Transaction (category fee, fine = amount, note "Late fee") + one student_fee_fines row.
     * $groupId is shared with the caller's fee transactions so the receipt groups them.
     * Returns the total late fee charged.
     */
    public function collectLateFees(int $studentId, array $months, array $payment, ?int &$groupId = null): float
    {
        $rows = StudentFees::where('student_id', $studentId)
            ->where('status', '!=', 'excluded')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $fined = StudentFeeFine::whereIn('student_fee_id', $rows->pluck('id'))
            ->lockForUpdate()
            ->pluck('student_fee_id')
            ->all();

        $pending = $this->computePending($rows, $fined);

        $isCash        = ($payment['payment_method'] ?? 'cash') === 'cash';
        $bankAccountId = $isCash
            ? BankAccount::where('bank_type', 'cash')->value('id')
            : ($payment['bank_account_id'] ?? null);

        $total = 0.0;

        foreach (array_unique($months) as $month) {
            $late = $pending->get($month);
            if (!$late) {
                continue;
            }

            $transaction = Transaction::create([
                'payment_group_id' => $groupId,
                'bank_account_id'  => $bankAccountId,
                'user_id'          => auth()->id(),
                'student_fee_id'   => $late->student_fee_id,
                'type'             => 'income',
                'category'         => 'fee',
                'amount'           => $late->amount,
                'fine'             => $late->amount,
                'payment_method'   => $payment['payment_method'] ?? 'cash',
                'reference_no'     => $payment['reference_no'] ?? null,
                'transaction_date' => now(),
                'note'             => 'Late fee',
            ]);

            if ($groupId === null) {
                $groupId = $transaction->id;
                $transaction->payment_group_id = $groupId;
                $transaction->save();
            }

            StudentFeeFine::create([
                'student_fee_id'  => $late->student_fee_id,
                'calculated_fine' => $late->amount,
                'applied_fine'    => $late->amount,
                'reason'          => 'Late fee',
                'applied_by'      => auth()->id(),
                'transaction_id'  => $transaction->id,
            ]);

            if ($bankAccountId) {
                BankAccount::where('id', $bankAccountId)->increment('current_balance', $late->amount);
            }

            $total += $late->amount;
        }

        return $total;
    }

    /**
     * Move the due date of the student's unpaid/partial rows of $month to $date (only rows with an earlier due date).
     * The late fee of that month disappears automatically (it is runtime). Returns the number of rows updated.
     */
    public function extendDueDate(int $studentId, string $month, string $date): int
    {
        return DB::transaction(function () use ($studentId, $month, $date) {
            $rows = StudentFees::where('student_id', $studentId)
                ->whereIn('status', ['unpaid', 'partial'])
                ->lockForUpdate()
                ->get()
                ->filter(fn($f) => $this->monthOf($f) === $month && $f->due_date->toDateString() < $date);

            foreach ($rows as $row) {
                $row->due_date = $date;
                $row->save();
            }

            return $rows->count();
        });
    }

    /**
     * Pending late fee per month from one student's rows ($fined = ids of rows that already carry a student_fee_fines record).
     * Rules: amount > 0, month >= cutoff, month has an unpaid/partial row, earliest of their due dates < today, month not fined yet.
     * The fee is stored against the month's lowest-id unpaid/partial row.
     */
    private function computePending(Collection $studentRows, array $fined): Collection
    {
        $s       = $this->lateFeeSetting();
        $pending = collect();

        if ($s['amount'] <= 0 || !$s['from_month']) {
            return $pending;
        }

        $today = today()->toDateString();

        foreach ($studentRows->groupBy(fn($f) => $this->monthOf($f)) as $month => $monthRows) {
            if ($month < $s['from_month']) {
                continue;
            }

            if ($monthRows->contains(fn($f) => in_array($f->id, $fined))) {
                continue;
            }

            $unpaid = $monthRows->whereIn('status', ['unpaid', 'partial']);
            if ($unpaid->isEmpty()) {
                continue;
            }

            $dueDate = $unpaid->map(fn($f) => $f->due_date->toDateString())->min();
            if ($dueDate >= $today) {
                continue;
            }

            $pending[$month] = (object) [
                'month'          => $month,
                'due_date'       => $dueDate,
                'amount'         => $s['amount'],
                'student_fee_id' => $unpaid->sortBy('id')->first()->id,
            ];
        }

        return $pending;
    }
}
