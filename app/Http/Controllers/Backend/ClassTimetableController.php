<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\ClassTimetable;
use App\Models\Group;
use App\Models\Room;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Teacher;
use App\Services\TimetableDayStatus;
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

    // Active year ke weekly off din (Copy Day modal ke hint ke liye, read-only). Year na ho to khali
    $year = AcademicYear::where('is_current', 1)->first();
    $weeklyOffDays = $year ? ($year->weekly_off_days ?: []) : [];

    return view('admin.class_timetable.create', compact('classes', 'teachers', 'days', 'weeklyOffDays'));
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

    public function teacherAvailability(Request $request)
    {
        $request->validate([
            'teacher_id'      => 'required|exists:teachers,id',
            'day'             => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'school_class_id' => 'required',
            'section_id'      => 'required',
            'time_from'       => 'nullable|date_format:H:i',
            'time_to'         => 'nullable|date_format:H:i',
        ]);

        $teacher   = Teacher::findOrFail($request->teacher_id);
        $classId   = $request->school_class_id;
        $sectionId = $request->section_id;

        $toMin = fn ($t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
        $toHi  = fn ($m) => sprintf('%02d:%02d', intdiv($m, 60), $m % 60);

        // Is class-section ke ilawa baaqi sab jagah ki busy rows
        // Sirf current year ki rows busy maani jayen (year na ho to null = koi row nahi)
        $busy = ClassTimetable::with(['schoolClass', 'section', 'subject'])
            ->where('academic_year_id', $this->getActiveSessionId())
            ->where('teacher_id', $teacher->id)
            ->where('day', $request->day)
            ->where(function ($q) use ($classId, $sectionId) {
                $q->where('school_class_id', '!=', $classId)
                ->orWhere('section_id', '!=', $sectionId);
            })
            ->orderBy('time_from')
            ->get()
            ->map(fn ($r) => [
                'from'    => substr($r->time_from, 0, 5),
                'to'      => substr($r->time_to, 0, 5),
                'class'   => optional($r->schoolClass)->name,
                'section' => optional($r->section)->name,
                'subject' => optional($r->subject)->name,
            ])->values();

        // Requested slot ke saath overlap?
        $conflict = null;
        if ($request->time_from && $request->time_to) {
            $conflict = $busy->first(fn ($b) =>
                $b['from'] < $request->time_to && $b['to'] > $request->time_from
            );
        }

        // Day window (default 07:00 - 16:00, busy/requested slots ke hisab se extend)
        $starts = [420];
        $ends   = [960];
        foreach ($busy as $b) { $starts[] = $toMin($b['from']); $ends[] = $toMin($b['to']); }
        if ($request->time_from) { $starts[] = $toMin($request->time_from); }
        if ($request->time_to)   { $ends[]   = $toMin($request->time_to); }
        $winStart = (int) floor(min($starts) / 60) * 60;
        $winEnd   = (int) ceil(max($ends) / 60) * 60;

        // Free gaps (minimum 15 min)
        $free   = [];
        $cursor = $winStart;
        foreach ($busy as $b) {
            $bf = $toMin($b['from']);
            if ($bf - $cursor >= 15) {
                $free[] = ['from' => $toHi($cursor), 'to' => $toHi($bf), 'minutes' => $bf - $cursor];
            }
            $cursor = max($cursor, $toMin($b['to']));
        }
        if ($winEnd - $cursor >= 15) {
            $free[] = ['from' => $toHi($cursor), 'to' => $toHi($winEnd), 'minutes' => $winEnd - $cursor];
        }

        return response()->json([
            'teacher'  => ['id' => $teacher->id, 'name' => $teacher->name],
            'day'      => $request->day,
            'window'   => ['start' => $toHi($winStart), 'end' => $toHi($winEnd)],
            'busy'     => $busy,
            'free'     => $free,
            'conflict' => $conflict,
        ]);
    }
    // End Method

    // Read-only: din ke slots ke hisab se busy teacher ids (save() wali overlap/exclusion condition)
    public function busyTeachers(Request $request)
    {
        $request->validate([
            'school_class_id' => 'required',
            'section_id'      => 'required',
            'day'             => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'slots'           => 'required|array|max:50',
            'slots.*'         => ['required', 'regex:/^\d{2}:\d{2}-\d{2}:\d{2}$/'],
        ]);

        $classId   = $request->school_class_id;
        $sectionId = $request->section_id;

        // Is din ki doosri class-sections ki rows (ek hi query)
        $rows = ClassTimetable::where('academic_year_id', $this->getActiveSessionId())
            ->where('day', $request->day)
            ->where(function ($q) use ($classId, $sectionId) {
                $q->where('school_class_id', '!=', $classId)
                    ->orWhere('section_id', '!=', $sectionId);
            })
            ->get(['teacher_id', 'time_from', 'time_to']);

        // Key = "HH:MM-HH:MM" slot (unique), value = busy teacher ids
        $busy = [];
        foreach (array_unique($request->slots) as $slot) {
            [$from, $to] = explode('-', $slot);
            $busy[$slot] = $rows->filter(fn ($r) => substr($r->time_from, 0, 5) < $to && substr($r->time_to, 0, 5) > $from)
                ->pluck('teacher_id')->unique()->values();
        }

        return response()->json(['success' => true, 'busy' => $busy]);
    }
    // End Method

    // AJAX: class+section select hone pe existing timetable fetch
    public function getData(Request $request)
    {
        $request->validate([
            'school_class_id' => 'required|exists:school_class,id',
            'section_id' => 'required|exists:sections,id',
            'week' => 'nullable|date',
        ]);

        $timetable = ClassTimetable::with(['subject', 'teacher'])
            ->where('academic_year_id', $this->getActiveSessionId())
            ->where('school_class_id', $request->school_class_id)
            ->where('section_id', $request->section_id)
            ->get();

        // Room period ka nahi, class-section ka hota hai: ek query se assigned room
        $roomNo = Room::where('school_class_id', $request->school_class_id)
            ->where('section_id', $request->section_id)
            ->value('room_no');

        $timetable->each(fn ($row) => $row->setAttribute('assigned_room_no', $roomNo));

        $grouped = $timetable->groupBy('day');

        // Index view `week` bhejta hai: day-wise status (holiday/weekly off) saath. Create page purani shape hi leta hai.
        if ($request->filled('week')) {
            return response()->json(array_merge(
                ['timetable' => $grouped],
                TimetableDayStatus::forWeek($request->week, $grouped->keys()->all())
            ));
        }

        return response()->json($grouped);
    }

    // Day-wise save (sirf request ka ek din create/update hota hai)
    public function save(Request $request)
    {
        // Timetable current academic year ka hota hai; year na ho to save nahi
        $yearId = $this->getActiveSessionId();
        if (!$yearId) {
            return response()->json([
                'success' => false,
                'message' => 'No current academic year set. Mark a session as current first.',
            ], 422);
        }

        // Weekly off: din khali save karna (Saturday/Sunday off ho to) -> sirf us din ki rows delete
        if ($request->boolean('day_off')) {
            $request->validate([
                'school_class_id' => 'required|exists:school_class,id',
                'section_id' => 'required|exists:sections,id',
                'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            ]);

            $count = ClassTimetable::where('academic_year_id', $yearId)
                ->where('school_class_id', $request->school_class_id)
                ->where('section_id', $request->section_id)
                ->where('day', $request->day)
                ->delete();

            return response()->json([
                'success' => true,
                'day_off' => true,
                'message' => "{$request->day} marked as weekly off ({$count} periods removed)",
            ]);
        }

        $validator = Validator::make($request->all(), [
            'school_class_id' => 'required|exists:school_class,id',
            'section_id' => 'required|exists:sections,id',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'periods' => 'required|array|min:1',
            'periods.*.day' => 'required|same:day', // har row usi din ki honi chahiye jo save ho raha hai
            'periods.*.subject_id' => 'required|exists:subjects,id',
            'periods.*.teacher_id' => 'required|exists:teachers,id',
            'periods.*.time_from' => 'required|date_format:H:i',
            'periods.*.time_to' => 'required|date_format:H:i|after:periods.*.time_from',
        ], [
            'periods.*.subject_id.required' => 'is required',
            'periods.*.subject_id.exists' => 'is invalid',
            'periods.*.teacher_id.required' => 'is required',
            'periods.*.teacher_id.exists' => 'is invalid',
            'periods.*.time_from.required' => 'is required',
            'periods.*.time_from.date_format' => 'format is invalid',
            'periods.*.time_to.required' => 'is required',
            'periods.*.time_to.date_format' => 'format is invalid',
            'periods.*.time_to.after' => 'must be after Time From',
            'periods.*.day.same' => 'does not match the selected day',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $classId = $request->school_class_id;
        $sectionId = $request->section_id;
        $day = $request->day;

        // --- Clash checking ---
        foreach ($request->periods as $row) {
            // 1) Teacher clash (kisi bhi doosri class-section mein same time pe busy to nahi)
            $teacherClash = ClassTimetable::where('academic_year_id', $yearId)
                ->where('teacher_id', $row['teacher_id'])
                ->where('day', $day)
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
                    'message' => "Teacher already assigned elsewhere on {$day} at {$row['time_from']} - {$row['time_to']}",
                ], 422);
            }
        }

        // 2) Same class-section ke andar hi overlap check (naye rows aapas mein, sirf is din ke)
        $rows = collect($request->periods)->sortBy('time_from')->values();
        for ($i = 0; $i < $rows->count() - 1; $i++) {
            if ($rows[$i]['time_to'] > $rows[$i + 1]['time_from']) {
                return response()->json([
                    'success' => false,
                    'message' => "Time overlap found on {$day} within the same class-section",
                ], 422);
            }
        }

        $clashRow = DB::transaction(function () use ($request, $classId, $sectionId, $day, $yearId) {
            // Concurrency: in teachers ki rows lock (sorted order, deadlock se bachne ke liye), taake same teacher
            // wali doosri save commit hone tak yahin ruke. Ye transaction ka pehla statement hi rehna chahiye.
            $teacherIds = collect($request->periods)->pluck('teacher_id')
                ->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
            Teacher::withTrashed()->whereIn('id', $teacherIds)->orderBy('id')->lockForUpdate()->pluck('id');

            // Lock ke baad teacher clash dobara (beech mein kisi aur ne commit kiya ho to ab nazar aayega)
            foreach ($request->periods as $row) {
                $teacherClash = ClassTimetable::where('academic_year_id', $yearId)
                    ->where('teacher_id', $row['teacher_id'])
                    ->where('day', $day)
                    ->where(function ($q) use ($row) {
                        $q->where('time_from', '<', $row['time_to'])
                            ->where('time_to', '>', $row['time_from']);
                    })
                    ->where(function ($q) use ($classId, $sectionId) {
                        $q->where('school_class_id', '!=', $classId)
                            ->orWhere('section_id', '!=', $sectionId);
                    })
                    ->exists();

                if ($teacherClash) {
                    return $row; // abhi kuch likha nahi, khali commit
                }
            }

            // Sirf isi din ki purani entries hata ke fresh insert (baaqi dinon ko haath nahi lagana)
            ClassTimetable::where('academic_year_id', $yearId)
                ->where('school_class_id', $classId)
                ->where('section_id', $sectionId)
                ->where('day', $day)
                ->delete();

            foreach ($request->periods as $row) {
                ClassTimetable::create([
                    'academic_year_id' => $yearId,
                    'school_class_id' => $classId,
                    'section_id' => $sectionId,
                    'subject_id' => $row['subject_id'],
                    'teacher_id' => $row['teacher_id'],
                    'day' => $day,
                    'time_from' => $row['time_from'],
                    'time_to' => $row['time_to'],
                ]);
            }

            return null;
        });

        if ($clashRow) {
            return response()->json([
                'success' => false,
                'message' => "Teacher already assigned elsewhere on {$day} at {$clashRow['time_from']} - {$clashRow['time_to']}",
            ], 422);
        }

        return response()->json(['success' => true, 'message' => "{$day} timetable saved successfully"]);
    }
    // End Method

    public function destroy($id)
    {
        $period = ClassTimetable::find($id);

        if (!$period) {
            return response()->json(['success' => false, 'message' => 'Period not found'], 404);
        }

        $day = $period->day;
        $period->delete();

        return response()->json(['success' => true, 'message' => "{$day} period deleted"]);
    }
    // End Method

}
