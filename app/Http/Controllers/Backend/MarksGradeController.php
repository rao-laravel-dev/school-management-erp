<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\MarksGrade;
use Illuminate\Http\Request;

class MarksGradeController extends Controller
{
    // Returns JSON: exam name + its grades list (used to populate the modal via AJAX)
    public function index($examId)
    {
        $exam = Exam::findOrFail($examId);
        $grades = MarksGrade::where('exam_id', $examId)
            ->orderBy('min_percentage', 'desc')
            ->get();

        return response()->json([
            'exam_name' => $exam->name,
            'grades' => $grades,
        ]);
    }

    public function save(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'grade_name' => 'required|string|max:50',
            'remark' =>  'nullable|string|max:255',
            'min_percentage' => 'required|numeric|min:0|max:100',
            'max_percentage' => 'required|numeric|min:0|max:100|gt:min_percentage',
        ], [
            'exam_id.required' => 'Exam is required.',
            'grade_name.required' => 'Grade name is required.',
            'min_percentage.required' => 'Minimum percentage is required.',
            'max_percentage.required' => 'Maximum percentage is required.',
            'max_percentage.gt' => 'Maximum percentage must be greater than minimum percentage.',
        ]);

        // prevent overlapping ranges within the same exam
        $overlap = MarksGrade::where('exam_id', $request->exam_id)
            ->where(function ($q) use ($request) {
                $q->whereBetween('min_percentage', [$request->min_percentage, $request->max_percentage])
                  ->orWhereBetween('max_percentage', [$request->min_percentage, $request->max_percentage])
                  ->orWhere(function ($q2) use ($request) {
                      $q2->where('min_percentage', '<=', $request->min_percentage)
                         ->where('max_percentage', '>=', $request->max_percentage);
                  });
            })->exists();

        if ($overlap) {
            return response()->json([
                'status' => 'error',
                'message' => 'This percentage range overlaps with an existing grade for this exam.',
            ], 422);
        }

        MarksGrade::create($request->only([
            'exam_id', 'grade_name', 'remark', 'min_percentage', 'max_percentage',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Grade added successfully.',
        ]);
    }

    public function update(Request $request, $id)
    {
        $grade = MarksGrade::findOrFail($id);

        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'grade_name' => 'required|string|max:50',
            'remark' =>  'nullable|string|max:255',
            'min_percentage' => 'required|numeric|min:0|max:100',
            'max_percentage' => 'required|numeric|min:0|max:100|gt:min_percentage',
        ], [
            'exam_id.required' => 'Exam is required.',
            'grade_name.required' => 'Grade name is required.',
            'min_percentage.required' => 'Minimum percentage is required.',
            'max_percentage.required' => 'Maximum percentage is required.',
            'max_percentage.gt' => 'Maximum percentage must be greater than minimum percentage.',
        ]);

        $overlap = MarksGrade::where('exam_id', $request->exam_id)
            ->where('id', '!=', $grade->id)
            ->where(function ($q) use ($request) {
                $q->whereBetween('min_percentage', [$request->min_percentage, $request->max_percentage])
                  ->orWhereBetween('max_percentage', [$request->min_percentage, $request->max_percentage])
                  ->orWhere(function ($q2) use ($request) {
                      $q2->where('min_percentage', '<=', $request->min_percentage)
                         ->where('max_percentage', '>=', $request->max_percentage);
                  });
            })->exists();

        if ($overlap) {
            return response()->json([
                'status' => 'error',
                'message' => 'This percentage range overlaps with an existing grade for this exam.',
            ], 422);
        }

        $grade->update($request->only([
            'exam_id', 'grade_name', 'remark', 'min_percentage', 'max_percentage',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Grade updated successfully.',
        ]);
    }

    public function destroy($id)
    {
        $grade = MarksGrade::findOrFail($id);
        $grade->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Grade deleted successfully.',
        ]);
    }
}
