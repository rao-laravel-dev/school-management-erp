<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateFeeVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware (can:manage-fee-vouchers) permission check karta hai
    }

    public function rules(): array
    {
        return [
            // Up to next month only: a far future month would print an arrears-only voucher
            'month' => 'required|date_format:Y-m|before_or_equal:' . now()->addMonth()->format('Y-m'),
        ];
    }

    public function messages(): array
    {
        return [
            'month.required'        => 'Please select the voucher month.',
            'month.date_format'     => 'Voucher month is not valid.',
            'month.before_or_equal' => 'Voucher month cannot be later than next month.',
        ];
    }
}
