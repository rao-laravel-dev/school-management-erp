<?php

namespace App\Http\Requests;

use App\Models\StudentFees;
use App\Services\FeeVoucherService;
use Illuminate\Foundation\Http\FormRequest;

class PayFeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Route middleware (auth:sanctum + can:manage-fee-collections) permission already check kar raha hai
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Balance route parameter {studentFeeId} se fee record fetch karke calculate hota hai
        $fee     = StudentFees::findOrFail($this->route('studentFeeId'));
        $balance = $fee->amount - $fee->discount - $fee->paid_amount;

        // Pending late fee of the fee's month is part of the max and must be covered first
        $lateFee   = $this->pendingLateFee($fee);
        $maxAmount = $balance + $lateFee;
        $minAmount = $lateFee > 0 ? $lateFee : 0.01;

        return [
            'amount'          => "required|numeric|min:{$minAmount}|max:{$maxAmount}",
            'payment_method'  => 'required|in:cash,cheque,bank_transfer,easypaisa,jazzcash,card',
            'bank_account_id' => 'required_unless:payment_method,cash|nullable|exists:bank_accounts,id',
            'reference_no'    => 'nullable|string|max:100',
            'note'            => 'nullable|string',
        ];
    }

    /**
     * Custom error messages for better UX
     */
    public function messages(): array
    {
        $lateFee = $this->pendingLateFee(StudentFees::findOrFail($this->route('studentFeeId')));

        return [
            'amount.min'              => $lateFee > 0 ? "Amount must cover the late fee Rs {$lateFee} first." : 'Amount must be at least 0.01.',
            'amount.required'         => 'Payment amount is required.',
            'amount.max'              => 'Amount exceeds the remaining fee balance.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in'       => 'Invalid payment method selected.',
            'bank_account_id.required_unless' => 'Bank account is required for non-cash payments.',
        ];
    }

    // Pending late fee (runtime) of this fee's month, 0 if none
    private function pendingLateFee(StudentFees $fee): float
    {
        $service = app(FeeVoucherService::class);
        $late    = $service->pendingLateFees($fee->student_id)->get($fee->student_id, collect())->get($service->monthOf($fee));

        return $late ? (float) $late->amount : 0.0;
    }
}
