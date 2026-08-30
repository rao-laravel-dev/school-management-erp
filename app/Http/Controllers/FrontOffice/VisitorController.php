<?php

namespace App\Http\Controllers\FrontOffice;

use App\Http\Controllers\Controller;
use App\Models\Purpose;
use App\Models\SchoolClass;
use App\Models\Visitor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class VisitorController extends Controller
{
    /**
     * Display a listing of the visitors.
     */
    public function index()
    {
        $visitors = Visitor::latest()->get();

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
                'staffRoles'
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
    public function getPeople($type, $id)
    {
        $data = [];

        if ($type == 'student') {
            // Enrollment table se students join kar rahe hain
            $data = DB::table('students')
                ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
                ->where('enrollments.class_id', $id) // Yahan class_id ka column check karein
                ->select('students.id', DB::raw("CONCAT(students.first_name, ' ', students.last_name) AS name"))
                ->get();
        } elseif ($type == 'staff') {
            $role = DB::table('roles')->find($id);
            if ($role) {
                $tableName = strtolower($role->name);

                // Debugging: check karein kya ye sahi table naam bana raha hai?
                // Agar table ka naam 'teachers' hai aur role ka naam 'teacher' hai, 
                // toh ho sakta hai aapko '$tableName . 's'' ki zaroorat ho.

                if (Schema::hasTable($tableName)) {
                    $data = DB::table($tableName)
                        ->select('id', DB::raw("CONCAT(first_name, ' ', last_name) AS name"))
                        ->get();
                } else {
                    // Agar table ka naam 'teacher' nahi 'teachers' hai:
                    $tableName = $tableName . 's';
                    if (Schema::hasTable($tableName)) {
                        $data = DB::table($tableName)
                            ->select('id', DB::raw("CONCAT(first_name, ' ', last_name) AS name"))
                            ->get();
                    }
                }
            }
        }

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
        // 1. Validation add karein
        $request->validate([
            'purpose_id'         => 'required|exists:purposes,id',
            'meeting_with_type'  => 'required|in:student,staff',
            'name'               => 'required|string|max:255',
            'phone'              => 'required|string|max:20'
        ]);

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
                'id_card'           => $request->id_card,
                'no_of_person'      => $request->no_of_person,
                'date'              => $request->date,
                'in_time'           => $request->in_time,
                'out_time'          => $request->out_time,
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
        $request->validate([
            'purpose_id'        => 'required',
            'meeting_with_type'  => 'required',
            'name'               => 'required|string|max:255',
            'phone'              => 'required|string|max:20',
        ]);

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
                'id_card'           => $request->id_card,
                'no_of_person'      => $request->no_of_person,
                'date'              => $request->date,
                'in_time'           => $request->in_time,
                'out_time'          => $request->out_time,
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

            return redirect()->route('visitor-book.index')->with('success', 'Visitor deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }
}
