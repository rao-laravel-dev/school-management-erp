<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\StaffPasswordUpdateRequest;
use App\Models\AcademicYear;
use App\Models\SalarySlip;
use App\Models\StaffAttendance;
use App\Models\StaffLeaveApplication;
use App\Models\StudentFees;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ImageService;
use App\Services\StaffSelfProfileService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountantProfileController extends Controller
{
     public function AccountantManage()
{
    // Har block apni permission par chalta hai; permission na ho to safe empty defaults view ko jate hain
    $authUser = auth()->user();
    $canFees = $authUser->can('access-fee-collections');
    $canSlips = $authUser->can('access-salary-slips');
    $canAtt = $authUser->can('access-my-attendance');
    $canLeave = $authUser->can('access-leave-application');

    $today = now()->toDateString();
    $monthStart = now()->startOfMonth()->toDateString();
    $monthEnd = now()->endOfMonth()->toDateString();
    $lastStart = now()->subMonthNoOverflow()->startOfMonth();
    $lastEnd = $lastStart->copy()->day(min(now()->day, $lastStart->daysInMonth))->toDateString();

    $collectedThis = $collectedLast = 0;
    $collectedDaily = [];
    $pendingStats = (object) ['cnt' => 0, 'amount' => 0];
    $feeStatus = ['Paid' => 0, 'Partial' => 0, 'Unpaid' => 0];
    $sessionLabels = $sessionCollected = $pendingLabels = $pendingSeries = [];
    $currentSession = null;
    $recentPayments = collect();

    if ($canFees) {
        // Fees Collected: month-to-date vs pichle mahine ke same din (Admin dashboard wali rule) + daily sparkline, ek grouped query
        $rows = Transaction::where('type', 'income')->where('category', 'fee')
            ->whereBetween('transaction_date', [$lastStart->toDateString(), $today])
            ->toBase()->selectRaw('transaction_date AS d, SUM(amount) AS s')->groupBy('d')->pluck('s', 'd');
        foreach ($rows as $d => $s) {
            if ($d >= $monthStart) {
                $collectedThis += (float) $s;
            } elseif ($d <= $lastEnd) {
                $collectedLast += (float) $s;
            }
        }
        for ($day = 1; $day <= now()->day; $day++) {
            $collectedDaily[] = (float) ($rows[now()->format('Y-m-') . sprintf('%02d', $day)] ?? 0);
        }

        // Current session ki wo fees jinki due_date is mahine ke end tak hai (purani unpaid bhi shamil): Pending = unpaid + partial ka balance,
        // donut = paid / partial / unpaid; excluded aur trashed students nahi. Session na ho to empty
        $currentSession = AcademicYear::where('is_current', 1)->first(['name', 'start_date', 'end_date']);
        $monthFees = collect();
        if ($currentSession && $currentSession->start_date) {
            $monthFees = StudentFees::whereHas('student')->where('status', '!=', 'excluded')
                ->whereBetween('due_date', [Carbon::parse($currentSession->start_date)->toDateString(), $monthEnd])
                ->toBase()->selectRaw('status, COUNT(*) AS cnt, COALESCE(SUM(amount - discount - paid_amount), 0) AS balance')
                ->groupBy('status')->get()->keyBy('status');

            // Pending sparkline: wahi filters, har due mahine ka baqaya (session start se is mahine tak), khali mahina 0
            $pendingByMonth = StudentFees::whereHas('student')->whereIn('status', ['unpaid', 'partial'])
                ->whereBetween('due_date', [Carbon::parse($currentSession->start_date)->toDateString(), $monthEnd])
                ->toBase()->selectRaw("DATE_FORMAT(due_date, '%Y-%m') AS ym, COALESCE(SUM(amount - discount - paid_amount), 0) AS balance")
                ->groupBy('ym')->pluck('balance', 'ym');
            for ($m = Carbon::parse($currentSession->start_date)->startOfMonth(); $m->lte(now()); $m->addMonthNoOverflow()) {
                $pendingLabels[] = $m->format('M y');
                $pendingSeries[] = (float) ($pendingByMonth[$m->format('Y-m')] ?? 0);
            }
        }
        $feeStatus = [
            'Paid'    => (int) ($monthFees['paid']->cnt ?? 0),
            'Partial' => (int) ($monthFees['partial']->cnt ?? 0),
            'Unpaid'  => (int) ($monthFees['unpaid']->cnt ?? 0),
        ];
        $pendingStats = (object) [
            'cnt'    => $feeStatus['Partial'] + $feeStatus['Unpaid'],
            'amount' => (float) ($monthFees['partial']->balance ?? 0) + (float) ($monthFees['unpaid']->balance ?? 0),
        ];

        // Fee Collection: current session ke har mahine (Admin Revenue vs Expense wali month range), ek grouped query
        if ($currentSession && $currentSession->start_date && $currentSession->end_date) {
            $sFrom = Carbon::parse($currentSession->start_date)->toDateString();
            // end_date mahine ki 1 tareekh ho (01 Jan 2026 -> 01 Jan 2027) to pichla mahina aakhri mahina; start se pehle na jaye
            $sEnd = Carbon::parse($currentSession->end_date);
            if ($sEnd->day === 1) {
                $sEnd = $sEnd->copy()->subDay();
            }
            $sTo = max($sEnd->toDateString(), $sFrom); // same $sTo query aur month loop dono me

            $sessFees = Transaction::where('type', 'income')->where('category', 'fee')
                ->whereBetween('transaction_date', [$sFrom, $sTo])
                ->toBase()->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') AS ym, SUM(amount) AS s")
                ->groupBy('ym')->pluck('s', 'ym');

            $sLast = Carbon::parse($sTo);
            for ($m = Carbon::parse($sFrom)->startOfMonth(); $m->lte($sLast); $m->addMonthNoOverflow()) {
                $sessionLabels[] = $m->format('M y');
                $sessionCollected[] = (float) ($sessFees[$m->format('Y-m')] ?? 0);
            }
        }

        // Recent 10 fee payments; student trashed ho to bhi naam (withTrashed)
        $recentPayments = Transaction::where('type', 'income')->where('category', 'fee')
            ->with(['studentFee.feeType', 'studentFee.student' => fn($q) => $q->withTrashed()])
            ->orderByDesc('transaction_date')->orderByDesc('id')->limit(10)->get();
    }
    $sessionCollectedTotal = array_sum($sessionCollected);

    // Salary slips is mahine (month = Y-m), ek grouped query
    $slipStats = (object) ['total' => 0, 'paid' => 0, 'pending' => 0, 'net' => 0];
    if ($canSlips) {
        $slipStats = SalarySlip::where('month', now()->format('Y-m'))
            ->selectRaw("COUNT(*) AS total, COALESCE(SUM(status = 'paid'), 0) AS paid,
                COALESCE(SUM(status = 'pending'), 0) AS pending, COALESCE(SUM(net_salary), 0) AS net")->first();
    }

    // Apni staff attendance is mahine (My Attendance Report wali % rule; Teacher / Librarian dashboard wali query)
    $userId = Auth::id();
    $myAtt = ['present' => 0, 'half_day' => 0, 'marked' => 0, 'percent' => null];
    if ($canAtt) {
        $staffAtt = StaffAttendance::where('user_id', $userId)->whereBetween('date', [$monthStart, $today])
            ->selectRaw('status, COUNT(*) AS c')->groupBy('status')->pluck('c', 'status');
        $myAtt = [
            'present'  => (int) ($staffAtt['present'] ?? 0),
            'half_day' => (int) ($staffAtt['half_day'] ?? 0),
            'marked'   => (int) $staffAtt->sum(),
        ];
        $myAtt['percent'] = $myAtt['marked'] > 0
            ? round((($myAtt['present'] + 0.5 * $myAtt['half_day']) / $myAtt['marked']) * 100, 1) : null;
    }

    // Apni leave requests: last 3
    $recentLeaves = collect();
    if ($canLeave) {
        $recentLeaves = StaffLeaveApplication::where('user_id', $userId)
            ->orderByDesc('apply_date')->orderByDesc('id')->limit(3)->get();
    }

    return view('accountant.dashboard', compact(
        'collectedThis', 'collectedLast', 'collectedDaily', 'pendingStats', 'feeStatus', 'currentSession',
        'pendingLabels', 'pendingSeries', 'sessionLabels', 'sessionCollected', 'sessionCollectedTotal', 'recentPayments',
        'slipStats', 'myAtt', 'recentLeaves'
    ));
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

    // Purani photo naya save hone ke BAAD delete
    if ($newPhoto && $oldPhoto) {
        $imageService->delete($oldPhoto, 'uploads/accountant_images');
    }

    // redirect back with success message
    return redirect()->back()->with([
        'message' => 'Accountant Profile Updated Successfully',
        'alert-type' => 'success'
    ]);
}
     // End Method


    // Change Password Page
    public function AccountantChangePassword()
    {
        return view('staff_profile.change_password', [
            'updateRoute' => route('accountant.password.update'),
        ]);
    }
    // End Method


    // Password Update
    public function AccountantPasswordUpdate(StaffPasswordUpdateRequest $request, StaffSelfProfileService $service)
    {
        $service->updatePassword(Auth::user(), $request->validated()['password']);

        // Password change ke baad logout (AccountantLogout jaisa) aur login page par redirect
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/accountant/login')->with([
            'message' => 'Password changed successfully. Please login with your new password.',
            'alert-type' => 'success'
        ]);
    }
    // End Method
}
