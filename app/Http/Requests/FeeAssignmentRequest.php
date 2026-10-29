<?php

namespace App\Http\Requests;

use App\Models\AcademicYear;
use App\Models\BillingSetting;
use App\Models\FeeAssignment;
use App\Models\FeeType;
use App\Services\BillingService;
use App\Services\FeeAssignmentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class FeeAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Route middleware (can:manage-fee-assignments) permission already check kar raha hai
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Update: title; recurring mein end_month bhi
        if ($this->isMethod('put')) {
            $rules      = ['title' => 'required|string|max:150'];
            $assignment = $this->route('feeAssignment');

            if ($assignment && $assignment->kind === FeeAssignment::KIND_RECURRING) {
                $rules['end_month'] = [
                    'nullable',
                    'date_format:Y-m',
                    function ($attribute, $value, $fail) use ($assignment) {
                        if ($value < $assignment->start_month) {
                            $fail('End Month cannot be before the Start Month.');
                        } elseif (!in_array($value, app(BillingService::class)->feeMonths($assignment->academicYear), true)) {
                            $fail('End Month must be inside the academic year.');
                        }
                    },
                ];
            }

            return $rules;
        }

        $rules = [
            'academic_year_id' => 'required|exists:academic_years,id',
            'target_type'      => ['required', Rule::in(FeeAssignment::TARGETS)],
            'class_ids'        => ['required_if:target_type,classes', 'nullable', 'array'],
            'class_ids.*'      => 'exists:school_class,id',
            'class_id'         => ['required_if:target_type,section', 'nullable', 'exists:school_class,id'],
            'section_id'       => [
                'required_if:target_type,section',
                'nullable',
                Rule::exists('school_class_section', 'section_id')->where(function ($q) {
                    $q->where('school_class_id', $this->input('class_id'))->where('status', 1);
                }),
            ],
            'student_ids'      => ['required_if:target_type,students', 'nullable', 'array'],
            'student_ids.*'    => 'exists:students,id',
        ];

        // Preview button ko sirf year + target chahiye; Save ka pehla step (full=1) saari fields check karta hai
        if ($this->routeIs('fee_assignments.preview') && !$this->boolean('full')) {
            return $rules;
        }

        $rules += [
            // Kind request se nahi aata, Fee Type se tay hota hai. Class-wide types allowed nahi
            'fee_type_id' => [
                'required',
                Rule::exists('fee_types', 'id')->where(function ($q) {
                    $q->where('frequency', 'one_time')->orWhere('is_per_student', true);
                }),
            ],
            'title'       => 'required|string|max:150',
            'amount'      => 'required|numeric|min:0.01|max:9999999999',
        ];

        if (!$this->isRecurring()) {
            return $rules + ['due_date' => 'required|date'];
        }

        // Recurring (Tuition): sirf selected students, due date billing settings se aati hai
        $rules['target_type'] = ['required', Rule::in(['students'])];
        $rules['academic_year_id'] = [
            'required',
            'exists:academic_years,id',
            function ($attribute, $value, $fail) {
                if (!BillingSetting::where('academic_year_id', $value)->exists()) {
                    $fail('Billing Settings are not saved for this Academic Year.');
                }
            },
        ];

        return $rules + [
            'start_month' => [
                'required',
                'date_format:Y-m',
                function ($attribute, $value, $fail) {
                    if (!$this->monthInsideYear($value)) {
                        $fail('Start Month must be inside the academic year.');
                    }
                },
            ],
            'end_month'   => [
                'nullable',
                'date_format:Y-m',
                function ($attribute, $value, $fail) {
                    if ($value < $this->input('start_month')) {
                        $fail('End Month cannot be before the Start Month.');
                    } elseif (!$this->monthInsideYear($value)) {
                        $fail('End Month must be inside the academic year.');
                    }
                },
            ],
        ];
    }

    // Fee Type se kind: recurring (per-student, jaise Tuition) hai ya nahi
    private function isRecurring(): bool
    {
        $feeType = FeeType::find($this->input('fee_type_id'));

        return $feeType && FeeAssignment::kindForFeeType($feeType) === FeeAssignment::KIND_RECURRING;
    }

    private function monthInsideYear($month): bool
    {
        $year = AcademicYear::find($this->input('academic_year_id'));

        return $year && in_array($month, app(BillingService::class)->feeMonths($year), true);
    }

    /**
     * Recurring overlap: same student ko usi fee type ki month range takrati ho to Students field par error
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->isMethod('put') || $validator->errors()->isNotEmpty()) {
                    return;
                }
                if ($this->routeIs('fee_assignments.preview') && !$this->boolean('full')) {
                    return;
                }
                if (!$this->isRecurring()) {
                    return;
                }

                $message = app(FeeAssignmentService::class)->overlapMessage(
                    (int) $this->input('academic_year_id'),
                    (int) $this->input('fee_type_id'),
                    (array) $this->input('student_ids', []),
                    $this->input('start_month'),
                    $this->input('end_month')
                );

                if ($message) {
                    $validator->errors()->add('student_ids', $message);
                }
            },
        ];
    }

    /**
     * Field names for the error messages
     */
    public function attributes(): array
    {
        return [
            'academic_year_id' => 'Academic Year',
            'fee_type_id'      => 'Fee Type',
            'title'            => 'Title',
            'amount'           => 'Amount',
            'due_date'         => 'Due Date',
            'start_month'      => 'Start Month',
            'end_month'        => 'End Month',
            'target_type'      => 'Target',
            'class_ids'        => 'Classes',
            'class_id'         => 'Class',
            'section_id'       => 'Section',
            'student_ids'      => 'Students',
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'The Academic Year field is required.',
            'fee_type_id.required'      => 'The Fee Type field is required.',
            'fee_type_id.exists'        => 'This Fee Type cannot be assigned here (class-wide fees come from Fee Structure).',
            'title.required'            => 'The Title field is required.',
            'amount.required'           => 'The Amount field is required.',
            'amount.min'                => 'The Amount must be greater than 0.',
            'due_date.required'         => 'The Due Date field is required.',
            'start_month.required'      => 'The Start Month field is required.',
            'start_month.date_format'   => 'Start Month must be a valid month.',
            'end_month.date_format'     => 'End Month must be a valid month.',
            'target_type.in'            => 'Recurring fees can only be assigned to selected students.',
            'target_type.required'      => 'Please choose who gets this fee.',
            'class_ids.required_if'     => 'Select at least one class.',
            'class_id.required_if'      => 'The Class field is required.',
            'section_id.required_if'    => 'The Section field is required.',
            'section_id.exists'         => 'Selected Section does not belong to this Class.',
            'student_ids.required_if'   => 'Select at least one student.',
        ];
    }
}
