<?php

namespace App\Services;

use App\Models\ClassSubject;
use App\Models\Enrollment;
use App\Models\HomeWork;

class StudentHomework
{
    /**
     * Latest ACTIVE enrollment of one student (class, section, group, academic year).
     * Same rule as StudentClassSchedule and the student header composer, so header, schedule and homework agree.
     */
    public static function activeEnrollment(int $studentId): ?Enrollment
    {
        return Enrollment::with(['schoolClass', 'section', 'academicYear'])
            ->where('student_id', $studentId)
            ->where('enroll_status', 1)
            ->latest('id')
            ->first();
    }

    /**
     * Homework of one student's own class/section/subjects (shared by student and parent portals).
     * The caller must already have verified that the student belongs to the logged-in user.
     */
    public static function forStudent(int $studentId): array
    {
        $enrollment = self::activeEnrollment($studentId);

        $homeworks = collect();

        if ($enrollment) {
            // Only subjects of this class (no group, or the student's own group)
            $subjectIds = ClassSubject::where('class_id', $enrollment->class_id)
                ->where(function ($q) use ($enrollment) {
                    $q->whereNull('group_id')->orWhere('group_id', $enrollment->group_id);
                })
                ->pluck('subject_id');

            $year = $enrollment->academicYear;

            $homeworks = HomeWork::with(['subject', 'createdBy'])
                ->where('class_id', $enrollment->class_id)
                ->where('section_id', $enrollment->section_id)
                ->where('status', 1)
                ->whereIn('subject_id', $subjectIds)
                ->when($year && $year->start_date, fn ($q) => $q->whereDate('homework_date', '>=', $year->start_date))
                ->when($year && $year->end_date, fn ($q) => $q->whereDate('homework_date', '<=', $year->end_date))
                ->orderByDesc('due_date')
                ->get();
        }

        $today = now()->startOfDay();
        $pendingCount = $homeworks->filter(fn ($hw) => $hw->due_date->gte($today))->count();
        $pastDueCount = $homeworks->count() - $pendingCount;

        return compact('homeworks', 'pendingCount', 'pastDueCount');
    }
}
