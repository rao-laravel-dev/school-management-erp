<?php

namespace App\Http\Controllers\FrontOffice;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\Source;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ImageService;

class ComplaintController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Display a listing of the complaints.
     */
    public function index()
    {
        $allComplaints = ComplaintType::all();
        $allSources = Source::all();
        // Saare active staff (user + profile trashed nahi), pehle role phir naam, label "Name (Role)"
        $staffs = User::activeStaff(['teacher', 'receptionist', 'accountant', 'librarian'])->get()
            ->sortBy(fn ($u) => strtolower($u->roles->first()->name . ' ' . $u->name))->values();

        // Naya record pehle (# 1 = latest), Created/Updated By ke liye roles eager load
        $complains = Complaint::with(['complaintType', 'source', 'staff.roles', 'createdBy.roles', 'updatedBy.roles'])
            ->orderByDesc('id')
            ->get();

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

    // Validation messages mein column ki jagah form label dikhao
    private function attributeNames(): array
    {
        return [
            'date'              => 'Date',
            'phone'             => 'Phone',
            'email'             => 'Email',
            'description'       => 'Description',
            'action_taken'      => 'Action Taken',
            'status'            => 'Status',
            'complaint_type_id' => 'Complaint Type',
            'source_id'         => 'Source',
            'complaint_by'      => 'Complain By',
            'assigned_to'       => 'Assign To Staff',
            'document'          => 'Attachment',
        ];
    }

    /**
     * Store a newly created complaint in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'complaint_type_id' => 'required|exists:complaint_types,id',
            'source_id'         => 'required|exists:sources,id',
            'date'              => 'required|date_format:d-m-Y',
            'complaint_by'      => 'required|string|max:255',
            'phone'             => 'required|string|max:255',
            'email'             => 'nullable|email|max:255',
            'description'       => 'required|string',
            'assigned_to'       => 'nullable|exists:users,id',
            'document'          => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx|max:5120',
        ], [], $this->attributeNames());

        try {
            $documentPath = null;
            if ($request->hasFile('document')) {
                $documentPath = $this->imageService->uploadFile($request->file('document'), 'complaints'); // DB mein sirf file name
            }

            Complaint::create([
                'complaint_type_id' => $request->complaint_type_id,
                'source_id'         => $request->source_id,
                'date'              => \Carbon\Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d'), // Fix: proper DB format
                'complaint_by'      => $request->complaint_by,
                'phone'             => $request->phone,
                'email'             => $request->email,
                'description'       => $request->description,
                'assigned_to'       => $request->assigned_to,
                'document'          => $documentPath,
                'status'            => 'pending',
                'created_by'        => Auth::id(),
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
            'date'              => 'required|date_format:d-m-Y',
            'complaint_by'      => 'required|string|max:255',
            'phone'             => 'required|string|max:255',
            'email'             => 'nullable|email|max:255',
            'description'       => 'required|string',
            'action_taken'      => 'nullable|string',
            'status'            => 'required|in:pending,in_progress,resolved',
            'assigned_to'       => 'nullable|exists:users,id',
            'document'          => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx|max:5120',
        ], [], $this->attributeNames());

        try {
            $oldDocument = $complain->document;
            $documentPath = $oldDocument;
            if ($request->hasFile('document')) {
                $documentPath = $this->imageService->uploadFile($request->file('document'), 'complaints'); // DB mein sirf file name
            }

            $complain->update([
                'complaint_type_id' => $request->complaint_type_id,
                'source_id'         => $request->source_id,
                'date'              => \Carbon\Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d'),
                'complaint_by'      => $request->complaint_by,
                'phone'             => $request->phone,
                'email'             => $request->email,
                'description'       => $request->description,
                'action_taken'      => $request->action_taken,
                'assigned_to'       => $request->assigned_to,
                'document'          => $documentPath,
                'status'            => $request->status,
                'updated_by'        => Auth::id(), // created_by kabhi change nahi hota
            ]);

            // Naya file save hone ke BAAD purani file storage se hatao
            if ($oldDocument && $documentPath !== $oldDocument) {
                $this->imageService->delete($oldDocument, 'complaints');
            }

            return redirect()->route('complain.index')->with('success', 'Complaint updated successfully!');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    /**
     * Update Complaint Status (popup se chuna hua target status)
     */
    public function UpdateComplaintStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,resolved',
        ]);

        $complain = Complaint::findOrFail($id);
        $complain->status = $request->status;

        $complain->updated_by = Auth::id();
        $complain->save();

        return response()->json([
            'status'  => $complain->status,
            'updated_by' => Auth::user()->name_with_role,
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
            $document = $complain->document;
            $complain->delete();

            // Row delete hone ke baad attachment file bhi hatao (orphan na rahe)
            if ($document) {
                $this->imageService->delete($document, 'complaints');
            }

            // AJAX DELETE: JSON reply, page JS toastr + reload karta hai
            return response()->json(['message' => 'Complaint deleted successfully!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Delete failed: ' . $e->getMessage()], 500);
        }
    }
}
