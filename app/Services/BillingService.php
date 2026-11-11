<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\BillingSetting;
use App\Models\Enrollment;
use App\Models\FeeAssignment;
use App\Models\FeeStructure;
use App\Models\FeeVoucher;
use App\Models\Student;
use App\Models\StudentDiscount;
use App\Models\StudentFees;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

// Auto monthly fees + salary slips: billing_settings (per academic year) ke hisab se
class BillingService
{
    public const TYPES = ['fees', 'salary'];

    /**
     * Academic year ke billing months ['Y-m', ...]. end_date mahine ki 1 tareekh ho to wo mahina nahi
     * ginte (pichla mahina aakhri hai). Dates na hon to khali.
     */
    public function billingMonths(AcademicYear $year, ?int $classId = null): array
    {
        // Class kisi override group mein ho to us group ki dates, warna session ki (classId null = session)
        [$start, $end] = $year->datesForClass($classId);

        if (!$start || !$end) {
            return [];
        }

        $first = $start->copy()->startOfMonth();
        $last  = $end->copy()->startOfMonth();
        if ($end->day === 1) {
            $last->subMonthNoOverflow();
        }

        $months = [];
        for ($m = $first->copy(); $m->lte($last); $m->addMonthNoOverflow()) {
            $months[] = $m->format('Y-m');
        }

        return $months;
    }

