<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccessControlActionRequest;
use App\Models\AccessLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

// Superadmin-only: admin user Active/Inactive (last active admin = school lock) + audit log
class AccessControlController extends Controller
{
    public function index()
    {
        // School lock status: koi admin user active nahi = locked
        $schoolLocked = User::isSchoolLocked();
        $activeAdminCount = User::activeAdminCount();
        $adminCount = User::withoutSuperadmin()->role('admin')->count();

        return view('admin.access_control.index', compact('schoolLocked', 'activeAdminCount', 'adminCount'));
    }

    // Admin Accounts: sirf admin-role users (superadmin hamesha hidden)
    public function users(Request $request): JsonResponse
    {
        $users = User::withoutSuperadmin()
            ->role('admin')
            ->with('roles:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'username', 'status']);

        // Last active admin ko inactive karna = school lock (JS strong warning + LOCK)
        $activeCount = $users->where('status', 1)->count();

        $rows = $users->map(function ($user) use ($activeCount) {
            $status = (int) $user->status;

            if ($status === 1) {
                $badge = '<span class="badge bg-success">Active</span>';
                $btn = '<button type="button" class="btn btn-sm btn-outline-danger set-user-status" data-id="' . $user->id . '" data-action="deactivate" data-last="' . ($activeCount === 1 ? 1 : 0) . '">Set Inactive</button>';
            } elseif ($status === 2) {
                $badge = '<span class="badge bg-warning text-dark">Pending</span>';
                $btn = '<button type="button" class="btn btn-sm btn-outline-success set-user-status" data-id="' . $user->id . '" data-action="activate">Set Active</button>';
            } else {
                $badge = '<span class="badge bg-danger">Inactive</span>';
                $btn = '<button type="button" class="btn btn-sm btn-outline-success set-user-status" data-id="' . $user->id . '" data-action="activate">Set Active</button>';
            }

            return [
                e($user->name),
                e($user->email),
                e($user->username ?: '-'),
                e($user->roles->pluck('name')->implode(', ')),
                $badge,
                $btn,
            ];
        });

        return response()->json(['data' => $rows]);
    }

    // Audit Log tab: latest 200
    public function logs(): JsonResponse
    {
        $rows = AccessLog::with('actor:id,name')
            ->latest('id')
            ->limit(200)
            ->get()
            ->map(function ($log) {
                return [
                    e($log->created_at->format('d-m-Y H:i')),
                    e($log->actor ? $log->actor->name : '-'),
                    e(ucfirst($log->target_type)),
                    e($log->target_name ?: ('#' . $log->target_id)),
                    e(ucfirst(str_replace('_', ' ', $log->action))),
                    e($log->ip ?: '-'),
                ];
            });

        return response()->json(['data' => $rows]);
    }

    // User: explicit Set Active (inactive + pending approve) / Set Inactive
    public function setUserStatus(AccessControlActionRequest $request, $id): JsonResponse
    {
        $action = $request->validated()['action'];

        if (! in_array($action, ['activate', 'deactivate'], true)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid action for a user.'], 422);
        }

        // withoutSuperadmin: superadmin id par 404; role('admin'): non-admin user id par bhi 404
        $user = User::withoutSuperadmin()->role('admin')->findOrFail($id);

        if ((int) $user->id === (int) auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'You cannot change your own status.'], 422);
        }

        $newStatus = $action === 'activate' ? 1 : 0;
        $oldStatus = (int) $user->status;

        if ($oldStatus === $newStatus) {
            return response()->json(['status' => 'error', 'message' => 'User is already ' . ($newStatus === 1 ? 'active' : 'inactive') . '.'], 422);
        }

        // Last active admin inactive = school lock; activate jab school locked tha = unlock
        $willLock = $newStatus === 0 && $oldStatus === 1 && User::activeAdminCount() === 1;
        $willUnlock = $newStatus === 1 && User::isSchoolLocked();

        if ($willLock && $request->input('confirm') !== 'LOCK') {
            return response()->json(['status' => 'error', 'message' => 'Type LOCK to lock the school.'], 422);
        }

        DB::transaction(function () use ($user, $newStatus, $oldStatus, $action, $request, $willLock, $willUnlock) {
            $user->status = $newStatus;
            $user->save();

            // Inactive user ke API tokens bhi band
            if ($newStatus === 0) {
                $user->tokens()->delete();
            }

            AccessLog::create([
                'actor_id'    => auth()->id(),
                'target_type' => 'user',
                'target_id'   => $user->id,
                'target_name' => $user->name,
                'action'      => $action,
                'old_value'   => (string) $oldStatus,
                'new_value'   => (string) $newStatus,
                'ip'          => $request->ip(),
            ]);

            // School lock: saare non-superadmin users ke Sanctum tokens revoke (web sessions EnsureUserIsActive se band)
            if ($willLock) {
                $superadminIds = User::withTrashed()->role('superadmin')->pluck('users.id');

                DB::table('personal_access_tokens')
                    ->where('tokenable_type', User::class)
                    ->whereNotIn('tokenable_id', $superadminIds)
                    ->delete();
            }

            // Ek extra log row: school_locked / school_unlocked
            if ($willLock || $willUnlock) {
                AccessLog::create([
                    'actor_id'    => auth()->id(),
                    'target_type' => 'school',
                    'target_id'   => 0,
                    'target_name' => 'School',
                    'action'      => $willLock ? 'school_locked' : 'school_unlocked',
                    'old_value'   => $willLock ? '1' : '0',
                    'new_value'   => $willLock ? '0' : '1',
                    'ip'          => $request->ip(),
                ]);
            }
        });

        if ($willLock) {
            $message = 'School locked. All users are logged out.';
        } elseif ($willUnlock) {
            $message = $user->name . ' is now active. School unlocked.';
        } else {
            $message = $user->name . ($newStatus === 1 ? ' is now active.' : ' is now inactive.');
        }

        return response()->json([
            'status'         => 'success',
            'message'        => $message,
            'locked_changed' => $willLock || $willUnlock,
        ]);
    }
}
