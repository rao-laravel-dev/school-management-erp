<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Enrollment;
use App\Models\ExamSchedule;
use App\Models\MarkSheet;
use App\Models\Result;
use App\Models\Subject;
use App\Models\User;
use App\Services\ImageService;
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
        $enrollment = DB::table('enrollments')->where('student_id', $student->id)->first();

        if ($enrollment && !empty($enrollment->class_id) && !empty($enrollment->section_id)) {
            $assignedTeacher = DB::table('teacher_assignments')
                ->join('teachers', 'teacher_assignments.teacher_id', '=', 'teachers.id')
                ->join('school_class', 'teacher_assignments.class_id', '=', 'school_class.id')
                ->join('sections', 'teacher_assignments.section_id', '=', 'sections.id')
                ->where('teacher_assignments.class_id', $enrollment->class_id)
                ->where('teacher_assignments.section_id', $enrollment->section_id)
                ->select(
                    'teachers.first_name',
                    'teachers.last_name',
                    'teachers.photo',
                    'school_class.name as class_name',
                    'sections.name as section_name'
                )
                ->first();
        }

        // 3. Zaroori data (Dashboard stats)
        $attendancePercentage = 92;
        $pendingFees = 5000;
        $activeSubjectsCount = 8;
        $pendingAssignments = 12;
        $recentExams = collect([]);

        // 4. Data return karein (compact mein 'assignedTeacher' ko add kar diya hai)
        return view('student.dashboard', compact(
            'student',
            'assignedTeacher', // 🔥 Yeh variable ab view aur master/header layout ko milega
            'attendancePercentage',
            'pendingFees',
            'activeSubjectsCount',
            'pendingAssignments',
            'recentExams'
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
            return redirect()->route('students.dashboard');
        }

        return view('student.student_login');
    }

    // Profile View
    public function StudentProfile()
    {
        $id = Auth::id();
        $profileData = User::find($id);

        return view('students.student_profile', compact('profileData'));
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
            if ($user->photo) {
                $imageService->delete($user->photo, 'uploads/student_images');
            }

            $data['photo'] = $imageService->upload(
                $request->file('photo'),
                'uploads/student_images',
                300,
                300
            );
        }

        $user->update($data);

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
        $enrollment = DB::table('enrollments')
            ->where('student_id', $studentProfile->id)
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
            ->where('academic_year_id', $activeSessionId)
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
    $studentId = session('active_student_id');

    $enrollment = Enrollment::where('student_id', $studentId)
        ->where('enroll_status', 1) // Active
        ->latest()
        ->first();

    if (!$enrollment) {
        return view('student.result.index', ['results' => collect()]);
    }

    $results = Result::with(['exam.examType', 'grade'])
        ->where('enrollment_id', $enrollment->id)
        ->whereHas('exam', function ($q) {
            $q->where('publish_result', 1);
        })
        ->orderByDesc('created_at')
        ->get();

    return view('student.result.index', compact('results'));
}
// End Method

    public function resultDetails($resultId)
    {
        $studentId = session('active_student_id');

        $enrollment = Enrollment::with(['student.parent', 'schoolClass', 'section'])
            ->where('student_id', $studentId)
            ->where('enroll_status', 1)
            ->latest()
            ->first();

        abort_if(!$enrollment, 404);

        $result = Result::with(['exam.examType', 'grade'])
            ->where('enrollment_id', $enrollment->id)
            ->whereHas('exam', function ($q) {
                $q->where('publish_result', 1);
            })
            ->findOrFail($resultId);

        $subjectMarks = MarkSheet::with('subject')
            ->where('exam_id', $result->exam_id)
            ->where('enrollment_id', $enrollment->id)
            ->get();

        $examSchedules = ExamSchedule::where('exam_id', $result->exam_id)
            ->where('class_id', $result->class_id)
            ->get()
            ->keyBy('subject_id');

        // Calculations
        $percentage = $result->percentage;

        $percentageBadge = match (true) {
            $percentage >= 80 => 'bg-success-subtle text-success',
            $percentage >= 60 => 'bg-primary-subtle text-primary',
            $percentage >= 50 => 'bg-warning-subtle text-warning',
            default => 'bg-danger-subtle text-danger',
        };

        $gradeName = $result->grade->grade_name ?? '-';
        $gradeBadge = match (true) {
            str_starts_with($gradeName, 'A') => 'bg-success-subtle text-success',
            str_starts_with($gradeName, 'B') => 'bg-primary-subtle text-primary',
            str_starts_with($gradeName, 'C') => 'bg-info-subtle text-info',
            str_starts_with($gradeName, 'D') => 'bg-warning-subtle text-warning',
            str_starts_with($gradeName, 'F') => 'bg-danger-subtle text-danger',
            default => 'bg-secondary-subtle text-secondary',
        };

        $fatherName = $enrollment->student->parent->father_name ?? '-';

        // Admission Number (Agar enrollment table me hai ya student table me, dono handle kar lein)
        $admissionNo = $enrollment->admission_no ?? ($enrollment->student->admission_no ?? '-');

        return view('student.result.details', compact(
            'result',
            'subjectMarks',
            'examSchedules',
            'enrollment',
            'percentageBadge',
            'gradeBadge',
            'gradeName',
            'fatherName',
            'admissionNo'
        ));
    }
    // End Method

}
