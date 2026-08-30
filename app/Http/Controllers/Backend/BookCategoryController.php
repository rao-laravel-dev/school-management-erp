<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BookCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookCategoryController extends Controller
{
    public function index()
    {
        $bookCategories = BookCategory::latest()->get();
        return view('admin.book_category.index', compact('bookCategories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:book_categories,name',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        BookCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => 1,
        ]);

        return response()->json(['message' => 'Book Category added successfully.']);
    }

    public function update(Request $request, $id)
    {
        $bookCategory = BookCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:book_categories,name,' . $id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bookCategory->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Book Category updated successfully.']);
    }

    public function destroy($id)
    {
        $bookCategory = BookCategory::findOrFail($id);

        if ($bookCategory->books()->exists()) {
            return response()->json(['message' => 'Cannot delete category — books are linked to it.'], 422);
        }

        $bookCategory->delete();

        return response()->json(['message' => 'Book Category deleted successfully.']);
    }

    public function toggleStatus($id)
    {
        $bookCategory = BookCategory::findOrFail($id);
        $bookCategory->update(['status' => !$bookCategory->status]);

        return redirect()->route('book_category.index')->with('success', 'Status updated successfully.');
    }
}