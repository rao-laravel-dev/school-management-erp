<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SkillAssessmentArea;
use App\Models\SkillCategory;
use Illuminate\Http\Request;

class SkillAssessmentAreaController extends Controller
{
    public function index()
    {
        $areas = SkillAssessmentArea::with('category')->latest()->get();
        $categories = SkillCategory::active()->get();
        return view('admin.skill_assessment_area.index', compact('areas', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:skill_categories,id',
            'name'        => 'required|string|max:255',
        ]);

        SkillAssessmentArea::create([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'status'      => $request->status ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Skill Assessment Area added successfully.',
        ]);
    }

    public function show($id)
    {
        $area = SkillAssessmentArea::findOrFail($id);
        return response()->json($area);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|exists:skill_categories,id',
            'name'        => 'required|string|max:255',
        ]);

        $area = SkillAssessmentArea::findOrFail($id);
        $area->update([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'status'      => $request->status ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Skill Assessment Area updated successfully.',
        ]);
    }

    public function destroy($id)
    {
        $area = SkillAssessmentArea::findOrFail($id);

        if ($area->studentSkillMarks()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete — marks are already recorded against this area.',
            ]);
        }

        $area->delete();

        return response()->json([
            'success' => true,
            'message' => 'Skill Assessment Area deleted successfully.',
        ]);
    }
}
