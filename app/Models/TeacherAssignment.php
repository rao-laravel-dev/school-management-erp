<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAssignment extends Model
{
    protected $fillable = [
        'academic_year_id',
        'class_id',
        'section_id',
        'teacher_id',
    ];

    public function schoolClass()  { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function section()      { return $this->belongsTo(Section::class, 'section_id'); }
    public function teacher()      { return $this->belongsTo(Teacher::class, 'teacher_id'); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class, 'academic_year_id'); }
}