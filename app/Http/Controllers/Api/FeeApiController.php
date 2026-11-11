<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayFeeRequest;
use App\Http\Resources\StudentFeeResource;
use App\Models\BankAccount;
use App\Models\StudentFees;
use App\Models\Transaction;
use App\Services\BillingNotifier;
use App\Services\FeeVoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FeeApiController extends Controller
{
    // List fees (optionally filter by student_id)
    public function index(Request $request)
    {
        $query = StudentFees::with('feeType')
            ->where('status', '!=', 'excluded');

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $fees = $query->orderByDesc('due_date')->get();

        $lateFees = $this->attachLateFees($fees);

        return response()->json([
            'fees'      => StudentFeeResource::collection($fees),
            'late_fees' => $lateFees, // one entry per month: total from here, not from the per-row late_fee
        ]);
    }
    // End Method

    // Show single fee record detail
    public function show($studentFeeId)
    {
        $fee = StudentFees::with('feeType', 'transactions')
            ->findOrFail($studentFeeId);

        $lateFees = $this->attachLateFees(collect([$fee]));

        return response()->json([
            'fee'       => new StudentFeeResource($fee),
            'late_fees' => $lateFees,
        ]);
    }
    // End Method

    // Collect payment against one fee record (same logic as web FeeCollectionController@pay)
    public function pay(PayFeeRequest $request, $studentFeeId)
    {
        $fee     = StudentFees::findOrFail($studentFeeId);
        $balance = $fee->amount - $fee->discount - $fee->paid_amount;

        $transaction = null;
        $groupId     = null;
        $lateCharged = 0.0;

        // Pending late fee of this fee's month is paid first (same rule as web pay())
        $feeService = app(FeeVoucherService::class);
        $month      = $feeService->monthOf($fee);
        $late       = $feeService->pendingLateFees($fee->student_id)->get($fee->student_id, collect())->get($month);
        $lateFee    = $late ? (float) $late->amount : 0.0;

        DB::transaction(function () use ($fee, $request, &$transaction, $feeService, $month, $lateFee, &$groupId, &$lateCharged) {
            if ($lateFee > 0) {
                $lateCharged = $feeService->collectLateFees($fee->student_id, [$month], $request->only(['payment_method', 'bank_account_id', 'reference_no']), $groupId);
                if (abs($lateCharged - $lateFee) > 0.001) {
                    throw ValidationException::withMessages(['amount' => 'Late fee has changed, please reload.']);
                }
            }

            $payAmount = $request->amount - $lateCharged;
            if ($payAmount <= 0) {
                $feeService->refreshUnpaid($fee->student_id); // late fee paid: unpaid voucher drops it
                return; // only the late fee was paid
            }

            $fee->paid_amount += $payAmount;

            $payable = $fee->amount - $fee->discount;
            $fee->status = $fee->paid_amount >= $payable
                ? 'paid'
                : ($fee->paid_amount > 0 ? 'partial' : 'unpaid');
            $fee->save();

            $isCash = $request->payment_method === 'cash';

            $bankAccountId = $isCash
                ? BankAccount::where('bank_type', 'cash')->value('id')
                : $request->bank_account_id;

            $transaction = Transaction::create([
                'payment_group_id' => $groupId,
                'bank_account_id'  => $bankAccountId,
                'user_id'          => auth()->id(),
                'student_fee_id'   => $fee->id,
                'type'             => 'income',
                'category'         => 'fee',
                'amount'           => $payAmount,
                'payment_method'   => $request->payment_method,
                'reference_no'     => $request->reference_no,
                'transaction_date' => now(),
                'note'             => $request->note,
            ]);

            if ($bankAccountId) {
                BankAccount::where('id', $bankAccountId)
                    ->increment('current_balance', $payAmount);
            }

            $feeService->refreshUnpaid($fee->student_id); // unpaid voucher follows the new balance
        });

        if (! $transaction && ! $groupId) {
            return response()->json(['message' => 'Transaction failed.'], 500);
        }

        // Staff API payment: parent gets the normal fee_paid notification (+ admin copy if staff is not admin)
        app(BillingNotifier::class)->feePaid($groupId ?: $transaction->id, auth()->user());

        return response()->json([
            'message'        => 'Payment collected successfully.',
            'paid_amount'    => number_format($fee->paid_amount, 2),
            'balance'        => number_format($fee->amount - $fee->discount - $fee->paid_amount, 2),
            'status'         => $fee->status,
            'late_fee'       => $lateCharged,
            'transaction_id' => $groupId ?: $transaction->id,
        ]);
    }
    // End Method

    // Sets month + pending late fee on each fee (for the resource) and returns the per-month late_fees list
    private function attachLateFees($fees): array
    {
        $service = app(FeeVoucherService::class);
        $pending = $service->pendingLateFees($fees->pluck('student_id')->unique()->all());

        foreach ($fees as $fee) {
            $month = $service->monthOf($fee);
            $late  = $pending->get($fee->student_id, collect())->get($month);

            $fee->setAttribute('fee_month', $month);
            $fee->setAttribute('pending_late_fee', $late ? (float) $late->amount : 0.0);
        }

        // Only months that appear in the returned fees
        $shown = $fees->map(fn($fee) => $fee->student_id . '|' . $fee->fee_month)->unique()->flip();

        $list = [];
        foreach ($pending as $studentId => $byMonth) {
            foreach ($byMonth as $late) {
                if ($shown->has($studentId . '|' . $late->month)) {
                    $list[] = [
                        'student_id'     => (int) $studentId,
                        'month'          => $late->month,
                        'due_date'       => $late->due_date,
                        'amount'         => (float) $late->amount,
                        'student_fee_id' => $late->student_fee_id,
                    ];
                }
            }
        }

        return $list;
    }
    // End Method
}
