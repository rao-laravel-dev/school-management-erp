<?php

namespace App\Http\Controllers\FrontOffice;

use App\Http\Controllers\Controller;
use App\Models\Accountant;
use App\Models\Librarian;
use App\Models\Purpose;
use App\Models\Receptionist;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Visitor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class VisitorController extends Controller
{
    // Staff role ka naam -> profile model (meeting_with_id isi profile ki id hai)
    private const STAFF_MODELS = [
        'teacher'      => Teacher::class,
        'receptionist' => Receptionist::class,
        'accountant'   => Accountant::class,
        'librarian'    => Librarian::class,
    ];

    // Form jo roles hide karta hai (getCategories ke same)
    private const EXCLUDED_ROLES = ['superadmin', 'admin', 'parent', 'student', 'user'];

    /**
     * Store + update ke same rules. Form sirf student / staff bhejta hai;
     * staff ko role ka naam category_id se milta hai.
     */
    private function rules(Request $request): array
    {
        return [
            'purpose_id'        => 'required|exists:purposes,id',
            'meeting_with_type' => 'required|in:student,staff',
            // staff = role id (roles table), student = class id, is liye exists sirf staff par
            'category_id'       => $request->meeting_with_type === 'staff'
                ? ['required', Rule::exists('roles', 'id')->whereNotIn('name', self::EXCLUDED_ROLES)]
                : 'nullable',
            'meeting_with_id'   => 'nullable|integer',
            'name'              => 'required|string|max:255',
            'phone'             => 'required|string|max:20',
            'id_card'           => ['nullable', 'regex:/^(\d{5}-\d{7}-\d|\d{13})$/'],
            'no_of_person'      => 'nullable|integer|min:1',
            'date'              => 'required|date',
            'in_time'           => 'required',
            'out_time'          => 'nullable',
        ];
    }

    // Validation messages mein column ki jagah form label dikhao
    private function attributeNames(): array
    {
        return [
            'phone'             => 'Phone',
            'date'              => 'Date',
            'purpose_id'        => 'Purpose',
            'meeting_with_type' => 'Meeting With',
            'category_id'       => 'Category',
            'meeting_with_id'   => 'Person',
            'name'              => 'Visitor Name',
            'id_card'           => 'ID Card',
            'no_of_person'      => 'Number of Persons',
            'in_time'           => 'In Time',
            'out_time'          => 'Out Time',
        ];
    }

    private function messages(): array
    {
        return [
            'category_id.required' => 'The Role field is required.',
            'id_card.regex'        => 'ID Card must be like 12345-1234567-8',
            'date.required'        => 'The Date field is required.',
            'in_time.required'     => 'The In Time field is required.',
        ];
    }

    // 13 digit CNIC ko 12345-1234567-8 format mein save karo
    private function formatCnic(?string $cnic): ?string
    {
        if ($cnic && preg_match('/^\d{13}$/', $cnic)) {
            return substr($cnic, 0, 5) . '-' . substr($cnic, 5, 7) . '-' . substr($cnic, 12, 1);
        }

        return $cnic;
    }

    /**
     * Display a listing of the visitors.
     */
    public function index()
    {
        // Naya record pehle (# 1 = latest), Created/Updated By ke liye roles eager load
        $visitors = Visitor::with(['purpose', 'createdBy.roles', 'updatedBy.roles'])
            ->orderByDesc('id')
            ->get();

        // "Meeted With" naam: har type ki ek query (accessor per-row queries karta tha)
        $meetingNames = Visitor::meetingNames($visitors);

        $allPurposes = Purpose::all();


        $excludedRoles = [
            'superadmin',
            'admin',
            'parent',
            'student',
            'user'
        ];


        $staffRoles = Role::whereNotIn('name', $excludedRoles)
            ->select('id', 'name')
            ->get();


        return view(
            'admin.front-office.visitor-book.index',
            compact(
                'visitors',
                'allPurposes',
                'staffRoles',
                'meetingNames'
            )
        );
    }

    // 1. Categories (Roles/Classes)
    public function getCategories($type)
    {
        if ($type == 'student') {
            // Check karein kya database table mein classes ka record hai?
            $classes = SchoolClass::all();
            return response()->json($classes);
        }

        if ($type == 'staff') {
            // Unwanted roles ki list
            $excludedRoles = ['superadmin', 'admin', 'parent', 'student', 'user'];

            // Sirf wahi roles layein jo list mein nahi hain
            return response()->json(
                Role::whereNotIn('name', $excludedRoles)
                    ->select('id', 'name')
                    ->get()
            );
        } elseif ($type == 'student') {
            return response()->json(SchoolClass::select('id', 'class_name as name')->get());
        }

        return response()->json([]);
    }

    // 2. People (Dynamic Table Selection)
    public function getPeople(Request $request, $type, $id)
    {
        $model = null;
        $query = null;

        if ($type == 'student') {
            $model = Student::class;
            // Sirf is class ke enrolled students (trashed enrollment/student Eloquent khud skip karta hai)
            $query = Student::whereHas('enrollments', function ($q) use ($id) {
                $q->where('class_id', $id);
            });
        } elseif ($type == 'staff') {
            $role = Role::whereNotIn('name', self::EXCLUDED_ROLES)->find($id);
            $model = $role ? (self::STAFF_MODELS[strtolower($role->name)] ?? null) : null;
            $query = $model ? $model::query() : null;
        }

        if (!$query) {
            return response()->json([]);
        }

        // Sirf active user (status 1), trashed user/profile nahi
        $people = $query
            ->whereHas('user', function ($q) {
                $q->where('status', 1);
            })
            ->with('user.roles')
            ->get();

        // Edit mein saved person (ab inactive/trashed ho to bhi) list mein rakho
        $keep = (int) $request->query('keep');
        if ($keep && !$people->contains('id', $keep)) {
            $saved = $model::withTrashed()->with('user.roles')->find($keep);
            if ($saved) {
                $people->push($saved);
            }
        }

        $data = $people->map(function ($p) {
            return [
                'id'   => $p->id,
                // "Nawal Imran (Teacher)"; user na mile to profile ka naam
                'name' => $p->user ? $p->user->name_with_role : trim($p->first_name . ' ' . $p->last_name),
            ];
        })->sortBy('name')->values();

        return response()->json($data);
    }

    public function getStudentClass($id)
    {
        return DB::table('enrollments')
            ->where('student_id', $id)
            ->value('class_id');
    }

    public function getEditData($id)
    {
        $visitor = Visitor::findOrFail($id);

        $data = [
            'visitor' => $visitor
        ];

        if ($visitor->meeting_with_type == 'student') {

            $data['type'] = 'student';

            $data['category_id'] = DB::table('enrollments')
                ->where('student_id', $visitor->meeting_with_id)
                ->value('class_id');
        } else {

            $data['type'] = 'staff';

            $role = Role::whereRaw(
                'LOWER(name) = ?',
                [strtolower($visitor->meeting_with_type)]
            )->first();

            $data['category_id'] = $role ? $role->id : null;
        }


        return response()->json($data);
    }

    public function store(Request $request)
    {
        // 1. Validation add karein (store + update same rules)
        $request->validate($this->rules($request), $this->messages(), $this->attributeNames());

        $meetingType = $request->meeting_with_type;

        if ($meetingType == 'staff') {

            $role = Role::find($request->category_id);

            if ($role) {
                $meetingType = strtolower($role->name);
            }
        }

        try {
            // 2. Data prepare karein
            $data = [
                'purpose_id'        => $request->purpose_id,
                'meeting_with_type' => $meetingType,
                'meeting_with_id'   => $request->meeting_with_id,
                'name'              => $request->name,
                'phone'             => $request->phone,
                'id_card'           => $this->formatCnic($request->id_card),
                'no_of_person'      => $request->no_of_person,
                'date'              => $request->date,
                'in_time'           => $request->in_time,
                'out_time'          => $request->out_time,
                'created_by'        => Auth::id(),
            ];

            // 3. Save karein
            Visitor::create($data);

            // Redirect ki jagah ye use karein
            return response()->json(['success' => 'Visitor added successfully!']);
        } catch (\Exception $e) {
            // Error log karein taake baad mein check kar sakein
            Log::error("Visitor Save Error: " . $e->getMessage());

            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rules($request), $this->messages(), $this->attributeNames());

        try {
            $visitor = Visitor::findOrFail($id);
            $meetingType = $request->meeting_with_type;

            if ($meetingType == 'staff') {
                $role = Role::find($request->category_id);
                if ($role) {
                    $meetingType = strtolower($role->name);
                }
            }

            $data = [
                'purpose_id'        => $request->purpose_id,
                'meeting_with_type' => $meetingType,
                'meeting_with_id'   => $request->meeting_with_id,
                'name'              => $request->name,
                'phone'             => $request->phone,
                'id_card'           => $this->formatCnic($request->id_card),
                'no_of_person'      => $request->no_of_person,
                'date'              => $request->date,
                'in_time'           => $request->in_time,
                'out_time'          => $request->out_time,
                'updated_by'        => Auth::id(), // created_by kabhi change nahi hota
            ];

            $visitor->update($data);

            // JSON response for AJAX
            return response()->json([
                'success' => true,
                'message' => 'Visitor updated successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error("Visitor Update Error: " . $e->getMessage());

            // Error response for AJAX
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified visitor from storage.
     */
    public function VisitorDestroy($id)
    {
        try {
            $visitor = Visitor::findOrFail($id);
            $visitor->delete();

            // AJAX DELETE: JSON reply, page JS toastr + reload karta hai
            return response()->json(['message' => 'Visitor deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Delete failed: ' . $e->getMessage()], 500);
        }
    }
}
