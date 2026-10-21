<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\BillingSettingRequest;
use App\Models\AcademicYear;
use App\Models\BillingSetting;
use App\Services\BillingService;
use App\Services\SalarySlipService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BillingSettingController extends Controller
{
    protected BillingService $billing;
    protected SalarySlipService $salary;

    public function __construct(BillingService $billing, SalarySlipService $salary)
    {
        $this->billing = $billing;
        $this->salary  = $salary;
    }

    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        $academicYearId = $request->query('academic_year_id');
        if (!$academicYears->contains('id', (int) $academicYearId)) {
            $academicYearId = optional($academicYears->firstWhere('is_current', 1))->id ?: optional($academicYears->first())->id;
        }

        $academicYear   = $academicYears->firstWhere('id', (int) $academicYearId);
        $billingSetting = $academicYear
            ? BillingSetting::firstOrNew(['academic_year_id' => $academicYear->id])
            : new BillingSetting();

        // Naye year ke liye defaults (table defaults sirf save ke baad dikhte hain)
        if (!$billingSetting->exists) {
            $billingSetting->fill([
                'fee_generate_day'    => 1,
                'fee_due_day'         => 10,
                'salary_generate_day' => 1,
            ]);
        }

        $currentMonth   = now()->format('Y-m');
        $feeMonths      = $academicYear ? $this->billing->billingMonths($academicYear) : [];
        // Salary sirf poore hue mahine ki
        $salaryMonths   = array_values(array_filter($feeMonths, fn($m) => $m < $currentMonth));
        $feeMonthDefault    = in_array($currentMonth, $feeMonths, true) ? $currentMonth : end($feeMonths);
        $salaryMonthDefault = end($salaryMonths);

        return view('admin.billing_settings.index', compact(
            'academicYears',
            'academicYear',
            'billingSetting',
            'feeMonths',
            'salaryMonths',
            'feeMonthDefault',
            'salaryMonthDefault'
        ));
    }

    public function update(BillingSettingRequest $request)
    {
        $data = $request->validated();

        $data['auto_fees']   = $request->boolean('auto_fees');
        $data['auto_salary'] = $request->boolean('auto_salary');

        // Auto on aur from-month khali ho to current month: catch-up isse pehle ke mahine nahi banata
        if ($data['auto_fees'] && empty($data['fees_from_month'])) {
            $data['fees_from_month'] = now()->format('Y-m');
        }
        if ($data['auto_salary'] && empty($data['salary_from_month'])) {
            $data['salary_from_month'] = now()->format('Y-m');
        }

        $billingSetting = BillingSetting::firstOrNew(['academic_year_id' => $data['academic_year_id']]);
        $billingSetting->fill($data)->save();

        return redirect()->route('billing_settings.index', ['academic_year_id' => $billingSetting->academic_year_id])
            ->with('success', 'Billing settings saved successfully.');
    }

    /**
     * Generate Now: ek type (fees / salary) ka ek mahina
     */
    public function generateNow(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'type'             => 'required|in:fees,salary',
            'month'            => 'required|date_format:Y-m',
        ], [
            'academic_year_id.required' => 'The Academic Year field is required.',
            'type.required'             => 'The Type field is required.',
            'month.required'            => 'The Month field is required.',
            'month.date_format'         => 'The Month field must be a valid month.',
        ]);

        $billingSetting = BillingSetting::with('academicYear')->where('academic_year_id', $request->academic_year_id)->first();
        if (!$billingSetting) {
            return response()->json(['message' => 'Save the billing settings for this academic year first.'], 422);
        }

        if (!in_array($request->month, $this->billing->billingMonths($billingSetting->academicYear), true)) {
            return response()->json(['message' => 'This month is outside the selected academic year.'], 422);
        }

        if ($request->type === 'salary' && $request->month >= now()->format('Y-m')) {
            return response()->json([
                'message' => 'Salary slips can only be generated for a completed month (before ' . now()->format('M Y') . ').',
            ], 422);
        }

        $row = $this->billing->runMonth($billingSetting, $request->type, $request->month);

        return response()->json(['message' => $this->resultMessage([$row])]);
    }

    /**
     * Catch up: from-month se ab tak ke due mahine (ek type)
     */
    public function catchUp(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'type'             => 'required|in:fees,salary',
        ], [
            'academic_year_id.required' => 'The Academic Year field is required.',
            'type.required'             => 'The Type field is required.',
        ]);

        $billingSetting = BillingSetting::with('academicYear')->where('academic_year_id', $request->academic_year_id)->first();
        if (!$billingSetting) {
            return response()->json(['message' => 'Save the billing settings for this academic year first.'], 422);
        }

        $fromMonth = $request->type === 'fees' ? $billingSetting->fees_from_month : $billingSetting->salary_from_month;
        if (!$fromMonth) {
            return response()->json(['message' => 'Set the From Month first (turn the auto switch on and save).'], 422);
        }

        $rows = $this->billing->catchUp($billingSetting, $request->type);
        if (!$rows) {
            return response()->json(['message' => 'Nothing due: no month is waiting to be generated.']);
        }

        return response()->json(['message' => $this->resultMessage($rows)]);
    }

    /**
     * Unmarked staff attendance days of a month (Generate Now salary warning, JSON)
     */
    public function unmarkedDays(Request $request)
    {
        $request->validate(['month' => 'required|date_format:Y-m'], [
            'month.required'    => 'The Month field is required.',
            'month.date_format' => 'The Month field must be a valid month.',
        ]);

        if ($request->month >= now()->format('Y-m')) {
            return response()->json([
                'message' => 'Salary slips can only be generated for a completed month (before ' . now()->format('M Y') . ').',
            ], 422);
        }

        $summary = $this->salary->unmarkedSummary($request->month);

        return response()->json([
            'label'    => Carbon::parse($request->month . '-01')->format('F'),
            'days'     => $summary['days'],
            'headline' => $summary['headline'],
            'dates'    => $summary['dates'],
        ]);
    }

    private function resultMessage(array $rows): string
    {
        $created = array_sum(array_column($rows, 'created'));
        $skipped = array_sum(array_column($rows, 'skipped'));
        $noun    = $rows[0]['type'] === 'fees' ? 'fee row' : 'salary slip';
        $period  = count($rows) === 1
            ? Carbon::parse($rows[0]['month'] . '-01')->format('F Y')
            : count($rows) . ' months';

        $message = $created . ' ' . $noun . ($created === 1 ? '' : 's') . ' created, ' . $skipped . ' skipped (' . $period . ').';

        // Dates kabhi toastr mein nahi: sirf ek chhoti line (full list log mein)
        $notes = array_values(array_filter(array_column($rows, 'note')));
        if (count($notes) === 1 && count($rows) === 1) {
            $message .= ' ' . $notes[0];
        } elseif ($notes) {
            $message .= ' Attendance not marked in ' . count($notes) . ' month(s).';
        }

        return $message;
    }
}
