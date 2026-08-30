<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CopyOldLessonController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::orderByDesc('id')->get(['id', 'name']);
        $classes = SchoolClass::where('status', 1)->orderBy('name')->get(['id', 'name']);

        return view('admin.copy_old_lesson.index', compact('academicYears', 'classes'));
    }

    public function getSections($classId)
    {
        $sections = DB::table('school_class_section')
            ->join('sections', 'sections.id', '=', 'school_class_section.section_id')
            ->where('school_class_section.school_class_id', $classId)
            ->where('school_class_section.status', 1)
            ->select('sections.id', 'sections.name')
            ->orderBy('sections.name')
            ->get();

        return response()->json(['success' => true, 'data' => $sections]);
    }

    public function getSubjects($classId)
    {
        $subjects = DB::table('class_subject')
            ->join('subjects', 'subjects.id', '=', 'class_subject.subject_id')
            ->where('class_subject.class_id', $classId)
            ->select('subjects.id', 'subjects.name')
            ->orderBy('subjects.name')
            ->get();

        return response()->json(['success' => true, 'data' => $subjects]);
    }

    public function getTopics(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id'         => 'required|exists:school_class,id',
            'section_id'       => 'required|exists:sections,id',
            'subject_id'       => 'required|exists:subjects,id',
        ]);

        $lessons = Lesson::with(['topics' => fn($q) => $q->where('status', 1)->orderBy('name')])
            ->where('academic_year_id', $request->academic_year_id)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('subject_id', $request->subject_id)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        if ($lessons->isEmpty()) {
            return response()->json(['success' => true, 'empty' => true]);
        }

        $academicYears = AcademicYear::orderByDesc('id')->get(['id', 'name']);
        $classes = SchoolClass::where('status', 1)->orderBy('name')->get(['id', 'name']);

        $html = view('admin.copy_old_lesson.result_partial', compact('lessons', 'academicYears', 'classes'))->render();

        return response()->json(['success' => true, 'empty' => false, 'html' => $html]);
    }

    public function copy(Request $request)
    {
        $request->validate([
            'source_academic_year_id' => 'required|exists:academic_years,id',
            'source_class_id'         => 'required|exists:school_class,id',   // school_classes → school_class
            'source_section_id'       => 'required|exists:sections,id',
            'source_subject_id'       => 'required|exists:subjects,id',
            'target_academic_year_id' => 'required|exists:academic_years,id',
            'target_class_id'         => 'required|exists:school_class,id',   // school_classes → school_class
            'target_section_id'       => 'required|exists:sections,id',
            'target_subject_id'       => 'required|exists:subjects,id',
            'lesson_ids'              => 'required|array|min:1',
            'lesson_ids.*'            => 'exists:lessons,id',
        ]);

        if (
            $request->source_academic_year_id == $request->target_academic_year_id &&
            $request->source_class_id == $request->target_class_id &&
            $request->source_section_id == $request->target_section_id &&
            $request->source_subject_id == $request->target_subject_id
        ) {
            return response()->json(['success' => false, 'message' => 'Source and target criteria cannot be the same.'], 422);
        }

        $copied = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($request->lesson_ids as $lessonId) {
                $oldLesson = Lesson::with(['topics' => function ($q) {
                    $q->where('status', 1);
                }])->find($lessonId);

                if (!$oldLesson) {
                    continue;
                }

                $exists = Lesson::where('academic_year_id', $request->target_academic_year_id)
                    ->where('class_id', $request->target_class_id)
                    ->where('section_id', $request->target_section_id)
                    ->where('subject_id', $request->target_subject_id)
                    ->where('name', $oldLesson->name)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $newLesson = Lesson::create([
                    'name'              => $oldLesson->name,
                    'academic_year_id'  => $request->target_academic_year_id,
                    'class_id'          => $request->target_class_id,
                    'section_id'        => $request->target_section_id,
                    'subject_id'        => $request->target_subject_id,
                    'status'            => 1,
                ]);

                foreach ($oldLesson->topics as $topic) {
                    Topic::create([
                        'academic_year_id' => $request->target_academic_year_id,
                        'class_id'         => $request->target_class_id,
                        'section_id'       => $request->target_section_id,
                        'subject_id'       => $request->target_subject_id,
                        'lesson_id'        => $newLesson->id,
                        'name'             => $topic->name,
                        'status'           => 1,
                    ]);
                }

                $copied++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Something went wrong. Nothing was copied.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "{$copied} lesson(s) copied, {$skipped} skipped (already exist in target).",
        ]);
    }
}
