<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Http\Request;

class LessonController extends Controller
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
        $query = Lesson::with(['schoolClass', 'section', 'subject'])
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

        $lessons = $query->latest()->get();

        return view('admin.lesson.index', compact('classes', 'lessons'));
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

    public function edit($id)
    {
        $lesson = Lesson::findOrFail($id);
        return response()->json($lesson);
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
            'name'       => 'required|array|min:1',
            'name.*'     => 'required|string|max:255',
            'id'         => 'nullable|exists:lessons,id',
        ]);

        if ($request->filled('id')) {
            $lesson = Lesson::findOrFail($request->id);
            $lesson->update([
                'class_id'   => $request->class_id,
                'section_id' => $request->section_id,
                'subject_id' => $request->subject_id,
                'name'       => $request->name[0],
            ]);

            return response()->json(['success' => true, 'message' => 'Lesson updated successfully']);
        }

        foreach ($request->name as $name) {
            Lesson::create([
                'academic_year_id' => $academicYear->id,
                'class_id'   => $request->class_id,
                'section_id' => $request->section_id,
                'subject_id' => $request->subject_id,
                'name'       => $name,
                'status'     => 1,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Lesson(s) added successfully']);
    }

    public function destroy($id)
    {
        Lesson::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Lesson deleted successfully']);
    }

    public function toggleStatus($id)
    {
        $lesson = Lesson::findOrFail($id);
        $lesson->status = !$lesson->status;
        $lesson->save();

        return response()->json(['success' => true, 'status' => $lesson->status]);
    }
}
