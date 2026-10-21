<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingSetting extends Model
{
    protected $fillable = [
        'academic_year_id',
        'fee_generate_day',
        'fee_due_day',
        'salary_generate_day',
        'auto_fees',
        'auto_salary',
        'fees_from_month',
        'salary_from_month',
    ];

    protected $casts = [
        'auto_fees'   => 'boolean',
        'auto_salary' => 'boolean',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
