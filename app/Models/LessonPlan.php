<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonPlan extends Model
{
    protected $guarded = [];

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
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
    public function classTimetable()
    {
        return $this->belongsTo(ClassTimetable::class);
    }
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function comments()
    {
        return $this->hasMany(LessonPlanComment::class)->latest();
    }
}
