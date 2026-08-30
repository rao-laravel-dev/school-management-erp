<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSkillMark extends Model
{
    protected $fillable = [
        'enrollment_id',
        'exam_id',
        'area_id',
        'grade_id',
        'remark',
        'created_by',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function area()
    {
        return $this->belongsTo(SkillAssessmentArea::class, 'area_id');
    }

    public function grade()
    {
        return $this->belongsTo(MarksGrade::class, 'grade_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
