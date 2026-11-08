<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BillingSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Route middleware (can:manage-billing-settings) permission already check kar raha hai
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'academic_year_id'    => 'required|exists:academic_years,id',
            'fee_generate_day'    => 'required|integer|between:1,28',
            'fee_due_day'         => 'required|integer|between:1,28',
            'salary_generate_day' => 'required|integer|between:1,28',
            'auto_fees'           => 'nullable|boolean',
            'auto_salary'         => 'nullable|boolean',
            'fees_from_month'     => 'nullable|date_format:Y-m',
            'salary_from_month'   => 'nullable|date_format:Y-m',
            'late_fee_amount'         => 'required|numeric|min:0|max:99999999.99',
            'voucher_bank_account_id' => 'nullable|exists:bank_accounts,id',
            'voucher_note'            => 'nullable|string|max:1000',
            'scholarship_category_id' => 'nullable|exists:student_categories,id',
            'onebill_enabled'         => 'nullable|boolean',
            'onebill_bank'            => 'nullable|required_if:onebill_enabled,1|string|max:100',
            'onebill_biller_code'     => 'nullable|required_if:onebill_enabled,1|string|max:100',
            'onebill_username'        => 'nullable|string|max:255',
            'onebill_password'        => 'nullable|string|max:255',
            'onebill_api_key'         => 'nullable|string|max:255',
        ];
    }

    /**
     * Field names for the error messages
     */
    public function attributes(): array
    {
        return [
            'academic_year_id'    => 'Academic Year',
            'fee_generate_day'    => 'Fee Generate Day',
            'fee_due_day'         => 'Fee Due Day',
            'salary_generate_day' => 'Salary Generate Day',
            'fees_from_month'     => 'Fees From Month',
            'salary_from_month'   => 'Salary From Month',
            'late_fee_amount'         => 'Late Fee',
            'voucher_bank_account_id' => 'Voucher Bank',
            'voucher_note'            => 'Voucher Note',
            'scholarship_category_id' => 'Scholarship Category',
            'onebill_bank'            => '1Bill Bank',
            'onebill_biller_code'     => '1Bill Biller Code',
            'onebill_username'        => '1Bill Username',
            'onebill_password'        => '1Bill Password',
            'onebill_api_key'         => '1Bill API Key',
        ];
    }

    /**
     * Custom error messages for better UX
     */
    public function messages(): array
    {
        return [
            'academic_year_id.required'    => 'The Academic Year field is required.',
            'fee_generate_day.required'    => 'The Fee Generate Day field is required.',
            'fee_generate_day.between'     => 'The Fee Generate Day must be between 1 and 28.',
            'fee_due_day.required'         => 'The Fee Due Day field is required.',
            'fee_due_day.between'          => 'The Fee Due Day must be between 1 and 28.',
            'salary_generate_day.required' => 'The Salary Generate Day field is required.',
            'salary_generate_day.between'  => 'The Salary Generate Day must be between 1 and 28.',
            'fees_from_month.date_format'  => 'The Fees From Month must be a valid month.',
            'salary_from_month.date_format' => 'The Salary From Month must be a valid month.',
            'late_fee_amount.required'     => 'The Late Fee field is required (0 = no late fee).',
            'late_fee_amount.min'          => 'The Late Fee cannot be negative.',
            'onebill_bank.required_if'         => 'The 1Bill Bank field is required when 1Bill is enabled.',
            'onebill_biller_code.required_if'  => 'The 1Bill Biller Code field is required when 1Bill is enabled.',
        ];
    }
}
