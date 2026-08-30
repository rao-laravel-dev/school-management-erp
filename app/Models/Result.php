<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $fillable = [
        'exam_id',
        'class_id',
        'section_id',
        'enrollment_id',
        'total_marks',
        'obtained_marks',
        'percentage',
        'grade_id',
        'position',
        'status',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function grade()
    {
        return $this->belongsTo(MarksGrade::class, 'grade_id');
    }
}
