<?php

namespace App\Http\Controllers\Backend;

use App\Models\StudentCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentCategoryController extends Controller
{

    public function index()
    {
        $categories = StudentCategory::latest()->get();
        return view('admin.student_category.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|unique:student_categories,name',
            'description' => 'nullable|string',
            'status'      => 'required|boolean',
        ]);

        StudentCategory::create($request->only('name', 'description', 'status'));

        return redirect()->back()->with('success', 'Category added successfully');
    }

    public function update(Request $request, StudentCategory $studentCategory)
    {
        $request->validate([
            'name'        => 'required|string|unique:student_categories,name,' . $studentCategory->id,
            'description' => 'nullable|string',
            'status'      => 'required|boolean',
        ]);

        $studentCategory->update($request->only('name', 'description', 'status'));

        return redirect()->back()->with('success', 'Category updated successfully');
    }

    public function destroy(StudentCategory $studentCategory)
    {
        $studentCategory->delete();
        return redirect()->back()->with('success', 'Category deleted successfully');
    }

    public function toggleStatus(StudentCategory $studentCategory)
    {
        $studentCategory->status = $studentCategory->status == 1 ? 0 : 1;
        $studentCategory->save();

        return response()->json([
            'status'  => $studentCategory->status,
            'message' => 'Status updated successfully',
        ]);
    }
}
