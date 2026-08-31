<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayFeeRequest;
use App\Http\Resources\StudentFeeResource;
use App\Models\BankAccount;
use App\Models\StudentFees;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return response()->json([
            'fees' => StudentFeeResource::collection($fees),
        ]);
    }
    // End Method

    // Show single fee record detail
    public function show($studentFeeId)
    {
        $fee = StudentFees::with('feeType', 'transactions')
            ->findOrFail($studentFeeId);

        return response()->json([
            'fee' => new StudentFeeResource($fee),
        ]);
    }
    // End Method

    // Collect payment against one fee record (same logic as web FeeCollectionController@pay)
    public function pay(PayFeeRequest $request, $studentFeeId)
    {
        $fee     = StudentFees::findOrFail($studentFeeId);
        $balance = $fee->amount - $fee->discount - $fee->paid_amount;

        $transaction = null;

        DB::transaction(function () use ($fee, $request, &$transaction) {
            $fee->paid_amount += $request->amount;

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

        if (! $transaction) {
            return response()->json(['message' => 'Transaction failed.'], 500);
        }

        return response()->json([
            'message'        => 'Payment collected successfully.',
            'paid_amount'    => number_format($fee->paid_amount, 2),
            'balance'        => number_format($fee->amount - $fee->discount - $fee->paid_amount, 2),
            'status'         => $fee->status,
            'transaction_id' => $transaction->id,
        ]);
    }
    // End Method
}
