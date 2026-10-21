<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeAssignment extends Model
{
    public const KIND_ONE_TIME  = 'one_time';
    public const KIND_RECURRING = 'recurring_monthly';

    public const KINDS   = [self::KIND_ONE_TIME, self::KIND_RECURRING];
    public const TARGETS = ['school', 'classes', 'section', 'students'];

    // Fee Type se kind tay hota hai: one_time -> one_time, per-student (jaise Tuition) -> recurring_monthly.
    // Baqi (class-wide monthly/annual/...) Assign Fee mein nahi aate, woh Fee Structure se aate hain.
    public static function kindForFeeType(FeeType $feeType): ?string
    {
        if ($feeType->frequency === 'one_time') {
            return self::KIND_ONE_TIME;
        }

        return $feeType->is_per_student ? self::KIND_RECURRING : null;
    }

    public static function assignableFeeTypes()
    {
        return FeeType::where(function ($q) {
            $q->where('frequency', 'one_time')->orWhere('is_per_student', true);
        })->orderBy('name')->get();
    }

    protected $fillable = [
        'academic_year_id',
        'fee_type_id',
        'kind',
        'title',
        'amount',
        'due_date',
        'start_month',
        'end_month',
        'target_type',
        'target_label',
        'student_ids',
        'created_by',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'due_date'    => 'date',
        'student_ids' => 'array',
    ];

    // Recurring: kya yeh assignment is mahine (Y-m) mein active hai
    public function isActiveIn(string $month): bool
    {
        return $this->start_month <= $month && (!$this->end_month || $this->end_month >= $month);
    }

    // List / messages mein: "Oct 2026 - Mar 2027" ya "Oct 2026 - open"
    public static function monthsLabel(?string $start, ?string $end): string
    {
        if (!$start) {
            return '-';
        }

        return \Carbon\Carbon::parse($start . '-01')->format('M Y') . ' - '
            . ($end ? \Carbon\Carbon::parse($end . '-01')->format('M Y') : 'open');
    }

    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function studentFees()
    {
        return $this->hasMany(StudentFees::class, 'fee_assignment_id');
    }
}
