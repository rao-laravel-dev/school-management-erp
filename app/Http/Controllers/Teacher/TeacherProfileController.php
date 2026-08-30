<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ExamSchedule;
use App\Models\SalarySlip;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TeacherProfileController extends Controller
{
    public function TeacherManage()
    {
        $role = auth()->user()->roles->first()->name;
        return view('teacher.dashboard');
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
        if ($request->filled('new_password')) {
            $request->validate([
                'current_password' => 'required|string',
                'new_password'     => 'required|string|min:8|confirmed',
            ]);
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->withErrors([
                    'current_password' => 'Current password is incorrect.',
                ])->withInput()->with('toastr-error', 'Current password is incorrect.');
            }
            $user->update(['password' => Hash::make($request->new_password)]);
        }
        return redirect()->back()->with('toastr-success', 'Teacher Profile Updated Successfully');
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
