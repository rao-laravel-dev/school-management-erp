<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function AdminManage()
{
    // 1. Active Users (Status 1 wale)
    $targetRoles = ['admin', 'teacher', 'staff', 'parent', 'student', 'receptionist', 'accountant', 'librarian'];
    $activeUsersCount = User::whereHas('roles', function($query) use ($targetRoles) {
    $query->whereIn('name', $targetRoles);
})->count();

    // 2. Teacher Attendance (Static as requested)
    $totalTeachers = 10;
    $presentTeachers = 8;

    // 3. Student Attendance
    // Total Active Students count
    $totalStudents = User::role('student')->where('status', 1)->count(); 
    
    $presentStudents = Attendance::whereHas('enrollment.student', function($query) {
                            $query->whereHas('user', function($q) {
                                $q->role('student')->where('status', 1);
                            });
                        })
                        ->whereDate('attendance_date', today())
                        ->where('status', Attendance::STATUS_PRESENT)
                        ->count();

    // 4. Session Status
    $totalSessions = AcademicYear::count();
    $activeSessions = AcademicYear::where('is_current', 1)->count(); 

    return view('admin.dashboard', compact(
        'activeUsersCount', 
        'totalTeachers', 'presentTeachers', 
        'totalStudents', 'presentStudents', 
        'totalSessions', 'activeSessions'
    ));
}
// End Method

    public function AdminLogout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
    // End Method 


    public function AdminLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.admin_login');
    }
    // End Method

    public function AdminProfile()
    {
        // current logged-in admin. ka data le rahe hain
        $id = Auth::id();

        // user record fetch
        $profileData = User::find($id);

        // view me data send
        return view('admin.admin_profile_view', compact('profileData'));
    }
    // End Method


    // FOR ADMIN PROFILE DATA UPDATE USING THIS METHOD:
    public function AdminProfileUpdate(ProfileUpdateRequest $request, ImageService $imageService)
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
                $imageService->delete($user->photo, 'uploads/admin_images');
            }

            // upload new image via service
            $data['photo'] = $imageService->upload(
                $request->file('photo'),
                'uploads/admin_images',
                300,
                300
            );
        }

        // update user record
        $user->update($data);

        // redirect back with success message
        return redirect()->back()->with([
            'message' => 'Admin Profile Updated Successfully',
            'alert-type' => 'success'
        ]);
    }
    // End Method

    // 1. Forgot Password Page Load
    
}
