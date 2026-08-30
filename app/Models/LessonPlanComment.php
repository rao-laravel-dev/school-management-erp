<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonPlanComment extends Model
{
    protected $guarded = [];

    public function lessonPlan()
    {
        return $this->belongsTo(LessonPlan::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
