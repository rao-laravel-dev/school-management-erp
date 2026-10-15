<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccessControlActionRequest extends FormRequest
{
    // Sirf superadmin (route par bhi role:superadmin hai)
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'action'  => ['required', 'in:activate,deactivate'],
            // Last active admin ko inactive karte waqt typed LOCK (server-side check controller mein)
            'confirm' => ['nullable', 'string', 'max:10'],
        ];
    }
}
