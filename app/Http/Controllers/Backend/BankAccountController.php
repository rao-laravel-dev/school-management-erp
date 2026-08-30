<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BankAccountController extends Controller
{
    public function index()
    {
        $bankAccounts = BankAccount::latest()->get();

        $totalFeesCollected = Transaction::where('type', 'income')->where('category', 'fee')->sum('amount');
        $totalSalaryExpense = Transaction::where('type', 'expense')->where('category', 'salary')->sum('amount');
        $totalOtherExpense  = Transaction::where('type', 'expense')->where('category', 'expense')->sum('amount');
        $netFunds = $bankAccounts->sum('current_balance');

        return view('admin.bank_accounts.index', compact(
            'bankAccounts',
            'totalFeesCollected',
            'totalSalaryExpense',
            'totalOtherExpense',
            'netFunds'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_name'       => 'required|string|max:255',
            'bank_type'       => 'required|in:commercial,microfinance_wallet,cash',
            'account_title'   => 'required|string|max:255',
            'account_number'  => 'required|string|max:255',
            'iban'            => 'nullable|string|max:50',
            'branch_name'     => 'nullable|string|max:255',
            'branch_code'     => 'nullable|string|max:50',
            'current_balance' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Only one "Cash in Hand" account is allowed
        if ($request->bank_type === 'cash') {
            $exists = BankAccount::where('bank_type', 'cash')->exists();

            if ($exists) {
                return response()->json([
                    'errors' => ['bank_type' => ['Only one "Cash in Hand" account is allowed.']]
                ], 422);
            }
        }

        BankAccount::create($request->only([
            'bank_name',
            'bank_type',
            'account_title',
            'account_number',
            'iban',
            'branch_name',
            'branch_code',
            'current_balance'
        ]));

        return response()->json(['message' => 'Bank account added successfully.']);
    }
    // End Method

    public function update(Request $request, $id)
    {
        $bankAccount = BankAccount::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'bank_name'       => 'required|string|max:255',
            'bank_type'       => 'required|in:commercial,microfinance_wallet,cash',
            'account_title'   => 'required|string|max:255',
            'account_number'  => 'required|string|max:255',
            'iban'            => 'nullable|string|max:50',
            'branch_name'     => 'nullable|string|max:255',
            'branch_code'     => 'nullable|string|max:50',
            'current_balance' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Only one "Cash in Hand" account is allowed (excluding this record itself)
        if ($request->bank_type === 'cash') {
            $exists = BankAccount::where('bank_type', 'cash')
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'errors' => ['bank_type' => ['Only one "Cash in Hand" account is allowed.']]
                ], 422);
            }
        }

        $bankAccount->update($request->only([
            'bank_name',
            'bank_type',
            'account_title',
            'account_number',
            'iban',
            'branch_name',
            'branch_code',
            'current_balance'
        ]));

        return response()->json(['message' => 'Bank account updated successfully.']);
    }
    // End Method

    public function destroy($id)
    {
        $bankAccount = BankAccount::findOrFail($id);
        $bankAccount->delete();

        return response()->json(['message' => 'Bank account deleted successfully.']);
    }
}
