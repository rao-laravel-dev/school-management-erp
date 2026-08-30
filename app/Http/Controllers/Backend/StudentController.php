<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Models\AcademicYear;
use App\Models\DiscountPolicy;
use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\Group;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentDiscount;
use App\Models\StudentFees;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DiscountService;
use App\Services\ImageService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    /**
     * Variable inject type declaration to clear blue warning matrix.
     * @var ImageService
     */
    protected $imageService;

    /**
     * 1. Constructor: Unified ImageService Dependency Injection
     */
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * 2. INDEX: Display Datatable / List of All Enrolled Students
     */
    public function AllStudent()
    {
        // 1. Dashboard Counters (Direct database se live aur sahi count)
        $totalCount = User::whereHas('studentProfile')->count();

        // Active Count (Sirf status 1)
        $activeCount = User::where('status', 1)->whereHas('studentProfile')->count();

        // Inactive Count (Status 0 aur Pending Status 2 ko plus kar diya taake dono isi card me dikhein)
        $inactiveCount = User::whereIn('status', [0, 2])->whereHas('studentProfile')->count();

        // 2. Aapka purana Enrollment data fetch karne ka logic
        $enrollments = Enrollment::with([
            'student.user',
            'student.parent.user',
            'schoolClass',
            'section',
            'group'
        ])
            ->latest()
            ->get();

        // 3. Dropdowns ke liye Classes aur Groups
        $classes = SchoolClass::where('status', 1)->get();
        $groups = Group::where('status', 1)->get();

        // 4. Variables ko view mein bhej diya
        return view('admin.students.index_student', compact(
            'enrollments',
            'classes',
            'groups',
            'totalCount',
            'activeCount',
            'inactiveCount'
        ));
    }
// End Method

    /**
     * 3. CREATE VIEW: Render Form Payload Components
     */
    public function AddStudent(Request $request)
    {
        $classes = SchoolClass::where('status', 1)
            ->orderBy('numeric_name', 'ASC')
            ->get();

        $selected_class = $request->get('class_id') ?? old('class_id');
        $sections = collect();

        if ($selected_class) {
            $sections = Section::whereHas('schoolClasses', function ($q) use ($selected_class) {
                $q->where('school_class_id', $selected_class)
                    ->where('school_class_section.status', 1);
            })
                ->where('status', 1)
                ->orderBy('name', 'ASC')
                ->get();
        }

        $groups = Group::where('status', 1)->orderBy('name', 'ASC')->get();

        // 👇 fix: 'category' column ab exist nahi karta, 'policy_type' se order karo
        $discountPolicies = DiscountPolicy::with('category')->where('status', 1)->orderBy('policy_type')->get();

        // 👇 naya — category & house dropdowns ke liye
        $categories = \App\Models\StudentCategory::where('status', 1)->orderBy('name')->get();
        $houses = \App\Models\StudentHouse::where('status', 1)->orderBy('name')->get();

        return view('admin.students.create_student', compact(
            'classes',
            'sections',
            'groups',
            'selected_class',
            'discountPolicies',
            'categories',   // 👈 naya
            'houses'        // 👈 naya
        ));
    }
