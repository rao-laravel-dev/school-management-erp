<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model
{
    // 1. Table ka naam define karein
    protected $table = 'class_subject';

    // 2. Fillable fields (jo database mein store honi hain)
    protected $fillable = [
        'class_id', 
        'subject_id', 
        'group_id', 
        'full_marks', 
        'pass_marks'
    ];

    
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
    
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
