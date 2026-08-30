<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'exam_type_id',
        'academic_year_id',
        'name',
        'publish',
        'publish_result',
    ];

    // Display mein hamesha Title Case (har word ka pehla letter capital)
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => ucwords($value),
        );
    }
 
    public function examType()
    {
        return $this->belongsTo(ExamType::class);
    }
 
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
 
    public function schedules()
    {
        return $this->hasMany(ExamSchedule::class);
    }
 
    public function marksGrades()
    {
        return $this->hasMany(MarksGrade::class);
    }
}
