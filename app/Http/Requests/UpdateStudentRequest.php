<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    /**
     * Route pehle se can:access-students middleware ke peeche hai.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rules edit_student.blade.php ke input names se match karte hain.
     * admission_no / roll_number yahan nahi — server-side hi decide hote hain (request trust nahi).
     */
    public function rules(): array
    {
        $image = 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048';

        return [
            // 🎓 Academic Details
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
            'house_id'           => 'nullable|integer|exists:student_houses,id',
            'religion'           => 'nullable|string|max:100',
            'caste'              => 'nullable|string|max:100',
            'blood_group'        => 'nullable|string|max:10',
            'height'             => 'nullable|string|max:50',
            'weight'             => 'nullable|string|max:50',
            'measurement_date'   => 'nullable|date',
            'medical_history'    => 'nullable|string',
            'student_photo'      => $image,

            // 👨‍👩‍👦 Parent & Contact Details
            'father_name'        => 'required|string|max:255',
            'father_phone'       => ['required', 'regex:/^(03|923|\+923)[0-9]{9}$/'],
            'father_photo'       => $image,
            'mother_name'        => 'nullable|string|max:255',
            'mother_phone'       => 'nullable|string|max:255',
            'mother_photo'       => $image,

            // 🛡️ Guardian Setup
            'is_guardian'        => 'required|in:father,mother,other',
            'guardian_name'      => 'required|string|max:255',
            'guardian_phone'     => 'required|string',
            'guardian_relation'  => 'required|string|max:100',
            'guardian_email'     => 'nullable|email|max:255',
            'guardian_address'   => 'nullable|string|max:500',
            'guardian_photo'     => $image,

            // 🪪 National Identification Documents
            'father_cnic'        => 'required|string|max:50',
            'father_cnic_front'  => $image,
            'father_cnic_back'   => $image,
            'mother_cnic'        => 'nullable|string|max:50',
            'mother_cnic_front'  => $image,
            'mother_cnic_back'   => $image,

            // 🔑 Account Security (blank = current password rakho)
            'password'           => 'nullable|string|min:6|confirmed',
            'guardian_password'  => 'nullable|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.required'          => 'Please select a valid class from the dropdown.',
            'section_id.required'        => 'Please select a valid section from the dropdown.',
            'first_name.required'        => 'Student first name field is required.',
            'last_name.required'         => 'Student last name field is required.',
            'gender.required'            => 'Please select student gender.',
            'category_id.exists'         => 'Selected category is invalid.',
            'house_id.exists'            => 'Selected house is invalid.',
            'father_name.required'       => 'Father name configuration is required.',
            'father_phone.required'      => 'Father primary contact number is required.',
            'father_phone.regex'         => 'Enter a valid Pakistani mobile number (03XXXXXXXXX).',
            'father_cnic.required'       => 'Father CNIC configuration unique identifier is required.',
            'guardian_name.required'     => 'Guardian contact name is required.',
            'guardian_phone.required'    => 'Guardian active phone number is required.',
            'guardian_relation.required' => 'Guardian relation mapping is required.',
            'password.min'               => 'Security password must be at least 6 characters long.',
            'password.confirmed'         => 'Security password confirmation does not match.',
            'guardian_password.min'      => 'Guardian password must be at least 6 characters long.',
            'guardian_password.confirmed' => 'Guardian password confirmation does not match.',

            'student_photo.max'          => 'Profile image size must not exceed 2MB.',
            'father_photo.max'           => 'Father photo size must not exceed 2MB.',
            'mother_photo.max'           => 'Mother photo size must not exceed 2MB.',
            'guardian_photo.max'         => 'Guardian photo size must not exceed 2MB.',
            'father_cnic_front.max'      => 'Father CNIC front size must not exceed 2MB.',
            'father_cnic_back.max'       => 'Father CNIC back size must not exceed 2MB.',
            'mother_cnic_front.max'      => 'Mother CNIC front size must not exceed 2MB.',
            'mother_cnic_back.max'       => 'Mother CNIC back size must not exceed 2MB.',
        ];
    }
}
