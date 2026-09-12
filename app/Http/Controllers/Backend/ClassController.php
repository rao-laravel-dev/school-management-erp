<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClassController extends Controller
{
    public function AllClass()
    {
        $classes = SchoolClass::with(['mappedSections'])
            ->withCount(['enrollments' => function ($query) {
                $query->where('enroll_status', 1);
            }])
            ->orderBy('numeric_name', 'ASC')
            ->get();

        return view('admin.classes.index_class', compact('classes'));
    }

    // AJAX: modal ke liye JSON
    public function EditClass($id)
    {
        $class = SchoolClass::findOrFail($id);
        return response()->json($class);
    }

    public function StoreClass(Request $request)
    {
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

        $className = $request->name;
        $slug = Str::slug($className);
        $classCode = 'CLS-' . str_replace('-', 'N', $request->numeric_name);
        // e.g. CLS-N3

        SchoolClass::create([
            'name' => $className,
            'has_subjects' => $request->has('has_subjects'),
            'numeric_name' => $request->numeric_name,
            'class_code' => $classCode,
            'slug' => $slug,
            'description' => $request->description,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return response()->json(['success' => true, 'message' => 'Class Created Successfully!']);
    }

    public function UpdateClass(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:school_class,name,' . $id,
            'numeric_name' => 'required|numeric',
        ], [
            'name.required' => 'Class name is required.',
            'name.unique' => 'This class name is already taken.',
        ]);

        SchoolClass::findOrFail($id)->update([
            'name' => $request->name,
            'has_subjects' => $request->has('has_subjects'),
            'slug' => Str::slug($request->name),
            'numeric_name' => $request->numeric_name,
            'description' => $request->description,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return response()->json(['success' => true, 'message' => 'Class Updated Successfully!']);
    }

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

    public function ClassDestroy($id)
    {
        SchoolClass::findOrFail($id)->delete();
        return redirect()->back()->with(['message' => 'Class Deleted Successfully', 'alert-type' => 'error']);
    }
}
