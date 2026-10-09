<?php

namespace App\Http\Requests;

use App\Models\ParentProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            // 🎓 Academic Details
            'is_existing_student' => 'nullable|boolean',
            'admission_no'         => ['nullable', 'required_if:is_existing_student,1', 'string', 'max:50', 'unique:students,admission_no'],
            'roll_number'        => 'nullable|string',
            'admission_date'     => 'required|date',
            'class_id'           => 'required|integer|exists:school_class,id',
            'section_id'         => 'required|integer|exists:sections,id',
            'group_id'           => 'nullable|integer|exists:groups,id',

            // 👤 Student Information
            'first_name'         => 'required|string|max:255',
            'last_name'          => 'required|string|max:255',
            'gender'             => 'required|in:male,female',
            'date_of_birth'      => 'required|date',
            'category_id'        => 'nullable|integer|exists:student_categories,id',
            'religion'           => 'nullable|string|max:100',
            'caste'              => 'nullable|string|max:100',
            'blood_group'        => 'nullable|string|max:10',
            'house_id'           => 'nullable|integer|exists:student_houses,id',
            'height'             => 'nullable|string|max:50',
            'weight'             => 'nullable|string|max:50',
            'measurement_date'   => 'nullable|date',
            'student_photo'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'medical_history'    => 'nullable|string',

            // 👨‍👩‍👦 Parent & Contact Details
            'father_name'        => 'required|string|max:255',
            'father_phone'       => ['required', 'regex:/^(03|923|\+923)[0-9]{9}$/'],
            'father_photo'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'mother_name'        => 'nullable|string|max:255',
            'mother_phone'       => 'nullable|string|max:255',
            'mother_photo'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // 🛡️ Guardian Setup
            'is_guardian'        => 'required|in:father,mother,other',
            'guardian_name'      => 'required|string|max:255',
            'guardian_phone'     => 'required|string',
            'guardian_relation'  => 'required|string|max:100',
            'guardian_email'     => 'nullable|email|max:255',
            'guardian_address'   => 'nullable|string|max:500',
            'guardian_photo'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // 🪪 National Identification Documents
            'father_cnic'        => 'required|string|max:50',
            'father_cnic_front'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'father_cnic_back'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'mother_cnic'        => 'nullable|string|max:50',
            'mother_cnic_front'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'mother_cnic_back'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // 🔑 Account Security Setup
            'password'           => 'required|string|min:6|confirmed',

            // 🛡️ Guardian Account Security
            // Sirf naye parent ke liye required — existing parent (same father_cnic lookup jo StoreStudent karta hai) ka password nahi badalta
            'guardian_password'  => [
                'nullable',
                Rule::requiredIf(function () {
                    $cnic = trim((string) $this->input('father_cnic'));
                    return $cnic === '' || !ParentProfile::where('father_cnic', $cnic)->exists();
                }),
                'string',
                'min:6',
                'confirmed',
            ],

            // 💸 Discounts
            'discount_policy_ids'   => 'nullable|array',
            'discount_policy_ids.*' => 'integer',
            'discount_remarks'      => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'admission_no.required_if' => 'Existing student ke liye Admission No dena zaroori hai.',
            'admission_no.unique'      => 'Yeh Admission No pehle se kisi aur student ko allot ho chuka hai.',
            'class_id.required'          => 'Please select a valid class from the dropdown.',
            'section_id.required'        => 'Please select a valid section from the dropdown.',
            'first_name.required'        => 'Student first name field is required.',
            'last_name.required'         => 'Student last name field is required.',
            'gender.required'            => 'Please select student gender.',
            'student_photo.image'        => 'The profile attachment must be an image file.',
            'student_photo.max'          => 'Profile image size must not exceed 2MB.',
            'father_name.required'       => 'Father name configuration is required.',
            'father_phone.required'      => 'Father primary contact number is required.',
            'father_phone.regex'         => 'Enter a valid Pakistani mobile number (03XXXXXXXXX).',
            'father_cnic.required'       => 'Father CNIC configuration unique identifier is required.',
            'guardian_name.required'     => 'Guardian contact name is required.',
            'guardian_phone.required'    => 'Guardian active phone number is required.',
            'guardian_relation.required' => 'Guardian relation mapping is required.',
            'password.required'          => 'System login security password is required.',
            'password.min'               => 'Security password must be at least 6 characters long.',
            'password.confirmed'         => 'Security password confirmation does not match.',
            'category_id.exists'         => 'Selected category is invalid.',
            'house_id.exists'            => 'Selected house is invalid.',
            'father_photo.max'           => 'Father photo size must not exceed 2MB.',
            'mother_photo.max'           => 'Mother photo size must not exceed 2MB.',
            'guardian_photo.max'         => 'Guardian photo size must not exceed 2MB.',
            'father_cnic_front.max'      => 'Father CNIC front size must not exceed 2MB.',
            'father_cnic_back.max'       => 'Father CNIC back size must not exceed 2MB.',
            'mother_cnic_front.max'      => 'Mother CNIC front size must not exceed 2MB.',
            'mother_cnic_back.max'       => 'Mother CNIC back size must not exceed 2MB.',

            'guardian_password.required'  => 'Guardian system password is required.',
            'guardian_password.min'       => 'Guardian password must be at least 6 characters long.',
            'guardian_password.confirmed' => 'Guardian password confirmation does not match.',
        ];
    }
}
