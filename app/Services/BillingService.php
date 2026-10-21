<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\BillingSetting;
use App\Models\Enrollment;
use App\Models\FeeAssignment;
use App\Models\FeeStructure;
use App\Models\StudentDiscount;
use App\Models\StudentFees;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Auto monthly fees + salary slips: billing_settings (per academic year) ke hisab se
class BillingService
{
    public const TYPES = ['fees', 'salary'];

    /**
     * Academic year ke billing months ['Y-m', ...]. end_date mahine ki 1 tareekh ho to wo mahina nahi
     * ginte (pichla mahina aakhri hai). Dates na hon to khali.
     */
    public function billingMonths(AcademicYear $year): array
    {
        if (!$year->start_date || !$year->end_date) {
            return [];
        }

        $first = $year->start_date->copy()->startOfMonth();
        $last  = $year->end_date->copy()->startOfMonth();
        if ($year->end_date->day === 1) {
            $last->subMonthNoOverflow();
        }

        $months = [];
        for ($m = $first->copy(); $m->lte($last); $m->addMonthNoOverflow()) {
            $months[] = $m->format('Y-m');
        }

        return $months;
    }

    /**
     * Catch-up: wo months jo ab tak due hain (from-month se, generate day guzar chuka). from-month na ho to khali.
     * Fees: month ki generate day. Salary: agle mahine ki salary generate day.
     */
    public function dueMonths(BillingSetting $setting, string $type, ?Carbon $today = null): array
    {
        $today = $today ? $today->copy() : now();
        $from  = $type === 'fees' ? $setting->fees_from_month : $setting->salary_from_month;
        if (!$from) {
            return [];
        }

        $due = [];
        foreach ($this->billingMonths($setting->academicYear) as $month) {
            if ($month < $from) {
                continue;
            }

            $monthStart = Carbon::parse($month . '-01')->startOfMonth();
            if ($type === 'fees') {
                $runDate = $monthStart->copy()->day(min($setting->fee_generate_day, $monthStart->daysInMonth));
            } else {
                $next    = $monthStart->copy()->addMonthNoOverflow();
                $runDate = $next->copy()->day(min($setting->salary_generate_day, $next->daysInMonth));
            }

            if ($runDate->startOfDay()->lte($today)) {
                $due[] = $month;
            }
        }

        return $due;
    }

    /**
     * Ek type ke saare due months chalao (command / Catch up button).
     */
    public function catchUp(BillingSetting $setting, string $type, ?Carbon $today = null): array
    {
        $rows = [];
        foreach ($this->dueMonths($setting, $type, $today) as $month) {
            $rows[] = $this->runMonth($setting, $type, $month);
        }

        return $rows;
    }

    /**
     * Ek mahina chalao aur result row do: year, type, month, created, skipped, note
     */
    public function runMonth(BillingSetting $setting, string $type, string $month): array
    {
        $result = $type === 'fees'
            ? $this->generateFeesForMonth($setting, $month)
            : $this->generateSalaryForMonth($setting, $month);

        return [
            'year'    => $setting->academicYear->name,
            'type'    => $type,
            'month'   => $month,
            'created' => $result['created'],
            'skipped' => $result['skipped'],
            'note'    => $result['note'],
        ];
    }

