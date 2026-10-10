<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountantProfileController extends Controller
{
     public function AccountantManage()
{
    $role = auth()->user()->roles->first()->name;

    return view('accountant.dashboard');
}
    // End Method


    // Logout
    public function AccountantLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/accountant/login');
    }
    // End Method


    // Login Page
    public function AccountantLogin()
    {
        if (Auth::check()) {
            return redirect()->route('accountant.dashboard');
        }

        return view('accountant.accountant_login');
    }
    // End Method


    // Profile View
    public function AccountantProfile()
{
    // current logged-in admin. ka data le rahe hain
    $id = Auth::id();

    // user record fetch
    $profileData = User::find($id);

    // view me data send
    return view('accountant.accountant_profile', compact('profileData'));
}
    // End Method


    // Profile Update
    public function AccountantProfileUpdate(ProfileUpdateRequest $request, ImageService $imageService)
    {
       // current logged-in user
    $user = User::find(Auth::id());

    // basic fields update data array me store
    $data = [
        'name'   => $request->profileName,
        'phone'  => $request->profilePhone,
        'address'   => $request->address,
        'gender' => $request->gender, // added missing field
    ];

    // check if new image uploaded
    $newPhoto = null;
    if ($request->hasFile('photo')) {
        // photo accountants table mein hoti hai (User::photo_url wahi pehle dekhta hai), users.photo mein nahi
        $profile = $user->accountant;
        $oldPhoto = $profile ? $profile->photo : $user->photo;
        if ($oldPhoto) {
            $imageService->delete($oldPhoto, 'uploads/accountant_images');
        }

        $newPhoto = $imageService->upload($request->file('photo'), 'uploads/accountant_images', 300, 300);

        if (!$profile) {
            $data['photo'] = $newPhoto; // profile row nahi to users.photo mein hi rakho
        }
    }

    // users + accountant dono ek sath save
    DB::transaction(function () use ($user, $data, $newPhoto) {
        $user->update($data);
        if ($newPhoto && $user->accountant) {
            $user->accountant->update(['photo' => $newPhoto]);
        }
    });

    // redirect back with success message
    return redirect()->back()->with([
        'message' => 'Accountant Profile Updated Successfully',
        'alert-type' => 'success'
    ]);
}
     // End Method
}
