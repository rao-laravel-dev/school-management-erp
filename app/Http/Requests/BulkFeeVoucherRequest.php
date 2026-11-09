<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkFeeVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware (can:manage-fee-vouchers) permission check karta hai
    }

    public function rules(): array
    {
        return [
            'class_id'   => ['required', Rule::exists('school_class', 'id')],
            'section_id' => [
                'nullable',
                Rule::exists('school_class_section', 'section_id')->where('school_class_id', $this->input('class_id')),
            ],
            'month'      => 'required|date_format:Y-m|before_or_equal:' . now()->addMonth()->format('Y-m'),
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.required'     => 'Please select a class.',
            'class_id.exists'       => 'Selected class is not valid.',
            'section_id.exists'     => 'Selected section does not belong to this class.',
            'month.required'        => 'Please select the voucher month.',
            'month.date_format'     => 'Voucher month is not valid.',
            'month.before_or_equal' => 'Voucher month cannot be later than next month.',
        ];
    }
}
