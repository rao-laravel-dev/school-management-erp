<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Accountant;
use App\Models\Enrollment;
use App\Models\Librarian;
use App\Models\Receptionist;
use App\Models\Teacher;
use Illuminate\Http\Request;

class PeopleSearchController extends Controller
{
    // Header search: students + staff by name / ID. Display fields only (no phone, CNIC, address, email)
    public function search(Request $request)
    {
        $empty = ['students' => [], 'staff' => []];
        $q = mb_substr(trim((string) $request->query('q', '')), 0, 50);
        if (mb_strlen($q) < 2) {
            return response()->json($empty);
        }

        // Teacher / student / parent: no people search
        $user = $request->user();
        if ($user->enabledRoleNames()->intersect(['superadmin', 'admin', 'receptionist', 'accountant', 'librarian'])->isEmpty()) {
            return response()->json($empty);
        }

        $like = '%' . addcslashes($q, '\\%_') . '%';

        $students = [];
        if ($user->can('access-students')) {
            // same base query as StudentController::FilterStudents so every hit is visible on the list page
            $sessionId = AcademicYear::getActiveSessionId();
            $students = Enrollment::with([
                    'student:id,first_name,last_name,admission_no,photo',
                    'schoolClass', 'section',
                ])
                ->whereHas('student')
                ->when($sessionId, fn($e) => $e->where(fn($w) => AcademicYear::whereCurrentOrGap($w)))
                ->where(function ($e) use ($like) {
                    $e->whereHas('student', function ($s) use ($like) {
                        $s->where('first_name', 'like', $like)
                            ->orWhere('last_name', 'like', $like)
                            ->orWhereRaw("CONCAT(first_name, ' ', COALESCE(last_name, '')) like ?", [$like])
                            ->orWhere('admission_no', 'like', $like);
                    })->orWhere('roll_no', 'like', $like);
                })
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn($e) => [
                    'name'  => trim($e->student->first_name . ' ' . $e->student->last_name),
                    'code'  => $e->student->admission_no,
                    'meta'  => trim(($e->schoolClass->name ?? '') . ' - ' . ($e->section->name ?? '') . ' · Roll ' . $e->roll_no, ' -·') . ' · Student',
                    'photo' => $e->student->photo_url,
                    'url'   => route('students.index', ['keyword' => $e->student->admission_no]),
                ])->values()->all();
        }

        // [permission, model, id column, label, list route]
        $types = [
            ['access-teacher', Teacher::class, 'teacher_id', 'Teacher', 'teacher.index'],
            ['access-receptionist', Receptionist::class, 'receptionist_id', 'Receptionist', 'receptionist.index'],
            ['access-accountant', Accountant::class, 'accountant_id', 'Accountant', 'accountant.index'],
            ['access-librarian', Librarian::class, 'librarian_id', 'Librarian', 'librarians.index'],
        ];

        $staff = collect();
        foreach ($types as [$perm, $model, $idCol, $label, $route]) {
            if (!$user->can($perm)) {
                continue;
            }
            $model::select('id', 'first_name', 'last_name', $idCol, 'photo')
                ->where(function ($w) use ($like, $idCol) {
                    $w->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", [$like])
                        ->orWhere($idCol, 'like', $like);
                })
                ->orderBy('first_name')
                ->limit(10)
                ->get()
                ->each(fn($p) => $staff->push([
                    'name'  => trim($p->first_name . ' ' . $p->last_name),
                    'code'  => $p->{$idCol},
                    'meta'  => $label,
                    'photo' => $p->photo_url,
                    'url'   => route($route, ['q' => $p->{$idCol}]),
                ]));
        }

        return response()->json([
            'students' => $students,
            'staff'    => $staff->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->take(10)->values()->all(),
        ]);
    }
}