    /**
     * Mahine ki monthly/quarterly/half-yearly/annual fees student_fees mein banao.
     * Period academic year ke pehle billing month se gina jata hai.
     */
    public function generateFeesForMonth(BillingSetting $setting, string $month): array
    {
        $year   = $setting->academicYear;
        $months = $this->billingMonths($year);
        $index  = array_search($month, $months, true);

        if ($index === false) {
            return ['created' => 0, 'skipped' => 0, 'note' => 'Month is outside the academic year.'];
        }

        $monthStart = Carbon::parse($month . '-01')->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();
        $dueDate    = $monthStart->copy()->day(min($setting->fee_due_day, $monthStart->daysInMonth));

        $structures = FeeStructure::with('feeType')
            ->where('academic_year_id', $year->id)
            ->whereNotNull('school_class_id')
            ->get()
            ->filter(fn($fs) => $fs->feeType && !$fs->feeType->is_per_student && $this->appliesInMonth($fs->feeType->frequency, $index));

        $created = 0;
        $skipped = 0;

        foreach ($structures as $fs) {
            // Sirf active enrolled students, jo is mahine tak admit ho chuke hon (purane mahino ki fee nahi)
            $studentIds = Enrollment::where('academic_year_id', $year->id)
                ->where('class_id', $fs->school_class_id)
                ->where('enroll_status', 1)
                ->whereHas('student', function ($q) use ($monthEnd) {
                    $q->where('status', 1)->whereDate('admission_date', '<=', $monthEnd->toDateString());
                })
                ->pluck('student_id')
                ->unique();

            // Duplicate: isi mahine ka period ya isi mahine ki due_date (manual Generate button ki rows bhi)
            $alreadyIds = StudentFees::where('fee_structure_id', $fs->id)
                ->whereIn('student_id', $studentIds)
                ->where(function ($q) use ($month, $monthStart, $monthEnd) {
                    $q->where('period', $month)
                        ->orWhereBetween('due_date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
                })
                ->pluck('student_id')
                ->unique();

            $pendingIds = $studentIds->diff($alreadyIds);
            $skipped   += $studentIds->count() - $pendingIds->count();

            if ($pendingIds->isEmpty()) {
                continue;
            }

            $discountsByStudent = $this->activeDiscounts($pendingIds, $fs);

            DB::transaction(function () use ($pendingIds, $fs, $month, $dueDate, $discountsByStudent, &$created) {
                foreach ($pendingIds as $studentId) {
                    StudentFees::create([
                        'fee_structure_id' => $fs->id,
                        'student_id'       => $studentId,
                        'fee_type_id'      => $fs->fee_type_id,
                        'amount'           => $fs->amount,
                        'discount'         => $this->maxDiscount($discountsByStudent->get($studentId), $fs),
                        'paid_amount'      => 0,
                        'due_date'         => $dueDate->toDateString(),
                        'period'           => $month,
                        'status'           => 'unpaid',
                    ]);
                    $created++;
                }
            });
        }

        // Assign Fee ki recurring (Tuition) assignments: sirf unke selected students ke liye
        $assignmentService = app(FeeAssignmentService::class);
        $recurring = FeeAssignment::with('feeType')
            ->where('academic_year_id', $year->id)
            ->where('kind', FeeAssignment::KIND_RECURRING)
            ->where('start_month', '<=', $month)
            ->where(function ($q) use ($month) {
                $q->whereNull('end_month')->orWhere('end_month', '>=', $month);
            })
            ->get();

        foreach ($recurring as $assignment) {
            $row      = $assignmentService->createRecurringRowsForMonth($assignment, $month, $setting);
            $created += $row['created'];
            $skipped += $row['skipped'];
        }

        Log::info("billing: fees {$month} (year {$year->name}) created {$created}, skipped {$skipped}");

        return ['created' => $created, 'skipped' => $skipped, 'note' => ''];
    }

    /**
     * Pichle (poore hue) mahine ki salary slips: SalarySlipService::generateBulk() (active staff).
     * Unmarked attendance dates sirf log hoti hain.
     */
    public function generateSalaryForMonth(BillingSetting $setting, string $month): array
    {
        if (!in_array($month, $this->billingMonths($setting->academicYear), true)) {
            return ['created' => 0, 'skipped' => 0, 'note' => 'Month is outside the academic year.'];
        }

        if ($month >= now()->format('Y-m')) {
            return ['created' => 0, 'skipped' => 0, 'note' => 'Month is not completed yet.'];
        }

        $service  = app(SalarySlipService::class);
        $unmarked = $service->unmarkedWorkingDays($month);
        $note     = '';

        if ($unmarked) {
            $dates = [];
            foreach ($unmarked as $date => $staff) {
                $dates[] = substr($date, 8, 2) . ' (' . $staff . ' staff)';
            }
            $note = count($unmarked) . ' working ' . (count($unmarked) === 1 ? 'day' : 'days') . ' not marked.';
            Log::warning("billing: salary {$month} attendance not marked on " . implode(', ', $dates));
        }

        $result  = $service->generateBulk($month);
        $created = count($result['generated']);
        $skipped = count($result['skipped']);

        Log::info("billing: salary {$month} created {$created}, skipped {$skipped}");

        return ['created' => $created, 'skipped' => $skipped, 'note' => $note];
    }

    /**
     * Frequency ke hisab se is billing-month index (0 = year ka pehla mahina) par fee banti hai ya nahi.
     * one_time kabhi auto nahi.
     */
    private function appliesInMonth(?string $frequency, int $index): bool
    {
        return match ($frequency) {
            'monthly'     => true,
            'quarterly'   => $index % 3 === 0,
            'half_yearly' => $index % 6 === 0,
            'annual'      => $index === 0,
            default       => false,
        };
    }

    /**
     * Students ki active discounts, ek hi query (no N+1): [student_id => Collection of StudentDiscount]
     */
    public function activeDiscounts($studentIds, FeeStructure|FeeAssignment $fs)
    {
        if (!$fs->feeType->is_discountable) {
            return collect();
        }

        return StudentDiscount::whereIn('student_id', $studentIds)
            ->where('status', 1)
            ->whereHas('discountPolicy', function ($q) use ($fs) {
                $q->where('status', 1)
                    ->where(function ($q2) use ($fs) {
                        $q2->whereNull('fee_type_id')
                            ->orWhere('fee_type_id', $fs->fee_type_id);
                    });
            })
            ->with('discountPolicy')
            ->get()
            ->groupBy('student_id');
    }

    /**
     * Same rule as the Fee Structure Generate button (FeeStructureController::calculateDiscount):
     * is_discountable + MAX of the matching active policies (percentage of amount, or fixed).
     */
    public function maxDiscount($studentDiscounts, FeeStructure|FeeAssignment $fs): float
    {
        if (!$studentDiscounts || $studentDiscounts->isEmpty()) {
            return 0;
        }

        return $studentDiscounts->max(function ($sd) use ($fs) {
            $p = $sd->discountPolicy;
            return $p->discount_type === 'percentage'
                ? round($fs->amount * $p->discount_value / 100, 2)
                : $p->discount_value;
        });
    }
}
