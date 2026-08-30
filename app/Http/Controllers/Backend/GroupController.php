<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{
    // 1. All Groups List (Ab class_id nahi hai, to direct data simple fetch hoga)
    public function AllGroup()
    {
        $groups = Group::latest()->get();
        return view('admin.groups.index_group', compact('groups'));
    }

    // 2. Add Group Form (Ab classes pass karne ki zaroorat nahi hai)
    public function AddGroup() 
    {
        return view('admin.groups.create_group');
    }

    // 3. Store Group Method
    public function StoreGroup(Request $request)
    {
        // Name unique hona chahiye pure table mein (e.g., Science, Arts, Commerce)
        $request->validate([
            'name' => 'required|string|max:255|unique:groups,name',
            'group_code' => 'required|string|max:50|unique:groups,group_code',
        ], [
            'name.unique' => 'This Group Name already exists.',
            'group_code.unique' => 'This Group Code already exists.',
        ]);

        // Create Global Group
        Group::create([
            'name'        => $request->name,
            'group_code'  => strtoupper($request->group_code), // Hamesha uppercase me save hoga (e.g., SCI, ART)
            'description' => $request->description,
            'status'      => $request->status ?? 0,
        ]);

        return redirect()->route('groups.index')->with([
            'message' => 'Global Academic Group Created Successfully!',
            'alert-type' => 'success'
        ]);
    }

    // 4. Edit Group Method (Classes remove kar di hain)
    public function EditGroup($id)
    {
        $group = Group::findOrFail($id);
        return view('admin.groups.edit_group', compact('group'));
    }

    // 5. Update Group Method
    public function UpdateGroup(Request $request, $id)
    {
        $group = Group::findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('groups', 'name')->ignore($group->id)
            ],
            'group_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('groups', 'group_code')->ignore($group->id)
            ],
        ]);

        $group->update([
            'name'        => $request->name,
            'group_code'  => strtoupper($request->group_code),
            'description' => $request->description,
            'status'      => $request->status ?? 1,
        ]);

        return redirect()->route('groups.index')->with([
            'message' => 'Academic Group Updated Successfully',
            'alert-type' => 'success'
        ]);
    }

    // 6. Toggle Status (AJAX Friendly)
    public function GroupStatus($id)
    {
        try {
            $group = Group::findOrFail($id);
            $group->status = ($group->status == 1) ? 0 : 1;
            $group->save();

            return response()->json([
                'status' => $group->status,
                'message' => 'Group status updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // 7. Delete Group Destroy Method
    public function GroupDestroy($id)
    {
        $group = Group::findOrFail($id);

        // Professional Check: Agar yeh group pehle se class_subject table me linked hai to direct delete nahi hone dena
        $isLinked = DB::table('class_subject')->where('group_id', $group->id)->exists();
        
        if ($isLinked) {
            return redirect()->back()->with([
                'message' => 'Cannot delete! This group is currently assigned to subjects in classes.',
                'alert-type' => 'error'
            ]);
        }

        $group->delete();

        return redirect()->back()->with([
            'message' => 'Group Deleted Successfully',
            'alert-type' => 'success'
        ]);
    }
}