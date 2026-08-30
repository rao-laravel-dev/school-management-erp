<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Topic extends Model
{
    protected $fillable = [
        'academic_year_id', // New Add
        'class_id',
        'section_id',
        'subject_id',
        'lesson_id',
        'name',
        'is_completed',
        'completion_date',
        'status',
    ];

    protected $casts = [
        'completion_date' => 'date',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    public function lessonPlans()
    {
        return $this->hasMany(LessonPlan::class);
    }
}
