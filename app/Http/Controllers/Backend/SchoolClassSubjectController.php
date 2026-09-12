<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

   public function editClassSubjectsMapping(Request $request, $id)
{
    $class    = SchoolClass::with('subjects')->findOrFail($id);
    $subjects = Subject::where('status', 1)->get();
    $classes  = SchoolClass::where('status', 1)->get();
    $groups   = class_exists('App\Models\Group') ? Group::where('status', 1)->get() : collect();

    // Query string se na milay to seedha pivot table se is class ka actual group_id nikal lein
    $groupId = $request->get('group_id');
    if (is_null($groupId)) {
        $groupId = DB::table('class_subject')->where('class_id', $id)->value('group_id');
    }

    $query = $class->subjects();
    if ($groupId) {
        $query->wherePivot('group_id', $groupId);
    } else {
        $query->wherePivotNull('group_id');
    }
    $assignedSubjects = $query->pluck('subjects.id')->toArray();

    $affectedClassesQuery = DB::table('class_subject')
        ->join('school_class', 'school_class.id', '=', 'class_subject.class_id')
        ->select('school_class.id', 'school_class.name')
        ->distinct();

    if ($groupId) {
        $affectedClassesQuery->where('class_subject.group_id', $groupId);
    } else {
        $affectedClassesQuery->whereNull('class_subject.group_id');
    }
    $affectedClasses = $affectedClassesQuery->get();

    return view('admin.school_class_subject.edit_sch_cls_sub', compact(
        'class', 'subjects', 'assignedSubjects', 'classes', 'groups', 'groupId', 'affectedClasses'
    ));
}
    // End Method

    public function updateClassSubjectsMapping(Request $request, $id)
{
    $request->validate([
        'subject_ids' => 'required|array',
        'group_id'    => 'nullable|exists:groups,id',
    ]);

    $groupId = $request->filled('group_id') ? $request->group_id : null;

    // 1. Is group ke andar is waqt jitni bhi classes assigned hain, unki IDs nikalo
    $query = DB::table('class_subject');
    if ($groupId !== null) {
        $query->where('group_id', $groupId);
    } else {
        $query->whereNull('group_id');
    }
    $classIds = $query->distinct()->pluck('class_id');

    // 2. Current class ($id) bhi list mein zaroor shamil ho (naye group ke case mein)
    $classIds = $classIds->push($id)->unique();

    // 3. Har class pe wahi update apply karo
    foreach ($classIds as $classId) {
        $class = SchoolClass::find($classId);
        if (!$class) continue;

        $q = $class->subjects();
        if ($groupId !== null) {
            $q->wherePivot('group_id', $groupId);
        } else {
            $q->wherePivotNull('group_id');
        }
        $q->detach();

        $syncData = [];
        foreach ($request->subject_ids as $subjectId) {
            $syncData[$subjectId] = [
                'group_id'   => $groupId,
                'full_marks' => 100,
                'pass_marks' => 33,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        $class->subjects()->attach($syncData);
    }

    return redirect()->route('school-class-subjects.index')->with([
        'message'    => 'Subjects mapping updated for all classes in this group!',
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
