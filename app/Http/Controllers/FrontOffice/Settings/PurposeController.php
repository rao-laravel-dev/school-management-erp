<?php

namespace App\Http\Controllers\FrontOffice\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purpose;
use Exception;

class PurposeController extends Controller
{
    public function index()
    {
        $purposes = Purpose::latest()->get();

        return view('admin.front-office.settings.purpose.index', compact('purposes'));
    }


    // Validation messages mein column ki jagah form label dikhao
    private function attributeNames(): array
    {
        return [
            'purpose'     => 'Purpose',
            'description' => 'Description',
        ];
    }


    public function store(Request $request)
    {
        // Validation fail hone par ye automatically back redirect karega aur errors session mein daal dega
        $validated = $request->validate([
            'purpose'     => 'required|string|max:255|unique:purposes,name',
            'description' => 'nullable|string',
        ], [], $this->attributeNames());

        try {
            Purpose::create([
                'name'     => $request->purpose,
                'description' => $request->description,
            ]);

            return redirect()->back()->with('success', 'Purpose added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }


    public function edit($id)
    {
        $purpose = Purpose::findOrFail($id);

        return response()->json($purpose);
    }


    public function update(Request $request, $id)
    {
        $purpose = Purpose::findOrFail($id);

        $request->validate([
            'purpose' => 'required|string|max:255|unique:purposes,name,' . $id,
            'description'   => 'nullable|string',
        ], [], $this->attributeNames());


        try {

            $purpose->update([
                'name' => $request->purpose,
                'description'   => $request->description,
            ]);


            return redirect()
                ->back()
                ->with('success', 'Purpose updated successfully!');
        } catch (Exception $e) {

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }


    public function PurposeDestroy($id)
    {
        try {

            $purpose = Purpose::withCount(['enquiries', 'visitors'])->findOrFail($id);

            // DB FK cascade hai: in-use purpose delete hua to uske enquiries/visitors bhi chale jate, isliye block
            if ($purpose->enquiries_count > 0 || $purpose->visitors_count > 0) {
                return response()->json(['message' => 'Cannot delete! This purpose is already in use.'], 422);
            }

            $purpose->delete();

            // AJAX DELETE: JSON reply, page JS toastr + reload karta hai
            return response()->json(['message' => 'Purpose deleted successfully!']);
        } catch (Exception $e) {

            return response()->json(['message' => 'Delete failed: ' . $e->getMessage()], 500);
        }
    }
}
