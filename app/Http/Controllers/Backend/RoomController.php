<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    private const FIELDS = ['room_no', 'name', 'building', 'floor', 'capacity', 'type', 'status', 'school_class_id', 'section_id'];

    public function index()
    {
        $rooms = Room::with(['schoolClass', 'section'])->withCount('classTimetables')->latest()->get();
        $types = Room::TYPES;
        $floors = Room::FLOORS;
        $classes = SchoolClass::where('status', 1)->orderBy('name')->get();

        return view('admin.rooms.index', compact('rooms', 'types', 'floors', 'classes'));
    }
    // End Method

    // AJAX: class select hone pe uske sections (ClassTimetable ke get-sections jaisa)
    public function getSectionsByClass($classId)
    {
        $sections = Section::join('school_class_section', 'school_class_section.section_id', '=', 'sections.id')
            ->where('school_class_section.school_class_id', $classId)
            ->where('school_class_section.status', 1)
            ->select('sections.id', 'sections.name')
            ->distinct()
            ->get();

        return response()->json($sections);
    }
    // End Method

    public function store(Request $request)
    {
        $request->validate($this->rules($request), $this->messages());

        Room::create($request->only(self::FIELDS));

        return redirect()->back()->with('success', 'Room added successfully');
    }
    // End Method

    public function update(Request $request, Room $room)
    {
        $request->validate($this->rules($request, $room), $this->messages());

        $room->update($request->only(self::FIELDS));

        return redirect()->back()->with('success', 'Room updated successfully');
    }
    // End Method

    public function destroy(Room $room)
    {
        // Timetable mein use ho raha ho to delete block
        if ($room->classTimetables()->exists()) {
            return redirect()->back()->with('error', 'Is room ko class timetable mein use kiya ja raha hai, delete nahi ho sakta. Inactive kar dein.');
        }

        $room->delete();

        return redirect()->back()->with('success', 'Room deleted successfully');
    }
    // End Method

    public function toggleStatus(Room $room)
    {
        $room->status = $room->status ? 0 : 1;
        $room->save();

        return response()->json([
            'status'  => (int) $room->status,
            'message' => 'Status updated successfully',
        ]);
    }
    // End Method

    private function rules(Request $request, ?Room $room = null): array
    {
        return [
            'room_no'         => 'required|string|max:50|unique:rooms,room_no' . ($room ? ',' . $room->id : ''),
            'name'            => 'nullable|string|max:100',
            'building'        => 'nullable|string|max:100',
            // Purane free-text floor wala room edit ho to uski maujooda value bhi allowed (warna update atak jata)
            'floor'           => ['nullable', 'string', 'max:50', Rule::in(array_merge(Room::FLOORS, ($room && $room->floor) ? [$room->floor] : []))],
            'capacity'        => 'nullable|integer|min:1|max:10000',
            'type'            => ['nullable', Rule::in(array_keys(Room::TYPES))],
            'status'          => 'required|boolean',
            // Class aur Section dono ya dono khali (Lab/Hall jaise rooms bina class ke)
            'school_class_id' => ['nullable', 'required_with:section_id', Rule::exists((new SchoolClass)->getTable(), 'id')],
            'section_id'      => [
                'nullable',
                'required_with:school_class_id',
                Rule::exists('school_class_section', 'section_id')
                    ->where('school_class_id', $request->school_class_id),
                // Ek class+section ko sirf ek room
                Rule::unique('rooms', 'section_id')
                    ->where('school_class_id', $request->school_class_id)
                    ->ignore($room ? $room->id : null),
            ],
        ];
    }

    private function messages(): array
    {
        return [
            'room_no.required'                => 'Room No. is required',
            'room_no.unique'                  => 'Room No. already exists',
            'status.required'                 => 'Status is required',
            'school_class_id.required_with'   => 'Class is required when Section is selected',
            'school_class_id.exists'          => 'Selected Class is invalid',
            'section_id.required_with'        => 'Section is required when Class is selected',
            'section_id.exists'               => 'Selected Section does not belong to this Class',
            'section_id.unique'               => 'Is class-section ko pehle se room assign hai',
            'capacity.integer'                => 'Capacity must be a number',
            'floor.in'                        => 'Selected Floor is invalid',
        ];
    }
}
