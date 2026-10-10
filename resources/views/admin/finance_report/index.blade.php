@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Finance', 'url' => '#'],
    ['label' => 'Finance Overview', 'url' => '#'],
]" />

{{-- Date Filter --}}
<div class="card radius-10 mb-3">
    <div class="card-body">
        <form id="filterForm" method="GET" action="{{ route('finance_report.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $from }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $to }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bx bx-filter-alt me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row">
    <div class="col-md-3">
        <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm">
            <div class="card-body">
                <p class="mb-0 text-secondary">Fee Collection</p>
                <h4 class="mb-0 text-success">{{ number_format($totalFeeCollection, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card radius-10 border-start border-0 border-3 border-warning shadow-sm">
            <div class="card-body">
                <p class="mb-0 text-secondary">Salary Paid</p>
                <h4 class="mb-0 text-warning">{{ number_format($totalSalaryPaid, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card radius-10 border-start border-0 border-3 border-danger shadow-sm">
            <div class="card-body">
                <p class="mb-0 text-secondary">Expenses</p>
                <h4 class="mb-0 text-danger">{{ number_format($totalExpenses, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card radius-10 border-start border-0 border-3 border-{{ $netCashFlow >= 0 ? 'primary' : 'danger' }} shadow-sm">
            <div class="card-body">
                <p class="mb-0 text-secondary">Net Cash Flow</p>
                <h4 class="mb-0">{{ number_format($netCashFlow, 2) }}</h4>
            </div>
        </div>
    </div>
</div>

{{-- Tabs --}}
<div class="card radius-10">
    <div class="card-header bg-transparent">
        <ul class="nav nav-tabs card-header-tabs" id="financeTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#feeTab" type="button">
                    Fee Collection
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#salaryTab" type="button">
                    Salary Paid
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#expenseTab" type="button">
                    Expenses
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">

            {{-- Fee Collection Tab --}}
            <div class="tab-pane fade show active" id="feeTab">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="feeTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Fee Type</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Bank Account</th>
                                <th>Collected By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feeCollections as $txn)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($txn->transaction_date)->format('d M, Y') }}</td>
                                <td>{{ trim(($txn->studentFee->student->first_name ?? '') . ' ' . ($txn->studentFee->student->last_name ?? '')) ?: '-' }}</td>
                                <td>{{ $txn->studentFee->feeType->name ?? '-' }}</td>
                                <td class="fw-bold text-success">{{ number_format($txn->amount, 2) }}</td>
                                <td>{{ ucfirst($txn->payment_method) }}</td>
                                <td>{{ $txn->bankAccount->bank_name ?? '-' }}</td>
                                <td>{{ $txn->user->name ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Salary Paid Tab --}}
            <div class="tab-pane fade" id="salaryTab">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="salaryTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Staff</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Bank Account</th>
                                <th>Paid By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salaryPayments as $txn)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($txn->transaction_date)->format('d M, Y') }}</td>
                                <td>{{ $txn->salarySlip->user->name ?? '-' }}</td>
                                <td class="fw-bold text-warning">{{ number_format($txn->amount, 2) }}</td>
                                <td>{{ ucfirst($txn->payment_method) }}</td>
                                <td>{{ $txn->bankAccount->bank_name ?? '-' }}</td>
                                <td>{{ $txn->user->name ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Expenses Tab --}}
            <div class="tab-pane fade" id="expenseTab">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="expenseTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Bank Account</th>
                                <th>Paid By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expensePayments as $txn)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($txn->transaction_date)->format('d M, Y') }}</td>
                                <td>{{ $txn->expense->title ?? '-' }}</td>
                                <td>{{ $txn->expense->category->name ?? '-' }}</td>
                                <td class="fw-bold text-danger">{{ number_format($txn->amount, 2) }}</td>
                                <td>{{ ucfirst($txn->payment_method) }}</td>
                                <td>{{ $txn->bankAccount->bank_name ?? '-' }}</td>
                                <td>{{ $txn->user->name ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    const feeTable = $('#feeTable').DataTable({ paging: true, searching: true });
    const salaryTable = $('#salaryTable').DataTable({ paging: true, searching: true });
    const expenseTable = $('#expenseTable').DataTable({ paging: true, searching: true });

    // Fix column width glitch when DataTable initializes inside a hidden tab
    $('#financeTabs button').on('shown.bs.tab', function (e) {
        const target = $(e.target).data('bs-target');
        if (target === '#salaryTab') salaryTable.columns.adjust();
        if (target === '#expenseTab') expenseTable.columns.adjust();
        if (target === '#feeTab') feeTable.columns.adjust();
    });
});
</script>
@endpush