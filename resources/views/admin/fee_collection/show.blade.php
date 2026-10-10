@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Fees Collection</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
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
        <button type="button" class="btn btn-success btn-sm" id="btnCollectSelected" disabled>
            <i class="bx bx-money"></i> Collect Selected (<span id="selectedCount">0</span>)
        </button>
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
                    @endphp
                    <tr id="feeRow{{ $fee->id }}">
                        <td>
                            @if($fee->status !== 'paid')
                            <input type="checkbox" class="feeCheckbox" value="{{ $fee->id }}" data-balance="{{ $balance }}">
                            @endif
                        </td>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $fee->feeType->name ?? '-' }}</td>
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
                                data-id="{{ $fee->id }}" data-balance="{{ $balance }}">
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

                    {{-- Excluded fees — history ke liye dabi hui row me --}}
                    @foreach($excludedFees as $fee)
                    <tr class="text-muted" style="background:#f8f9fa;">
                        <td></td>
                        <td>—</td>
                        <td>{{ $fee->feeType->name ?? '-' }}</td>
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
                    <h5 class="modal-title" id="payModalTitle">Collect Payment</h5>
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
                        <label class="form-label" for="amount">Paying Amount ($) <span class="text-danger">*</span></label>
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
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <div>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnCollectFees">Collect Fees</button>
                        <button type="submit" class="btn btn-info btn-sm text-white" id="btnCollectPrint">Collect &amp; Print</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(function() {
        let currentFeeId = null;
        let isBulkMode = false;

        // ------------------------------------------------------------
        // Single row "Collect" button (existing behaviour)
        // ------------------------------------------------------------
        $(document).on('click', '.btnPay', function() {
            isBulkMode = false;
            currentFeeId = $(this).data('id');
            const balance = parseFloat($(this).data('balance'));
            openPayModal([currentFeeId], balance, 'Collect Payment');
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
            $checked.each(function() {
                totalBalance += parseFloat($(this).data('balance'));
            });

            openPayModal(ids, totalBalance, 'Collect Payment (' + ids.length + ' fees)');
        });

        // ------------------------------------------------------------
        // Shared modal opener
        // ------------------------------------------------------------
        function openPayModal(ids, balance, title) {
            $('#payForm')[0].reset();
            $('#payForm').find('.is-invalid').removeClass('is-invalid');

            $('#payModalTitle').text(title);
            $('#pay_fee_id').val(ids.length === 1 ? ids[0] : '');
            $('#pay_fee_ids').val(ids.join(','));
            $('#pay_balance_display').text(balance.toFixed(2));
            $('#amount').attr('max', balance);
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
                    toastr.success(res.message);

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
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            $('#' + field).addClass('is-invalid');
                            $('#error-' + field).text(messages[0]);
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
                            toastr.success(res.message);
                            location.reload();
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
                            toastr.success(res.message);
                            location.reload();
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

@endsection