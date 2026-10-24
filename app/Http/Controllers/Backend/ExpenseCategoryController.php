<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseCategoryController extends Controller
{
    // Validation messages mein form labels ("The Category Name field is required.")
    private function categoryAttributes(): array
    {
        return ['name' => 'Category Name'];
    }

    public function index()
    {
        $categories = ExpenseCategory::orderBy('id', 'desc')->get();
        return view('admin.expense_categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:expense_categories,name',
        ], [], $this->categoryAttributes());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        ExpenseCategory::create(['name' => $request->name]);

        return response()->json(['message' => 'Category added successfully.']);
    }

    public function update(Request $request, $id)
    {
        $category = ExpenseCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $id,
        ], [], $this->categoryAttributes());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category->update(['name' => $request->name]);

        return response()->json(['message' => 'Category updated successfully.']);
    }

    public function destroy($id)
    {
        ExpenseCategory::findOrFail($id)->delete();
        return response()->json(['message' => 'Category deleted successfully.']);
    }
}
