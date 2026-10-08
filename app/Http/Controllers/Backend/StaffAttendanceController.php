<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use App\Models\EventType;
use App\Models\StaffAttendance;
use App\Models\User;
use App\Services\OffDays;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class StaffAttendanceController extends Controller
{
    protected $excludedRoles = ['superadmin', 'admin', 'parent', 'student', 'user'];

    // ==========================================
    // INDEX — listing of marked attendance (read-only) for a date
    // ==========================================
    public function index(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        $roleFilter = $request->query('role');

        $roles = Role::whereNotIn('name', $this->excludedRoles)->pluck('name');

        $query = StaffAttendance::with(['staff.roles', 'markedBy'])
            ->where('date', $date);

        if ($roleFilter) {
            $query->whereHas('staff.roles', function ($q) use ($roleFilter) {
                $q->where('name', $roleFilter);
            });
        }

        $records = $query->get();

        $summary = [
            'total'    => User::whereHas('roles', fn($q) => $q->whereNotIn('name', $this->excludedRoles))->count(),
            'present'  => $records->where('status', 'present')->count(),
            'absent'   => $records->where('status', 'absent')->count(),
            'half_day' => $records->where('status', 'half_day')->count(),
            'leave'    => $records->where('status', 'leave')->count(),
        ];

        return view('admin.staff_attendance.index', compact('records', 'date', 'roles', 'roleFilter', 'summary'));
    }

    // ==========================================
    // CREATE — Mark Attendance form (naya ya same-date-existing dono handle karta hai)
    // ==========================================
    public function CreateAttendance(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        $roleFilter = $request->query('role');

        $roles = Role::whereNotIn('name', $this->excludedRoles)->pluck('name');

        $staffMembers = collect();

        if ($roleFilter) {
            $staffMembers = User::whereHas('roles', function ($q) use ($roleFilter) {
                $q->where('name', $roleFilter);
            })->with('roles')->orderBy('name')->get();
        }

        $existing = StaffAttendance::where('date', $date)->get()->keyBy('user_id');

        // Sirf selected role ke staff IDs ke against check karein
        $isEditMode = $roleFilter
            && $staffMembers->isNotEmpty()
            && $staffMembers->pluck('id')->intersect($existing->keys())->isNotEmpty();

        $summary = [
            'total'    => $roleFilter
                ? User::whereHas('roles', fn($q) => $q->where('name', $roleFilter))->count()
                : 0,
            'present'  => $staffMembers->pluck('id')->map(fn($id) => $existing[$id]->status ?? null)->filter(fn($s) => $s === 'present')->count(),
            'absent'   => $staffMembers->pluck('id')->map(fn($id) => $existing[$id]->status ?? null)->filter(fn($s) => $s === 'absent')->count(),
            'half_day' => $staffMembers->pluck('id')->map(fn($id) => $existing[$id]->status ?? null)->filter(fn($s) => $s === 'half_day')->count(),
            'leave'    => $staffMembers->pluck('id')->map(fn($id) => $existing[$id]->status ?? null)->filter(fn($s) => $s === 'leave')->count(),
        ];

        $isHoliday = $this->isHoliday($date);

        return view('admin.staff_attendance.create', compact(
            'staffMembers',
            'date',
            'existing',
            'roles',
            'roleFilter',
            'summary',
            'isHoliday',
            'isEditMode'
        ));
    }

    // ==========================================
    // EDIT — Report/Index se kisi date ki attendance edit karne ke liye
    // ==========================================
    public function EditAttendance(Request $request)
    {
        $date = $request->query('date');
        $role = $request->query('role');

        if (!$date) {
            return redirect()->route('staff_attendance.create')
                ->with('error', 'Please select a date to edit.');
        }

        $exists = StaffAttendance::where('date', $date)->exists();

        if (!$exists) {
            return redirect()->route('staff_attendance.create', ['date' => $date, 'role' => $role])
                ->with('info', 'No attendance found for this date yet — you can mark it now.');
        }

        $request->merge(['date' => $date, 'role' => $role]);
        return $this->CreateAttendance($request);
    }

    // ==========================================
    // STORE — naya attendance bulk save
    // ==========================================
    public function StoreAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date'           => 'required|date',
            'role'           => 'required|string',
            'attendance'     => 'required|array',
            'attendance.*'   => 'required|in:present,absent,half_day,leave',
            'time_out'       => 'nullable|array',
            'remarks'        => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('staff_attendance.create', ['date' => $request->date, 'role' => $request->role])
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->attendance as $userId => $status) {

                    $timeOut = $request->time_out[$userId] ?? null;
                    $remarks = $request->remarks[$userId] ?? null;

                    $existing = StaffAttendance::where('user_id', $userId)
                        ->where('date', $request->date)
                        ->first();

                    // Row bilkul naya hai — create karo, marked_at = now()
                    if (!$existing) {
                        StaffAttendance::create([
                            'user_id'   => $userId,
                            'date'      => $request->date,
                            'status'    => $status,
                            'time_out'  => $timeOut,
                            'remarks'   => $remarks,
                            'marked_by' => auth()->id(),
                            'marked_at' => now(),
                        ]);
                        continue;
                    }

                    // Row already exist karti hai — sirf tab update karo jab kuch actually change hua ho
                    $changed = $existing->status   !== $status
                        || $existing->time_out !== $timeOut
                        || $existing->remarks  !== $remarks;

                    if ($changed) {
                        $existing->update([
                            'status'    => $status,
                            'time_out'  => $timeOut,
                            'remarks'   => $remarks,
                            'marked_by' => auth()->id(),
                            'marked_at' => now(),
                        ]);
                    }
                    // Agar $changed false hai to skip — purana marked_at/marked_by intact rahega
                }
            });

            return redirect()
                ->route('staff_attendance.index', ['date' => $request->date])
                ->with('success', 'Attendance saved successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('staff_attendance.create', ['date' => $request->date, 'role' => $request->role])
                ->with('error', 'Something went wrong while saving attendance.');
        }
    }

    // ==========================================
    // UPDATE — existing attendance ko update karna
    // ==========================================
    public function UpdateAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date'           => 'required|date',
            'role'           => 'required|string',
            'attendance'     => 'required|array',
            'attendance.*'   => 'required|in:present,absent,half_day,leave',
            'time_out'       => 'nullable|array',
            'remarks'        => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('staff_attendance.create', ['date' => $request->date, 'role' => $request->role])
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->attendance as $userId => $status) {

                    $timeOut = $request->time_out[$userId] ?? null;
                    $remarks = $request->remarks[$userId] ?? null;

                    $existing = StaffAttendance::where('user_id', $userId)
                        ->where('date', $request->date)
                        ->first();

                    if (!$existing) {
                        StaffAttendance::create([
                            'user_id'   => $userId,
                            'date'      => $request->date,
                            'status'    => $status,
                            'time_out'  => $timeOut,
                            'remarks'   => $remarks,
                            'marked_by' => auth()->id(),
                            'marked_at' => now(),
                        ]);
                        continue;
                    }

                    $changed = $existing->status   !== $status
                        || $existing->time_out !== $timeOut
                        || $existing->remarks  !== $remarks;

                    if ($changed) {
                        $existing->update([
                            'status'    => $status,
                            'time_out'  => $timeOut,
                            'remarks'   => $remarks,
                            'marked_by' => auth()->id(),
                            'marked_at' => now(),
                        ]);
                    }
                }
            });

            return redirect()
                ->route('staff_attendance.index', ['date' => $request->date])
                ->with('success', 'Attendance updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('staff_attendance.create', ['date' => $request->date, 'role' => $request->role])
                ->with('error', 'Something went wrong while updating attendance.');
        }
    }

    // ==========================================
    // REPORT — full attendance report (date range + role filter)
    // ==========================================
    public function ReportAttendnce(Request $request)
    {
        $fromDate   = $request->query('from_date', now()->startOfMonth()->toDateString());
        $toDate     = $request->query('to_date', now()->toDateString());
        $roleFilter = $request->query('role');

        $roles = Role::whereNotIn('name', $this->excludedRoles)->pluck('name');

        $query = StaffAttendance::with(['staff.roles', 'markedBy'])
            ->whereBetween('date', [$fromDate, $toDate]);

        if ($roleFilter) {
            $query->whereHas('staff.roles', function ($q) use ($roleFilter) {
                $q->where('name', $roleFilter);
            });
        }

        $records = $query->orderByDesc('date')->get();

        $totalQuery = User::whereHas('roles', function ($q) {
            $q->whereNotIn('name', $this->excludedRoles);
        });

        if ($roleFilter) {
            $totalQuery->whereHas('roles', function ($q) use ($roleFilter) {
                $q->where('name', $roleFilter);
            });
        }

        $summary = [
            'total'    => $totalQuery->count(),
            'present'  => $records->where('status', 'present')->count(),
            'absent'   => $records->where('status', 'absent')->count(),
            'half_day' => $records->where('status', 'half_day')->count(),
            'leave'    => $records->where('status', 'leave')->count(),
        ];

        return view('admin.staff_attendance.report', compact(
            'records',
            'fromDate',
            'toDate',
            'roles',
            'roleFilter',
            'summary'
        ));
    }

    // ==========================================
    // Holiday check helper
    // ==========================================
    protected function isHoliday($date)
    {
        // Calendar off-day (status = 1, event type is_off_day = 1); weekly off yahan shamil nahi
        $day = \Carbon\Carbon::parse($date);

        return isset(OffDays::dates($day, $day)[$day->toDateString()]);
    }

    // ==========================================
    // Mark a date as Holiday (AJAX)
    // ==========================================
    public function markHoliday(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'A valid date is required.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // Pehle se off day ho to dobara entry nahi banti
            if ($this->isHoliday($request->date)) {
                return response()->json([
                    'success' => true,
                    'message' => 'This date is already a holiday.',
                ]);
            }

            $eventType = EventType::firstOrCreate(
                ['name' => 'Holiday'],
                ['color' => '#dc3545', 'status' => 1, 'is_off_day' => true]
            );

            // "Holiday" naam ka type hamesha off day hona chahiye
            if (!$eventType->is_off_day) {
                $eventType->update(['is_off_day' => true]);
            }

            $currentYear = AcademicYear::where('is_current', 1)->first();

            if (!$currentYear) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active academic year found. Please set one first.',
                ], 422);
            }

            AcademicCalendar::create([
                'academic_year_id' => $currentYear->id,
                'title'            => 'Staff Holiday',
                'start_date'       => $request->date,
                'end_date'         => $request->date,
                'event_type_id'    => $eventType->id,
                'color'            => $eventType->color,
                'all_day'          => true,
                'status'           => 1,
                'created_by'       => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Holiday marked successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while marking the holiday.',
            ], 500);
        }
    }

    // ==========================================
    // SHOW — single staff member ki all-time attendance detail
    // ==========================================
    public function ShowAttendance(Request $request, $user)
    {
        $staff = User::with('roles')->findOrFail($user);

        return view('admin.staff_attendance.show', array_merge(['staff' => $staff], $this->attendanceData($request, $staff)));
    }

    // ==========================================
    // MY ATTENDANCE — logged-in staff ki apni attendance (read-only)
    // ==========================================
    public function myAttendance(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'from_date' => 'nullable|date',
            'to_date'   => 'nullable|date|after_or_equal:from_date',
            'status'    => 'nullable|in:present,absent,half_day,leave',
        ], [
            'to_date.after_or_equal' => 'To Date must be on or after From Date',
        ]);

        // GET filter galat ho to bina params wale page par wapas (back() same URL par loop kar sakta hai)
        if ($validator->fails()) {
            return redirect()->route('my.attendance.index')->withErrors($validator);
        }

        // Sirf apna record, kisi aur staff ka nahi
        $staff = User::with('roles')->findOrFail(auth()->id());

        return view('my.attendance.index', array_merge(['staff' => $staff], $this->attendanceData($request, $staff)));
    }
    // End Method

    // ShowAttendance aur myAttendance dono ki shared query (date range + status, counts, %, monthly)
    private function attendanceData(Request $request, User $staff): array
    {
        $fromDate     = $request->query('from_date', now()->startOfMonth()->toDateString());
        $toDate       = $request->query('to_date', now()->toDateString());
        $statusFilter = $request->query('status');

        $query = StaffAttendance::with('markedBy')
            ->where('user_id', $staff->id)
            ->whereBetween('date', [$fromDate, $toDate]);

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $records = $query->orderByDesc('date')->get();

        $summary = [
            'present'  => $records->where('status', 'present')->count(),
            'absent'   => $records->where('status', 'absent')->count(),
            'half_day' => $records->where('status', 'half_day')->count(),
            'leave'    => $records->where('status', 'leave')->count(),
        ];
        $summary['total'] = $records->count();
        $summary['percentage'] = $summary['total'] > 0
            ? round((($summary['present'] + ($summary['half_day'] * 0.5)) / $summary['total']) * 100, 1)
            : 0;

        // Month-wise breakdown — trend chart ke liye
        $monthly = $records
            ->groupBy(fn($r) => \Carbon\Carbon::parse($r->date)->format('Y-m'))
            ->map(function ($group, $month) {
                return [
                    'month'    => \Carbon\Carbon::parse($month . '-01')->format('M Y'),
                    'present'  => $group->where('status', 'present')->count(),
                    'absent'   => $group->where('status', 'absent')->count(),
                    'half_day' => $group->where('status', 'half_day')->count(),
                    'leave'    => $group->where('status', 'leave')->count(),
                ];
            })
            ->sortKeys()
            ->values();

        return compact(
            'records',
            'summary',
            'monthly',
            'fromDate',
            'toDate',
            'statusFilter'
        );
    }
    // End Method
}
