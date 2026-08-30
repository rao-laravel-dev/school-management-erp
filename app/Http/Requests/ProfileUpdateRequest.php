<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Admin name is required and must be a string
            'profileName'  => 'required|string|max:255',

            // Phone is optional but must be string and limited length
            'profilePhone' => 'nullable|string|max:15',

            // Address is optional field
            'adrs'         => 'nullable|string|max:255',

            // Gender validation (safe enum check)
            'gender'       => 'nullable|in:male,female',

            // Profile image validation
            // Only image files allowed with specific formats and max size 2MB
            'photo'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ];
    }

    /**
     * Custom error messages for better UX
     */
    public function messages(): array
    {
        return [
            // Name error
            'profileName.required' => 'Name is required',

            // Image errors
            'photo.image' => 'Please upload a valid image file',
            'photo.mimes' => 'Only jpg, jpeg, png, webp images are allowed',

            // Gender error
            'gender.in'   => 'Please select a valid gender option',
        ];
    }
}
