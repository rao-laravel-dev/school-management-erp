<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class FinanceReportController extends Controller
{
     public function index(Request $request)
    {
        $from = $request->from_date ?? now()->startOfMonth()->toDateString();
        $to   = $request->to_date ?? now()->toDateString();

        $baseQuery = Transaction::whereBetween('transaction_date', [$from, $to]);

        // Summary totals
        $totalFeeCollection = (clone $baseQuery)->where('category', 'fee')->where('type', 'income')->sum('amount');
        $totalSalaryPaid    = (clone $baseQuery)->where('category', 'salary')->sum('amount');
        $totalExpenses      = (clone $baseQuery)->where('category', 'expense')->sum('amount');
        $netCashFlow        = $totalFeeCollection - $totalSalaryPaid - $totalExpenses;

        // Detailed lists per tab
        $feeCollections = (clone $baseQuery)->where('category', 'fee')->where('type', 'income')
            ->with(['studentFee.student', 'studentFee.feeType', 'bankAccount', 'user'])
            ->orderByDesc('transaction_date')->get();

        $salaryPayments = (clone $baseQuery)->where('category', 'salary')
            ->with(['salarySlip.user', 'bankAccount', 'user'])
            ->orderByDesc('transaction_date')->get();

        $expensePayments = (clone $baseQuery)->where('category', 'expense')
            ->with(['expense.category', 'bankAccount', 'user'])
            ->orderByDesc('transaction_date')->get();

        return view('admin.finance_report.index', compact(
            'from', 'to',
            'totalFeeCollection', 'totalSalaryPaid', 'totalExpenses', 'netCashFlow',
            'feeCollections', 'salaryPayments', 'expensePayments'
        ));
    }
}
