<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function AllSection()
    {
        $sections = Section::latest()->get();
        return view('admin.sections.index_sec', compact('sections'));
    }

    public function AddSection()
    {
        // numeric_name (1, 2, 3) par order lagane se sequence perfect ayega
        $classes = SchoolClass::where('status', 1)->orderBy('numeric_name', 'ASC')->get();

        return view('admin.sections.create_sec', compact('classes'));
    }

    public function StoreSection(Request $request)
    {
        // 1. Clean Single Column Validation (Ab class_id ka check khatam)
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:sections,name', // Section Name poori table mein independent unique hoga
            ],
            'capacity'    => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Section name is mandatory.',
            'name.unique'   => 'This section name already exists in the system.',
        ]);

        // 2. Auto-Generate Section Code (e.g., SEC-A)
        $cleanName = strtoupper(trim($request->name));
        $sectionCode = 'SEC-' . current(explode(' ', $cleanName));

        // Double check code uniqueness safely
        if (Section::where('section_code', $sectionCode)->exists()) {
            $sectionCode .= '-' . rand(10, 99); // Agar SEC-A pehle se ho to SEC-A-12 ban jaye safely
        }

        // 3. Database Create (class_id removed)
        Section::create([
            'name'         => $request->name,
            'section_code' => $sectionCode,
            'capacity'     => $request->capacity,
            'description' => $request->description,
            'status'       => $request->status ?? 1, // Default active rakhna behtar ha
        ]);

        // 4. Toastr Response
        $notification = [
            'message'    => 'Section Created Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('sections.index')->with($notification);
    }
    // End Method

    public function EditSection($id)
    {
        $section = Section::findOrFail($id);
        $classes = SchoolClass::where('status', 1)->orderBy('numeric_name', 'ASC')->get();
        return view('admin.sections.edit_sec', compact('section', 'classes'));
    }

    public function UpdateSection(Request $request, $id)
    {
        // 1. Validation Cleaned (class_id reference completely removed)
        $request->validate([
            'name' => 'required|string|max:255|unique:sections,name,' . $id,
            'capacity'    => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Section name is mandatory.',
            'name.unique'   => 'This section name is already registered.',
        ]);

        $section = Section::findOrFail($id);

        // 2. Regenerate Clean Section Code on Update base (e.g., SEC-A)
        $cleanName = strtoupper(trim($request->name));
        $generatedCode = 'SEC-' . current(explode(' ', $cleanName));

        // 3. Update execution without class_id
        $section->update([
            'name'         => $request->name,
            'section_code' => $generatedCode,
            'capacity'     => $request->capacity,
            'description' => $request->description,
            'status'       => $request->status ?? 1,
        ]);

        return redirect()->route('sections.index')->with([
            'message'    => 'Section Updated Successfully',
            'alert-type' => 'info'
        ]);
    }
    // End Method

    public function UpdateSectionStatus($id)
    {
        $section = Section::findOrFail($id);
        $section->status = $section->status == 1 ? 0 : 1;
        $section->save();

        return response()->json([
            'status' => $section->status,
            'message' => $section->status == 1 ? 'Section Activated' : 'Section Deactivated'
        ]);
    }

    public function SectionDestroy($id)
    {
        Section::findOrFail($id)->delete();
        return redirect()->back()->with([
            'message' => 'Section Deleted Successfully',
            'alert-type' => 'success'
        ]);
    }
}
