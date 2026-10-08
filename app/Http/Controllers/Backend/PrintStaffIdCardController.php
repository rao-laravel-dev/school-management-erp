<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
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
        $templates = StaffIdCardTemplate::where('status', 1)->get();

        return view('admin.staff_id_card_template.print', [
            'roles' => $roles,
            'templates' => $templates,
        ]);
    }

    public function getStaffByRole($role)
    {
        $staffMembers = User::role($role)
            ->where('status', 1)
            ->with(['teacher', 'accountant', 'receptionist', 'roles']) // roles: staff_code accessor N+1 se bachne ke liye
            ->get();

        $staff =$staffMembers->map(function ($user) {
            $photo  = $user->teacher->photo ?? $user->accountant->photo ?? $user->receptionist->photo ?? null;
            $folder = $user->teacher ? 'teacher_images' : ($user->accountant ? 'accountants' : ($user->receptionist ? 'receptionists' : null));

            return [
                'id'       => $user->id,
                'staff_id' => $user->staff_code,
                'name'     => strtoupper($user->name),
                'photo'    => $user->teacher ? $user->teacher->photo_url : ($photo && $folder ? asset('uploads/' . $folder . '/' . $photo) : asset('images/no-image.png')),
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

        $staffMembers = User::whereIn('id', $request->staff_ids)
            ->with(['teacher', 'accountant', 'receptionist', 'roles'])
            ->get();

        return view('admin.staff_id_card_template.card', compact('template', 'staffMembers'));
    }
}