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

    public function store(Request $request)
    {
        $request->validate([
            // unique validation add ki hai taake duplicate entries na hon
            'source'      => 'required|string|max:255|unique:sources,name',
            'description' => 'nullable|string',
        ]);

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
        ]);

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
            Source::findOrFail($id)->delete();
            return redirect()->back()->with('success', 'Source deleted successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
