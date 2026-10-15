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

    // ID server par banti hai (store mein bhi yahi), client ki value par bharosa nahi
    private function generateAccountantId($firstName = null, $lastName = null)
    {
        // Naam ko upper case mein convert karein aur spaces/symbols khatam kar dein
        // Agar first_name khali ho to default 'ACC' prefix (teacher/receptionist jaisa)
        $first = !empty($firstName) ? strtoupper(preg_replace('/[^A-Za-z]/', '', $firstName)) : 'ACC';

        // Max existing number nikalen (withTrashed) takki delete ke baad bhi ID duplicate na ho
        $maxNumber = Accountant::withTrashed()
            ->get(['accountant_id'])
            ->map(function ($a) {
                return preg_match('/(\d+)$/', (string) $a->accountant_id, $m) ? (int) $m[1] : 0;
            })
            ->max();

        $uniqueNumber = str_pad($maxNumber + 1, 3, '0', STR_PAD_LEFT);

        // Final Format: FIRSTNAME + 001 (receptionist/teacher jaisa)
        // Misal: KHAWAR + 004 = KHAWAR004
        return $first . $uniqueNumber;
    }

    // 3. Generate ID (AJAX Request) - sirf preview, save par dobara server banata hai
    public function getGeneratedId(Request $request)
    {
        return response()->json([
            'accountant_id' => $this->generateAccountantId($request->first_name, $request->last_name),
        ]);
    }

    // 4. Store Accountant
    public function StoreAccountant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'father_name'    => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'cnic'           => 'required|string|max:15|unique:accountants,cnic', // Table name check karein
            'phone'          => 'required|string|max:15',
            'emergency_name'     => 'required|string|max:255',
            'emergency_relation' => 'nullable|string|max:100',
            'emergency_phone'    => 'required|string|max:20',
            'dob'            => 'required|date',
            'password'       => 'required|string|min:8|confirmed',
            'basic_salary'   => 'required|numeric|min:0',
            'bank_type'      => 'required|in:commercial,microfinance_wallet',
            'account_title'  => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'bank_name'      => 'nullable|string|max:255',
            'photo'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            // AJAX form ko JSON errors chahiye (warna 302 follow hoke "added successfully" dikhta tha)
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput()->with('toastr-error', 'Validation failed.');
        }

        // 1. Photo Handle karein (Transaction se bahar)
        $photoName = null;
        if ($request->hasFile('photo')) {
            $photoName = $this->imageService->upload($request->file('photo'), 'uploads/accountant_images', 300, 300);
        }

        DB::beginTransaction();
        try {
            // ID server se, request ki accountant_id ignore
            $accountantId = $this->generateAccountantId($request->first_name, $request->last_name);

            // 2. Create User
            $user = User::create([
                'name'     => $request->first_name . ' ' . $request->last_name,
                'email'    => $request->email,
                'username' => $accountantId, // Generated ID
                'password' => Hash::make($request->password),
                'status'   => 2,
            ]);

            $user->assignRole('accountant');

            // 3. Save Accountant Profile
            Accountant::create([
                'user_id'           => $user->id,
                'accountant_id'     => $accountantId,
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
                'iban'           => $request->bank_type == 'commercial' ? $request->iban : null,
                'branch_name'    => $request->bank_type == 'commercial' ? $request->branch_name : null,
                'branch_code'    => $request->bank_type == 'commercial' ? $request->branch_code : null,
            ]);

            DB::commit();
            return redirect()->route('accountant.index')->with('toastr-success', 'Accountant added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // Agar error aaye aur photo upload ho gayi ho to usse delete krne ke liye logic yahan likh sakte hain
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
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
            'father_name'    => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'cnic'           => 'required|string|unique:accountants,cnic,' . $accountant->id,
            'phone'              => 'required|string|max:15',
            'basic_salary'   => 'required|numeric',
            'password'       => 'nullable|string|min:8|confirmed', // Khali = purana password
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

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]); // Admin reset, sirf isi staff user ka
            }

            // B. Handle Photo
            $photoName = $accountant->photo;
            if ($request->hasFile('photo')) {
                // Purani photo delete karein agar exist karti ho
                if ($photoName && file_exists(public_path('uploads/accountant_images/' . $photoName))) {
                    unlink(public_path('uploads/accountant_images/' . $photoName));
                }
                // Purani photo storage disk se bhi delete (store ImageService se wahin save karta hai)
                if ($photoName) {
                    $this->imageService->delete($photoName, 'uploads/accountant_images');
                }
                $photoName = $this->imageService->upload($request->file('photo'), 'uploads/accountant_images', 300, 300);
            }

            // C. Update Accountant Profile
            $accountant->update([
                'first_name'         => $request->first_name,
                'last_name'          => $request->last_name,
                'father_name'        => $request->father_name,
                'mother_name'        => $request->mother_name,
                'email'              => $request->email, // index list accountants.email se padhti hai, sirf users.email update hoti thi
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
                    'iban'           => $request->bank_type == 'commercial' ? $request->iban : null,
                    'branch_name'    => $request->bank_type == 'commercial' ? $request->branch_name : null,
                    'branch_code'    => $request->bank_type == 'commercial' ? $request->branch_code : null,
                ]
            );

            DB::commit();
            // AJAX ko JSON chahiye (redirect follow hoke fake success dikhata tha)
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Accountant updated successfully!']);
            }
            return redirect()->route('accountant.index')->with('toastr-success', 'Accountant updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('UpdateAccountant failed: ' . $e->getMessage(), ['accountant_id' => $id]); // XHR redirect flash error chhupa deta tha
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()], 500);
            }
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

        // Redirect index par jata hai, index sirf 'toastr-success' / 'toastr-error' render karta hai
        return redirect()->back()->with('toastr-success', 'Accountant moved to trash.');
    }

    // 4. Restore
    public function AccountantRestore($id)
    {
        Accountant::withTrashed()->findOrFail($id)->restore();
        // Trash page koi flash key render nahi karta; admin layout 'message' + 'alert-type' render karta hai
        return redirect()->back()->with('message', 'Accountant restored successfully.')->with('alert-type', 'success');
    }

    // 5. Force Delete (Permanent)
    public function AccountantForceDelete($id)
    {
        try {
            // 1. Accountant dhoondein (Trash mein se)
            $accountant = Accountant::onlyTrashed()->findOrFail($id);

            // History guard: user delete se salary slips / attendance / leaves cascade ho jati hain, isliye history ho to delete na karein
            if ($accountant->user_id) {
                $hasHistory = \App\Models\SalarySlip::where('user_id', $accountant->user_id)->exists()
                    || \App\Models\StaffAttendance::where('user_id', $accountant->user_id)->exists()
                    || \App\Models\StaffLeaveApplication::where('user_id', $accountant->user_id)->exists()
                    || DB::table('print_report_cards')->where('issued_by', $accountant->user_id)->exists();
                if ($hasHistory) {
                    return redirect()->back()->with('message', 'Cannot delete permanently: this accountant has salary, attendance, leave or report card history. Keep the record in trash.')->with('alert-type', 'error');
                }
            }

            // 2 + 4. Linked User aur Accountant ek transaction mein delete karein (FK error par user delete bhi rollback ho)
            DB::transaction(function () use ($accountant) {
                // Linked User dhoondein aur delete karein
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

                // Accountant permanently delete karein
                $accountant->forceDelete();
            });

            // 3. File cleanup, sirf transaction success hone ke baad
            if ($accountant->photo && File::exists(public_path('uploads/accountant_images/' . $accountant->photo))) {
                File::delete(public_path('uploads/accountant_images/' . $accountant->photo));
            }
            // Storage disk (ImageService) wali photo bhi delete
            if ($accountant->photo) {
                $this->imageService->delete($accountant->photo, 'uploads/accountant_images');
            }

            return redirect()->back()->with('message', 'Accountant and User permanently deleted successfully.')->with('alert-type', 'success');
        } catch (\Exception $e) {
            \Log::error('AccountantForceDelete failed: ' . $e->getMessage(), ['accountant_id' => $id]);
            return redirect()->back()->with('message', 'Something went wrong: ' . $e->getMessage())->with('alert-type', 'error');
        }
    }
}
