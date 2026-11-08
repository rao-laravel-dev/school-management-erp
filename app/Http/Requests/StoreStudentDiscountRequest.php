<?php

namespace App\Http\Requests;

use App\Models\AcademicYear;
use App\Models\DiscountPolicy;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\StudentDiscount;
use App\Services\DiscountService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware (can:manage-discount-policies) permission already check kar raha hai
    }

    public function rules(): array
    {
        return [
            'discount_policy_id' => 'required|integer|exists:discount_policies,id,status,1',
            'amount'             => 'nullable|numeric|min:0.01|max:99999999.99',
            'remarks'            => 'nullable|string|max:500',
        ];
    }

    public function attributes(): array
    {
        return [
            'discount_policy_id' => 'Discount Policy',
            'amount'             => 'Amount',
            'remarks'            => 'Remarks',
        ];
    }

    public function messages(): array
    {
        return [
            'discount_policy_id.required' => 'Please select a discount policy.',
            'discount_policy_id.exists'   => 'The selected discount policy is invalid or inactive.',
            'amount.min'                  => 'The Amount must be greater than 0.',
        ];
    }

    /**
     * Duplicate, sibling vs scholarship conflict aur fixed scholarship amount checks
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $studentId = (int) $this->route('id');
                $policy    = DiscountPolicy::findOrFail($this->input('discount_policy_id'));
                $service   = new DiscountService();
                $catId     = $service->scholarshipCategoryId();

                $active = StudentDiscount::with('discountPolicy')
                    ->where('student_id', $studentId)
                    ->where('status', 1)
                    ->get();

                if ($active->contains('discount_policy_id', $policy->id)) {
                    $validator->errors()->add('discount_policy_id', 'This discount is already applied.');
                    return;
                }

                // Sibling aur scholarship ek saath nahi
                $isSibling      = $policy->policy_type === 'sibling';
                $isScholarship  = $service->isScholarship($policy, $catId);
                $activePolicies = $active->pluck('discountPolicy')->filter();
                if ($isScholarship && $activePolicies->contains(fn($p) => $p->policy_type === 'sibling')) {
                    $validator->errors()->add('discount_policy_id', 'Remove the sibling discount first.');
                    return;
                }
                if ($isSibling && $activePolicies->contains(fn($p) => $service->isScholarship($p, $catId))) {
                    $validator->errors()->add('discount_policy_id', 'Remove the scholarship first.');
                    return;
                }

                // Fixed scholarship: amount zaroori aur class fee se zyada nahi
                if ($isScholarship && $policy->discount_type === 'fixed') {
                    $amount = (float) $this->input('amount');
                    if ($amount <= 0) {
                        $validator->errors()->add('amount', 'The Amount is required for a fixed scholarship.');
                        return;
                    }

                    $student = Student::with('currentEnrollment')->find($studentId);
                    $classId = optional(optional($student)->currentEnrollment)->class_id;
                    $yearId  = AcademicYear::where('is_current', 1)->value('id');
                    if ($policy->fee_type_id && $classId && $yearId) {
                        $feeAmount = FeeStructure::where('school_class_id', $classId)
                            ->where('academic_year_id', $yearId)
                            ->where('fee_type_id', $policy->fee_type_id)
                            ->value('amount');
                        if ($feeAmount !== null && $amount > (float) $feeAmount) {
                            $validator->errors()->add('amount', 'The Amount cannot be more than the class fee (Rs ' . number_format($feeAmount, 2) . ').');
                        }
                    }
                }
            },
        ];
    }
}
