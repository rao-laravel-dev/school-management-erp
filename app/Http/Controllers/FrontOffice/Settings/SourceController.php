<?php

namespace App\Http\Controllers\FrontOffice\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Source;
use Exception;

class SourceController extends Controller
{
    public function index()
    {
        $sources = Source::latest()->get();
        return view('admin.front-office.settings.source.index', compact('sources'));
    }

    // Validation messages mein column ki jagah form label dikhao
    private function attributeNames(): array
    {
        return [
            'source'      => 'Source Name',
            'description' => 'Description',
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            // unique validation add ki hai taake duplicate entries na hon
            'source'      => 'required|string|max:255|unique:sources,name',
            'description' => 'nullable|string',
        ], [], $this->attributeNames());

        try {
            Source::create([
                'name'        => $request->source,
                'description' => $request->description,
            ]);

            return redirect()->back()->with('success', 'Source added successfully!');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $sources = Source::findOrFail($id);

        // Agar aap AJAX se call kar rahe hain toh json return karein
        return response()->json($sources);
    }

    public function update(Request $request, $id)
    {
        $source = Source::findOrFail($id);

        $request->validate([
            // unique validation mein id ko ignore kiya hai
            'source'      => 'required|string|max:255|unique:sources,name,' . $id,
            'description' => 'nullable|string',
        ], [], $this->attributeNames());

        try {
            $source->update([
                'name'        => $request->source,
                'description' => $request->description,
            ]);

            return redirect()->back()->with('success', 'Source updated successfully!');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function SourceDestroy($id)
    {
        try {
            $source = Source::withCount(['enquiries', 'complaints'])->findOrFail($id);

            // Enquiries ya complaints is source se juray hon to delete block
            if ($source->enquiries_count > 0 || $source->complaints_count > 0) {
                return response()->json(['message' => 'Cannot delete! This source is already in use.'], 422);
            }

            $source->delete();

            // AJAX DELETE: JSON reply, page JS toastr + reload karta hai
            return response()->json(['message' => 'Source deleted successfully!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Delete failed: ' . $e->getMessage()], 500);
        }
    }
}
