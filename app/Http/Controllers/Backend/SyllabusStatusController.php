<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Topic;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SyllabusStatusController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('class_id') || $request->has('section_id') || $request->has('subject_id')) {
            $request->validate([
                'class_id'   => 'required',
                'section_id' => 'required',
                'subject_id' => 'required',
            ], [
                'class_id.required'   => 'Please select Class',
                'section_id.required' => 'Please select Section',
                'subject_id.required' => 'Please select Subject',
            ]);
        }

        $currentAcademicYear = AcademicYear::where('is_current', 1)->first();

        $classes = SchoolClass::where('status', 1)->get();
        $topics = collect();

        if ($request->filled('class_id') && $request->filled('section_id') && $request->filled('subject_id')) {
            $topics = Topic::with(['schoolClass', 'section', 'subject', 'lesson'])
                ->where('academic_year_id', $currentAcademicYear->id ?? null)
                ->where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->where('subject_id', $request->subject_id)
                ->where('status', 1)
                ->orderBy('lesson_id')
                ->get();
        }

        return view('admin.syllabus_status.index', compact('classes', 'topics'));
    }

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

    public function getSubjectsByClass($classId)
    {
        // class_subject pivot table hi source of truth hai — Lesson/Topic modules jaisa convention
        $subjects = ClassSubject::with('subject')
            ->where('class_id', $classId)
            ->get()
            ->pluck('subject')
            ->filter()
            ->values();

        return response()->json($subjects);
    }

    public function toggleStatus($id)
    {
        $topic = Topic::findOrFail($id);
        $topic->is_completed = !$topic->is_completed;

        if ($topic->is_completed && !$topic->completion_date) {
            $topic->completion_date = now()->format('Y-m-d');
        }

        if (!$topic->is_completed) {
            $topic->completion_date = null;
        }

        $topic->save();

        return response()->json([
            'success' => true,
            'is_completed' => $topic->is_completed,
            'completion_date_raw' => $topic->completion_date ? Carbon::parse($topic->completion_date)->format('Y-m-d') : null,
        ]);
    }

    public function updateCompletionDate(Request $request, $id)
    {
        $request->validate([
            'completion_date' => 'required|date',
        ]);

        $topic = Topic::findOrFail($id);
        $topic->completion_date = $request->completion_date;
        $topic->is_completed = 1; // date set karne ka matlab topic completed hai

        $topic->save();

        return response()->json([
            'success' => true,
            'message' => 'Completion date updated.',
            'completion_date' => Carbon::parse($topic->completion_date)->format('d-M-Y'),
        ]);
    }
}
