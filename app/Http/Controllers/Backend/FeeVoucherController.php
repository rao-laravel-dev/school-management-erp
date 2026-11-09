<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkFeeVoucherRequest;
use App\Http\Requests\GenerateFeeVoucherRequest;
use App\Models\AcademicYear;
use App\Models\FeeVoucher;
use App\Models\Student;
use App\Services\FeeVoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FeeVoucherController extends Controller
{
    // One student, one month: create the voucher or regenerate the unpaid / cancelled one
    public function generate(GenerateFeeVoucherRequest $request, Student $student)
    {
        $voucher = app(FeeVoucherService::class)->generate($student, $request->month);

        return response()->json([
            'success'    => true,
            'message'    => 'Voucher #' . $voucher->voucher_no . ' is ready.',
            'voucher_id' => $voucher->id,
            'pdf_url'    => route('fees.voucher.pdf', $voucher->id),
        ]);
    }
    // End Method

    // Whole class (or one section): one voucher per student, students that cannot get one are returned in `skipped`
    public function bulk(BulkFeeVoucherRequest $request)
    {
        // Same student scope as the Collect Fees class list (current session, approved students)
        $sessionId    = $this->getActiveSessionId();
        $sessionScope = fn($q) => $q->when($sessionId, fn($q2) => $q2->where(fn($w) => AcademicYear::whereCurrentOrGap($w)));

        $students = Student::where('status', 1)
            ->whereHas('enrollments', function ($q) use ($request, $sessionScope) {
                $sessionScope($q);
                $q->where('class_id', $request->class_id);
                if ($request->section_id) {
                    $q->where('section_id', $request->section_id);
                }
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $service = app(FeeVoucherService::class);
        $ids     = [];
        $skipped = [];

        foreach ($students as $student) {
            try {
                $ids[] = $service->generate($student, $request->month)->id;
            } catch (ValidationException $e) {
                // Paid voucher or nothing payable: reported to the user, not an error
                $skipped[] = [
                    'name'   => $student->full_name . ' (' . $student->admission_no . ')',
                    'reason' => collect($e->errors())->flatten()->first(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($ids) . ' voucher(s) ready, ' . count($skipped) . ' skipped.',
            'ids'     => $ids,
            'skipped' => $skipped,
            'pdf_url' => $ids ? route('fees.voucher.print', ['ids' => implode(',', $ids)]) : null,
        ]);
    }
    // End Method

    // Print several vouchers in one PDF (one voucher per page); cancelled ones are left out
    public function print(Request $request)
    {
        $request->validate(['ids' => ['required', 'regex:/^\d+(,\d+)*$/']]);

        $vouchers = FeeVoucher::whereIn('id', explode(',', $request->ids))
            ->where('status', '!=', 'cancelled')
            ->orderBy('voucher_no')
            ->get();

        abort_if($vouchers->isEmpty(), 404);

        return app(FeeVoucherService::class)->pdf($vouchers)->stream('fee-vouchers.pdf');
    }
    // End Method

    // One voucher PDF (read only, nothing is written)
    public function pdf(FeeVoucher $voucher)
    {
        abort_if($voucher->status === 'cancelled', 404);

        return app(FeeVoucherService::class)->pdf(collect([$voucher]))->stream('fee-voucher-' . $voucher->voucher_no . '.pdf');
    }
    // End Method

    // Manual cancel: a paid voucher can never be cancelled (checked here, not only by hiding the button)
    public function cancel(FeeVoucher $voucher)
    {
        // Re-read under a lock: a payment may have marked it paid a moment ago
        return DB::transaction(function () use ($voucher) {
            $voucher = FeeVoucher::lockForUpdate()->findOrFail($voucher->id);

            if ($voucher->status === 'paid') {
                return response()->json(['success' => false, 'message' => 'A paid voucher cannot be cancelled.'], 422);
            }

            if ($voucher->status === 'cancelled') {
                return response()->json(['success' => false, 'message' => 'This voucher is already cancelled.'], 422);
            }

            $voucher->update(['status' => 'cancelled']);

            return response()->json(['success' => true, 'message' => 'Voucher #' . $voucher->voucher_no . ' cancelled.']);
        });
    }
    // End Method
}
