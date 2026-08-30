<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Subject;
use Illuminate\Http\Request;

class GroupSubjectController extends Controller
{
    // 1. Assigned groups aur unke subjects ki list dikhane k liye
   public function indexGroupSubjects()
{
    // Ab model mein relation mojood hai, eager loading bina kisi error ke chalegi
    $groups = Group::with(['subjects', 'school_class'])->get();
    return view('admin.group_subject.index_grp_sub', compact('groups'));
}

    // 2. Naya subject group me assign krne ka form
    public function assignGroupSubjectsForm()
    {
        $groups = Group::all();
        $subjects = Subject::all();

        return view('admin.group_subject.assign_grp_sub', compact('groups', 'subjects'));
    }

    // 3. Mapping ko database (pivot table) me save krne k liye
    public function saveGroupSubjectsMapping(Request $request)
{
    // 1. Validation
    $request->validate([
        'group_id'      => 'required|exists:groups,id',
        'subject_ids'   => 'required|array',
        'subject_ids.*' => 'exists:subjects,id',
    ], [
        'group_id.required'   => 'Please select an academic group.',
        'subject_ids.required' => 'Please select at least one subject.',
    ]);

    $group = Group::findOrFail($request->group_id);

    // 2. Pivot Extra Data Prepare Karein
    // Kyunki 'class_subject' table ko 'class_id' lazmi chahiye, hum dynamic ya dummy context save karte hain
    // Ya agar aapke form par class_id hai toh $request->class_id use karein.
    $syncData = [];
    foreach ($request->subject_ids as $subjectId) {
        $syncData[$subjectId] = [
            'class_id'   => $request->class_id ?? 1, // Agar form par class_id add kar dein toh behtareen hai
            'full_marks' => 100,
            'pass_marks' => 33,
        ];
    }

    // 3. Smart Sync to prevent wiping other group classes
    $group->subjects()->syncWithoutDetaching($syncData);

    $notification = [
        'message' => 'Subjects assigned to group successfully.',
        'alert-type' => 'success'
    ];

    return redirect()->route('admin.group-subjects.index')->with($notification);
}

    // 4. Edit form jahan group selected hoga aur admin chahe to group change bhi kr sake
    public function editGroupSubjectsMapping($id)
    {
        $group = Group::with('subjects')->findOrFail($id);
        $groups = Group::all();
        $subjects = Subject::all();

        // Pehle se assigned subjects ki IDs nikalne k liye
        $assignedSubjects = $group->subjects->pluck('id')->toArray();

        return view('admin.group_subject.edit_grp_sub', compact('group', 'groups', 'subjects', 'assignedSubjects'));
    }

    // 5. AJAX Status Toggle for Groups
public function GroupSubjectStatus($id)
{
    $group = Group::findOrFail($id);
    
    // Status invert/toggle logic
    $group->status = $group->status == 1 ? 0 : 1;
    $group->save();

    return response()->json([
        'status' => $group->status,
        'message' => 'Group mapping status updated successfully.'
    ]);
}

// 6. Delete Assignment / Detach all subjects from group
public function DeleteGroupSubjectMapping($id)
{
    $group = Group::findOrFail($id);
    
    // Pivot table se saare linked subjects ko detach kar dega
    $group->subjects()->detach();

    $notification = [
        'message' => 'Group subjects mapping removed successfully.',
        'alert-type' => 'success'
    ];

    return redirect()->route('admin.group-subjects.index')->with($notification);
}
}
