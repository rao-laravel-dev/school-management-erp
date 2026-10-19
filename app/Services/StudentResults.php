<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\ExamSchedule;
use App\Models\MarkSheet;
use App\Models\Result;

class StudentResults
{
    /**
     * Published results of one student (shared by student and parent portals).
     * The caller must already have verified that the student belongs to the logged-in user.
     */
    public static function forStudent($studentId): array
    {
        $enrollment = Enrollment::where('student_id', $studentId)
            ->where('enroll_status', 1) // Active
            ->latest()
            ->first();

        if (!$enrollment) {
            return ['results' => collect()];
        }

        $results = Result::with(['exam.examType', 'grade'])
            ->where('enrollment_id', $enrollment->id)
            ->whereHas('exam', function ($q) {
                $q->where('publish_result', 1);
            })
            ->orderByDesc('created_at')
            ->get();

        return compact('results');
    }

    /**
     * One published result of one student with subject marks (404 if it is not this student's result).
     * The caller must already have verified that the student belongs to the logged-in user.
     */
    public static function details($studentId, $resultId): array
    {
        $enrollment = Enrollment::with(['student.parent', 'schoolClass', 'section'])
            ->where('student_id', $studentId)
            ->where('enroll_status', 1)
            ->latest()
            ->first();

        abort_if(!$enrollment, 404);

        $result = Result::with(['exam.examType', 'grade'])
            ->where('enrollment_id', $enrollment->id)
            ->whereHas('exam', function ($q) {
                $q->where('publish_result', 1);
            })
            ->findOrFail($resultId);

        $subjectMarks = MarkSheet::with('subject')
            ->where('exam_id', $result->exam_id)
            ->where('enrollment_id', $enrollment->id)
            ->get();

        $examSchedules = ExamSchedule::where('exam_id', $result->exam_id)
            ->where('class_id', $result->class_id)
            ->get()
            ->keyBy('subject_id');

        // Calculations
        $percentage = $result->percentage;

        $percentageBadge = match (true) {
            $percentage >= 80 => 'bg-success-subtle text-success',
            $percentage >= 60 => 'bg-primary-subtle text-primary',
            $percentage >= 50 => 'bg-warning-subtle text-warning',
            default => 'bg-danger-subtle text-danger',
        };

        $gradeName = $result->grade->grade_name ?? '-';
        $gradeBadge = match (true) {
            str_starts_with($gradeName, 'A') => 'bg-success-subtle text-success',
            str_starts_with($gradeName, 'B') => 'bg-primary-subtle text-primary',
            str_starts_with($gradeName, 'C') => 'bg-info-subtle text-info',
            str_starts_with($gradeName, 'D') => 'bg-warning-subtle text-warning',
            str_starts_with($gradeName, 'F') => 'bg-danger-subtle text-danger',
            default => 'bg-secondary-subtle text-secondary',
        };

        $fatherName = $enrollment->student->parent->father_name ?? '-';

        // Admission Number (Agar enrollment table me hai ya student table me, dono handle kar lein)
        $admissionNo = $enrollment->admission_no ?? ($enrollment->student->admission_no ?? '-');

        return compact(
            'result',
            'subjectMarks',
            'examSchedules',
            'enrollment',
            'percentageBadge',
            'gradeBadge',
            'gradeName',
            'fatherName',
            'admissionNo'
        );
    }
}
