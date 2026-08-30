<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Accountant;
use App\Models\StaffBankDetail;
use App\Models\StaffLeaveSetting;
use App\Models\StaffSalary;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class AccountantController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }


    // 1. All Accountant List
    public function AllAccountant()
    {
        // Agar aapko salary ya bank details bhi sath lane hon, to ye use karein:
        // $accountants = Accountant::with(['user', 'salary', 'bankDetails'])->latest()->get();

        $accountants = Accountant::with(['user'])->latest()->get();
        return view('admin.accountant.index', compact('accountants'));
    }

    // 2. Add Accountant Form
    public function AddAccountant()
    {
        $currentDate = date('Y-m-d');
        $accountantId = ''; // ID ab AJAX se aayegi, isliye yahan khali rakhein
        return view('admin.accountant.create_acc', compact('currentDate', 'accountantId'));
    }

    // 3. Generate ID (AJAX Request)
    public function getGeneratedId(Request $request)
    {
        // Naam ko upper case mein convert karein aur spaces khatam kar dein
        $firstName = str_replace(' ', '', strtoupper($request->first_name));
        $lastName = str_replace(' ', '', strtoupper($request->last_name));

        // Count se unique ID number (3 digits ka)
        $count = Accountant::count() + 1;
        $uniqueNumber = str_pad($count, 3, '0', STR_PAD_LEFT);

        // Final Format: ACC + FIRSTNAME + LASTNAME + 001
        // Misal: ACC + MUHAMMAD + SIDDIQUE + 001 = ACCMUHAMMADSIDDIQUE001
        $id = 'ACC' . $firstName . $lastName . $uniqueNumber;

        return response()->json(['accountant_id' => $id]);
    }

    // 4. Store Accountant
    public function StoreAccountant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'cnic'           => 'required|string|max:15|unique:accountants,cnic', // Table name check karein
            'phone'          => 'required|string|max:15',
            'emergency_name'     => 'required|string|max:255',
            'emergency_relation' => 'nullable|string|max:100',
            'emergency_phone'    => 'required|string|max:20',
            'dob'            => 'required|date',
            'basic_salary'   => 'required|numeric|min:0',
            'bank_type'      => 'required|in:commercial,microfinance_wallet',
            'account_title'  => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'bank_name'      => 'nullable|string|max:255',
            'photo'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('toastr-error', 'Validation failed.');
        }

        // 1. Photo Handle karein (Transaction se bahar)
        $photoName = null;
        if ($request->hasFile('photo')) {
            $photoName = $this->imageService->upload($request->file('photo'), 'uploads/accountant_images', 300, 300);
        }

        DB::beginTransaction();
        try {
            // 2. Create User
            $user = User::create([
                'name'     => $request->first_name . ' ' . $request->last_name,
                'email'    => $request->email,
                'username' => $request->accountant_id, // Generated ID
                'password' => Hash::make('accountant123'),
                'role'     => 'accountant',
                'status'   => 2,
            ]);

            $user->assignRole('accountant');

            // 3. Save Accountant Profile
            Accountant::create([
                'user_id'           => $user->id,
                'accountant_id'     => $request->accountant_id,
                'first_name'        => $request->first_name,
                'last_name'         => $request->last_name,
                'father_name'       => $request->father_name,
                'mother_name'       => $request->mother_name,
                'email'             => $request->email,
                'cnic'              => $request->cnic,
                'gender'            => $request->gender,
                'dob'               => $request->dob,
                'marital_status'    => $request->marital_status,
                'phone'             => $request->phone,
                'address'           => $request->address,
                'permanent_address' => $request->permanent_address,
                'emergency_name'     => $request->emergency_name,
                'emergency_relation' => $request->emergency_relation,
                'emergency_phone'    => $request->emergency_phone,
                'qualification'     => $request->qualification,
                'work_experience'   => $request->work_experience,
                'joining_date'      => $request->joining_date ?? now()->toDateString(),
                'photo'             => $photoName,
                'contract_type'     => $request->contract_type ?? 'permanent',
                'work_shift'        => $request->work_shift,
            ]);

            // 4. Save Salary
            StaffSalary::create([
                'user_id'         => $user->id,
                'basic_salary'    => $request->basic_salary,
            ]);

            // 5. Save Bank
            StaffBankDetail::create([
                'user_id'        => $user->id,
                'bank_type'      => $request->bank_type,
                'bank_name'      => $request->bank_name,
                'account_title'  => $request->account_title,
                'account_number' => $request->account_number,
            ]);

            DB::commit();
            return redirect()->route('accountant.index')->with('toastr-success', 'Accountant added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // Agar error aaye aur photo upload ho gayi ho to usse delete krne ke liye logic yahan likh sakte hain
            return back()->with('toastr-error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    // 5. Get Details (For Profile or View)
    public function getDetails($id)
    {
        // 'bankDetail' (singular) use karein jaisa humne pehle fix kiya
        $accountant = Accountant::with(['user', 'salary', 'bankDetail'])->findOrFail($id);

        // Spatie se role get karein
        $user = $accountant->user;
        $leaveSettings = collect();

        if ($user && $user->roles->isNotEmpty()) {
            $role = $user->roles->first();

            // Role ki ID se leave settings fetch karein
            $leaveSettings = StaffLeaveSetting::where('role_id', $role->id)
                ->get()
                ->unique('leave_type');
        }

        return view('admin.accountant.details_partial', compact('accountant', 'leaveSettings'))->render();
    }

    /**
     * 1. Edit Accountant Page
     */
    public function EditAccountant($id)
    {
        // Eager loading use karte hue Accountant, User, Salary, aur BankDetail fetch karein
        $accountant = Accountant::with(['user', 'bankDetail', 'salary'])->findOrFail($id);
        // dd($accountant);
        // Relationships se data extract karein (agar data exist na kare toh null milega)
        $bankDetail = $accountant->bankDetail;
        $salary = $accountant->salary;

        // View file ka path check kar lein, maine 'edit' rakha hai
        return view('admin.accountant.edit_acc', compact('accountant', 'bankDetail', 'salary'));
    }

    /**
     * 2. Update Accountant Profile
     */
    public function UpdateAccountant(Request $request, $id)
    {
        $accountant = Accountant::findOrFail($id);
        $user = User::findOrFail($accountant->user_id);

        // Validation
        $request->validate([
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'cnic'           => 'required|string|unique:accountants,cnic,' . $accountant->id,
            'phone'              => 'required|string|max:15',
            'basic_salary'   => 'required|numeric',
            'photo'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // A. Update User
            $user->update([
                'name'  => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'gender' => $request->gender,

            ]);

            // B. Handle Photo
            $photoName = $accountant->photo;
            if ($request->hasFile('photo')) {
                // Purani photo delete karein agar exist karti ho
                if ($photoName && file_exists(public_path('uploads/accountant_images/' . $photoName))) {
                    unlink(public_path('uploads/accountant_images/' . $photoName));
                }
                $photoName = $this->imageService->upload($request->file('photo'), 'uploads/accountant_images', 300, 300);
            }

            // C. Update Accountant Profile
            $accountant->update([
                'first_name'         => $request->first_name,
                'last_name'          => $request->last_name,
                'father_name'        => $request->father_name,
                'mother_name'        => $request->mother_name,
                'cnic'               => $request->cnic,
                'dob'                => $request->dob,
                'marital_status'     => $request->marital_status,
                'phone'              => $request->phone,
                'emergency_name'     => $request->emergency_name,
                'emergency_relation' => $request->emergency_relation,
                'emergency_phone'    => $request->emergency_phone,
                'address'            => $request->address,
                'permanent_address'  => $request->permanent_address,
                'qualification'      => $request->qualification,
                'work_experience'    => $request->work_experience,
                'joining_date'       => $request->joining_date,
                'photo'              => $photoName,
                'contract_type'      => $request->contract_type,
                'work_shift'         => $request->work_shift,
                'fb_url'             => $request->fb_url,
                'twitter_url'        => $request->twitter_url,
                'linkedin_url'       => $request->linkedin_url,
                'insta_url'          => $request->insta_url,
            ]);

            // D. Update Salary
            StaffSalary::updateOrCreate(
                ['user_id' => $user->id],
                ['basic_salary' => $request->basic_salary]
            );

            // E. Update Bank Details
            StaffBankDetail::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'bank_type'      => $request->bank_type,
                    'bank_name'      => $request->bank_name,
                    'account_title'  => $request->account_title,
                    'account_number' => $request->account_number,
                ]
            );

            DB::commit();
            return redirect()->route('accountant.index')->with('toastr-success', 'Accountant updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('toastr-error', 'Update failed: ' . $e->getMessage());
        }
    }

    // 1. Status Toggle (AJAX)
    public function AccountantStatus(Request $request, $id)
    {
        // 1. Accountant aur usse connected User dhoondein
        $accountant = Accountant::findOrFail($id);
        $user = $accountant->user;

        // 2. Action handle karein (Approve/Reject/Toggle)
        if ($request->has('action') && $request->action != 'toggle') {
            if ($request->action == 'approve') {
                $user->status = 1;
            } elseif ($request->action == 'reject') {
                $user->status = 0;
            }
        } else {
            // Normal Toggle Logic
            if ($user->status == 2) {
                $user->status = 1; // Pending to Active
            } elseif ($user->status == 1) {
                $user->status = 0; // Active to Inactive
            } else {
                $user->status = 1; // Inactive (0) to Active (1)
            }
        }

        $user->save();

        // 3. Counts Calculate Karein (Accountant relation ke saath)
        // Yahan ensure karein ke 'accountant' relation aapke User model mein define ho
        $totalCount = User::whereHas('accountant')->count();
        $activeCount = User::whereHas('accountant')->where('status', 1)->count();

        // Inactive + Pending (0 + 2) ka combined count
        $inactivePendingCount = User::whereHas('accountant')->whereIn('status', [0, 2])->count();

        // 4. Updated JSON response
        return response()->json([
            'status' => (int)$user->status,
            'message' => 'Status updated successfully!',
            'totalCount' => $totalCount,
            'activeCount' => $activeCount,
            'inactiveCount' => $inactivePendingCount,
        ]);
    }

    // 2. Trash List View
    public function AccountantTrash()
    {
        $trashedAccountants = Accountant::onlyTrashed()->latest()->get();
        // dd($trashedAccountants);
        return view('admin.accountant.trash', compact('trashedAccountants'));
    }

    // 3. Soft Delete
    public function AccountantDestroy($id)
    {
        Accountant::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Accountant moved to trash.');
    }

    // 4. Restore
    public function AccountantRestore($id)
    {
        Accountant::withTrashed()->findOrFail($id)->restore();
        return redirect()->back()->with('success', 'Accountant restored successfully.');
    }

    // 5. Force Delete (Permanent)
    public function AccountantForceDelete($id)
    {
        try {
            // 1. Accountant dhoondein (Trash mein se)
            $accountant = Accountant::onlyTrashed()->findOrFail($id);

            // 2. Linked User dhoondein aur delete karein
            if ($accountant->user_id) {
                $user = \App\Models\User::find($accountant->user_id);
                if ($user) {
                    // Check karein ke kya User model mein SoftDeletes trait hai
                    if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($user))) {
                        $user->forceDelete(); // Agar soft delete hai, to forceDelete karein
                    } else {
                        $user->delete(); // Agar simple delete hai, to delete karein
                    }
                }
            }

            // 3. File cleanup
            if ($accountant->photo && File::exists(public_path('uploads/accountant_images/' . $accountant->photo))) {
                File::delete(public_path('uploads/accountant_images/' . $accountant->photo));
            }

            // 4. Accountant permanently delete karein
            $accountant->forceDelete();

            return redirect()->back()->with('success', 'Accountant and User permanently deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
