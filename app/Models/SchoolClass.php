<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SchoolClass extends Model
{
    protected $table = 'school_class';
    protected $fillable = [
        'name',
        'has_subjects',
        'class_code',
        'numeric_name',
        'slug',
        'description',
        'status',
    ];

    protected $casts = [
        'has_subjects' => 'boolean',
        'status' => 'boolean',
    ];

    // Accessor for Name (ucwords)
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => ucwords($value),
            set: fn(string $value) => strtolower($value), // Database me hamesha lowercase save hoga for consistency
        );
    }

    /**
     * 🔗 NEW Relation: Many-to-Many via the new pivot table
     * Is ke zariye hum UI par classes aur sections ko map karenge.
     */
    public function mappedSections()
    {
        return $this->belongsToMany(Section::class, 'school_class_section', 'school_class_id', 'section_id')
            ->withPivot('id', 'status')
            ->withTimestamps();
    }

    // Ye naya add karein (Alias)
    public function sections()
    {
        return $this->mappedSections();
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'class_id', 'subject_id')
            ->withPivot('group_id', 'full_marks', 'pass_marks')
            ->withTimestamps();
    }
    // SchoolClass Model mein
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    // In SchoolClass.php
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'class_id', 'id');
    }

    public function classTimetables()
    {
        return $this->hasMany(ClassTimetable::class, 'school_class_id');
    }


// Roll number banane ke liye clean class-code (numeric_name se independent — negative sign ka masla hi khatam)
public function getRollCodeAttribute(): string
{
    // class_code hamesha "CLS-XXX" format mein hota hai
    return Str::after($this->class_code, 'CLS-'); // "CLS-10" -> "10", "CLS-MONT" -> "MONT"
}
}
