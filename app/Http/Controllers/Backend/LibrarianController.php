<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLibrarianRequest;
use App\Http\Requests\UpdateLibrarianRequest;
use App\Models\Librarian;
use App\Models\SalarySlip;
use App\Models\StaffAttendance;
use App\Models\StaffBankDetail;
use App\Models\StaffLeaveApplication;
use App\Models\StaffLeaveSetting;
use App\Models\StaffSalary;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Admin side Librarian CRUD (portal/profile LibrariansController mein hai, Accountant/Accountants jaisa pair)
class LibrarianController extends Controller
{
    protected $imageService;

    // Photo folder (storage disk) — Librarian::photo_url bhi yahi padhta hai
    private const PHOTO_FOLDER = 'uploads/librarian_images';

    // Librarian profile ke columns jo form se aate hain (librarian_id / user_id / photo server set karta hai)
    private const PROFILE_FIELDS = [
        'first_name', 'last_name', 'father_name', 'mother_name', 'email', 'cnic', 'gender', 'dob',
        'marital_status', 'phone', 'emergency_name', 'emergency_relation', 'emergency_phone',
        'address', 'permanent_address', 'qualification', 'work_experience', 'joining_date',
        'contract_type', 'work_shift',
    ];

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }


    // List All Librarians
    public function AllLibrarian()
    {
        $librarians = Librarian::with('user')->latest()->get();

        return view('admin.librarians.index', compact('librarians'));
    }
    // End Method


    // Show Create Form (ID AJAX preview se aati hai, save par server dobara banata hai)
    public function AddLibrarian()
    {
        $currentDate = now()->format('Y-m-d');
        $librarianId = '';

        return view('admin.librarians.create', compact('currentDate', 'librarianId'));
    }
    // End Method


    // ID server par banti hai: FIRSTNAME + 3-digit counter (withTrashed max + 1)
    // users.username sab staff mein unique hai, isliye kisi bhi user ka username ho to counter aage
    private function generateLibrarianId($firstName = null)
    {
        $prefix = !empty($firstName) ? strtoupper(preg_replace('/[^A-Za-z]/', '', $firstName)) : '';
        if ($prefix === '') {
            $prefix = 'LIB';
        }

        $maxNumber = (int) Librarian::withTrashed()
            ->get(['librarian_id'])
            ->map(function ($l) {
                return preg_match('/(\d+)$/', (string) $l->librarian_id, $m) ? (int) $m[1] : 0;
            })
            ->max();

        $number = $maxNumber + 1;
        $librarianId = $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);

        // Teacher/accountant/receptionist ka same username ho to clash se bachne ke liye next number
        while (User::withTrashed()->where('username', $librarianId)->exists()) {
            $number++;
            $librarianId = $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
        }

        return $librarianId;
    }
    // End Method


    // AJAX ID preview (sirf dikhane ke liye)
    public function getGeneratedId(Request $request)
    {
        $request->validate(['first_name' => 'nullable|string|max:255']);

        return response()->json([
            'librarian_id' => $this->generateLibrarianId($request->input('first_name')),
        ]);
    }
    // End Method


    // Details modal (index page)
    public function getDetails($id)
    {
        $librarian = Librarian::with(['user.roles', 'bankDetail', 'salary'])->findOrFail($id);

        $leaveSettings = collect();
        $role = $librarian->user ? $librarian->user->roles->first() : null;

        if ($role) {
            $leaveSettings = StaffLeaveSetting::where('role_id', $role->id)
                ->get()
                ->unique('leave_type');
        }

        return view('admin.librarians.details_partial', compact('librarian', 'leaveSettings'))->render();
    }
    // End Method


    // Save Librarian (users + librarians + bank + salary ek transaction mein)
    public function StoreLibrarian(StoreLibrarianRequest $request)
    {
        $data = $request->validated();

        // Photo transaction se pehle; fail ho to neeche delete
        $photoName = null;
        if ($request->hasFile('photo')) {
            $photoName = $this->imageService->upload($request->file('photo'), self::PHOTO_FOLDER, 300, 300);
        }

        try {
            $librarianId = DB::transaction(function () use ($request, $data, $photoName) {
                // ID server se, request ki librarian_id ignore
                $librarianId = $this->generateLibrarianId($data['first_name']);

                $user = User::create([
                    'name'     => $data['first_name'] . ' ' . $data['last_name'],
                    'email'    => $data['email'],
                    'username' => $librarianId,
                    'password' => Hash::make($data['password']),
                    'phone'    => $data['phone'],
                    'gender'   => $data['gender'],
                    'status'   => 2, // StoreTeacher jaisa: Pending, admin Active karega
                ]);

                // Role hamesha server se, request ka role ignore
                $user->assignRole('librarian');

                $profile = $request->only(self::PROFILE_FIELDS);
                $profile['user_id']       = $user->id;
                $profile['librarian_id']  = $librarianId;
                $profile['photo']         = $photoName;
                $profile['joining_date']  = $data['joining_date'] ?? now()->toDateString();
                $profile['contract_type'] = $data['contract_type'] ?? 'permanent';
                Librarian::create($profile);

                StaffBankDetail::create([
                    'user_id'        => $user->id,
                    'bank_type'      => $data['bank_type'],
                    'bank_name'      => $data['bank_name'] ?? null,
                    'account_title'  => $data['account_title'],
                    'account_number' => $data['account_number'],
                    'iban'           => $data['bank_type'] == 'commercial' ? ($data['iban'] ?? null) : null,
                    'branch_name'    => $data['bank_type'] == 'commercial' ? ($data['branch_name'] ?? null) : null,
                    'branch_code'    => $data['bank_type'] == 'commercial' ? ($data['branch_code'] ?? null) : null,
                ]);

                StaffSalary::create([
                    'user_id'         => $user->id,
                    'basic_salary'    => $data['basic_salary'],
                    'allowance'       => 0,
                    'deduction'       => 0,
                    'advance_balance' => 0,
                ]);

                return $librarianId;
            });
        } catch (\Exception $e) {
            // Transaction rollback ho chuka; upload hui photo bhi hata dein
            if ($photoName) {
                $this->imageService->delete($photoName, self::PHOTO_FOLDER);
            }
            \Log::error('StoreLibrarian failed: ' . $e->getMessage());
            return back()->withInput()->with('toastr-error', 'Database Error: ' . $e->getMessage());
        }

        return redirect()->route('librarians.index')
            ->with('toastr-success', 'Librarian profile created successfully! ID: ' . $librarianId);
    }
    // End Method


    // Edit View
    public function EditLibrarian($id)
    {
        $librarian = Librarian::with(['user', 'bankDetail', 'salary'])->findOrFail($id);

        return view('admin.librarians.edit', compact('librarian'));
    }
    // End Method


    // Update (AJAX JSON, accountant jaisa). librarian_id / username kabhi change nahi hote
    public function UpdateLibrarian(UpdateLibrarianRequest $request, $id)
    {
        $librarian = Librarian::with('user')->findOrFail($id);
        $user = $librarian->user;
        $data = $request->validated();

        $oldPhoto = $librarian->photo;
        $newPhoto = null;
        if ($request->hasFile('photo')) {
            $newPhoto = $this->imageService->upload($request->file('photo'), self::PHOTO_FOLDER, 300, 300);
        }

        try {
            DB::transaction(function () use ($request, $data, $librarian, $user, $newPhoto) {
                $userData = [
                    'name'   => $data['first_name'] . ' ' . $data['last_name'],
                    'email'  => $data['email'],
                    'phone'  => $data['phone'],
                    'gender' => $data['gender'],
                ];
                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($data['password']);
                }
                $user->update($userData);

                // Email dono tables mein (index librarians.email padhta hai, login users.email)
                $profile = $request->only(self::PROFILE_FIELDS);
                if ($newPhoto) {
                    $profile['photo'] = $newPhoto;
                }
                $librarian->update($profile);

                StaffBankDetail::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'bank_type'      => $data['bank_type'],
                        'bank_name'      => $data['bank_name'] ?? null,
                        'account_title'  => $data['account_title'],
                        'account_number' => $data['account_number'],
                        'iban'           => $data['bank_type'] == 'commercial' ? ($data['iban'] ?? null) : null,
                        'branch_name'    => $data['bank_type'] == 'commercial' ? ($data['branch_name'] ?? null) : null,
                        'branch_code'    => $data['bank_type'] == 'commercial' ? ($data['branch_code'] ?? null) : null,
                    ]
                );

                StaffSalary::updateOrCreate(
                    ['user_id' => $user->id],
                    ['basic_salary' => $data['basic_salary']]
                );
            });
        } catch (\Exception $e) {
            if ($newPhoto) {
                $this->imageService->delete($newPhoto, self::PHOTO_FOLDER);
            }
            \Log::error('UpdateLibrarian failed: ' . $e->getMessage(), ['librarian_id' => $id]);
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('toastr-error', 'Update failed: ' . $e->getMessage());
        }

        // Purani photo sirf save success ke baad delete
        if ($newPhoto && $oldPhoto) {
            $this->imageService->delete($oldPhoto, self::PHOTO_FOLDER);
        }

        if ($request->ajax()) {
            // JS index par redirect karta hai, wahan yahi flash toastr banta hai
            session()->flash('toastr-success', 'Librarian updated successfully!');
            return response()->json(['success' => true, 'message' => 'Librarian updated successfully!']);
        }
        return redirect()->route('librarians.index')->with('toastr-success', 'Librarian updated successfully!');
    }
    // End Method


    // Status Toggle (AJAX, teacher jaisa)
    public function LibrarianStatus(Request $request, $id)
    {
        $request->validate(['action' => 'nullable|in:toggle,approve,reject']);

        $librarian = Librarian::with('user')->findOrFail($id);
        $user = $librarian->user;

        if ($request->filled('action') && $request->action != 'toggle') {
            $user->status = $request->action == 'approve' ? 1 : 0;
        } else {
            // Pending/Inactive -> Active, Active -> Inactive
            $user->status = $user->status == 1 ? 0 : 1;
        }
        $user->save();

        return response()->json([
            'status'        => (int) $user->status,
            'message'       => 'Status updated successfully!',
            'totalCount'    => User::whereHas('librarian')->count(),
            'activeCount'   => User::whereHas('librarian')->where('status', 1)->count(),
            'inactiveCount' => User::whereHas('librarian')->whereIn('status', [0, 2])->count(), // Inactive + Pending
        ]);
    }
    // End Method


    // Trash List
    public function LibrarianTrash()
    {
        $trashedLibrarians = Librarian::onlyTrashed()->latest('deleted_at')->get();

        return view('admin.librarians.trash', compact('trashedLibrarians'));
    }
    // End Method


    // Soft Delete (index par wapis, index 'toastr-success' render karta hai)
    public function LibrarianDestroy($id)
    {
        Librarian::findOrFail($id)->delete();

        return redirect()->route('librarians.index')->with('toastr-success', 'Librarian moved to trash.');
    }
    // End Method


    // Restore (trash page koi flash key render nahi karta; admin layout 'message' + 'alert-type')
    public function LibrarianRestore($id)
    {
        Librarian::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('librarians.trash')->with('message', 'Librarian restored successfully.')->with('alert-type', 'success');
    }
    // End Method


    // Force Delete: history ho to refuse, warna user + profile ek transaction mein
    public function LibrarianForceDelete($id)
    {
        $librarian = Librarian::onlyTrashed()->findOrFail($id);
        $userId = $librarian->user_id;

        // History guard: users delete se salary slips / attendance / leaves cascade ho jati hain,
        // book issues / library members ka issued_by / added_by NULL ho jata hai
        $hasHistory = SalarySlip::where('user_id', $userId)->exists()
            || StaffAttendance::where('user_id', $userId)->exists()
            || StaffLeaveApplication::where('user_id', $userId)->exists()
            || DB::table('print_report_cards')->where('issued_by', $userId)->exists()
            || DB::table('book_issues')->where('issued_by', $userId)->orWhere('returned_by', $userId)->exists()
            || DB::table('library_members')->where('added_by', $userId)->exists();

        if ($hasHistory) {
            return redirect()->route('librarians.trash')->with('message', 'Cannot delete permanently: this librarian has salary, attendance, leave, report card or library history. Keep the record in trash.')->with('alert-type', 'error');
        }

        try {
            DB::transaction(function () use ($librarian, $userId) {
                $user = User::withTrashed()->find($userId);
                if ($user) {
                    $user->forceDelete();
                }
                $librarian->forceDelete();
            });
        } catch (\Exception $e) {
            \Log::error('LibrarianForceDelete failed: ' . $e->getMessage(), ['librarian_id' => $id]);
            return redirect()->route('librarians.trash')->with('message', 'Something went wrong: ' . $e->getMessage())->with('alert-type', 'error');
        }

        // Photo cleanup sirf transaction success ke baad
        if ($librarian->photo) {
            $this->imageService->delete($librarian->photo, self::PHOTO_FOLDER);
        }

        return redirect()->route('librarians.trash')->with('message', 'Librarian and associated User permanently deleted.')->with('alert-type', 'success');
    }
    // End Method
}
