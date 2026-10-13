<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLibrarianRequest extends FormRequest
{
    /**
     * Route manage-librarian group mein hai; yahan bhi double check.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage-librarian');
    }

    /**
     * Rules librarians/create.blade.php ke input names se match karte hain.
     * librarian_id yahan nahi — server hi banata hai (request trust nahi).
     */
    public function rules(): array
    {
        return [
            'first_name'         => 'required|string|max:255',
            'last_name'          => 'required|string|max:255',
            'father_name'        => 'required|string|max:255',
            'mother_name'        => 'nullable|string|max:255',
            'email'              => 'required|email|max:255|unique:users,email',
            'cnic'               => 'required|string|max:15|unique:librarians,cnic',
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
            'password'           => 'required|string|min:8|confirmed',
        ];
    }
}
