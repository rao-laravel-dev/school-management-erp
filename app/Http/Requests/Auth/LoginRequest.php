<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string'], // Validation for combined field
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginValue = $this->input('email');

        // Logic: Email hai ya Username/CNIC/RollNo
        $credentials = filter_var($loginValue, FILTER_VALIDATE_EMAIL) 
            ? ['email' => $loginValue, 'password' => $this->password]
            : ['username' => $loginValue, 'password' => $this->password];

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Staff (student/parent ke ilawa har role) sirf email se login karega: staff ID/username (e.g. RIZWAN002) block
        if (! filter_var($loginValue, FILTER_VALIDATE_EMAIL) && ! Auth::user()->hasAnyRole(['student', 'parent'])) {
            Auth::guard('web')->logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Staff must log in with email.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        // Inactive (0) / pending (2) user ko login na karne do (superadmin exempt)
        // Trashed staff (users row ya profile soft-deleted) bhi login na kare
        if (Auth::user()->isLoginBlocked() || Auth::user()->isStaffTrashed()) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'Your account is inactive or pending approval.',
            ]);
        }
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) return;

        event(new Lockout($this));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}