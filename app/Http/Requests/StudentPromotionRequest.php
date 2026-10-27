<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class StudentPromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware (can:manage-student-promotion) permission already check kar raha hai
    }

    public function rules(): array
    {
        return [
            'from_year_id'  => ['required', Rule::exists('academic_years', 'id')],
            'to_year_id'    => ['required', 'different:from_year_id', Rule::exists('academic_years', 'id')],
            'from_class_id' => ['required', Rule::exists('school_class', 'id')],
            'from_section_id' => [
                'required',
                Rule::exists('school_class_section', 'section_id')->where('school_class_id', $this->from_class_id),
            ],
            // To class/section sirf Promote ke liye zaroori hain (controller check karta hai)
            'to_class_id'   => ['nullable', Rule::exists('school_class', 'id')],
            'to_section_id' => [
                'nullable',
                Rule::exists('school_class_section', 'section_id')->where('school_class_id', $this->to_class_id),
            ],
            'rows'                  => ['required', 'array', 'min:1'],
            'rows.*.enrollment_id'  => ['required', 'integer', Rule::exists('enrollments', 'id')],
            'rows.*.action'         => ['required', Rule::in(['promote', 'detain', 'passed_out', 'skip'])],
        ];
    }

    public function messages(): array
    {
        return [
            'from_year_id.required'       => 'From session is required',
            'to_year_id.required'         => 'To session is required',
            'to_year_id.different'        => 'To session must be different from the From session',
            'from_class_id.required'      => 'From class is required',
            'from_section_id.required'    => 'From section is required',
            'from_section_id.exists'      => 'Selected section does not belong to the From class',
            'to_section_id.exists'        => 'Selected section does not belong to the To class',
            'rows.required'               => 'Load the students first',
            'rows.*.action.in'            => 'Invalid action selected',
        ];
    }

    // Ye AJAX endpoint hai: redirect nahi, JSON 422 (page ek hi toastr dikhata hai)
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
            'errors'  => $validator->errors(),
        ], 422));
    }
}
