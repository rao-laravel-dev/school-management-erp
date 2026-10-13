<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Librarian portal profile: sirf phone, address, gender, photo (name/email/CNIC/ID/bank read-only)
class LibrarianProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone'   => 'required|string|max:15',
            'address' => 'nullable|string|max:255',
            'gender'  => 'required|in:male,female',
            'photo'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'photo.image' => 'Please upload a valid image file',
            'photo.mimes' => 'Only jpg, jpeg, png, webp images are allowed',
            'gender.in'   => 'Please select a valid gender option',
        ];
    }
}
