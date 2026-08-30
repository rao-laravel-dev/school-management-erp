<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use Illuminate\Http\Request;

class ExamTypeController extends Controller
{
    public function index()
    {
        $examTypes = ExamType::orderBy('name')->get();

        return view('admin.exam_type.index', compact('examTypes'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:exam_types,name',
            'result_scope' => 'required|in:single,cumulative',
        ], [
            'name.required' => 'Exam Type name is required.',
            'name.unique' => 'This Exam Type already exists.',
        ]);

        ExamType::create([
            'name' => $request->name,
            'result_scope' => $request->result_scope,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Exam Type added successfully.',
        ]);
    }
    // End Method

    public function update(Request $request, $id)
    {
        $examType = ExamType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:exam_types,name,' . $examType->id,
            'result_scope' => 'required|in:single,cumulative',
        ], [
            'name.required' => 'Exam Type name is required.',
            'name.unique' => 'This Exam Type already exists.',
        ]);

        $examType->update([
            'name' => $request->name,
            'result_scope' => $request->result_scope,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Exam Type updated successfully.',
        ]);
    }
    // End Method

    public function destroy($id)
    {
        $examType = ExamType::findOrFail($id);

        // Prevent deletion if this exam type is already used by an exam
        if ($examType->exams()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This Exam Type is already in use and cannot be deleted.',
            ]);
        }

        $examType->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Exam Type deleted successfully.',
        ]);
    }
}
