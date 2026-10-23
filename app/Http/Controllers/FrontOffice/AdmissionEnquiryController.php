<?php

namespace App\Http\Controllers\FrontOffice;

use App\Http\Controllers\Controller;
use App\Models\AdmissionEnquiry;
use App\Models\Purpose;
use App\Models\Receptionist;
use App\Models\Source;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdmissionEnquiryController extends Controller
{
    /**
     * Display a listing of the enquiries.
     */
    public function index()
    {
        $sources = Source::all();
        $purposes = Purpose::all();
        // Saare active staff (teacher, receptionist, accountant, librarian), pehle role phir naam (value = users.id, assigned_to isi ko store karta hai)
        $staffs = User::activeStaff(['teacher', 'receptionist', 'accountant', 'librarian'])->get()
            ->sortBy(fn ($u) => strtolower($u->roles->first()->name . ' ' . $u->name))->values();

        // Naya record pehle (# 1 = latest), Created/Updated By ke liye roles eager load
        $enquiries = AdmissionEnquiry::with(['source', 'purpose', 'staff.roles', 'createdBy.roles', 'updatedBy.roles'])
            ->orderByDesc('id')
            ->get();

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

    // Validation messages mein column ki jagah form label dikhao
    private function attributeNames(): array
    {
        return [
            'name'                => 'Name',
            'phone'               => 'Phone',
            'email'               => 'Email',
            'address'             => 'Address',
            'description'         => 'Description',
            'source_id'           => 'Source',
            'purpose_id'          => 'Purpose',
            'date'                => 'Enquiry Date',
            'next_follow_up_date' => 'Next Follow Up',
            'assigned_to'         => 'Assign To Staff',
        ];
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
            'source_id'           => 'required|exists:sources,id',
            'purpose_id'          => 'required|exists:purposes,id',
            'assigned_to'         => 'nullable|exists:users,id',
            'description'         => 'nullable|string',
            'address'             => 'nullable|string',
        ], [], $this->attributeNames());

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
                'created_by'          => Auth::id(),
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
            'source_id'           => 'required|exists:sources,id',
            'purpose_id'          => 'required|exists:purposes,id',
            'assigned_to'         => 'nullable|exists:users,id', // Ye zaroori hai
            'description'         => 'nullable|string',
            'address'             => 'nullable|string',
        ], [], $this->attributeNames());

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
                'updated_by'          => Auth::id(), // created_by kabhi change nahi hota
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

    $enquiry->updated_by = Auth::id();
    $enquiry->save();

    return response()->json([
        'status' => $enquiry->status,
        'updated_by' => Auth::user()->name_with_role,
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

            // AJAX DELETE: JSON reply, page JS toastr + reload karta hai
            return response()->json(['message' => 'Enquiry deleted successfully!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Delete failed: ' . $e->getMessage()], 500);
        }
    }
}
