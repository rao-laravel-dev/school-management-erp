<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubjectController extends Controller
{
    /// 1. Sab Subjects ko list karne ke liye
    public function AllSubject()
    {
       // Yahan variable ka naam $subjects (plural) hona chahiye
    $subjects = Subject::with(['school_class', 'groups'])->latest()->get();

    // Class badge mein group code dikhane ke liye (id => group_code)
    $groupCodes = Group::pluck('group_code', 'id');

    // View ka path aur variable pass karna
    return view('admin.subjects.index_sub', compact('subjects', 'groupCodes'));
    }

    // 2. Create form dikhane ke liye
  public function AddSubject()
{
    // 1. Classes fetch karein
    $classes = SchoolClass::where('status', 1)->orderBy('numeric_name', 'ASC')->get();

    // 'with' use karne se saari classes ek hi baar mein fetch ho jayengi
    $groups = Group::where('status', 1)->orderBy('name', 'ASC')->get();

    // 3. Dono variables ko compact mein pass karein
    return view('admin.subjects.create_sub', compact('classes', 'groups'));
}

    // 3. Data database mein save karne ke liye
public function StoreSubject(Request $request)
{
    // 1. Clean Validation (Sirf Subject ki apni properties)
    $request->validate([
        'name' => 'required|string|max:255|unique:subjects,name',
        'type' => 'required|in:Theory,Practical,Both',
        'description' => 'nullable|string',
    ], [
        'name.required' => 'The subject name is required.',
        'name.unique' => 'This subject name already exists in the system.',
        'type.required' => 'Please specify if the subject is Theory, Practical, or Both.',
    ]);

    // 2. Safe & Standard Code Generation (No Class/Group dependencies)
    $subjectPart = strtoupper(substr(trim($request->name), 0, 4)); // e.g., MATH, ENGL
    $typePart    = strtoupper($request->type); // THEORY, PRACTICAL
    $yearPart    = date('Y'); // 2026

    // Clean format string bina kisi extra characters ke
    $subjectCode = "{$subjectPart}-{$typePart}-{$yearPart}";

    // Handle duplicate codes safely if any
    if (Subject::where('subject_code', $subjectCode)->exists()) {
        $subjectCode .= '-' . strtoupper(Str::random(3));
    }

    // 3. Create Pure Generic Subject
    Subject::create([
        'name'         => $request->name,
        'subject_code' => $subjectCode,
        'type'         => $request->type,
        'description'  => $request->description,
        'status'       => $request->status ?? 0, // Bootstrap Switch fix
    ]);

    return redirect()->route('subjects.index')->with([
        'message' => 'Generic Subject Created Successfully!',
        'alert-type' => 'success'
    ]);
}

    // Edit Page Show Karne Ke Liye
public function EditSubject($id) // Ya jo bhi aapke method ka naam hai
{
    // 1. Subject ko uski numeric ID se dhoondhein
    $subject = Subject::findOrFail($id);
    
    // 2. Saari active classes aur GLOBAL groups ko fetch karein (bina kisi purani relationship ke)
    $classes = SchoolClass::where('status', 1)->orderBy('numeric_name', 'ASC')->get();
    $groups = Group::where('status', 1)->orderBy('name', 'ASC')->get();
    
    // 3. Data ko safely view file mein pass kar dein
    return view('admin.subjects.edit_sub', compact('subject', 'classes', 'groups'));
}

    // Data Update Karne Ke Liye
    public function UpdateSubject(Request $request, $id)
{
    $subject = Subject::findOrFail($id);

    // 1. Validation with English Messages
    $request->validate([
        'name'     => 'required|string|max:255',
        'type'     => 'required|in:Theory,Practical,Both',
    ], [
        'name.required'     => 'Please provide the subject name.',
        'type.required'     => 'You must select a subject type.',
    ]);

    // 2. Update Main Subject Data
    $subject->update([
        'name'        => $request->name,
        'type'        => $request->type,
        'description' => $request->description,
        'status'      => $request->has('status') ? 1 : 0, // Unchecked switch request mein nahi aata, isliye off = 0
    ]);

    // 3. Class/Group links yahan se nahi badalte: class_subject rows (classes, groups, marks, class-wise codes)
    // sirf Class Subjects Assignment page se manage hoti hain. Pehle yahan detach hota tha jo sab mappings mita deta tha.

    // 4. Success Notification
    $notification = [
        'message' => 'Subject updated successfully!',
        'alert-type' => 'success'
    ];

    return redirect()->route('subjects.index')->with($notification);
}

    public function SubjectDestroy($id)
{
    $subject = Subject::findOrFail($id);

    // 1. Pivot table se relationship khatam karein
    $subject->school_class()->detach();

    // 2. Subject delete karein
    $subject->delete();

    $notification = [
        'message' => 'Subject Deleted Successfully',
        'alert-type' => 'danger'
    ];

    return redirect()->back()->with($notification);
}

    public function SubjectStatus($id)
{
    $subject = Subject::findOrFail($id);
    
    // Toggle logic
    $subject->status = $subject->status == 1 ? 0 : 1;
    $subject->save();

    // AJAX response (Aapki blade file ke JS logic ke mutabiq)
    return response()->json([
        'status' => $subject->status,
        'message' => 'Subject status updated successfully!'
    ]);
}
}
