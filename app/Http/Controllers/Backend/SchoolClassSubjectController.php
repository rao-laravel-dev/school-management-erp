<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;

class SchoolClassSubjectController extends Controller
{
    // Assigned classes aur unke subjects ki list dikhane k liye
    public function indexClassSubjects()
    {
        // Yahan 'with' bhi zaroori hai taake subjects show hon
        // Aur 'orderBy' se classes sequence mein aayengi
        $classes = SchoolClass::with('subjects')->orderBy('id', 'asc')->get();

        return view('admin.school_class_subject.index_sch_cls_sub', compact('classes'));
    }
    // End Method

    // Naya subject assign krne ka form
    public function assignClassSubjectsForm()
    {
        // Status check ke sath, taake sirf kaam ki cheezein dikhein
        $classes  = SchoolClass::where('status', 1)->orderBy('id', 'asc')->get();
        $subjects = Subject::where('status', 1)->get();

        // Agar Groups table exist karti hai toh load karein, warna empty collection
        $groups   = class_exists('App\Models\Group') ? Group::where('status', 1)->get() : collect();

        return view('admin.school_class_subject.assign_sch_cls_sub', compact('classes', 'subjects', 'groups'));
    }
    // End Method

    // Mapping ko database (pivot table) me save krne k liye
    public function saveClassSubjectsMapping(Request $request)
{
    // 1. Validation
    $request->validate([
        'class_id'    => 'required|array',
        'subject_ids' => 'required|array',
        'group_id'    => 'nullable', 
    ]);

    $classIds = $request->class_id; 
    
    // 2. Har selected class ke liye loop chalayein
    foreach ($classIds as $classId) {
        $class = SchoolClass::findOrFail($classId);
        
        // 3. Pehle detach logic (Safe removal)
        $query = $class->subjects();
        
        if ($request->filled('group_id')) {
            $query->wherePivot('group_id', $request->group_id);
        } else {
            // Agar group_id null hai to jahan pivot mein group_id null hai, wahi hatao
            $query->wherePivotNull('group_id');
        }
        $query->detach();

        // 4. Attach logic (Data preparation)
        $syncData = [];
        foreach ($request->subject_ids as $subjectId) {
            $syncData[$subjectId] = [
                'group_id'   => $request->group_id ?: null, // Empty string ko null mein convert karega
                'full_marks' => 100, 
                'pass_marks' => 33,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 5. Naya data attach karein
        $class->subjects()->attach($syncData);
    }

    return redirect()->route('school-class-subjects.index')
                     ->with(['message' => 'Bulk mapping successful!', 'alert-type' => 'success']);
}
    // End Method

    // Edit form
    public function editClassSubjectsMapping($id)
    {
        $class = SchoolClass::with('subjects')->findOrFail($id);
        $subjects = Subject::all();

        // YEH LINE ADD KI HAI: Saari classes fetch karne ke liye
        $classes = SchoolClass::all();

        // Pehle se assigned subjects ki sirf IDs nikalne k liye taake checkbox checked ho sakein
        $assignedSubjects = $class->subjects->pluck('id')->toArray();

        // compact me 'classes' variable ko lazmi pass kiya hai
        return view('admin.school_class_subject.edit_sch_cls_sub', compact('class', 'subjects', 'assignedSubjects', 'classes'));
    }
    // End Method

    public function updateClassSubjectsMapping(Request $request, $id)
{
    // 1. Validation
    $request->validate([
        'subject_ids' => 'required|array',
        'group_id'    => 'nullable|exists:groups,id',
    ]);

    $class = SchoolClass::findOrFail($id);

    // 2. Pivot Extra Fields Format (Subjects ko group_id ke sath map karna)
    $syncData = [];
    foreach ($request->subject_ids as $subjectId) {
        $syncData[$subjectId] = [
            'group_id'   => $request->group_id ?? null,
            'full_marks' => 100, 
            'pass_marks' => 33,
            'updated_at' => now(),
        ];
    }

    // 3. Perfect Logic: 
    // Pehle us class ke us specific group ke purane subjects hatao (Detach)
    $class->subjects()->wherePivot('group_id', $request->group_id)->detach();

    // Phir naye subjects ko us group ke sath attach karo (Attach)
    $class->subjects()->attach($syncData);

    return redirect()->route('school-class-subjects.index')->with([
        'message'    => 'Subjects mapping updated successfully!',
        'alert-type' => 'success'
    ]);
}
// End Method


    public function ClassSubjectStatus($id)
    {
        try {
            $class = SchoolClass::findOrFail($id);

            // Status flip logic
            $class->status = $class->status == 1 ? 0 : 1;
            $class->save();

            return response()->json([
                'status' => $class->status,
                'message' => 'Class assignment status updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while updating status.'
            ], 500);
        }
    }
    // End Method

    /**
     * Remove All Subject Mappings From a Class
     */
    public function DeleteClassSubjectsMapping($id)
    {
        try {
            $class = SchoolClass::findOrFail($id);

            // Many-to-Many pivot relationship se saare linked subjects detach kar diye
            $class->subjects()->detach();

            // Toastr notification trigger for success
            $notification = [
                'message' => 'Class subjects assignment removed successfully!',
                'alert-type' => 'success'
            ];

            return redirect()->route('school-class-subjects.index')->with($notification);
        } catch (\Exception $e) {
            $notification = [
                'message' => 'Failed to remove assignment configuration.',
                'alert-type' => 'error'
            ];

            return redirect()->back()->with($notification);
        }
    }
    // End Method
}
