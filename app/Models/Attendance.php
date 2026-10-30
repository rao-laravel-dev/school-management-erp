<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\StudentAttendanceNotification;
use Illuminate\Support\Carbon;

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
    const STATUS_HALF_DAY = 3;
    const STATUS_LEAVE   = 4; // 4 + remarks "Holiday" = Holiday

    // Parent alert (bell + push): manual, bulk aur scan teeno save yahin se guzarte hain
    protected static function booted(): void
    {
        static::saved(function (Attendance $attendance) {
            // Status naya/badla, ya time_out pehli dafa laga (school se chutti); same values ka re-save = kuch nahi
            $statusChanged = $attendance->wasRecentlyCreated || $attendance->wasChanged('status');
            $leftSchool = filled($attendance->time_out)
                && ($attendance->wasRecentlyCreated || blank($attendance->getOriginal('time_out')));
            if (! $statusChanged && ! $leftSchool) {
                return;
            }

            // Holiday (4 + remarks "Holiday") aur aaj ke ilawa koi date (back-dated edit) = alert nahi
            $isHoliday = (int) $attendance->status === self::STATUS_LEAVE
                && strcasecmp(trim((string) $attendance->remarks), 'Holiday') === 0;
            if ($isHoliday || ! Carbon::parse($attendance->attendance_date)->isToday()) {
                return;
            }

            $attendance->loadMissing(['enrollment.student.parent.user', 'enrollment.schoolClass', 'enrollment.section']);
            $parentUser = $attendance->enrollment?->student?->parent?->user;
            if (! $parentUser) {
                return; // parent account nahi (ya student trashed): kisi ko nahi bhejna
            }

            $parentUser->notify(new StudentAttendanceNotification($attendance, $statusChanged, $leftSchool));
        });
    }

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
    // Marker user (soft-deleted staff ka naam bhi history mein dikhe)
    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by')->withTrashed();
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
            self::STATUS_HALF_DAY => 'Half Day',
            self::STATUS_LEAVE   => strcasecmp(trim((string) $this->remarks), 'Holiday') === 0 ? 'Holiday' : 'Leave',
            default              => 'Absent',
        };
    }
}
