<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LibrariansController extends Controller
{
    // Dashboard
public function LibrarianManage()
{
    $role = auth()->user()->getRoleNames()->first(); // safe

    return view('librarian.dashboard');
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

    return view('librarian.librarian_login');
}
// End Method


// Profile View
public function LibrarianProfile()
{
    $id = Auth::id();

    $profileData = User::find($id);

    return view('librarian.librarian_profile', compact('profileData'));
}
// End Method


// Profile Update
public function LibrarianProfileUpdate(ProfileUpdateRequest $request, ImageService $imageService)
{
    $user = User::find(Auth::id());

    $data = [
        'name'   => $request->profileName,
        'phone'  => $request->profilePhone,
        'address'=> $request->address,
        'gender' => $request->gender,
    ];

    if ($request->hasFile('photo')) {

        if ($user->photo) {
            $imageService->delete($user->photo, 'uploads/librarian_images');
        }

        $data['photo'] = $imageService->upload(
            $request->file('photo'),
            'uploads/librarian_images',
            300,
            300
        );
    }

    $user->update($data);

    return redirect()->back()->with([
        'message' => 'Librarian Profile Updated Successfully',
        'alert-type' => 'success'
    ]);
}
// End Method
}
