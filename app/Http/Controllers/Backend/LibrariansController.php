<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\LibrarianProfileUpdateRequest;
use App\Http\Requests\StaffPasswordUpdateRequest;
use App\Models\AcademicYear;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\LibraryMember;
use App\Models\StaffAttendance;
use App\Models\StaffLeaveApplication;
use App\Models\Student;
use App\Models\User;
use App\Services\ImageService;
use App\Services\StaffSelfProfileService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LibrariansController extends Controller
{
    // Dashboard
public function LibrarianManage()
{
    $role = auth()->user()->getRoleNames()->first(); // safe

    // Har block apni permission par chalta hai; permission na ho to safe empty defaults view ko jate hain
    $authUser = auth()->user();
    $canBooks = $authUser->can('access-books');
    $canIssues = $authUser->can('access-book-issues');
    $canMembers = $authUser->can('access-library-members');
    $canAtt = $authUser->can('access-my-attendance');
    $canLeave = $authUser->can('access-leave-application');

    $today = now()->toDateString();

    // Books: titles + copies (ek query)
    $bookStats = (object) ['titles' => 0, 'copies' => 0, 'available' => 0];
    if ($canBooks) {
        $bookStats = Book::selectRaw('COUNT(*) AS titles, COALESCE(SUM(total_copies),0) AS copies, COALESCE(SUM(available_copies),0) AS available')->first();
    }

    // Issued + overdue + lost: status 'overdue' kahin save nahi hota, isliye due_date se nikalte hain
    // Book Status donut books ya issues dono me se kisi ek permission par bhi dikhta hai, isliye dono par chalta hai
    $issueStats = (object) ['issued' => 0, 'overdue' => 0, 'lost' => 0];
    if ($canIssues || $canBooks) {
        $issueStats = BookIssue::selectRaw("COALESCE(SUM(status IN ('issued','overdue')),0) AS issued,
            COALESCE(SUM(status IN ('issued','overdue') AND due_date < ?),0) AS overdue,
            COALESCE(SUM(status = 'lost'),0) AS lost", [$today])->first();
    }

    // Members student / staff
    $memberStats = (object) ['total' => 0, 'students' => 0, 'staff' => 0];
    if ($canMembers) {
        $memberStats = LibraryMember::selectRaw("COUNT(*) AS total, COALESCE(SUM(member_type='student'),0) AS students,
            COALESCE(SUM(member_type='staff'),0) AS staff")->first();
    }

    // Recent 10 issues; names batch mein (member_name accessor har row par find() karta hai)
    $recentIssues = collect();
    $studentNames = collect();
    $staffNames = collect();
    if ($canIssues) {
        $recentIssues = BookIssue::with(['book:id,title', 'libraryMember'])->latest()->limit(10)->get();
        $members = $recentIssues->pluck('libraryMember')->filter();
        $studentNames = Student::withTrashed()
            ->whereIn('id', $members->where('member_type', 'student')->pluck('member_id'))
            ->get()
            ->mapWithKeys(function ($s) {
                return [$s->id => $s->full_name];
            });
        $staffNames = User::whereIn('id', $members->where('member_type', 'staff')->pluck('member_id'))->pluck('name', 'id');
    }

    // Issued / Returned cards: month-to-date vs pichle mahine ke same din (Admin dashboard wali rule) + is mahine ki daily sparkline
    $monthStart = now()->startOfMonth()->toDateString();
    $lastStart = now()->subMonthNoOverflow()->startOfMonth();
    $lastEnd = $lastStart->copy()->day(min(now()->day, $lastStart->daysInMonth))->toDateString();

    $issuedThis = $issuedLast = $returnedThis = $returnedLast = 0;
    $issuedDaily = $returnedDaily = [];
    $overdueSeries = array_fill(0, 12, 0);
    $monthLabels = [];
    $sessionLabels = $sessionIssued = $sessionReturned = [];
    $currentSession = null;
    $bookStatus = ['Available' => 0, 'Issued' => 0, 'Overdue' => 0, 'Lost' => 0];

    if ($canIssues) {
        // $column: issue_date ya return_date; ek grouped query, din 1..aaj 0-fill
        $dailyStats = function ($column) use ($lastStart, $today, $monthStart, $lastEnd) {
            $rows = BookIssue::whereNotNull($column)->whereBetween($column, [$lastStart->toDateString(), $today])
                ->toBase()->selectRaw("$column AS d, COUNT(*) AS c")->groupBy('d')->pluck('c', 'd');
            $thisMonth = 0;
            $last = 0;
            foreach ($rows as $d => $c) {
                if ($d >= $monthStart) {
                    $thisMonth += (int) $c;
                } elseif ($d <= $lastEnd) {
                    $last += (int) $c;
                }
            }
            $daily = [];
            for ($day = 1; $day <= now()->day; $day++) {
                $daily[] = (int) ($rows[now()->format('Y-m-') . sprintf('%02d', $day)] ?? 0);
            }
            return [$thisMonth, $last, $daily];
        };
        [$issuedThis, $issuedLast, $issuedDaily] = $dailyStats('issue_date');
        [$returnedThis, $returnedLast, $returnedDaily] = $dailyStats('return_date');

        // Overdue sparkline: due month ke hisab se pichle 12 mahine; purane due_date pehle bar me (bars ka sum = Overdue count)
        $sparkStart = now()->startOfMonth()->subMonthsNoOverflow(11);
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $m = $sparkStart->copy()->addMonthsNoOverflow($i);
            $months[] = $m->format('Y-m');
            $monthLabels[] = $m->format('M y');
        }
        $overdueByMonth = BookIssue::whereIn('status', ['issued', 'overdue'])->where('due_date', '<', $today)
            ->toBase()->selectRaw("DATE_FORMAT(GREATEST(due_date, ?), '%Y-%m') AS ym, COUNT(*) AS c", [$sparkStart->toDateString()])
            ->groupBy('ym')->pluck('c', 'ym');
        $overdueSeries = array_map(fn($m) => (int) ($overdueByMonth[$m] ?? 0), $months);

        // Issues vs Returns: current session ke har mahine (Admin Revenue vs Expense wali month range), do grouped queries
        $currentSession = AcademicYear::where('is_current', 1)->first(['name', 'start_date', 'end_date']);
        if ($currentSession && $currentSession->start_date && $currentSession->end_date) {
            $sFrom = Carbon::parse($currentSession->start_date)->toDateString();
            // end_date mahine ki 1 tareekh ho (01 Jan 2026 -> 01 Jan 2027) to pichla mahina aakhri mahina; start se pehle na jaye
            $sEnd = Carbon::parse($currentSession->end_date);
            if ($sEnd->day === 1) {
                $sEnd = $sEnd->copy()->subDay();
            }
            $sTo = max($sEnd->toDateString(), $sFrom); // same $sTo query aur month loop dono me

            $sessIssued = BookIssue::whereBetween('issue_date', [$sFrom, $sTo])
                ->toBase()->selectRaw("DATE_FORMAT(issue_date, '%Y-%m') AS ym, COUNT(*) AS c")->groupBy('ym')->pluck('c', 'ym');
            $sessReturned = BookIssue::whereNotNull('return_date')->whereBetween('return_date', [$sFrom, $sTo])
                ->toBase()->selectRaw("DATE_FORMAT(return_date, '%Y-%m') AS ym, COUNT(*) AS c")->groupBy('ym')->pluck('c', 'ym');

            $sLast = Carbon::parse($sTo);
            for ($m = Carbon::parse($sFrom)->startOfMonth(); $m->lte($sLast); $m->addMonthNoOverflow()) {
                $ym = $m->format('Y-m');
                $sessionLabels[] = $m->format('M y');
                $sessionIssued[] = (int) ($sessIssued[$ym] ?? 0);
                $sessionReturned[] = (int) ($sessReturned[$ym] ?? 0);
            }
        }
    }
    $sessionIssuedTotal = array_sum($sessionIssued);
    $sessionReturnedTotal = array_sum($sessionReturned);

    // Book Status donut: Available (books) + Issued / Overdue / Lost (issues)
    if ($canIssues || $canBooks) {
        $bookStatus = [
            'Available' => (int) $bookStats->available,
            'Issued'    => max(0, (int) $issueStats->issued - (int) $issueStats->overdue),
            'Overdue'   => (int) $issueStats->overdue,
            'Lost'      => (int) $issueStats->lost,
        ];
    }

    // Apni staff attendance is mahine (My Attendance Report wali % rule; Teacher dashboard wali query)
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

    // Apni leave requests: last 3 (Teacher dashboard wali query)
    $recentLeaves = collect();
    if ($canLeave) {
        $recentLeaves = StaffLeaveApplication::where('user_id', $userId)
            ->orderByDesc('apply_date')->orderByDesc('id')->limit(3)->get();
    }

    return view('admin.librarian.dashboard', compact(
        'bookStats', 'issueStats', 'memberStats', 'recentIssues', 'studentNames', 'staffNames', 'today',
        'issuedThis', 'issuedLast', 'issuedDaily', 'returnedThis', 'returnedLast', 'returnedDaily',
        'monthLabels', 'overdueSeries', 'currentSession',
        'sessionLabels', 'sessionIssued', 'sessionReturned', 'sessionIssuedTotal', 'sessionReturnedTotal',
        'bookStatus', 'myAtt', 'recentLeaves'
    ));
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


// Change Password Page
public function LibrarianChangePassword()
{
    return view('staff_profile.change_password', [
        'updateRoute' => route('librarian.password.update'),
    ]);
}
// End Method


// Password Update
public function LibrarianPasswordUpdate(StaffPasswordUpdateRequest $request, StaffSelfProfileService $service)
{
    $service->updatePassword(Auth::user(), $request->validated()['password']);

    // Password change ke baad logout (LibrarianLogout jaisa) aur login page par redirect
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/librarian/login')->with([
        'message' => 'Password changed successfully. Please login with your new password.',
        'alert-type' => 'success'
    ]);
}
// End Method
}
