<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SchoolResetPasswordMail;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
{
    $request->validate([
        'email' => ['required', 'email', 'exists:users,email'],
    ]);

    // 1. Token generate karein
    $token = Str::random(64);

    // 2. Database (password_resets table) mein entry karein
    DB::table('password_reset_tokens')->updateOrInsert(
        ['email' => $request->email],
        [
            'token' => $token,
            'created_at' => Carbon::now()
        ]
    );

    // 3. APNA PROFESSIONAL MAIL BHEJEIN (Default ki jagah)
    Mail::to($request->email)->send(new SchoolResetPasswordMail($token, $request->email));

    // 4. Success message ke sath wapis bhejein
    return back()->with('status', 'We have emailed your professional reset link!');
}
}
