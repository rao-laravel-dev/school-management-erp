<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    // Roles the admin page can edit (admin + superadmin roles are never reachable from it)
    const ADMIN_TARGET_ROLES = ['receptionist', 'accountant', 'librarian', 'teacher', 'parent', 'student'];

    // Hidden on both pages; the server always keeps them untouched
    const PROTECTED_PERMISSIONS = ['view dashboard', 'access-notifications'];

    // Admin can never grant these (role/permission management)
    const MANAGEMENT_PERMISSIONS = ['view roles', 'add roles', 'edit roles', 'delete roles'];

    // Portal-only permissions: student/parent roles only (T196), never shown for admin or staff roles
    const PORTAL_ONLY_PERMISSIONS = [
        'access-student-academics', 'access-student-attendance', 'access-student-timetable',
        'access-student-homework', 'access-student-fees', 'access-assignments',
        'access-parent-timetable', 'access-parent-homework', 'access-parent-results',
        'access-parent-students', 'access-parent-fees',
    ];

    // Self-service permissions the admin role itself does not hold, but may grant to the matching role
    const STAFF_PORTAL_PERMISSIONS = [
        'access-my-salary-slips', 'access-my-attendance', 'access-profile', 'view school-notices',
    ];
    const TEACHER_PORTAL_PERMISSIONS = [
        'access-my-timetable', 'access-my-lesson-plan', 'access-my-syllabus-status', 'access-my-exam-schedule',
    ];
    const STUDENT_PORTAL_PERMISSIONS = [
        'access-student-academics', 'access-student-attendance', 'access-student-timetable',
        'access-student-homework', 'access-student-fees', 'access-assignments', 'access-profile',
    ];
    const PARENT_PORTAL_PERMISSIONS = [
        'access-parent-timetable', 'access-parent-homework', 'access-parent-results',
        'access-parent-students', 'access-parent-fees', 'access-profile',
    ];

    // Row order inside a module
    const VERB_ORDER = ['view', 'access', 'add', 'edit', 'delete', 'manage', 'mark', 'admit'];

public function AddRole()
{
    $permissions = Permission::all()->groupBy(function ($perm) {
        return explode('.', $perm->name)[0] ?? $perm->name;
    });

    return view('superadmin.roles.create_role', compact('permissions'));
}

// Role permission matrix. {id} = role NAME (admin / receptionist ...), never a numeric id.
// superadmin.* route -> only the admin role; admin.* route -> ADMIN_TARGET_ROLES only.
public function EditRole(Request $request, $id = null)
{
    $isSuperPage = $request->routeIs('superadmin.*');

    if ($id === null) {
        $id = self::ADMIN_TARGET_ROLES[0];
    }
    $role = $this->resolveTargetRole($id, $isSuperPage);

    $visible = $this->visiblePermissions($role, $isSuperPage);
    $held = $role->permissions->pluck('name')->all();
    $groups = $this->groupPermissions($visible, $held);

    return view('superadmin.roles.edit_role', [
        'role'        => $role->name,
        'groups'      => $groups,
        'isSuperPage' => $isSuperPage,
        'roleOptions' => $isSuperPage ? [] : self::ADMIN_TARGET_ROLES,
        'editUrl'     => route($isSuperPage ? 'superadmin.roles.edit' : 'admin.roles.edit', ':id'),
        'updateUrl'   => route($isSuperPage ? 'superadmin.roles.update' : 'admin.roles.update', ':id'),
    ]);
}

public function UpdateRole(Request $request, $id)
{
    $isSuperPage = $request->routeIs('superadmin.*');
    $role = $this->resolveTargetRole($id, $isSuperPage);

    $request->validate([
        'permissions'     => 'nullable|array',
        'permissions.*'   => 'string|distinct|exists:permissions,name,guard_name,web',
    ]);

    $posted = $request->input('permissions', []);
    $visible = $this->visiblePermissions($role, $isSuperPage);

    // Server side ceiling: anything outside the visible (grantable) set is rejected
    $outside = array_values(array_diff($posted, $visible));
    if (count($outside) > 0) {
        return response()->json([
            'success' => false,
            'message' => 'Not allowed to grant: ' . implode(', ', $outside),
        ], 422);
    }

    DB::transaction(function () use ($role, $posted, $visible, $isSuperPage) {
        $old = $role->permissions->pluck('name')->all();

        // Hidden / protected / not grantable permissions stay exactly as they are
        $kept = array_values(array_diff($old, $visible));
        $role->syncPermissions(array_values(array_unique(array_merge($kept, $posted))));

        // Cascade (superadmin -> admin only): a revoked permission is also revoked from the child roles. Grants never cascade.
        if ($isSuperPage && $role->name === 'admin') {
            $revoked = array_values(array_diff(array_intersect($old, $visible), $posted));
            if (count($revoked) > 0) {
                Role::whereIn('name', self::ADMIN_TARGET_ROLES)->where('guard_name', 'web')->get()
                    ->each(function ($child) use ($revoked) {
                        $child->revokePermissionTo($revoked);
                    });
            }
        }
    });

    return response()->json(['success' => true, 'message' => 'Permissions updated successfully.']);
}

