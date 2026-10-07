<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ClassTimetable;
use App\Models\StaffBankDetail;
use App\Models\StaffLeaveSetting;
use App\Models\StaffSalary;
use App\Models\Teacher;
use App\Models\User;
use App\Services\ImageService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TeachersController extends Controller
{

    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }


    // List All Teachers
    public function AllTeacher()
    {
        $teachers = Teacher::with('user')->latest()->get();

        // Counts calculate karein
        $totalCount = $teachers->count();
        $activeCount = $teachers->where('user.status', 1)->count();
        $inactiveCount = $teachers->where('user.status', 0)->count();
        $pendingCount = $teachers->where('user.status', 2)->count(); // Pending count

        return view('admin.teacher.index_teacher', compact('teachers', 'totalCount', 'activeCount', 'inactiveCount', 'pendingCount'));
    }

    // Show Create Form
    // 1. Show Registration Form
    // TeacherController.php

    public function AddTeacher()
    {
        $currentDate = Carbon::now()->format('Y-m-d');

        // Default call karein, function ab 'NEW' handle kar lega
        $teacherId = '';

        return view('admin.teacher.create_teacher', compact('currentDate', 'teacherId'));
    }

    public function getDetails($id)
    {
        $teacher = Teacher::with(['user', 'bankDetail', 'salary', 'assignments.schoolClass', 'assignments.section'])->findOrFail($id);

        // Spatie se role get karein
        $user = $teacher->user; // Teacher ke saath linked user
        $role = $user->roles->first(); // User ka pehla role uthayein

        $leaveSettings = collect(); // Default empty collection

        if ($role) {
            // Ab role ki ID mil gayi hai
            $leaveSettings = StaffLeaveSetting::where('role_id', $role->id)
                ->get()
                ->unique('leave_type');
        }

        return view('admin.teacher.details_partial', compact('teacher', 'leaveSettings'))->render();
    }


    // Function ko update karein takki woh argument handle kar sake
    private function generateTeacherId($firstName = null)
    {
        // Agar first_name khali hai, toh default 'TCH' prefix rakhein (NEW hata dein)
        $prefix = !empty($firstName) ? strtoupper(preg_replace('/[^A-Za-z]/', '', $firstName)) : 'TCH';

        $lastTeacher = Teacher::withTrashed()->latest('id')->first();

        $number = 1;
        if ($lastTeacher && !empty($lastTeacher->teacher_id)) {
            // Sirf numbers nikalen
            $lastId = $lastTeacher->teacher_id;
            $number = (int)preg_replace('/[^0-9]/', '', $lastId) + 1;
        }

        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }


    public function getGeneratedId(Request $request)
    {
        $firstName = $request->input('first_name');

        return response()->json([
            'teacher_id' => $this->generateTeacherId($firstName),
        ]);
    }



    // 4. Save Teacher Profile (Complete Transaction Process)
    public function StoreTeacher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'         => 'required|string|max:255',
            'last_name'          => 'required|string|max:255',
            'father_name'        => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email',
            'cnic'               => 'required|string|max:15|unique:teachers,cnic',
            'phone'              => 'required|string|max:15',
            'emergency_name'     => 'required|string|max:255',
            'emergency_relation' => 'nullable|string|max:100',
            'emergency_phone'    => 'required|string|max:20',
            'dob'                => 'required|date',
            'password'           => 'required|string|min:8|confirmed',
            'basic_salary'       => 'required|numeric|min:0',
            'bank_type'          => 'required|in:commercial,microfinance_wallet',
            'account_title'      => 'required|string|max:255',
            'account_number'     => 'required|string|max:255',
            'bank_name'          => 'nullable|string|max:255',
            'photo'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('toastr-error', $validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $teacherId = $this->generateTeacherId($request->first_name);

            if (is_null($teacherId)) {
                throw new Exception("Failed to generate Teacher ID.");
            }

            $username = $teacherId;

            $user = User::create([
                'name'     => $request->first_name . ' ' . $request->last_name,
                'email'    => $request->email,
                'username' => $username,
                'password' => Hash::make($request->password),
                'gender'   => $request->gender,
                'status'   => 2,
            ]);

            $user->assignRole($request->role ?? 'teacher');

            $photoName = null;
            if ($request->hasFile('photo')) {
                $photoName = $this->imageService->upload($request->file('photo'), 'teacher_images', 300, 300);
            }

            $teacher = Teacher::create([
                'user_id'            => $user->id,
                'teacher_id'         => $teacherId,
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
                'emergency_name'     => $request->emergency_name,
                'emergency_relation' => $request->emergency_relation,
                'emergency_phone'    => $request->emergency_phone,
                'address'            => $request->address,
                'permanent_address'  => $request->permanent_address,
                'qualification'      => $request->qualification,
                'work_experience'    => $request->work_experience,
                'joining_date'       => $request->joining_date ?? now()->toDateString(),
                'photo'              => $photoName,
                'contract_type'      => $request->contract_type ?? 'permanent',
                'work_shift'         => $request->work_shift,
            ]);

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

            StaffSalary::create([
                'user_id'         => $user->id,
                'basic_salary'    => $request->basic_salary,
                'allowance'       => 0,
                'deduction'       => 0,
                'advance_balance' => 0,
            ]);

            DB::commit();
            return redirect()->route('teacher.index')
                ->with('toastr-success', 'Teacher profile created successfully! ID: ' . $teacherId);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('toastr-error', 'Database Error: ' . $e->getMessage());
        }
    }
    // End Method



    // Edit View
    public function EditTeacher($id)
    {
        // Eager loading se data asani se mil jayega
        $teacher = Teacher::with(['user', 'bankDetail', 'salary'])->findOrFail($id);

        // Agar aap alag variables chahte hain to aise extract kar sakte hain:
        $bankDetail = $teacher->bankDetail;
        $salary = $teacher->salary;

        return view('admin.teacher.edit_teacher', compact('teacher', 'bankDetail', 'salary'));
    }

    // Update Data
    public function UpdateTeacher(Request $request, $id)
    {
        $teacher = Teacher::with(['user', 'bankDetail', 'salary'])->findOrFail($id);
        $user = $teacher->user;

        $validator = Validator::make($request->all(), [
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $user->id,
            'cnic'         => 'required|string|max:15|unique:teachers,cnic,' . $teacher->id,
            'phone'        => 'required|string|max:15',
            'basic_salary' => 'required|numeric|min:0',
            'password'     => 'nullable|string|min:8|confirmed',
            'photo'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('toastr-error', $validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $userData = [
                'name'   => $request->first_name . ' ' . $request->last_name,
                'email'  => $request->email,
                'gender' => $request->gender,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            $photoName = $teacher->photo;
            if ($request->hasFile('photo')) {
                if ($teacher->photo) {
                    $this->imageService->delete($teacher->photo, 'teacher_images');
                }
                $photoName = $this->imageService->upload($request->file('photo'), 'teacher_images', 300, 300);
            }

            $teacher->update([
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

            $teacher->bankDetail()->updateOrCreate(
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

            $teacher->salary()->updateOrCreate(
                ['user_id' => $user->id],
                ['basic_salary' => $request->basic_salary]
            );

            DB::commit();
            return redirect()->route('teacher.index')->with('toastr-success', 'Teacher updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('toastr-error', 'Error: ' . $e->getMessage());
        }
    }
    // End Method

    // Status Toggle
    public function TeacherStatus(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);
        $user = $teacher->user;

        // 1. Action handle karein (Approve/Reject/Toggle)
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

        // 2. Counts Calculate Karein (Teacher relation ke saath)
        $totalCount = User::whereHas('teacher')->count();
        $activeCount = User::whereHas('teacher')->where('status', 1)->count();

        // Inactive + Pending (0 + 2) ka combined count
        $inactivePendingCount = User::whereHas('teacher')->whereIn('status', [0, 2])->count();

        // 3. Updated JSON response
        return response()->json([
            'status' => (int)$user->status,
            'message' => 'Status updated successfully!',
            'totalCount' => $totalCount,
            'activeCount' => $activeCount,
            'inactiveCount' => $inactivePendingCount, // Yahan combined count bhej rahe hain
        ]);
    }

    // Soft Delete
    // 1. Trash List (Jo delete ho chuke hain)
    public function TeacherTrash()
    {
        $trashedTeachers = Teacher::onlyTrashed()->latest()->get();
        return view('admin.teacher.trash', compact('trashedTeachers'));
    }

        // 2. Soft Delete (assigned teacher block)
public function TeacherDestroy($id)
{
    $teacher = Teacher::findOrFail($id);

    $isAssigned = $teacher->assignments()->exists()
        || ClassTimetable::where('teacher_id', $teacher->id)->exists();

    if ($isAssigned) {
        return redirect()->back()
            ->with('error', 'This teacher is assigned as a Class Teacher or has timetable periods. Please remove the assignment first.');
    }

    $teacher->delete();

    return redirect()->back()->with('success', 'Teacher moved to trash successfully.');
}
// End Method

    // 3. Restore (Trash se wapis lane ke liye)
    public function TeacherRestore($id)
    {
        Teacher::withTrashed()->findOrFail($id)->restore();
        return redirect()->back()->with('success', 'Teacher restored successfully.');
    }

    // 4. Force Delete (Database se hamesha ke liye khatam)
    public function TeacherForceDelete($id)
    {
        try {
            // 1. Teacher dhoondein (Trash mein se)
            $teacher = Teacher::onlyTrashed()->findOrFail($id);

            // 2. Linked User dhoondein aur delete karein
            if ($teacher->user_id) {
                $user = \App\Models\User::find($teacher->user_id);
                if ($user) {
                    // Check karein ke kya User model mein SoftDeletes trait hai
                    if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($user))) {
                        $user->forceDelete(); // Agar soft delete hai, to forceDelete karein
                    } else {
                        $user->delete(); // Agar simple delete hai, to delete karein
                    }
                }
            }

            // 3. File cleanup (Teacher photo)
            if ($teacher->photo) {
                $this->imageService->delete($teacher->photo, 'teacher_images');
            }

            // 4. Teacher permanently delete karein
            $teacher->forceDelete();

            return redirect()->back()->with('success', 'Teacher and associated User permanently deleted.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
