<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\StaffIdCardTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class PrintStaffIdCardController extends Controller
{
    private $excludedRoles = ['superadmin', 'admin', 'parent', 'student', 'user'];

    public function index()
    {
        $roles = Role::whereNotIn('name', $this->excludedRoles)->get();
        $templates = StaffIdCardTemplate::where('status', 1)->get()
            ->filter(fn ($t) => auth()->user()->can('access-id-card-design-' . $t->design)) // only designs this user may print
            ->values();

        return view('admin.staff_id_card_template.print', [
            'roles' => $roles,
            'templates' => $templates,
        ]);
    }

    public function getStaffByRole($role)
    {
        // superadmin/admin/parent/student/user ke ID card nahi bante
        if (in_array($role, $this->excludedRoles, true)) {
            return response()->json([]);
        }

        $staffMembers = User::role($role)
            ->where('status', 1)
            ->with(['teacher', 'accountant', 'receptionist', 'librarian', 'teacherWithTrashed', 'accountantWithTrashed', 'receptionistWithTrashed', 'librarianWithTrashed', 'roles']) // roles: staff_code accessor N+1 se bachne ke liye
            // old: (no orderBy, oldest first)
            ->orderByDesc('users.id') // newest registered first, same as student list
            ->get()
            ->reject(fn ($user) => $user->isStaffTrashed()) // no card for a trashed staff profile
            ->values(); // keep a JSON array for print.blade.php (data.length / forEach)

        $staff =$staffMembers->map(function ($user) {
            return [
                'id'       => $user->id,
                'staff_id' => $user->staff_code,
                'name'     => strtoupper($user->name),
                'photo'    => $user->photo_url, // User accessor: sab staff roles (teacher/accountant/receptionist/librarian)
            ];
        });

        return response()->json($staff);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'template_id'  => 'required|exists:staff_id_card_templates,id',
            'staff_ids'    => 'required|array|min:1',
            'staff_ids.*'  => 'exists:users,id',
        ]);

        $template = StaffIdCardTemplate::findOrFail($request->template_id);
        abort_unless($request->user()->can('access-id-card-design-' . $template->design), 403); // stale / tampered POST guard

        $staffMembers = User::whereIn('id', $request->staff_ids)
            ->withoutSuperadmin()
            ->with(['teacher', 'accountant', 'receptionist', 'librarian', 'teacherWithTrashed', 'accountantWithTrashed', 'receptionistWithTrashed', 'librarianWithTrashed', 'roles'])
            // old: (no orderBy, oldest first)
            ->orderByDesc('users.id') // printed cards follow the list order (newest first)
            ->get();

        // Card "Valid" = active session ki dates; null = view purana now() wala value dikhaye
        $activeYear = AcademicYear::where('is_current', 1)->first(['start_date', 'end_date']);
        $validSession = null;
        if ($activeYear && $activeYear->start_date && $activeYear->end_date) {
            $validSession = $activeYear->start_date->format('Y') === $activeYear->end_date->format('Y')
                ? $activeYear->start_date->format('Y')
                : $activeYear->start_date->format('Y') . '-' . $activeYear->end_date->format('y');
        }

        return view('admin.staff_id_card_template.card', compact('template', 'staffMembers', 'validSession'));
    }
}