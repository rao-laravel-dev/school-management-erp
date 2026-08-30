<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    /**
     * 1. Display Sessions List
     */
    public function index()
    {
        // Newest sessions upar dikhane ke liye orderBy lagaya hai
        $academic_years = AcademicYear::orderBy('id', 'desc')->get();
        return view('admin.academic_years.index', compact('academic_years'));
    }

    /**
     * 2. Store New Session
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|unique:academic_years,name',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ], [
            'name.unique' => 'This Academic Year / Session is already registered.',
        ]);

        AcademicYear::create([
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'status'     => $request->status ?? 1,
        ]);
        

        return redirect()->back()->with([
            'message'    => 'Academic Year created successfully!',
            'alert-type' => 'success'
        ]);
    }

    /**
     * 3. Edit Session (Optional - Since we handle inline editing via JS, 
     * but keeping it if you ever need traditional edit page)
     */
    public function edit($id)
    {
        $academic_year = AcademicYear::findOrFail($id);
        return view('admin.academic_years.edit', compact('academic_year'));
    }

    /**
     * 4. Update Existing Session
     */
    public function update(Request $request, $id)
    {
        $academic_year = AcademicYear::findOrFail($id);

        $request->validate([
            'name'       => 'required|string|unique:academic_years,name,' . $id,
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ], [
            'name.unique' => 'This Academic Year / Session name is already taken.',
        ]);

        $academic_year->update([
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
        ]);

        return redirect()->route('academic_years.index')->with([
            'message'    => 'Academic Year updated successfully!',
            'alert-type' => 'info'
        ]);
    }

    /**
     * 5. Delete Session (Destroy)
     */
    public function destroy($id)
    {
        $academic_year = AcademicYear::findOrFail($id);
        
        // Safety Check: Agar yeh current active session hai to delete nahi karne dena
        if ($academic_year->is_current == 1) {
            return redirect()->back()->with([
                'message'    => 'Cannot delete the current active running session!',
                'alert-type' => 'error'
            ]);
        }

        $academic_year->delete();

        return redirect()->back()->with([
            'message'    => 'Academic Year removed successfully!',
            'alert-type' => 'warning'
        ]);
    }

    /**
     * 6. Special Action: Toggle Status via AJAX
     */
    public function toggleStatus($id)
    {
        $year = AcademicYear::findOrFail($id);
        
        // Status toggle toggle (1 to 0 or 0 to 1)
        $year->status = $year->status == 1 ? 0 : 1;
        $year->save();

        // AJAX response matching Toastr notifications
        return response()->json([
            'status'  => $year->status,
            'message' => 'Session status updated to ' . ($year->status == 1 ? 'Active' : 'Inactive')
        ]);
    }

    /**
     * 7. Special Action: Mark As Current Running Session (Database Transaction)
     */
    public function markAsCurrent($id)
    {
        // DB Transaction lagayi taake data consistency kharab na ho
        DB::transaction(function () use ($id) {
            // Step A: Pehle poore school system ke saare sessions ko 0 (false) karo
            AcademicYear::query()->update(['is_current' => 0]);

            // Step B: Ab selected session ko 1 (true) aur status active kar do
            $year = AcademicYear::findOrFail($id);
            $year->update([
                'is_current' => 1,
                'status'     => 1 // Current session lazmi active hona chahiye
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'System context successfully shifted to the selected academic cycle!'
        ]);
    }
}

