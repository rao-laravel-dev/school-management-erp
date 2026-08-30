<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\StaffLeaveSetting;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;


class StaffLeaveSettingController extends Controller
{
 public function index()
{
    // StaffLeaveSetting se data uthayein aur role_id par group karein
    $leaveQuotas = \App\Models\StaffLeaveSetting::with('role') // Make sure Relation exists in StaffLeaveSetting model
        ->get()
        ->groupBy('role_id');

    return view('admin.staffleavesett.index', compact('leaveQuotas'));
}               

public function create() {
    // Sirf woh roles uthao jo aapko chahiye
    $roles = Role::whereNotIn('name', ['superadmin', 'admin', 'parent', 'student', 'user'])->get();

    return view('admin.staffleavesett.create', compact('roles'));
}

   public function store(Request $request) 
{
    try {
        // Validation
        $validator = Validator::make($request->all(), [
            'role_id'    => 'required|exists:roles,id',
            'leave_type' => 'required|string',
            'total_days' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed!']);
        }

        // 1. Role ka naam hasil karein
        $role = Role::find($request->role_id);
        
        if (!$role) {
            return response()->json(['status' => 'error', 'message' => 'Role not found!']);
        }

        // 2. Role ke naam se users find karein (Spatie standard)
        $usersInRole = User::role($role->name)->get();

        if ($usersInRole->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Is role mein koi staff nahi hai!']);
        }

        // 3. Har user ke liye record create/update karein
        foreach ($usersInRole as $user) {
            StaffLeaveSetting::updateOrCreate(
                [
                    'user_id'    => $user->id, 
                    'leave_type' => $request->leave_type
                ],
                [
                    'total_days' => $request->total_days,
                    'role_id'    => $request->role_id
                ]
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Quota assigned successfully to all staff!']);

    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}

public function edit($id) {
    $teacher = User::findOrFail($id);
    // Sirf unique leave types lein
    $leaveSettings = StaffLeaveSetting::where('user_id', $id)->get()->unique('leave_type');
    
    return view('admin.staffleavesett.edit_modal', compact('teacher', 'leaveSettings'));
}

public function update(Request $request, $id) 
{
    // 1. Validation
    $request->validate([
        'days.*' => 'required|integer|min:0'
    ]);

    try {
        // 2. Loop through and update
        foreach ($request->days as $settingId => $totalDays) {
            // Hum yahan 'where' mein user_id bhi check kar rahe hain taake security rahe
            StaffLeaveSetting::where('id', $settingId)
                ->where('user_id', $id) 
                ->update(['total_days' => $totalDays]);
        }

        return response()->json(['success' => 'All leave quotas updated successfully!']);
        
    } catch (Exception $e) {
        return response()->json(['error' => 'Error updating records!'], 500);
    }
}

public function destroy($user_id) 
{
    try {
        $deleted = StaffLeaveSetting::where('user_id', $user_id)->delete();
        
        if ($deleted) {
            return response()->json(['success' => 'Deleted successfully!'], 200);
        }
        return response()->json(['error' => 'No record found!'], 404);
        
    } catch (Exception $e) {
        // Yahan error ko return karein taake console mein pata chale
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}
