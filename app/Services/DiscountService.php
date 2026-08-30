<?php

namespace App\Services;

use App\Models\DiscountPolicy;
use App\Models\StudentDiscount;

class DiscountService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Ek specific fee_type ke liye total discount amount calculate karta hai,
     * multiple discount policy IDs ke against (array ya single ID dono chalenge).
     */
    public function calculateDiscount(float $amount, ?int $feeTypeId, $policyIds = null): float
    {
        if (empty($policyIds)) {
            return 0;
        }

        // Single ID bhi aa sakti hai (backward compatible), array bhi
        $policyIds = is_array($policyIds) ? $policyIds : [$policyIds];
        $policyIds = array_filter($policyIds); // empty/null values hata do

        if (empty($policyIds)) {
            return 0;
        }

        $policies = DiscountPolicy::whereIn('id', $policyIds)
            ->where('status', 1)
            ->get();

        $totalDiscount = 0;

        foreach ($policies as $policy) {
            // policy ka fee_type_id null hai (sab fees pe apply hoti hai) YA is fee_type se match karti hai
            if (!is_null($policy->fee_type_id) && $policy->fee_type_id != $feeTypeId) {
                continue; // ye policy is fee type pe apply nahi hoti, skip karo
            }

            if ($policy->discount_type === 'percentage') {
                $totalDiscount += ($amount * $policy->discount_value) / 100;
            } else {
                $totalDiscount += $policy->discount_value;
            }
        }

        // Discount kabhi bhi original amount se zyada nahi ho sakta
        return round(min($totalDiscount, $amount), 2);
    }

    /**
     * Student ko already assign ki gayi active discount policy IDs
     * (Approve Admission ke waqt recurring fees generate karne ke liye use hota hai)
     */
    public function getStudentPolicyIds(int $studentId): array
    {
        return StudentDiscount::where('student_id', $studentId)
            ->where('status', 1)
            ->pluck('discount_policy_id')
            ->toArray();
    }
}
