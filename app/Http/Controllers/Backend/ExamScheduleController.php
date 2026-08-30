<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExamScheduleController extends Controller
{
    public function index(Request $request)
    {
        $exams = Exam::with('examType')->latest()->get();
        $classes = SchoolClass::where('status', 1)->orderBy('numeric_name')->get();

        $selectedExamId = $request->exam_id;
        $selectedClassId = $request->class_id;

        $schedules = collect();

        if ($selectedExamId && $selectedClassId) {
            $schedules = ExamSchedule::with('subject')
                ->where('exam_id', $selectedExamId)
                ->where('class_id', $selectedClassId)
                ->orderBy('date')
                ->orderBy('time_from')
                ->get();
        }

        return view('admin.exam_schedule.index', compact(
            'exams',
            'classes',
            'schedules',
            'selectedExamId',
            'selectedClassId'
        ));
    }

    // Route: /exam_schedule/get-subjects/{classId}
    public function getSubjectsByClass($classId, Request $request)
    {
        $class = SchoolClass::findOrFail($classId);
        $subjects = $class->subjects()->get(); // pivot: full_marks, pass_marks

        if ($request->exam_id) {
            $scheduledQuery = ExamSchedule::where('exam_id', $request->exam_id)
                ->where('class_id', $classId);

            if ($request->exclude_id) {
                $scheduledQuery->where('id', '!=', $request->exclude_id);
            }

            $scheduledSubjectIds = $scheduledQuery->pluck('subject_id')->toArray();
            $subjects = $subjects->whereNotIn('id', $scheduledSubjectIds)->values();
        }

        return response()->json($subjects);
    }

    // Route: /exam_schedule/get-default-marks/{classId}/{subjectId}
    public function getDefaultMarks($classId, $subjectId)
    {
        $pivot = ClassSubject::where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->first();

        return response()->json([
            'max_marks' => $pivot->full_marks ?? 100,
            'passing_marks' => $pivot->pass_marks ?? 33,
        ]);
    }

    public function save(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:school_class,id',
            'subject_id' => [
                'required',
                'exists:subjects,id',
                Rule::unique('exam_schedules')->where(function ($query) use ($request) {
                    return $query->where('exam_id', $request->exam_id)
                        ->where('class_id', $request->class_id);
                }),
            ],
            'date' => 'required|date',
            'time_from' => 'required',
            'time_to' => 'required|after:time_from',
            'max_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:0|lte:max_marks',
        ], [
            'exam_id.required' => 'Exam is required.',
            'class_id.required' => 'Class is required.',
            'subject_id.required' => 'Subject is required.',
            'subject_id.unique' => 'This subject is already scheduled for the selected Exam and Class.',
            'date.required' => 'Date is required.',
            'time_from.required' => 'Start time is required.',
            'time_to.required' => 'End time is required.',
            'time_to.after' => 'End time must be after start time.',
            'max_marks.required' => 'Max marks is required.',
            'passing_marks.required' => 'Passing marks is required.',
            'passing_marks.lte' => 'Passing marks cannot exceed max marks.',
        ]);

        ExamSchedule::create($request->only([
            'exam_id',
            'class_id',
            'subject_id',
            'date',
            'time_from',
            'time_to',
            'max_marks',
            'passing_marks',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Exam Schedule added successfully.',
        ]);
    }

    public function update(Request $request, $id)
    {
        $schedule = ExamSchedule::findOrFail($id);

        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:school_class,id',
            'subject_id' => [
                'required',
                'exists:subjects,id',
                Rule::unique('exam_schedules')->where(function ($query) use ($request) {
                    return $query->where('exam_id', $request->exam_id)
                        ->where('class_id', $request->class_id);
                })->ignore($schedule->id),
            ],
            'date' => 'required|date',
            'time_from' => 'required',
            'time_to' => 'required|after:time_from',
            'max_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:0|lte:max_marks',
        ], [
            'exam_id.required' => 'Exam is required.',
            'class_id.required' => 'Class is required.',
            'subject_id.required' => 'Subject is required.',
            'subject_id.unique' => 'This subject is already scheduled for the selected Exam and Class.',
            'date.required' => 'Date is required.',
            'time_from.required' => 'Start time is required.',
            'time_to.required' => 'End time is required.',
            'time_to.after' => 'End time must be after start time.',
            'max_marks.required' => 'Max marks is required.',
            'passing_marks.required' => 'Passing marks is required.',
            'passing_marks.lte' => 'Passing marks cannot exceed max marks.',
        ]);

        $schedule->update($request->only([
            'exam_id',
            'class_id',
            'subject_id',
            'date',
            'time_from',
            'time_to',
            'max_marks',
            'passing_marks',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Exam Schedule updated successfully.',
        ]);
    }

    public function destroy($id)
    {
        $schedule = ExamSchedule::findOrFail($id);
        $schedule->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Exam Schedule deleted successfully.',
        ]);
    }
}
