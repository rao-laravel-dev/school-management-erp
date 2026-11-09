@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Fees Collection</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('fees.collect.index') }}">Collect Fees</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $student->full_name }}</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Student Information -->
<div class="card radius-10 mb-3">
    <div class="card-body d-flex align-items-start flex-wrap">
        <div class="me-4">
            <img src="{{ $student->photo_url }}"
                alt="{{ $student->first_name }}"
                class="rounded-3"
                style="width: 160px; height: 160px; object-fit: cover; border-radius: 12px !important; border: 1px solid #dee2e6; padding: 3px; background: #fff;">
        </div>

        <div class="flex-grow-1 row">
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td class="fw-bold ps-0 py-1" style="width: 140px;">Name</td>
                        <td class="py-1 px-3">{{ $student->first_name }} {{ $student->last_name }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td class="fw-bold ps-0 py-1">Admission No</td>
                        <td class="py-1 px-3">{{ $student->admission_no }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td class="fw-bold ps-0 py-1">Admission Date</td>
                        <td class="py-1 px-3">{{ $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('d-m-Y') : '-' }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td class="fw-bold ps-0 py-1">Date of Birth</td>
                        <td class="py-1 px-3">{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d-m-Y') : '-' }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td class="fw-bold ps-0 py-1">Gender</td>
                        <td class="py-1 px-3">{{ ucfirst($student->gender) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td class="fw-bold ps-0 py-1">Blood Group</td>
                        <td class="py-1 px-3">{{ $student->blood_group ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold ps-0 py-1">Category</td>
                        <td class="py-1 px-3">
                            @if($student->category)
                            <span class="badge bg-success text-dark">{{ $student->category->name }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td class="fw-bold ps-0 py-1" style="width: 140px;">Class</td>
                        <td class="py-1 px-3">{{ $student->currentEnrollment->schoolClass->name ?? '-' }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td class="fw-bold ps-0 py-1">Section</td>
                        <td class="py-1 px-3">{{ $student->currentEnrollment->section->name ?? '-' }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td class="fw-bold ps-0 py-1">Academic Year</td>
                        <td class="py-1 px-3">{{ $student->currentEnrollment->academicYear->name ?? '-' }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td class="fw-bold ps-0 py-1">Roll No</td>
                        <td class="py-1 px-3">{{ $student->currentEnrollment->roll_no ?? '-' }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td class="fw-bold ps-0 py-1">Father Name</td>
                        <td class="py-1 px-3">{{ $student->parent->father_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold ps-0 py-1">Contact No</td>
                        <td class="py-1 px-3">{{ $student->parent->father_phone ?? $student->phone ?? '-' }}</td>
                    </tr>
                    <tr style="border-top: 1px solid #dee2e6;">
                        <td class="fw-bold ps-0 py-1">Class Teacher</td>
                        <td class="py-1 px-3">
                            @if($classTeacher)
                            <span class="badge bg-warning text-dark">{{ $classTeacher->first_name }} {{ $classTeacher->last_name }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="card radius-10">
    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
        <h5 class="mb-0 text-primary">Fee Records</h5>
        @if($arrears['total'] > 0)
        <span class="badge bg-danger fs-6">
            Arrears: Rs {{ number_format($arrears['total'], 2) }}@if($arrears['late_fee'] > 0) (incl. late fee Rs {{ number_format($arrears['late_fee'], 2) }})@endif
        </span>
        @endif
        <div class="d-flex gap-2">
            @can('manage-fee-vouchers')
            <button type="button" class="btn btn-primary btn-sm" id="btnGenerateVoucher">
                <i class="bx bx-file"></i> Generate Voucher
            </button>
            @endcan
            <button type="button" class="btn btn-success btn-sm" id="btnCollectSelected" disabled>
                <i class="bx bx-money"></i> Collect Selected (<span id="selectedCount">0</span>)
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="30"><input type="checkbox" id="checkAll"></th>
                        <th>#</th>
                        <th>Fee Type</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Discount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th width="160">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fees as $fee)
                    @php
                    $payable = $fee->amount - $fee->discount;
                    $balance = $payable - $fee->paid_amount;
                    $feeMonth = $fee->period ?: \Carbon\Carbon::parse($fee->due_date)->format('Y-m');
                    @endphp
                    <tr id="feeRow{{ $fee->id }}">
                        <td>
                            @if($fee->status !== 'paid')
                            <input type="checkbox" class="feeCheckbox" value="{{ $fee->id }}" data-balance="{{ $balance }}" data-month="{{ $feeMonth }}">
                            @endif
                        </td>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $fee->feeType->name ?? '-' }}@if($fee->feeAssignment)<small class="fee-assignment-title d-block">{{ $fee->feeAssignment->title }}</small>@endif</td>
                        <td>{{ \Carbon\Carbon::parse($fee->due_date)->format('d-m-Y') }}</td>
                        <td>{{ number_format($fee->amount, 2) }}</td>
                        <td>
                            @if($fee->discount > 0)
                            <span class="text-success">-{{ number_format($fee->discount, 2) }}</span>
                            @else
                            0.00
                            @endif
                        </td>
                        <td class="paid-cell">{{ number_format($fee->paid_amount, 2) }}</td>
                        <td class="balance-cell">{{ number_format($balance, 2) }}</td>
                        <td class="status-cell">
                            @php
                            $badge = ['paid' => 'bg-success', 'partial' => 'bg-warning text-dark', 'unpaid' => 'bg-danger'][$fee->status] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $badge }}">{{ ucfirst($fee->status) }}</span>
                        </td>
                        <td class="text-nowrap">
                            @if($fee->status !== 'paid')
                            <button type="button" class="btn btn-success btn-sm btnPay"
                                data-id="{{ $fee->id }}" data-balance="{{ $balance }}" data-month="{{ $feeMonth }}">
                                <i class="bx bx-money"></i> Collect
                            </button>

                            @if((float) $fee->paid_amount === 0.0)
                            <button type="button" class="btn btn-outline-danger btn-sm na-btn" data-id="{{ $fee->id }}">
                                N/A
                            </button>
                            @endif
                            @endif

                            @if($fee->paid_amount > 0)
                            @php
                            $lastTxn = $fee->transactions->sortByDesc('id')->first();
                            @endphp
                            @if($lastTxn)
                            <a href="{{ route('fees.collect.receipt', $lastTxn->receipt_id ?? $lastTxn->id) }}"
                                target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-receipt"></i> Receipt
                            </a>
                            @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center">No fee records found for this student.</td>
                    </tr>
                    @endforelse

                    {{-- Pending late fee: one row per month, paid together with that month's fee --}}
                    @foreach($lateFees as $late)
                    <tr class="table-danger late-fee-row">
                        <td></td>
                        <td>—</td>
                        <td>Late fee<small class="d-block">{{ \Carbon\Carbon::parse($late->month . '-01')->format('M Y') }}</small></td>
                        <td>{{ \Carbon\Carbon::parse($late->due_date)->format('d-m-Y') }}</td>
                        <td>{{ number_format($late->amount, 2) }}</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>{{ number_format($late->amount, 2) }}</td>
                        <td><span class="badge bg-danger">Pending</span></td>
                        <td class="text-nowrap">
                            @can('manage-fee-due-dates')
                            <button type="button" class="btn btn-warning text-dark btn-sm btnExtend" data-month="{{ $late->month }}">
                                <i class="bx bx-calendar-plus"></i> Extend Due Date
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @endforeach

                    {{-- Excluded fees — history ke liye dabi hui row me --}}
                    @foreach($excludedFees as $fee)
                    <tr class="text-muted" style="background:#f8f9fa;">
                        <td></td>
                        <td>—</td>
                        <td>{{ $fee->feeType->name ?? '-' }}@if($fee->feeAssignment)<small class="fee-assignment-title d-block">{{ $fee->feeAssignment->title }}</small>@endif</td>
                        <td>{{ \Carbon\Carbon::parse($fee->due_date)->format('d-m-Y') }}</td>
                        <td>{{ number_format($fee->amount, 2) }}</td>
                        <td>—</td>
                        <td>—</td>
                        <td>—</td>
                        <td><span class="badge bg-secondary">Excluded</span></td>
                        <td>
                            <button type="button" class="btn btn-outline-success btn-sm reassign-btn" data-id="{{ $fee->id }}">
                                <i class="bx bx-undo"></i> Reassign
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@can('manage-fee-vouchers')
{{-- Vouchers of this student (one per month) --}}
<div class="card radius-10 mt-3">
    <div class="card-header bg-transparent">
        <h5 class="mb-0 text-primary">Vouchers</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Voucher No</th>
                        <th>Month</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Payable by Due Date</th>
                        <th>Payable After Due Date</th>
                        <th>Status</th>
                        <th width="220">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                    @php
                    $voucherBadge = ['paid' => 'bg-success', 'unpaid' => 'bg-danger', 'cancelled' => 'bg-secondary'][$voucher->status] ?? 'bg-secondary';
                    @endphp
                    <tr>
                        <td>{{ $voucher->voucher_no }}</td>
                        <td>{{ \Carbon\Carbon::parse($voucher->period . '-01')->format('M Y') }}</td>
                        <td>{{ $voucher->issue_date->format('d-m-Y') }}</td>
                        <td>{{ $voucher->due_date->format('d-m-Y') }}</td>
                        <td>{{ number_format($voucher->amount_before_due, 2) }}</td>
                        <td>{{ number_format($voucher->amount_after_due, 2) }}</td>
                        <td><span class="badge {{ $voucherBadge }}">{{ ucfirst($voucher->status) }}</span></td>
                        <td class="text-nowrap">
                            @if($voucher->status !== 'cancelled')
                            <a href="{{ route('fees.voucher.pdf', $voucher->id) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-printer"></i> Print
                            </a>
                            @endif
                            @if($voucher->status !== 'paid')
                            <button type="button" class="btn btn-outline-success btn-sm btnRegenerate" data-month="{{ $voucher->period }}">
                                <i class="bx bx-refresh"></i> {{ $voucher->status === 'cancelled' ? 'Reactivate' : 'Regenerate' }}
                            </button>
                            @endif
                            @if($voucher->status === 'unpaid')
                            <button type="button" class="btn btn-outline-danger btn-sm btnCancelVoucher" data-id="{{ $voucher->id }}" data-no="{{ $voucher->voucher_no }}">
                                <i class="bx bx-x-circle"></i> Cancel
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No vouchers generated yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Generate Voucher Modal --}}
<div class="modal fade" id="voucherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="voucherForm" novalidate>
                @csrf
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">Generate Voucher</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">One voucher per month. An unpaid voucher of the same month is regenerated (same voucher number).</p>
                    <label class="form-label" for="voucher_month">Month <span class="text-danger">*</span></label>
                    <input type="month" class="form-control form-control-sm" id="voucher_month" name="month">
                    <div class="invalid-feedback" id="error-voucher_month"></div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm" id="btnVoucherSubmit">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

{{-- Payment Modal --}}
<div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="payForm">
                @csrf
                <input type="hidden" id="pay_fee_id" name="fee_id">
                <input type="hidden" id="pay_fee_ids" name="fee_ids">
                <input type="hidden" id="pay_action" name="action" value="collect">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white" id="payModalTitle">Collect Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Balance: <strong id="pay_balance_display"></strong></p>

                    <div class="mb-3">
                        <label class="form-label" for="pay_date">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm" id="pay_date" name="date">
                        <div class="invalid-feedback" id="error-date"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="amount">Paying Amount (Rs.) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control form-control-sm" id="amount" name="amount">
                        <div class="invalid-feedback" id="error-amount"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Mode <span class="text-danger">*</span></label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input pay_method" type="radio" name="payment_method" value="cash" id="pm_cash" checked>
                            <label class="form-check-label" for="pm_cash">Cash</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input pay_method" type="radio" name="payment_method" value="cheque" id="pm_cheque">
                            <label class="form-check-label" for="pm_cheque">Cheque</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input pay_method" type="radio" name="payment_method" value="bank_transfer" id="pm_bank">
                            <label class="form-check-label" for="pm_bank">Bank Transfer</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input pay_method" type="radio" name="payment_method" value="easypaisa" id="pm_easypaisa">
                            <label class="form-check-label" for="pm_easypaisa">Easypaisa</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input pay_method" type="radio" name="payment_method" value="jazzcash" id="pm_jazzcash">
                            <label class="form-check-label" for="pm_jazzcash">JazzCash</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input pay_method" type="radio" name="payment_method" value="card" id="pm_card">
                            <label class="form-check-label" for="pm_card">Card</label>
                        </div>
                        <div class="invalid-feedback d-block" id="error-payment_method"></div>
                    </div>

                    <div class="mb-3 d-none" id="bankAccountWrap">
                        <label class="form-label" for="bank_account_id">Bank Account <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" id="bank_account_id" name="bank_account_id">
                            <option value="">Select Bank Account</option>
                            @foreach($bankAccounts as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_title }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="error-bank_account_id"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="reference_no">Reference No</label>
                        <input type="text" class="form-control form-control-sm" id="reference_no" name="reference_no" placeholder="Cheque no / transaction id">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="note">Note</label>
                        <textarea class="form-control form-control-sm" id="note" name="note" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <div class="d-flex flex-nowrap gap-1">
                        <button type="submit" class="btn btn-primary btn-sm" id="btnCollectFees"><span class="d-none d-sm-inline">Collect Fees</span><span class="d-sm-none">Collect</span></button>
                        <button type="submit" class="btn btn-info btn-sm text-white" id="btnCollectPrint"><span class="d-none d-sm-inline">Collect &amp; Print</span><span class="d-sm-none">Print</span></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@can('manage-fee-due-dates')
{{-- Extend Due Date Modal --}}
<div class="modal fade" id="extendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="extendForm">
                @csrf
                <input type="hidden" id="extend_month" name="month">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Extend Due Date</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Unpaid fees of <strong id="extend_month_label"></strong> get the new due date and the late fee of that month is removed.</p>
                    <label class="form-label" for="extend_due_date">New Due Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control form-control-sm" id="extend_due_date" name="due_date">
                    <div class="invalid-feedback" id="error-extend_due_date"></div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Extend</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
    // Is page par toastr zyada der dikhe (global default 3s hai)
    toastr.options.timeOut = 6000;
    toastr.options.extendedTimeOut = 2000;

    // Reload ke baad toast gayab ho jata hai: message save karo, naye page par dikhao
    function reloadWithToast(msg, delay) {
        try {
            sessionStorage.setItem('feeCollectToast', msg || '');
        } catch (e) {}
        setTimeout(function() {
            location.reload();
        }, delay || 800);
    }

    $(function() {
        try {
            const savedToast = sessionStorage.getItem('feeCollectToast');
            if (savedToast) {
                sessionStorage.removeItem('feeCollectToast');
                toastr.success(savedToast);
            }
        } catch (e) {}

        let currentFeeId = null;
        let isBulkMode = false;
        // month (Y-m) => pending late fee amount
        const lateFees = @json($lateFees->mapWithKeys(fn($l) => [$l->month => (float) $l->amount]));

        // ------------------------------------------------------------
        // Single row "Collect" button (existing behaviour)
        // ------------------------------------------------------------
        $(document).on('click', '.btnPay', function() {
            isBulkMode = false;
            currentFeeId = $(this).data('id');
            const balance = parseFloat($(this).data('balance'));
            openPayModal([currentFeeId], balance, 'Collect Payment', [$(this).data('month')]);
        });

        // ------------------------------------------------------------
        // Checkbox selection (header "select all" + per-row)
        // ------------------------------------------------------------
        $(document).on('change', '#checkAll', function() {
            $('.feeCheckbox').prop('checked', $(this).is(':checked'));
            updateSelectedState();
        });

        $(document).on('change', '.feeCheckbox', function() {
            if (!$(this).is(':checked')) {
                $('#checkAll').prop('checked', false);
            }
            updateSelectedState();
        });

        function updateSelectedState() {
            const $checked = $('.feeCheckbox:checked');
            $('#selectedCount').text($checked.length);
            $('#btnCollectSelected').prop('disabled', $checked.length === 0);

            // keep "select all" in sync if every row is checked
            const total = $('.feeCheckbox').length;
            $('#checkAll').prop('checked', total > 0 && $checked.length === total);
        }

        // ------------------------------------------------------------
        // "Collect Selected" bulk button
        // ------------------------------------------------------------
        $(document).on('click', '#btnCollectSelected', function() {
            const $checked = $('.feeCheckbox:checked');
            if (!$checked.length) return;

            isBulkMode = true;
            const ids = $checked.map(function() {
                return $(this).val();
            }).get();
            let totalBalance = 0;
            const months = [];
            $checked.each(function() {
                totalBalance += parseFloat($(this).data('balance'));
                months.push($(this).data('month'));
            });

            openPayModal(ids, totalBalance, 'Collect Payment (' + ids.length + ' fees)', months);
        });

        // ------------------------------------------------------------
        // Shared modal opener
        // ------------------------------------------------------------
        function openPayModal(ids, balance, title, months) {
            $('#payForm')[0].reset();
            $('#payForm').find('.is-invalid').removeClass('is-invalid');
            $('#payForm').find('.invalid-feedback').text('');

            // Pending late fee of each distinct month is added once
            let lateTotal = 0;
            const seenMonths = {};
            $.each(months || [], function(_, m) {
                if (!seenMonths[m] && lateFees[m]) {
                    seenMonths[m] = true;
                    lateTotal += lateFees[m];
                }
            });
            const payable = balance + lateTotal;

            $('#payModalTitle').text(title);
            $('#pay_fee_id').val(ids.length === 1 ? ids[0] : '');
            $('#pay_fee_ids').val(ids.join(','));
            $('#pay_balance_display').text(payable.toFixed(2) + (lateTotal > 0 ? ' (incl. late fee ' + lateTotal.toFixed(2) + ')' : ''));
            $('#amount').attr('max', payable);
            $('#pay_date').val(new Date().toISOString().split('T')[0]); // aaj ki date default
            $('input[value=cash]').prop('checked', true);
            $('#bankAccountWrap').addClass('d-none');
            $('#payModal').modal('show');
        }

        $(document).on('change', '.pay_method', function() {
            const val = $(this).val();
            if (val === 'cash') {
                $('#bankAccountWrap').addClass('d-none');
                $('#bank_account_id').val('');
            } else {
                $('#bankAccountWrap').removeClass('d-none');
            }
        });

        // Konsa button dabaya (Collect Fees ya Collect & Print) track karo
        $('#btnCollectFees, #btnCollectPrint').on('click', function() {
            $('#pay_action').val(this.id === 'btnCollectPrint' ? 'print' : 'collect');
        });

        // Field edit karte hi uska red border + error message clear
        $('#payForm').on('input change', 'input, select, textarea', function() {
            const name = $(this).attr('name');
            $(this).removeClass('is-invalid');
            $('#error-' + name).text('');
        });

        $('#payForm').on('submit', function(e) {
            e.preventDefault();
            $(this).find('.is-invalid').removeClass('is-invalid');
            const action = $('#pay_action').val();

            // Bulk mode hits a different endpoint since it pays multiple fee ids at once
            const url = isBulkMode ?
                "{{ url('fees/collect/bulk-pay') }}" :
                "{{ url('fees/collect') }}/" + currentFeeId + "/pay";

            $.ajax({
                url: url,
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#payModal').modal('hide');

                    if (isBulkMode && res.fees) {
                        // Backend should return an array of updated fees: [{id, paid_amount, balance, status}, ...]
                        $.each(res.fees, function(_, feeRes) {
                            const $row = $('#feeRow' + feeRes.id);
                            updateRow($row, feeRes);
                        });
                        $('.feeCheckbox').prop('checked', false);
                        $('#checkAll').prop('checked', false);
                        updateSelectedState();
                    } else {
                        const $row = $('#feeRow' + currentFeeId);
                        updateRow($row, res);
                    }

                    // Agar "Collect & Print" dabaya tha to receipt print page open karo
                    if (action === 'print' && res.transaction_id) {
                        window.open("{{ url('fees/collect/receipt') }}/" + res.transaction_id, '_blank');
                    } else if (action === 'print' && res.receipt_id) {
                        // Bulk print: ek hi receipt window khulti hai jisme saari selected
                        // fees ek group ke tor pe list hoti hain (backend receipt_id se group detect karta hai)
                        window.open("{{ url('fees/collect/receipt') }}/" + res.receipt_id, '_blank');
                    }

                    // Payment changes late fee row, arrears badge and Vouchers card: reload (toast shown after reload)
                    reloadWithToast(res.message, 1200);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            $('#' + field).addClass('is-invalid');
                            $('#error-' + field).text(messages[0]);
                            toastr.error(messages[0]);
                        });
                    } else {
                        toastr.error('Something went wrong.');
                    }
                }
            });
        });

        function updateRow($row, res) {
            $row.find('.paid-cell').text(res.paid_amount);
            $row.find('.balance-cell').text(res.balance);

            const badgeMap = {
                paid: 'bg-success',
                partial: 'bg-warning text-dark',
                unpaid: 'bg-danger'
            };
            $row.find('.status-cell').html('<span class="badge ' + badgeMap[res.status] + '">' + res.status.charAt(0).toUpperCase() + res.status.slice(1) + '</span>');

            // 👇 NAYA CODE — Receipt button add/refresh karna
            const receiptId = res.receipt_id ?? res.transaction_id;
            if (parseFloat(res.paid_amount) > 0 && receiptId) {
                const receiptUrl = "{{ url('fees/collect/receipt') }}/" + receiptId;
                const $actionCell = $row.find('td.text-nowrap');
                const $existingReceiptBtn = $actionCell.find('.btn-outline-primary');

                if ($existingReceiptBtn.length === 0) {
                    // pehli dafa receipt button add karo
                    $actionCell.append(
                        ' <a href="' + receiptUrl + '" target="_blank" class="btn btn-outline-primary btn-sm">' +
                        '<i class="bx bx-receipt"></i> Receipt</a>'
                    );
                } else {
                    // 👇 NAYA — already maujood button ka href refresh karo taake naye (latest) transaction/receipt ki taraf point kare
                    $existingReceiptBtn.attr('href', receiptUrl);
                }
            }

            if (res.status === 'paid') {
                $row.find('.btnPay').replaceWith('');
                $row.find('.na-btn').remove();
                $row.find('.feeCheckbox').replaceWith('');
            }
        }

        // ------------------------------------------------------------
        // Extend Due Date (late fee row)
        // ------------------------------------------------------------
        $(document).on('click', '.btnExtend', function() {
            const month = $(this).data('month');
            $('#extendForm')[0].reset();
            $('#extendForm').find('.is-invalid').removeClass('is-invalid');
            $('#extendForm').find('.invalid-feedback').text('');
            $('#extend_month').val(month);
            $('#extend_month_label').text(month);
            $('#extend_due_date').attr('min', new Date().toISOString().split('T')[0]);
            $('#extendModal').modal('show');
        });

        // Date edit karte hi red border + error message clear
        $('#extendForm').on('input change', '#extend_due_date', function() {
            $(this).removeClass('is-invalid');
            $('#error-extend_due_date').text('');
        });

        $('#extendForm').on('submit', function(e) {
            e.preventDefault();
            $(this).find('.is-invalid').removeClass('is-invalid');

            $.ajax({
                url: "{{ route('fees.collect.extend_due_date', $student->id) }}",
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#extendModal').modal('hide');
                    reloadWithToast(res.message, 800);
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const msg = xhr.responseJSON.errors.due_date || xhr.responseJSON.errors.month;
                        $('#extend_due_date').addClass('is-invalid');
                        $('#error-extend_due_date').text(msg ? msg[0] : 'Invalid data.');
                        toastr.error(msg ? msg[0] : 'Invalid data.');
                    } else if (xhr.status === 422 && xhr.responseJSON) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Something went wrong.');
                    }
                }
            });
        });

        $(document).on('click', '.na-btn', function() {
            const feeId = $(this).data('id');

            Swal.fire({
                title: 'Mark this fee as N/A?',
                text: 'This fee will no longer be applicable for this student.',
                input: 'text',
                inputPlaceholder: 'Reason (optional)',
                showCancelButton: true,
                confirmButtonText: 'Yes, Mark it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('/fees/collect') }}/${feeId}/exclude`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            reason: result.value
                        },
                        success: function(res) {
                            reloadWithToast(res.message, 300);
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON.error ?? 'Something went wrong');
                        }
                    });
                }
            });
        });

        $(document).on('click', '.reassign-btn', function() {
            const feeId = $(this).data('id');

            Swal.fire({
                title: 'Reassign this fee?',
                text: 'This fee will become unpaid again for this student.',
                showCancelButton: true,
                confirmButtonText: 'Yes, Reassign'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('/fees/collect') }}/${feeId}/reassign`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            reloadWithToast(res.message, 300);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

@can('manage-fee-vouchers')
@push('scripts')
<script>
    $(function() {
        const voucherGenerateUrl = "{{ route('fees.voucher.generate', $student->id) }}";
        const voucherCancelUrl = "{{ route('fees.voucher.cancel', ':id') }}";

        function twoDigits(n) {
            return ('0' + n).slice(-2);
        }

        // Month input limits: up to next month (same rule as the server)
        const now = new Date();
        const thisMonth = now.getFullYear() + '-' + twoDigits(now.getMonth() + 1);
        const next = new Date(now.getFullYear(), now.getMonth() + 1, 1);
        const nextMonth = next.getFullYear() + '-' + twoDigits(next.getMonth() + 1);
        $('#voucher_month').attr('max', nextMonth);

        function openVoucherModal(month) {
            $('#voucherForm').find('.is-invalid').removeClass('is-invalid');
            $('#error-voucher_month').text('');
            $('#voucher_month').val(month || thisMonth);
            $('#btnVoucherSubmit').prop('disabled', false);
            $('#voucherModal').modal('show');
        }

        $('#btnGenerateVoucher').on('click', function() {
            openVoucherModal(thisMonth);
        });

        $(document).on('click', '.btnRegenerate', function() {
            openVoucherModal($(this).data('month'));
        });

        // Field edit karte hi red border + error message clear
        $('#voucherForm').on('input change', '#voucher_month', function() {
            $(this).removeClass('is-invalid');
            $('#error-voucher_month').text('');
        });

        $('#voucherForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $('#btnVoucherSubmit');

            if (!$('#voucher_month').val()) {
                $('#voucher_month').addClass('is-invalid');
                $('#error-voucher_month').text('Please select the voucher month.');
                toastr.clear();
                toastr.error('Please select the voucher month.');
                return;
            }

            $btn.prop('disabled', true); // double click guard

            $.ajax({
                url: voucherGenerateUrl,
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#voucherModal').modal('hide');
                    window.open(res.pdf_url, '_blank');
                    reloadWithToast(res.message, 1200);
                },
                error: function(xhr) {
                    $btn.prop('disabled', false);
                    toastr.clear();
                    if (xhr.status === 422 && xhr.responseJSON) {
                        const msg = (xhr.responseJSON.errors && xhr.responseJSON.errors.month)
                            ? xhr.responseJSON.errors.month[0]
                            : xhr.responseJSON.message;
                        $('#voucher_month').addClass('is-invalid');
                        $('#error-voucher_month').text(msg);
                        toastr.error(msg);
                    } else {
                        toastr.error('Something went wrong.');
                    }
                }
            });
        });

        $(document).on('click', '.btnCancelVoucher', function() {
            const $btn = $(this);
            const id = $btn.data('id');

            Swal.fire({
                title: 'Cancel voucher #' + $btn.data('no') + '?',
                text: 'The voucher will be cancelled. You can reactivate it later by regenerating it.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, cancel it',
                cancelButtonText: 'No',
                buttonsStyling: false,
                customClass: {
                    popup: 'fee-assignment-swal',
                    confirmButton: 'btn btn-danger btn-sm',
                    cancelButton: 'btn btn-secondary btn-sm'
                }
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $btn.prop('disabled', true);
                $.ajax({
                    url: voucherCancelUrl.replace(':id', id),
                    type: 'POST',
                    success: function(res) {
                        reloadWithToast(res.message, 1200);
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false);
                        toastr.clear();
                        toastr.error((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong.');
                    }
                });
            });
        });
    });
</script>
@endpush
@endcan

@endsection