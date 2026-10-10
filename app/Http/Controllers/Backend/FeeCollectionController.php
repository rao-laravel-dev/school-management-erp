<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFees;
use App\Models\TeacherAssignment;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FeeCollectionController extends Controller
{
    // Part A: Class/Section-wise student list
    public function index(Request $request)
    {
        $schoolClasses = SchoolClass::where('status', 1)->get();

        $classId   = $request->query('class_id');
        $sectionId = $request->query('section_id');
        $keyword   = $request->query('keyword');

        $sections = collect();
        if ($classId) {
            $class    = SchoolClass::find($classId);
            $sections = $class
                ? $class->mappedSections()->where('school_class_section.status', 1)->get()
                : collect();
        }

        $students = collect();

        if ($keyword) {
            // Keyword search: poori school, class/section filter ignore
            $studentsQuery = Student::query()
                ->where('status', 1) // 👈 sirf approved students
                ->with(['enrollments.schoolClass', 'enrollments.section', 'parent'])
                ->where(function ($q) use ($keyword) {
                    $q->where('admission_no', 'like', "%{$keyword}%")
                        ->orWhere('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhereHas('parent', function ($q2) use ($keyword) {
                            $q2->where('father_phone', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('enrollments', function ($q2) use ($keyword) {
                            $q2->where('roll_no', 'like', "%{$keyword}%");
                        });
                });

            $students = $studentsQuery->get()->map(function ($student) {
                $student->fee_balance = StudentFees::where('student_id', $student->id)
                    ->where('status', '!=', 'excluded')
                    ->selectRaw('COALESCE(SUM(amount - discount - paid_amount), 0) as balance')
                    ->value('balance');
                return $student;
            });
        } elseif ($classId) {
            // Class/Section filter: sirf selected class pe
            $studentsQuery = Student::query()
                ->where('status', 1) // 👈 sirf approved students
                ->whereHas('enrollments', function ($q) use ($classId, $sectionId) {
                    $q->where('class_id', $classId);
                    if ($sectionId) $q->where('section_id', $sectionId);
                })
                ->with(['enrollments' => function ($q) use ($classId, $sectionId) {
                    $q->where('class_id', $classId);
                    if ($sectionId) $q->where('section_id', $sectionId);
                }, 'parent']);

            $students = $studentsQuery->get()->map(function ($student) {
                $student->fee_balance = StudentFees::where('student_id', $student->id)
                    ->where('status', '!=', 'excluded')
                    ->selectRaw('COALESCE(SUM(amount - discount - paid_amount), 0) as balance')
                    ->value('balance');
                return $student;
            });
        }

        return view('admin.fee_collection.index', compact(
            'schoolClasses',
            'sections',
            'students',
            'classId',
            'sectionId',
            'keyword'
        ));
    }
    // End Method

    // AJAX: class change -> sections load
    public function getSections($classId)
    {
        $class    = SchoolClass::findOrFail($classId);
        $sections = $class->mappedSections()
            ->where('school_class_section.status', 1)
            ->get(['sections.id', 'sections.name']);

        return response()->json($sections);
    }

    // Part B: Individual student fee detail
    public function show($studentId)
    {
        $student = Student::with(['user', 'currentEnrollment.schoolClass', 'currentEnrollment.section', 'parent', 'category'])
            ->findOrFail($studentId);

        // 👇 Class Teacher fetch karein current enrollment ke hisab se
        $classTeacher = null;
        if ($student->currentEnrollment) {
            $assignment = TeacherAssignment::with('teacher')
                ->where('class_id', $student->currentEnrollment->class_id)
                ->where('section_id', $student->currentEnrollment->section_id)
                ->where('academic_year_id', $student->currentEnrollment->academic_year_id)
                ->first();

            $classTeacher = $assignment->teacher ?? null;
        }

        $fees = StudentFees::with('feeType', 'transactions')
            ->where('student_id', $student->id)
            ->where('status', '!=', 'excluded')
            ->orderByDesc('due_date')
            ->get();

        $excludedFees = StudentFees::with('feeType')
            ->where('student_id', $student->id)
            ->where('status', 'excluded')
            ->orderByDesc('due_date')
            ->get();

        $bankAccounts = BankAccount::all();

        return view('admin.fee_collection.show', compact('student', 'fees', 'bankAccounts', 'excludedFees', 'classTeacher'));
    }

    // Collect payment against one fee record
    public function pay(Request $request, $studentFeeId)
    {
        $fee     = StudentFees::findOrFail($studentFeeId);
        $balance = $fee->amount - $fee->discount - $fee->paid_amount;

        $validator = Validator::make($request->all(), [
            'amount'          => "required|numeric|min:0.01|max:{$balance}",
            'payment_method'  => 'required|in:cash,cheque,bank_transfer,easypaisa,jazzcash,card',
            'bank_account_id' => 'required_unless:payment_method,cash|nullable|exists:bank_accounts,id',
            'reference_no'    => 'nullable|string|max:100',
            'note'            => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /** @var \App\Models\Transaction $transaction */
        $transaction = null; // 👈 bahar declare kiya

        DB::transaction(function () use ($fee, $request, &$transaction) {
            $fee->paid_amount += $request->amount;

            $payable = $fee->amount - $fee->discount;
            $fee->status = $fee->paid_amount >= $payable
                ? 'paid'
                : ($fee->paid_amount > 0 ? 'partial' : 'unpaid');
            $fee->save();

            $isCash = $request->payment_method === 'cash';

            // 🔥 Cash ho to "Cash in Hand" account dhoondo, warna jo select kiya wahi
            $bankAccountId = $isCash
                ? BankAccount::where('bank_type', 'cash')->value('id')
                : $request->bank_account_id;

            $transaction = Transaction::create([
                'bank_account_id'  => $bankAccountId,
                'user_id'          => auth()->id(),
                'student_fee_id'   => $fee->id,
                'type'             => 'income',
                'category'         => 'fee',
                'amount'           => $request->amount,
                'payment_method'   => $request->payment_method,
                'reference_no'     => $request->reference_no,
                'transaction_date' => now(),
                'note'             => $request->note,
            ]);

            if ($bankAccountId) {
                BankAccount::where('id', $bankAccountId)
                    ->increment('current_balance', $request->amount);
            }
        });

        return response()->json([
            'message'        => 'Payment collected successfully.',
            'paid_amount'    => number_format($fee->paid_amount, 2),
            'balance'        => number_format($fee->amount - $fee->discount - $fee->paid_amount, 2),
            'status'         => $fee->status,
            'transaction_id' => $transaction->id, // 👈 sirf ye add karna kaafi hai
        ]);
    }
    // End Method

    public function bulkPay(Request $request)
    {
        $ids = array_filter(explode(',', $request->fee_ids));

        $fees = StudentFees::whereIn('id', $ids)->get();

        if ($fees->isEmpty()) {
            return response()->json(['message' => 'No fees selected.'], 422);
        }

        $totalBalance = $fees->sum(fn($fee) => $fee->amount - $fee->discount - $fee->paid_amount);

        $validator = Validator::make($request->all(), [
            'fee_ids'         => 'required|string',
            'amount'          => "required|numeric|min:0.01|max:{$totalBalance}",
            'payment_method'  => 'required|in:cash,cheque,bank_transfer,easypaisa,jazzcash,card',
            'bank_account_id' => 'required_unless:payment_method,cash|nullable|exists:bank_accounts,id',
            'reference_no'    => 'nullable|string|max:100',
            'note'            => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $isCash      = $request->payment_method === 'cash';
        $remaining   = (float) $request->amount;
        $updatedFees = [];
        $groupId     = null;

        DB::transaction(function () use ($fees, $request, $isCash, &$remaining, &$updatedFees, &$groupId) {
            // 🔥 Cash account ID ek hi baar nikal lo (loop se pehle)
            $cashAccountId = BankAccount::where('bank_type', 'cash')->value('id');

            // Admission Fees ko hamesha priority — phir baaki due-date se
            $sortedFees = $fees->sortBy(function ($fee) {
                $isAdmission = strtolower($fee->feeType->name ?? '') === 'admission fees';
                return $isAdmission ? '0000-00-00' : $fee->due_date;
            });

            foreach ($sortedFees as $fee) {
                if ($remaining <= 0) {
                    break;
                }

                $feeBalance = $fee->amount - $fee->discount - $fee->paid_amount;
                if ($feeBalance <= 0) {
                    continue;
                }

                $payNow = min($remaining, $feeBalance);

                $fee->paid_amount += $payNow;

                $payable = $fee->amount - $fee->discount;
                $fee->status = $fee->paid_amount >= $payable
                    ? 'paid'
                    : ($fee->paid_amount > 0 ? 'partial' : 'unpaid');
                $fee->save();

                // 🔥 bank_account_id ab cash ke liye bhi set hoga
                $bankAccountId = $isCash ? $cashAccountId : $request->bank_account_id;

                $transaction = Transaction::create([
                    'payment_group_id' => $groupId,
                    'bank_account_id'  => $bankAccountId,
                    'user_id'          => auth()->id(),
                    'student_fee_id'   => $fee->id,
                    'type'             => 'income',
                    'category'         => 'fee',
                    'amount'           => $payNow,
                    'payment_method'   => $request->payment_method,
                    'reference_no'     => $request->reference_no,
                    'transaction_date' => now(),
                    'note'             => $request->note,
                ]);

                if ($groupId === null) {
                    $groupId = $transaction->id;
                    $transaction->payment_group_id = $groupId;
                    $transaction->save();
                } else {
                    $transaction->payment_group_id = $groupId;
                    $transaction->save();
                }

                if ($bankAccountId) {
                    BankAccount::where('id', $bankAccountId)
                        ->increment('current_balance', $payNow);
                }

                $remaining -= $payNow;

                $updatedFees[] = [
                    'id'          => $fee->id,
                    'paid_amount' => number_format($fee->paid_amount, 2),
                    'balance'     => number_format($fee->amount - $fee->discount - $fee->paid_amount, 2),
                    'status'      => $fee->status,
                    'receipt_id'     => $groupId, // 👈 ye add karo — bulk case mein group id hi receipt id hai
                ];
            }
        });

        return response()->json([
            'message'    => 'Payment collected successfully.',
            'fees'       => $updatedFees,
            'receipt_id' => $groupId,
        ]);
    }
    // End Method

    // Fee ko N/A mark karna (student ye fee type nahi le raha)
    public function exclude(Request $request, $studentFeeId)
    {
        $fee = StudentFees::findOrFail($studentFeeId);

        if ($fee->status === 'paid') {
            return response()->json(['error' => 'Cannot exclude a paid fee.'], 422);
        }

        if ($fee->paid_amount > 0) {
            return response()->json(['error' => 'Partial payment already made, please adjust/refund first.'], 422);
        }

        $fee->update([
            'status'          => 'excluded',
            'excluded_by'     => auth()->id(),
            'excluded_reason' => $request->input('reason'),
        ]);

        return response()->json(['message' => 'Fee marked as N/A successfully.']);
    }
    // End Method

    // Excluded fee ko wapis unpaid state me le aana
    public function reassign($studentFeeId)
    {
        $fee = StudentFees::findOrFail($studentFeeId);

        if ($fee->status !== 'excluded') {
            return response()->json(['error' => 'This fee is not excluded.'], 422);
        }

        $fee->update([
            'status'          => 'unpaid',
            'excluded_by'     => null,
            'excluded_reason' => null,
        ]);

        return response()->json(['message' => 'Fee reassigned successfully.']);
    }
    // End Method

    public function receipt($transactionId)
    {
        $transaction = Transaction::with([
            'studentFee.student.currentEnrollment.schoolClass',
            'studentFee.student.currentEnrollment.section',
            'studentFee.feeType',
            'bankAccount',
            'user',
        ])->findOrFail($transactionId);

        $transactions = $transaction->payment_group_id
            ? Transaction::with(['studentFee.feeType', 'bankAccount', 'user'])
            ->where('payment_group_id', $transaction->payment_group_id)
            ->orderBy('id')
            ->get()
            : collect([$transaction]);

        // 👇 NAYA — har transaction ke waqt ka sahi (snapshot) balance
        $balanceAsOf = [];
        foreach ($transactions as $txn) {
            $paidUpToThis = Transaction::where('student_fee_id', $txn->studentFee->id)
                ->where('id', '<=', $txn->id)
                ->sum('amount');

            $feeAmount   = $txn->studentFee->amount ?? 0;
            $feeDiscount = $txn->studentFee->discount ?? 0;

            $balanceAsOf[$txn->id] = max($feeAmount - $feeDiscount - $paidUpToThis, 0);
        }

        $rowCount   = $transactions->count();
        $baseHeight = 230;
        $rowHeight  = 16;
        $pdfHeight  = $baseHeight + ($rowCount * $rowHeight);

        $pdf = Pdf::loadView('admin.fee_collection.receipt_pdf', [
            'transactions' => $transactions,
            'student'      => $transaction->studentFee->student,
            'balanceAsOf'  => $balanceAsOf, // 👈 naya
        ])->setPaper([0, 0, 842, $pdfHeight]);

        return $pdf->stream('receipt-' . $transaction->id . '.pdf');
    }
    // End Method

    public function report(Request $request)
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to   = $request->query('to', now()->toDateString());

        $todayTotal = Transaction::where('type', 'income')->where('category', 'fee')
            ->whereDate('transaction_date', now()->toDateString())->sum('amount');

        $monthTotal = Transaction::where('type', 'income')->where('category', 'fee')
            ->whereBetween('transaction_date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])->sum('amount');

        $rangeQuery = Transaction::with(['studentFee.feeType', 'studentFee.student' => fn($q) => $q->withTrashed(), 'bankAccount', 'user'])
            ->where('type', 'income')->where('category', 'fee')
            ->whereBetween('transaction_date', [$from, $to]);

        $rangeTotal = (clone $rangeQuery)->sum('amount');

        $modeBreakdown = (clone $rangeQuery)
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')->pluck('total', 'payment_method');

        $rawTransactions = $rangeQuery->orderByDesc('transaction_date')->orderByDesc('id')->get();

        // Un students ki IDs jinki is range me payment aayi hai
        $paidStudentIds = $rawTransactions->pluck('studentFee.student_id')->unique();

        // Un students ki SAARI pending/partial fees ek sath fetch (N+1 se bachne ke liye)
        $pendingFeesByStudent = StudentFees::with('feeType')
            ->whereIn('student_id', $paidStudentIds)
            ->whereIn('status', ['unpaid', 'partial'])
            ->get()
            ->map(function ($fee) {
                $fee->pending_amount = $fee->amount - $fee->discount - $fee->paid_amount;
                return $fee;
            })
            ->groupBy('student_id');

        // Student_id se group — ek student ki ek hi row (poori date range ke andar)
        $transactions = $rawTransactions
            ->groupBy(fn($txn) => $txn->studentFee->student_id)
            ->map(function ($group) use ($pendingFeesByStudent) {
                $first = $group->first();
                $studentId = $first->studentFee->student_id;
                return (object) [
                    'student'    => $first->studentFee->student,
                    'amount'     => $group->sum('amount'),
                    'modes'      => $group->pluck('payment_method')->unique()->map(fn($m) => str_replace('_', ' ', $m))->implode(', '),
                    'fee_types'  => $group->pluck('studentFee.feeType.name')->filter()->unique()->implode('|'),
                    'last_date'  => $group->max('transaction_date'),
                    'detail'     => $group->values(),
                    'pending'    => $pendingFeesByStudent->get($studentId, collect())->values(),
                ];
            })->values();

        $feeTypes = FeeType::orderBy('name')->pluck('name', 'id');

        // Due / Outstanding — due_date range filter
        $dueFrom = $request->query('due_from');
        $dueTo   = $request->query('due_to');

        $dueQuery = StudentFees::with(['student' => fn($q) => $q->withTrashed(), 'feeType'])->whereIn('status', ['unpaid', 'partial']);

        if ($dueFrom && $dueTo) {
            $dueQuery->whereBetween('due_date', [$dueFrom, $dueTo]);
        }

        $rawDueFees = $dueQuery->get()->map(function ($fee) {
            $fee->pending_amount = $fee->amount - $fee->discount - $fee->paid_amount;
            $fee->is_overdue = \Carbon\Carbon::parse($fee->due_date)->lt(today());
            return $fee;
        });

        // Due wale students ki IDs
        $dueStudentIds = $rawDueFees->pluck('student_id')->unique();

        // In students ki SAARI fees (paid + unpaid) — modal me full breakdown dikhane ke liye
        $allFeesByStudent = StudentFees::with('feeType')
            ->whereIn('student_id', $dueStudentIds)
            ->get()
            ->map(function ($fee) {
                $fee->pending_amount = $fee->amount - $fee->discount - $fee->paid_amount;
                $fee->is_overdue = \Carbon\Carbon::parse($fee->due_date)->lt(today());
                return $fee;
            })
            ->groupBy('student_id');

        $dueByStudent = $rawDueFees->groupBy('student_id')->map(function ($group, $studentId) use ($allFeesByStudent) {
            $first = $group->first();
            $allFees = $allFeesByStudent->get($studentId, collect());
            return (object) [
                'student'          => $first->student,
                'total_payable'    => $allFees->sum(fn($f) => $f->amount - $f->discount),
                'total_pending'    => $group->sum('pending_amount'),
                'is_overdue'       => $group->contains('is_overdue', true),
                'nearest_due_date' => $group->min('due_date'),
                'fee_types'        => $group->pluck('feeType.name')->filter()->unique()->implode('|'),
                'detail'           => $allFees->values(), // paid + unpaid dono
            ];
        })->values();

        // Totals hamesha SAB unpaid/partial fees pe based hon (filter se independent)
        $allDueFees = StudentFees::whereIn('status', ['unpaid', 'partial'])->get()->map(function ($fee) {
            $fee->pending_amount = $fee->amount - $fee->discount - $fee->paid_amount;
            $fee->is_overdue = \Carbon\Carbon::parse($fee->due_date)->lt(today());
            return $fee;
        });

        $totalOutstanding = $allDueFees->sum('pending_amount');
        $totalOverdue     = $allDueFees->where('is_overdue', true)->sum('pending_amount');
        $overdueCount     = $allDueFees->where('is_overdue', true)->count();
        $partialCount     = $allDueFees->where('status', 'partial')->count();

        return view('admin.fee_collection.report', compact(
            'todayTotal',
            'monthTotal',
            'rangeTotal',
            'modeBreakdown',
            'transactions',
            'from',
            'to',
            'dueByStudent',
            'totalOutstanding',
            'totalOverdue',
            'overdueCount',
            'partialCount',
            'feeTypes',
            'dueFrom',
            'dueTo'
        ));
    }
    // End Method
}
