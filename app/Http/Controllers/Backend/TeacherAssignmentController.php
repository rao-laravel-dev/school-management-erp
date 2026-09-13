<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeacherAssignmentController extends Controller
{
    // 1. Assignments List Page
    // TeacherAssignmentController.php mein ye method add karein
    public function AllTeacherClass()
    {
        // Yahan hum wahi logic call kar rahe hain jo AddTeacherClass mein hai
        $teachers = Teacher::all();
        $academicYears = AcademicYear::where('status', 1)->get();
        $classes = SchoolClass::where('status', 1)->get();
        $sections = collect();

        $assignments = TeacherAssignment::with(['teacher', 'schoolClass', 'section', 'academicYear'])->latest()->get();

        // Kyunki aapne kaha ke create page pe ye method call hoga:
        return view('admin.teacher_assign.index', compact('teachers', 'sections', 'academicYears', 'classes', 'assignments'));
    }
    // 1. Load Form & Table View
    public function AddTeacherClass()
    {
        $teachers = Teacher::all();
        $academicYears = AcademicYear::where('status', 1)->get();
        $classes = SchoolClass::where('status', 1)->get();
        $sections = collect(); // Dropdown shuru me khali rahega

        // 🔴 FIX: Ab direct 'schoolClass' relation load hoga bina kisi crash k!
        $assignments = TeacherAssignment::with(['teacher', 'schoolClass', 'section', 'academicYear'])->latest()->get();

        return view('admin.teacher_assign.create', compact('teachers', 'sections', 'academicYears', 'classes', 'assignments'));
    }

    // 2. Fetch Sections via AJAX (No Change Here)
    public function getSectionsByClass(Request $request)
    {
        $sections = DB::table('school_class_section')
            ->join('sections', 'school_class_section.section_id', '=', 'sections.id')
            ->where('school_class_section.school_class_id', $request->class_id)
            ->select('sections.id', 'sections.name')
            ->get();

        return response()->json($sections);
    }

    // class_id select hone par us class ke subjects laayein (class_subject se)
    public function getSubjectsByClass(Request $request)
    {
        $class = SchoolClass::find($request->class_id);
        $subjects = ClassSubject::with('subject')
            ->where('class_id', $request->class_id)
            ->get()
            ->map(fn($cs) => ['id' => $cs->id, 'name' => $cs->subject->name ?? 'N/A']);

        return response()->json([
            'has_subjects' => $class->has_subjects ?? true,
            'subjects' => $subjects,
        ]);
    }
    // End Method

    // 3. Store Data Direct Pipeline
    public function StoreTeacherClass(Request $request)
    {
        try {
            $request->validate([
                'academic_year_id' => 'required|exists:academic_years,id',
                'class_id'         => 'required|exists:school_class,id',
                'section_id'       => 'required|exists:sections,id',
                // Yahan conditional validation lagayi hai
    'class_subject_id' => [
        Rule::requiredIf(function () use ($request) {
            $class = SchoolClass::find($request->class_id);
            return $class && $class->has_subjects == 1;
        }),
        'nullable',
        'exists:class_subjects,id', // Table name check kar lijiyega (class_subjects hai ya class_subject)
    ],
                'teacher_id'       => 'required|array',
                'teacher_id.*'     => 'exists:teachers,id',
            ]);

            $academic_year_id = $request->academic_year_id;
            $class_id         = $request->class_id;
            $section_id       = $request->section_id;
            $class_subject_id = $request->class_subject_id;

            $assignedCount = 0;
            $duplicateCount = 0;

            foreach ($request->teacher_id as $t_id) {
                $exists = TeacherAssignment::where([
                    'academic_year_id' => $academic_year_id,
                    'class_id'         => $class_id,
                    'section_id'       => $section_id,
                    'class_subject_id' => $class_subject_id,
                    'teacher_id'       => $t_id,
                ])->exists();

                if (!$exists) {
                    TeacherAssignment::create([
                        'academic_year_id' => $academic_year_id,
                        'class_id'         => $class_id,
                        'section_id'       => $section_id,
                        'class_subject_id' => $class_subject_id,
                        'teacher_id'       => $t_id,
                    ]);
                    $assignedCount++;
                } else {
                    $duplicateCount++;
                }
            }

            // Logic for Toastr messages
            if ($assignedCount > 0) {
                // Teacher models ka collection get karein (ya aapke pass pehle se ho)
                $assignedTeachers = \App\Models\Teacher::whereIn('id', $request->teacher_id)->get();

                // Map method se pura naam banayen aur implode se join karein
                $teacherNames = $assignedTeachers->map(function ($teacher) {
                    return $teacher->first_name . ' ' . $teacher->last_name;
                })->implode(', ');

                $message = "Successfully assigned: " . $teacherNames;

                if ($duplicateCount > 0) {
                    $message .= " ($duplicateCount teacher(s) were already assigned).";
                }

                return redirect()->back()->with('success', $message);
            } else {
                return redirect()->back()->with('error', 'Selected teacher(s) are already assigned to this class.');
            }
        } catch (\Exception $e) {
            // Agar system mein koi error aata hai
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function EditTeacherClass($id)
    {
        $editData = TeacherAssignment::findOrFail($id);
        $teachers = Teacher::all();
        $academicYears = AcademicYear::where('status', 1)->get();
        $classes = SchoolClass::where('status', 1)->get();

        $sections = DB::table('school_class_section')
            ->join('sections', 'school_class_section.section_id', '=', 'sections.id')
            ->where('school_class_section.school_class_id', $editData->class_id)
            ->select('sections.id', 'sections.name')
            ->get();

        // 👈 naya
        $subjects = ClassSubject::with('subject')
            ->where('class_id', $editData->class_id)
            ->get()
            ->map(fn($cs) => ['id' => $cs->id, 'name' => $cs->subject->name ?? 'N/A']);

        $assignments = TeacherAssignment::with(['teacher', 'schoolClass', 'section', 'academicYear', 'classSubject.subject'])
            ->latest()->get();

        return view('admin.teacher_assign.create', compact('editData', 'teachers', 'academicYears', 'classes', 'sections', 'subjects', 'assignments'));
    }

    public function UpdateTeacherClass(Request $request, $id)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id'         => 'required|exists:school_class,id', // table name check karein
            'section_id'       => 'required|exists:sections,id',
            // Yahan conditional validation lagayi hai
    'class_subject_id' => [
        Rule::requiredIf(function () use ($request) {
            $class = SchoolClass::find($request->class_id);
            return $class && $class->has_subjects == 1;
        }),
        'nullable',
        'exists:class_subjects,id', // Table name check kar lijiyega (class_subjects hai ya class_subject)
    ],
            'teacher_id'       => 'required|exists:teachers,id',
        ]);

        $assignment = TeacherAssignment::findOrFail($id);

        $teacherId = $request->teacher_id[0];
        // Single teacher ke liye direct update
        $assignment->update([
            'academic_year_id' => $request->academic_year_id,
            'class_id'         => $request->class_id,
            'section_id'       => $request->section_id,
            'class_subject_id' => $request->class_subject_id,
            'teacher_id'       => $teacherId,
        ]);

        return redirect()->route('teacher.assign.create')->with('success', 'Assignment updated successfully!');
    }

    // 4. Delete Assignment
    public function DestroyTeacherClass($id)
    {
        $assignment = TeacherAssignment::findOrFail($id);
        $assignment->delete();

        // Wapis usi page par bhein jahan se delete kiya tha
        return redirect()->route('teacher.assign.create')->with('success', 'Assignment Deleted successfully!');
    }
}
