<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\StaffLeaveApplication;
use App\Models\StaffLeaveSetting;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StaffLeaveApplicationController extends Controller
{
    private function getLeaveData()
    {
        // Ab yahan sirf staffs fetch karein, leaveSettings ki zaroorat nahi
        // Sirf active (status 1) staff; roles + profile relations eager load (staff_code ke liye, N+1 nahi)
        $staffs = User::whereDoesntHave('roles', function ($query) {
            $query->whereIn('name', ['superadmin', 'admin', 'parent', 'student', 'user']);
        })->where('status', 1)
            ->with(['roles', 'teacher', 'accountant', 'receptionist'])
            ->get();

        return $staffs; // Ab sirf staff return ho raha hai
    }

    // Sirf admin/superadmin sab rows dekh sakte hain
    private function canManageAllLeaves(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'superadmin']);
    }

    // Non-admin sirf apni row (user_id == auth id), warna 403
    private function ensureLeaveOwner($userId): void
    {
        if (! $this->canManageAllLeaves() && (int) $userId !== (int) auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
    }

    // Half day sirf ek din ki leave par (start = end), store/update dono mein
    private function halfDayRule(Request $request): \Closure
    {
        return function ($attribute, $value, $fail) use ($request) {
            if ($value !== 'full' && $request->start_date !== $request->end_date) {
                $fail('Half day leave sirf ek din (start = end) ke liye ho sakti hai.');
            }
        };
    }

    public function index()
    {
        $staffs = $this->getLeaveData();

        // Agar admin/superadmin hai toh sab, agar staff hai toh sirf uska
        if ($this->canManageAllLeaves()) {
            $applications = StaffLeaveApplication::with('user')->latest()->get();
        } else {
            $applications = StaffLeaveApplication::where('user_id', auth()->id())->latest()->get();
        }

        return view('admin.staffleaveapp.index', compact('applications', 'staffs'));
    }

    // --- Naye AJAX Methods (Yahan add karein) ---

    public function getLeaveTypes($user_id)
{
    $this->ensureLeaveOwner($user_id);

    // Specific user ki settings
    $types = StaffLeaveSetting::where('user_id', $user_id)->get(['leave_type']);

    if ($types->isEmpty()) {
        Log::info("No settings found for user_id: " . $user_id);

        $types = StaffLeaveSetting::whereNull('user_id')
                                  ->orWhere('user_id', 0)
                                  ->get(['leave_type']);
    }

    // values() add kiya taake JSON array saaf mile
    return response()->json($types->values()); 
}

    public function getLeaveBalance($user_id, $leave_type)
    {
        $this->ensureLeaveOwner($user_id);

        $setting = StaffLeaveSetting::where('user_id', $user_id)
            ->where('leave_type', $leave_type)
            ->first();

        $total_allowed = $setting ? $setting->total_days : 0;

        $used_leaves = StaffLeaveApplication::where('user_id', $user_id)
            ->where('leave_type', $leave_type)
            ->where('status', 'approved')
            ->sum('total_days');

        $balance = $total_allowed - $used_leaves;

        return response()->json([
            'total_allowed' => $total_allowed,
            'used'          => $used_leaves,
            'balance'       => $balance
        ]);
    }

    public function create()
    {
        // Apply ab sirf index page ke modal se hota hai (sab roles), alag page band
        return redirect()->route('staffleaveapp.index');
    }



    public function store(Request $request)
    {
        Log::info($request->all());
        // Agar admin/superadmin nahi hai to force current user ID
        if (! $this->canManageAllLeaves()) {
            $request->merge(['user_id' => auth()->id()]);
        }
        // 1. Validation
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'leave_type' => 'required',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'document'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'leave_duration' => ['nullable', 'in:full,first_half,second_half', $this->halfDayRule($request)],
        ]);

        try {
            // 2. File Upload (Updated: Stores ONLY file name in DB)
            $filePath = null;
            if ($request->hasFile('document')) {
                $file = $request->file('document');

                // hashName() se unique naam generate hoga bina folder path ke (e.g., abc123xyz.jpg)
                $fileName = $file->hashName();

                // File physically 'public/storage/leave_documents/' folder mein save hogi
                $file->storeAs('leave_documents', $fileName, 'public');

                // DB table ke column ke liye sirf file ka naam set hoga
                $filePath = $fileName;
            }

            // 3. Calculation
            // Form mein duration field na ho to full day maan lo
            $duration = $request->leave_duration ?: 'full';
            $days = (strtotime($request->end_date) - strtotime($request->start_date)) / 86400 + 1;
            if ($duration != 'full') {
                $days = 0.5;
            }

            // 4. Save to DB
            StaffLeaveApplication::create([
                'user_id'        => $request->user_id,
                'apply_date'     => $request->apply_date ?? date('Y-m-d'),
                'leave_type'     => $request->leave_type,
                'from_date'      => $request->start_date, // 'start_date' ko 'from_date' mein map kiya
                'to_date'        => $request->end_date,   // 'end_date' ko 'to_date' mein map kiya
                'leave_duration' => $duration,
                'total_days'     => $days,                // 'days_count' ko 'total_days' mein map kiya
                'reason'         => $request->reason,
                'document'       => $filePath,            // Yahan ab sirf clean file name save hoga
                'status'         => 'pending'
            ]);

            // AJAX (index modal) ko JSON, normal form (create page) ko redirect
            if ($request->expectsJson()) {
                return response()->json(['success' => 'Application submitted successfully!']);
            }

            return redirect()->route('staffleaveapp.index')->with([
                'message'    => 'Application submitted successfully!',
                'alert-type' => 'success',
            ]);
        } catch (\Exception $e) {
            // Asal error log mein, user ko generic message
            Log::error('Leave store failed: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Something went wrong. Please try again.'], 500);
            }

            return back()->withInput()->with([
                'message'    => 'Something went wrong. Please try again.',
                'alert-type' => 'error',
            ]);
        }
    }

    public function edit($id)
    {
        $application = StaffLeaveApplication::findOrFail($id);
        $this->ensureLeaveOwner($application->user_id);

        // Security Check
        if ($application->status !== 'pending') {
            return response()->json(['error' => 'Cannot edit processed application.'], 403);
        }

        // Modal prefill ke liye sirf zaroori fields (index ka apply modal edit mode mein)
        if (request()->expectsJson()) {
            return response()->json($application->only([
                'id', 'user_id', 'apply_date', 'leave_type', 'from_date', 'to_date', 'leave_duration', 'reason',
            ]));
        }

        // Alag edit page nahi, index modal se hota hai
        return redirect()->route('staffleaveapp.index');
    }

    public function update(Request $request, $id)
    {
        // 1. Validation
        $request->validate([
            'leave_type' => 'required',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'document'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'leave_duration' => ['nullable', 'in:full,first_half,second_half', $this->halfDayRule($request)],
        ]);

        // Owner check try se bahar, warna 403 catch hokar 500 ban jata
        $application = StaffLeaveApplication::findOrFail($id);
        $this->ensureLeaveOwner($application->user_id);

        try {
            // Sirf pending applications edit ho sakein (Security check)
            if ($application->status !== 'pending') {
                return response()->json(['error' => 'You cannot edit an application that is already processed.'], 403);
            }

            // 2. File Handling
            $filePath = $application->document; // Purani file ka naam
            if ($request->hasFile('document')) {
                // Agar purani file exist karti hai to delete karein
                if ($filePath && Storage::disk('public')->exists('leave_documents/' . $filePath)) {
                    Storage::disk('public')->delete('leave_documents/' . $filePath);
                }

                // Nayi file store karein
                $file = $request->file('document');
                $fileName = $file->hashName();
                $file->storeAs('leave_documents', $fileName, 'public');
                $filePath = $fileName;
            }

            // 3. Calculation
            // Form mein duration field na ho to full day maan lo
            $duration = $request->leave_duration ?: 'full';
            $days = (strtotime($request->end_date) - strtotime($request->start_date)) / 86400 + 1;
            if ($duration != 'full') {
                $days = 0.5;
            }

            // 4. Update Database
            $application->update([
                'leave_type'     => $request->leave_type,
                'from_date'      => $request->start_date,
                'to_date'        => $request->end_date,
                'leave_duration' => $duration,
                'total_days'     => $days,
                'reason'         => $request->reason,
                'document'       => $filePath,
            ]);

            return response()->json(['success' => 'Application updated successfully!']);
        } catch (Exception $e) {
            // Asal error log mein, user ko generic message
            Log::error('Leave update failed: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong. Please try again.'], 500);
        }
    }


    public function show($id)
    {
        $leave = StaffLeaveApplication::findOrFail($id);
        $this->ensureLeaveOwner($leave->user_id);

        if (!$leave->document) {
            return back()->with('error', 'No attachment found!');
        }

        // File ka path check karein
        $filePath = 'leave_documents/' . $leave->document;

        if (Storage::disk('public')->exists($filePath)) {
            return response()->file(storage_path('app/public/' . $filePath));
        }

        return back()->with('error', 'File does not exist on server.');
    }



    public function updateStatus($id)
    {
        $app = StaffLeaveApplication::findOrFail($id);

        // Toggle Logic: Agar pending/rejected hai toh approve kar do, warna reject kar do
        if ($app->status == 'pending' || $app->status == 'rejected') {
            $app->status = 'approved';
            $message = 'Leave Approved Successfully';
        } else {
            $app->status = 'rejected';
            $message = 'Leave Rejected Successfully';
        }

        $app->approved_by = auth()->id();
        $app->save();

        return response()->json([
            'status' => $app->status,
            'message' => $message
        ]);
    }

    public function destroy($id)
    {
        // Owner check try se bahar, warna 403 catch hokar 500 ban jata
        $leaveApp = StaffLeaveApplication::findOrFail($id);
        $this->ensureLeaveOwner($leaveApp->user_id);

        // Non-admin sirf apni pending leave delete kare
        if (! $this->canManageAllLeaves() && $leaveApp->status !== 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Processed application delete nahi ho sakti.'], 403);
        }

        try {
            // 1. Storage se file delete karna (Agar document exist karta hai)
            if ($leaveApp->document) {
                // Check karein ke path mein folder name hai ya nahi (Hamara purana vs naya logic)
                $exists = Storage::disk('public')->exists('leave_documents/' . $leaveApp->document);

                if ($exists) {
                    Storage::disk('public')->delete('leave_documents/' . $leaveApp->document);
                }
            }

            // 2. Database se record delete karna
            $leaveApp->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Leave record deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
