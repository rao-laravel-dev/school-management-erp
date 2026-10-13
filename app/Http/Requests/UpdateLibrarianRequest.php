<?php

namespace App\Http\Requests;

use App\Models\Librarian;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLibrarianRequest extends FormRequest
{
    /**
     * Route manage-librarian group mein hai; yahan bhi double check.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage-librarian');
    }

    /**
     * Rules librarians/edit.blade.php ke input names se match karte hain.
     * AJAX request par fail = 422 JSON (errors), edit page ka JS wahi padhta hai.
     */
    public function rules(): array
    {
        $librarian = Librarian::findOrFail($this->route('id'));

        return [
            'first_name'         => 'required|string|max:255',
            'last_name'          => 'required|string|max:255',
            'father_name'        => 'required|string|max:255',
            'mother_name'        => 'nullable|string|max:255',
            'email'              => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($librarian->user_id)],
            'cnic'               => ['required', 'string', 'max:15', Rule::unique('librarians', 'cnic')->ignore($librarian->id)],
            'gender'             => 'required|in:male,female',
            'dob'                => 'required|date',
            'marital_status'     => 'nullable|in:single,married',
            'phone'              => 'required|string|max:15',
            'emergency_name'     => 'required|string|max:255',
            'emergency_relation' => 'nullable|string|max:100',
            'emergency_phone'    => 'required|string|max:20',
            'address'            => 'nullable|string|max:255',
            'permanent_address'  => 'nullable|string|max:255',
            'qualification'      => 'nullable|string|max:255',
            'work_experience'    => 'nullable|string|max:255',
            'joining_date'       => 'nullable|date',
            'contract_type'      => 'nullable|in:permanent,contract',
            'work_shift'         => 'nullable|string|max:255',
            'photo'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'basic_salary'       => 'required|numeric|min:0',
            'bank_type'          => 'required|in:commercial,microfinance_wallet',
            'account_title'      => 'required|string|max:255',
            'account_number'     => 'required|string|max:255',
            'bank_name'          => 'nullable|string|max:255',
            'iban'               => 'nullable|string|max:34',
            'branch_name'        => 'nullable|string|max:255',
            'branch_code'        => 'nullable|string|max:50',
            // Khali chhoren to purana password hi rahega
            'password'           => 'nullable|string|min:8|confirmed',
        ];
    }
}