    /**
     * Fees ke months: session ke months + har class group (override) ke months, sorted, duplicate nahi.
     * Overrides NULL ho to billingMonths($year) ke barabar.
     */
    public function feeMonths(AcademicYear $year): array
    {
        $months = $this->billingMonths($year);

        foreach ((array) $year->class_date_overrides as $row) {
            $classIds = isset($row['class_ids']) ? (array) $row['class_ids'] : [];
            if ($classIds) {
                $months = array_merge($months, $this->billingMonths($year, (int) reset($classIds)));
            }
        }

        $months = array_values(array_unique($months));
        sort($months);

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
        // Fees: class groups ke months bhi; salary school-wide (session ke months)
        $billingMonths = $type === 'fees' ? $this->feeMonths($setting->academicYear) : $this->billingMonths($setting->academicYear);
        foreach ($billingMonths as $month) {
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

        // Vouchers fee rows commit hone ke BAAD (koi outer transaction nahi), voucher failure fees rollback nahi karta
        $voucher = $type === 'fees'
            ? $this->generateVouchersForMonth($setting, $month)
            : ['created' => 0, 'skipped' => 0, 'failed' => 0, 'note' => '', 'student_ids' => []];

        // Parents ko notification: is run ki NAYI fee rows wale students (voucher fail/skip bhi fee notification nahi rokta)
        if ($type === 'fees' && !empty($result['student_ids'])) {
            app(BillingNotifier::class)->feesGenerated($month, $result['student_ids'], $voucher['student_ids']);
        }

        return [
            'year'           => $setting->academicYear->name,
            'type'           => $type,
            'month'          => $month,
            'created'        => $result['created'],
            'skipped'        => $result['skipped'],
            'note'           => $result['note'],
            'vouchers'       => $voucher['created'],
            'voucher_failed' => $voucher['failed'],
            'voucher_note'   => $voucher['note'],
        ];
    }

    /**
     * Auto vouchers: un students ke liye jin ki is mahine ki unpaid/partial fee row hai aur is mahine ka
     * koi voucher (kisi bhi status ka) nahi. Mojooda voucher kabhi refresh nahi hota (admin Regenerate kare).
     * Har student alag try/catch: ek ki ghalti baqi ko nahi rokti.
     */
    public function generateVouchersForMonth(BillingSetting $setting, string $month): array
    {
        $out  = ['created' => 0, 'skipped' => 0, 'failed' => 0, 'note' => '', 'student_ids' => []];
        $year = $setting->academicYear;

        // generate() current academic year, bank account aur late fee use karta hai
        if (!$year->is_current) {
            $out['note'] = 'Vouchers not created: academic year ' . $year->name . ' is not the current year.';
            Log::info("billing: vouchers {$month} skipped, academic year {$year->name} is not the current year");

            return $out;
        }

        $monthStart = Carbon::parse($month . '-01')->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        // Same rule as FeeVoucherService::monthOf(): period, warna due_date ka mahina
        $candidateIds = StudentFees::whereIn('status', ['unpaid', 'partial'])
            ->where(function ($q) use ($month, $monthStart, $monthEnd) {
                $q->where('period', $month)
                    ->orWhere(function ($q2) use ($monthStart, $monthEnd) {
                        $q2->whereNull('period')
                            ->whereBetween('due_date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
                    });
            })
            ->whereIn('student_id', Enrollment::where('academic_year_id', $year->id)->where('enroll_status', 1)->select('student_id'))
            ->pluck('student_id')
            ->unique();

        $existingIds = FeeVoucher::where('period', $month)
            ->whereIn('student_id', $candidateIds)
            ->pluck('student_id');

        $pendingIds = $candidateIds->diff($existingIds);
        $out['skipped'] = $candidateIds->count() - $pendingIds->count();

        $voucherService = app(FeeVoucherService::class);

        foreach (Student::whereIn('id', $pendingIds)->get() as $student) {
            try {
                $voucherService->generate($student, $month);
                $out['created']++;
                $out['student_ids'][] = $student->id;
            } catch (ValidationException $e) {
                $out['skipped']++;
            } catch (\Throwable $e) {
                $out['failed']++;
                Log::error("billing: voucher {$month} student {$student->id} failed: " . $e->getMessage());
            }
        }

        Log::info("billing: vouchers {$month} created {$out['created']}, skipped {$out['skipped']}, failed {$out['failed']}");

        return $out;
    }

    /**
     * Mahine ki monthly/quarterly/half-yearly/annual fees student_fees mein banao.
     * Period academic year (ya class group) ke pehle billing month se gina jata hai.
     */
    public function generateFeesForMonth(BillingSetting $setting, string $month): array
    {
        $year   = $setting->academicYear;
        $months = $this->feeMonths($year);
        $index  = array_search($month, $months, true);

        if ($index === false) {
            return ['created' => 0, 'skipped' => 0, 'note' => 'Month is outside the academic year.', 'student_ids' => []];
        }

        $monthStart = Carbon::parse($month . '-01')->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();
        $dueDate    = $monthStart->copy()->day(min($setting->fee_due_day, $monthStart->daysInMonth));

        $structures = FeeStructure::with('feeType')
            ->where('academic_year_id', $year->id)
            ->whereNotNull('school_class_id')
            ->get()
            ->filter(function ($fs) use ($year, $month) {
                if (!$fs->feeType || $fs->feeType->is_per_student) {
                    return false;
                }
                // Period index class ke apne months se: class group ki dates alag ho to us ka pehla mahina 0 hai
                $classIndex = array_search($month, $this->billingMonths($year, (int) $fs->school_class_id), true);

                return $classIndex !== false && $this->appliesInMonth($fs->feeType->frequency, $classIndex);
            });

        $created = 0;
        $skipped = 0;
        $newStudentIds = []; // notification ke liye: is run mein nayi fee row wale students

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

            $newStudentIds = array_merge($newStudentIds, $pendingIds->all());
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
            $newStudentIds = array_merge($newStudentIds, $row['student_ids']);
        }

        Log::info("billing: fees {$month} (year {$year->name}) created {$created}, skipped {$skipped}");

        return ['created' => $created, 'skipped' => $skipped, 'note' => '', 'student_ids' => array_values(array_unique($newStudentIds))];
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

        // Staff ko slip notification (cron = actor nahi, Generate Now = admin: koi admin copy nahi)
        app(BillingNotifier::class)->salarySlipsGenerated(collect($result['generated'])->pluck('id')->all(), auth()->user());

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
                : min((float) ($sd->amount ?: $p->discount_value), (float) $fs->amount); // fixed: per-student amount ya policy value
        });
    }
}
