<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
{
    return [
        'token'    => 'required',
        'email'    => 'required|email|exists:users,email',
        'password' => 'required|confirmed|min:8',
    ];
}

public function messages(): array
{
    return [
        'email.exists'       => 'Ye email hamare record mein nahi hai.',
        'password.confirmed' => 'Password confirmation match nahi ho rahi.',
        'password.min'       => 'Password kam az kam 8 characters ka hona chahiye.',
    ];
}
}
