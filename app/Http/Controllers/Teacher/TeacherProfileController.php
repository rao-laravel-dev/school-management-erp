<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ClassSubject;
use App\Models\ClassTimetable;
use App\Models\ExamSchedule;
use App\Models\Lesson;
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

    public function mySyllabusStatus()
    {
        $teacher = Auth::user()->teacher;
        // $userId  = Auth::id(); // ClassTimetable.teacher_id -> users.id

        // ============================================
        // SECTION 1: Homeroom class overview (poori class ka syllabus)
        // ============================================
        $homeroomAssignments = $teacher->assignments()
            ->whereNull('class_subject_id')
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
