<?php

namespace App\Models;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    // 🛠️ UPDATED: 'class_id' ko $fillable se remove kar diya ha
    protected $fillable = ['name', 'section_code', 'capacity', 'description', 'status'];

    /**
     * Accessor & Mutator for Name
     * Sections aam tor par A, B hote hain to UpperCase behtar hai
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => strtoupper($value), // Display ke liye Capital
            set: fn($value) => strtoupper($value), // 🛠️ Ab Database mein bhi Capital Save hoga
        );
    }

    /**
     * 🔗 MANY-TO-MANY RELATIONSHIP
     * Yeh batata hai ke yeh section kaun-kaun si classes ke sath mapped hai.
     */
    public function schoolClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'school_class_section', 'section_id', 'school_class_id')
            ->withPivot('id', 'status')
            ->withTimestamps();
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'section_id');
    }

    public function classTimetables()
    {
        return $this->hasMany(ClassTimetable::class);
    }
}
