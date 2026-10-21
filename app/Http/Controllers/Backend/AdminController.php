<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\StaffPasswordUpdateRequest;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\StaffAttendance;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ImageService;
use App\Services\StaffSelfProfileService;
use Carbon\Carbon;
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
})->where('status', 1)->withoutSuperadmin()->count();

    // 1b. Is mahine naye active users (Active Users card ki chhoti line)
    $newUsersThisMonth = User::whereHas('roles', function($query) use ($targetRoles) {
    $query->whereIn('name', $targetRoles);
})->where('status', 1)->withoutSuperadmin()->where('created_at', '>=', now()->startOfMonth())->count();

    // 1c. Revenue / Expense: month-to-date vs pichle mahine ke same din (Finance Report wali rule)
    $today = now()->toDateString();
    $monthStart = now()->startOfMonth()->toDateString();
    $lastStart = now()->subMonthNoOverflow()->startOfMonth();
    $lastEnd = $lastStart->copy()->day(min(now()->day, $lastStart->daysInMonth))->toDateString();

    $finance = Transaction::where(function ($q) use ($lastStart, $lastEnd, $monthStart, $today) {
            $q->whereBetween('transaction_date', [$lastStart->toDateString(), $lastEnd])
              ->orWhereBetween('transaction_date', [$monthStart, $today]);
        })
        ->selectRaw("
            COALESCE(SUM(CASE WHEN type = 'income' AND category = 'fee' AND transaction_date >= ? THEN amount ELSE 0 END), 0) AS rev_this,
            COALESCE(SUM(CASE WHEN type = 'income' AND category = 'fee' AND transaction_date <= ? THEN amount ELSE 0 END), 0) AS rev_last,
            COALESCE(SUM(CASE WHEN type = 'expense' AND category IN ('salary', 'expense') AND transaction_date >= ? THEN amount ELSE 0 END), 0) AS exp_this,
            COALESCE(SUM(CASE WHEN type = 'expense' AND category IN ('salary', 'expense') AND transaction_date <= ? THEN amount ELSE 0 END), 0) AS exp_last
        ", [$monthStart, $lastEnd, $monthStart, $lastEnd])
        ->first();

    // 1d. Sparkline: pichle 12 mahine (current mahina shamil), mahine ke hisab se ek ek grouped query
    // Data na ho to mahina 0 dikhta hai
    $sparkStart = now()->startOfMonth()->subMonthsNoOverflow(11);
    $months = [];
    $monthLabels = [];
    for ($i = 0; $i < 12; $i++) {
        $m = $sparkStart->copy()->addMonthsNoOverflow($i);
        $months[] = $m->format('Y-m');
        $monthLabels[] = $m->format('M y');
    }

    $financeByMonth = Transaction::where('transaction_date', '>=', $sparkStart->toDateString())
        ->whereIn('category', ['fee', 'salary', 'expense'])
        ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') AS ym,
            COALESCE(SUM(CASE WHEN type = 'income' AND category = 'fee' THEN amount ELSE 0 END), 0) AS revenue,
            COALESCE(SUM(CASE WHEN type = 'expense' AND category IN ('salary', 'expense') THEN amount ELSE 0 END), 0) AS expense")
        ->groupBy('ym')->get()->keyBy('ym');

    $newUsersByMonth = User::whereHas('roles', function($query) use ($targetRoles) {
    $query->whereIn('name', $targetRoles);
})->where('status', 1)->withoutSuperadmin()->where('created_at', '>=', $sparkStart)
        ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS total")
        ->groupBy('ym')->pluck('total', 'ym');

    $revenueSeries = array_map(fn($m) => (float) ($financeByMonth[$m]->revenue ?? 0), $months);
    $expenseSeries = array_map(fn($m) => (float) ($financeByMonth[$m]->expense ?? 0), $months);
    $usersSeries   = array_map(fn($m) => (int) ($newUsersByMonth[$m] ?? 0), $months);

    // 2. Staff cards: role ke active staff + aaj ki attendance (present + half_day = present)
    // Multi-role user har us role ke card me ginta hai (relation name = role name)
    $staffMap = [
        'teacher'      => ['Teachers', 'bx bxs-chalkboard', 'primary'],
        'receptionist' => ['Receptionists', 'bx bxs-phone-call', 'info'],
        'accountant'   => ['Accountants', 'bx bxs-calculator', 'warning'],
        'librarian'    => ['Librarians', 'bx bxs-book-reader', 'danger'],
    ];

    $staffIds = [];
    foreach ($staffMap as $role => $meta) {
        $staffIds[$role] = User::role($role)->where('status', 1)->whereHas($role)->pluck('id')->all();
    }

    $attendanceToday = StaffAttendance::where('date', $today)
        ->whereIn('user_id', array_merge(...array_values($staffIds)))
        ->pluck('status', 'user_id');

    $staffCards = [];
    foreach ($staffMap as $role => [$label, $icon, $color]) {
        $total = count($staffIds[$role]);
        $rows = $attendanceToday->only($staffIds[$role]);
        $present = $rows->filter(fn($s) => in_array($s, ['present', 'half_day'], true))->count();
        $staffCards[] = [
            'label'   => $label,
            'icon'    => $icon,
            'color'   => $color,
            'total'   => $total,
            'present' => $present,
            'marked'  => $rows->isNotEmpty(),
            'percent' => $total > 0 ? round($present / $total * 100) : 0,
        ];
    }

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
    $studentPercent = $totalStudents > 0 ? round($presentStudents / $totalStudents * 100) : 0;

    // 4. Session Status
    $currentSession = AcademicYear::where('is_current', 1)->first(['name', 'start_date', 'end_date']);
    $sessionCounts = AcademicYear::selectRaw('COUNT(*) AS total, COALESCE(SUM(is_current = 1), 0) AS active')->first();

    // 5a. Revenue vs Expense: current session ke har mahine (start month -> end month), top cards wali rule, ek grouped query
    $sessionLabels = [];
    $sessionRevenue = [];
    $sessionExpense = [];
    if ($currentSession && $currentSession->start_date && $currentSession->end_date) {
        $sFrom = Carbon::parse($currentSession->start_date)->toDateString();
        // end_date mahine ki 1 tareekh ho (01 Jan 2026 -> 01 Jan 2027) to pichla mahina aakhri mahina; start se pehle na jaye
        $sEnd = Carbon::parse($currentSession->end_date);
        if ($sEnd->day === 1) {
            $sEnd = $sEnd->copy()->subDay();
        }
        $sTo = max($sEnd->toDateString(), $sFrom); // same $sTo finance query aur month loop dono me

        $sessionFinance = Transaction::whereBetween('transaction_date', [$sFrom, $sTo])
            ->whereIn('category', ['fee', 'salary', 'expense'])
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') AS ym,
                COALESCE(SUM(CASE WHEN type = 'income' AND category = 'fee' THEN amount ELSE 0 END), 0) AS revenue,
                COALESCE(SUM(CASE WHEN type = 'expense' AND category IN ('salary', 'expense') THEN amount ELSE 0 END), 0) AS expense")
            ->groupBy('ym')->get()->keyBy('ym');

        $sLast = Carbon::parse($sTo);
        for ($m = Carbon::parse($sFrom)->startOfMonth(); $m->lte($sLast); $m->addMonthNoOverflow()) {
            $ym = $m->format('Y-m');
            $sessionLabels[]  = $m->format('M y');
            $sessionRevenue[] = (float) ($sessionFinance[$ym]->revenue ?? 0);
            $sessionExpense[] = (float) ($sessionFinance[$ym]->expense ?? 0);
        }
    }
    $sessionRevenueTotal = array_sum($sessionRevenue);
    $sessionExpenseTotal = array_sum($sessionExpense);
    $sessionNet = $sessionRevenueTotal - $sessionExpenseTotal;

    // 5b. Student Attendance (is mahine): Students card wali filter, status + holiday flag par ek grouped query
    // Status: 0 Absent, 1 Present, 2 Late, 3 Half Day, 4 Leave (4 + remarks "Holiday" = Holiday, chart me nahi)
    $attRows = Attendance::whereHas('enrollment.student', function($query) {
                            $query->whereHas('user', function($q) {
                                $q->role('student')->where('status', 1);
                            });
                        })
                        ->whereBetween('attendance_date', [$monthStart, now()->endOfMonth()->toDateString()])
                        ->toBase()
                        ->selectRaw("attendances.status AS st, (attendances.status = 4 AND LOWER(TRIM(COALESCE(attendances.remarks, ''))) = 'holiday') AS is_holiday, COUNT(*) AS c")
                        ->groupBy('st', 'is_holiday')->get();

    $attendanceCounts = ['Present' => 0, 'Late' => 0, 'Half Day' => 0, 'Absent' => 0, 'Leave' => 0];
    foreach ($attRows as $r) {
        $key = match ((int) $r->st) {
            1 => 'Present',
            2 => 'Late',
            3 => 'Half Day',
            0 => 'Absent',
            default => ((int) $r->is_holiday === 1 ? null : 'Leave'),
        };
        if ($key !== null) {
            $attendanceCounts[$key] += (int) $r->c;
        }
    }

    return view('admin.dashboard', compact(
        'activeUsersCount', 'newUsersThisMonth',
        'finance', 'staffCards',
        'monthLabels', 'revenueSeries', 'expenseSeries', 'usersSeries',
        'totalStudents', 'presentStudents', 'studentPercent',
        'currentSession', 'sessionCounts',
        'sessionLabels', 'sessionRevenue', 'sessionExpense',
        'sessionRevenueTotal', 'sessionExpenseTotal', 'sessionNet',
        'attendanceCounts'
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

    // Change Password Page (admin + superadmin, shared staff view)
    public function AdminChangePassword()
    {
        return view('staff_profile.change_password', [
            'updateRoute' => route(Auth::user()->isSuperAdmin() ? 'superadmin.password.update' : 'admin.password.update'),
        ]);
    }
    // End Method


    // Password Update (sirf apna password: Auth::user())
    public function AdminPasswordUpdate(StaffPasswordUpdateRequest $request, StaffSelfProfileService $service)
    {
        $user = Auth::user();

        // Logout se pehle login route tay karein (superadmin ka apna login page hai)
        $loginRoute = $user->isSuperAdmin() ? 'superadmin.login' : 'admin.login';

        $service->updatePassword($user, $request->validated()['password']);

        // Password change ke baad logout (AdminLogout jaisa) aur login page par redirect
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($loginRoute)->with([
            'message' => 'Password changed successfully. Please login with your new password.',
            'alert-type' => 'success'
        ]);
    }
    // End Method

    // 1. Forgot Password Page Load

}
