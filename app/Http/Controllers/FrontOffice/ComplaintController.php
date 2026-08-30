<?php

namespace App\Http\Controllers\FrontOffice;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\Source;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    /**
     * Display a listing of the complaints.
     */
    public function index()
    {
        $allComplaints = ComplaintType::all();
        $allSources = Source::all();
        $staffs = User::role(['Teacher', 'Receptionist'])->get();

        $complains = Complaint::latest()->get(); // Fix: naya record pehle

        return view(
            'admin.front-office.complain.index',
            compact('complains', 'allComplaints', 'allSources', 'staffs')
        );
    }

    /**
     * Show the form for creating a new complaint.
     */
    public function create()
    {
        $allComplaints = ComplaintType::all();
        $allSources = Source::all();
        $staffs = User::role(['Teacher', 'Receptionist'])->get();
        $complains = Complaint::latest()->get(); // yahan bhi

        return view(
            'admin.front-office.complain.create',
            compact('allComplaints', 'allSources', 'staffs', 'complains')
        );
    }

    /**
     * Store a newly created complaint in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'complaint_type_id' => 'required|exists:complaint_types,id',
            'source_id'         => 'required|exists:sources,id',
            'date'              => 'required',
            'complaint_by'      => 'required|string|max:255',
            'phone'             => 'required|string|max:255',
            'email'             => 'nullable|email|max:255',
            'description'       => 'nullable|string',
            'assigned_to'       => 'nullable|exists:users,id',
            'document'          => 'nullable|file|max:5120',
        ]);

        try {
            $documentPath = null;
            if ($request->hasFile('document')) {
                $documentPath = $request->file('document')->store('complaints', 'public');
            }

            Complaint::create([
                'complaint_type_id' => $request->complaint_type_id,
                'source_id'         => $request->source_id,
                'date'              => \Carbon\Carbon::parse($request->date)->format('Y-m-d'), // Fix: proper DB format
                'complaint_by'      => $request->complaint_by,
                'phone'             => $request->phone,
                'email'             => $request->email,
                'description'       => $request->description,
                'assigned_to'       => $request->assigned_to,
                'document'          => $documentPath,
                'status'            => 'pending',
            ]);

            return redirect()->route('complain.index')->with('success', 'Complaint added successfully!');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified complaint.
     */
    public function edit($id)
    {
        $complain = Complaint::findOrFail($id);
        $allComplaints = ComplaintType::all();
        $allSources = Source::all();
        $staffs = User::role(['Teacher', 'Receptionist'])->get();

        return view(
            'admin.front-office.complain.edit',
            compact('complain', 'allComplaints', 'allSources', 'staffs')
        );
    }

    /**
     * Update the specified complaint in storage.
     */
    public function update(Request $request, $id)
    {
        $complain = Complaint::findOrFail($id);

        $request->validate([
            'complaint_type_id' => 'required|exists:complaint_types,id',
            'source_id'         => 'required|exists:sources,id',
            'date'              => 'required',
            'complaint_by'      => 'required|string|max:255',
            'phone'             => 'required|string|max:255',
            'email'             => 'nullable|email|max:255',
            'description'       => 'nullable|string',
            'action_taken'      => 'nullable|string',
            'assigned_to'       => 'nullable|exists:users,id',
            'document'          => 'nullable|file|max:5120',
        ]);

        try {
            $documentPath = $complain->document;
            if ($request->hasFile('document')) {
                $documentPath = $request->file('document')->store('complaints', 'public');
            }

            $complain->update([
                'complaint_type_id' => $request->complaint_type_id,
                'source_id'         => $request->source_id,
                'date'              => \Carbon\Carbon::parse($request->date)->format('Y-m-d'),
                'complaint_by'      => $request->complaint_by,
                'phone'             => $request->phone,
                'email'             => $request->email,
                'description'       => $request->description,
                'action_taken'      => $request->action_taken,
                'assigned_to'       => $request->assigned_to,
                'document'          => $documentPath,
            ]);

            return redirect()->route('complain.index')->with('success', 'Complaint updated successfully!');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    /**
     * Update Complaint Status (Pending -> Active -> Inactive cycle)
     */
    public function UpdateComplaintStatus(Request $request, $id)
    {
        $complain = Complaint::findOrFail($id);
        $currentStatus = strtolower($complain->status);

        // Cycle: Pending -> In Progress -> Resolved -> Pending (reopen)
        if ($currentStatus == 'pending') {
            $complain->status = 'in_progress';
        } elseif ($currentStatus == 'in_progress') {
            $complain->status = 'resolved';
        } else {
            $complain->status = 'pending';
        }

        $complain->save();

        return response()->json([
            'status'  => $complain->status,
            'message' => 'Status updated to ' . ucfirst(str_replace('_', ' ', $complain->status))
        ]);
    }

    /**
     * Delete Complaint
     */
    public function ComplaintDestroy($id)
    {
        try {
            $complain = Complaint::findOrFail($id);
            $complain->delete();

            return redirect()->route('complain.index')->with('success', 'Complaint deleted successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }
}
