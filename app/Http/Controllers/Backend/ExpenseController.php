<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with(['category', 'bankAccount'])->latest()->get();
        $categories = ExpenseCategory::orderBy('name')->get();
        $bankAccounts = BankAccount::orderBy('bank_name')->get();

        return view('admin.expenses.index', compact('expenses', 'categories', 'bankAccounts'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title'                => 'required|string|max:255',
            'description'          => 'nullable|string',
            'amount'               => 'required|numeric|min:0.01',
            'expense_date'         => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Expense::create([
            'expense_category_id' => $request->expense_category_id,
            'title'                => $request->title,
            'description'          => $request->description,
            'amount'               => $request->amount,
            'expense_date'         => $request->expense_date,
            'status'               => 'pending',
            'created_by'           => auth()->id(),
        ]);

        return response()->json(['message' => 'Expense added successfully.']);
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->status === 'paid') {
            return response()->json(['message' => 'Paid expense edit nahi ho sakta.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title'                => 'required|string|max:255',
            'description'          => 'nullable|string',
            'amount'               => 'required|numeric|min:0.01',
            'expense_date'         => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expense->update([
            'expense_category_id' => $request->expense_category_id,
            'title'                => $request->title,
            'description'          => $request->description,
            'amount'               => $request->amount,
            'expense_date'         => $request->expense_date,
        ]);

        return response()->json(['message' => 'Expense updated successfully.']);
    }

    // 🔽 Ye method expense ko "Paid" mark karta hai + Transaction bana kar bank balance minus karta hai
    public function pay(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->status === 'paid') {
            return response()->json(['message' => 'Ye expense pehle hi paid hai.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payment_method'  => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bankAccount = BankAccount::findOrFail($request->bank_account_id);

        if ($bankAccount->current_balance < $expense->amount) {
            return response()->json(['message' => 'Is account me itni balance nahi hai.'], 422);
        }

        DB::transaction(function () use ($expense, $bankAccount, $request) {
            // Expense update
            $expense->update([
                'bank_account_id' => $bankAccount->id,
                'payment_method'  => $request->payment_method,
                'status'          => 'paid',
            ]);

            // Transaction record
            Transaction::create([
                'bank_account_id' => $bankAccount->id,
                'user_id'         => auth()->id(),
                'expense_id'      => $expense->id,
                'type'            => 'expense',
                'category'        => 'expense',
                'amount'          => $expense->amount,
                'payment_method'  => $request->payment_method,
                'transaction_date' => now()->toDateString(),
                'note'            => $expense->title,
            ]);

            // Bank balance minus
            $bankAccount->decrement('current_balance', $expense->amount);
        });

        return response()->json(['message' => 'Expense paid successfully.']);
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->status === 'paid') {
            return response()->json(['message' => 'Paid expense delete nahi ho sakta.'], 422);
        }

        $expense->delete();
        return response()->json(['message' => 'Expense deleted successfully.']);
    }
}
