<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\BillingSetting;
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
    public function calculateDiscount(float $amount, ?int $feeTypeId, $policyIds = null, ?int $studentId = null): float
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

        // Student diya ho to fixed policy ka per-student amount (policy_id => amount), ek hi query
        $studentAmounts = $studentId
            ? StudentDiscount::where('student_id', $studentId)->where('status', 1)->whereNotNull('amount')->pluck('amount', 'discount_policy_id')
            : collect();

        $totalDiscount = 0;

        foreach ($policies as $policy) {
            // policy ka fee_type_id null hai (sab fees pe apply hoti hai) YA is fee_type se match karti hai
            if (!is_null($policy->fee_type_id) && $policy->fee_type_id != $feeTypeId) {
                continue; // ye policy is fee type pe apply nahi hoti, skip karo
            }

            if ($policy->discount_type === 'percentage') {
                $totalDiscount += ($amount * $policy->discount_value) / 100;
            } else {
                $totalDiscount += (float) ($studentAmounts->get($policy->id) ?: $policy->discount_value);
            }
        }

        // Discount kabhi bhi original amount se zyada nahi ho sakta
        return round(min($totalDiscount, $amount), 2);
    }

    /**
     * Current academic year ki billing settings se scholarship category (null = define nahi, check skip)
     */
    public function scholarshipCategoryId(): ?int
    {
        $yearId = AcademicYear::where('is_current', 1)->value('id');
        if (!$yearId) {
            return null;
        }

        $categoryId = BillingSetting::where('academic_year_id', $yearId)->value('scholarship_category_id');

        return $categoryId ? (int) $categoryId : null;
    }

    public function isScholarship(DiscountPolicy $policy, ?int $scholarshipCategoryId): bool
    {
        return $scholarshipCategoryId !== null
            && $policy->policy_type === 'category'
            && (int) $policy->category_id === $scholarshipCategoryId;
    }

    /**
     * Policies (collection) mein sibling + scholarship dono hon to error message, warna null
     */
    public function conflictMessage($policies, ?int $scholarshipCategoryId): ?string
    {
        $hasSibling     = $policies->contains(fn($p) => $p->policy_type === 'sibling');
        $hasScholarship = $policies->contains(fn($p) => $this->isScholarship($p, $scholarshipCategoryId));

        return ($hasSibling && $hasScholarship)
            ? 'Sibling discount and scholarship cannot be applied together.'
            : null;
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
