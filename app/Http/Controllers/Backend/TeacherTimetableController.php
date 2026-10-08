<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassTimetable;
use App\Models\Room;
use App\Models\Teacher;
use App\Services\TimetableDayStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function getData(Request $request, $teacherId)
    {
        // Teacher portal route par URL ka id trust nahi: sirf apna teachers.id, warna 403 (IDOR band)
        if ($request->routeIs('teacher.timetable.get_data')) {
            $ownTeacherId = optional(Auth::user()->teacher)->id;
            abort_if(! $ownTeacherId || (int) $teacherId !== (int) $ownTeacherId, 403);
        }

        $request->validate(['week' => 'nullable|date']);

        $timetable = ClassTimetable::with(['schoolClass', 'section', 'subject'])
            ->where('academic_year_id', $this->getActiveSessionId())
            ->where('teacher_id', $teacherId)
            ->get();

        // Room class-section ka hota hai: teacher ki saari class-sections ke rooms ek query mein
        $rooms = Room::whereIn('school_class_id', $timetable->pluck('school_class_id')->unique())
            ->whereIn('section_id', $timetable->pluck('section_id')->unique())
            ->get()
            ->keyBy(fn ($r) => $r->school_class_id . '-' . $r->section_id);

        $timetable->each(fn ($t) => $t->setAttribute(
            'assigned_room_no',
            optional($rooms->get($t->school_class_id . '-' . $t->section_id))->room_no
        ));

        $grouped = $timetable->groupBy('day');

        // Index views `week` bhejti hain: day-wise status (holiday/weekly off) saath
        if ($request->filled('week')) {
            return response()->json(array_merge(
                ['timetable' => $grouped],
                TimetableDayStatus::forWeek($request->week, $grouped->keys()->all())
            ));
        }

        return response()->json($grouped);
    }
    // End Method

    public function getTeacherInfo($teacherId)
    {
        $activeYearId = AcademicYear::getActiveSessionId();

        $teacher = Teacher::with(['assignments' => function ($q) use ($activeYearId) {
            $q->where('academic_year_id', $activeYearId)->with(['schoolClass', 'section']);
        }])->findOrFail($teacherId);

        $label = fn ($className, $sectionName) =>
            $className ? ($sectionName ? "{$className} - {$sectionName}" : $className) : null;

        $classTeacherOf = $teacher->assignments
            ->map(fn ($a) => $label(optional($a->schoolClass)->name, optional($a->section)->name))
            ->filter()->unique()->values();

        $teachingClasses = ClassTimetable::with(['schoolClass', 'section'])
            ->where('academic_year_id', $activeYearId)
            ->where('teacher_id', $teacherId)
            ->get()
            ->map(fn ($t) => $label(optional($t->schoolClass)->name, optional($t->section)->name))
            ->filter()->unique()->values();

        return response()->json([
            'name'             => $teacher->name,
            'father_name'      => $teacher->father_name,
            'phone'            => $teacher->phone,
            'photo'            => $teacher->photo_url,
            'class_teacher_of' => $classTeacherOf,
            'classes'          => $teachingClasses,
        ]);
    }
    // End Method

}
