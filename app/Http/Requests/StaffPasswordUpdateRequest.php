<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StaffPasswordUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Role access route middleware (role:...) se already controlled hai
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Current password is required',
            'current_password.current_password' => 'Current password is incorrect',
            'password.required'  => 'New password is required',
            'password.min'       => 'New password must be at least 8 characters',
            'password.confirmed' => 'New password and confirm password do not match',
            'password.different' => 'New password must be different from current password',
        ];
    }
}
