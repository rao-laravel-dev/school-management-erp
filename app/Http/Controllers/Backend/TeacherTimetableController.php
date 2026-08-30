<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassTimetable;
use App\Models\Teacher;

class TeacherTimetableController extends Controller
{
    // Admin-only: pick any teacher and view their weekly timetable
    public function index()
    {
        $teachers = Teacher::orderBy('first_name')->get();
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return view('admin.teacher_timetable.index', compact('teachers', 'days'));
    }
    // End Method

    public function getData($teacherId)
    {
        $timetable = ClassTimetable::with(['schoolClass', 'section', 'subject'])
            ->where('teacher_id', $teacherId)
            ->get()
            ->groupBy('day');

        return response()->json($timetable);
    }
    // End Method

    public function getTeacherInfo($teacherId)
    {
        $activeYearId = AcademicYear::getActiveSessionId();

        $teacher = Teacher::with(['assignments' => function ($q) use ($activeYearId) {
            $q->where('academic_year_id', $activeYearId)
                ->with(['schoolClass', 'section']);
        }])->findOrFail($teacherId);

        $assignedClasses = $teacher->assignments
            ->map(function ($a) {
                $className = optional($a->schoolClass)->name;
                $sectionName = optional($a->section)->name;
                if (!$className) return null;
                return $sectionName ? "{$className} - {$sectionName}" : $className;
            })
            ->filter()
            ->unique()
            ->values();

        return response()->json([
            'name'        => $teacher->name,
            'father_name' => $teacher->father_name,
            'phone'       => $teacher->phone,
            'photo'       => $teacher->photo ? asset('storage/teacher_images/' . $teacher->photo) : asset('backend/assets/images/avatar.png'),
            'classes'     => $assignedClasses,
        ]);
    }
    // End Method

}
