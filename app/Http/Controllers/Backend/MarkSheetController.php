<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\MarkSheet;
use App\Models\SchoolClass;
use App\Models\ClassTimetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarkSheetController extends Controller
{
    public function index(Request $request)
    {
        $exams = Exam::with('examType')->latest()->get();
        $classes = SchoolClass::where('status', 1)->get();

        $selectedExamId = $request->exam_id;
        $selectedClassId = $request->class_id;
        $selectedSectionId = $request->section_id;
        $selectedSubjectId = $request->subject_id;

        $sections = collect();
        $subjects = collect();
        $students = collect();
        $schedule = null;
        $isLocked = false;
        $hasAccess = true;

        if ($selectedClassId) {
            $sections = DB::table('school_class_section')
                ->join('sections', 'school_class_section.section_id', '=', 'sections.id')
                ->where('school_class_section.school_class_id', $selectedClassId)
                ->select('sections.id', 'sections.name')
                ->get();

            $subjects = ClassSubject::with('subject')
                ->where('class_id', $selectedClassId)
                ->get()
                ->map(fn($cs) => ['id' => $cs->subject_id, 'name' => $cs->subject->name ?? 'N/A']);
        }

        if ($selectedExamId && $selectedClassId && $selectedSectionId && $selectedSubjectId) {

            $exam = Exam::findOrFail($selectedExamId);
            $isLocked = (bool) $exam->publish_result;

            // ===== Teacher access check (Admin bypasses this) =====
            $user = Auth::user();
            if ($user->teacher) {
                // Marks ka haq class_timetables se (teacher + class + section + subject)
                $hasAccess = ClassTimetable::where('academic_year_id', $exam->academic_year_id)
                    ->where('teacher_id', $user->teacher->id)
                    ->where('school_class_id', $selectedClassId)
                    ->where('section_id', $selectedSectionId)
                    ->where('subject_id', $selectedSubjectId)
                    ->exists();
            }

            if ($hasAccess) {
                // Max/Passing marks from Exam Schedule
                $schedule = ExamSchedule::where('exam_id', $selectedExamId)
                    ->where('class_id', $selectedClassId)
                    ->where('subject_id', $selectedSubjectId)
                    ->first();

                // Active students of this class-section
                $enrollments = Enrollment::with('student')
                    ->where('class_id', $selectedClassId)
                    ->where('section_id', $selectedSectionId)
                    ->where('enroll_status', 1)
                    ->whereHas('student') // soft-deleted student marks entry mein nahi
                    ->orderBy('roll_no')
                    ->get();

                // Existing marks (if any already saved)
                $existingMarks = MarkSheet::where('exam_id', $selectedExamId)
                    ->where('subject_id', $selectedSubjectId)
                    ->whereIn('enrollment_id', $enrollments->pluck('id'))
                    ->get()
                    ->keyBy('enrollment_id');

                $students = $enrollments->map(function ($enrollment) use ($existingMarks) {
                    return [
                        'enrollment_id' => $enrollment->id,
                        'roll_no' => $enrollment->roll_no,
                        'name' => trim(($enrollment->student->first_name ?? '') . ' ' . ($enrollment->student->last_name ?? '')),
                        'marks_obtained' => $existingMarks->get($enrollment->id)->marks_obtained ?? null,
                        'remark' => $existingMarks->get($enrollment->id)->remark ?? null,
                    ];
                });
            }
        }

        return view('admin.marksheet.index', compact(
            'exams',
            'classes',
            'sections',
            'subjects',
            'students',
            'schedule',
            'isLocked',
            'hasAccess',
            'selectedExamId',
            'selectedClassId',
            'selectedSectionId',
            'selectedSubjectId'
        ));
    }

    public function getSectionsByClass($classId)
    {
        $sections = DB::table('school_class_section')
            ->join('sections', 'school_class_section.section_id', '=', 'sections.id')
            ->where('school_class_section.school_class_id', $classId)
            ->select('sections.id', 'sections.name')
            ->get();

        return response()->json($sections);
    }

    public function getSubjectsByClass($classId)
    {
        $subjects = ClassSubject::with('subject')
            ->where('class_id', $classId)
            ->get()
            ->map(fn($cs) => ['id' => $cs->subject_id, 'name' => $cs->subject->name ?? 'N/A']);

        return response()->json($subjects);
    }

    public function save(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:school_class,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'marks' => 'required|array',
            'marks.*' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|array',
            'remarks.*' => 'nullable|string|max:255',
        ]);

        $exam = Exam::findOrFail($request->exam_id);

        // ===== Lock check =====
        if ($exam->publish_result) {
            return response()->json([
                'status' => 'error',
                'message' => 'This exam\'s result is published. Marks are locked.',
            ], 403);
        }

        // ===== Teacher access re-check (server-side, don't trust the UI) =====
        $user = Auth::user();
        if ($user->teacher) {
            // Marks ka haq class_timetables se (teacher + class + section + subject)
            $hasAccess = ClassTimetable::where('academic_year_id', $exam->academic_year_id)
                ->where('teacher_id', $user->teacher->id)
                ->where('school_class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->where('subject_id', $request->subject_id)
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not assigned to this class/subject.',
                ], 403);
            }
        }

        // ===== Max marks validation from Exam Schedule =====
        $schedule = ExamSchedule::where('exam_id', $request->exam_id)
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->first();

        $maxMarks = $schedule->max_marks ?? null;

        foreach ($request->marks as $enrollmentId => $marksObtained) {
            if ($marksObtained !== null && $maxMarks !== null && $marksObtained > $maxMarks) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Marks cannot exceed Max Marks ({$maxMarks}).",
                ], 422);
            }

            MarkSheet::updateOrCreate(
                [
                    'exam_id' => $request->exam_id,
                    'subject_id' => $request->subject_id,
                    'enrollment_id' => $enrollmentId,
                ],
                [
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'marks_obtained' => $marksObtained,
                    'remark' => $request->remarks[$enrollmentId] ?? null,
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Marks saved successfully.',
        ]);
    }
}
