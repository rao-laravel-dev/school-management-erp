<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminController extends Controller
{
     public function SuperAdminManage()
{
    return view('superadmin.dashboard');
}

     public function SuperAdminLogout(Request $request){
        Auth::guard('web')->logout();
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('adminsuperadmin.login');
    } 
    // End Method 


     public function SuperAdminLogin(Request $request){
        if (Auth::check()) {
          return redirect()->route('adminsuperadmin.dashboard');
        }
        return view('superadmin.superadmin_login');
       
    }
    // End Method

    public function SuperAdminProfile()
{
    // current logged-in super-admin. ka data le rahe hain
    $id = Auth::id();

    // user record fetch
    $profileData = User::find($id);

    // view me data send
    return view('superadmin.superadmin_profile', compact('profileData'));
}
    // End Method


     // FOR SUPER-ADMIN PROFILE DATA UPDATE USING THIS METHOD:
    public function SuperAdminProfileUpdate(ProfileUpdateRequest $request, ImageService $imageService)
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
    if ($request->hasFile('photo')) {

        // old image delete (safe path)
        if ($user->photo) {
            $imageService->delete($user->photo, 'uploads/superadmin_images');
        }

        // upload new image via service
        $data['photo'] = $imageService->upload(
            $request->file('photo'),
            'uploads/superadmin_images',
            300,
            300
        );
    }

    // update user record
    $user->update($data);

    // redirect back with success message
    return redirect()->back()->with([
        'message' => 'Super Admin Profile Updated Successfully',
        'alert-type' => 'success'
    ]);
}
     // End Method
}
