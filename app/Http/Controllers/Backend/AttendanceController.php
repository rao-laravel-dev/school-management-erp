<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\QrCode;
use App\Models\SchoolClass;
use App\Models\SchoolTiming;
use App\Models\Section;
use App\Models\StaffAttendance;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\OffDays;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function AllAttendance(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $totalClasses = SchoolClass::count();

        // Attendance done check (Grouped by class and section)
        $attendanceDone = Attendance::where('attendance_date', $date)
            ->select('class_id', 'section_id')
            ->groupBy('class_id', 'section_id')
            ->get()
            ->count();

        $classes = SchoolClass::with('mappedSections')->withCount([
            // Present Card: 1 (Present) + 2 (Late) dono shamil hain
            'attendances as present_count' => fn($q) => $q->where('attendance_date', $date)
                ->whereIn('status', [1, 2]),

            // Absent Card
            'attendances as absent_count'  => fn($q) => $q->where('attendance_date', $date)
                ->where('status', 0),

            // Late Card: Ye alag se sirf status 2 ko ginega
            'attendances as late_count'    => fn($q) => $q->where('attendance_date', $date)
                ->where('status', 2),

            // Half-Day Card
            'attendances as halfday_count' => fn($q) => $q->where('attendance_date', $date)
                ->where('status', 3),

            // Leave Card
            'attendances as leave_count'   => fn($q) => $q->where('attendance_date', $date)
                ->where('status', 4),
        ])->get();

        // Marked By: us date par har class-section ki attendance kin users ne mark ki ("Name (Role)")
        $markers = Attendance::with('markedBy.roles')
            ->where('attendance_date', $date)
            ->whereNotNull('marked_by')
            ->get(['class_id', 'section_id', 'marked_by'])
            ->groupBy(fn($r) => $r->class_id . '-' . $r->section_id)
            ->map(fn($rows) => $rows->pluck('markedBy.name_with_role')->filter()->unique()->implode(', '));

        return view('admin.attendance.index_attendance', compact('totalClasses', 'attendanceDone', 'classes', 'date', 'markers'));
    }


    public function showDetails($class_id)
    {
        $today = date('Y-m-d');
        $class = SchoolClass::findOrFail($class_id);
        $students = Student::where('class_id', $class_id)->with(['attendance' => function ($q) use ($today) {
            $q->where('attendance_date', $today);
        }])->get();

        return view('admin.attendance.show_attendance', compact('class', 'students'));
    }
    // End Method

    // 1. Initial page load (Dropdowns ke liye)
    public function AddAttendance()
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['admin', 'superadmin'])) {
            // Admin ke liye sab open hai
            $classes = SchoolClass::all();
            $sections = Section::all();
        } else {
            // Teacher profile verify karein
            $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();

            if (!$teacher) {
                return redirect()->back()->with('error', 'Teacher profile not found.');
            }

            // Assigned classes aur sections fetch karein
            $assignments = \App\Models\TeacherAssignment::where('teacher_id', $teacher->id)->get();

            $classIds = $assignments->pluck('class_id')->unique();
            $sectionIds = $assignments->pluck('section_id')->unique();

            // Sirf assigned data get karein
            $classes = SchoolClass::whereIn('id', $classIds)->get();
            $sections = Section::whereIn('id', $sectionIds)->get();
        }

        return view('admin.attendance.create_attendance', compact('classes', 'sections'));
    }

    public function markAttendance($class_id = null, $section_id = null, $date = null)
    {
        $data['classes'] = SchoolClass::all();
        $data['selected_class'] = $class_id;
        $data['selected_section'] = $section_id;
        $data['selected_date'] = $date ?? date('Y-m-d');

        // View ka naam update kiya (create -> create_attendance)
        return view('admin.attendance.create_attendance', $data);
    }
    // Helper method to get active session ID

    // --- GetStudents Method (AJAX) ---
    public function GetStudents(Request $request)
    {
        $user = auth()->user();
        $date = $request->attendance_date ?? date('Y-m-d');

        // --- SECURITY CHECK (Sirf Admin ko sab allow hai, Teacher apni assigned class dekhega) ---
        if (!$user->hasAnyRole(['admin', 'superadmin'])) {
            $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();

            if ($teacher) {
                $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $teacher->id)
                    ->where('class_id', $request->class_id)
                    ->where('section_id', $request->section_id)
                    ->exists();

                // Agar teacher us class/section me assigned nahi hai toh error return karein
                if (!$isAssigned) {
                    return response()->json(['error' => 'Unauthorized Access'], 403);
                }
            }
        }
        // --- END SECURITY CHECK ---

        // 1. Students fetch karein with attendance for the selected date
        $students = \App\Models\Enrollment::with(['student', 'attendance' => function ($query) use ($date) {
            $query->where('attendance_date', $date);
        }])
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->get();

        // 2. Check karein kya is class, section aur date ki attendance DB mein majood hai?
        $isMarked = \App\Models\Attendance::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('attendance_date', $date)
            ->exists();

        // 3. Dono cheezein JSON mein return karein
        return response()->json([
            'students' => $students,
            'is_marked' => $isMarked
        ]);
    }

    // --- StoreAttendance Method ---
    public function StoreAttendance(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->hasAnyRole(['admin', 'superadmin']);

        // Ab line 199 par ye error nahi aayega
        Log::info("Logged In User ID: " . $user->id);

        $request->validate([
            'attendance_date' => 'required|date',
            'class_id'        => 'required',
            'section_id'      => 'required',
        ]);

        // 1. Authorization Check (Naya logic)
        if (!$isAdmin) {
            // 1. Pehle User ID se Teacher ka record uthayein
            $teacher = Teacher::where('user_id', $user->id)->first();

            if (!$teacher) {
                return redirect()->back()->with('error', 'Teacher profile not found.');
            }

            // 2. Ab Teacher table ki id use karein
            $isAssigned = TeacherAssignment::where('teacher_id', $teacher->id)
                ->where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->exists();

            if (!$isAssigned) {
                return redirect()->back()->with('error', 'You are not authorized to mark attendance for this class or section.');
            }
        }

        // 2. Date Gap Check (Purana logic)
        if (!$isAdmin) {
            $lastAttendance = Attendance::where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->max('attendance_date');

            if ($lastAttendance && \Carbon\Carbon::parse($request->attendance_date)->diffInDays(\Carbon\Carbon::parse($lastAttendance)) > 1) {
                return redirect()->back()->with('error', 'You cannot skip dates. Please fill previous dates first.');
            }
        }

        // 2. Variables
        $date = $request->attendance_date;
        $class_id = $request->class_id;
        $section_id = $request->section_id;
        $activeSessionId = $this->getActiveSessionId();

        // 3. Database Transaction
        DB::transaction(function () use ($request, $date, $class_id, $section_id, $activeSessionId, $user) {

            if ($request->has('is_holiday') && $request->is_holiday == 'on') {
                Attendance::where('class_id', $class_id)
                    ->where('section_id', $section_id)
                    ->where('attendance_date', $date)
                    ->delete();

                $enrollments = Enrollment::where('class_id', $class_id)
                    ->where('section_id', $section_id)
                    ->where('academic_year_id', $activeSessionId)
                    ->get();

                foreach ($enrollments as $e) {
                    Attendance::create([
                        'enrollment_id'   => $e->id,
                        'class_id'        => $class_id,
                        'section_id'      => $section_id,
                        'status'          => 4,
                        'attendance_date' => $date,
                        'remarks'         => 'Holiday',
                        'marked_via'      => 'manual',   // NAYA
                        'marked_by'       => $user->id,  // NAYA
                    ]);
                }
            } else if ($request->has('status')) {
                foreach ($request->status as $enrollment_id => $status) {
                    Attendance::updateOrCreate(
                        [
                            'enrollment_id'   => $enrollment_id,
                            'attendance_date' => $date
                        ],
                        [
                            'class_id'   => $class_id,
                            'section_id' => $section_id,
                            'status'     => $status,
                            'remarks'    => $request->remarks[$enrollment_id] ?? null,
                            'time_out'   => $request->time_out[$enrollment_id] ?? null,
                            'marked_via' => 'manual',
                            'marked_by'  => $user->id,   // already $user = auth()->user() upar defined hai
                        ]
                    );
                }
            }
        });

        // 4. Final Response
        if ($request->has('is_holiday') && $request->is_holiday == 'on') {

            return response()->json([
                'status' => 'success',
                'message' => 'Today has been marked as a Holiday!',
                'redirect' => url()->previous()
            ]);
        } else if ($request->has('status')) {
            // Check karein ke record pehle se tha ya nahi (Update vs Submit)
            $alreadyExists = Attendance::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('attendance_date', $date)
                ->exists();

            $msg = $alreadyExists ? 'Attendance updated successfully!' : 'Attendance submitted successfully!';

            // Redirect path define karein
            $url = auth()->user()->hasRole('teacher') ? route('attendance.report') : route('attendance.index');

            return response()->json([
                'status' => 'success',
                'message' => $msg,
                'redirect' => $url
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'No attendance data found to save.'], 400);
    }

    // --- EditAttendance Method ---
    public function EditAttendance($id, Request $request)
    {

        $user = auth()->user();

        // Authorization Check for Teacher
        if (!$user->hasAnyRole(['admin', 'superadmin'])) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            $isAssigned = TeacherAssignment::where('teacher_id', $teacher->id ?? 0)
                ->where('class_id', $id)
                ->exists();
            if (!$isAssigned) {
                return redirect()->back()->with('You are not authorized to edit attendance for this class.');
            }
        }


        $section_id = $request->query('section_id');
        $date = $request->query('attendance_date', date('Y-m-d'));
        $activeSessionId = $this->getActiveSessionId(); // Session ID fetch

        // Yahan variable ka naam 'classes' rakhein taake view mein error na aaye
        $classes = SchoolClass::all();
        $sections = Section::all();
        $currentClass = SchoolClass::find($id);

        if (!$currentClass) return "Class not found!";

        $students = Enrollment::where('class_id', $id)
            ->where('section_id', $section_id)
            ->where('academic_year_id', $activeSessionId) // FILTER ADDED
            ->with(['student', 'attendance' => function ($q) use ($date) {
                $q->where('attendance_date', $date);
            }])->get();

        // Compact mein 'classes' pass karein
        return view('admin.attendance.index_attendance', [
            'classes'        => $classes,
            'sections'       => $sections,
            'students'       => $students,
            'selected_class' => $id,
            'selected_date'  => $date,
            'section_id'     => $section_id
        ]);
    }

    // --- MarkClassDeparture Method (Updated) ---
    public function markClassDeparture(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'section_id' => 'required',
            'attendance_date' => 'required',
            'departure_time' => 'required'
        ]);

        $activeSessionId = $this->getActiveSessionId();

        // Sirf current session ke students ka hi record update ho
        $updated = Attendance::where('attendance_date', $request->attendance_date)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->whereIn('status', [1, 2])
            ->whereHas('enrollment', fn($q) => $q->where('academic_year_id', $activeSessionId)) // Session filter
            ->update(['time_out' => $request->departure_time]);

        return response()->json(['success' => 'Departure time updated for the class!']);
    }

    // --- ViewMyAttendance Method (Updated) ---
    public function viewMyAttendance(Request $request)
    {
        $user = auth()->user();
        $studentId = session('active_student_id');
        $activeSessionId = $this->getActiveSessionId(); // Session ID fetch

        if (!$studentId) {
            return redirect()->back()->with('error', 'Please select a student first.');
        }

        $student = Student::with(['currentEnrollment.schoolClass', 'currentEnrollment.section'])
            ->findOrFail($studentId);

        if ($user->hasRole('student') && $student->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        // Active enrollment ka check (session wise)
        $enrollment = Enrollment::where('student_id', $student->id)
            ->where('academic_year_id', $activeSessionId)
            ->first();

        if (!$enrollment) {
            return redirect()->back()->with('error', 'Active enrollment for this session not found.');
        }

        // Academic year ki range: start se aaj tak (future mahine navigation mein nahi)
        $today = now()->startOfDay();
        $academicYear = AcademicYear::find($enrollment->academic_year_id);
        $rangeStart = ($academicYear && $academicYear->start_date)
            ? $academicYear->start_date->copy()->startOfDay()
            : $today->copy()->startOfYear();
        $rangeEnd = ($academicYear && $academicYear->end_date)
            ? $academicYear->end_date->copy()->startOfDay()
            : $today->copy()->endOfYear()->startOfDay();
        if ($rangeEnd->gt($today)) {
            $rangeEnd = $today->copy();
        }
        if ($rangeEnd->lt($rangeStart)) {
            $rangeEnd = $rangeStart->copy();
        }

        // ?month=&year= GET params: galat/khali ho to default (rangeEnd ka mahina), redirect ya error nahi
        $reqMonth = (int) $request->query('month');
        $reqYear = (int) $request->query('year');
        $selectedMonth = ($reqMonth >= 1 && $reqMonth <= 12 && $reqYear >= 2000 && $reqYear <= 2100)
            ? Carbon::create($reqYear, $reqMonth, 1)->startOfDay()
            : $rangeEnd->copy()->startOfMonth();
        if ($selectedMonth->lt($rangeStart->copy()->startOfMonth())) {
            $selectedMonth = $rangeStart->copy()->startOfMonth();
        }
        if ($selectedMonth->gt($rangeEnd->copy()->startOfMonth())) {
            $selectedMonth = $rangeEnd->copy()->startOfMonth();
        }

        // Attendance fetch (Session + Month/Year wise)
        $allAttendance = Attendance::where('enrollment_id', $enrollment->id)
            ->whereMonth('attendance_date', $selectedMonth->month)
            ->whereYear('attendance_date', $selectedMonth->year)
            ->get();

        $matrix = [];
        $stats = ['P' => 0, 'A' => 0, 'L' => 0, 'H' => 0, 'F' => 0];

        foreach ($allAttendance as $att) {
            $day = \Carbon\Carbon::parse($att->attendance_date)->format('j');
            $matrix[$day] = $att->status;

            // Mapping status logic...
            if ($att->status == 1) $stats['P']++;
            if ($att->status == 0) $stats['A']++;
            if ($att->status == 2) $stats['L']++;
            if ($att->status == 3) $stats['H']++;
            if ($att->status == 4) $stats['F']++;
        }

        // Student page ka data (calendar, legend, rings, yearly): sab dayKey() se, taake cards/legend/% ek hi rule par chalein
        $yearRows = Attendance::where('enrollment_id', $enrollment->id)
            ->whereBetween('attendance_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->attendance_date)->toDateString());
        $offDates = OffDays::dates($rangeStart, $rangeEnd->copy()->endOfMonth());
        $weeklyOff = ($academicYear && is_array($academicYear->weekly_off_days)) ? $academicYear->weekly_off_days : ['Sunday'];

        $legend = ['present' => 0, 'absent' => 0, 'late' => 0, 'halfday' => 0, 'leave' => 0, 'holiday' => 0];
        $calendar = array_fill(0, $selectedMonth->dayOfWeekIso - 1, null); // Monday-first padding
        for ($d = 1; $d <= $selectedMonth->daysInMonth; $d++) {
            $date = $selectedMonth->copy()->day($d);
            $dateStr = $date->toDateString();
            $key = $date->lt($rangeStart)
                ? 'none'
                : $this->dayKey($yearRows->get($dateStr), $dateStr, $offDates, $weeklyOff, $today);
            $cssKey = $key === 'weekoff' ? 'holiday' : $key; // weekly off aur holiday ek hi rang
            // Future holidays calendar mein grey dikhte hain par count nahi hote (sirf aaj tak)
            if (isset($legend[$cssKey]) && !$date->gt($today)) {
                $legend[$cssKey]++;
            }
            $calendar[] = ['day' => $d, 'date' => $dateStr, 'key' => $cssKey, 'isToday' => $date->eq($today), 'isFuture' => $date->gt($today)];
        }
        $calendar = array_chunk($calendar, 7);
        $lastWeek = count($calendar) - 1;
        $calendar[$lastWeek] = array_pad($calendar[$lastWeek], 7, null);
        $monthPercent = $this->attendancePercent(['P' => $legend['present'], 'A' => $legend['absent'], 'L' => $legend['late'], 'H' => $legend['halfday']]);

        // Yearly: academic year ke har mahine ka din-by-din status + counts + %
        $yearMonths = [];
        $yearTotals = ['P' => 0, 'A' => 0, 'L' => 0, 'H' => 0, 'F' => 0];
        for ($cursor = $rangeStart->copy()->startOfMonth(); $cursor->lte($rangeEnd); $cursor->addMonth()) {
            $days = [];
            $counts = ['P' => 0, 'A' => 0, 'L' => 0, 'H' => 0, 'F' => 0];
            for ($d = 1; $d <= $cursor->daysInMonth; $d++) {
                $date = $cursor->copy()->day($d);
                $dateStr = $date->toDateString();
                $key = $date->lt($rangeStart)
                    ? 'none'
                    : $this->dayKey($yearRows->get($dateStr), $dateStr, $offDates, $weeklyOff, $today);
                $days[$d] = $key;
                $letter = ['present' => 'P', 'absent' => 'A', 'late' => 'L', 'halfday' => 'H', 'leave' => 'F'][$key] ?? null;
                if ($letter) {
                    $counts[$letter]++;
                    $yearTotals[$letter]++;
                }
            }
            $yearMonths[] = [
                'label' => $cursor->format('M'),
                'year' => $cursor->year,
                'month' => $cursor->month,
                'days' => $days,
                'counts' => $counts,
                'percent' => $this->attendancePercent($counts),
            ];
        }
        $yearPercent = $this->attendancePercent($yearTotals);

        $todayKey = $this->dayKey($yearRows->get($today->toDateString()), $today->toDateString(), $offDates, $weeklyOff, $today);
        if ($todayKey === 'weekoff') {
            $todayKey = 'holiday';
        }
        $todayLabels = ['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'halfday' => 'Half-day', 'leave' => 'Leave', 'holiday' => 'Holiday', 'unmarked' => 'Not marked'];
        $todayInfo = ['date' => $today, 'key' => $todayKey, 'label' => $todayLabels[$todayKey]];

        $prevMonth = $selectedMonth->copy()->subMonth()->gte($rangeStart->copy()->startOfMonth()) ? $selectedMonth->copy()->subMonth() : null;
        $nextMonth = $selectedMonth->copy()->addMonth()->lte($rangeEnd) ? $selectedMonth->copy()->addMonth() : null;

        if ($user->hasRole('student')) {
            return view('student.attendance.view_myattendance', compact(
                'student', 'matrix', 'allAttendance', 'stats',
                'selectedMonth', 'prevMonth', 'nextMonth', 'monthPercent', 'yearPercent',
                'calendar', 'legend', 'yearMonths'
            ) + ['today' => $todayInfo]);
        } else {
            return view('admin.attendance.student_view_attendance', compact('student', 'enrollment', 'matrix', 'allAttendance', 'stats'));
        }
    }
    // End Method

    // Ek din ka status key. Priority: attendance row (0-3, 4) > calendar holiday > weekly off > future/unmarked
    private function dayKey($row, string $date, array $offDates, array $weeklyOff, Carbon $today): string
    {
        if ($row) {
            $status = (int) $row->status;
            if ($status === 4) {
                // Status 4 + remarks Holiday = holiday, warna leave
                return strtolower(trim((string) $row->remarks)) === 'holiday' ? 'holiday' : 'leave';
            }
            $map = [0 => 'absent', 1 => 'present', 2 => 'late', 3 => 'halfday'];
            if (isset($map[$status])) {
                return $map[$status];
            }
        }
        if (isset($offDates[$date])) {
            return 'holiday';
        }
        $d = Carbon::parse($date);
        if (in_array($d->format('l'), $weeklyOff, true)) {
            return 'weekoff';
        }
        return $d->gt($today) ? 'future' : 'unmarked';
    }
    // End Method

    // (P + L + 0.5 x H) / (P + L + H + A) x 100; Leave/holiday/unmarked shamil nahi. Denominator 0 = null
    private function attendancePercent(array $c): ?float
    {
        $total = $c['P'] + $c['L'] + $c['H'] + $c['A'];
        if ($total === 0) {
            return null;
        }
        return round((($c['P'] + $c['L'] + 0.5 * $c['H']) / $total) * 100, 1);
    }
    // End Method

    public function viewReport(Request $request)
    {
        $userId = Auth::id();
        $student = Student::where('user_id', $userId)->first();
        $enrollment = Enrollment::where('student_id', $student->id)->latest()->first();

        // Default range: 1 Month
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $attendanceRecords = Attendance::where('enrollment_id', $enrollment->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->latest('attendance_date')
            ->get();

        return view('admin.attendance.student_report_attendance', compact('student', 'attendanceRecords', 'startDate', 'endDate'));
    }

    // Scan scope: null = full access (admin / superadmin / receptionist gate).
    // Teacher = apni class-teacher assignment(s) (active session) ki collection; assignment na ho to empty (fail closed)
    private function scanScope()
    {
        $user = auth()->user();

        if ($user->enabledRoleNames()->intersect(['admin', 'superadmin', 'receptionist'])->isNotEmpty()) {
            return null;
        }

        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) {
            return collect();
        }

        return TeacherAssignment::with(['schoolClass', 'section'])
            ->where('teacher_id', $teacher->id)
            ->where('academic_year_id', $this->getActiveSessionId())
            ->get();
    }

    public function ScanAttendance()
    {
        $scope       = $this->scanScope();
        $classScoped = $scope !== null;
        $noClass     = $classScoped && $scope->isEmpty();
        $scopeLabel  = $classScoped
            ? $scope->map(fn($a) => optional($a->schoolClass)->name . ' - ' . optional($a->section)->name)->implode(', ')
            : null;

        return view('admin.attendance.scan_attendance', compact('classScoped', 'noClass', 'scopeLabel'));
    }
    // End Method

    public function GetLatestScan()
    {
        // Class-scoped teacher ko gate ka global latest-scan card nahi dikhana
        if ($this->scanScope() !== null) {
            return response()->json([]);
        }

        return response()->json(Cache::get('kiosk_latest_scan', []));
    }
    // End Method

    public function StoreScanAttendance(Request $request)
    {
        $request->validate([
            'code'      => 'required|string',
            'device_id' => 'nullable|string',
        ]);

        $code = preg_replace('/\s+/', '', ltrim(trim($request->code), '#')); // leading "#" aur spaces hata do

        // Scope server-side (client par bharosa nahi): null = full, collection = teacher ki apni class(es)
        $scope = $this->scanScope();
        if ($scope !== null && $scope->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No class assigned to you for the current session.'], 403);
        }
        $staffDenied = response()->json(['status' => 'error', 'message' => 'Staff attendance cannot be marked from the teacher portal.'], 403);

        // STEP 1: QR/Barcode code se owner dhoondo — Student ya Staff dono isi table se aate hain
        $qrRecord = QrCode::where('code', $code)->first();

        if ($qrRecord && $qrRecord->owner) {
            if ($qrRecord->owner_type === Student::class) {
                return $this->handleStudentScan($qrRecord->owner, $request->device_id, $scope);
            }
            if ($qrRecord->owner_type === User::class) {
                if ($scope !== null) {
                    return $staffDenied;
                }
                return $this->handleStaffScan($qrRecord->owner, $request->device_id);
            }
        }

        // STEP 2: Staff ka Employee ID (jaisa TEACH001 / KHAWAR004) — manual type ke liye, saare staff roles
        // users.username = staff ID hota hai (unique). Sirf active user (status 1), non-trashed profile wala staff
        // (profile na ho ya trashed ho to whereHas fail = seeder/trashed users scan nahi honge; librarian profile abhi nahi, T123)
        $staffUser = User::role(['teacher', 'receptionist', 'accountant', 'librarian'])
            ->where('username', $code)
            ->where('status', 1)
            ->where(function ($q) {
                $q->whereHas('teacher')->orWhereHas('receptionist')->orWhereHas('accountant')->orWhereHas('librarian');
            })
            ->with('roles')
            ->first();
        if ($staffUser) {
            if ($scope !== null) {
                return $staffDenied;
            }
            return $this->handleStaffScan($staffUser, $request->device_id);
        }

        // STEP 3: Roll No (current session); teacher ke liye sirf apni class-section ke andar
        $activeSessionId = $this->getActiveSessionId();
        $enrollmentsByRoll = Enrollment::with('student')
            ->where('roll_no', $code)
            ->where('academic_year_id', $activeSessionId)
            ->where('enroll_status', 1) // sirf Active enrollment
            ->whereHas('student') // soft-deleted student skip
            ->when($scope !== null, function ($q) use ($scope) {
                $q->where(function ($w) use ($scope) {
                    foreach ($scope as $a) {
                        $w->orWhere(fn($x) => $x->where('class_id', $a->class_id)->where('section_id', $a->section_id));
                    }
                });
            })
            ->get();
        if ($enrollmentsByRoll->count() > 1) { // roll no sirf class+section ke andar unique hai
            return response()->json(['status' => 'error', 'message' => 'Roll no matches more than one student. Use admission no.'], 422);
        }
        $enrollmentByRoll = $enrollmentsByRoll->first();
        if ($enrollmentByRoll && $enrollmentByRoll->student) { // soft-deleted student = null, scan aage "Invalid code" par jaye
            return $this->handleStudentScan($enrollmentByRoll->student, $request->device_id, $scope);
        }

        // STEP 4: Admission No
        $student = Student::where('admission_no', $code)->first();
        if ($student) {
            return $this->handleStudentScan($student, $request->device_id, $scope);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid code. No matching student or staff found.'], 404);
    }

    // ==========================================
    // Time compare karke Present/Late decide karna — dono (student/staff) ke liye common
    // ==========================================
    protected function isLate($scannedAt)
    {
        $timing = SchoolTiming::active();

        if (!$timing) {
            return false; // koi active timing set nahi — default Present
        }

        $graceLimit = Carbon::parse($timing->start_time)->addMinutes($timing->late_grace_minutes);
        $scanTime   = Carbon::parse($scannedAt->format('H:i:s'));

        return $scanTime->gt($graceLimit);
    }

    // ==========================================
    // STUDENT scan handle karna (purana logic yahan move hua, + late-detection add hui)
    // ==========================================
    protected function handleStudentScan(Student $student, $deviceId, $scope = null)
    {
        $activeSessionId = $this->getActiveSessionId();
        $student->load('parent');

        $enrollment = Enrollment::with(['schoolClass', 'section', 'group'])
            ->where('student_id', $student->id)
            ->where('academic_year_id', $activeSessionId)
            ->first();

        // Teacher scope: sirf apni class-section ka student; baqi reject, koi record save nahi (kisi write se pehle)
        if ($scope !== null && (!$enrollment || !$scope->contains(fn($a) => $a->class_id == $enrollment->class_id && $a->section_id == $enrollment->section_id))) {
            return response()->json([
                'status'  => 'error',
                'message' => "{$student->first_name} is not in your class. Attendance not saved.",
            ], 403);
        }

        if (!$enrollment) {
            $data = [
                'status'  => 'error',
                'message' => "{$student->first_name}'s active enrollment not found for this session.",
            ];
            $data['scan_id'] = now()->format('YmdHisv') . '-' . uniqid();
            if ($scope === null) { Cache::put('kiosk_latest_scan', $data, now()->addMinutes(2)); } // teacher scan gate screen par publish nahi hota

            return response()->json($data, 404);
        }

        $studentInfo = [
            'name'          => trim($student->first_name . ' ' . $student->last_name),
            'father_name'   => $student->parent->father_name ?? 'N/A',
            'class_section' => ($enrollment->schoolClass->name ?? '') . ' - ' . ($enrollment->section->name ?? ''),
            'roll_no'       => $enrollment->roll_no,
            'admission_no'  => $student->admission_no,
            'admission_date' => $student->admission_date ? Carbon::parse($student->admission_date)->format('d-m-Y') : null,
            'group'         => optional($enrollment->group)->name,
            'photo'         => $student->photo_url,
            'date'          => now()->format('d-m-Y'),
            'time'          => now()->format('h:i A'),
        ];

        $today = date('Y-m-d');
        $existing = Attendance::with('markedBy.roles')
            ->where('enrollment_id', $enrollment->id)
            ->where('attendance_date', $today)
            ->first();

        // Marked By: naya scan = abhi wala user, warna pehle scan karne wala
        $studentInfo['marked_by'] = $existing ? optional($existing->markedBy)->name_with_role : auth()->user()->name_with_role;

        if (!$existing) {
            $now = now();
            $status = $this->isLate($now) ? Attendance::STATUS_LATE : Attendance::STATUS_PRESENT;

            Attendance::create([
                'enrollment_id'   => $enrollment->id,
                'class_id'        => $enrollment->class_id,
                'section_id'      => $enrollment->section_id,
                'attendance_date' => $today,
                'status'          => $status,
                'marked_via'      => 'device',
                'marked_by'       => auth()->id(),
                'device_id'       => $deviceId,
                'scanned_at'      => $now,
            ]);

            $data = [
                'status'    => 'success',
                'scan_type' => 'checkin',
                'message'   => $status === Attendance::STATUS_LATE
                    ? "Welcome, {$student->first_name}! (Late)"
                    : "Welcome, {$student->first_name}!",
                'is_late'   => $status === Attendance::STATUS_LATE,
                'student'   => $studentInfo,
            ];
            $data['scan_id'] = now()->format('YmdHisv') . '-' . uniqid();
            if ($scope === null) { Cache::put('kiosk_latest_scan', $data, now()->addMinutes(2)); } // teacher scan gate screen par publish nahi hota

            return response()->json($data);
        }

        if (is_null($existing->time_out)) {
            $existing->update(['time_out' => now()->format('H:i:s')]);

            $data = [
                'status'    => 'success',
                'scan_type' => 'checkout',
                'message'   => "Good Bye, {$student->first_name}!",
                'student'   => $studentInfo,
            ];
            $data['scan_id'] = now()->format('YmdHisv') . '-' . uniqid();
            if ($scope === null) { Cache::put('kiosk_latest_scan', $data, now()->addMinutes(2)); } // teacher scan gate screen par publish nahi hota

            return response()->json($data);
        }

        $data = [
            'status'    => 'info',
            'scan_type' => 'already_done',
            'message'   => "{$student->first_name} already checked out at " . Carbon::parse($existing->time_out)->format('h:i A'),
            'student'   => $studentInfo,
        ];
        $data['scan_id'] = now()->format('YmdHisv') . '-' . uniqid();
        if ($scope === null) { Cache::put('kiosk_latest_scan', $data, now()->addMinutes(2)); } // teacher scan gate screen par publish nahi hota

        return response()->json($data);
    }

    // ==========================================
    // STAFF scan handle karna — same 3-case pattern
    // ==========================================
    protected function handleStaffScan(User $staffUser, $deviceId)
    {
        // Joining date teacher/receptionist/accountant profile se (kiosk public screen hai, isliye phone nahi dikhate)
        $staffUser->loadMissing(['teacher', 'receptionist', 'accountant', 'librarian']);
        $profile     = $staffUser->teacher ?: ($staffUser->receptionist ?: ($staffUser->accountant ?: $staffUser->librarian));
        $joiningDate = ($profile && $profile->joining_date) ? Carbon::parse($profile->joining_date)->format('d-m-Y') : null;

        $staffInfo = [
            'type'     => 'staff', // kiosk JS isi se staff card render karta hai
            'name'     => $staffUser->name,
            'role'     => ucfirst((string) optional($staffUser->roles->first())->name),
            'staff_id' => $staffUser->staff_code, // e.g. NAWAL004
            'photo'    => $staffUser->photo_url, // Teachers list wala same source (User::photo_url)
            'joining_date' => $joiningDate,
            'date'  => now()->format('d-m-Y'),
            'time'  => now()->format('h:i A'),
        ];

        $today = date('Y-m-d');
        $existing = StaffAttendance::with('markedBy.roles')
            ->where('user_id', $staffUser->id)
            ->where('date', $today)
            ->first();

        $staffInfo['marked_by'] = $existing ? optional($existing->markedBy)->name_with_role : auth()->user()->name_with_role;

        if (!$existing) {
            $now = now();
            $isLate = $this->isLate($now);
            // Late tracking baad mein: staff_attendances.status enum mein 'late' nahi (strict mode mein insert fail),
            // isliye abhi Present save hota hai. marked_via/device_id/scanned_at columns table mein nahi hain.
            $status = 'present';

            StaffAttendance::create([
                'user_id'    => $staffUser->id,
                'date'       => $today,
                'status'     => $status,
                'marked_by'  => auth()->id(),
                'marked_at'  => $now,
            ]);

            $data = [
                'status'    => 'success',
                'scan_type' => 'checkin',
                'message'   => $isLate
                    ? "Welcome, {$staffUser->name}! (Late)"
                    : "Welcome, {$staffUser->name}!",
                'is_late'   => $isLate,
                'student'   => $staffInfo, // frontend key wahi rakha, taake existing UI bina change ke chale
            ];
            $data['scan_id'] = now()->format('YmdHisv') . '-' . uniqid();
            Cache::put('kiosk_latest_scan', $data, now()->addMinutes(2));

            return response()->json($data);
        }

        if (is_null($existing->time_out)) {
            $existing->update(['time_out' => now()->format('H:i:s')]);

            $data = [
                'status'    => 'success',
                'scan_type' => 'checkout',
                'message'   => "Good Bye, {$staffUser->name}!",
                'student'   => $staffInfo,
            ];
            $data['scan_id'] = now()->format('YmdHisv') . '-' . uniqid();
            Cache::put('kiosk_latest_scan', $data, now()->addMinutes(2));

            return response()->json($data);
        }

        $data = [
            'status'    => 'info',
            'scan_type' => 'already_done',
            'message'   => "{$staffUser->name} already checked out at " . Carbon::parse($existing->time_out)->format('h:i A'),
            'student'   => $staffInfo,
        ];
        $data['scan_id'] = now()->format('YmdHisv') . '-' . uniqid();
        Cache::put('kiosk_latest_scan', $data, now()->addMinutes(2));

        return response()->json($data);
    }
    // End Method

    //  TEACHER K LIYE VIEW PAGE METHOD
    public function myClassAttendanceReport(Request $request)
    {
        $user = auth()->user();
        $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return redirect()->back()->with('error', 'Teacher profile not found.');
        }

        // Teacher ko assigned classes ki ID nikalna
        $assignedClassIds = TeacherAssignment::where('teacher_id', $teacher->id)
            ->pluck('class_id');

        // Filter dates
        $startDate = $request->input('start_date', now()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Sirf unki assigned classes ka data fetch karna
        $attendanceRecords = Attendance::with(['enrollment.student', 'enrollment.schoolClass', 'enrollment.section', 'markedBy.roles'])
            ->whereIn('class_id', $assignedClassIds)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date', 'DESC')
            ->get()
            ->groupBy('attendance_date');

        return view('teacher.my_class_attendance', compact('attendanceRecords', 'startDate', 'endDate'));
    }
    // End Method


}
