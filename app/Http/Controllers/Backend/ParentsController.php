<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\StaffPasswordUpdateRequest;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\ClassTimetable;
use App\Models\Enrollment;
use App\Models\Room;
use App\Models\User;
use App\Services\ImageService;
use App\Services\StaffSelfProfileService;
use App\Services\StudentClassSchedule;
use App\Services\StudentHomework;
use App\Services\StudentResults;
use App\Services\TimetableDayStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ParentsController extends Controller
{

    public function ParentManage(Request $request)
    {
        // Sidebar "no child selected" links use ?select=1; land on the clean URL so only Dashboard is highlighted
        if ($request->has('select')) {
            return redirect()->route('parent.dashboard');
        }

        $parent = auth()->user()->parentProfile;

        if (!$parent) {
            return redirect()->back()->with('error', 'Profile not found.');
        }

        $children = $parent->students;

        // 1. Agar bacha hi nahi hai
        if ($children->isEmpty()) {
            return view('parent.no_students');
        }

        // 2. Agar 1 ya 1 se zyada bache hain, to selection screen hi dikhayein.
        // Redirection hata di hai taake selection page hamesha show ho.
        return view('parent.dashboard', compact('children'));
    }
    // End Method

    public function StudentDashboard($id)
{
    $parent = auth()->user()->parentProfile;

    // 1. Check & Eager Load: Enrollment table ke relationships ko yahan fetch kar rahe hain
    $student = $parent->students()
    ->with(['currentEnrollment.schoolClass', 'currentEnrollment.section'])
    ->where('students.id', $id)
    ->first();

// Yeh laga kar page refresh karein aur check karein ke data relations mein kya aa raha hai
 //dd($student->currentEnrollment);

    if (!$student) {
        return redirect()->route('parent.dashboard')->with('error', 'Unauthorized access! You can only view your own children.');
    }
    
    // 2. Set Session
    session(['active_student_id' => $student->id]);

    // 3. Current Month Attendance
    $attendanceRecords = collect([]);
    $presentCount = 0;
    $absentCount = 0;
    $lateCount = 0;
    $leaveCount = 0;
    $halfDayCount = 0;

    // Aapke model ke mutabik currentEnrollment check lagaya hai
    if ($student->currentEnrollment) {
        
        $attendanceRecords = Attendance::where('enrollment_id', $student->currentEnrollment->id)
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->orderBy('attendance_date', 'DESC')
            ->get();

        // AGAR CURRENT MONTH KA DATA NAHI HAI, TO ALL TIME RECENT DATA LEIN
        if ($attendanceRecords->isEmpty()) {
            $attendanceRecords = Attendance::where('enrollment_id', $student->currentEnrollment->id)
                ->orderBy('attendance_date', 'DESC')
                ->take(15)
                ->get();
        }

        // Attendance breakdown counts
        $presentCount = $attendanceRecords->where('status', 1)->count(); // 1=Present only (Late ka apna slice hai)
        $absentCount = $attendanceRecords->where('status', 0)->count();
        $lateCount = $attendanceRecords->where('status', 2)->count();
        $halfDayCount = $attendanceRecords->where('status', 3)->count();
        $leaveCount = $attendanceRecords->where('status', 4)
            ->filter(fn ($r) => strcasecmp(trim((string) $r->remarks), 'Holiday') !== 0)->count(); // 4 + Holiday = Holiday, Leave nahi
    }

    // 4. Exam and Fees
    $examResults = class_exists('App\Models\ExamResult') ? $student->examResults : collect([]);
    $fees = class_exists('App\Models\Fee') ? $student->fees : collect([]);

    // 5. 📚 DYNAMIC BOOK LIST LOGIC (Merged & Bulletproof Version)
$currentEnrollment = $student->currentEnrollment ?? null;
$classId = $currentEnrollment->class_id ?? null;
$className = $currentEnrollment->schoolClass->name ?? 'N/A'; // 👈 Class name safely extracted here

$bookList = collect([]); // Safe default empty collection

if ($classId) {
        // Model use karein kyunki model mein relation set hai
        $bookList = ClassSubject::with('subject')
            ->where('class_id', $classId)
            ->get();
    }

// 6. Saare variables aur $className ko view mein compact kar ke bhej diya
return view('parent.student_details', compact(
    'student',
    'attendanceRecords',
    'examResults',
    'fees',
    'bookList',
    'className', // 👈 View mein 'Class 8 - A' ya real class dikhane ke liye lazmi hai
    'presentCount',
    'absentCount',
    'lateCount',
    'leaveCount',
    'halfDayCount'
));
}
    // End Method


    // Real dashboard of one child (own children only): same cards/rules as the student dashboard, each block behind its parent portal permission
    public function childDashboard($id)
    {
        $user = auth()->user();
        $student = $this->findOwnChild($id);

        // Current session enrollment (latest active); no current session = old behavior (latest active enrollment of any session)
        $sessionId = $this->getActiveSessionId();
        $enrollment = Enrollment::with(['schoolClass', 'section', 'academicYear'])
            ->where('student_id', $student->id)
            ->where('enroll_status', 1)
            ->when($sessionId, fn ($q) => $q->where('academic_year_id', $sessionId))
            ->latest('id')
            ->first();

        $today = now()->toDateString();
        $todayName = now()->englishDayOfWeek;
        $monthStart = now()->startOfMonth()->toDateString();
        $canStudents = $user->can('access-parent-students');
        $canTimetable = $user->can('access-parent-timetable');
        $canHomework = $user->can('access-parent-homework');
        $canResults = $user->can('access-parent-results');

        // 1. Attendance (is mahine): 0 Absent, 1 Present, 2 Late, 3 Half Day, 4 Leave (4 + remarks "Holiday" ginti me nahi)
        $monthCounts = ['Present' => 0, 'Late' => 0, 'Half Day' => 0, 'Absent' => 0, 'Leave' => 0];
        if ($canStudents && $enrollment) {
            $attRows = Attendance::where('enrollment_id', $enrollment->id)
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

        // 2. Attendance (Session): enrollment ke academic year ke mahine, Present (P + L + H) vs Absent, ek grouped query
        $sessionLabels = [];
        $sessionPresent = [];
        $sessionAbsent = [];
        $sessionP = 0; $sessionL = 0; $sessionH = 0;
        $year = $enrollment ? $enrollment->academicYear : null;
        if ($canStudents && $year && $year->start_date && $year->end_date) {
            $sFrom = Carbon::parse($year->start_date)->toDateString();
            // end_date mahine ki 1 tareekh ho to pichla mahina aakhri mahina; aaj ke baad ka data nahi
            $sEnd = Carbon::parse($year->end_date);
            if ($sEnd->day === 1) {
                $sEnd = $sEnd->copy()->subDay();
            }
            $sTo = min(max($sEnd->toDateString(), $sFrom), $today); // same $sTo query aur month loop dono me
            if ($sTo >= $sFrom) {
                $sessionRows = Attendance::where('enrollment_id', $enrollment->id)
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

        // 3. Aaj ke periods (class_timetables, bache ki class-section), time order; room class-section ka hota hai
        $todayPeriods = collect();
        $roomNo = null;
        $offDayLabel = null;
        if ($canTimetable && $enrollment) {
            $todayPeriods = ClassTimetable::with(['subject', 'teacher'])
                ->where('academic_year_id', $enrollment->academic_year_id)
                ->where('school_class_id', $enrollment->class_id)
                ->where('section_id', $enrollment->section_id)
                ->where('day', $todayName)->orderBy('time_from')->get();
            $roomNo = Room::where('school_class_id', $enrollment->class_id)
                ->where('section_id', $enrollment->section_id)->value('room_no');
            // Calendar off event aaj ho to periods ki jagah off note
            $dayStatus = TimetableDayStatus::forWeek($today, $todayPeriods->isNotEmpty() ? [$todayName] : [])['day_status'][$todayName];
            $offDayLabel = $dayStatus['status'] === 'not_scheduled' ? $dayStatus['label'] : null;
        }
        $periodsDone = $offDayLabel ? 0 : $todayPeriods->filter(fn ($p) => $p->time_to && Carbon::parse($p->time_to)->lte(now()))->count();

        // 4. Homework (StudentHomework): pending / past due, 12 mahine sparkline, agle 5 due
        $pendingHomework = 0;
        $pastDueHomework = 0;
        $upcomingHomework = collect();
        $homeworkMonthLabels = [];
        $homeworkSeries = [];
        if ($canHomework && $enrollment) {
            $hw = StudentHomework::forStudent($student->id);
            $pendingHomework = $hw['pendingCount'];
            $pastDueHomework = $hw['pastDueCount'];
            $upcomingHomework = $hw['homeworks']->filter(fn ($h) => $h->due_date->gte(now()->startOfDay()))->sortBy('due_date')->take(5)->values();
            $sparkStart = now()->startOfMonth()->subMonthsNoOverflow(11);
            $byMonth = $hw['homeworks']->groupBy(fn ($h) => $h->homework_date->format('Y-m'))->map->count();
            for ($i = 0; $i < 12; $i++) {
                $m = $sparkStart->copy()->addMonthsNoOverflow($i);
                $homeworkMonthLabels[] = $m->format('M y');
                $homeworkSeries[] = (int) ($byMonth->get($m->format('Y-m')) ?: 0);
            }
        }

        // 5. Published results (StudentResults): latest 3
        $recentResults = ($canResults && $enrollment) ? StudentResults::forStudent($student->id)['results']->take(3)->values() : collect();

        // 6. Class teacher (teacher_assignments, enrollment ki class-section-year)
        $assignedTeacher = null;
        if ($canStudents && $enrollment && $enrollment->class_id && $enrollment->section_id) {
            $assignedTeacher = DB::table('teacher_assignments')
                ->join('teachers', 'teacher_assignments.teacher_id', '=', 'teachers.id')
                ->join('school_class', 'teacher_assignments.class_id', '=', 'school_class.id')
                ->join('sections', 'teacher_assignments.section_id', '=', 'sections.id')
                ->where('teacher_assignments.class_id', $enrollment->class_id)
                ->where('teacher_assignments.section_id', $enrollment->section_id)
                ->where('teacher_assignments.academic_year_id', $enrollment->academic_year_id)
                ->select('teachers.first_name', 'teachers.last_name', 'school_class.name as class_name', 'sections.name as section_name')
                ->first();
        }

        return view('parent.child_dashboard', compact(
            'student', 'enrollment', 'assignedTeacher',
            'monthCounts', 'monthDaysPresent', 'monthDaysCounted', 'monthAttPercent',
            'sessionLabels', 'sessionPresent', 'sessionAbsent',
            'sessionPresentTotal', 'sessionAbsentTotal', 'sessionAttPercent',
            'todayPeriods', 'roomNo', 'offDayLabel', 'periodsDone',
            'pendingHomework', 'pastDueHomework', 'upcomingHomework', 'homeworkMonthLabels', 'homeworkSeries',
            'recentResults'
        ) + ['activeEnrollment' => $enrollment] + $this->childHeader($student, 'parent.students.child-dashboard'));
    }
    // End Method

    // Child of the logged-in parent (404 for anyone else's child); remembers it as the active child for the sidebar
    private function findOwnChild($id)
    {
        $student = auth()->user()->parentProfile->students()->where('students.id', $id)->first();
        abort_if(!$student, 404);

        session(['active_student_id' => $student->id]);

        return $student;
    }

    // Variables for the "active child + Switch Student" header (parent.partials.child_switcher)
    private function childHeader($student, $switchRoute)
    {
        return [
            'activeChild'      => $student,
            'activeEnrollment' => StudentHomework::activeEnrollment($student->id), // same row the schedule / homework use
            'siblings'         => auth()->user()->parentProfile->students()->get(),
            'switchRoute'      => $switchRoute,
        ];
    }

    // Class Schedule page of one child (read-only, child must belong to the logged-in parent)
    public function childClassSchedule($id)
    {
        $student = $this->findOwnChild($id);

        return view('student.class_schedule.index', [
            'scheduleDataUrl'   => route('parent.students.class_schedule.get_data', ':id'),
            'scheduleStudentId' => $student->id,
            'scheduleTitle'     => $student->full_name . "'s Class Schedule",
            'dashboardUrl'      => route('parent.students.dashboard', $student->id),
        ] + $this->childHeader($student, 'parent.students.class_schedule.index'));
    }
    // End Method

    // Homework page of one child (read-only, same query as the student My Homework page)
    public function childHomework($id)
    {
        $student = $this->findOwnChild($id);

        return view('student.homework.index', StudentHomework::forStudent($student->id) + [
            'homeworkTitle' => $student->full_name . "'s Homework",
            'dashboardUrl'  => route('parent.students.dashboard', $student->id),
        ] + $this->childHeader($student, 'parent.students.homework.index'));
    }
    // End Method

    // AJAX: weekly timetable of one child (same ownership check)
    public function childClassScheduleData(Request $request, $id)
    {
        $request->validate(['week' => 'nullable|date']);

        $student = auth()->user()->parentProfile->students()->where('students.id', $id)->first();
        abort_if(!$student, 404);

        return response()->json(StudentClassSchedule::forStudent($student->id, $request->week));
    }
    // End Method

    // Logout
    public function ParentLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/parent/login');
    }
    // End Method


    // Login Page
    public function ParentLogin()
    {
        if (Auth::check()) {
            return redirect()->route('parent.dashboard');
        }

        return view('parent.parents_login');
    }
    // End Method


    // Profile View
    public function ParentProfile()
    {
        // current logged-in admin. ka data le rahe hain
        $id = Auth::id();

        // user record fetch
        $profileData = User::find($id);

        // view me data send
        return view('parent.parents_profile', compact('profileData'));
    }
    // End Method

    // Change Password page (own account only)
    public function ParentChangePassword()
    {
        return view('staff_profile.change_password', [
            'updateRoute' => route('parent.password.update'),
        ]);
    }
    // End Method

    // Password Update (own account only), then logout like the staff pages
    public function ParentPasswordUpdate(StaffPasswordUpdateRequest $request, StaffSelfProfileService $service)
    {
        $service->updatePassword(Auth::user(), $request->validated()['password']);

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/parent/login')->with([
            'message' => 'Password changed successfully. Please login with your new password.',
            'alert-type' => 'success'
        ]);
    }
    // End Method


    // Profile Update
    public function ParentProfileUpdate(ProfileUpdateRequest $request, ImageService $imageService)
    {
        // current logged-in user
        $user = User::find(Auth::id());

        // basic fields update data array me store
        $data = [
            'name'   => $request->profileName,
            'phone'  => $request->profilePhone,
            'address'   => $request->address,
            'gender' => $request->gender, // added missing field
        ];

        // check if new image uploaded
        $newPhoto = null;
        if ($request->hasFile('photo')) {
            // photo parent_profiles.father_photo mein hoti hai (User::photo_url wahi pehle dekhta hai), users.photo mein nahi
            $profile = $user->parentProfile;
            $oldPhoto = $profile ? $profile->father_photo : $user->photo;
            $oldFolder = $profile ? 'uploads/parents' : 'uploads/parent_images';

            $newPhoto = $imageService->upload($request->file('photo'), 'uploads/parents', 300, 300); // student edit page wala folder

            if (!$profile) {
                $data['photo'] = $newPhoto; // profile row nahi to users.photo mein hi rakho
            }
        }

        // users + parent_profiles dono ek sath save
        DB::transaction(function () use ($user, $data, $newPhoto) {
            $user->update($data);
            if ($newPhoto && $user->parentProfile) {
                $user->parentProfile->update(['father_photo' => $newPhoto]);
            }
        });

        // Purani photo naya save hone ke BAAD delete
        if ($newPhoto && $oldPhoto) {
            $imageService->delete($oldPhoto, $oldFolder);
        }

        // redirect back with success message
        return redirect()->back()->with([
            'message' => 'Parent Profile Updated Successfully',
            'alert-type' => 'success'
        ]);
    }
    // End Method

    // Results list of one child (read-only, same query as the student Results page)
    public function childResults($id)
    {
        $student = $this->findOwnChild($id);

        return view('student.result.index', StudentResults::forStudent($student->id) + [
            'resultsTitle'    => $student->full_name . "'s Results",
            'dashboardUrl'    => route('parent.students.dashboard', $student->id),
            'resultsIndexUrl' => route('parent.students.results.index', $student->id),
            'detailsRoute'    => 'parent.students.results.details',
            'detailsParams'   => [$student->id],
        ] + $this->childHeader($student, 'parent.students.results.index'));
    }
    // End Method

    // One published result of one child (404 if the child or the result is not the parent's)
    public function childResultDetails($id, $result)
    {
        $student = $this->findOwnChild($id);

        return view('student.result.details', StudentResults::details($student->id, $result) + [
            'dashboardUrl'    => route('parent.students.dashboard', $student->id),
            'resultsIndexUrl' => route('parent.students.results.index', $student->id),
        ] + $this->childHeader($student, 'parent.students.results.index'));
    }
    // End Method
}
