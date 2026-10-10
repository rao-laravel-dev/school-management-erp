<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\MarkSheet;
use App\Models\PrintReportCard;
use App\Models\ReportCardTemplate;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\SiteSetting;
use App\Models\StudentSkillMark;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrintReportCardController extends Controller
{
    /**
     * Criteria selection page: Exam / Class / Section / Report Card Template
     */
    public function index()
    {
        $exams     = Exam::orderBy('name')->get();
        $classes   = SchoolClass::where('status', 1)->orderBy('name')->get();
        $templates = ReportCardTemplate::orderBy('template_name')->get();
 
        return view('admin.print_report_card.index', compact('exams', 'classes', 'templates'));
    }
    // End Method
 
    /**
     * AJAX: sections for a class (same pattern as Print Marksheet)
     */
    public function getSectionsByClass($classId)
    {
        $class = SchoolClass::findOrFail($classId);
 
        $sections = $class->sections()
            ->where('school_class_section.status', 1)
            ->get();
 
        return response()->json($sections);
    }
    // End Method
 
    /**
     * AJAX: enrollments that have a Result for the selected exam, ordered by roll_no
     * (identical filter logic to PrintMarksheetController::getStudents)
     */
    public function getStudents(Request $request)
    {
        $request->validate([
            'exam_id'    => 'required|exists:exams,id',
            'class_id'   => 'required|exists:school_class,id',
            'section_id' => 'required|exists:sections,id',
        ]);
 
        $enrollments = Enrollment::with('student')
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('enroll_status', 1)
            ->whereHas('student') // soft-deleted student list mein nahi
            ->whereHas('result', function ($q) use ($request) {
                $q->where('exam_id', $request->exam_id);
            })
            ->get()
            ->sortBy(fn ($e) => $e->roll_no)
            ->values();
 
        return response()->json($enrollments);
    }
    // End Method
 
    /**
     * Resolve the attendance-range END date for a given exam:
     * latest exam_schedules.date for that exam+class (last paper date).
     * Falls back to today if no schedule rows exist yet.
     */
    protected function resolveAttendanceEndDate(Exam $exam, int $classId): Carbon
    {
        $lastScheduleDate = ExamSchedule::where('exam_id', $exam->id)
            ->where('class_id', $classId)
            ->max('date');
 
        return $lastScheduleDate ? Carbon::parse($lastScheduleDate) : now();
    }
    // End Method
 
    /**
     * Replace [bracket] placeholders in template body_text/footer_text with
     * actual student data — same convention as Marksheet Template placeholders.
     * Assumption: student fields (father_name, mother_name, admission_no, dob)
     * live on the Student model via $enrollment->student — confirm if different.
     */
    protected function renderTemplateText(?string $text, Enrollment $enrollment, Exam $exam): string
    {
        if (!$text) {
            return '';
        }
 
        $student = $enrollment->student;
 
        $replacements = [
            '[name]'          => $student->name ?? '',
            '[father_name]'   => $student->father_name ?? '',
            '[mother_name]'   => $student->mother_name ?? '',
            '[admission_no]'  => $student->admission_no ?? '',
            '[roll_number]'   => $enrollment->roll_no ?? '',
            '[class]'         => $enrollment->schoolClass->name ?? '',
            '[section]'       => $enrollment->section->name ?? '',
            '[exam_name]'     => $exam->name ?? '',
            '[dob]'           => $student->dob ?? '',
        ];
 
        return strtr($text, $replacements);
    }
    // End Method
 
    /**
     * Build + issue report cards for selected enrollments, log to print_report_cards,
     * return the printable view (same shape as PrintMarksheetController::generate)
     */
    public function generate(Request $request)
    {
        $request->validate([
            'exam_id'          => 'required|exists:exams,id',
            'class_id'         => 'required|exists:school_class,id',
            'template_id'      => 'required|exists:report_card_templates,id',
            'enrollment_ids'   => 'required|array|min:1',
            'enrollment_ids.*' => 'exists:enrollments,id',
        ]);
 
        $exam         = Exam::with('academicYear')->findOrFail($request->exam_id);
        $template     = ReportCardTemplate::findOrFail($request->template_id);
        $academicYear = $exam->academicYear;
 
        // School name, logo, address, phone, email — always live from Site Settings,
        // never stored on the template itself
        $siteSetting = SiteSetting::first();
 
        $attendanceFrom = Carbon::parse($academicYear->start_date);
        $attendanceTo   = $this->resolveAttendanceEndDate($exam, (int) $request->class_id);
 
        $sheets = collect();
 
        foreach ($request->enrollment_ids as $enrollmentId) {
 
            $enrollment = Enrollment::with(['student', 'schoolClass', 'section'])
                ->findOrFail($enrollmentId);
 
            // Marks & Grade -> ALWAYS current exam only (no cumulative logic here)
            $result = Result::with('grade')
                ->where('enrollment_id', $enrollmentId)
                ->where('exam_id', $exam->id)
                ->first();
 
            $subjectMarks = MarkSheet::where('enrollment_id', $enrollmentId)
                ->where('exam_id', $exam->id)
                ->get();
 
            // Attendance -> cumulative, session start to this exam's date
            $attendance = Attendance::where('enrollment_id', $enrollmentId)
                ->whereBetween('attendance_date', [
                    $attendanceFrom->toDateString(),
                    $attendanceTo->toDateString(),
                ])
                ->get();
 
            $totalDays   = $attendance->count();
            $presentDays = $attendance->whereIn('status', [1, 2])->count(); // Present + Late
            $attendancePercentage = $totalDays > 0
                ? round(($presentDays / $totalDays) * 100, 2)
                : 0;
 
            // Co-curricular / Behavioral -> current exam only (same as Marks & Grade)
            $skillMarks = StudentSkillMark::with(['area.category', 'grade'])
                ->where('enrollment_id', $enrollmentId)
                ->where('exam_id', $exam->id)
                ->get();
 
            $sheets->push([
                'enrollment' => $enrollment,
                'result'     => $result,
                'subjectMarks' => $subjectMarks,
                'attendance' => [
                    'from'       => $attendanceFrom,
                    'to'         => $attendanceTo,
                    'total_days' => $totalDays,
                    'present'    => $presentDays,
                    'percentage' => $attendancePercentage,
                ],
                'skillMarks' => $skillMarks,
                'bodyText'   => $this->renderTemplateText($template->body_text, $enrollment, $exam),
                'footerText' => $this->renderTemplateText($template->footer_text, $enrollment, $exam),
            ]);
 
            PrintReportCard::create([
                'enrollment_id' => $enrollmentId,
                'exam_id'       => $exam->id,
                'template_id'   => $template->id,
                'issued_by'     => Auth::id(),
                'issued_at'     => now(),
            ]);
        }
 
        return view('admin.print_report_card.print', compact('sheets', 'exam', 'template', 'siteSetting'));
    }
    // End Method
}
