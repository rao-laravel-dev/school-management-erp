<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\MarkSheet;
use App\Models\MarksGrade;
use App\Models\MarksheetTemplate;
use App\Models\PrintMarksheet;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\SiteSetting;
use App\Services\PrintDesignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrintMarksheetController extends Controller
{
    /**
     * Selection page: pick Exam, Class, Section, Template.
     */
    public function index()
    {
        $exams     = Exam::with('examType')->latest()->get();
        $classes   = SchoolClass::where('status', 1)->orderBy('numeric_name')->get();
        $templates = MarksheetTemplate::orderBy('template_name')->get();

        return view('admin.print_marksheet.index', compact('exams', 'classes', 'templates'));
    }

    /**
     * AJAX: sections belonging to a class — same school_class_section pivot
     * pattern already used in MarkSheetController/ResultController/ExamScheduleController.
     */
    public function getSectionsByClass($classId)
    {
        $sections = DB::table('school_class_section')
            ->join('sections', 'school_class_section.section_id', '=', 'sections.id')
            ->where('school_class_section.school_class_id', $classId)
            ->select('sections.id', 'sections.name')
            ->get();

        return response()->json($sections);
    }

    /**
     * AJAX: students (enrollments) for Exam + Class + Section that already have a
     * calculated result, so the admin only picks students who can actually be printed.
     */
    public function getStudents($examId, $classId, $sectionId)
    {
        // Avoid depending on an Enrollment->results relation that may not exist —
        // pull the enrollment_ids directly from Result, same as ResultController::index() does.
        $resultEnrollmentIds = Result::where('exam_id', $examId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->pluck('enrollment_id');

        $examYearId = Exam::whereKey($examId)->value('academic_year_id');

        $enrollments = Enrollment::with('student')
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->when($examYearId, function ($q) use ($examYearId) {
                $q->where('academic_year_id', $examYearId)
                  ->whereIn('enroll_status', [1, 2, 3, 5, Enrollment::STATUS_LEFT]); // Active + Promoted / Detained / Passed Out + School Left (old: [1, 2, 3, 5])
            }, function ($q) {
                $q->where('enroll_status', 1); // exam ka saal nahi: purana behaviour
            })
            ->whereIn('id', $resultEnrollmentIds)
            ->whereHas('student') // soft-deleted student list mein nahi
            ->orderBy('roll_no')
            ->get()
            ->map(function ($enrollment) {
                return [
                    'enrollment_id' => $enrollment->id,
                    'admission_no'  => $enrollment->student->admission_no ?? '',
                    'name'          => trim(($enrollment->student->first_name ?? '') . ' ' . ($enrollment->student->last_name ?? '')),
                    'roll_number'   => $enrollment->roll_no ?? '',
                ];
            });

        return response()->json($enrollments);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'exam_id'        => 'required|exists:exams,id',
            'class_id'       => 'required|exists:school_class,id',
            'section_id'     => 'required|exists:sections,id',
            'template_id'    => 'required|exists:marksheet_templates,id',
            'enrollment_ids' => 'required|array|min:1',
        ]);

        $exam        = Exam::findOrFail($request->exam_id);
        $template    = MarksheetTemplate::findOrFail($request->template_id);
        $siteSetting = SiteSetting::current();

        // Max/Passing marks per subject for this Exam+Class (same source ExamSchedule module already uses)
        $examSchedules = ExamSchedule::where('exam_id', $exam->id)
            ->where('class_id', $request->class_id)
            ->get()
            ->keyBy('subject_id');

        // Grade scale + class-section size only when a Modern/Formal extra needs them (Classic and old templates: no extra queries)
        $grades = ($template->show_subject_details || $template->show_grade_scale)
            ? MarksGrade::where('exam_id', $exam->id)->orderBy('min_percentage', 'desc')->get()
            : collect();
        $sizes = [];

        $sheets = collect($request->enrollment_ids)->map(function ($enrollmentId) use ($exam, $request, $examSchedules, $template, $grades, &$sizes) {
            $enrollment = Enrollment::with(['student.parent', 'schoolClass', 'section'])->whereHas('student')->findOrFail($enrollmentId); // soft-deleted student = 404 (500 nahi)
            $student    = $enrollment->student;

            $result = Result::with('grade')
                ->where('exam_id', $exam->id)
                ->where('enrollment_id', $enrollmentId)
                ->first();

            $subjectMarks = MarkSheet::with('subject')
                ->where('exam_id', $exam->id)
                ->where('enrollment_id', $enrollmentId)
                ->get()
                ->map(function ($mark) use ($examSchedules) {
                    $schedule = $examSchedules->get($mark->subject_id);
                    $mark->max_marks     = $schedule->max_marks ?? '-';
                    $mark->passing_marks = $schedule->passing_marks ?? '-';
                    // Ensure remark exists or fallback
                    $mark->remark        = $mark->remark ?? '-';
                    return $mark;
                });

            PrintMarksheet::create([
                'exam_id'       => $exam->id,
                'class_id'      => $request->class_id,
                'section_id'    => $request->section_id,
                'enrollment_id' => $enrollmentId,
                'template_id'   => $request->template_id,
                'printed_by'    => auth()->id(),
                'printed_at'    => now(),
            ]);

            // Replace [placeholder] tokens in body_text with real data for this student
            // old: '[father_name]' => $student->father_name, '[mother_name]' => $student->mother_name (students table mein ye columns nahi; ab parent profile se)
            $bodyText = PrintDesignService::marksheetBody($template->body_text, $student, $enrollment, $exam);

            // Position is "rank / students with a result in the same class-section for this exam"
            $totalStudents = null;
            if ($template->show_summary_boxes && $result) {
                $key = $result->class_id . '-' . $result->section_id;
                $sizes[$key] ??= Result::where('exam_id', $exam->id)->where('class_id', $result->class_id)->where('section_id', $result->section_id)->count();
                $totalStudents = $sizes[$key];
            }

            return [
                'enrollment' => $enrollment,
                'student'    => $student,
                'result'     => $result,
                'subjects'   => $subjectMarks,
                'body_text'  => $bodyText,
                'grades'     => $grades,
                'total_students' => $totalStudents,
            ];
        });

        return view('admin.print_marksheet.print', [
            'exam'        => $exam,
            'template'    => $template,
            'sheets'      => $sheets,
            'siteSetting' => $siteSetting,
            'branding'    => PrintDesignService::branding($siteSetting, $template),
            'isPreview'   => false,
        ]);
    }
}
