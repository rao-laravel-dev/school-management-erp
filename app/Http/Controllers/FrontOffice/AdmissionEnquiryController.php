<?php

namespace App\Http\Controllers\FrontOffice;

use App\Http\Controllers\Controller;
use App\Models\AdmissionEnquiry;
use App\Models\Purpose;
use App\Models\Receptionist;
use App\Models\Source;
use Exception;
use Illuminate\Http\Request;

class AdmissionEnquiryController extends Controller
{
    /**
     * Display a listing of the enquiries.
     */
    public function index()
    {
        $sources = Source::all();
        $purposes = Purpose::all();
        // Yahan seedha Receptionist table se data uthayein
        $staffs = Receptionist::all();

        $enquiries = AdmissionEnquiry::all();

        return view(
            'admin.front-office.admission-enquiry.index',
            compact('enquiries', 'sources', 'purposes', 'staffs')
        );
    }

    /**
     * Show the form for creating a new enquiry.
     */
    public function create()
    {
        $sources = Source::all();
        $purposes = Purpose::all();
        $staffs = Receptionist::all();
        $enquiries = AdmissionEnquiry::all();

        // Compact use karna zyada readable hai, aap ye use karein
        return view(
            'admin.front-office.admission-enquiry.create',
            compact('sources', 'purposes', 'staffs', 'enquiries')
        );
    }

    /**
     * Store a newly created enquiry in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'phone'               => 'required|string|max:255',
            'email'               => 'nullable|email|max:255',
            'date'                => 'required|date',
            'next_follow_up_date' => 'nullable|date',
            'source_id'           => 'nullable|exists:sources,id',
            'purpose_id'          => 'nullable|exists:purposes,id',
            'description'         => 'nullable|string',
            'address'             => 'nullable|string',
        ]);

        try {
            AdmissionEnquiry::create([
                'name'                => $request->name,
                'phone'               => $request->phone,
                'email'               => $request->email,
                'date'                => $request->date,
                'next_follow_up_date' => $request->next_follow_up_date,
                'source_id'           => $request->source_id,
                'purpose_id'          => $request->purpose_id,
                'assigned_to'         => $request->assigned_to, // Ya auth()->id() agar login user assign karna hai
                'description'         => $request->description,
                'address'             => $request->address,
                'status'              => 'pending', // Default status
            ]);

            return redirect()->route('admission-enquiry.index')->with('success', 'Enquiry added successfully!');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified enquiry.
     */
    public function edit($id)
    {
        $enquiry = AdmissionEnquiry::findOrFail($id);
        $sources = Source::all();
        $purposes = Purpose::all();
        // Receptionist ya Staff ka data zaroor fetch karein
        $staffs = \App\Models\Receptionist::all();

        return view(
            'admin.front-office.admission-enquiry.edit',
            compact('enquiry', 'sources', 'purposes', 'staffs')
        );
    }

    /**
     * Update the specified enquiry in storage.
     */
    public function update(Request $request, $id)
    {
        $enquiry = AdmissionEnquiry::findOrFail($id);

        $request->validate([
            'name'                => 'required|string|max:255',
            'phone'               => 'required|string|max:255',
            'email'               => 'nullable|email|max:255',
            'date'                => 'required|date',
            'next_follow_up_date' => 'nullable|date',
            'source_id'           => 'nullable|exists:sources,id', // 'required' hata diya
            'purpose_id'          => 'nullable|exists:purposes,id', // 'required' hata diya
            'assigned_to'         => 'nullable', // Ye zaroori hai
            'description'         => 'nullable|string',
            'address'             => 'nullable|string',
        ]);

        try {
            $enquiry->update([
                'name'                => $request->name,
                'phone'               => $request->phone,
                'email'               => $request->email,
                'date'                => $request->date,
                'next_follow_up_date' => $request->next_follow_up_date,
                'source_id'           => $request->source_id,
                'purpose_id'          => $request->purpose_id,
                'assigned_to'         => $request->assigned_to, // Update ho raha hai
                'description'         => $request->description,
                'address'             => $request->address,
            ]);

            return redirect()->route('admission-enquiry.index')->with('success', 'Enquiry updated successfully!');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    /**
     * Custom Action: Search Enquiries
     */
    public function search(Request $request)
    {
        $query = AdmissionEnquiry::query();

        if ($request->has('search_term')) {
            $query->where('name', 'LIKE', '%' . $request->search_term . '%')
                ->orWhere('phone', 'LIKE', '%' . $request->search_term . '%');
        }

        $enquiries = $query->with(['source', 'purpose'])->get();
        return view('admin.front-office.admission-enquiry.index', compact('enquiries'));
    }

    /**
     * Level 2: Update Enquiry Status (Active/Passive/Resolved)
     */
    public function UpdateEnquiryStatus(Request $request, $id)
{
    $enquiry = AdmissionEnquiry::findOrFail($id);
    $currentStatus = strtolower($enquiry->status);

    if ($request->has('action') && in_array($request->action, ['approve', 'reject'])) {
        // Explicit buttons (Approve/Reject)
        $enquiry->status = ($request->action == 'approve') ? 'active' : 'inactive';
    } else {
        // Toggle Logic Cycle: Pending -> Active -> Inactive -> Pending
        if ($currentStatus == 'pending') {
            $enquiry->status = 'active';
        } elseif ($currentStatus == 'active') {
            $enquiry->status = 'inactive';
        } else {
            $enquiry->status = 'pending';
        }
    }

    $enquiry->save();

    return response()->json([
        'status' => $enquiry->status,
        'message' => 'Status updated to ' . ucfirst($enquiry->status)
    ]);
}

    /**
     * Level 2: Delete Enquiry (EnquiryDestroy)
     */
    public function EnquiryDestroy($id)
    {
        try {
            $enquiry = AdmissionEnquiry::findOrFail($id);
            $enquiry->delete();

            return redirect()->route('admission-enquiry.index')->with('success', 'Enquiry deleted successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }
}
