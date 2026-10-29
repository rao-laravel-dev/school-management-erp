<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\StaffPasswordUpdateRequest;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\ClassTimetable;
use App\Models\Room;
use App\Services\TimetableDayStatus;
use Carbon\Carbon;
use App\Models\Enrollment;
use App\Models\ExamSchedule;
use App\Models\HomeWork;
use App\Models\MarkSheet;
use App\Models\Result;
use App\Models\Subject;
use App\Models\User;
use App\Services\ImageService;
use App\Services\StaffSelfProfileService;
use App\Services\StudentClassSchedule;
use App\Services\StudentHomework;
use App\Services\StudentResults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentProfileController extends Controller
{


    public function StudentManage()
    {
        /** @var User $user */
        // 1. Logged in user ki profile fetch karein
        $user = Auth::user();
        $student = $user->studentProfile()->with('parent')->first();

        // 2. Security Check: Agar student profile nahi milti
        if (!$student) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }

        // 🔥 FIX: Session mein Student ID set karein 
        session(['active_student_id' => $student->id]);

        // 🌟 NAYA CODE: Enrollment aur Assigned Teacher ka data topbar ke liye fetch karna
        $assignedTeacher = null;
        // Latest active enrollment (same rule as header composer + results)
        $enrollment = DB::table('enrollments')->where('student_id', $student->id)->where('enroll_status', 1)->orderByDesc('id')->first();

        if ($enrollment && !empty($enrollment->class_id) && !empty($enrollment->section_id)) {
            $assignedTeacher = DB::table('teacher_assignments')
                ->join('teachers', 'teacher_assignments.teacher_id', '=', 'teachers.id')
                ->join('school_class', 'teacher_assignments.class_id', '=', 'school_class.id')
                ->join('sections', 'teacher_assignments.section_id', '=', 'sections.id')
                ->where('teacher_assignments.class_id', $enrollment->class_id)
                ->where('teacher_assignments.section_id', $enrollment->section_id)
                ->where('teacher_assignments.academic_year_id', $enrollment->academic_year_id)
                ->select(
                    'teachers.first_name',
                    'teachers.last_name',
                    'teachers.photo',
                    'school_class.name as class_name',
                    'sections.name as section_name'
                )
                ->first();
        }

        // 3. Dashboard data: sirf apna data, har block apni permission ke saath; permission/enrollment na ho to safe defaults
        $activeEnrollment = StudentHomework::activeEnrollment($student->id);
        $today = now()->toDateString();
        $todayName = now()->englishDayOfWeek;
        $monthStart = now()->startOfMonth()->toDateString();
        $canAtt = $user->can('access-student-attendance');
        $canTimetable = $user->can('access-student-timetable');
        $canHomework = $user->can('access-student-homework');
        $canAcademics = $user->can('access-student-academics');

        // 3a. Attendance (is mahine): View Attendance wali rule, 0 Absent, 1 Present, 2 Late, 3 Half Day, 4 Leave (4 + remarks "Holiday" ginti me nahi)
        $monthCounts = ['Present' => 0, 'Late' => 0, 'Half Day' => 0, 'Absent' => 0, 'Leave' => 0];
        if ($canAtt && $activeEnrollment) {
            $attRows = Attendance::where('enrollment_id', $activeEnrollment->id)
                ->whereBetween('attendance_date', [$monthStart, $today])->toBase()
                ->selectRaw("attendances.status AS st, (attendances.status = 4 AND LOWER(TRIM(COALESCE(attendances.remarks, ''))) = 'holiday') AS is_holiday, COUNT(*) AS c")
                ->groupBy('st', 'is_holiday')->get();
            foreach ($attRows as $r) {
                $key = match ((int) $r->st) {
                    Attendance::STATUS_PRESENT  => 'Present',
                    Attendance::STATUS_LATE     => 'Late',
                    Attendance::STATUS_HALF_DAY => 'Half Day',
                    Attendance::STATUS_ABSENT   => 'Absent',
                    default => ((int) $r->is_holiday === 1 ? null : 'Leave'),
                };
                if ($key !== null) {
                    $monthCounts[$key] += (int) $r->c;
                }
            }
        }
        // X = P + L + H, Y = X + A (Leave ginti me nahi), % = (P + L + 0.5 x H) / Y
        $monthDaysPresent = $monthCounts['Present'] + $monthCounts['Late'] + $monthCounts['Half Day'];
        $monthDaysCounted = $monthDaysPresent + $monthCounts['Absent'];
        $monthAttPercent = $monthDaysCounted > 0
            ? round((($monthCounts['Present'] + $monthCounts['Late'] + 0.5 * $monthCounts['Half Day']) / $monthDaysCounted) * 100, 1) : null;

        // 3b. My Attendance (Session): enrollment ke academic year ke mahine, Present (P + L + H) vs Absent, ek grouped query
        $sessionLabels = [];
        $sessionPresent = [];
        $sessionAbsent = [];
        $sessionP = 0; $sessionL = 0; $sessionH = 0; $sessionA = 0;
        $year = $activeEnrollment ? $activeEnrollment->academicYear : null;
        // Enrollment ki apni year row + class ki dates (class group override ho to us ki)
        [$yearStart, $yearEnd] = $year ? $year->datesForClass((int) $activeEnrollment->class_id) : [null, null];
        if ($canAtt && $year && $yearStart && $yearEnd) {
            $sFrom = Carbon::parse($yearStart)->toDateString();
            // end_date mahine ki 1 tareekh ho to pichla mahina aakhri mahina; aaj ke baad ka data nahi
            $sEnd = Carbon::parse($yearEnd);
            if ($sEnd->day === 1) {
                $sEnd = $sEnd->copy()->subDay();
            }
            $sTo = min(max($sEnd->toDateString(), $sFrom), $today); // same $sTo query aur month loop dono me
            if ($sTo >= $sFrom) {
                $sessionRows = Attendance::where('enrollment_id', $activeEnrollment->id)
                    ->whereBetween('attendance_date', [$sFrom, $sTo])->toBase()
                    ->selectRaw("DATE_FORMAT(attendances.attendance_date, '%Y-%m') AS ym, attendances.status AS st, COUNT(*) AS c")
                    ->groupBy('ym', 'st')->get();
                $presentByMonth = [];
                $absentByMonth = [];
                foreach ($sessionRows as $r) {
                    $st = (int) $r->st;
                    if (in_array($st, [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE, Attendance::STATUS_HALF_DAY], true)) {
                        $presentByMonth[$r->ym] = (isset($presentByMonth[$r->ym]) ? $presentByMonth[$r->ym] : 0) + (int) $r->c;
                        if ($st === Attendance::STATUS_PRESENT) { $sessionP += (int) $r->c; }
                        if ($st === Attendance::STATUS_LATE) { $sessionL += (int) $r->c; }
                        if ($st === Attendance::STATUS_HALF_DAY) { $sessionH += (int) $r->c; }
                    } elseif ($st === Attendance::STATUS_ABSENT) {
                        $absentByMonth[$r->ym] = (isset($absentByMonth[$r->ym]) ? $absentByMonth[$r->ym] : 0) + (int) $r->c;
                        $sessionA += (int) $r->c;
                    }
                }
                $sLast = Carbon::parse($sTo);
                for ($m = Carbon::parse($sFrom)->startOfMonth(); $m->lte($sLast); $m->addMonthNoOverflow()) {
                    $ym = $m->format('Y-m');
                    $sessionLabels[] = $m->format('M y');
                    $sessionPresent[] = isset($presentByMonth[$ym]) ? $presentByMonth[$ym] : 0;
                    $sessionAbsent[] = isset($absentByMonth[$ym]) ? $absentByMonth[$ym] : 0;
                }
            }
        }
        $sessionPresentTotal = array_sum($sessionPresent);
        $sessionAbsentTotal = array_sum($sessionAbsent);
        $sessionCounted = $sessionPresentTotal + $sessionAbsentTotal;
        $sessionAttPercent = $sessionCounted > 0
            ? round((($sessionP + $sessionL + 0.5 * $sessionH) / $sessionCounted) * 100, 1) : 0;

        // 3c. Aaj ke periods (class_timetables, apni class-section), time order; room class-section ka hota hai (StudentClassSchedule jaisa)
        $todayPeriods = collect();
        $roomNo = null;
        $offDayLabel = null;
        if ($canTimetable && $activeEnrollment) {
            $todayPeriods = ClassTimetable::with(['subject', 'teacher'])
                ->where('academic_year_id', $activeEnrollment->academic_year_id)
                ->where('school_class_id', $activeEnrollment->class_id)
                ->where('section_id', $activeEnrollment->section_id)
                ->where('day', $todayName)->orderBy('time_from')->get();
            $roomNo = Room::where('school_class_id', $activeEnrollment->class_id)
                ->where('section_id', $activeEnrollment->section_id)->value('room_no');
            // Calendar off event aaj ho to periods ki jagah off note (teacher dashboard wali rule)
            $dayStatus = TimetableDayStatus::forWeek($today, $todayPeriods->isNotEmpty() ? [$todayName] : [])['day_status'][$todayName];
            $offDayLabel = $dayStatus['status'] === 'not_scheduled' ? $dayStatus['label'] : null;
        }
        $periodsDone = $offDayLabel ? 0 : $todayPeriods->filter(fn($p) => $p->time_to && Carbon::parse($p->time_to)->lte(now()))->count();

        // 3d. Homework (StudentHomework, My Homework jaisa): pending/past due, 12 mahine sparkline, agle 5 due
        $pendingHomework = 0;
        $pastDueHomework = 0;
        $upcomingHomework = collect();
        $homeworkMonthLabels = [];
        $homeworkSeries = [];
        if ($canHomework) {
            $hw = StudentHomework::forStudent($student->id);
            $pendingHomework = $hw['pendingCount'];
            $pastDueHomework = $hw['pastDueCount'];
            $upcomingHomework = $hw['homeworks']->filter(fn($h) => $h->due_date->gte(now()->startOfDay()))->sortBy('due_date')->take(5)->values();
            $sparkStart = now()->startOfMonth()->subMonthsNoOverflow(11);
            $byMonth = $hw['homeworks']->groupBy(fn($h) => $h->homework_date->format('Y-m'))->map->count();
            for ($i = 0; $i < 12; $i++) {
                $m = $sparkStart->copy()->addMonthsNoOverflow($i);
                $homeworkMonthLabels[] = $m->format('M y');
                $homeworkSeries[] = (int) ($byMonth->get($m->format('Y-m')) ?: 0);
            }
        }

        // 3e. Published results (StudentResults, My Results jaisa): latest + last 3
        $recentResults = $canAcademics ? StudentResults::forStudent($student->id)['results']->take(3)->values() : collect();

        // 4. Data return karein (compact mein 'assignedTeacher' ko add kar diya hai)
        return view('student.dashboard', compact(
            'student',
            'assignedTeacher', // 🔥 Yeh variable ab view aur master/header layout ko milega
            'monthCounts', 'monthDaysPresent', 'monthDaysCounted', 'monthAttPercent',
            'sessionLabels', 'sessionPresent', 'sessionAbsent',
            'sessionPresentTotal', 'sessionAbsentTotal', 'sessionAttPercent',
            'todayPeriods', 'roomNo', 'offDayLabel', 'periodsDone',
            'pendingHomework', 'pastDueHomework', 'upcomingHomework', 'homeworkMonthLabels', 'homeworkSeries',
            'recentResults'
        ));
    }

    // Logout
    public function StudentLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('students.login'); // Named route use kiya
    }

    // Login Page
    public function StudentLogin()
    {
        if (Auth::check()) {
            return redirect()->route('student.dashboard');
        }

        return view('student.student_login');
    }

    // Profile View
    public function StudentProfile()
    {
        $id = Auth::id();
        $profileData = User::find($id);

        return view('student.student_profile', compact('profileData'));
    }

    // Profile Update
    public function StudentProfileUpdate(ProfileUpdateRequest $request, ImageService $imageService)
    {
        $user = User::find(Auth::id());

        $data = [
            'name'    => $request->profileName,
            'phone'   => $request->profilePhone,
            'address' => $request->address,
            'gender'  => $request->gender,
        ];

        if ($request->hasFile('photo')) {
            // admin ki upload ki hui photo sirf students.photo mein hoti hai, isliye pehle wahi dekho
            $oldPhoto = $user->studentProfile ? $user->studentProfile->photo : $user->photo;

            $data['photo'] = $imageService->upload(
                $request->file('photo'),
                'uploads/students', // Student::photo_url yahi folder dekhta hai
                300,
                300
            );
        }

        DB::transaction(function () use ($user, $data) { // users + students dono ek sath save
            $user->update($data);

            if (isset($data['photo']) && $user->studentProfile) {
                $user->studentProfile->update(['photo' => $data['photo']]);
            }
        });

        // Purani photo naya save hone ke BAAD delete
        if (!empty($oldPhoto)) {
            $imageService->delete($oldPhoto, 'uploads/students');
        }

        return redirect()->back()->with([
            'message' => 'Student Profile Updated Successfully',
            'alert-type' => 'success'
        ]);
    }
    // End Method

    public function myTeachers()
    {
        // 1. Logged-in user ke zariye student profile record layein
        $studentProfile = Auth::user()->studentProfile()->first();

        if (!$studentProfile) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }

        // 2. Enrollment table se student ki current class_id aur section_id nikalna
        // Latest active enrollment (same rule as dashboard + header composer)
        $enrollment = DB::table('enrollments')
            ->where('student_id', $studentProfile->id)
            ->where('enroll_status', 1)
            ->orderByDesc('id')
            ->first();

        // Agar student enrolled nahi hai kisi class mein
        if (!$enrollment || empty($enrollment->class_id) || empty($enrollment->section_id)) {
            $assignedTeacher = null;
            return view('student.teacher.myteacher', compact('studentProfile', 'assignedTeacher'));
        }

        // 3. Joins optimized: teacher_assignments, teachers, school_class, aur sections
        $assignedTeacher = DB::table('teacher_assignments')
            ->join('teachers', 'teacher_assignments.teacher_id', '=', 'teachers.id')
            ->join('school_class', 'teacher_assignments.class_id', '=', 'school_class.id') // Singlular table 'school_class'
            ->join('sections', 'teacher_assignments.section_id', '=', 'sections.id')
            ->where('teacher_assignments.class_id', $enrollment->class_id)
            ->where('teacher_assignments.section_id', $enrollment->section_id)
            ->where('teacher_assignments.academic_year_id', $enrollment->academic_year_id)
            ->select(
                'teachers.first_name',
                'teachers.last_name',
                'teachers.email',
                'teachers.phone',
                'teachers.photo',
                'school_class.name as class_name',  // school_class table se 'name' column uthaya
                'sections.name as section_name'     // sections table se 'name' column uthaya
            )
            ->first();

        return view('student.teacher.myteacher', compact('studentProfile', 'assignedTeacher'));
    }
    // End Method

    public function viewMySubjects()
    {
        $studentId = session('active_student_id');
        $activeSessionId = $this->getActiveSessionId();

        // 1. Enrollment fetch karein (group_id ke saath)
        $enrollment = Enrollment::where('student_id', $studentId)
            ->where(fn($q) => \App\Models\AcademicYear::whereCurrentOrGap($q)) // gap class = pichle session ki enrollment
            ->first();

        if (!$enrollment) {
            return redirect()->back()->with('error', 'Enrollment record not found.');
        }

        // 2. Query: Class aur Group ke hisaab se subjects laayein
        // Hum Subject model ko use kar rahe hain aur filter laga rahe hain
        $subjects = Subject::whereHas('school_class', function ($query) use ($enrollment) {
            $query->where('class_id', $enrollment->class_id);
        })
            ->with(['school_class' => function ($query) use ($enrollment) {
                $query->where('class_id', $enrollment->class_id);
            }])
            ->get();

        return view('student.subjects.view_mysubjects', compact('subjects', 'enrollment'));
    }
    // End Method
    public function resultIndex()
{
    // Query lives in StudentResults (shared with the parent portal)
    return view('student.result.index', StudentResults::forStudent(session('active_student_id')) + [
        'resultsTitle'      => 'My Results',
        'dashboardUrl'      => route('student.dashboard'),
        'resultsIndexUrl'   => route('student.results.index'),
        'detailsRoute'      => 'student.results.details',
        'detailsParams'     => [], // parent adds the child id first
    ]);
}
// End Method

    public function resultDetails($resultId)
    {
        return view('student.result.details', StudentResults::details(session('active_student_id'), $resultId) + [
            'dashboardUrl'      => route('student.dashboard'),
            'resultsIndexUrl'   => route('student.results.index'),
        ]);
    }
    // End Method

    // Change Password page (own account only)
    public function StudentChangePassword()
    {
        return view('staff_profile.change_password', [
            'updateRoute' => route('student.password.update'),
        ]);
    }
    // End Method

    // Password Update (own account only), then logout like the staff pages
    public function StudentPasswordUpdate(StaffPasswordUpdateRequest $request, StaffSelfProfileService $service)
    {
        $service->updatePassword(Auth::user(), $request->validated()['password']);

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('students.login')->with([
            'message' => 'Password changed successfully. Please login with your new password.',
            'alert-type' => 'success'
        ]);
    }
    // End Method

    // Class Schedule page (read-only, own class/section only)
    public function myClassSchedule()
    {
        return view('student.class_schedule.index', [
            'scheduleDataUrl'  => route('student.class_schedule.get_data'),
            'scheduleStudentId' => null,
            'scheduleTitle'    => 'My Class Schedule',
            'dashboardUrl'     => route('student.dashboard'),
        ]);
    }
    // End Method

    // AJAX: weekly timetable of the logged-in student's own class/section (no ids from request)
    public function myClassScheduleData(Request $request)
    {
        $request->validate(['week' => 'nullable|date']);

        $student = Auth::user()->studentProfile;

        if (!$student) {
            return response()->json(['timetable' => (object) []]);
        }

        return response()->json(StudentClassSchedule::forStudent($student->id, $request->week));
    }
    // End Method

    // My Homework page (read-only): homework of own class/section/subjects only, no ids from request
    public function myHomework()
    {
        $student = Auth::user()->studentProfile;
        abort_if(!$student, 404);

        // Query shared with the parent child Homework page (latest active enrollment, class subjects, academic year)
        return view('student.homework.index', StudentHomework::forStudent($student->id) + [
            'homeworkTitle' => 'My Homework',
            'dashboardUrl'  => route('student.dashboard'),
        ]);
    }
    // End Method

    // Academic Records page (read-only): admission details, enrollment history, published results
    public function academicRecords()
    {
        $student = Auth::user()->studentProfile;
        abort_if(!$student, 404);

        $student->load(['category', 'house']);

        $enrollments = Enrollment::with(['academicYear', 'schoolClass', 'section', 'group'])
            ->where('student_id', $student->id)
            ->orderByDesc('id')
            ->get();

        $results = Result::with(['exam.examType', 'grade'])
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->whereHas('exam', function ($q) {
                $q->where('publish_result', 1);
            })
            ->orderByDesc('created_at')
            ->get();

        // Result details page only opens for the active enrollment
        $activeEnrollmentId = optional($enrollments->firstWhere('enroll_status', 1))->id;

        return view('student.academic_records.index', compact('student', 'enrollments', 'results', 'activeEnrollmentId'));
    }
    // End Method

}
