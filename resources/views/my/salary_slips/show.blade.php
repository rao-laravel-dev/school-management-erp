@extends($current_layout)
@section('content')

{{-- My Salary Slip detail: read-only + print (owner check controller mein) --}}
<x-breadcrumb :items="[
    ['label' => 'Payroll', 'url' => '#'],
    ['label' => 'My Salary Slips', 'url' => route('my.salary_slips.index')],
    ['label' => \Carbon\Carbon::parse($slip->month)->format('F Y'), 'url' => '#'],
]" />

<div class="card radius-10" id="slipCard">
    <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
        <h5 class="mb-0 text-primary">Salary Slip — {{ \Carbon\Carbon::parse($slip->month)->format('F Y') }}</h5>
        <div class="no-print">
            <a href="{{ route('my.salary_slips.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-arrow-back"></i> Back
            </a>
            <button type="button" class="btn btn-primary btn-sm" id="printSlipBtn">
                <i class="bx bx-printer"></i> Print
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <tr><th style="width:250px;">Staff Name</th><td>{{ $slip->user->name }}</td></tr>
                <tr><th>Role</th><td>{{ ucfirst($slip->user->roles->first()->name ?? '-') }}</td></tr>
                <tr><th>Month</th><td>{{ \Carbon\Carbon::parse($slip->month)->format('F Y') }}</td></tr>
                <tr><th>Basic Salary</th><td>{{ number_format($slip->basic_salary, 2) }}</td></tr>
                <tr><th>Allowance</th><td>{{ number_format($slip->allowance, 2) }}</td></tr>
                <tr class="table-light"><th colspan="2">Attendance Summary</th></tr>
                <tr><th>Total Working Days</th><td>{{ $slip->total_days ?? '-' }}</td></tr>
                <tr><th>Present</th><td><span class="text-success">{{ $slip->present_days ?? '-' }}</span></td></tr>
                <tr><th>Absent</th><td><span class="text-danger">{{ $slip->absent_days ?? '-' }}</span></td></tr>
                <tr><th>Half Day</th><td><span class="text-info">{{ $slip->half_days ?? '-' }}</span></td></tr>
                <tr><th>Leave</th><td><span class="text-secondary">{{ $slip->leave_days ?? '-' }}</span></td></tr>
                <tr><th>Per Day Rate</th><td>{{ number_format($slip->per_day_rate ?? 0, 2) }}</td></tr>
                <tr class="table-light"><th colspan="2">Deductions</th></tr>
                <tr><th>Attendance Deduction</th><td>{{ number_format($slip->attendance_deduction ?? 0, 2) }}</td></tr>
                <tr><th>Manual Deduction</th><td>{{ number_format($slip->manual_deduction ?? 0, 2) }}</td></tr>
                <tr><th>Advance Deduction</th><td>{{ number_format($slip->advance_deduction ?? 0, 2) }}</td></tr>
                <tr><th>Total Deduction</th><td>{{ number_format($slip->deduction ?? 0, 2) }}</td></tr>
                <tr class="table-success"><th>Net Salary</th><td><strong>{{ number_format($slip->net_salary ?? 0, 2) }}</strong></td></tr>
                <tr>
                    <th>Status</th>
                    <td><span class="badge {{ $slip->status == 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($slip->status) }}</span></td>
                </tr>
                @if($slip->status == 'paid')
                    <tr><th>Payment Date</th><td>{{ $slip->payment_date ? $slip->payment_date->format('d-m-Y') : '-' }}</td></tr>
                    <tr><th>Paid By</th><td>{{ $slip->paidBy->name ?? '-' }}</td></tr>
                    <tr><th>Payment Method</th><td>{{ ucfirst(str_replace('_', ' ', $slip->transaction->payment_method ?? '-')) }}</td></tr>
                    <tr><th>Reference No</th><td>{{ $slip->transaction->reference_no ?? '-' }}</td></tr>
                @endif
            </table>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Print par sirf slip card, sidebar/header/buttons hide */
    @media print {
        .sidebar-wrapper, header, .topbar, .page-footer, .page-breadcrumb, .back-to-top, .no-print { display: none !important; }
        .page-wrapper { margin: 0 !important; padding: 0 !important; }
        .page-content { padding: 0 !important; }
        #slipCard { box-shadow: none !important; border: 0 !important; }
    }
</style>
@endpush

@push('scripts')
<script>
    $(function() {
        $('#printSlipBtn').on('click', function() {
            window.print();
        });
    });
</script>
@endpush
