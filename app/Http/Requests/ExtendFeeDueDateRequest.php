<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExtendFeeDueDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware (can:manage-fee-due-dates) permission check karta hai
    }

    public function rules(): array
    {
        return [
            'month'    => 'required|date_format:Y-m',
            'due_date' => 'required|date|after_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'due_date.after_or_equal' => 'New due date cannot be in the past.',
        ];
    }
}
