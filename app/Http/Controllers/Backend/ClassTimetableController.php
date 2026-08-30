<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\ClassTimetable;
use App\Models\Group;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClassTimetableController extends Controller
{
    // Index page load (list/view mode)
    public function index()
    {
        $classes = SchoolClass::where('status', 1)->get();
        return view('admin.class_timetable.index', compact('classes'));
    }

    // Create page load (form mode)
    public function create()
{
    $classes = SchoolClass::where('status', 1)->get();
    $teachers = Teacher::orderBy('first_name')->get();
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    return view('admin.class_timetable.create', compact('classes', 'teachers', 'days'));
}

    // AJAX: class select hone pe section 
    public function getSectionsByClass($classId)
    {
        $sections = Section::join('school_class_section', 'school_class_section.section_id', '=', 'sections.id')
            ->where('school_class_section.school_class_id', $classId)
            ->where('school_class_section.status', 1)
            ->select('sections.*')
            ->distinct()
            ->get();

        return response()->json($sections);
    }
    // AJAX: class select hone pe groups (agar us class ke liye groups exist karte hain)
    // Groups jo is class ke liye class_subject mein use ho rahe hain
    public function getGroupsByClass($classId)
    {
        $groupIds = ClassSubject::where('class_id', $classId)
            ->whereNotNull('group_id')
            ->distinct()
            ->pluck('group_id');

        $groups = Group::whereIn('id', $groupIds)->get();

        return response()->json($groups);
    }

    // Subjects — seedha class_subject se, optional group filter ke sath
    public function getSubjectsByClass(Request $request, $classId)
    {
        $groupId = $request->query('group_id');

        $query = ClassSubject::with('subject')
            ->where('class_id', $classId);

        if ($groupId) {
            // Us group ke subjects + wo subjects jo kisi group se linked nahi (common/compulsory)
            $query->where(function ($q) use ($groupId) {
                $q->where('group_id', $groupId)->orWhereNull('group_id');
            });
        } else {
            $query->whereNull('group_id');
        }

        $subjects = $query->get()->pluck('subject')->filter()->values();

        return response()->json($subjects);
    }

    // AJAX: class+section select hone pe existing timetable fetch
    public function getData(Request $request)
    {
        $request->validate([
            'school_class_id' => 'required|exists:school_class,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $timetable = ClassTimetable::with(['subject', 'teacher'])
            ->where('school_class_id', $request->school_class_id)
            ->where('section_id', $request->section_id)
            ->get()
            ->groupBy('day');

        return response()->json($timetable);
    }

    // Bulk save (all 7 days ek sath)
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'school_class_id' => 'required|exists:school_class,id',
            'section_id' => 'required|exists:sections,id',
            'periods' => 'required|array|min:1',
            'periods.*.day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'periods.*.subject_id' => 'required|exists:subjects,id',
            'periods.*.teacher_id' => 'required|exists:teachers,id',
            'periods.*.time_from' => 'required|date_format:H:i',
            'periods.*.time_to' => 'required|date_format:H:i|after:periods.*.time_from',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $classId = $request->school_class_id;
        $sectionId = $request->section_id;

        // --- Clash checking ---
        foreach ($request->periods as $row) {
            // 1) Teacher clash (kisi bhi doosri class-section mein same time pe busy to nahi)
            $teacherClash = ClassTimetable::where('teacher_id', $row['teacher_id'])
                ->where('day', $row['day'])
                ->where(function ($q) use ($row) {
                    $q->where('time_from', '<', $row['time_to'])
                        ->where('time_to', '>', $row['time_from']);
                })
                ->where(function ($q) use ($classId, $sectionId) {
                    // update case mein isi class-section ke purane rows ko clash mat mano
                    $q->where('school_class_id', '!=', $classId)
                        ->orWhere('section_id', '!=', $sectionId);
                })
                ->exists();

            if ($teacherClash) {
                return response()->json([
                    'success' => false,
                    'message' => "Teacher already assigned elsewhere on {$row['day']} at {$row['time_from']} - {$row['time_to']}",
                ], 422);
            }
        }

        // 2) Same class-section ke andar hi overlap check (naye rows aapas mein)
        $sorted = collect($request->periods)->groupBy('day');
        foreach ($sorted as $day => $rows) {
            $rows = $rows->sortBy('time_from')->values();
            for ($i = 0; $i < $rows->count() - 1; $i++) {
                if ($rows[$i]['time_to'] > $rows[$i + 1]['time_from']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Time overlap found on {$day} within the same class-section",
                    ], 422);
                }
            }
        }

        DB::transaction(function () use ($request, $classId, $sectionId) {
            // Purani entries hata ke fresh insert (simplest approach)
            ClassTimetable::where('school_class_id', $classId)
                ->where('section_id', $sectionId)
                ->delete();

            foreach ($request->periods as $row) {
                ClassTimetable::create([
                    'school_class_id' => $classId,
                    'section_id' => $sectionId,
                    'subject_id' => $row['subject_id'],
                    'teacher_id' => $row['teacher_id'],
                    'day' => $row['day'],
                    'time_from' => $row['time_from'],
                    'time_to' => $row['time_to'],
                ]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Timetable saved successfully']);
    }

    public function destroy($id)
    {
        ClassTimetable::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Period deleted']);
    }
}