// End Method

    /**
     * 4. DEPENDENCY FETCH: Dynamic Section Population Matrix (AJAX)
     */
    public function getSectionsByClass($class_id)
    {
        if (!$class_id) {
            return response()->json([
                'status' => 'error',
                'data' => []
            ], 400);
        }

        try {
            $sections = Section::whereHas('schoolClasses', function ($q) use ($class_id) {
                $q->where('school_class_id', $class_id)
                    ->where('school_class_section.status', 1);
            })
                ->where('status', 1)
                ->orderBy('name', 'ASC')
                ->get(['id', 'name']);

            return response()->json([
                'status' => 'success',
                'data' => $sections
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getGroupsByClass($class_id)
    {
        // Enrollments table se unique group_ids uthayein jo is class_id se related hain
        $groups = Enrollment::where('class_id', $class_id)
            ->with('group') // Enrollment model mein 'group' relation hona chahiye
            ->whereHas('group') // Sirf wahi enrollments jinka group exist karta hai
            ->get()
            ->pluck('group') // Sirf group ka data lein
            ->unique('id')   // Duplicate hatayein
            ->values();      // Index reset karein

        return response()->json([
            'status' => 'success',
            'data' => $groups
        ]);
    }


    public function FilterStudents(Request $request)
    {
        // 1. Student model se start karne ke bajaye Enrollment se start karein
        // Kyunke enrollments table mein sab kuch linked hai.
        $query = Enrollment::with(['student.user', 'schoolClass', 'section', 'group']);

        // 2. Dynamic Filtering (Condition checks)
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        // 👈 NAYA: Keyword search (Student ke first_name, last_name, admission_no par)
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('student', function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('admission_no', 'like', "%{$keyword}%");
            });
        }

        // 3. Execution
        $enrollments = $query->latest()->get();

        // 4. View Rendering
        // Note: Aapka partial view ab 'enrollments' object expect karega
        $html = view('admin.students.partials.table_rows', ['enrollments' => $enrollments])->render();

        return response()->json([
            'status' => 'success',
            'html' => $html,
            'count' => $enrollments->count()
        ]);
    }

    /**
     * 5. SMART LOGIC: Live AJAX Generation Layer for Unique Identifiers
     */
    public function GetNextAcademicIdentifiers(Request $request)
    {
        $classId   = $request->class_id;
        $sectionId = $request->section_id;
        $groupId   = $request->group_id;

        if (!$classId || !$sectionId) {
            return response()->json(['success' => false, 'message' => 'Required parameters missing.']);
        }

        $activeYear = AcademicYear::where('is_current', 1)->first();
        if (!$activeYear) {
            return response()->json(['success' => false, 'message' => 'No active academic year configured.']);
        }

        // 1. EXTRACT 2-DIGIT YEAR PREFIX (e.g., "2026-2027" -> "26")
        $sessionName = trim($activeYear->name);
        $startYear = explode('-', $sessionName)[0];
        $sessionDigit = substr(trim($startYear), -2); // e.g., 26

        // 2. CLASS 2-DIGIT MATRIX ENFORCEMENT (01 se 15)
        // 1. Class model dhundhein
        $classModel = SchoolClass::find($classId);

        // 2. Safety check (agar class na mile to wahi se ruk jaye)
        if (!$classModel) {
            return response()->json(['success' => false, 'message' => 'Class configuration not found.']);
        }

        // 3. Digit generation (ab numeric_name use ho raha hai)
        $classDigit = str_pad($classModel->numeric_name, 2, '0', STR_PAD_LEFT);

        // 3. SECTION DIGIT CONVERSION ENGINE (A->11, B->12, C->13)
        $sectionModel = Section::find($sectionId);
        $sectionChar  = $sectionModel ? strtoupper(substr(trim($sectionModel->name), 0, 1)) : 'A';

        $asciiVal = ord($sectionChar);
        if ($asciiVal >= 65 && $asciiVal <= 90) {
            $sectionDigit = $asciiVal - 54; // A=11, B=12, C=13...
        } else {
            $sectionDigit = 11;
        }

        // 4. SEQUENCE COUNT FOR CUSTOM REALTIME COUNTER (Updated Logic)
        $lastRoll = Enrollment::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('academic_year_id', $activeYear->id) // Yahan $activeYear->id use karein
            ->orderBy('id', 'DESC')
            ->first();

        $lastSequence = $lastRoll ? (int)substr((string)$lastRoll->roll_no, -2) : 0;
        $uniqueIncrement = $lastSequence + 1;
        $rollSequenceDigit = str_pad($uniqueIncrement, 2, '0', STR_PAD_LEFT);

        // 🔥 GENERATE PURE NUMERIC CUSTOM ROLL NUMBER (e.g., 26011101)
        $customNumericRollNo = $sessionDigit . $classDigit . $sectionDigit . $rollSequenceDigit;

        // Standard Admission Number Pattern for Background Track
        $groupModel = Group::find($groupId);
        $groupCode  = $groupModel ? strtoupper(trim($groupModel->group_code ?? ($groupModel->code ?? $groupModel->name))) : 'GEN';
        $admission_no = "ADM-" . $sessionName . "-" . $classDigit . $sectionChar . "-" . $groupCode . "-" . str_pad($uniqueIncrement, 5, '0', STR_PAD_LEFT);

        return response()->json([
            'success'      => true,
            'admission_no' => $admission_no,
            'roll_no'      => $customNumericRollNo // 🔥 Ab input field me exact "26011101" load hoga!
        ]);
    }

    /**
     * =====================================================================
     * 1. STORE METHOD (final version — already working, included for reference)
     * =====================================================================
     */
    public function StoreStudent(StoreStudentRequest $request)
    {
        DB::beginTransaction();

        try {
            $activeYear = AcademicYear::where('is_current', 1)->first();
            if (!$activeYear) {
                throw new Exception("Active Academic Year setting configuration table entry is missing.");
            }
            $academic_year_id = $activeYear->id;

            $classId   = $request->class_id;
            $sectionId = $request->section_id;
            $groupId   = $request->group_id;
            $sessionName = trim($activeYear->name);

            $startYear = explode('-', $sessionName)[0];
            $sessionDigit = substr(trim($startYear), -2);

            $classModel = SchoolClass::find($classId);
            if (!$classModel) {
                return response()->json(['success' => false, 'message' => 'Class configuration not found.']);
            }
            $classDigit = str_pad($classModel->numeric_name, 2, '0', STR_PAD_LEFT);

            $sectionModel = Section::find($sectionId);
            $sectionChar  = $sectionModel ? strtoupper(substr(trim($sectionModel->name), 0, 1)) : 'A';

            $asciiVal = ord($sectionChar);
            if ($asciiVal >= 65 && $asciiVal <= 90) {
                $sectionDigit = $asciiVal - 54;
            } else {
                $sectionDigit = 11;
            }

            // SEQUENCE BASE — MAX sequence for class/section/year (ek hi query)
            $maxSeq = Enrollment::where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->where('academic_year_id', $academic_year_id)
                ->selectRaw('MAX(CAST(SUBSTRING(roll_no, -2) AS UNSIGNED)) as max_seq')
                ->value('max_seq') ?? 0;

            $uniqueIncrement = $maxSeq + 1;

            $groupModel = Group::find($groupId);
            $groupCode  = $groupModel ? strtoupper(trim($groupModel->group_code ?? ($groupModel->code ?? $groupModel->name))) : 'GEN';

            // RETRY-GUARD — clash hone par khud agla number try karta hai
            $maxAttempts = 50;
            $attempt = 0;
            $customNumericRollNo = null;
            $admission_no = null;

            do {
                $rollSequenceDigit   = str_pad($uniqueIncrement, 2, '0', STR_PAD_LEFT);
                $customNumericRollNo = $sessionDigit . $classDigit . $sectionDigit . $rollSequenceDigit;
                $admission_no         = "ADM-" . $sessionName . "-" . $classDigit . $sectionChar . "-" . $groupCode . "-" . str_pad($uniqueIncrement, 5, '0', STR_PAD_LEFT);

                $usernameTaken  = User::where('username', $customNumericRollNo)->exists();
                $admissionTaken = Student::where('admission_no', $admission_no)->exists();

                if (!$usernameTaken && !$admissionTaken) {
                    break;
                }

                $uniqueIncrement++;
                $attempt++;
            } while ($attempt < $maxAttempts);

            if ($attempt >= $maxAttempts) {
                throw new Exception("Roll No / Admission No generate nahi ho saka — {$maxAttempts} attempts ke baad bhi free number nahi mila.");
            }

            // SIBLING PROFILE CHECK MATRIX
            $fatherCnicClean = trim($request->father_cnic);
            $parentProfile = ParentProfile::where('father_cnic', $fatherCnicClean)->first();

            if (!$parentProfile) {
                if (User::where('username', $fatherCnicClean)->exists()) {
                    throw new Exception("This Father CNIC unique identifier is already allocated to another system user entity.");
                }

                // Guardian email optional
                $guardianEmail = $request->filled('guardian_email') ? strtolower(trim($request->guardian_email)) : null;

                $parentUser = User::create([
                    'name'     => $request->father_name,
                    'username' => $fatherCnicClean,
                    'email'    => $guardianEmail,
                    'password' => Hash::make($request->guardian_password), // guardian password (father/mother se independent)
                    'role_id'  => 7,
                    'status'   => 2,
                ]);

                $parentUser->assignRole('parent');

                $fatherPhoto = $request->hasFile('father_photo') ? $this->imageService->upload($request->file('father_photo'), 'uploads/parents') : null;
                $motherPhoto = $request->hasFile('mother_photo') ? $this->imageService->upload($request->file('mother_photo'), 'uploads/parents') : null;

                if ($request->input('is_guardian') === 'father') {
                    $guardianPhoto = $fatherPhoto;
                } elseif ($request->input('is_guardian') === 'mother') {
                    $guardianPhoto = $motherPhoto;
                } else {
                    $guardianPhoto = $request->hasFile('guardian_photo') ? $this->imageService->upload($request->file('guardian_photo'), 'uploads/parents') : null;
                }

                $fCnicFront = $request->hasFile('father_cnic_front') ? $this->imageService->upload($request->file('father_cnic_front'), 'uploads/documents') : null;
                $fCnicBack  = $request->hasFile('father_cnic_back')  ? $this->imageService->upload($request->file('father_cnic_back'), 'uploads/documents') : null;
                $mCnicFront = $request->hasFile('mother_cnic_front') ? $this->imageService->upload($request->file('mother_cnic_front'), 'uploads/documents') : null;
                $mCnicBack  = $request->hasFile('mother_cnic_back')  ? $this->imageService->upload($request->file('mother_cnic_back'), 'uploads/documents') : null;

                $parentProfile = ParentProfile::create([
                    'user_id'           => $parentUser->id,
                    'father_name'       => $request->father_name,
                    'father_phone'      => $request->father_phone,
                    'father_photo'      => $fatherPhoto,
                    'mother_name'       => $request->mother_name,
                    'mother_phone'      => $request->mother_phone,
                    'mother_photo'      => $motherPhoto,
                    'is_guardian'       => $request->is_guardian,
                    'guardian_name'     => $request->guardian_name,
                    'guardian_relation' => $request->guardian_relation,
                    'guardian_phone'    => $request->guardian_phone,
                    'guardian_email'    => $request->guardian_email,
                    'guardian_address'  => $request->guardian_address,
                    'guardian_photo'    => $guardianPhoto,
                    'father_cnic'       => $fatherCnicClean,
                    'father_cnic_front' => $fCnicFront,
                    'father_cnic_back'  => $fCnicBack,
                    'mother_cnic'       => $request->mother_cnic,
                    'mother_cnic_front' => $mCnicFront,
                    'mother_cnic_back'  => $mCnicBack,
                ]);
            }

            // CREATE STUDENT AUTH ACCOUNT
            $studentUser = User::create([
                'name'     => $request->first_name . ' ' . $request->last_name,
                'username' => $customNumericRollNo,
                'email'    => null,   // student ka email form me nahi hai — hamesha null
                'password' => Hash::make($request->password),
                'role_id'  => 8,
                'status'   => 2,
            ]);

            $studentUser->assignRole('student');

            $studentPhoto = $request->hasFile('student_photo') ? $this->imageService->upload($request->file('student_photo'), 'uploads/students') : null;

            $student = Student::create([
                'user_id'          => $studentUser->id,
                'parent_id'        => $parentProfile->id,
                'admission_no'     => $admission_no,
                'roll_number'      => $customNumericRollNo,
                'first_name'       => $request->first_name,
                'last_name'        => $request->last_name,
                'gender'           => $request->gender,
                'date_of_birth'    => $request->date_of_birth,
                'admission_date'   => $request->admission_date,
                'category_id'      => $request->category_id,
                'house_id'         => $request->house_id,
                'religion'         => $request->religion,
                'caste'            => $request->caste,
                'blood_group'      => $request->blood_group,
                'height'           => $request->height,
                'weight'           => $request->weight,
                'measurement_date' => $request->measurement_date,
                'photo'            => $studentPhoto,
                'medical_history'  => $request->medical_history,
            ]);

            Enrollment::create([
                'student_id'       => $student->id,
                'academic_year_id' => $academic_year_id,
                'class_id'         => $classId,
                'section_id'       => $sectionId,
                'group_id'         => $groupId,
                'roll_no'          => $customNumericRollNo,
                'enroll_status'    => 1,
            ]);

            $selectedPolicyIds = $request->input('discount_policy_ids', []);

            foreach ($selectedPolicyIds as $policyId) {
                StudentDiscount::create([
                    'student_id'         => $student->id,
                    'discount_policy_id' => $policyId,
                    'remarks'            => $request->input('discount_remarks'),
                    'approved_by'        => auth()->id(),
                    'status'             => 1,
                ]);
            }

            $discountService = new DiscountService();

            $feeStructures = FeeStructure::with('feeType')
                ->where('school_class_id', $classId)
                ->where('academic_year_id', $academic_year_id)
                ->get();

            $admissionGroupId = null; // 👈 admission ki saari one-time fees isi group_id se linked hongi (single receipt)

            foreach ($feeStructures as $fs) {
                $frequency = $fs->feeType->frequency ?? 'monthly';

                if ($frequency === 'one_time') {
                    $discountAmt = $discountService->calculateDiscount(
                        (float) $fs->amount,
                        $fs->fee_type_id,
                        $selectedPolicyIds
                    );

                    $payableAmt = max(0, (float) $fs->amount - $discountAmt);

                    $studentFee = StudentFees::create([
                        'fee_structure_id' => $fs->id,
                        'student_id'       => $student->id,
                        'fee_type_id'      => $fs->fee_type_id,
                        'amount'           => $fs->amount,
                        'discount'         => $discountAmt,
                        'paid_amount'      => $payableAmt,
                        'due_date'         => $fs->due_date,
                        'status'           => 'paid', // 👈 admission ke waqt hi paid mark
                    ]);

                    // 👇 sirf tab Transaction banayein jab kuch actually payable ho (0 discount-se-clear fee ke liye transaction nahi chahiye)
                    if ($payableAmt > 0) {
                        $transaction = Transaction::create([
                            'payment_group_id' => $admissionGroupId,
                            'bank_account_id'  => null,
                            'user_id'          => auth()->id(),
                            'student_fee_id'   => $studentFee->id,
                            'type'             => 'income',
                            'category'         => 'fee',
                            'amount'           => $payableAmt,
                            'payment_method'   => 'cash',
                            'reference_no'     => null,
                            'transaction_date' => $request->admission_date ?? now(),
                            'note'             => 'Collected at admission',
                        ]);

                        if ($admissionGroupId === null) {
                            $admissionGroupId = $transaction->id;
                            $transaction->payment_group_id = $admissionGroupId;
                            $transaction->save();
                        } else {
                            $transaction->payment_group_id = $admissionGroupId;
                            $transaction->save();
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('students.index')
                ->with('toastr-success', 'Enrollment successful! Numeric Login ID Allocated: ' . $customNumericRollNo);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('toastr-error', 'Database Layer Crash Interception: ' . $e->getMessage());
        }
    }
    // End Method

    /**
     * Dynamic Sibling Fetch & Auto-Population Data Interceptor
     */
    public function CheckParentProfile($cnic)
    {
        try {
            // Clean incoming CNIC string formatting
            $cleanCnic = trim($cnic);

            // Find if parent exists
            $parent = ParentProfile::where('father_cnic', $cleanCnic)->first();

            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'New parent configuration layout initialized.'
                ], 200);
            }

            // Count how many children this parent currently has active in system
            $childrenCount = Student::where('parent_id', $parent->id)->count();

            // Evaluate Dynamic Fee Concession or Discount Band Matrix
            $suggestedDiscountPercent = 0;
            if ($childrenCount == 1) {
                $suggestedDiscountPercent = 20;
            } elseif ($childrenCount >= 2) {
                $suggestedDiscountPercent = 50;
            }

            return response()->json([
                'success' => true,
                'sibling_detected' => true,
                'children_count' => $childrenCount,
                'discount_percent' => $suggestedDiscountPercent,
                'parent_data' => [
                    'father_name'       => $parent->father_name,
                    'father_phone'      => $parent->father_phone,
                    'mother_name'       => $parent->mother_name,
                    'mother_phone'      => $parent->mother_phone,
                    'is_guardian'       => $parent->is_guardian,
                    'guardian_name'     => $parent->guardian_name,
                    'guardian_relation' => $parent->guardian_relation,
                    'guardian_email'    => $parent->guardian_email,    // 🔥 Field Added
                    'guardian_phone'    => $parent->guardian_phone,
                    'guardian_address'  => $parent->guardian_address,  // 🔥 Field Added
                    'mother_cnic'       => $parent->mother_cnic,       // 🔥 Field Added
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing request: ' . $e->getMessage()
            ], 500);
        }
    }
    // End Method


    public function getFeeStructure(Request $request)
    {
        $classId   = $request->class_id;
        $policyIds = $request->input('discount_policy_ids', []); // 👈 ab array

        if (!$classId) {
            return response()->json(['success' => false, 'message' => 'Class ID required.']);
        }

        $activeYear = AcademicYear::where('is_current', 1)->first();
        if (!$activeYear) {
            $html = '<div class="alert alert-warning mb-0 small border-0">Active academic year set nahi hai.</div>';
            return response()->json(['success' => true, 'html' => $html, 'total' => 0]);
        }

        $feeStructures = FeeStructure::with('feeType')
            ->where('school_class_id', $classId)
            ->where('academic_year_id', $activeYear->id)
            ->orderBy('id')
            ->get();

        if ($feeStructures->isEmpty()) {
            $html = '<div class="alert alert-warning mb-0 small border-0">
            <i class="bx bx-error-circle me-1"></i> Is class ke liye abhi tak koi fee structure configure nahi hui.
          </div>';
            return response()->json(['success' => true, 'html' => $html, 'total' => 0]);
        }

        $discountService = new DiscountService();
        $oneTimeTotal = 0;
        $rows = '';

        foreach ($feeStructures as $fs) {
            $frequency = $fs->feeType->frequency ?? 'monthly';
            $frequencyLabel = ucfirst(str_replace('_', '-', $frequency));

            $discountAmt = $discountService->calculateDiscount(
                (float) $fs->amount,
                $fs->fee_type_id,
                $policyIds
            );
            $payable = $fs->amount - $discountAmt;

            if ($frequency === 'one_time') {
                $oneTimeTotal += $payable;
            }

            $badgeClass = match ($frequency) {
                'one_time' => 'bg-success',
                'monthly'  => 'bg-info',
                'annual'   => 'bg-warning text-dark',
                default    => 'bg-secondary',
            };

            $discountCell = $discountAmt > 0
                ? '<span class="text-danger">-' . number_format($discountAmt, 2) . '</span>'
                : '0.00';

            $rows .= '<tr>
                        <td><span class="fw-bold text-dark">' . e($fs->feeType->name ?? 'N/A') . '</span></td>
                        <td><span class="badge ' . $badgeClass . '">' . e($frequencyLabel) . '</span></td>
                        <td>' . ($fs->due_date ? e($fs->due_date->format('d M Y')) : '--') . '</td>
                        <td class="text-end">' . number_format($fs->amount, 2) . '</td>
                        <td class="text-end">' . $discountCell . '</td>
                        <td class="text-end fw-bold">' . number_format($payable, 2) . '</td>
                    </tr>';
        }

        $html = '
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fee Head</th>
                                <th>Type</th>
                                <th>Due Date</th>
                                <th class="text-end">Amount (PKR)</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Payable</th>
                            </tr>
                        </thead>
                        <tbody>' . $rows . '</tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th colspan="5" class="text-end">Payable at Admission (One-Time)</th>
                                <th class="text-end">' . number_format($oneTimeTotal, 2) . '</th>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="alert alert-info mt-2 mb-0 small border-0">
                        <i class="bx bx-info-circle me-1"></i> Recurring fees (Monthly/Quarterly) alag se collect hongi enrollment ke baad.
                    </div>
                </div>';

        return response()->json(['success' => true, 'html' => $html, 'total' => $oneTimeTotal]);
    }
    // End Method

    public function getStudentDetails($id)
    {
        // Student ko Enrollment ke through fetch karein
        $enrollment = Enrollment::with(['student.user', 'student.parent', 'schoolClass', 'section', 'attendance'])
            ->where('student_id', $id)
            ->latest()
            ->firstOrFail();

        $student = $enrollment->student;

        return view('admin.students.details_partial', compact('student', 'enrollment'))->render();
    }

    // --- 🅰️ EDIT METHOD ---
    public function EditStudent($id)
    {
        // 1. Student aur uski current enrollment history nikaalein
        $student = Student::with(['user', 'parent', 'currentEnrollment'])->findOrFail($id);

        // Pehle check karein ke enrollment mapping database me majood hai ya nahi
        // Agar enrollment table me class_id hai, to wo uthao, warna safe side fallback do
        $classId = $student->currentEnrollment->class_id ?? null;

        $classes = SchoolClass::all();
        $groups = Group::all();
        $academic_years = AcademicYear::all();

        // 2. Mapping Pivot Table se dynamic active sections nikaalein
        $sections = collect(); // Fallback empty collection
        if ($classId) {
            $sections = DB::table('sections')
                ->join('school_class_section', 'sections.id', '=', 'school_class_section.section_id')
                ->where('school_class_section.school_class_id', $classId)
                ->select('sections.id', 'sections.name')
                ->get();
        }



        return view('admin.students.edit_student', compact(
            'student',
            'classes',
            'sections',
            'groups',
            'academic_years'
        ));
    }

    /**
     * 7. ATOMIC TRANSACTION: Update Student Record, Parent Profile & Enrollment Sync Matrix
     */
    public function UpdateStudent(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // 1. Fetch Core Records using strict Primary Keys
            $student       = Student::findOrFail($id);
            $studentUser   = User::findOrFail($student->user_id);
            $parentProfile = ParentProfile::findOrFail($student->parent_id);

            // Fetch Current Active Academic Session Mapping
            $activeYear = AcademicYear::where('is_current', 1)->first();
            if (!$activeYear) {
                throw new Exception("Active Academic Year setting configuration table entry is missing.");
            }
            $academic_year_id = $activeYear->id;

            // 2. Class, Section, Group fallback from existing enrollment if not present in request
            $currentEnrollment = Enrollment::where('student_id', $student->id)
                ->where('academic_year_id', $academic_year_id)
                ->first();

            $classId   = $request->input('class_id', $currentEnrollment->class_id ?? null);
            $sectionId = $request->input('section_id', $currentEnrollment->section_id ?? null);
            $groupId   = $request->input('group_id', $currentEnrollment->group_id ?? null);

            // Roll No / Admission No: keep existing unless explicitly overridden
            $admission_no        = $request->filled('admission_no') ? $request->admission_no : $student->admission_no;
            $customNumericRollNo = $request->filled('roll_no') ? $request->roll_no : $student->roll_number;

            // =================================================================
            // TRACK LAYER A: UPDATE PARENT / GUARDIAN STRUCTURAL PROFILE
            // =================================================================
            $fatherCnicClean = trim($request->father_cnic);

            if (!empty($fatherCnicClean)) {
                $cnicConflict = ParentProfile::where('father_cnic', $fatherCnicClean)
                    ->where('id', '!=', $parentProfile->id)
                    ->first();

                if ($cnicConflict) {
                    // Sibling scenario — re-parent this student under the existing profile
                    $parentProfile = $cnicConflict;
                } else {
                    $parentUser = User::find($parentProfile->user_id);
                    if ($parentUser) {
                        // Guardian email stays optional — never force-fill
                        $guardianEmail = $request->filled('guardian_email')
                            ? strtolower(trim($request->guardian_email))
                            : $parentUser->email;

                        $parentUserUpdateData = [
                            'name'     => $request->father_name ?? $parentUser->name,
                            'username' => $fatherCnicClean,
                            'email'    => $guardianEmail,
                        ];

                        // Guardian password sirf tab update hoga jab form se naya diya jaye
                        if ($request->filled('guardian_password')) {
                            $parentUserUpdateData['password'] = Hash::make($request->guardian_password);
                        }

                        $parentUser->update($parentUserUpdateData);
                    }
                }
            }

            // Document Processing Streams with structural preservation layout
            $fatherPhoto = $parentProfile->father_photo;
            if ($request->hasFile('father_photo')) {
                $fatherPhoto = $this->imageService->upload($request->file('father_photo'), 'uploads/parents');
            }

            $motherPhoto = $parentProfile->mother_photo;
            if ($request->hasFile('mother_photo')) {
                $motherPhoto = $this->imageService->upload($request->file('mother_photo'), 'uploads/parents');
            }

            if ($request->input('is_guardian') === 'father') {
                $guardianPhoto = $fatherPhoto;
            } elseif ($request->input('is_guardian') === 'mother') {
                $guardianPhoto = $motherPhoto;
            } else {
                $guardianPhoto = $request->hasFile('guardian_photo')
                    ? $this->imageService->upload($request->file('guardian_photo'), 'uploads/parents')
                    : $parentProfile->guardian_photo;
            }

            $fCnicFront = $request->hasFile('father_cnic_front') ? $this->imageService->upload($request->file('father_cnic_front'), 'uploads/documents') : $parentProfile->father_cnic_front;
            $fCnicBack  = $request->hasFile('father_cnic_back')  ? $this->imageService->upload($request->file('father_cnic_back'), 'uploads/documents')  : $parentProfile->father_cnic_back;
            $mCnicFront = $request->hasFile('mother_cnic_front') ? $this->imageService->upload($request->file('mother_cnic_front'), 'uploads/documents') : $parentProfile->mother_cnic_front;
            $mCnicBack  = $request->hasFile('mother_cnic_back')  ? $this->imageService->upload($request->file('mother_cnic_back'), 'uploads/documents')  : $parentProfile->mother_cnic_back;

            $parentProfile->update([
                'father_name'       => $request->father_name ?? $parentProfile->father_name,
                'father_phone'      => $request->father_phone ?? $parentProfile->father_phone,
                'father_photo'      => $fatherPhoto,
                'mother_name'       => $request->mother_name ?? $parentProfile->mother_name,
                'mother_phone'      => $request->mother_phone ?? $parentProfile->mother_phone,
                'mother_photo'      => $motherPhoto,
                'is_guardian'       => $request->is_guardian ?? $parentProfile->is_guardian,
                'guardian_name'     => $request->guardian_name ?? $parentProfile->guardian_name,
                'guardian_relation' => $request->guardian_relation ?? $parentProfile->guardian_relation,
                'guardian_phone'    => $request->guardian_phone ?? $parentProfile->guardian_phone,
                'guardian_email'    => $request->filled('guardian_email') ? $request->guardian_email : $parentProfile->guardian_email,
                'guardian_address'  => $request->guardian_address ?? $parentProfile->guardian_address,
                'guardian_photo'    => $guardianPhoto,
                'father_cnic'       => !empty($fatherCnicClean) ? $fatherCnicClean : $parentProfile->father_cnic,
                'father_cnic_front' => $fCnicFront,
                'father_cnic_back'  => $fCnicBack,
                'mother_cnic'       => $request->mother_cnic ?? $parentProfile->mother_cnic,
                'mother_cnic_front' => $mCnicFront,
                'mother_cnic_back'  => $mCnicBack,
            ]);

            // =================================================================
            // TRACK LAYER B: UPDATE STUDENT AUTH ACCOUNT
            // =================================================================
            $studentUserUpdateData = [
                'name'     => $request->first_name . ' ' . $request->last_name,
                'username' => $customNumericRollNo,
                // email intentionally untouched — student has no email field on the form,
                // matching Store's behaviour (always null, never fabricated)
            ];

            if ($request->filled('password')) {
                $studentUserUpdateData['password'] = Hash::make($request->password);
            }

            $studentUser->update($studentUserUpdateData);

            // =================================================================
            // TRACK LAYER C: UPDATE STUDENT PROFILE DATABASE CORE RECORD
            // =================================================================
            $studentPhoto = $student->photo;
            if ($request->hasFile('student_photo')) {
                $studentPhoto = $this->imageService->upload($request->file('student_photo'), 'uploads/students');
            }

            $student->update([
                'parent_id'        => $parentProfile->id,
                'admission_no'     => $admission_no,
                'roll_number'      => $customNumericRollNo,
                'first_name'       => $request->first_name,
                'last_name'        => $request->last_name,
                'gender'           => $request->gender,
                'date_of_birth'    => $request->date_of_birth,
                'admission_date'   => $request->admission_date,
                'category_id'      => $request->category_id,
                'house_id'         => $request->house_id,
                'religion'         => $request->religion,
                'caste'            => $request->caste,
                'blood_group'      => $request->blood_group,
                'height'           => $request->height,
                'weight'           => $request->weight,
                'measurement_date' => $request->measurement_date,
                'photo'            => $studentPhoto,
                'medical_history'  => $request->medical_history,
            ]);

            // =================================================================
            // TRACK LAYER D: SYNCHRONIZE ACTIVE ENROLLMENT HISTORY ENTRY
            // =================================================================
            if (!empty($classId)) {
                Enrollment::updateOrCreate(
                    [
                        'student_id'       => $student->id,
                        'academic_year_id' => $academic_year_id,
                    ],
                    [
                        'class_id'      => $classId,
                        'section_id'    => $sectionId,
                        'group_id'      => $groupId,
                        'roll_no'       => $customNumericRollNo,
                        'enroll_status' => 1,
                    ]
                );
            }

            DB::commit();

            return redirect()->route('students.index')
                ->with('toastr-success', 'Records updated successfully!');
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('toastr-error', 'Database Layer Update Exception: ' . $e->getMessage());
        }
    }
    // End Method
    /**
     * 8. ADMIN ADMISSION PANEL: Approve or Reject Pending Student Entry
     */
    public function ApproveAdmission(Request $request, $id)
    {
        // 🔒 Route middleware (can:manage-students) already protect kar raha hai — yahan dobara check nahi chahiye

        DB::beginTransaction();
        try {
            $student = Student::findOrFail($id);
            $action = $request->input('action');

            // 1. Student Status Update
            $student->status = ($action === 'approve') ? 1 : 0;
            $student->save();

            // 2. Student's linked User account update
            if ($student->user) {
                $student->user()->update(['status' => ($action === 'approve') ? 1 : 0]);
            }

            // 3. Parent's linked User account bhi activate karo (approve hone par)
            if ($action === 'approve' && $student->parent && $student->parent->user) {
                $student->parent->user()->update(['status' => 1]);
            }

            // 4. Enrollment Status Update
            $enrollment = Enrollment::where('student_id', $student->id)->latest()->first();
            if ($enrollment) {
                $enrollment->update(['enroll_status' => ($action === 'approve') ? 1 : 0]);
            }

            // 💰 5. Approve hone pe baaki (recurring) fee types generate karo
            if ($action === 'approve' && $enrollment) {
                $alreadyAssignedTypeIds = StudentFees::where('student_id', $student->id)
                    ->pluck('fee_type_id')
                    ->toArray();

                $discountService = new DiscountService();
                $studentPolicyIds = $discountService->getStudentPolicyIds($student->id); // 👈 plural

                $feeStructures = FeeStructure::with('feeType')
                    ->where('school_class_id', $enrollment->class_id)
                    ->where('academic_year_id', $enrollment->academic_year_id)
                    ->get();

                foreach ($feeStructures as $fs) {
                    $frequency = $fs->feeType->frequency ?? 'monthly';

                    if ($frequency === 'one_time' || in_array($fs->fee_type_id, $alreadyAssignedTypeIds)) {
                        continue;
                    }

                    $discountAmt = $discountService->calculateDiscount(
                        (float) $fs->amount,
                        $fs->fee_type_id,
                        $studentPolicyIds
                    );

                    StudentFees::create([
                        'fee_structure_id' => $fs->id,
                        'student_id'       => $student->id,
                        'fee_type_id'      => $fs->fee_type_id,
                        'amount'           => $fs->amount,
                        'discount'         => $discountAmt,
                        'paid_amount'      => 0,
                        'due_date'         => $fs->due_date,
                        'status'           => 'unpaid',
                    ]);
                }
            }

            $msg = ($action === 'approve') ? 'Account Activated Successfully.' : 'Account Deactivated.';

            DB::commit();
            return response()->json([
                'status' => ($action === 'approve' ? 1 : 0),
                'message' => $msg,
                'activeCount' => \App\Models\User::where('status', 1)->whereHas('studentProfile')->count(),
                'inactiveCount' => \App\Models\User::where('status', 0)->whereHas('studentProfile')->count(),
                'pendingCount' => \App\Models\User::where('status', 2)->whereHas('studentProfile')->count()
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    // End Method
    public function StudentStatus($id)
    {
        $student = Student::findOrFail($id);

        // Toggle logic: 1 to 0, or 0 to 1
        $newStatus = $student->status == 1 ? 0 : 1;

        // 1. Update Student Status
        $student->status = $newStatus;
        $student->save();

        // 2. Update Linked User Status (Bulletproof sync)
        if ($student->user) {
            $student->user()->update(['status' => $newStatus]);
        }

        // 3. Update Enrollment Status (Optional but recommended for consistency)
        $enrollment = Enrollment::where('student_id', $student->id)->latest()->first();
        if ($enrollment) {
            $enrollment->update(['enroll_status' => $newStatus]);
        }

        return response()->json([
            'status' => $newStatus,
            'message' => 'Status Updated & Synchronized Successfully!',
            'activeCount' => \App\Models\User::where('status', 1)->whereHas('studentProfile')->count(),
            'inactiveCount' => \App\Models\User::where('status', 0)->whereHas('studentProfile')->count(),
            'pendingCount' => \App\Models\User::where('status', 2)->whereHas('studentProfile')->count()
        ]);
    }
    // 1. Trash List
    public function StudentTrash()
    {
        $trashedStudents = Student::onlyTrashed()->latest()->get();
        return view('admin.student.trash', compact('trashedStudents'));
    }

    // 2. Soft Delete
    public function StudentDestroy($id)
    {
        Student::findOrFail($id)->delete();
        return redirect()->route('students.index')
            ->with('toastr-success', 'Student moved to trash.');
    }

    // 3. Restore
    public function StudentRestore($id)
    {
        Student::withTrashed()->findOrFail($id)->restore();
        return redirect()->route('students.trash')
            ->with('toastr-success', 'Student restored successfully.');
    }

    // 4. Force Delete
    public function StudentForceDelete($id)
    {
        try {
            // 1. Student ko trash mein se dhoondein
            $student = Student::onlyTrashed()->findOrFail($id);

            // 2. Agar koi User account linked hai to usay handle karein
            if ($student->user_id) {
                $user = User::find($student->user_id);
                if ($user) {
                    if (method_exists($user, 'forceDelete')) {
                        $user->forceDelete();
                    } else {
                        $user->delete();
                    }
                }
            }

            // 3. File cleanup (Student ki profile photo)
            if ($student->photo && file_exists(public_path('uploads/students/' . $student->photo))) {
                unlink(public_path('uploads/students/' . $student->photo));
            }

            // 4. Student permanently delete karein
            $student->forceDelete();

            return redirect()->route('students.trash')
                ->with('toastr-success', 'Student and associated data permanently deleted.');
        } catch (\Exception $e) {
            return redirect()->route('students.trash')
                ->with('toastr-error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
