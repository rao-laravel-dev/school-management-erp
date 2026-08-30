<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Receptionist;
use App\Models\StaffBankDetail;
use App\Models\StaffSalary;
use App\Models\User;
use App\Services\ImageService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ReceptionController extends Controller
{
    // Class ke andar sabse upar property define karein
    protected $imageService;

    // Constructor ke zariye service inject karein
    public function __construct(ImageService $imageService) // 👈 Apni sahi ImageService ka namespace likhein
    {
        $this->imageService = $imageService;
    }
    /**
     * 1. All Receptionists Listing Page
     */
   public function AllReceptionist()
{
    // Sirf wahi users count karein jo Receptionist table mein exist karte hain
    $query = User::role('receptionist')->whereHas('receptionist');

    $totalCount = $query->count();
    $activeCount = (clone $query)->where('status', 1)->count();
    $inactiveCount = (clone $query)->whereIn('status', [0, 2])->count();

    $receptionists = Receptionist::with('user')->latest()->get();

    return view('admin.receptionist.index_receptionist', compact(
        'receptionists', 'totalCount', 'activeCount', 'inactiveCount'
    ));
}

    /**
     * 2. Show Add New Receptionist Form
     */
    public function AddReceptionist()
    {
        // Current date form me auto-fill krne k liye
        $currentDate = Carbon::now()->format('Y-m-d');

        // Khali ID bhej rhe hain kyun k ye ab AJAX se generate hogi
        $receptionistId = '';

        return view('admin.receptionist.add_receptionist', compact('currentDate', 'receptionistId'));
    }

   public function getDetails($id)
{
    $receptionist = Receptionist::with(['user.salaries', 'user.bankDetail'])->findOrFail($id);
    
    // Spatie se role get karein
    $user = $receptionist->user; 
    $role = $user ? $user->roles->first() : null; 
    
    $leaveSettings = collect(); // Default empty

    if ($role) {
        $leaveSettings = \App\Models\StaffLeaveSetting::where('role_id', $role->id)
                            ->get()
                            ->unique('leave_type');
    }

    return view('admin.receptionist.details_partials', compact('receptionist', 'leaveSettings'))->render();
}

    private function generateReceptionistId($firstName = null)
    {
        // Agar first_name khali ho to default 'REC' lag jaye
        $prefix = !empty($firstName) ? strtoupper(preg_replace('/[^A-Za-z]/', '', $firstName)) : 'REC';

        $lastReceptionist = Receptionist::latest('id')->first();

        $number = 1;
        if ($lastReceptionist && !empty($lastReceptionist->receptionist_id)) {
            $lastId = $lastReceptionist->receptionist_id;
            $number = (int)preg_replace('/[^0-9]/', '', $lastId) + 1;
        }

        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function getReceptionistId(Request $request)
    {
        $firstName = $request->get('first_name');
        if (!$firstName || trim($firstName) === '') {
            return response()->json(['receptionist_id' => '']);
        }

        // Pehle 4 letters uppercase mein
        $prefix = strtoupper(substr(trim($firstName), 0, 4));

        // Count nikalne ke liye seedha Receptionist profile table ka count use karein
        $count = Receptionist::count() + 1;
        $newId = $prefix . str_pad($count, 3, '0', STR_PAD_LEFT); // Output: KASH001

        return response()->json(['receptionist_id' => $newId]);
    }


    public function getGeneratedId(Request $request)
    {
        $firstName = $request->input('first_name');
        $prefix = strtoupper(preg_replace('/[^A-Za-z]/', '', $firstName));

        $lastReceptionist = Receptionist::where('receptionist_id', 'like', $prefix . '%')->latest('id')->first();

        return response()->json([
            'receptionist_id' => $this->generateReceptionistId($firstName),
            'debug_found' => $lastReceptionist ? $lastReceptionist->receptionist_id : 'None'
        ]);
    }

    public function StoreReceptionist(Request $request)
    {
        // Form Validation
        $validator = Validator::make($request->all(), [
            'first_name'         => 'required|string|max:255',
            'last_name'          => 'required|string|max:255',
            'father_name'        => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email',
            'cnic'               => 'required|string|max:15|unique:receptionists,cnic',
            'phone'              => 'required|string|max:15',
            'alt_phone'          => 'nullable|string|max:15',
            'emergency_name'     => 'required|string|max:255',
            'emergency_relation' => 'nullable|string|max:100',
            'emergency_phone'    => 'required|string|max:20',
            'dob'                => 'required|date',
            'basic_salary'       => 'required|numeric|min:0',
            'bank_type'          => 'required|in:commercial,microfinance_wallet',
            'account_title'      => 'required|string|max:255',
            'account_number'     => 'required|string|max:255',
            'bank_name'          => 'nullable|string|max:255',
            'photo'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Validation Fail Condition
        if ($validator->fails()) {
            return back()
                ->withErrors($validator) // 👈 Yeh fields ko red highlight karega (.is-invalid)
                ->withInput()
                ->with('error', 'Form validation failed. Please check all fields.'); // 👈 Yeh Toastr trigger karega
        }

        DB::beginTransaction();
        try {
            $receptionistId = $this->generateReceptionistId($request->first_name);

            if (is_null($receptionistId)) {
                throw new Exception("Failed to generate Receptionist ID.");
            }

            $username = $receptionistId;

            // Step 1: Create User
            $user = User::create([
                'name'     => $request->first_name . ' ' . $request->last_name,
                'email'    => $request->email,
                'username' => $username,
                'password' => Hash::make('receptionist123'), // Default Password
                'gender'   => $request->gender,
                'status'   => 2,
            ]);

            // Spatie Role Assigning
            $user->assignRole($request->role ?? 'receptionist');

            // Step 2: Handle Photo Upload
            $photoName = null;
            if ($request->hasFile('photo')) {
                $photoName = $this->imageService->upload($request->file('photo'), 'uploads/receptionist_images', 300, 300);
            }

            // Step 3: Create Receptionist Profile
            $receptionist = Receptionist::create([
                'user_id'            => $user->id,
                'receptionist_id'    => $receptionistId,
                'first_name'         => $request->first_name,
                'last_name'          => $request->last_name,
                'father_name'        => $request->father_name,
                'mother_name'        => $request->mother_name,
                'email'              => $request->email,
                'cnic'               => $request->cnic,
                'gender'             => $request->gender,
                'dob'                => $request->dob,
                'marital_status'     => $request->marital_status,
                'phone'              => $request->phone,
                'alt_phone'          => $request->alt_phone,
                'emergency_name'     => $request->emergency_name,
                'emergency_relation' => $request->emergency_relation,
                'emergency_phone'    => $request->emergency_phone,
                'address'            => $request->address,
                'permanent_address'  => $request->permanent_address,
                'desk_number'        => $request->desk_number,
                'qualification'      => $request->qualification,
                'work_experience'    => $request->work_experience,
                'joining_date'       => $request->joining_date ?? now()->toDateString(),
                'photo'              => $photoName,
                'contract_type'      => $request->contract_type ?? 'permanent',
                'work_shift'         => $request->work_shift,
            ]);

            // Step 4: Save Staff Bank Details
            StaffBankDetail::create([
                'user_id'        => $user->id,
                'bank_type'      => $request->bank_type,
                'bank_name'      => $request->bank_name,
                'account_title'  => $request->account_title,
                'account_number' => $request->account_number,
                'iban'           => $request->bank_type == 'commercial' ? $request->iban : null,
                'branch_name'    => $request->bank_type == 'commercial' ? $request->branch_name : null,
                'branch_code'    => $request->bank_type == 'commercial' ? $request->branch_code : null,
            ]);

            // Step 5: Initialize Salary Record
            StaffSalary::create([
                'user_id'         => $user->id,
                'basic_salary'    => $request->basic_salary,
                'allowance'       => 0,
                'deduction'       => 0,
                'advance_balance' => 0,
            ]);

            DB::commit();

            // Consistent Toastr Response Key
            return redirect()->route('receptionist.index')->with('success', 'Receptionist profile created successfully! ID: ' . $receptionistId);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Database Error: ' . $e->getMessage())->withInput();
        }
    }


    // 1. Edit Form View
    public function EditReceptionist($id)
    {
        // If $id is the Receptionist primary key ID:
        $receptionist = Receptionist::with(['user', 'salary', 'bankDetail'])->findOrFail($id);
        return view('admin.receptionist.edit_receptionist', compact('receptionist'));
    }

    // 2. Update Data Logic
    public function UpdateReceptionist(Request $request, $id)
    {
        $receptionist = Receptionist::findOrFail($id);
        $user = User::findOrFail($receptionist->user_id);

        // Validation Rules
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'phone'      => 'required|string',
            'gender'     => 'required',
            'photo'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Standard Laravel Photo Upload Handle
        $photoName = $receptionist->photo; // Purani photo backup
        if ($request->hasFile('photo')) {
            // Agar purani photo file folder me exist karti ho to delete karna
            if ($receptionist->photo && file_exists(public_path('uploads/receptionist_images/' . $receptionist->photo))) {
                @unlink(public_path('uploads/receptionist_images/' . $receptionist->photo));
            }

            $file = $request->file('photo');
            $photoName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/receptionist_images'), $photoName);
        }

        // A. User Table Update
        $user->update([
            'name'  => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
        ]);

        // B. Receptionist Table Update
        $receptionist->update([
            'first_name'         => $request->first_name,
            'last_name'          => $request->last_name,
            'father_name'        => $request->father_name,
            'mother_name'        => $request->mother_name,
            'email'              => $request->email,
            'cnic'               => $request->cnic,
            'gender'             => $request->gender,
            'dob'                => $request->dob,
            'marital_status'     => $request->marital_status,
            'phone'              => $request->phone,
            'alt_phone'          => $request->alt_phone,
            'emergency_name'     => $request->emergency_name,
            'emergency_relation' => $request->emergency_relation,
            'emergency_phone'    => $request->emergency_phone,
            'address'            => $request->address,
            'permanent_address'  => $request->permanent_address,
            'desk_number'        => $request->desk_number,
            'qualification'      => $request->qualification,
            'work_experience'    => $request->work_experience,
            'joining_date'       => $request->joining_date,
            'contract_type'      => $request->contract_type,
            'work_shift'         => $request->work_shift,
            'photo'              => $photoName,
        ]);

        // Step 4: Save Staff Bank Details
            StaffBankDetail::updateOrCreate(
    ['user_id' => $user->id], // Is ID se dhondho
                ['bank_type'      => $request->bank_type,
                'bank_name'      => $request->bank_name,
                'account_title'  => $request->account_title,
                'account_number' => $request->account_number,
                'iban'           => $request->bank_type == 'commercial' ? $request->iban : null,
                'branch_name'    => $request->bank_type == 'commercial' ? $request->branch_name : null,
                'branch_code'    => $request->bank_type == 'commercial' ? $request->branch_code : null,
            ]
            );

            // Step 5: Initialize Salary Record
            StaffSalary::updateOrCreate(
    ['user_id' => $user->id], // Is ID se dhondho
    [
                'basic_salary'    => $request->basic_salary,
                'allowance'       => 0,
                'deduction'       => 0,
                'advance_balance' => 0,
            ]
            );

        return redirect()->route('receptionist.index')->with('success', 'Receptionist Profile Updated Successfully!');
    }

    // 3. Dynamic AJAX Status Toggle (Approve / Reject / Toggle)
    public function ReceptionistStatus(Request $request, $id)
{
    try {
        $receptionist = Receptionist::findOrFail($id);
        
        // 1. Check user_id
        if (!$receptionist->user_id) {
            return response()->json(['message' => 'User ID is missing in receptionist table!'], 400);
        }
        
        $user = User::findOrFail($receptionist->user_id);
        $action = $request->input('action');

        // 2. Logic Update
        if ($action === 'approve') {
            $user->status = 1; 
            $message = "Receptionist Approved successfully!";
        } elseif ($action === 'reject') {
            $user->status = 0; 
            $message = "Receptionist Rejected successfully!";
        } else {
            $user->status = ($user->status == 1) ? 0 : 1;
            $message = "Status updated successfully!";
        }
        
        $user->save();
        
        // **Fix:** $newStatus ko define karein
        $newStatus = $user->status;

       // Ye base query hai jo sirf 'receptionist' table wale users ko target karegi
$query = User::role('receptionist')->whereHas('receptionist');

return response()->json([
    'status' => $newStatus,
    'message' => $message,
    'totalCount' => (clone $query)->count(),
    'activeCount' => (clone $query)->where('status', 1)->count(),
    // Ab yahan [0, 2] matlab Inactive aur Pending dono count honge
    'inactiveCount' => (clone $query)->whereIn('status', [0, 2])->count(), 
]);

    } catch (\Exception $e) {
        return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
    }
}

    // 4. Permanent Delete / Clean Up Profile
    // 1. Soft Delete 
public function ReceptionistDestroy($id)
{
    $receptionist = Receptionist::findOrFail($id);
    
    // 1. Soft delete se pehle User ka status 0 (Inactive) kar dein
    if ($receptionist->user) {
        $receptionist->user->update(['status' => 0]);
    }
    
    // 2. Ab soft delete karein (Receptionist table mein deleted_at fill ho jayega)
    $receptionist->delete(); 
    
    return redirect()->back()->with('success', 'Receptionist moved to trash!');
}

// 2. Show Trash List
public function TrashReceptionist()
{
    $trashedReceptionists = Receptionist::onlyTrashed()->get();
    return view('admin.receptionist.trash', compact('trashedReceptionists'));
}

// 3. Restore
public function RestoreReceptionist($id)
{
    $receptionist = Receptionist::withTrashed()->findOrFail($id);
    
    // 1. Record restore karein
    $receptionist->restore();

    // 2. Restore hote hi User status ko wapis Active (1) kar dein
    if ($receptionist->user) {
        $receptionist->user->update(['status' => 1]);
    }

    return redirect()->back()->with('success', 'Receptionist restored and activated!');
}

// 4. Permanent Delete
public function ForceDeleteReceptionist($id)
{
    $receptionist = Receptionist::withTrashed()->find($id);
    // Physical file delete logic yahan aayega (unlink...)
    $receptionist->forceDelete();
    return redirect()->back()->with('success', 'Receptionist permanently removed!');
}
}
