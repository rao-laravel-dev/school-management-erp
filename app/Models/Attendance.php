<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    
    protected $table = 'attendances';

    protected $fillable = [
        'enrollment_id', 
        'class_id', 
        'section_id', 
        'attendance_date', 
        'status',
        'time_out', 
        'remarks', 
        'marked_via', 
        'marked_by', 
        'device_id', 
        'scanned_at'
    ];

    // Constants for clean code
    const STATUS_ABSENT = 0;
    const STATUS_PRESENT = 1;
    const STATUS_LATE    = 2;
    const STATUS_LEAVE   = 3;

    // Relationship

    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            Enrollment::class,
            'id',          // Enrollment table ka PK
            'id',          // User table ka PK
            'enrollment_id', // Attendance table mein Enrollment ka FK
            'user_id'      // Enrollment table mein User ka FK
        );
    }
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    // Ye method aapko Student ke user tak le jayega
    public function student()
    {
        // Attendance -> Enrollment -> Student
        return $this->hasOneThrough(
            Student::class,
            Enrollment::class,
            'id',            // Enrollment ID (Local in Attendance)
            'id',            // Student ID (Target in Student)
            'enrollment_id', // Foreign key in Attendance
            'student_id'     // Local key in Enrollment
        );
    }

    

    // MUTATOR: Ye ensure karega ke agar koi string "present" bhej de, to wo automatically 1 mein convert ho jaye
    // public function setStatusAttribute($value)
    // {
    //     if (is_string($value)) {
    //         $map = [
    //             'absent' => self::STATUS_ABSENT,
    //             'present' => self::STATUS_PRESENT,
    //             'late' => self::STATUS_LATE,
    //             'leave' => self::STATUS_LEAVE,
    //         ];
    //         $this->attributes['status'] = $map[strtolower($value)] ?? self::STATUS_ABSENT;
    //     } else {
    //         $this->attributes['status'] = $value;
    //     }
    // }

    // ACCESSOR: Ye database se value nikalte waqt human-readable bana dega
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PRESENT => 'Present',
            self::STATUS_LATE    => 'Late',
            self::STATUS_LEAVE   => 'Leave',
            default              => 'Absent',
        };
    }
}
