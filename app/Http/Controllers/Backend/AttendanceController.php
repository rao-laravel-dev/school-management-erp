<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\QrCode;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StaffAttendance;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return view('admin.attendance.index_attendance', compact('totalClasses', 'attendanceDone', 'classes', 'date'));
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

        if ($user->hasRole('admin')) {
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
        if (!$user->hasRole('admin')) {
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
        $isAdmin = $user->hasRole('admin');

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
        if (!$user->hasRole('admin')) {
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
    public function viewMyAttendance()
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

        // Attendance fetch (Session + Month/Year wise)
        $allAttendance = Attendance::where('enrollment_id', $enrollment->id)
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
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

        if ($user->hasRole('student')) {
            return view('student.attendance.view_myattendance', compact('student', 'matrix', 'allAttendance', 'stats'));
        } else {
            return view('admin.attendance.student_view_attendance', compact('student', 'enrollment', 'matrix', 'allAttendance', 'stats'));
        }
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

    public function ScanAttendance()
    {
        return view('admin.attendance.scan_attendance');
    }
    // End Method

    public function StoreScanAttendance(Request $request)
    {
        $request->validate([
            'code'      => 'required|string',
            'device_id' => 'nullable|string',
        ]);

        $code = trim($request->code);

        // STEP 1: QR/Barcode code se owner dhoondo — Student ya Staff dono isi table se aate hain
        $qrRecord = QrCode::where('code', $code)->first();

        if ($qrRecord && $qrRecord->owner) {
            if ($qrRecord->owner_type === Student::class) {
                return $this->handleStudentScan($qrRecord->owner, $request->device_id);
            }
            if ($qrRecord->owner_type === User::class) {
                return $this->handleStaffScan($qrRecord->owner, $request->device_id);
            }
        }

        // STEP 2: Staff ka Employee/Teacher ID (jaisa TEACH001) — manual type ke liye
        $teacher = Teacher::where('teacher_id', $code)->first();
        if ($teacher && $teacher->user) {
            return $this->handleStaffScan($teacher->user, $request->device_id);
        }

        // STEP 3: Roll No (current session)
        $activeSessionId = $this->getActiveSessionId();
        $enrollmentByRoll = Enrollment::where('roll_no', $code)
            ->where('academic_year_id', $activeSessionId)
            ->first();
        if ($enrollmentByRoll) {
            return $this->handleStudentScan($enrollmentByRoll->student, $request->device_id);
        }

        // STEP 4: Admission No
        $student = Student::where('admission_no', $code)->first();
        if ($student) {
            return $this->handleStudentScan($student, $request->device_id);
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
    protected function handleStudentScan(Student $student, $deviceId)
    {
        $activeSessionId = $this->getActiveSessionId();
        $student->load('parent');

        $enrollment = Enrollment::with(['schoolClass', 'section'])
            ->where('student_id', $student->id)
            ->where('academic_year_id', $activeSessionId)
            ->first();

        if (!$enrollment) {
            return response()->json(['status' => 'error', 'message' => "{$student->first_name}'s active enrollment not found for this session."], 404);
        }

        $studentInfo = [
            'name'          => trim($student->first_name . ' ' . $student->last_name),
            'father_name'   => $student->parent->father_name ?? 'N/A',
            'class_section' => ($enrollment->schoolClass->name ?? '') . ' - ' . ($enrollment->section->name ?? ''),
            'roll_no'       => $enrollment->roll_no,
            'photo'         => $student->photo ? asset('uploads/students/' . $student->photo) : asset('images/no-image.png'),
            'date'          => now()->format('d-m-Y'),
            'time'          => now()->format('h:i A'),
        ];

        $today = date('Y-m-d');
        $existing = Attendance::where('enrollment_id', $enrollment->id)
            ->where('attendance_date', $today)
            ->first();

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

            return response()->json([
                'status'    => 'success',
                'scan_type' => 'checkin',
                'message'   => $status === Attendance::STATUS_LATE
                    ? "Welcome, {$student->first_name}! (Marked Late)"
                    : "Welcome, {$student->first_name}!",
                'student'   => $studentInfo,
            ]);
        }

        if (is_null($existing->time_out)) {
            $existing->update(['time_out' => now()->format('H:i:s')]);

            return response()->json([
                'status'    => 'success',
                'scan_type' => 'checkout',
                'message'   => "Good Bye, {$student->first_name}!",
                'student'   => $studentInfo,
            ]);
        }

        return response()->json([
            'status'    => 'info',
            'scan_type' => 'already_done',
            'message'   => "{$student->first_name} already checked out at " . Carbon::parse($existing->time_out)->format('h:i A'),
            'student'   => $studentInfo,
        ]);
    }

    // ==========================================
    // STAFF scan handle karna — same 3-case pattern
    // ==========================================
    protected function handleStaffScan(User $staffUser, $deviceId)
    {
        $staffInfo = [
            'name'  => $staffUser->name,
            'role'  => optional($staffUser->roles->first())->name,
            'photo' => asset('images/no-image.png'), // agar staff photo field hai to yahan replace kar dena
            'date'  => now()->format('d-m-Y'),
            'time'  => now()->format('h:i A'),
        ];

        $today = date('Y-m-d');
        $existing = StaffAttendance::where('user_id', $staffUser->id)
            ->where('date', $today)
            ->first();

        if (!$existing) {
            $now = now();
            $status = $this->isLate($now) ? 'late' : 'present';

            StaffAttendance::create([
                'user_id'    => $staffUser->id,
                'date'       => $today,
                'status'     => $status,
                'marked_via' => 'device',
                'marked_by'  => auth()->id(),
                'device_id'  => $deviceId,
                'scanned_at' => $now,
                'marked_at'  => $now,
            ]);

            return response()->json([
                'status'    => 'success',
                'scan_type' => 'checkin',
                'message'   => $status === 'late'
                    ? "Welcome, {$staffUser->name}! (Marked Late)"
                    : "Welcome, {$staffUser->name}!",
                'student'   => $staffInfo, // frontend key wahi rakha, taake existing UI bina change ke chale
            ]);
        }

        if (is_null($existing->time_out)) {
            $existing->update(['time_out' => now()->format('H:i:s')]);

            return response()->json([
                'status'    => 'success',
                'scan_type' => 'checkout',
                'message'   => "Good Bye, {$staffUser->name}!",
                'student'   => $staffInfo,
            ]);
        }

        return response()->json([
            'status'    => 'info',
            'scan_type' => 'already_done',
            'message'   => "{$staffUser->name} already checked out at " . Carbon::parse($existing->time_out)->format('h:i A'),
            'student'   => $staffInfo,
        ]);
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
        $attendanceRecords = Attendance::with(['enrollment.student', 'enrollment.schoolClass', 'enrollment.section'])
            ->whereIn('class_id', $assignedClassIds)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date', 'DESC')
            ->get()
            ->groupBy('attendance_date');

        return view('teacher.my_class_attendance', compact('attendanceRecords', 'startDate', 'endDate'));
    }
    // End Method


}
