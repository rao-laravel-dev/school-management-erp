<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\MarksheetTemplate;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ParentsController extends Controller
{

    public function ParentManage()
    {
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
        $presentCount = $attendanceRecords->whereIn('status', [1, 2])->count(); // 1=Present, 2=Late
        $absentCount = $attendanceRecords->where('status', 0)->count();
        $lateCount = $attendanceRecords->where('status', 2)->count();
        $leaveCount = $attendanceRecords->where('status', 3)->count();
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
    'leaveCount'
));
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

    // Step 1: Exam + Template selection form for a specific child
    public function resultSelectForm($id)
    {
        $parent = auth()->user()->parentProfile;

        $student = $parent->students()
            ->with('currentEnrollment.schoolClass')
            ->where('students.id', $id)
            ->first();

        if (!$student || !$student->currentEnrollment) {
            return redirect()->route('parent.dashboard')->with('error', 'Unauthorized access! You can only view your own children.');
        }

        session(['active_student_id' => $student->id]);

        $classId = $student->currentEnrollment->class_id;

        $examIds = ExamSchedule::where('class_id', $classId)->pluck('exam_id')->unique();

        $exams = Exam::where('publish_result', 1)
            ->whereIn('id', $examIds)
            ->latest()
            ->get();

        $templates = MarksheetTemplate::orderBy('template_name')->get();

        return view('parent.result_select', compact('exams', 'templates', 'student'));
    }

    // Step 2: Generate + show the actual designed marksheet
    public function viewMarksheet(Request $request, $id)
    {
        $request->validate([
            'exam_id'     => 'required|exists:exams,id',
            'template_id' => 'required|exists:marksheet_templates,id',
        ]);

        $parent = auth()->user()->parentProfile;

        $student = $parent->students()
            ->with('currentEnrollment')
            ->where('students.id', $id)
            ->first();

        if (!$student || !$student->currentEnrollment) {
            return redirect()->route('parent.dashboard')->with('error', 'Unauthorized access! You can only view your own children.');
        }

        $exam     = Exam::where('id', $request->exam_id)->where('publish_result', 1)->firstOrFail();
        $template = MarksheetTemplate::findOrFail($request->template_id);
        $siteSetting = SiteSetting::current();

        $sheets = $this->buildMarksheetSheets(collect([$student->currentEnrollment->id]), $exam, $template);

        return view('admin.print_marksheet.print', [
            'exam'        => $exam,
            'template'    => $template,
            'sheets'      => $sheets,
            'siteSetting' => $siteSetting,
        ]);
    }
}
