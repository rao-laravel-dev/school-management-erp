<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ClassTimetable extends Model
{
    protected $fillable = [
        'school_class_id',
        'section_id',
        'subject_id',
        'teacher_id',
        'day',
        'time_from',
        'time_to',
    ];

    // Always included in JSON output — any page reading this model gets
    // AM/PM time for free, no per-page JS formatting needed ever again.
    protected $appends = [
        'time_from_formatted',
        'time_to_formatted',
    ];

    public function getTimeFromFormattedAttribute()
    {
        return $this->time_from ? Carbon::parse($this->time_from)->format('g:i A') : null;
    }

    public function getTimeToFormattedAttribute()
    {
        return $this->time_to ? Carbon::parse($this->time_to)->format('g:i A') : null;
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
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
}