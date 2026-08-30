<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\DiscountPolicy;
use App\Models\FeeType;
use App\Models\StudentCategory;
use Illuminate\Http\Request;

class DiscountPolicyController extends Controller
{
    public function index()
    {
        $policies   = DiscountPolicy::with(['feeType', 'category'])->latest()->get();
        $feeTypes   = FeeType::where('is_discountable', true)->get();
        $categories = StudentCategory::where('status', 1)->get();

        return view('admin.discount_policies.index', compact('policies', 'feeTypes', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'policy_type'     => 'required|in:category,sibling,other',
            'category_id'     => 'required_if:policy_type,category|nullable|exists:student_categories,id',
            'trigger_value'   => 'required_if:policy_type,sibling|nullable|integer|min:1',
            'discount_type'   => 'required|in:percentage,fixed',
            'discount_value'  => 'required|numeric|min:0',
            'fee_type_id'     => 'nullable|exists:fee_types,id',
        ]);

        if ($validated['policy_type'] !== 'category') {
            $validated['category_id'] = null;
        }
        if ($validated['policy_type'] !== 'sibling') {
            $validated['trigger_value'] = null;
        }

        $validated['status'] = 1;

        DiscountPolicy::create($validated);

        return response()->json(['success' => 'Discount policy added successfully!']);
    }

    public function update(Request $request, $id)
    {
        $discountPolicy = DiscountPolicy::findOrFail($id);

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'policy_type'     => 'required|in:category,sibling,other',
            'category_id'     => 'required_if:policy_type,category|nullable|exists:student_categories,id',
            'trigger_value'   => 'required_if:policy_type,sibling|nullable|integer|min:1',
            'discount_type'   => 'required|in:percentage,fixed',
            'discount_value'  => 'required|numeric|min:0',
            'fee_type_id'     => 'nullable|exists:fee_types,id',
        ]);

        if ($validated['policy_type'] !== 'category') {
            $validated['category_id'] = null;
        }
        if ($validated['policy_type'] !== 'sibling') {
            $validated['trigger_value'] = null;
        }

        $discountPolicy->update($validated);

        return response()->json(['message' => 'Discount policy updated successfully!']);
    }

    public function destroy($id)
    {
        $discountPolicy = DiscountPolicy::findOrFail($id);
        $discountPolicy->delete();

        return redirect()->route('discount_policies.index')
            ->with('success', 'Discount policy deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $discountPolicy = DiscountPolicy::findOrFail($id);
        $discountPolicy->update(['status' => !$discountPolicy->status]);

        return response()->json(['success' => true, 'status' => $discountPolicy->status]);
    }
}