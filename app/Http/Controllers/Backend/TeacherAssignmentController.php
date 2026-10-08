<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeacherAssignmentController extends Controller
{
    private const RELATIONS = ['teacher.user', 'schoolClass', 'section', 'academicYear'];

    public function AllTeacherClass()
    {
        $assignments = TeacherAssignment::with(self::RELATIONS)->latest()->get();

        return view('admin.teacher_assign.index', compact('assignments'));
    }

    public function AddTeacherClass()
    {
        $teachers      = Teacher::whereHas('user', fn ($q) => $q->where('status', 1))->get();
        $academicYears = AcademicYear::where('status', 1)->get();
        $classes       = SchoolClass::where('status', 1)->get();
        $sections      = collect();
        $assignments   = TeacherAssignment::with(self::RELATIONS)->latest()->get();

        return view('admin.teacher_assign.create', compact('teachers', 'sections', 'academicYears', 'classes', 'assignments'));
    }

    public function getSectionsByClass(Request $request)
    {
        $sections = DB::table('school_class_section')
            ->join('sections', 'school_class_section.section_id', '=', 'sections.id')
            ->where('school_class_section.school_class_id', $request->class_id)
            ->select('sections.id', 'sections.name')
            ->get();

        return response()->json($sections);
    }

    private function rules(Request $request): array
    {
        return [
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id'         => ['required', Rule::exists((new SchoolClass)->getTable(), 'id')],
            'section_id'       => [
                'required',
                Rule::exists('school_class_section', 'section_id')
                    ->where('school_class_id', $request->class_id),
            ],
            'teacher_id'       => 'required|exists:teachers,id',
        ];
    }

    private function teacherAlreadyInCharge(Request $request, $ignoreId = null): bool
{
    return TeacherAssignment::where('academic_year_id', $request->academic_year_id)
        ->where('teacher_id', $request->teacher_id)
        ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
        ->exists();
}

    public function StoreTeacherClass(Request $request)
{
    $request->validate($this->rules($request));

    $exists = TeacherAssignment::where([
        'academic_year_id' => $request->academic_year_id,
        'class_id'         => $request->class_id,
        'section_id'       => $request->section_id,
    ])->exists();

    if ($exists) {
        return redirect()->back()->withInput()
            ->with('error', 'Is class/section ka Class Teacher pehle se assign hai.');
    }

    if ($this->teacherAlreadyInCharge($request)) {
        return redirect()->back()->withInput()
            ->with('error', 'Ye teacher is session mein pehle se kisi aur class ka Class Teacher hai.');
    }

    TeacherAssignment::create($request->only('academic_year_id', 'class_id', 'section_id', 'teacher_id'));

    $teacher = Teacher::find($request->teacher_id);

    return redirect()->back()
        ->with('success', 'Class Teacher assigned: ' . $teacher->first_name . ' ' . $teacher->last_name);
}
// End Method

    public function EditTeacherClass($id)
    {
        $editData      = TeacherAssignment::findOrFail($id);
        // Active teachers + current assigned teacher (inactive ho to bhi selected rahe)
        $teachers      = Teacher::with('user')
            ->where(fn ($q) => $q->whereHas('user', fn ($u) => $u->where('status', 1))
                ->orWhere('id', $editData->teacher_id))
            ->get();
        $academicYears = AcademicYear::where('status', 1)->get();
        $classes       = SchoolClass::where('status', 1)->get();

        $sections = DB::table('school_class_section')
            ->join('sections', 'school_class_section.section_id', '=', 'sections.id')
            ->where('school_class_section.school_class_id', $editData->class_id)
            ->select('sections.id', 'sections.name')
            ->get();

        $assignments = TeacherAssignment::with(self::RELATIONS)->latest()->get();

        return view('admin.teacher_assign.create', compact('editData', 'teachers', 'academicYears', 'classes', 'sections', 'assignments'));
    }

    public function UpdateTeacherClass(Request $request, $id)
{
    $request->validate($this->rules($request));

    $assignment = TeacherAssignment::findOrFail($id);

    $exists = TeacherAssignment::where([
        'academic_year_id' => $request->academic_year_id,
        'class_id'         => $request->class_id,
        'section_id'       => $request->section_id,
    ])->where('id', '!=', $id)->exists();

    if ($exists) {
        return redirect()->back()->withInput()
            ->with('error', 'Is class/section ka Class Teacher pehle se assign hai.');
    }

    if ($this->teacherAlreadyInCharge($request, $id)) {
        return redirect()->back()->withInput()
            ->with('error', 'Ye teacher is session mein pehle se kisi aur class ka Class Teacher hai.');
    }

    $assignment->update($request->only('academic_year_id', 'class_id', 'section_id', 'teacher_id'));

    return redirect()->route('teacher.assign.create')->with('success', 'Assignment updated successfully!');
}
// End Method

    public function DestroyTeacherClass($id)
    {
        TeacherAssignment::findOrFail($id)->delete();

        return redirect()->route('teacher.assign.create')->with('success', 'Assignment Deleted successfully!');
    }
}