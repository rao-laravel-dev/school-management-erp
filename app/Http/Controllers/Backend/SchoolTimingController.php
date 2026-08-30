<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SchoolTiming;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SchoolTimingController extends Controller
{
    public function index()
    {
        $timings = SchoolTiming::where('status', 1)->orderByDesc('is_active')->orderBy('season_name')->get();
        return view('admin.school_timing.index', compact('timings'));
    }
 
    private function rules()
    {
        return [
            'season_name'         => 'required|string|max:100',
            'start_time'          => 'required|date_format:H:i',
            'end_time'            => 'required|date_format:H:i|after:start_time',
            'late_grace_minutes'  => 'required|integer|min:0|max:120',
        ];
    }
 
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
 
        SchoolTiming::create($validator->validated());
 
        return response()->json(['message' => 'School timing added successfully.']);
    }
 
    public function update(Request $request, $id)
    {
        $timing = SchoolTiming::findOrFail($id);
 
        $validator = Validator::make($request->all(), $this->rules());
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
