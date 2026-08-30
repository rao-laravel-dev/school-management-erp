<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\IdCardTemplate;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Http\Request;

class PrintIdCardController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::where('status', 1)->get();
        $templates = IdCardTemplate::where('status', 1)->get();

        return view('admin.id_card_template.print', compact('classes', 'templates'));
    }

    public function getSectionsByClass($classId)
    {
        $sections = Section::whereHas('schoolClasses', function ($q) use ($classId) {
            $q->where('school_class_id', $classId);
        })->get(['id', 'name']);

        // Fallback: agar Section->SchoolClass relation direct hai
        if ($sections->isEmpty()) {
            $sections = Section::where('school_class_id', $classId)->get(['id', 'name']);
        }

        return response()->json($sections);
    }

    // naya method — students list AJAX
    public function getStudents($classId, $sectionId)
    {
        $enrollments = Enrollment::with('student')
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->get();

        $students = $enrollments->map(function ($enrollment) {
            $student = $enrollment->student;
            return [
                'enrollment_id' => $enrollment->id,
                'admission_no'  => $student->admission_no,
                'name'          => trim($student->first_name . ' ' . $student->last_name),
                'roll_no'       => $enrollment->roll_no,
                'photo'         => $student->photo
                    ? asset('uploads/students/' . $student->photo)
                    : asset('images/no-image.png'),
            ];
        });

        return response()->json($students);
    }

    // update — ab poori class ki bajaye sirf selected enrollment_ids
    public function generate(Request $request)
    {
        $request->validate([
            'template_id'      => 'required|exists:id_card_templates,id',
            'enrollment_ids'   => 'required|array|min:1',
            'enrollment_ids.*' => 'exists:enrollments,id',
        ]);

        $template = IdCardTemplate::findOrFail($request->template_id);

        $enrollments = Enrollment::with(['student.parent', 'student.house', 'schoolClass', 'section'])
            ->whereIn('id', $request->enrollment_ids)
            ->get();

        return view('admin.id_card_template.card', compact('template', 'enrollments'));
    }
    
}
