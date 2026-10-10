<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\LibraryMember;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class LibraryMemberController extends Controller
{
    // ---------- STUDENT MEMBERS PAGE ----------
    public function studentPage()
    {
        $classes = SchoolClass::all();
        return view('admin.library_settings.members.student', compact('classes'));
    }

    public function searchStudents(Request $request)
    {
        $request->validate(['class_id' => 'required']);

        $currentYear = AcademicYear::where('is_current', 1)->first();

        if (!$currentYear) {
            return response()->json(['message' => 'No current academic year is set.'], 422);
        }

        $students = Enrollment::with(['student.parent', 'section', 'schoolClass'])
            ->where('academic_year_id', $currentYear->id)
            ->where('class_id', $request->class_id)
            ->where('enroll_status', 1) // sirf Active enrollments
            ->when($request->section_id, fn($q) => $q->where('section_id', $request->section_id))
            ->whereHas('student') // soft-deleted student wali enrollment skip (warna null crash)
            ->get();

        $memberIds = LibraryMember::where('member_type', 'student')
            ->pluck('library_card_no', 'member_id'); // [student_id => card_no]

        $data = $students->map(function ($enrollment) use ($memberIds) {
            $student = $enrollment->student;

            return [
                'id' => $student->id,
                'admission_no' => $student->admission_no,
                'name' => $student->full_name,
                'roll_no' => $enrollment->roll_no,
                'class_section' => ($enrollment->schoolClass->name ?? '-') . ' (' . ($enrollment->section->name ?? '-') . ')',
                'father_name' => $student->parent->father_name ?? '-',
                'dob' => $student->date_of_birth,
                'gender' => $student->gender,
                'mobile' => $student->parent->guardian_phone ?? $student->phone ?? '-',
                'is_member' => $memberIds->has($student->id),
                'library_card_no' => $memberIds->get($student->id),
            ];
        });

        return response()->json(['students' => $data]);
    }

    public function addStudent($id)
    {
        if (!Student::whereKey($id)->exists()) { // soft-deleted / invalid id par member nahi banta
            return response()->json(['message' => 'Student not found.'], 422);
        }

        $exists = LibraryMember::where('member_type', 'student')->where('member_id', $id)->exists();
        if ($exists) {
            return response()->json(['message' => 'Already a library member.'], 422);
        }

        $cardNo = $this->generateCardNo('student');

        $member = LibraryMember::create([
            'member_type' => 'student',
            'member_id' => $id,
            'library_card_no' => $cardNo,
            'added_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Student added as library member.',
            'library_card_no' => $cardNo,
        ]);
    }

    public function removeStudent($id)
    {
        if ($guard = $this->removeGuard('student', $id)) {
            return $guard;
        }

        LibraryMember::where('member_type', 'student')->where('member_id', $id)->delete();
        return response()->json(['message' => 'Student removed from library membership.']);
    }

    // ---------- STAFF MEMBERS PAGE ----------
    public function staffPage()
    {
        $roles = Role::whereNotIn('id', [1, 2, 7, 8, 9])->get();
        return view('admin.library_settings.members.staff', compact('roles'));
    }

    private const STAFF_MODEL_MAP = [
        'receptionist' => \App\Models\Receptionist::class,
        'accountant'   => \App\Models\Accountant::class,
        //'librarian'    => \App\Models\Librarian::class,
        'teacher'      => \App\Models\Teacher::class,
    ];

    public function searchStaff(Request $request)
    {
        $request->validate(['role_id' => 'required']);

        $role = Role::findOrFail($request->role_id);

        $modelClass = self::STAFF_MODEL_MAP[$role->name] ?? null;

        if (!$modelClass) {
            return response()->json(['message' => 'No matching staff table for this role.'], 422);
        }

        $rows = $modelClass::with('user')->get();

        $memberIds = LibraryMember::where('member_type', 'staff')
            ->pluck('library_card_no', 'member_id'); // [user_id => card_no]

        $data = $rows->map(function ($row) use ($memberIds) {
            return [
                'user_id' => $row->user_id,
                'name' => $row->user->name ?? '-',
                'email' => $row->user->email ?? '-',
                'phone' => $row->phone ?? '-',
                'is_member' => $memberIds->has($row->user_id),
                'library_card_no' => $memberIds->get($row->user_id),
            ];
        });

        return response()->json(['staff' => $data]);
    }

    public function addStaff($userId)
    {
        $exists = LibraryMember::where('member_type', 'staff')->where('member_id', $userId)->exists();
        if ($exists) {
            return response()->json(['message' => 'Already a library member.'], 422);
        }

        $cardNo = $this->generateCardNo('staff');

        LibraryMember::create([
            'member_type' => 'staff',
            'member_id' => $userId,
            'library_card_no' => $cardNo,
            'added_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Staff added as library member.',
            'library_card_no' => $cardNo,
        ]);
    }

    public function removeStaff($userId)
    {
        if ($guard = $this->removeGuard('staff', $userId)) {
            return $guard;
        }

        LibraryMember::where('member_type', 'staff')->where('member_id', $userId)->delete();
        return response()->json(['message' => 'Staff removed from library membership.']);
    }

    // ---------- SHARED: REMOVE GUARD (book_issues cascadeOnDelete, history delete na ho) ----------
    private function removeGuard(string $type, $memberId)
    {
        $member = LibraryMember::where('member_type', $type)->where('member_id', $memberId)->first();
        if (!$member) {
            return null;
        }

        if ($member->bookIssues()->whereIn('status', ['issued', 'overdue', 'lost'])->exists()) {
            return response()->json(['message' => 'This member has issued books. Return them first.'], 422);
        }

        if ($member->bookIssues()->exists()) {
            return response()->json(['message' => 'This member has library history, it cannot be removed (history is kept).'], 422);
        }

        return null;
    }

    // ---------- SHARED: CARD NUMBER GENERATOR ----------
    private function generateCardNo(string $type): string
    {
        $prefix = $type === 'student' ? 'LIB-S-' : 'LIB-T-';

        $last = LibraryMember::where('member_type', $type)
            ->where('library_card_no', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(library_card_no, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
            ->value('library_card_no');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
