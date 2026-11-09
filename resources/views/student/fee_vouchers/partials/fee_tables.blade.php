{{-- Fee tables of one student (read-only), shared by the student My Fees page and the parent Fee Voucher / Fee Ledger tabs.
     $section = 'vouchers' | 'ledger'; $voucherUrl / $receiptUrl = closures that build the portal's own download links --}}
@if($section === 'vouchers')
<div class="card radius-10 shadow-sm border-0">
    <div class="card-header bg-transparent border-0 pt-3">
        <h6 class="mb-0 fw-bold text-dark dark-readable">Fee Vouchers</h6>
    </div>
    <div class="card-body">
        @if($feeVouchers->isEmpty())
        <p class="text-muted mb-0">No fee voucher has been issued yet.</p>
        @else
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Voucher No</th>
                        <th>Month</th>
                        <th>Due Date</th>
                        <th>Payable by Due Date</th>
                        <th>Payable After Due Date</th>
                        <th>Status</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($feeVouchers as $voucher)
                    <tr>
                        <td>{{ $voucher->voucher_no }}</td>
                        <td>{{ \Carbon\Carbon::parse($voucher->period . '-01')->format('M Y') }}</td>
                        <td>{{ $voucher->due_date->format('d-m-Y') }}</td>
                        <td>Rs. {{ number_format($voucher->amount_before_due, 2) }}</td>
                        <td>Rs. {{ number_format($voucher->amount_after_due, 2) }}</td>
                        <td>
                            @if($voucher->status === 'paid')
                            <span class="badge bg-success">Paid</span>
                            @else
                            <span class="badge bg-danger">Unpaid</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ $voucherUrl($voucher) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-download"></i> PDF
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@else
<div class="card radius-10 shadow-sm border-0 mb-3">
    <div class="card-header bg-transparent border-0 pt-3">
        <h6 class="mb-0 fw-bold text-dark dark-readable">Fee Records</h6>
    </div>
    <div class="card-body">
        @if($feeLedger['fees']->isEmpty())
        <p class="text-muted mb-0">No fee records found.</p>
        @else
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fee</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Discount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($feeLedger['fees'] as $fee)
                    @php
                    $feeBadge = ['paid' => 'bg-success', 'partial' => 'bg-warning text-dark', 'unpaid' => 'bg-danger'][$fee->status] ?? 'bg-secondary';
                    @endphp
                    <tr>
                        <td>{{ $fee->feeType->name ?? '-' }}@if($fee->feeAssignment) - {{ $fee->feeAssignment->title }}@endif</td>
                        <td>{{ $fee->due_date->format('d-m-Y') }}</td>
                        <td>Rs. {{ number_format($fee->amount, 2) }}</td>
                        <td>Rs. {{ number_format($fee->discount, 2) }}</td>
                        <td>Rs. {{ number_format($fee->paid_amount, 2) }}</td>
                        <td>Rs. {{ number_format($fee->amount - $fee->discount - $fee->paid_amount, 2) }}</td>
                        <td><span class="badge {{ $feeBadge }}">{{ ucfirst($fee->status) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<div class="card radius-10 shadow-sm border-0">
    <div class="card-header bg-transparent border-0 pt-3">
        <h6 class="mb-0 fw-bold text-dark dark-readable">Payment History</h6>
    </div>
    <div class="card-body">
        @if($feeLedger['payments']->isEmpty())
        <p class="text-muted mb-0">No payment has been received yet.</p>
        @else
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Fee</th>
                        <th>Mode</th>
                        <th>Amount</th>
                        <th>Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($feeLedger['payments'] as $payment)
                    <tr>
                        <td>{{ $payment->date->format('d-m-Y') }}</td>
                        <td>{{ $payment->heads }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                        <td>Rs. {{ number_format($payment->amount, 2) }}</td>
                        <td>
                            <a href="{{ $receiptUrl($payment->receipt_id) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-receipt"></i> Receipt
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endif
