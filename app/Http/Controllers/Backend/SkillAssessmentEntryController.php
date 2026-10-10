<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\MarksGrade;
use App\Models\SchoolClass;
use App\Models\SkillAssessmentArea;
use App\Models\StudentSkillMark;
use Illuminate\Http\Request;

class SkillAssessmentEntryController extends Controller
{
    public function index()
    {
        $exams = Exam::all(); // 'name' field already sahi hai, blade mein $exam->name use ho raha hai
        $classes = SchoolClass::where('status', 1)->get();
        $areas = SkillAssessmentArea::where('status', 1)->with('category')->get();

        return view('admin.skill_assessment_entry.index', compact('exams', 'classes', 'areas'));
    }
    // End Method

    public function getSections(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_class,id',
        ]);

        $class = SchoolClass::findOrFail($request->class_id);

        $sections = $class->sections()
            ->wherePivot('status', 1)
            ->get(['sections.id', 'sections.name']);

        return response()->json($sections);
    }
    // End Method

    public function getGrades(Request $request)
    {
        $grades = MarksGrade::where('exam_id', $request->exam_id)->get(['id', 'grade_name']);
        return response()->json($grades);
    }
    // End Method


    public function getStudents(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:school_class,id',
            'section_id' => 'required|exists:sections,id',
            'area_id' => 'required|exists:skill_assessment_areas,id',
        ]);

        $enrollments = Enrollment::with('student')
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('enroll_status', 1)
            ->whereHas('student') // soft-deleted student list mein nahi
            ->orderBy('roll_no')
            ->get();

        $existingMarks = StudentSkillMark::where('exam_id', $request->exam_id)
            ->where('area_id', $request->area_id)
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->get()
            ->keyBy('enrollment_id');

        $data = $enrollments->map(function ($enrollment) use ($existingMarks) {
            $mark = $existingMarks->get($enrollment->id);
            return [
                'enrollment_id' => $enrollment->id,
                'roll_no' => $enrollment->roll_no,
                'student_name' => $enrollment->student->full_name ?? '-',
                'grade_id' => $mark->grade_id ?? null,
                'remark' => $mark->remark ?? '',
            ];
        });

        return response()->json($data);
    }
    // End Method

    public function save(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'area_id' => 'required|exists:skill_assessment_areas,id',
            'entries' => 'required|array',
            'entries.*.enrollment_id' => 'required|exists:enrollments,id',
            'entries.*.grade_id' => 'nullable|exists:marks_grades,id',
            'entries.*.remark' => 'nullable|string',
        ]);

        foreach ($request->entries as $entry) {
            if (empty($entry['grade_id']) && empty($entry['remark'])) {
                continue; // skip fully empty rows
            }

            StudentSkillMark::updateOrCreate(
                [
                    'enrollment_id' => $entry['enrollment_id'],
                    'exam_id' => $request->exam_id,
                    'area_id' => $request->area_id,
                ],
                [
                    'grade_id' => $entry['grade_id'] ?? null,
                    'remark' => $entry['remark'] ?? null,
                    'created_by' => auth()->id(),
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Skill assessment marks saved successfully.']);
    }
    // End Method

}
