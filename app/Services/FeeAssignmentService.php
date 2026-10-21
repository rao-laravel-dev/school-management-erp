<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\BillingSetting;
use App\Models\Enrollment;
use App\Models\FeeAssignment;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentFees;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// Assign Fee: target se active students nikalna aur student_fees rows banana
class FeeAssignmentService
{
    public function __construct(private BillingService $billing)
    {
    }

    /**
     * Target ke active students (us academic year ki enrollment, enroll_status = 1, students.status = 1).
     * Selected students mein se jo active nahi unka count 'skipped' mein aata hai.
     */
    public function resolveStudents(array $data): array
    {
        $query = Enrollment::where('academic_year_id', $data['academic_year_id'])
            ->where('enroll_status', 1)
            ->whereHas('student', fn($q) => $q->where('status', 1));

        $requested = null;

        switch ($data['target_type']) {
            case 'classes':
                $query->whereIn('class_id', $data['class_ids']);
                break;
            case 'section':
                $query->where('class_id', $data['class_id'])->where('section_id', $data['section_id']);
                break;
            case 'students':
                $requested = collect($data['student_ids'])->unique()->count();
                $query->whereIn('student_id', $data['student_ids']);
                break;
        }

        $ids = $query->pluck('student_id')->unique()->values();

        return [
            'ids'     => $ids,
            'skipped' => $requested === null ? 0 : $requested - $ids->count(),
        ];
    }

    /**
     * Preview ke liye: count, skipped aur pehle 5 students ke naam
     */
    public function preview(array $data): array
    {
        $resolved = $this->resolveStudents($data);

        $names = Student::whereIn('id', $resolved['ids']->take(5))
            ->get(['id', 'first_name', 'last_name', 'admission_no'])
            ->map(fn($s) => trim($s->first_name . ' ' . $s->last_name) . ' (' . $s->admission_no . ')')
            ->all();

        $result = [
            'count'   => $resolved['ids']->count(),
            'skipped' => $resolved['skipped'],
            'names'   => $names,
        ];

        // Recurring (start_month sirf recurring ke liye validate hota hai): months x students = rows
        if (!empty($data['start_month'])) {
            $months         = count($this->recurringMonths($data['academic_year_id'], $data['start_month'], $data['end_month'] ?? null));
            $result['months'] = $months;
            $result['rows']   = $months * $result['count'];
        }

        return $result;
    }

    /**
     * Recurring: start month se current month tak (end month / year ke aakhri billing month se cap) ke billing months
     */
    public function recurringMonths(int $academicYearId, string $start, ?string $end): array
    {
        $cap = now()->format('Y-m');
        if ($end && $end < $cap) {
            $cap = $end;
        }

        $months = $this->billing->billingMonths(AcademicYear::findOrFail($academicYearId));

        return array_values(array_filter($months, fn($m) => $m >= $start && $m <= $cap));
    }

    /**
     * Recurring overlap: wohi year + fee type, months ki range takrae aur koi student common ho to message
     * (pehla common student), warna null
     */
    public function overlapMessage(int $academicYearId, int $feeTypeId, array $studentIds, string $start, ?string $end): ?string
    {
        $studentIds = array_map('intval', $studentIds);

        $others = FeeAssignment::with('feeType')
            ->where('kind', FeeAssignment::KIND_RECURRING)
            ->where('academic_year_id', $academicYearId)
            ->where('fee_type_id', $feeTypeId)
            ->where('start_month', '<=', $end ?: '9999-12')
            ->where(function ($q) use ($start) {
                $q->whereNull('end_month')->orWhere('end_month', '>=', $start);
            })
            ->get();

        foreach ($others as $other) {
            $common = array_values(array_intersect($studentIds, array_map('intval', $other->student_ids ?: [])));
            if (!$common) {
                continue;
            }

            $student = Student::find($common[0], ['id', 'first_name', 'last_name', 'admission_no']);
            $name    = $student ? trim($student->first_name . ' ' . $student->last_name) . ' (' . $student->admission_no . ')' : 'A selected student';

            return $name . ' already has ' . ($other->feeType->name ?? 'this fee') . ' for '
                . FeeAssignment::monthsLabel($other->start_month, $other->end_month) . ' (' . $other->title . ').';
        }

        return null;
    }

