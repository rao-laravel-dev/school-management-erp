<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\StaffPasswordUpdateRequest;
use App\Models\AdmissionEnquiry;
use App\Models\Attendance;
use App\Models\Complaint;
use App\Models\StaffAttendance;
use App\Models\StaffLeaveApplication;
use App\Models\User;
use App\Models\Visitor;
use App\Services\ImageService;
use App\Services\StaffSelfProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceptionistProfileController extends Controller
{
     // Dashboard
    public function ReceptionistManage()
{
    $userId = Auth::id();
    $today = now()->toDateString();
    $monthStart = now()->startOfMonth()->toDateString();

    // 1. Visitors aaj: ek query (count, persons, abhi andar = out_time NULL)
    $visitorStats = Visitor::where('date', $today)->toBase()
        ->selectRaw('COUNT(*) AS c, COALESCE(SUM(no_of_person), 0) AS p, COALESCE(SUM(out_time IS NULL), 0) AS inside')->first();
    $todayVisitors = Visitor::with('purpose')->where('date', $today)->orderByDesc('in_time')->get();

    // 2. Admission enquiries: pending + follow-up due / overdue (status: pending / active / inactive, BACKLOG T252)
    $enquiryStats = AdmissionEnquiry::toBase()
        ->selectRaw("COALESCE(SUM(status = 'pending'), 0) AS pending, COALESCE(SUM(status IN ('pending','active') AND next_follow_up_date = ?), 0) AS due, COALESCE(SUM(status IN ('pending','active') AND next_follow_up_date < ?), 0) AS overdue", [$today, $today])->first();
    $dueEnquiries = AdmissionEnquiry::with('purpose')->whereIn('status', ['pending', 'active'])
        ->where('next_follow_up_date', $today)->orderBy('name')->get();

    // 3. Complaints: open (pending + in_progress) + aaj ki
    $complaintStats = Complaint::toBase()
        ->selectRaw("COALESCE(SUM(status IN ('pending','in_progress')), 0) AS open_c, COALESCE(SUM(date = ?), 0) AS today_c", [$today])->first();
    $todayComplaints = Complaint::with('complaintType')->where('date', $today)->orderByDesc('id')->get();

    // 4. Apni staff attendance is mahine (My Attendance Report wali % rule)
    $staffAtt = StaffAttendance::where('user_id', $userId)->whereBetween('date', [$monthStart, $today])
        ->selectRaw('status, COUNT(*) AS c')->groupBy('status')->pluck('c', 'status');
    $myAtt = [
        'present'  => (int) ($staffAtt['present'] ?? 0),
        'half_day' => (int) ($staffAtt['half_day'] ?? 0),
        'marked'   => (int) $staffAtt->sum(),
    ];
    $myAtt['percent'] = $myAtt['marked'] > 0
        ? round((($myAtt['present'] + 0.5 * $myAtt['half_day']) / $myAtt['marked']) * 100, 1) : null;

    // 5. Student attendance aaj: admin dashboard wali active-student filter, status + holiday flag par ek grouped query
    // Status: 0 Absent, 1 Present, 2 Late, 3 Half Day, 4 Leave (4 + remarks "Holiday" = Holiday, chart me nahi)
    $attRows = Attendance::whereHas('enrollment.student', function ($query) {
                            $query->whereHas('user', function ($q) {
                                $q->role('student')->where('status', 1);
                            });
                        })
                        ->where('attendance_date', $today)->toBase()
                        ->selectRaw("attendances.status AS st, (attendances.status = 4 AND LOWER(TRIM(COALESCE(attendances.remarks, ''))) = 'holiday') AS is_holiday, COUNT(*) AS c")
                        ->groupBy('st', 'is_holiday')->get();
    $attendanceCounts = ['Present' => 0, 'Late' => 0, 'Half Day' => 0, 'Absent' => 0, 'Leave' => 0];
    foreach ($attRows as $r) {
        $key = match ((int) $r->st) {
            Attendance::STATUS_PRESENT  => 'Present',
            Attendance::STATUS_LATE     => 'Late',
            Attendance::STATUS_HALF_DAY => 'Half Day',
            Attendance::STATUS_ABSENT   => 'Absent',
            default => ((int) $r->is_holiday === 1 ? null : 'Leave'),
        };
        if ($key !== null) {
            $attendanceCounts[$key] += (int) $r->c;
        }
    }
    $studentsMarked = $attRows->isNotEmpty();
    $studentsHoliday = $studentsMarked && $attRows->every(fn($r) => (int) $r->is_holiday === 1);

    // Staff marked X / Y: Y = active staff roles (Staff Attendance page wali rule), X = unme se aaj mark hue
    $staffIds = User::whereHas('roles', fn($q) => $q->whereNotIn('name', ['superadmin', 'admin', 'parent', 'student', 'user']))
        ->where('status', 1)->pluck('id');
    $staffTotal = $staffIds->count();
    $staffMarked = $staffTotal > 0 ? StaffAttendance::where('date', $today)->whereIn('user_id', $staffIds)->count() : 0;

    // 6. Apni pending leave requests
    $pendingLeaves = StaffLeaveApplication::where('user_id', $userId)->where('status', 'pending')
        ->orderByDesc('apply_date')->orderByDesc('id')->get();

    return view('reception.dashboard', compact(
        'visitorStats', 'todayVisitors', 'enquiryStats', 'dueEnquiries',
        'complaintStats', 'todayComplaints', 'myAtt',
        'attendanceCounts', 'studentsMarked', 'studentsHoliday',
        'staffTotal', 'staffMarked', 'pendingLeaves'
    ));
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


    // Change Password Page
    public function ReceptionistChangePassword()
    {
        return view('staff_profile.change_password', [
            'updateRoute' => route('reception.password.update'),
        ]);
    }
    // End Method


    // Password Update
    public function ReceptionistPasswordUpdate(StaffPasswordUpdateRequest $request, StaffSelfProfileService $service)
    {
        $service->updatePassword(Auth::user(), $request->validated()['password']);

        // Password change ke baad logout (ReceptionLogout jaisa) aur login page par redirect
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/reception/login')->with([
            'message' => 'Password changed successfully. Please login with your new password.',
            'alert-type' => 'success'
        ]);
    }
    // End Method

}
