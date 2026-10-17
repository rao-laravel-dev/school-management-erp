<?php

namespace App\Services;

use App\Models\ClassTimetable;
use App\Models\Enrollment;
use App\Models\Room;

class StudentClassSchedule
{
    /**
     * Weekly timetable of one student's own class/section (shared by student and parent portals).
     * The caller must already have verified that the student belongs to the logged-in user.
     */
    public static function forStudent(int $studentId, ?string $week): array
    {
        // Latest active enrollment decides class, section and academic year
        $enrollment = Enrollment::where('student_id', $studentId)->where('enroll_status', 1)->latest('id')->first();

        if (!$enrollment) {
            return ['timetable' => (object) []];
        }

        $timetable = ClassTimetable::with(['subject', 'teacher'])
            ->where('academic_year_id', $enrollment->academic_year_id)
            ->where('school_class_id', $enrollment->class_id)
            ->where('section_id', $enrollment->section_id)
            ->get();

        // Room belongs to class-section, not to the period
        $roomNo = Room::where('school_class_id', $enrollment->class_id)
            ->where('section_id', $enrollment->section_id)
            ->value('room_no');

        $timetable->each(fn ($row) => $row->setAttribute('assigned_room_no', $roomNo));

        $grouped = $timetable->groupBy('day');

        return array_merge(
            ['timetable' => $grouped],
            TimetableDayStatus::forWeek($week, $grouped->keys()->all())
        );
    }
}
