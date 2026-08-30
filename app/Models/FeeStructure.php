<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    protected $fillable = [
        'fee_type_id',
        'school_class_id',
        'academic_year_id',
        'amount',
        'due_date',
        'last_generated_period',  
        'last_generated_at',      
    ];

    protected $casts = [
        'due_date' => 'date',
        'last_generated_at' => 'datetime',
    ];

    public function feeType()
    {
        return $this->belongsTo(FeeType::class, 'fee_type_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function isGeneratedForCurrentPeriod(): bool
    {
        if ($this->feeType->frequency === 'monthly') {
            return $this->last_generated_period === $this->due_date->format('Y-m');
        }
        return !is_null($this->last_generated_at);
    }
}
