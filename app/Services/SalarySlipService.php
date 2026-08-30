<?php

namespace App\Services;

use App\Models\AcademicCalendar;
use App\Models\EventType;
use App\Models\SalarySlip;
use App\Models\StaffAttendance;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalarySlipService
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}
    /**
     * Generate a salary slip for a single staff member for a given month.
     * $month format: 'Y-m' e.g. '2026-07'
     */
    public function generate(User $user, string $month): SalarySlip
    {
        if (SalarySlip::where('user_id', $user->id)->where('month', $month)->exists()) {
            throw new \Exception("Salary slip already generated for {$user->name} for {$month}.");
        }

        $salaryStructure = $user->salaries;

        if (!$salaryStructure) {
            throw new \Exception("No salary structure found for {$user->name}. Please set up basic salary first.");
        }

        $attendance = $this->getAttendanceSummary($user, $month);

        $perDayRate = $attendance['total_days'] > 0
            ? round($salaryStructure->basic_salary / $attendance['total_days'], 2)
            : 0;

        $attendanceDeduction = round(
            ($attendance['absent_days'] * $perDayRate) +
                ($attendance['half_days'] * ($perDayRate / 2)),
            2
        );

        $manualDeduction = $salaryStructure->deduction ?? 0;

        // Advance recovery: deduct from current advance_balance, cap at balance available
        $advanceDeduction = 0;
        if ($salaryStructure->advance_balance > 0) {
            $advanceDeduction = $salaryStructure->advance_balance;
        }

        $grossSalary = $salaryStructure->basic_salary + $salaryStructure->allowance;
        $totalDeduction = $manualDeduction + $attendanceDeduction + $advanceDeduction;
        $netSalary = round($grossSalary - $totalDeduction, 2);

        return DB::transaction(function () use (
            $user,
            $month,
            $salaryStructure,
            $attendance,
            $perDayRate,
            $attendanceDeduction,
            $manualDeduction,
            $advanceDeduction,
            $totalDeduction,
            $netSalary
        ) {
            $slip = SalarySlip::create([
                'user_id'              => $user->id,
                'month'                => $month,
                'basic_salary'         => $salaryStructure->basic_salary,
                'allowance'            => $salaryStructure->allowance,
                'deduction'            => $totalDeduction,
                'manual_deduction'     => $manualDeduction,
                'advance_deduction'    => $advanceDeduction,
                'net_salary'           => $netSalary,
                'status'               => 'pending',
                'generated_by'         => Auth::id(),
                'total_days'           => $attendance['total_days'],
                'present_days'         => $attendance['present_days'],
                'absent_days'          => $attendance['absent_days'],
                'half_days'            => $attendance['half_days'],
                'leave_days'           => $attendance['leave_days'],
                'per_day_rate'         => $perDayRate,
                'attendance_deduction' => $attendanceDeduction,
            ]);

            // Reduce advance balance since it's now being recovered in this slip
            if ($advanceDeduction > 0) {
                $salaryStructure->decrement('advance_balance', $advanceDeduction);
            }

            return $slip;
        });
    }

    /**
     * Generate slips for all staff, optionally filtered by role.
     * Returns ['generated' => [...], 'skipped' => [...]]
     */
    public function generateBulk(string $month, ?string $role = null): array
    {
        $excludedRoles = ['superadmin', 'admin', 'parent', 'student', 'user'];

        $staff = User::whereHas('roles', function ($q) use ($excludedRoles, $role) {
            $q->whereNotIn('name', $excludedRoles);
            if ($role) {
                $q->where('name', $role);
            }
        })->get();

        $generated = [];
        $skipped = [];

        foreach ($staff as $user) {
            try {
                $generated[] = $this->generate($user, $month);
            } catch (\Exception $e) {
                $skipped[] = [
                    'user' => $user->name,
                    'reason' => $e->getMessage(),
                ];
            }
        }

        return ['generated' => $generated, 'skipped' => $skipped];
    }

    /**
     * Mark a salary slip as paid and create the corresponding transaction record.
     */
    public function pay(SalarySlip $slip, array $paymentData): SalarySlip
    {
        if ($slip->status === 'paid') {
            throw new \Exception('This salary slip is already marked as paid.');
        }

        return DB::transaction(function () use ($slip, $paymentData) {
            $slip->update([
                'status'       => 'paid',
                'payment_date' => now(),
                'paid_by'      => Auth::id(),
            ]);

            $isCash = ($paymentData['payment_method'] ?? 'cash') === 'cash';

            $bankAccountId = $isCash
                ? \App\Models\BankAccount::where('bank_type', 'cash')->value('id')
                : ($paymentData['bank_account_id'] ?? null);

            Transaction::create([
                'bank_account_id'  => $bankAccountId,
                'user_id'          => $slip->user_id,
                'salary_slip_id'   => $slip->id,
                'type'             => 'expense',
                'category'         => 'salary',
                'amount'           => $slip->net_salary,
                'payment_method'   => $paymentData['payment_method'] ?? 'cash',
                'reference_no'     => $paymentData['reference_no'] ?? null,
                'transaction_date' => now(),
                'note'             => "Salary payment for {$slip->month} - {$slip->user->name}",
            ]);

            if ($bankAccountId) {
                \App\Models\BankAccount::where('id', $bankAccountId)
                    ->decrement('current_balance', $slip->net_salary);
            }

            return $slip->fresh();
        });
    }

    /**
     * Calculate attendance summary for a staff member for a given month.
     */
    private function getAttendanceSummary(User $user, string $month): array
    {
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $totalDays = $this->getWorkingDays($start, $end);

        $records = StaffAttendance::where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $presentDays = $records->where('status', 'present')->count();
        $absentDays  = $records->where('status', 'absent')->count();
        $halfDays    = $records->where('status', 'half_day')->count();
        $leaveDays   = $records->where('status', 'leave')->count();

        // Any working day with NO attendance record at all is treated as absent
        $markedDays = $records->count();
        $unmarkedDays = max($totalDays - $markedDays, 0);
        $absentDays += $unmarkedDays;

        return [
            'total_days'   => $totalDays,
            'present_days' => $presentDays,
            'absent_days'  => $absentDays,
            'half_days'    => $halfDays,
            'leave_days'   => $leaveDays,
        ];
    }

    /**
     * Working days = calendar days - Sundays - declared holidays (academic_calendars)
     */
    private function getWorkingDays(Carbon $start, Carbon $end): int
    {
        $workingDays = 0;

        $holidayEventType = EventType::where('name', 'Holiday')->first();

        $holidayDates = [];
        if ($holidayEventType) {
            $holidayDates = AcademicCalendar::where('event_type_id', $holidayEventType->id)
                ->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
                ->pluck('start_date')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->toArray();
        }

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->isSunday()) {
                continue;
            }
            if (in_array($date->toDateString(), $holidayDates)) {
                continue;
            }
            $workingDays++;
        }

        return $workingDays;
    }
}
