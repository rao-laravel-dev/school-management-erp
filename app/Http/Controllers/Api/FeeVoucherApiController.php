<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeeVoucher;
use App\Services\FeeVoucherService;
use Illuminate\Http\Request;

// Portal fee vouchers for the mobile app (read-only, own data only; same rules as the web portals)
class FeeVoucherApiController extends Controller
{
    // Parent: vouchers of own children (optional ?student_id= of one own child)
    public function parentVouchers(Request $request)
    {
        $request->validate(['student_id' => 'nullable|integer']);

        $childIds = $this->childIds();

        if ($request->filled('student_id')) {
            abort_unless($childIds->contains((int) $request->student_id), 404);
            $childIds = collect([(int) $request->student_id]);
        }

        $vouchers = FeeVoucher::with('student')
            ->whereIn('student_id', $childIds)
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('period')
            ->orderBy('student_id')
            ->get();

        return response()->json([
            'vouchers' => $vouchers->map(fn($v) => $this->voucherRow($v, 'api.parent.fees.vouchers.pdf'))->values(),
        ]);
    }
    // End Method

    // Parent: voucher PDF (404 for another parent's child or a cancelled voucher)
    public function parentVoucherPdf($voucher)
    {
        $voucher = FeeVoucher::whereIn('student_id', $this->childIds())
            ->where('status', '!=', 'cancelled')
            ->findOrFail($voucher);

        return app(FeeVoucherService::class)->pdf(collect([$voucher]))->stream('fee-voucher-' . $voucher->voucher_no . '.pdf');
    }
    // End Method

    // Student: own vouchers
    public function studentVouchers()
    {
        $student = auth()->user()->studentProfile;
        abort_if(!$student, 404);

        $vouchers = FeeVoucher::with('student')
            ->where('student_id', $student->id)
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('period')
            ->get();

        return response()->json([
            'vouchers' => $vouchers->map(fn($v) => $this->voucherRow($v, 'api.student.fees.vouchers.pdf'))->values(),
        ]);
    }
    // End Method

    // Student: own voucher PDF (404 for another student's voucher or a cancelled one)
    public function studentVoucherPdf($voucher)
    {
        $student = auth()->user()->studentProfile;
        abort_if(!$student, 404);

        $voucher = FeeVoucher::where('student_id', $student->id)
            ->where('status', '!=', 'cancelled')
            ->findOrFail($voucher);

        return app(FeeVoucherService::class)->pdf(collect([$voucher]))->stream('fee-voucher-' . $voucher->voucher_no . '.pdf');
    }
    // End Method

    // students.id of the logged-in parent's children (404 when the user has no parent profile)
    private function childIds()
    {
        $parent = auth()->user()->parentProfile;
        abort_if(!$parent, 404);

        return $parent->students()->pluck('students.id');
    }

    // One JSON shape for both roles
    private function voucherRow(FeeVoucher $v, string $pdfRoute): array
    {
        return [
            'id'                => $v->id,
            'voucher_no'        => $v->voucher_no,
            'student_id'        => $v->student_id,
            'student_name'      => $v->student->full_name,
            'admission_no'      => $v->student->admission_no,
            'period'            => $v->period,
            'issue_date'        => $v->issue_date ? $v->issue_date->format('Y-m-d') : null,
            'due_date'          => $v->due_date ? $v->due_date->format('Y-m-d') : null,
            'fee_amount'        => (float) $v->fee_amount,
            'discount'          => (float) $v->discount,
            'arrears'           => (float) $v->arrears,
            'late_fee'          => (float) $v->late_fee,
            'amount_before_due' => (float) $v->amount_before_due,
            'amount_after_due'  => (float) $v->amount_after_due,
            'status'            => $v->status,
            'paid_at'           => $v->paid_at ? $v->paid_at->toDateTimeString() : null,
            'pdf_url'           => route($pdfRoute, $v->id),
        ];
    }
}
