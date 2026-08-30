<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ClassController extends Controller
{
    // 1. All Classes View
    public function AllClass()
    {
        // Yahan 'withCount' add kar dein
        $classes = SchoolClass::with(['mappedSections'])
            ->withCount(['enrollments' => function ($query) {
                $query->where('enroll_status', 1); // Sirf active students
            }])
            ->orderBy('numeric_name', 'ASC') // Aapne pehle use kiya tha
            ->get();

        return view('admin..classes.index_class', compact('classes'));
    }

    // 2. Add Class View
    public function AddClass()
    {
        return view('admin..classes.create_class');
    }

    // 3. Store Class

    public function StoreClass(Request $request)
    {
        // 1. Validation Tight + Custom Messages
        $request->validate([
            'name' => 'required|string|max:255|unique:school_class,name',
            'numeric_name' => 'required|integer|unique:school_class,numeric_name',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'The class name is mandatory.',
            'name.unique' => 'This class name already exists.',
            'numeric_name.required' => 'Numeric name is required.',
            'numeric_name.unique' => 'A class with this numeric identity already exists.',
        ]);

        // 2. Background Generation (Ab count ya saal ka koi jhanjhat nahi)
        $className = $request->name;
        $slug = Str::slug($className);
        $classCode = 'CLS-' . $request->numeric_name; // e.g., CLS-10

        // 3. Eloquent Create (Mutators trigger honge)
        SchoolClass::create([
            'name' => $className,
            'numeric_name' => $request->numeric_name,
            'class_code' => $classCode,
            'slug' => $slug,
            'description' => $request->description,
            'status' => $request->status ?? 0,
        ]);

        // 4. Standard Toastr Notification
        $notification = [
            'message' => 'Class Created Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('adminclasses.index')->with($notification);
    }
    // 4. Edit Class View
    public function EditClass($id)
    {
        $class = SchoolClass::findOrFail($id);
        return view('admin..classes.edit_class', compact('class'));
    }

    // 5. Update Class
    // Method argument mein $id lazmi dalkein
    public function UpdateClass(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:school_class,name,' . $id,
            'numeric_name' => 'required|numeric',
        ], [
            'name.required' => 'Class name is required.',
            'name.unique' => 'This class name is already taken.',
        ]);

        // Naya format generate karein (Edit ke waqt bhi)
        $currentYear = date('Y');
        $nextYear = date('y', strtotime('+1 year'));
        $session = $currentYear . '-' . $nextYear;

        // Serial wahi purana use karein ya naya, behtar hai naya session format save ho
        $generatedCode = 'CLS/' . $session . '/' . $request->numeric_name . '/' . str_pad($id, 3, '0', STR_PAD_LEFT);

        SchoolClass::findOrFail($id)->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'class_code' => $generatedCode, // Ye line add karein taake code update ho
            'numeric_name' => $request->numeric_name,
            'description' => $request->description,
            'status' => $request->status ?? 0,
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        $notification = array(
            'message' => 'Class Updated Successfully!',
            'alert-type' => 'info'
        );

        return redirect()->route('adminclasses.index')->with($notification);
    }

    // Status Update (AJAX Friendly)
    public function UpdateClassStatus($id)
    {
        $class = SchoolClass::findOrFail($id);
        $class->status = $class->status == 1 ? 0 : 1;
        $class->save();

        return response()->json([
            'status' => $class->status,
            'message' => 'Class status updated successfully!'
        ]);
    }

    // 6. Delete Class
    public function ClassDestroy($id)
    {
        SchoolClass::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Class Deleted Successfully',
            'alert-type' => 'error' // Toastr error color
        );

        return redirect()->back()->with($notification);
    }
}
