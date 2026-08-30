<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class MarksGrade extends Model
{
    protected $fillable = [
        'exam_id',
        'grade_name',
        'remark',
        'min_percentage',
        'max_percentage',
    ];

    // Display mein hamesha Title Case
    protected function gradeName(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value ? ucwords($value) : $value,
        );
    }

    protected function remark(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value ? ucwords($value) : $value,
        );
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
