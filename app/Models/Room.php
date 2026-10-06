<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    public const TYPES = ['classroom' => 'Classroom', 'lab' => 'Lab', 'hall' => 'Hall', 'library' => 'Library', 'other' => 'Other'];

    // Floor string mein hi save hota hai (label hi value hai)
    public const FLOORS = ['Ground Floor', '1st Floor', '2nd Floor', '3rd Floor', '4th Floor', '5th Floor'];

    protected $fillable = ['room_no', 'name', 'building', 'floor', 'capacity', 'type', 'status', 'school_class_id', 'section_id'];

    protected $casts = [
        'capacity' => 'integer',
        'status'   => 'boolean',
    ];

    public function classTimetables()
    {
        return $this->hasMany(ClassTimetable::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
