<?php

namespace App\Notifications;

use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

// Parent ko apne bachay ki AAJ ki attendance: status naya/badla ya school se chutti (time_out). Bell + push
class StudentAttendanceNotification extends AppNotification
{
    // Sirf plain strings: queue job mein model dobara load nahi hota aur bell + push ka text same rehta hai
    protected string $titleText;
    protected string $bodyText;
    protected string $urlPath;
    protected ?string $imagePath;

    // $attendance par enrollment.student, enrollment.schoolClass, enrollment.section loaded hon (Attendance::booted())
    public function __construct(Attendance $attendance, bool $statusChanged, bool $leftSchool)
    {
        parent::__construct();

        $enrollment = $attendance->enrollment;
        $student = $enrollment->student;
        $classSection = trim(($enrollment->schoolClass->name ?? '') . ' - ' . ($enrollment->section->name ?? ''), ' -');

        $this->titleText = "{$student->full_name} ({$classSection})";

        // "Marked present at 7:52 AM" / "Left school at 1:30 PM" / dono ek save mein: "Marked present at 7:52 AM, left school at 1:30 PM"
        $parts = [];
        if ($statusChanged) {
            $parts[] = 'Marked ' . self::statusWord($attendance) . ' at ' . now()->format('g:i A');
        }
        if ($leftSchool) {
            $parts[] = 'left school at ' . Carbon::parse($attendance->time_out)->format('g:i A');
        }
        $this->bodyText = ucfirst(implode(', ', $parts)) . '.';

        // Parent sidebar "Attendance" yahi page kholta hai (relative path)
        $this->urlPath = route('parent.students.child-dashboard', $student->id, false);

        // photo_url accessor se relative path; default no-image = null (bell khud default dikhata hai)
        $path = Str::after($student->photo_url, asset(''));
        $this->imagePath = $path === 'uploads/no_image.jpg' ? null : $path;
    }

    // Saada alfaaz: 0 absent, 1 present, 2 late, 3 half day, 4 on leave (Holiday yahan aata hi nahi)
    protected static function statusWord(Attendance $attendance): string
    {
        return match ((int) $attendance->status) {
            Attendance::STATUS_PRESENT  => 'present',
            Attendance::STATUS_LATE     => 'late',
            Attendance::STATUS_HALF_DAY => 'half day',
            Attendance::STATUS_LEAVE    => 'on leave',
            default                     => 'absent',
        };
    }

    protected function title(object $notifiable): string
    {
        return $this->titleText;
    }

    protected function body(object $notifiable): string
    {
        return $this->bodyText;
    }

    protected function url(object $notifiable): string
    {
        return $this->urlPath;
    }

    protected function image(object $notifiable): ?string
    {
        return $this->imagePath;
    }
}