    /**
     * Assignments list mein dikhane wala target text
     */
    public function targetLabel(array $data, int $studentCount): string
    {
        switch ($data['target_type']) {
            case 'classes':
                return 'Classes: ' . SchoolClass::whereIn('id', $data['class_ids'])->orderBy('numeric_name')->pluck('name')->implode(', ');
            case 'section':
                $class   = SchoolClass::find($data['class_id']);
                $section = Section::find($data['section_id']);
                return 'Class: ' . ($class ? $class->name : '-') . ' - ' . ($section ? $section->name : '-');
            case 'students':
                return $studentCount . ' selected ' . ($studentCount === 1 ? 'student' : 'students');
            default:
                return 'Whole school';
        }
    }

    /**
     * One-time: har student ki ek unpaid row. Discount = BillingService jaisa MAX rule.
     * Caller DB::transaction() mein chalata hai.
     */
    public function createOneTimeRows(FeeAssignment $assignment, Collection $studentIds): int
    {
        $assignment->loadMissing('feeType');

        $discountsByStudent = $this->billing->activeDiscounts($studentIds, $assignment);
        $period             = $assignment->due_date->format('Y-m');
        $created            = 0;

        foreach ($studentIds as $studentId) {
            StudentFees::create([
                'fee_structure_id'  => null,
                'fee_assignment_id' => $assignment->id,
                'student_id'        => $studentId,
                'fee_type_id'       => $assignment->fee_type_id,
                'amount'            => $assignment->amount,
                'discount'          => $this->billing->maxDiscount($discountsByStudent->get($studentId), $assignment),
                'paid_amount'       => 0,
                'due_date'          => $assignment->due_date->toDateString(),
                'period'            => $period,
                'status'            => 'unpaid',
            ]);
            $created++;
        }

        return $created;
    }

    /**
     * Recurring: ek mahine (Y-m) ki rows. Sirf active enrolled students jo is mahine tak admit ho chuke hon.
     * Duplicate = isi assignment ka period, ya isi fee type ki koi bhi row jiska period / due_date isi mahine mein ho.
     * Discount = BillingService jaisa MAX rule. Return: created, skipped (inactive + already generated)
     */
    public function createRecurringRowsForMonth(FeeAssignment $assignment, string $month, BillingSetting $setting): array
    {
        $assignment->loadMissing('feeType');

        $monthStart = Carbon::parse($month . '-01')->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();
        $dueDate    = $monthStart->copy()->day(min($setting->fee_due_day, $monthStart->daysInMonth));
        $requested  = collect($assignment->student_ids ?: [])->unique();

        $activeIds = Enrollment::where('academic_year_id', $assignment->academic_year_id)
            ->where('enroll_status', 1)
            ->whereIn('student_id', $requested)
            ->whereHas('student', function ($q) use ($monthEnd) {
                $q->where('status', 1)->whereDate('admission_date', '<=', $monthEnd->toDateString());
            })
            ->pluck('student_id')
            ->unique();

        $alreadyIds = StudentFees::whereIn('student_id', $activeIds)
            ->where(function ($q) use ($assignment, $month, $monthStart, $monthEnd) {
                $q->where(function ($q2) use ($assignment, $month) {
                    $q2->where('fee_assignment_id', $assignment->id)->where('period', $month);
                })->orWhere(function ($q2) use ($assignment, $month, $monthStart, $monthEnd) {
                    $q2->where('fee_type_id', $assignment->fee_type_id)
                        ->where(function ($q3) use ($month, $monthStart, $monthEnd) {
                            $q3->where('period', $month)
                                ->orWhereBetween('due_date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
                        });
                });
            })
            ->pluck('student_id')
            ->unique();

        $pendingIds = $activeIds->diff($alreadyIds)->values();
        $skipped    = $requested->count() - $pendingIds->count();

        if ($pendingIds->isEmpty()) {
            return ['created' => 0, 'skipped' => $skipped];
        }

        $discountsByStudent = $this->billing->activeDiscounts($pendingIds, $assignment);

        DB::transaction(function () use ($pendingIds, $assignment, $month, $dueDate, $discountsByStudent) {
            foreach ($pendingIds as $studentId) {
                StudentFees::create([
                    'fee_structure_id'  => null,
                    'fee_assignment_id' => $assignment->id,
                    'student_id'        => $studentId,
                    'fee_type_id'       => $assignment->fee_type_id,
                    'amount'            => $assignment->amount,
                    'discount'          => $this->billing->maxDiscount($discountsByStudent->get($studentId), $assignment),
                    'paid_amount'       => 0,
                    'due_date'          => $dueDate->toDateString(),
                    'period'            => $month,
                    'status'            => 'unpaid',
                ]);
            }
        });

        return ['created' => $pendingIds->count(), 'skipped' => $skipped];
    }
}
