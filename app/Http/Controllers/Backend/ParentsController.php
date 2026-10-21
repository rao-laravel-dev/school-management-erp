<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\StaffPasswordUpdateRequest;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\User;
use App\Services\ImageService;
use App\Services\StaffSelfProfileService;
use App\Services\StudentClassSchedule;
use App\Services\StudentHomework;
use App\Services\StudentResults;
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
            if ($oldPhoto) {
                $imageService->delete($oldPhoto, $profile ? 'uploads/parents' : 'uploads/parent_images');
            }

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
