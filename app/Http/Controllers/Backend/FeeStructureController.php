<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\StudentDiscount;
use App\Models\StudentFees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FeeStructureController extends Controller
{
    public function index()
    {
        $feeStructures = FeeStructure::with(['feeType', 'schoolClass', 'academicYear'])->latest()->get();
        $feeTypes      = FeeType::all();
        $schoolClasses = SchoolClass::all();
        $academicYears = AcademicYear::all();

        return view('admin.fee_structure.index', compact(
            'feeStructures',
            'feeTypes',
            'schoolClasses',
            'academicYears'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fee_type_id'      => 'required|exists:fee_types,id',
            'school_class_id'  => 'nullable|exists:school_class,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'amount'           => 'required|numeric|min:0',
            'due_date'         => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        FeeStructure::create($request->only([
            'fee_type_id',
            'school_class_id',
            'academic_year_id',
            'amount',
            'due_date'
        ]));

        return response()->json(['message' => 'Fee structure added successfully.']);
    }

    public function update(Request $request, $id)
    {
        $feeStructure = FeeStructure::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'fee_type_id'      => 'required|exists:fee_types,id',
            'school_class_id'  => 'nullable|exists:school_class,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'amount'           => 'required|numeric|min:0',
            'due_date'         => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $feeStructure->update($request->only([
            'fee_type_id',
            'school_class_id',
            'academic_year_id',
            'amount',
            'due_date'
        ]));

        return response()->json(['message' => 'Fee structure updated successfully.']);
    }

    public function generateFees($id)
    {
        try {
            $feeStructure = FeeStructure::with('feeType')->findOrFail($id);

            if (!$feeStructure->school_class_id) {
                return response()->json([
                    'message' => 'This fee structure has no class assigned.'
                ], 422);
            }

            $studentIdsQuery = DB::table('enrollments')
                ->join('students', 'enrollments.student_id', '=', 'students.id')
                ->where('enrollments.class_id', $feeStructure->school_class_id);

            if ($feeStructure->academic_year_id) {
                $studentIdsQuery->where('enrollments.academic_year_id', $feeStructure->academic_year_id);
            }

            $studentIds = $studentIdsQuery->pluck('students.id')->unique();

            if ($studentIds->isEmpty()) {
                return response()->json([
                    'message' => 'No students found in the selected class.'
                ], 422);
            }

            // Duplicate check due_date ke sath bhi match karega
            $alreadyGeneratedStudentIds = StudentFees::where('fee_structure_id', $feeStructure->id)
                ->where('due_date', $feeStructure->due_date)
                ->whereIn('student_id', $studentIds)
                ->pluck('student_id')
                ->toArray();

            $pendingStudentIds = $studentIds->diff($alreadyGeneratedStudentIds);

            if ($pendingStudentIds->isEmpty()) {
                return response()->json([
                    'message' => 'Fees already generated for all students for this due date.'
                ], 422);
            }

            DB::beginTransaction();
            $count = 0;

            foreach ($pendingStudentIds as $studentId) {
                StudentFees::create([
                    'fee_structure_id' => $feeStructure->id,
                    'student_id'       => $studentId,
                    'fee_type_id'      => $feeStructure->fee_type_id,
                    'amount'           => $feeStructure->amount,
                    'discount'         => $this->calculateDiscount($studentId, $feeStructure),
                    'paid_amount'      => 0,
                    'due_date'         => $feeStructure->due_date,
                    'status'           => 'unpaid',
                ]);
                $count++;
            }

            // 🔥 naya — generation tracking update
            $feeStructure->update([
                'last_generated_period' => $feeStructure->feeType->frequency === 'monthly'
                    ? $feeStructure->due_date->format('Y-m')
                    : 'done',
                'last_generated_at' => now(),
            ]);
            
            DB::commit();

            $skipped = $studentIds->count() - $count;
            $message = $count . ' student fee record(s) generated for ' . $feeStructure->due_date->format('d M Y') . '!';
            if ($skipped > 0) {
                $message .= ' (' . $skipped . ' already had this exact due date, skipped)';
            }

            return response()->json(['message' => $message]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Generate Fees Error: " . $e->getMessage());
            return response()->json([
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }
    // End Method

    private function calculateDiscount($studentId, $feeStructure): float
    {
        if (!$feeStructure->feeType->is_discountable) {
            return 0;
        }

        $discounts = StudentDiscount::where('student_id', $studentId)
            ->where('status', 1)
            ->whereHas('discountPolicy', function ($q) use ($feeStructure) {
                $q->where('status', 1)
                    ->where(function ($q2) use ($feeStructure) {
                        $q2->whereNull('fee_type_id')
                            ->orWhere('fee_type_id', $feeStructure->fee_type_id);
                    });
            })
            ->with('discountPolicy')
            ->get();

        if ($discounts->isEmpty()) return 0;

        return $discounts->max(function ($sd) use ($feeStructure) {
            $p = $sd->discountPolicy;
            return $p->discount_type === 'percentage'
                ? round($feeStructure->amount * $p->discount_value / 100, 2)
                : $p->discount_value;
        });
    }

    public function destroy($id)
    {
        $feeStructure = FeeStructure::findOrFail($id);
        $feeStructure->delete();

        return response()->json(['message' => 'Fee structure deleted successfully.']);
    }
}
