<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{

public function AddRole()
{
    $permissions = Permission::all()->groupBy(function ($perm) {
        return explode('.', $perm->name)[0] ?? $perm->name;
    });

    return view('superadmin.roles.create_role', compact('permissions'));
}
}
