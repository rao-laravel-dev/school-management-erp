<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\StudentHouse;
use Illuminate\Http\Request;

class StudentHouseController extends Controller
{
    public function index()
    {
        $houses = StudentHouse::latest()->get();

        return view('admin.student_house.index', compact('houses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|unique:student_houses,name',
            'color'  => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        StudentHouse::create($request->only('name', 'color', 'status'));

        return redirect()->back()->with('success', 'House added successfully');
    }

    public function update(Request $request, StudentHouse $house)
    {
        $request->validate([
            'name'   => 'required|string|unique:student_houses,name,' . $house->id,
            'color'  => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        $house->update($request->only('name', 'color', 'status'));

        return redirect()->back()->with('success', 'House updated successfully');
    }

    public function destroy(StudentHouse $house)
    {
        $house->delete();

        return redirect()->back()->with('success', 'House deleted successfully');
    }

    public function toggleStatus(StudentHouse $house)
    {
        $house->status = $house->status == 1 ? 0 : 1;
        $house->save();

        return response()->json([
            'status'  => $house->status,
            'message' => 'Status updated successfully',
        ]);
    }
}
