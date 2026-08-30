<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('class_id') || $request->has('section_id') || $request->has('subject_id') || $request->has('lesson_id')) {
            if (!$request->filled('class_id') && !$request->filled('subject_id') && !$request->filled('lesson_id')) {
                return redirect()->route('topic.index')->withErrors([
                    'filter' => 'Please select at least Class, Subject or Lesson to search.'
                ])->withInput();
            }
        }

        $currentAcademicYear = AcademicYear::where('is_current', 1)->first();

        $classes = SchoolClass::where('status', 1)->get();

        $query = Topic::with(['schoolClass', 'section', 'subject', 'lesson'])
            ->where('academic_year_id', $currentAcademicYear->id ?? null);   // <-- add this

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('lesson_id')) {
            $query->where('lesson_id', $request->lesson_id);
        }
        $topics = $query->get();

        return view('admin.topic.index', compact('classes', 'topics'));
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
        $subjects = ClassSubject::with('subject')
            ->where('class_id', $classId)
            ->get()
            ->pluck('subject')
            ->filter()
            ->values();

        return response()->json($subjects);
    }

    public function getLessonsBySubject($subjectId)
    {
        $currentAcademicYear = AcademicYear::where('is_current', 1)->first();

        $lessons = Lesson::where('subject_id', $subjectId)
            ->where('academic_year_id', $currentAcademicYear->id ?? null)
            ->where('status', 1)
            ->get(['id', 'name']);

        return response()->json($lessons);
    }

    public function edit($id)
    {
        $topic = Topic::findOrFail($id);
        return response()->json($topic);
    }

    public function save(Request $request)
    {
        $academicYear = AcademicYear::where('is_current', 1)->first();

        if (!$academicYear) {
            return response()->json(['success' => false, 'message' => 'No current academic year set.'], 422);
        }

        $request->validate([
            'class_id'   => 'required|exists:school_class,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'lesson_id'  => 'required|exists:lessons,id',
            'name'       => 'required|array|min:1',
            'name.*'     => 'required|string|max:255',
            'id'         => 'nullable|exists:topics,id',
        ]);

        if ($request->filled('id')) {
            $topic = Topic::findOrFail($request->id);
            $topic->update([
                'class_id'   => $request->class_id,
                'section_id' => $request->section_id,
                'subject_id' => $request->subject_id,
                'lesson_id'  => $request->lesson_id,
                'name'       => $request->name[0],
            ]);

            return response()->json(['success' => true, 'message' => 'Topic updated successfully']);
        }

        foreach ($request->name as $name) {
            Topic::create([
                'academic_year_id' => $academicYear->id,
                'class_id'   => $request->class_id,
                'section_id' => $request->section_id,
                'subject_id' => $request->subject_id,
                'lesson_id'  => $request->lesson_id,
                'name'       => $name,
                'status'     => 1,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Topic(s) added successfully']);
    }

    public function destroy($id)
    {
        Topic::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Topic deleted successfully']);
    }

    public function toggleStatus($id)
    {
        $topic = Topic::findOrFail($id);
        $topic->status = !$topic->status;
        $topic->save();

        return response()->json(['success' => true, 'status' => $topic->status]);
    }
}
