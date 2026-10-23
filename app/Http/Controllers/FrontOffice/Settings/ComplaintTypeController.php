<?php

namespace App\Http\Controllers\FrontOffice\Settings;

use App\Http\Controllers\Controller;
use App\Models\ComplaintType;
use Illuminate\Http\Request;
use Exception;

class ComplaintTypeController extends Controller
{
    public function index()
    {
        $complaintTypes = ComplaintType::latest()->get();
        return view('admin.front-office.settings.complaint-type.index', compact('complaintTypes'));
    }

    // Validation messages mein column ki jagah form label dikhao
    private function attributeNames(): array
    {
        return [
            'name'        => 'Complaint Type',
            'description' => 'Description',
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ], [], $this->attributeNames());

        ComplaintType::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Complaint Type added successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ], [], $this->attributeNames());

        $type = ComplaintType::findOrFail($id);
        $type->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Complaint Type updated successfully!');
    }

   public function ComplaintTypeDestroy($id)
{
    try {
        // 1. ComplaintType ko dhoondhein
        $type = ComplaintType::withCount('complaints')->findOrFail($id);

        // 2. Check karein ki kya is type se koi complaints attached hain?
        if ($type->complaints_count > 0) {
            return response()->json(['message' => 'Cannot delete! This complaint type is already in use.'], 422);
        }

        // 3. Agar koi complaint attached nahi hai, toh delete karein
        $type->delete();

        // AJAX DELETE: JSON reply, page JS toastr + reload karta hai
        return response()->json(['message' => 'Complaint Type deleted successfully!']);

    } catch (Exception $e) {
        // Agar database level par koi error aata hai
        return response()->json(['message' => 'Delete failed: ' . $e->getMessage()], 500);
    }
}
}