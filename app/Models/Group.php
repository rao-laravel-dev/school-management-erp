<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    // 🔥 class_id removed because groups are now global entities
    protected $fillable = ['name', 'group_code', 'description', 'status'];

    // Name Accessor - Automatically capitalizes titles for UI displays
    public function getNameAttribute($value)
    {
        return $value ? ucwords($value) : null;
    }

    // Name Mutator - Saves lowercase variations inside DB columns safely
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value ? strtolower($value) : null;
    }

    /**
     * Group ke linked subjects nikalne ke liye via central pivot table
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'group_id', 'subject_id')
                    ->withPivot('class_id', 'full_marks', 'pass_marks')
                    ->withTimestamps();
    }

    /**
     * Group ki linked classes nikalne ke liye via class_subject bridge table
     */
    public function school_class()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject', 'group_id', 'class_id')
                    ->withTimestamps();
    }
}