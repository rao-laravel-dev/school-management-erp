<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\StaffPasswordUpdateRequest;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\ClassTimetable;
use App\Models\Enrollment;
use App\Models\ExamSchedule;
use App\Models\HomeWork;
use App\Models\Lesson;
use App\Models\SalarySlip;
use App\Models\StaffAttendance;
use App\Models\StaffLeaveApplication;
use App\Models\Topic;
use App\Services\ImageService;
use App\Services\StaffSelfProfileService;
use App\Services\TimetableDayStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherProfileController extends Controller
{
    public function TeacherManage()
    {
        // Teacher role hai magar profile link nahi: 404 nahi, dashboard empty states ke saath khulta hai
        $teacher = Auth::user()->teacher;
        $profileMissing = !$teacher;
        $userId = Auth::id();
        $sessionId = $this->getActiveSessionId();
        $today = now()->toDateString();
        $todayName = now()->englishDayOfWeek;
        $monthStart = now()->startOfMonth()->toDateString();

        // 1. Aaj ke periods (class_timetables.teacher_id = teachers.id), time order. Profile nahi = koi query nahi
        $todayPeriods = $teacher
            ? ClassTimetable::with(['schoolClass', 'section', 'subject', 'room'])
                ->where('academic_year_id', $sessionId)->where('teacher_id', $teacher->id)
                ->where('day', $todayName)->orderBy('time_from')->get()
            : collect();
        // Calendar off event aaj ho to periods ki jagah off note (My Timetable wali rule); profile nahi = skip
        $offDayLabel = null;
        if ($teacher) {
            $dayStatus = TimetableDayStatus::forWeek($today, $todayPeriods->isNotEmpty() ? [$todayName] : [])['day_status'][$todayName];
            $offDayLabel = $dayStatus['status'] === 'not_scheduled' ? $dayStatus['label'] : null;
        }

        // 2. Class teacher sections (active session) + roster + aaj ki attendance: grouped queries
        $myClasses = $teacher
            ? $teacher->assignments()->with(['schoolClass', 'section'])
                ->where('academic_year_id', $sessionId)->orderBy('id')->get()
            : collect();
        $classKeys = $myClasses->map(fn($a) => $a->class_id . '-' . $a->section_id)->all();
        $rosterTotal = 0;
        $classCounts = ['Present' => 0, 'Late' => 0, 'Half Day' => 0, 'Absent' => 0, 'Leave' => 0];
        $classMarked = false;
        $classHoliday = false;
        if ($myClasses->isNotEmpty()) {
            $rosterTotal = Enrollment::whereIn('class_id', $myClasses->pluck('class_id'))
                ->whereIn('section_id', $myClasses->pluck('section_id'))
                ->where('academic_year_id', $sessionId)->where('enroll_status', 1)
                ->selectRaw('class_id, section_id, COUNT(*) AS c')->groupBy('class_id', 'section_id')->get()
                ->filter(fn($r) => in_array($r->class_id . '-' . $r->section_id, $classKeys, true))->sum('c');

            // Status: 0 Absent, 1 Present, 2 Late, 3 Half Day, 4 Leave (4 + remarks "Holiday" = Holiday, chart me nahi)
            $attRows = Attendance::whereIn('class_id', $myClasses->pluck('class_id'))
                ->whereIn('section_id', $myClasses->pluck('section_id'))
                ->where('attendance_date', $today)->toBase()
                ->selectRaw("attendances.class_id, attendances.section_id, attendances.status AS st, (attendances.status = 4 AND LOWER(TRIM(COALESCE(attendances.remarks, ''))) = 'holiday') AS is_holiday, COUNT(*) AS c")
                ->groupBy('attendances.class_id', 'attendances.section_id', 'st', 'is_holiday')->get()
                ->filter(fn($r) => in_array($r->class_id . '-' . $r->section_id, $classKeys, true));
            $classMarked = $attRows->isNotEmpty();
            $classHoliday = $classMarked && $attRows->every(fn($r) => (int) $r->is_holiday === 1);
            foreach ($attRows as $r) {
                $key = match ((int) $r->st) {
                    Attendance::STATUS_PRESENT  => 'Present',
                    Attendance::STATUS_LATE     => 'Late',
                    Attendance::STATUS_HALF_DAY => 'Half Day',
                    Attendance::STATUS_ABSENT   => 'Absent',
                    default => ((int) $r->is_holiday === 1 ? null : 'Leave'),
                };
                if ($key !== null) {
                    $classCounts[$key] += (int) $r->c;
                }
            }
        }

        // 3. Homework (created_by = users.id); submissions table nahi hai
        $homeworkThisMonth = HomeWork::where('created_by', $userId)
            ->whereBetween('homework_date', [$monthStart, now()->endOfMonth()->toDateString()])->count();
        $recentHomework = HomeWork::with(['class', 'section', 'subject'])->where('created_by', $userId)
            ->orderByDesc('homework_date')->orderByDesc('id')->limit(5)->get();

        // 4. Apni staff attendance is mahine (My Attendance Report wali % rule)
        $staffAtt = StaffAttendance::where('user_id', $userId)->whereBetween('date', [$monthStart, $today])
            ->selectRaw('status, COUNT(*) AS c')->groupBy('status')->pluck('c', 'status');
        $myAtt = [
            'present'  => (int) ($staffAtt['present'] ?? 0),
            'half_day' => (int) ($staffAtt['half_day'] ?? 0),
            'marked'   => (int) $staffAtt->sum(),
        ];
        $myAtt['percent'] = $myAtt['marked'] > 0
            ? round((($myAtt['present'] + 0.5 * $myAtt['half_day']) / $myAtt['marked']) * 100, 1) : null;

        // 5. Syllabus progress: timetable ke subjects (My Syllabus Status section 2), ek grouped topics query
        $combos = $teacher
            ? ClassTimetable::with(['schoolClass', 'section', 'subject'])
                ->where('academic_year_id', $sessionId)->where('teacher_id', $teacher->id)->get()
                ->unique(fn($r) => $r->school_class_id . '-' . $r->section_id . '-' . $r->subject_id)
                ->filter(fn($r) => $r->schoolClass && $r->section && $r->subject)
            : collect();
        $topicRows = $combos->isEmpty() ? collect() : Topic::join('lessons', 'lessons.id', '=', 'topics.lesson_id')
            ->whereIn('lessons.class_id', $combos->pluck('school_class_id'))
            ->whereIn('lessons.section_id', $combos->pluck('section_id'))
            ->whereIn('lessons.subject_id', $combos->pluck('subject_id'))
            ->toBase()
            ->selectRaw("CONCAT(lessons.class_id, '-', lessons.section_id, '-', lessons.subject_id) AS k, COUNT(*) AS total, COALESCE(SUM(topics.is_completed = 1), 0) AS done")
            ->groupBy('k')->get()->keyBy('k');
        $syllabus = $combos->map(function ($r) use ($topicRows) {
            $t = $topicRows->get($r->school_class_id . '-' . $r->section_id . '-' . $r->subject_id);
            $total = $t ? (int) $t->total : 0;
            $done = $t ? (int) $t->done : 0;
            return [
                'label'   => $r->schoolClass->name . ' - ' . $r->section->name . ' · ' . $r->subject->name,
                'total'   => $total,
                'done'    => $done,
                'percent' => $total > 0 ? round($done / $total * 100) : 0,
            ];
        })->sortBy('label')->values();

        // 6. Apni leave requests: last 3
        $recentLeaves = StaffLeaveApplication::where('user_id', $userId)
            ->orderByDesc('apply_date')->orderByDesc('id')->limit(3)->get();

        return view('teacher.dashboard', compact(
            'profileMissing', 'todayPeriods', 'offDayLabel',
            'myClasses', 'rosterTotal', 'classCounts', 'classMarked', 'classHoliday',
            'homeworkThisMonth', 'recentHomework', 'myAtt', 'syllabus', 'recentLeaves'
        ));
    }
    // End Method
    // Logout
    public function TeacherLogout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/teacher/login');
    }
    // End Method
    // Login Page
    public function TeacherLogin()
    {
        if (Auth::check()) {
            return redirect()->route('teacher.dashboard');
        }
        return view('teacher.teacher_login');
    }
    // End Method
    // Profile View
    public function TeacherProfile()
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            abort(404, 'Teacher profile not found.');
        }
        return view('teacher.teacher_profile', compact('teacher'));
    }
    // End Method
    // Profile Update
    public function TeacherProfileUpdate(ProfileUpdateRequest $request, ImageService $imageService)
    {
        $user = Auth::user();
        $teacher = $user->teacher()->firstOrFail();
        $user->update([
            'name'    => $request->profileName,
            'phone'   => $request->profilePhone,
            'address' => $request->address,
            'gender'  => $request->gender,
        ]);
        if ($request->hasFile('photo')) {
            if ($teacher->photo) {
                $imageService->delete($teacher->photo, 'teacher_images');
            }
            $teacher->update([
                'photo' => $imageService->upload($request->file('photo'), 'teacher_images', 300, 300),
            ]);
        }
        return redirect()->back()->with('toastr-success', 'Teacher Profile Updated Successfully');
    }
    // End Method

    // Change Password Page
    public function TeacherChangePassword()
    {
        return view('staff_profile.change_password', [
            'updateRoute' => route('teacher.password.update'),
        ]);
    }
    // End Method

    // Password Update
    public function TeacherPasswordUpdate(StaffPasswordUpdateRequest $request, StaffSelfProfileService $service)
    {
        $service->updatePassword(Auth::user(), $request->validated()['password']);

        // Password change ke baad logout (TeacherLogout jaisa) aur login page par redirect
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/teacher/login')->with([
            'message' => 'Password changed successfully. Please login with your new password.',
            'alert-type' => 'success'
        ]);
    }
    // End Method

    // My Exam Schedule (read-only, scoped to this teacher's assigned classes only)
    public function myExamSchedule()
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            abort(404, 'Teacher profile not found.');
        }

        $classIds = $teacher->assignments()->pluck('class_id')->unique();

        $schedules = ExamSchedule::whereIn('class_id', $classIds)
            ->with(['exam.examType', 'schoolClass', 'subject'])
            ->orderBy('date')
            ->get();

        return view('teacher.exam_schedule.index', compact('schedules'));
    }
    // End Method

    public function mySyllabusStatus()
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            abort(404, 'Teacher profile not found.');
        }
        // $userId  = Auth::id(); // ClassTimetable.teacher_id -> users.id

        // ============================================
        // SECTION 1: Homeroom class overview (poori class ka syllabus)
        // ============================================
        // teacher_assignments ki har row = Class Teacher (class_subject_id column ab nahi)
        $homeroomAssignments = $teacher->assignments()
            ->with(['schoolClass', 'section'])
            ->get();

        $homeroomData = [];
        foreach ($homeroomAssignments as $assignment) {
            $classSubjects = ClassSubject::with('subject')
                ->where('class_id', $assignment->class_id)
                ->get();

            foreach ($classSubjects as $cs) {
                if (!$cs->subject) continue;

                $homeroomData[] = $this->buildSyllabusRow(
                    $assignment->class_id,
                    $assignment->section_id,
                    $cs->subject_id,
                    $cs->subject->name,
                    $assignment->schoolClass->name,
                    $assignment->section->name
                );
            }
        }

        // ============================================
        // SECTION 2: Subjects jo wo khud padhati hai (Timetable se)
        // ============================================
        $combos = ClassTimetable::with(['schoolClass', 'section', 'subject'])
            ->where('academic_year_id', $this->getActiveSessionId())
            ->where('teacher_id', $teacher->id)
            ->get()
            ->unique(function ($row) {
                return $row->school_class_id . '-' . $row->section_id . '-' . $row->subject_id;
            });

        $teachingData = [];
        foreach ($combos as $row) {
            if (!$row->subject || !$row->schoolClass || !$row->section) continue;

            $teachingData[] = $this->buildSyllabusRow(
                $row->school_class_id,
                $row->section_id,
                $row->subject_id,
                $row->subject->name,
                $row->schoolClass->name,
                $row->section->name
            );
        }

        return view('teacher.syllabus_status.index', compact('homeroomData', 'teachingData'));
    }
    // End Method

    private function buildSyllabusRow($classId, $sectionId, $subjectId, $subjectName, $className, $sectionName)
    {
        $lessons = Lesson::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('subject_id', $subjectId)
            ->with('topics')
            ->get();

        $totalTopics = $lessons->pluck('topics')->flatten()->count();
        $completedTopics = $lessons->pluck('topics')->flatten()
            ->where('is_completed', 1)->count();

        $percentage = $totalTopics > 0 ? round(($completedTopics / $totalTopics) * 100) : 0;

        return [
            'subject'    => $subjectName,
            'class'      => $className,
            'section'    => $sectionName,
            'lessons'    => $lessons,
            'percentage' => $percentage,
        ];
    }
    // End Method

    // My Salary Slips (strictly own records — never other staff's)
    public function mySalarySlips()
    {
        $slips = SalarySlip::where('user_id', Auth::id())
            ->orderByDesc('month')
            ->get();

        return view('teacher.salary.index', compact('slips'));
    }
    // End Method

    // My Timetable (scoped to this teacher's assigned classes or teacher timetable)
    public function myTimetable()
    {
        $ownTeacherId = optional(Auth::user()->teacher)->id;

        return view('teacher.timetable.index', compact('ownTeacherId'));
    }
    // End Method
}
