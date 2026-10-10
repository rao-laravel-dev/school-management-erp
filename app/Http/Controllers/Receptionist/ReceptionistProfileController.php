<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceptionistProfileController extends Controller
{
     // Dashboard
    public function ReceptionistManage()
{
    $role = auth()->user()->roles->first()->name;

    return view('reception.dashboard');
}
    // End Method


    // Logout
    public function ReceptionLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/reception/login');
    }
    // End Method


    // Login Page
    public function ReceptionLogin()
    {
        if (Auth::check()) {
            return redirect()->route('reception.dashboard');
        }

        return view('reception.reception_login');
    }
    // End Method


    // Profile View
    public function ReceptionistProfile()
{
    // current logged-in admin. ka data le rahe hain
    $id = Auth::id();

    // user record fetch
    $profileData = User::find($id);

    // view me data send
    return view('reception.reception_profile', compact('profileData'));
}
    // End Method


    // Profile Update
    public function ReceptionistProfileUpdate(ProfileUpdateRequest $request, ImageService $imageService)
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
        // photo receptionists table mein hoti hai (User::photo_url wahi pehle dekhta hai), users.photo mein nahi
        $profile = $user->receptionist;
        $oldPhoto = $profile ? $profile->photo : $user->photo;
        if ($oldPhoto) {
            $imageService->delete($oldPhoto, 'uploads/receptionist_images');
        }

        $newPhoto = $imageService->upload($request->file('photo'), 'uploads/receptionist_images', 300, 300);

        if (!$profile) {
            $data['photo'] = $newPhoto; // profile row nahi to users.photo mein hi rakho
        }
    }

    // users + receptionist dono ek sath save
    DB::transaction(function () use ($user, $data, $newPhoto) {
        $user->update($data);
        if ($newPhoto && $user->receptionist) {
            $user->receptionist->update(['photo' => $newPhoto]);
        }
    });

    // redirect back with success message
    return redirect()->back()->with([
        'message' => 'Receptionist Profile Updated Successfully',
        'alert-type' => 'success'
    ]);
}
     // End Method

}
