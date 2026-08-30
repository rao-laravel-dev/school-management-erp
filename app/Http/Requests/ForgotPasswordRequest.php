<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Sab roles access kar sakein
    }

    public function rules(): array
    {
        return [
            // 'exists' check karta hai ke ye email database mein hai ya nahi
            'email' => 'required|email|exists:users,email',
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists' => 'Ye email hamare record mein nahi hai.',
            'email.required' => 'Email likhna lazmi hai.',
        ];
    }
}
