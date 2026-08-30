<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Section;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SchoolClassSectionController extends Controller
{
    /**
     * Display Mapping Form and Table List
     */
    public function index(Request $request)
    {
        $selectedClassId = $request->query('class_id');
        $classes = SchoolClass::where('status', 1)->get();
        $sections = Section::where('status', 1)->get();

        // Sahi Tarika:
        $mapped_classes = SchoolClass::with(['mappedSections' => function ($query) {
            // Pivot id ki jagah sections table ka 'name' use karein
            $query->orderBy('sections.name', 'ASC');
        }])
            ->orderBy('name', 'ASC') // Class name sort karne ke liye
            ->get();

        return view('admin..school_class_section.index', compact('classes', 'sections', 'mapped_classes', 'selectedClassId'));
    }

    /**
     * Store / Assign Sections to a Class
     */
    public function store(Request $request)
    {
        $request->validate([
            'school_class_id' => 'required|exists:school_class,id',
            'section_ids'     => 'required|array|min:1',
            'section_ids.*'   => 'exists:sections,id',
        ], [
            'school_class_id.required' => 'Please select a class.',
            'section_ids.required'     => 'Select at least one section to map.',
        ]);

        try {
            $class = SchoolClass::findOrFail($request->school_class_id);

            // Sync without detaching taake purane records delete na hon, sirf naye add hon
            $class->mappedSections()->syncWithoutDetaching($request->section_ids);

            return redirect()->back()->with([
                'message'    => 'Sections mapped to Class successfully!',
                'alert-type' => 'success'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message'    => 'Something went wrong: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * Fetch mapped sections via AJAX for full control / inline edit
     */
    public function edit($id)
    {
        // 1. Data fetch karein
        $mapping = SchoolClass::with('mappedSections')->findOrFail($id);
        $classes = SchoolClass::all();
        $sections = Section::all();

        // 2. IMPORTANT: Yahan 'return' mein view pass karein, na ke data
        return view('admin..school_class_section.edit', compact('mapping', 'classes', 'sections'));
    }

    /**
     * Update/Override existing section mappings for a specific class
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'section_ids'   => 'required|array|min:1',
            'section_ids.*' => 'exists:sections,id',
        ]);

        try {
            $class = SchoolClass::findOrFail($id);
            $class->mappedSections()->sync($request->section_ids);

            return redirect()->route('adminschool_class_section.index')->with([
                'message'    => 'Section mapping updated successfully!', // Updated toastr text
                'alert-type' => 'success'
            ]);
        } catch (Exception $e) {
            return redirect()->back()->with([
                'message'    => 'Something went wrong: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * Remove individual section from a class mapping
     */
    public function destroy($id)
    {
        // $id yahan direct pivot table (school_class_section) ka record id hai
        DB::table('school_class_section')->where('id', $id)->delete();

        return redirect()->back()->with([
            'message'    => 'Section disconnected from this class mapping.',
            'alert-type' => 'warning'
        ]);
    }

    /**
     * Toggle Pivot Status via AJAX
     */
    public function toggleStatus($id)
    {
        // Pehle record find karein
        $mapping = DB::table('school_class_section')->where('id', $id)->first();

        if (!$mapping) {
            return response()->json(['success' => false, 'message' => 'Mapping not found'], 404);
        }

        // Status toggle karein
        $newStatus = ($mapping->status == 1) ? 0 : 1;

        DB::table('school_class_section')
            ->where('id', $id)
            ->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated to ' . ($newStatus == 1 ? 'Active' : 'Inactive')
        ]);
    }
}
