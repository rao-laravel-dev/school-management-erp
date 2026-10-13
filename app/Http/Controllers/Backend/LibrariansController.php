<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\LibrarianProfileUpdateRequest;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LibrariansController extends Controller
{
    // Dashboard
public function LibrarianManage()
{
    $role = auth()->user()->getRoleNames()->first(); // safe

    return view('admin.librarian.dashboard');
}
// End Method


// Logout
public function LibrarianLogout(Request $request)
{
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/librarian/login');
}
// End Method


// Login Page
public function LibrarianLogin()
{
    if (Auth::check()) {
        return redirect()->route('librarian.dashboard');
    }

    return view('admin.librarian.librarian_login');
}
// End Method


// Profile View
public function LibrarianProfile()
{
    $user = Auth::user();

    // Librarian profile row (bank ke saath); na mile to 404 (teacher pattern)
    $librarian = $user->librarian()->with('bankDetail')->first();
    if (! $librarian) {
        abort(404, 'Librarian profile not found.');
    }

    $profileData = $user;

    return view('admin.librarian.librarian_profile', compact('profileData', 'librarian'));
}
// End Method


// Profile Update (sirf phone, address, gender, photo)
public function LibrarianProfileUpdate(LibrarianProfileUpdateRequest $request, ImageService $imageService)
{
    $user = Auth::user();

    $librarian = $user->librarian;
    if (! $librarian) {
        abort(404, 'Librarian profile not found.');
    }

    $data = $request->validated();

    $profile = [
        'phone'   => $data['phone'],
        'address' => $data['address'] ?? null,
        'gender'  => $data['gender'],
    ];

    $oldPhoto = $librarian->photo;
    $newPhoto = null;
    if ($request->hasFile('photo')) {
        // Admin LibrarianController jaisa folder (Librarian::photo_url yahi padhta hai)
        $newPhoto = $imageService->upload($request->file('photo'), 'uploads/librarian_images', 300, 300);
        $profile['photo'] = $newPhoto;
    }

    DB::transaction(function () use ($user, $librarian, $profile) {
        $librarian->update($profile);

        // users table mein bhi mirror (photo sirf librarians table mein)
        $user->update([
            'phone'   => $profile['phone'],
            'address' => $profile['address'],
            'gender'  => $profile['gender'],
        ]);
    });

    // Purani photo sirf save success ke baad delete
    if ($newPhoto && $oldPhoto) {
        $imageService->delete($oldPhoto, 'uploads/librarian_images');
    }

    // Librarian layout flash 'message' + 'alert-type' render karta hai
    return redirect()->back()->with([
        'message' => 'Librarian Profile Updated Successfully',
        'alert-type' => 'success'
    ]);
}
// End Method
}
