<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarkSheet extends Model
{
    protected $table = 'mark_sheets';   // 👈 confirm/add karein

    protected $fillable = [
        'exam_id',
        'class_id',
        'section_id',
        'subject_id',
        'enrollment_id',
        'marks_obtained',
        'remark',
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

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}