// Only the allowed role for the page is resolvable; superadmin role is never reachable
private function resolveTargetRole($name, bool $isSuperPage): Role
{
    $allowed = $isSuperPage ? ['admin'] : self::ADMIN_TARGET_ROLES;
    abort_unless(in_array($name, $allowed, true), 403);

    return Role::where('name', $name)->where('guard_name', 'web')->firstOrFail();
}

// Permission names the page may show / grant for this role (protected ones are never included)
private function visiblePermissions(Role $role, bool $isSuperPage): array
{
    $all = Permission::where('guard_name', 'web')->pluck('name')->all();

    if ($isSuperPage) {
        // Staff/teacher self-service permissions are also hidden here (admin page offers them to the matching roles)
        $names = array_diff($all, self::PORTAL_ONLY_PERMISSIONS, self::STAFF_PORTAL_PERMISSIONS, self::TEACHER_PORTAL_PERMISSIONS);
    } else {
        $portal = $this->portalSet($role->name);

        if (in_array($role->name, ['parent', 'student'], true)) {
            // Portal roles: portal set only
            $names = $portal;
        } else {
            // Ceiling = permissions the admin role itself holds, plus the self-service set of this role
            $adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->firstOrFail();
            $own = $adminRole->permissions->pluck('name')->all();
            $own = array_diff($own, self::MANAGEMENT_PERMISSIONS, self::PORTAL_ONLY_PERMISSIONS);
            $names = array_merge($own, $portal);
        }
    }

    // Management permissions are already excluded on the admin page above; superadmin may toggle them for admin
    $names = array_diff($names, self::PROTECTED_PERMISSIONS);

    // Only names that really exist in the permissions table; stray legacy "can:*" rows are never shown or granted (left untouched in the DB)
    $names = array_values(array_intersect($all, $names));

    return array_values(array_filter($names, function ($name) {
        return strpos($name, 'can:') !== 0;
    }));
}

private function portalSet(string $roleName): array
{
    if ($roleName === 'student') {
        return self::STUDENT_PORTAL_PERMISSIONS;
    }
    if ($roleName === 'parent') {
        return self::PARENT_PORTAL_PERMISSIONS;
    }
    if ($roleName === 'teacher') {
        return array_merge(self::STAFF_PORTAL_PERMISSIONS, self::TEACHER_PORTAL_PERMISSIONS);
    }

    return self::STAFF_PORTAL_PERMISSIONS;
}

// Group by module from the permission name prefix ("access-class-timetable" -> module "class-timetable")
private function groupPermissions(array $names, array $held): array
{
    $modules = [];

    foreach ($names as $name) {
        if (preg_match('/^(access|manage|mark|admit|view|add|edit|delete)[- ](.+)$/', $name, $m)) {
            $verb = $m[1];
            $module = $m[2];
        } else {
            $verb = 'access';
            $module = $name;
        }

        $modules[$module]['key'] = $module;
        $modules[$module]['label'] = \Illuminate\Support\Str::headline($module);
        $modules[$module]['perms'][] = [
            'name'    => $name,
            'verb'    => ucfirst($verb),
            'order'   => array_search($verb, self::VERB_ORDER, true),
            'checked' => in_array($name, $held, true),
        ];
    }

    foreach ($modules as $key => $module) {
        usort($modules[$key]['perms'], function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });
    }

    uasort($modules, function ($a, $b) {
        return strcmp($a['label'], $b['label']);
    });

    return array_values($modules);
}
}
