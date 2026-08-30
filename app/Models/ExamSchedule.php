<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSchedule extends Model
{
    protected $fillable = [
        'exam_id',
        'class_id',
        'subject_id',
        'date',
        'time_from',
        'time_to',
        'max_marks',
        'passing_marks',
    ];
 
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
 
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
 
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
