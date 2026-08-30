<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

         // Get logged in user
        $user = $request->user();

        /*
        |--------------------------------------
        | ROLE BASED REDIRECT SYSTEM
        |--------------------------------------
        */

        if ($user->hasRole('superadmin')) {
        return redirect()->route('superadmin.dashboard');
    }

    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('receptionist')) {
        return redirect()->route('reception.dashboard');
    }

    if ($user->hasRole('accountant')) {
        return redirect()->route('accountant.dashboard');
    }

    if ($user->hasRole('librarian')) {
        return redirect()->route('librarian.dashboard');
    }

    if ($user->hasRole('teacher')) {
        return redirect()->route('teacher.dashboard');
    }

    if ($user->hasRole('parent')) {
        return redirect()->route('parent.dashboard');
    }

    if ($user->hasRole('student')) {
        return redirect()->route('student.dashboard');
    }

    if ($user->hasRole('user')) {
        return redirect()->route('dashboard');
    }

    // fallback
    return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
  public function destroy(Request $request)
{
    // 1. Logout karne se pehle current user ko capture karein
    $user = Auth::user();

    // 2. Logout karne se pehle hi check kar lein ke user ka role kya hai
    $redirectRoute = '/login'; // Default fallback

    if ($user) {
        if ($user->hasRole('superadmin')) {
            $redirectRoute = route('superadmin.login');
        } elseif ($user->hasRole('admin')) {
            $redirectRoute = route('admin.login');
        } elseif ($user->hasRole('receptionist')) {
            $redirectRoute = route('reception.login');
        } elseif ($user->hasRole('accountant')) {
            $redirectRoute = route('accountant.login');
        } elseif ($user->hasRole('librarian')){
            $redirectRoute = route('librarian.login');
        }elseif ($user->hasRole('parent')) {
            $redirectRoute = route('parent.login');
        } elseif ($user->hasRole('teacher')) {
            $redirectRoute = route('teacher.login');
        } elseif ($user->hasRole('student')) {
            $redirectRoute = route('student.login');
        }
    }

    // 3. Ab safely logout karein
    Auth::logout();

    // 4. Session ko invalidate aur token regenerate karein (CSRF protection ke liye)
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // 5. Pehle se tay shuda route par redirect kar dein
    return redirect($redirectRoute);
}
}
