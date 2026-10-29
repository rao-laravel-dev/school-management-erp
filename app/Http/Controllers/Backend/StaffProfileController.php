<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffProfileRequest;
use App\Services\ImageService;
use App\Services\StaffSelfProfileService;
use Illuminate\Support\Facades\Auth;

// Teacher / receptionist / accountant / librarian ka apna profile (active role ke hisab se)
class StaffProfileController extends Controller
{
    // Profile View
    public function index(StaffSelfProfileService $service)
    {
        $user = Auth::user();
        [$staffRole, $staffProfile] = $service->profileFor($user);

        return view('staff_profile.index', compact('user', 'staffProfile', 'staffRole'));
    }
    // End Method


    // Profile Update (sirf phone, address, gender, photo)
    public function update(StaffProfileRequest $request, StaffSelfProfileService $service, ImageService $imageService)
    {
        $user = Auth::user();
        [$staffRole, $staffProfile] = $service->profileFor($user);

        $service->updateProfile($user, $staffProfile, $staffRole, $request->validated(), $request->file('photo'), $imageService);

        // Saari staff layouts flash 'message' + 'alert-type' render karti hain
        return redirect()->route('staff_profile.index')->with([
            'message' => ucfirst($staffRole) . ' Profile Updated Successfully',
            'alert-type' => 'success'
        ]);
    }
    // End Method
}
