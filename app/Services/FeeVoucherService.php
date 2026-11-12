<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\BankAccount;
use App\Models\BillingSetting;
use App\Models\FeeVoucher;
use App\Models\SiteSetting;
use App\Models\Student;
use App\Models\StudentFeeFine;
use App\Models\StudentFees;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

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
                // Old rows have no period (month came from due_date): pin the original month before moving the date
                if (!$row->period) {
                    $row->period = $month;
                }
                $row->due_date = $date;
                $row->save();
            }

            // Unpaid voucher of this student shows the new due date and no late fee
            $this->refreshUnpaid($studentId);

            return $rows->count();
        });
    }

    /**
     * Create or update the voucher of one student for month $month (Y-m).
     * A student has one voucher per month: an existing unpaid / cancelled row is updated in place
     * (voucher_no never changes), a paid one is refused. Refusals are ValidationExceptions (422 for AJAX).
     */
    public function generate(Student $student, string $month): FeeVoucher
    {
        return DB::transaction(function () use ($student, $month) {
            // Lock the student's fee rows so a double click cannot build two vouchers
            StudentFees::where('student_id', $student->id)->orderBy('id')->lockForUpdate()->get();

            $voucher = FeeVoucher::where('student_id', $student->id)->where('period', $month)->lockForUpdate()->first();

            if ($voucher && $voucher->status === 'paid') {
                throw ValidationException::withMessages(['month' => 'The voucher of this month is already paid.']);
            }

            $calc = $this->calculate($student->id, $month);

            if ($calc['payable'] <= 0) {
                throw ValidationException::withMessages(['month' => 'Nothing is payable for this month.']);
            }

            $data = collect($calc)->except('payable')->all() + [
                'academic_year_id' => AcademicYear::where('is_current', 1)->value('id'),
                'bank_account_id'  => $this->billingRow()?->voucher_bank_account_id,
            ];

            if ($voucher) {
                // Regenerate / reactivate: same row, same voucher_no
                $voucher->update($data + ['issue_date' => today(), 'status' => 'unpaid', 'paid_at' => null, 'regenerated_by' => auth()->id(), 'regenerated_at' => now()]);

                return $voucher;
            }

            return FeeVoucher::create($data + [
                'voucher_no' => (int) FeeVoucher::lockForUpdate()->max('voucher_no') + 1,
                'student_id' => $student->id,
                'period'     => $month,
                'issue_date' => today(),
                'status'     => 'unpaid',
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Recalculate every UNPAID voucher of the given students (amounts, arrears, late fee, due date, rows).
     * voucher_no and issue_date never change; paid / cancelled vouchers are never touched.
     * Call inside the caller's DB::transaction() after a payment, exclude, reassign or due date change.
     */
    public function refreshUnpaid(int|array $studentIds): void
    {
        $ids = (array) $studentIds;

        // Status first: a voucher whose rows are all settled becomes paid / cancelled and is not recalculated
        $this->syncStatus($ids);

        $vouchers = FeeVoucher::whereIn('student_id', $ids)->where('status', 'unpaid')->lockForUpdate()->get();

        foreach ($vouchers as $voucher) {
            $calc = $this->calculate($voucher->student_id, $voucher->period);

            if ($calc['payable'] <= 0) {
                continue;
            }

            // Rows stay listed once they were on the voucher, so paid vs cancelled can still be decided later
            $calc['student_fee_ids'] = array_values(array_unique(array_merge($voucher->student_fee_ids ?: [], $calc['student_fee_ids'])));

            $voucher->update(collect($calc)->except('payable')->all());
        }
    }

    /**
     * Unpaid vouchers: all rows settled and at least one paid = paid (+ paid_at); all rows excluded = cancelled;
     * anything still open (also a newer row of an earlier or the same month) keeps the voucher unpaid.
     * A partial payment keeps it unpaid (refreshUnpaid shrinks its amounts).
     */
    public function syncStatus(int|array $studentIds): void
    {
        $ids      = (array) $studentIds;
        $vouchers = FeeVoucher::whereIn('student_id', $ids)->where('status', 'unpaid')->lockForUpdate()->get();

        if ($vouchers->isEmpty()) {
            return;
        }

        $open = StudentFees::whereIn('student_id', $ids)
            ->whereIn('status', ['unpaid', 'partial'])
            ->get()
            ->groupBy('student_id');

        $listed = StudentFees::whereIn('id', $vouchers->pluck('student_fee_ids')->flatten()->filter()->unique())->get()->keyBy('id');

        foreach ($vouchers as $voucher) {
            $stillOpen = $open->get($voucher->student_id, collect())
                ->contains(fn($f) => $this->monthOf($f) <= $voucher->period);

            if ($stillOpen) {
                continue;
            }

            $rows = collect($voucher->student_fee_ids ?: [])->map(fn($id) => $listed->get($id))->filter();

            if ($rows->isEmpty()) {
                continue;
            }

            // A listed row is still open (e.g. its month moved): the voucher stays unpaid
            if ($rows->contains(fn($f) => in_array($f->status, ['unpaid', 'partial']))) {
                continue;
            }

            if ($rows->contains(fn($f) => $f->status === 'paid')) {
                $voucher->update(['status' => 'paid', 'paid_at' => now()]);
            } else {
                $voucher->update(['status' => 'cancelled']);
            }
        }
    }

    /**
     * Data for the voucher PDF view, one entry per voucher (loads everything in a few queries).
     * Unpaid vouchers list only their open rows, so the head lines add up to the printed amounts.
     */
    public function pdfData(Collection $vouchers): array
    {
        $vouchers = new EloquentCollection($vouchers->all()); // callers may pass collect([$voucher]); load() needs this type
        $vouchers->load(['student.parent', 'student.enrollments.schoolClass', 'student.enrollments.section', 'bankAccount', 'createdBy', 'regeneratedBy']);

        $rows = StudentFees::with(['feeType', 'feeAssignment'])
            ->whereIn('id', $vouchers->pluck('student_fee_ids')->flatten()->filter()->unique())
            ->get()
            ->keyBy('id');

        $settings = BillingSetting::whereIn('academic_year_id', $vouchers->pluck('academic_year_id')->unique())
            ->get()
            ->keyBy('academic_year_id');

        // Late fee inside the arrears of each unpaid voucher: one query for all students
        $pendingLate = $this->pendingLateFees($vouchers->where('status', 'unpaid')->pluck('student_id')->unique()->all());

        $school = SiteSetting::current();
        $logo   = $this->logoDataUri($school);

        $items = [];
        foreach ($vouchers as $voucher) {
            $student    = $voucher->student;
            $enrollment = $student->enrollments->firstWhere('academic_year_id', $voucher->academic_year_id)
                ?: $student->enrollments->sortByDesc('id')->first();

            $heads = collect($voucher->student_fee_ids ?: [])
                ->map(fn($id) => $rows->get($id))
                ->filter(fn($f) => $f
                    && $this->monthOf($f) === $voucher->period
                    && ($voucher->status !== 'unpaid' || in_array($f->status, ['unpaid', 'partial'])))
                ->values();

            $arrearsLate = $voucher->status === 'unpaid'
                ? $pendingLate->get($voucher->student_id, collect())->filter(fn($late, $m) => $m < $voucher->period)->sum('amount')
                : 0;

            $setting = $settings->get($voucher->academic_year_id);

            $items[] = [
                'voucher'      => $voucher,
                'student'      => $student,
                'className'    => $enrollment?->schoolClass?->name ?: '-',
                'sectionName'  => $enrollment?->section?->name ?: '-',
                'father'       => $student->parent->father_name ?? '-',
                'monthLabel'   => Carbon::createFromFormat('Y-m-d', $voucher->period . '-01')->format('F Y'),
                'heads'        => $heads,
                'headDiscount' => (float) $heads->sum('discount'),
                'headPaid'     => (float) $heads->sum('paid_amount'),
                'arrearsLate'  => (float) $arrearsLate,
                'note'         => $setting?->voucher_note,
                'lateFeeAmount' => $setting ? (float) $setting->late_fee_amount : 0.0,
                'onebill'      => $setting && $setting->onebill_enabled ? $setting : null,
                'words'        => $this->amountInWords((float) $voucher->amount_before_due),
                'wordsAfter'   => $this->amountInWords((float) $voucher->amount_after_due),
            ];
        }

        return ['items' => $items, 'school' => $school, 'logo' => $logo];
    }

    // Site Settings logo as a small PNG data URI for dompdf (webp converted with GD, max 300px wide); null when not set / missing
    public function logoDataUri(SiteSetting $school): ?string
    {
        $path = $school->logo ? public_path('uploads/site_setting/' . $school->logo) : null;

        if (!$path || !is_file($path)) {
            return null;
        }

        return (new ImageManager(new Driver()))->read($path)->scaleDown(width: 300)->toPng()->toDataUri();
    }

    // Voucher PDF (A4 portrait, student slip on top, compact copies below, font subsetting keeps the file small); callers stream or download it
    public function pdf(Collection $vouchers)
    {
        return Pdf::loadView('admin.fee_voucher.voucher_pdf', $this->pdfData($vouchers))->setPaper('a4', 'portrait')->setOption(['enable_font_subsetting' => true]);
    }

    /**
     * Fee rows and receipts of one student for the portal Fee Ledger.
     * Returns ['fees' => rows, 'payments' => one entry per receipt (group id, or the transaction id)].
     */
    public function ledger(int $studentId): array
    {
        $fees = StudentFees::with(['feeType', 'feeAssignment'])
            ->where('student_id', $studentId)
            ->where('status', '!=', 'excluded')
            ->orderByDesc('due_date')
            ->orderByDesc('id')
            ->get();

        $payments = Transaction::with(['studentFee.feeType', 'studentFee.feeAssignment'])
            ->whereIn('student_fee_id', $fees->pluck('id'))
            ->where('category', 'fee')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn($t) => $t->payment_group_id ?: $t->id)
            ->map(fn($group, $receiptId) => (object) [
                'receipt_id' => $receiptId,
                'date'       => $group->first()->transaction_date,
                'amount'     => $group->sum('amount'),
                'method'     => $group->first()->payment_method,
                'heads'      => $group->map(fn($t) => $t->fine > 0
                    ? 'Late fee'
                    : (($t->studentFee->feeType->name ?? '-') . ($t->studentFee->feeAssignment ? ' - ' . $t->studentFee->feeAssignment->title : '')))
                    ->unique()
                    ->implode(', '),
            ])
            ->values();

        return ['fees' => $fees, 'payments' => $payments];
    }

    /**
     * Portal receipt check: the transaction (and, for a grouped receipt, EVERY transaction of the group)
     * must belong to a fee row of this student. The receipt PDF itself has no owner check.
     */
    public function ownsReceipt(int $studentId, int $transactionId): bool
    {
        $txn = Transaction::with('studentFee')->find($transactionId);

        if (!$txn || !$txn->studentFee || (int) $txn->studentFee->student_id !== $studentId) {
            return false;
        }

        if (!$txn->payment_group_id) {
            return true;
        }

        return !Transaction::where('payment_group_id', $txn->payment_group_id)
            ->where(function ($q) use ($studentId) {
                $q->whereNull('student_fee_id')
                    ->orWhereDoesntHave('studentFee', fn($s) => $s->where('student_id', $studentId));
            })
            ->exists();
    }

    // Billing setting row of the current academic year (null when missing)
    private function billingRow(): ?BillingSetting
    {
        $yearId = AcademicYear::where('is_current', 1)->value('id');

        return $yearId ? BillingSetting::where('academic_year_id', $yearId)->first() : null;
    }

    /**
     * Voucher amounts of one student for month $month (shared by generate and refreshUnpaid).
     * Current heads = unpaid / partial rows of the month; arrears = earlier months (+ their pending late fee).
     * The month's own late fee is what applies after the due date: only when no late fee was paid for it yet.
     */
    private function calculate(int $studentId, string $month): array
    {
        $rows      = StudentFees::with('fines')->where('student_id', $studentId)->where('status', '!=', 'excluded')->orderBy('id')->get();
        $monthRows = $rows->filter(fn($f) => $this->monthOf($f) === $month);
        $current   = $monthRows->whereIn('status', ['unpaid', 'partial']);
        $earlier   = $rows->whereIn('status', ['unpaid', 'partial'])->filter(fn($f) => $this->monthOf($f) < $month);

        $feeAmount = (float) $current->sum('amount');
        $discount  = (float) $current->sum('discount');
        $balance   = $feeAmount - $discount - (float) $current->sum('paid_amount');
        $arrears   = $this->arrears($studentId, $month)['total'];

        $s       = $this->lateFeeSetting();
        $lateFee = 0.0;
        if ($s['amount'] > 0 && $s['from_month'] && $month >= $s['from_month'] && $current->isNotEmpty()
            && !$monthRows->contains(fn($f) => $f->fines->isNotEmpty())) {
            $lateFee = (float) $s['amount'];
        }

        $before = round($balance + $arrears, 2);

        if ($current->isNotEmpty()) {
            $dueDate = $current->map(fn($f) => $f->due_date->toDateString())->min();
        } else {
            $dueDay  = (int) ($this->billingRow()?->fee_due_day ?: 10);
            $dueDate = Carbon::createFromFormat('Y-m-d', $month . '-01')->day($dueDay)->toDateString();
        }

        return [
            'fee_amount'        => round($feeAmount, 2),
            'discount'          => round($discount, 2),
            'arrears'           => round($arrears, 2),
            'late_fee'          => round($lateFee, 2),
            'amount_before_due' => $before,
            'amount_after_due'  => round($before + $lateFee, 2),
            'due_date'          => $dueDate,
            'student_fee_ids'   => $current->pluck('id')->merge($earlier->pluck('id'))->values()->all(),
            'payable'           => $before,
        ];
    }

    // Amount in words for the voucher (Pakistani scale: crore, lakh, thousand), whole rupees only
    private function amountInWords(float $amount): string
    {
        $n = (int) round($amount);

        if ($n <= 0) {
            return 'Zero Rupees Only';
        }

        $parts = [];
        foreach ([[10000000, 'Crore'], [100000, 'Lakh'], [1000, 'Thousand']] as [$size, $label]) {
            $chunk = intdiv($n, $size);
            if ($chunk > 0) {
                $parts[] = $this->wordsBelowThousand($chunk) . ' ' . $label;
                $n      -= $chunk * $size;
            }
        }
        if ($n > 0) {
            $parts[] = $this->wordsBelowThousand($n);
        }

        return implode(' ', $parts) . ' Rupees Only';
    }

    private function wordsBelowThousand(int $n): string
    {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
            'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $words = [];
        if ($n >= 100) {
            $words[] = $ones[intdiv($n, 100)] . ' Hundred';
            $n      %= 100;
        }
        if ($n >= 20) {
            $words[] = $tens[intdiv($n, 10)] . ($n % 10 ? '-' . $ones[$n % 10] : '');
        } elseif ($n > 0) {
            $words[] = $ones[$n];
        }

        return implode(' ', $words);
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
