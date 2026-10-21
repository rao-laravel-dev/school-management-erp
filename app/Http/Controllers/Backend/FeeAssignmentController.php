<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\FeeAssignmentRequest;
use App\Models\AcademicYear;
use App\Models\BillingSetting;
use App\Models\FeeAssignment;
use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Services\FeeAssignmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeAssignmentController extends Controller
{
    public function __construct(private FeeAssignmentService $feeAssignmentService)
    {
    }

    public function index()
    {
        $feeAssignments = FeeAssignment::with(['feeType', 'academicYear'])
            ->withCount('studentFees')
            ->withSum('studentFees', 'paid_amount')
            ->latest()
            ->get();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $feeTypes      = FeeAssignment::assignableFeeTypes();
        $classes       = SchoolClass::where('status', 1)->orderBy('numeric_name', 'ASC')->get();

        return view('admin.fee_assignments.index', compact('feeAssignments', 'academicYears', 'feeTypes', 'classes'));
    }
    // End Method

    // AJAX: class select hone pe uske sections (Rooms ke get-sections jaisa)
    public function getSectionsByClass($classId)
    {
        $sections = Section::join('school_class_section', 'school_class_section.section_id', '=', 'sections.id')
            ->where('school_class_section.school_class_id', $classId)
            ->where('school_class_section.status', 1)
            ->select('sections.id', 'sections.name')
            ->distinct()
            ->get();

        return response()->json($sections);
    }
    // End Method

    // AJAX (Select2): active students ka naam / admission no se search
    public function searchStudents(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'q'                => 'nullable|string|max:100',
        ]);

        $keyword = trim((string) $request->q);

        $students = Student::where('status', 1)
            ->whereHas('enrollments', function ($q) use ($request) {
                $q->where('academic_year_id', $request->academic_year_id)->where('enroll_status', 1);
            })
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->where(function ($q2) use ($keyword) {
                    $q2->where('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhere('admission_no', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('first_name')
            ->limit(20)
            ->get(['id', 'first_name', 'last_name', 'admission_no']);

        return response()->json([
            'results' => $students->map(fn($s) => [
                'id'   => $s->id,
                'text' => trim($s->first_name . ' ' . $s->last_name) . ' (' . $s->admission_no . ')',
            ])->values(),
        ]);
    }
    // End Method

    // AJAX: save se pehle kitne active students milenge
    public function preview(FeeAssignmentRequest $request)
    {
        return response()->json($this->feeAssignmentService->preview($request->validated()));
    }
    // End Method

    public function store(FeeAssignmentRequest $request)
    {
        $data     = $request->validated();
        $resolved = $this->feeAssignmentService->resolveStudents($data);

        if ($resolved['ids']->isEmpty()) {
            return response()->json(['message' => 'No active students found for the selected target.'], 422);
        }

        $created = 0;
        $kind    = FeeAssignment::kindForFeeType(FeeType::findOrFail($data['fee_type_id']));

        DB::transaction(function () use ($data, $resolved, $kind, &$created) {
            $feeAssignment = FeeAssignment::create([
                'academic_year_id' => $data['academic_year_id'],
                'fee_type_id'      => $data['fee_type_id'],
                'kind'             => $kind,
                'title'            => $data['title'],
                'amount'           => $data['amount'],
                'due_date'         => $kind === FeeAssignment::KIND_ONE_TIME ? $data['due_date'] : null,
                'start_month'      => $kind === FeeAssignment::KIND_RECURRING ? $data['start_month'] : null,
                'end_month'        => $kind === FeeAssignment::KIND_RECURRING && !empty($data['end_month']) ? $data['end_month'] : null,
                'target_type'      => $data['target_type'],
                'target_label'     => $this->feeAssignmentService->targetLabel($data, $resolved['ids']->count()),
                'student_ids'      => $resolved['ids']->all(),
                'created_by'       => auth()->id(),
            ]);

            if ($kind === FeeAssignment::KIND_ONE_TIME) {
                $created = $this->feeAssignmentService->createOneTimeRows($feeAssignment, $resolved['ids']);
                return;
            }

            // Recurring: start month se current month tak ki rows abhi banao (aage ki billing run banati hai)
            $setting = BillingSetting::where('academic_year_id', $data['academic_year_id'])->firstOrFail();
            $months  = $this->feeAssignmentService->recurringMonths($data['academic_year_id'], $data['start_month'], $feeAssignment->end_month);

            foreach ($months as $month) {
                $created += $this->feeAssignmentService->createRecurringRowsForMonth($feeAssignment, $month, $setting)['created'];
            }
        });

        return response()->json(['message' => $created . ' fee row(s) created.']);
    }
    // End Method

    public function update(FeeAssignmentRequest $request, FeeAssignment $feeAssignment)
    {
        if ($feeAssignment->kind !== FeeAssignment::KIND_RECURRING) {
            $feeAssignment->update($request->only(['title']));

            return response()->json(['message' => 'Fee assignment updated successfully.']);
        }

        $newEnd = $request->input('end_month') ?: null;

        // End month ke baad ki wo rows jinpar payment / transaction / fine hai: end month un se pehle nahi ja sakta
        if ($newEnd) {
            $blocked = $feeAssignment->studentFees()
                ->where('period', '>', $newEnd)
                ->where(function ($q) {
                    $q->where('paid_amount', '>', 0)->orWhereHas('transactions')->orWhereHas('fines');
                })
                ->orderBy('period')
                ->first();

            if ($blocked) {
                $message = Carbon::parse($blocked->period . '-01')->format('M Y') . ' already has a payment or fine; End Month cannot be before it.';

                return response()->json(['message' => $message, 'errors' => ['end_month' => [$message]]], 422);
            }
        }

        $removed = 0;

        DB::transaction(function () use ($feeAssignment, $request, $newEnd, &$removed) {
            $feeAssignment->update(['title' => $request->input('title'), 'end_month' => $newEnd]);

            // Upar check ho chuka: end month ke baad ki baqi rows par kuch nahi, isliye delete
            if ($newEnd) {
                $removed = $feeAssignment->studentFees()->where('period', '>', $newEnd)->delete();
            }
        });

        return response()->json([
            'message' => 'Fee assignment updated successfully.' . ($removed ? ' ' . $removed . ' unpaid fee row(s) removed.' : ''),
        ]);
    }
    // End Method

    public function destroy(FeeAssignment $feeAssignment)
    {
        // Payment wali row ho to delete nahi (nullOnDelete se history bhi toot jati)
        $hasPayments = $feeAssignment->studentFees()
            ->where(function ($q) {
                $q->where('paid_amount', '>', 0)->orWhereHas('transactions');
            })
            ->exists();

        if ($hasPayments) {
            return response()->json(['message' => 'This assignment has payments, it cannot be deleted.'], 422);
        }

        DB::transaction(function () use ($feeAssignment) {
            $feeAssignment->studentFees()->delete();
            $feeAssignment->delete();
        });

        return response()->json(['message' => 'Fee assignment deleted successfully.']);
    }
    // End Method
}
