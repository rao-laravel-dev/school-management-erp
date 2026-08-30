<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamType;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::with(['examType', 'academicYear'])
            ->latest()
            ->get();
 
        $examTypes = ExamType::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $currentAcademicYear = AcademicYear::where('is_current', 1)->first();
 
        return view('admin.exam.index', compact('exams', 'examTypes', 'academicYears', 'currentAcademicYear'));
    }
 
    public function save(Request $request)
    {
        $request->validate([
            'exam_type_id' => 'required|exists:exam_types,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|string|max:255',
        ], [
            'exam_type_id.required' => 'Exam Type is required.',
            'academic_year_id.required' => 'Academic Year is required.',
            'name.required' => 'Exam name is required.',
        ]);
 
        Exam::create([
            'exam_type_id' => $request->exam_type_id,
            'academic_year_id' => $request->academic_year_id,
            'name' => $request->name,
            'publish' => $request->has('publish'),
            'publish_result' => $request->has('publish_result'),
        ]);
 
        return response()->json([
            'status' => 'success',
            'message' => 'Exam added successfully.',
        ]);
    }
 
    public function update(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);
 
        $request->validate([
            'exam_type_id' => 'required|exists:exam_types,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|string|max:255',
        ], [
            'exam_type_id.required' => 'Exam Type is required.',
            'academic_year_id.required' => 'Academic Year is required.',
            'name.required' => 'Exam name is required.',
        ]);
 
        $exam->update([
            'exam_type_id' => $request->exam_type_id,
            'academic_year_id' => $request->academic_year_id,
            'name' => $request->name,
            'publish' => $request->has('publish'),
            'publish_result' => $request->has('publish_result'),
        ]);
 
        return response()->json([
            'status' => 'success',
            'message' => 'Exam updated successfully.',
        ]);
    }
 
    public function destroy($id)
    {
        $exam = Exam::findOrFail($id);
 
        // Prevent deletion if this exam already has schedules or grades tied to it
        if ($exam->schedules()->exists() || $exam->marksGrades()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This Exam already has schedules/grades and cannot be deleted.',
            ]);
        }
 
        $exam->delete();
 
        return response()->json([
            'status' => 'success',
            'message' => 'Exam deleted successfully.',
        ]);
    }
}
