<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    // class_id aur group_id yahan se bilkul saaf kar diye kyunki yeh master table hai
    protected $fillable = ['name', 'subject_code', 'type', 'description', 'status'];

    // Accessor & Mutator for Name (Old Method Syntax)
    public function getNameAttribute($value)
    {
        return $value ? ucwords($value) : null;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value ? strtolower($value) : null;
    }

    // Accessor & Mutator for Subject Code
    public function getSubjectCodeAttribute($value)
    {
        return $value ? strtoupper($value) : null;
    }

    public function setSubjectCodeAttribute($value)
    {
        $this->attributes['subject_code'] = $value ? strtoupper($value) : null;
    }

    // Relationships (Pivot tables ke liye ready rahein gi)
    public function school_class(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject', 'subject_id', 'class_id')->withPivot('group_id');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_subject', 'subject_id', 'group_id')->withTimestamps();
    }

    public function classTimetables()
    {
        return $this->hasMany(ClassTimetable::class);
    }
}
