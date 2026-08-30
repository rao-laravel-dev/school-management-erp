<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use App\Models\EventType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcademicCalendarController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $eventTypes    = EventType::active()->get();
        $events        = AcademicCalendar::with(['academicYear', 'eventType', 'creator'])
            ->latest()
            ->get();

        return view('admin.academic_calendar.index', compact('academicYears', 'eventTypes', 'events'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'start_date'       => ['required', 'date'],
            'end_date'         => ['nullable', 'date', 'after_or_equal:start_date'],
            'event_type_id'    => ['required', 'exists:event_types,id'],
        ]);

        $data['created_by'] = Auth::id();
        $data['all_day']    = true;

        AcademicCalendar::create($data);

        return response()->json(['status' => true, 'message' => 'Event add ho gaya.']);
    }

    public function update(Request $request, AcademicCalendar $academicCalendar)
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'start_date'       => ['required', 'date'],
            'end_date'         => ['nullable', 'date', 'after_or_equal:start_date'],
            'event_type_id'    => ['required', 'exists:event_types,id'],
        ]);

        $data['updated_by'] = Auth::id();

        $academicCalendar->update($data);

        return response()->json(['status' => true, 'message' => 'Event update ho gaya.']);
    }

    public function calendarView()
    {
        $canManage = auth()->user()->can('manage-academics');

        $events = AcademicCalendar::with('eventType')->get()->map(function ($event) {
            return [
                'id'    => $event->id,
                'title' => $event->title,
                'start' => \Carbon\Carbon::parse($event->start_date)->format('Y-m-d'),
                'end'   => \Carbon\Carbon::parse($event->end_date)->addDay()->format('Y-m-d'),
                'color' => optional($event->eventType)->color ?? '#0d6efd',
                'extendedProps' => [
                    'academic_year_id' => $event->academic_year_id,
                    'event_type_id'    => $event->event_type_id,
                    'description'      => $event->description,
                    'start_date'       => \Carbon\Carbon::parse($event->start_date)->format('Y-m-d'),
                    'end_date'         => \Carbon\Carbon::parse($event->end_date)->format('Y-m-d'),
                ],
            ];
        });

        $academicYears = AcademicYear::all();
        $eventTypes    = EventType::all();

        return view('admin.academic_calendar.calendar', compact('events', 'academicYears', 'eventTypes', 'canManage'));
    }

    public function destroy(AcademicCalendar $academicCalendar)
    {
        $academicCalendar->delete();

        return response()->json(['status' => true, 'message' => 'Event delete ho gaya.']);
    }

    public function toggleStatus(AcademicCalendar $academicCalendar)
    {
        $academicCalendar->update(['status' => ! $academicCalendar->status]);

        return response()->json(['status' => true, 'message' => 'Status update ho gaya.']);
    }
}
