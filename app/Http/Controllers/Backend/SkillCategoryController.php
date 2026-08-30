<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SkillCategory;
use Illuminate\Http\Request;

class SkillCategoryController extends Controller
{
    public function index()
    {
        $categories = SkillCategory::latest()->get();
        return view('admin.skill_category.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:skill_categories,name',
        ]);

        SkillCategory::create([
            'name'   => $request->name,
            'status' => $request->status ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Skill Category added successfully.',
        ]);
    }

    public function show($id)
    {
        $category = SkillCategory::findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:skill_categories,name,' . $id,
        ]);

        $category = SkillCategory::findOrFail($id);
        $category->update([
            'name'   => $request->name,
            'status' => $request->status ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Skill Category updated successfully.',
        ]);
    }

    public function destroy($id)
    {
        $category = SkillCategory::findOrFail($id);

        if ($category->skillAssessmentAreas()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete — Skill Assessment Areas already exist under this category.',
            ]);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Skill Category deleted successfully.',
        ]);
    }
}
