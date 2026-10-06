<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SalarySlip;
use App\Models\User;
use App\Services\SalarySlipService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class SalarySlipController extends Controller
{
    protected SalarySlipService $service;
 
    public function __construct(SalarySlipService $service)
    {
        $this->service = $service;
    }
 
    /**
     * Listing page — filterable by month, role, status
     */
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $role = $request->input('role');
        $status = $request->input('status');
 
        $slips = SalarySlip::with(['user.roles', 'user.teacher', 'user.accountant', 'user.receptionist'])
            ->where('month', $month)
            ->when($role, function ($q) use ($role) {
                $q->whereHas('user.roles', fn($r) => $r->where('name', $role));
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20);
 
        $summary = [
            'total'  => SalarySlip::where('month', $month)->count(),
            'pending' => SalarySlip::where('month', $month)->where('status', 'pending')->count(),
            'paid'   => SalarySlip::where('month', $month)->where('status', 'paid')->count(),
            'total_amount' => SalarySlip::where('month', $month)->sum('net_salary'),
        ];
 
        $roles = Role::whereNotIn('name', [
            'superadmin',
            'admin',
            'parent',
            'student',
            'user',
        ])->pluck('name');
 
        return view('admin.salary_slips.index', compact('slips', 'summary', 'roles', 'month', 'role', 'status'));
    }
 
    /**
     * Bulk generate form
     */
    public function create(Request $request)
    {
        $roles = Role::whereNotIn('name', [
            'superadmin',
            'admin',
            'parent',
            'student',
            'user',
        ])->pluck('name');
 
        $excludedRoles = ['superadmin', 'admin', 'parent', 'student', 'user'];
        $staff = User::whereHas('roles', fn($q) => $q->whereNotIn('name', $excludedRoles))
            ->with('roles', 'salaries')
            ->get();
 
        // Prefill from the Salary Slips index page's current filters (role/month),
        // if the admin arrived here via that page's "Generate" button.
        $prefillMonth = $request->query('month');
        $prefillRole  = $request->query('role');
 
        return view('admin.salary_slips.create', compact('roles', 'staff', 'prefillMonth', 'prefillRole'));
    }
 
    /**
     * Handle bulk / single generation
     */
    public function generate(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'mode'  => 'required|in:all,role,selected',
            'role'  => 'nullable|required_if:mode,role|string',
            'staff_ids'   => 'nullable|required_if:mode,selected|array',
            'staff_ids.*' => 'exists:users,id',
        ]);
 
        $month = $request->month;
 
        if ($request->mode === 'selected') {
            $generated = [];
            $skipped = [];
            foreach ($request->staff_ids as $userId) {
                $user = User::find($userId);
                try {
                    $generated[] = $this->service->generate($user, $month);
                } catch (\Exception $e) {
                    $skipped[] = ['user' => $user->name, 'reason' => $e->getMessage()];
                }
            }
        } else {
            $role = $request->mode === 'role' ? $request->role : null;
            $result = $this->service->generateBulk($month, $role);
            $generated = $result['generated'];
            $skipped = $result['skipped'];
        }
 
        $message = count($generated) . ' salary slip(s) generated successfully.';
        if (count($skipped) > 0) {
            $message .= ' ' . count($skipped) . ' skipped (already generated or missing salary structure).';
        }
 
        return redirect()->route('salary_slips.index', ['month' => $month])
            ->with('success', $message)
            ->with('skipped', $skipped);
    }
 
    /**
     * Single slip detail / payslip view
     */
    public function show($id)
    {
        $slip = SalarySlip::with(['user.roles', 'user.teacher', 'user.accountant', 'generatedBy', 'paidBy', 'transaction.bankAccount'])
            ->findOrFail($id);
 
        return view('admin.salary_slips.show', compact('slip'));
    }
 
    /**
     * Edit pending slip (adjust manual deduction / allowance)
     */
    public function edit($id)
    {
        $slip = SalarySlip::findOrFail($id);
 
        if ($slip->status === 'paid') {
            return redirect()->route('salary_slips.show', $slip->id)
                ->with('error', 'Paid slips cannot be edited.');
        }
 
        return view('admin.salary_slips.edit', compact('slip'));
    }
 
    public function update(Request $request, $id)
    {
        $slip = SalarySlip::findOrFail($id);
 
        if ($slip->status === 'paid') {
            return redirect()->route('salary_slips.show', $slip->id)
                ->with('error', 'Paid slips cannot be edited.');
        }
 
        $request->validate([
            'allowance'        => 'required|numeric|min:0',
            'manual_deduction' => 'required|numeric|min:0',
        ]);
 
        $grossSalary = $slip->basic_salary + $request->allowance;
        $totalDeduction = $request->manual_deduction + $slip->attendance_deduction + $slip->advance_deduction;

        // Total deductions gross se zyada nahi ho sakti (net negative nahi), save nahi hota
        if (round($totalDeduction, 2) > round($grossSalary, 2)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'manual_deduction' => 'Total deductions cannot exceed gross salary',
            ]);
        }

        $netSalary = $grossSalary - $totalDeduction;
 
        $slip->update([
            'allowance'        => $request->allowance,
            'manual_deduction' => $request->manual_deduction,
            'deduction'        => $totalDeduction,
            'net_salary'       => $netSalary,
        ]);
 
        return redirect()->route('salary_slips.show', $slip->id)
            ->with('success', 'Salary slip updated successfully.');
    }
 
    /**
     * Mark as paid — creates transaction record
     */
    public function pay(Request $request, $id)
    {
        $slip = SalarySlip::findOrFail($id);
 
        $request->validate([
            'bank_account_id' => 'required_unless:payment_method,cash|nullable|exists:bank_accounts,id',
            'payment_method'  => 'required|in:cash,cheque,bank_transfer,easypaisa,jazzcash,card',
            'reference_no'    => 'nullable|string',
        ]);
 
        try {
            $this->service->pay($slip, $request->only(['bank_account_id', 'payment_method', 'reference_no']));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
 
        return redirect()->route('salary_slips.show', $slip->id)
            ->with('success', 'Salary slip marked as paid.');
    }
 
    /**
     * Delete pending slip only
     */
    public function destroy($id)
    {
        $slip = SalarySlip::findOrFail($id);
 
        if ($slip->status === 'paid') {
            return back()->with('error', 'Paid slips cannot be deleted.');
        }
 
        // Refund the advance balance since this slip is being deleted
        if ($slip->advance_deduction > 0 && $slip->user->salaries) {
            $slip->user->salaries->increment('advance_balance', $slip->advance_deduction);
        }
 
        $slip->delete();
 
        return redirect()->route('salary_slips.index')
            ->with('success', 'Salary slip deleted.');
    }
}
