<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SchoolTiming;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SchoolTimingController extends Controller
{
    public function index()
    {
        $timings = SchoolTiming::where('status', 1)->orderByDesc('is_active')->orderBy('season_name')->get();
        return view('admin.school_timing.index', compact('timings'));
    }
 
    private function rules($id = null)
    {
        return [
            'season_name'         => ['required', 'string', 'max:100', Rule::unique('school_timings', 'season_name')->ignore($id)],
            'start_time'          => 'required|date_format:H:i',
            'end_time'            => 'required|date_format:H:i|after:start_time',
            'late_grace_minutes'  => 'required|integer|min:0|max:120',
            'attendance_summary_time' => 'nullable|date_format:H:i|after:start_time|before:end_time',
        ];
    }

    private function messages()
    {
        return [
            'season_name.unique'                => 'This season name already exists.',
            'attendance_summary_time.after'     => 'Summary time must be after start time.',
            'attendance_summary_time.before'    => 'Summary time must be before end time.',
        ];
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules(), $this->messages());
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
 
        SchoolTiming::create($validator->validated());
 
        return response()->json(['message' => 'School timing added successfully.']);
    }
 
    public function update(Request $request, $id)
    {
        $timing = SchoolTiming::findOrFail($id);
 
        $validator = Validator::make($request->all(), $this->rules($id), $this->messages());
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $timing->update($validator->validated());
 
        return response()->json(['message' => 'School timing updated successfully.']);
    }
 
    // Sirf ek row active ho sakti hai — baaki sab automatically off
    public function setActive($id)
    {
        DB::transaction(function () use ($id) {
            SchoolTiming::where('is_active', true)->update(['is_active' => false]);
            SchoolTiming::findOrFail($id)->update(['is_active' => true]);
        });
 
        return response()->json(['message' => 'Active school timing updated.']);
    }
 
    // Hard delete nahi — sirf status off/on (history-safe)
    public function status($id)
    {
        $timing = SchoolTiming::findOrFail($id);
        $timing->update(['status' => $timing->status ? 0 : 1]);
 
        return response()->json(['message' => 'Status updated.']);
    }
}
