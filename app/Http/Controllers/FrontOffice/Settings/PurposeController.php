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


    public function store(Request $request)
    {
        // Validation fail hone par ye automatically back redirect karega aur errors session mein daal dega
        $validated = $request->validate([
            'purpose'     => 'required|string|max:255|unique:purposes,name',
            'description' => 'nullable|string',
        ]);

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
        ]);


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

            $purpose = Purpose::findOrFail($id);
            $purpose->delete();

            return redirect()
                ->back()
                ->with('success', 'Purpose deleted successfully!');
        } catch (Exception $e) {

            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
