<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\MarksGrade;
use App\Models\MarkSheet;
use App\Models\Result;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $exams = Exam::with('examType')->latest()->get();
        $classes = SchoolClass::where('status', 1)->get();

        $selectedExamId = $request->exam_id;
        $selectedClassId = $request->class_id;
        $selectedSectionId = $request->section_id;

        $sections = collect();
        $results = collect();

        if ($selectedClassId) {
            $sections = DB::table('school_class_section')
                ->join('sections', 'school_class_section.section_id', '=', 'sections.id')
                ->where('school_class_section.school_class_id', $selectedClassId)
                ->select('sections.id', 'sections.name')
                ->get();
        }

        if ($selectedExamId && $selectedClassId && $selectedSectionId) {
            $results = Result::with(['enrollment.student', 'grade'])
                ->where('exam_id', $selectedExamId)
                ->where('class_id', $selectedClassId)
                ->where('section_id', $selectedSectionId)
                ->whereHas('enrollment.student') // soft-deleted student ki result row list mein nahi (row DB mein rehti hai)
                ->orderBy('position')
                ->get();
        }

        return view('admin.result.index', compact(
            'exams', 'classes', 'sections', 'results',
            'selectedExamId', 'selectedClassId', 'selectedSectionId'
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

    public function calculate(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:school_class,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $examId = $request->exam_id;
        $classId = $request->class_id;
        $sectionId = $request->section_id;

        // ===== 1. Fail threshold: derive from lowest grade's max_percentage =====
        $lowestGrade = MarksGrade::where('exam_id', $examId)
            ->orderBy('min_percentage', 'asc')
            ->first();

        if (!$lowestGrade) {
            return response()->json([
                'status' => 'error',
                'message' => 'No grades defined for this Exam. Please set up Marks Grade first.',
            ], 422);
        }

        $failThreshold = $lowestGrade->max_percentage; // e.g. F = 0-49, threshold = 49

        // ===== 2. Get all subjects scheduled for this exam+class (total possible marks) =====
        $schedules = ExamSchedule::where('exam_id', $examId)
            ->where('class_id', $classId)
            ->get();

        if ($schedules->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No Exam Schedule found for this Exam+Class.',
            ], 422);
        }

        $totalMaxMarks = $schedules->sum('max_marks');
        $subjectIds = $schedules->pluck('subject_id');

        // ===== 3. Get all active students of this class-section =====
        $enrollments = Enrollment::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('enroll_status', 1)
            ->whereHas('student') // soft-deleted student ka result generate nahi hota
            ->get();

        if ($enrollments->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active students found in this Class/Section.',
            ], 422);
        }

        // ===== 4. Get all marks for this exam+class+section+subjects in one query =====
        $allMarks = MarkSheet::where('exam_id', $examId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereIn('subject_id', $subjectIds)
            ->get()
            ->groupBy('enrollment_id');

        // ===== 5. Get all grades for this exam, sorted high to low (for lookup) =====
        $grades = MarksGrade::where('exam_id', $examId)
            ->orderBy('min_percentage', 'desc')
            ->get();

        // ===== 6. Calculate each student's result =====
        $studentResults = [];

        foreach ($enrollments as $enrollment) {
            $marksForStudent = $allMarks->get($enrollment->id, collect());
            $obtainedMarks = $marksForStudent->sum('marks_obtained');
            $percentage = $totalMaxMarks > 0 ? round(($obtainedMarks / $totalMaxMarks) * 100, 2) : 0;

            // Grade lookup
            $grade = $grades->first(function ($g) use ($percentage) {
                return $percentage >= $g->min_percentage && $percentage <= $g->max_percentage;
            });

            $status = $percentage > $failThreshold ? 'Pass' : 'Fail';

            $studentResults[] = [
                'enrollment_id' => $enrollment->id,
                'total_marks' => $totalMaxMarks,
                'obtained_marks' => $obtainedMarks,
                'percentage' => $percentage,
                'grade_id' => $grade->id ?? null,
                'status' => $status,
            ];
        }

        // ===== 7. Rank/Position: sort by obtained_marks descending =====
        usort($studentResults, function ($a, $b) {
            return $b['obtained_marks'] <=> $a['obtained_marks'];
        });

        $position = 1;
        foreach ($studentResults as $index => &$result) {
            // handle ties: same marks = same position
            if ($index > 0 && $result['obtained_marks'] < $studentResults[$index - 1]['obtained_marks']) {
                $position = $index + 1;
            }
            $result['position'] = $position;
        }
        unset($result);

        // ===== 8. Save/update results =====
        foreach ($studentResults as $result) {
            Result::updateOrCreate(
                [
                    'exam_id' => $examId,
                    'enrollment_id' => $result['enrollment_id'],
                ],
                [
                    'class_id' => $classId,
                    'section_id' => $sectionId,
                    'total_marks' => $result['total_marks'],
                    'obtained_marks' => $result['obtained_marks'],
                    'percentage' => $result['percentage'],
                    'grade_id' => $result['grade_id'],
                    'position' => $result['position'],
                    'status' => $result['status'],
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Results calculated successfully for ' . count($studentResults) . ' student(s).',
        ]);
    }
}
