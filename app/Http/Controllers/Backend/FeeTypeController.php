<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\FeeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeeTypeController extends Controller
{
    /**
     * Display a listing of fee types.
     */
    public function index()
    {
        $feeTypes = FeeType::latest()->get();
        return view('admin.fee_type.index', compact('feeTypes'));
    }

    /**
     * Show the form for creating a new fee type.
     * (Modal approach hai, is route ki UI mein zarurat nahi,
     * lekin route defined hai to blank/placeholder rakh dete hain)
     */
    public function create()
    {
        return response()->json(['message' => 'Use modal on index page to add fee type.']);
    }

    /**
     * Store a newly created fee type.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255|unique:fee_types,name',
            'frequency'   => 'required|in:one_time,monthly,quarterly,half_yearly,annual',
            'description' => 'nullable|string',
            'is_discountable' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        FeeType::create([
            'name'        => $request->name,
            'frequency'   => $request->frequency,
            'description' => $request->description,
            'is_discountable' => $request->boolean('is_discountable'), // new
        ]);

        return response()->json(['message' => 'Fee type added successfully.']);
    }

    /**
     * Show the form for editing a fee type.
     */
    public function edit($id)
    {
        $feeType = FeeType::findOrFail($id);
        return response()->json($feeType);
    }

    /**
     * Update the specified fee type.
     */
    public function update(Request $request, $id)
    {
        $feeType = FeeType::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255|unique:fee_types,name,' . $feeType->id,
            'frequency'   => 'required|in:one_time,monthly,quarterly,half_yearly,annual',
            'description' => 'nullable|string',
            'is_discountable' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $feeType->update([
            'name'        => $request->name,
            'frequency'   => $request->frequency,
            'description' => $request->description,
            'is_discountable' => $request->boolean('is_discountable'), // new
        ]);

        return response()->json(['message' => 'Fee type updated successfully.']);
    }

    /**
     * Remove the specified fee type.
     */
    public function destroy($id)
    {
        $feeType = FeeType::findOrFail($id);

        // Check karo koi FeeStructure isse linked to nahi hai
        if ($feeType->feeStructures()->exists()) {
            return response()->json([
                'message' => 'Cannot delete — this fee type is linked to one or more fee structures.'
            ], 422);
        }

        $feeType->delete();

        return response()->json(['message' => 'Fee type deleted successfully.']);
    }
}
