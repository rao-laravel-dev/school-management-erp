@extends($current_layout)
@section('content')

{{-- Breadcrumb --}}
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Fee Reports</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Fee Collection Report</li>
            </ol>
        </nav>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-3" id="feeReportTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ !($dueFrom || $dueTo) ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#collectionTab" type="button">
            Fee Collection
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ ($dueFrom || $dueTo) ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#dueTab" type="button">
            Due / Outstanding
            @if($overdueCount > 0)
            <span class="badge bg-danger ms-1">{{ $overdueCount }}</span>
            @endif
        </button>
    </li>
</ul>

<div class="tab-content">

    {{-- ============ TAB 1: Fee Collection ============ --}}
    <div class="tab-pane fade {{ !($dueFrom || $dueTo) ? 'show active' : '' }}" id="collectionTab">

        {{-- Summary Cards --}}
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card radius-10 border-0 h-100" style="background: #e8f5e9;">
                    <div class="card-body">
                        <p class="mb-1 text-muted small"> Collected (Today)</p>
                        <h4 class="mb-0 text-success">Rs {{ number_format($todayTotal, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card radius-10 border-0 h-100" style="background: #e3f2fd;">
                    <div class="card-body">
                        <p class="mb-1 text-muted small">This Month Collected</p>
                        <h4 class="mb-0 text-primary">Rs {{ number_format($monthTotal, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card radius-10 border-0 h-100" style="background: #fff3e0;">
                    <div class="card-body">
                        <p class="mb-1 text-muted small">Selected Range Total</p>
                        <h4 class="mb-0 text-warning">Rs {{ number_format($rangeTotal, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter (date range + fee type) --}}
        <div class="card radius-10 mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('fees.collect.report') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label mb-1" for="from">From</label>
                        <input type="date" class="form-control form-control-sm" id="from" name="from" value="{{ $from }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1" for="to">To</label>
                        <input type="date" class="form-control form-control-sm" id="to" name="to" value="{{ $to }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1" for="collectionFeeTypeFilter">Fee Type</label>
                        <select id="collectionFeeTypeFilter" class="form-select form-select-sm">
                            <option value="">-- All Fee Types --</option>
                            @foreach($feeTypes as $name)
                            <option value="{{ $name }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Payment Mode Breakdown --}}
        <div class="card radius-10 mb-3">
            <div class="card-header bg-transparent">
                <h5 class="mb-0 text-primary">Payment Mode Breakdown ({{ \Carbon\Carbon::parse($from)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($to)->format('d-m-Y') }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Payment Mode</th>
                                <th class="text-end">Amount (Rs)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(['cash', 'easypaisa', 'jazzcash', 'bank_transfer', 'cheque', 'card'] as $mode)
                            <tr>
                                <td>{{ ucfirst(str_replace('_', ' ', $mode)) }}</td>
                                <td class="text-end">{{ number_format($modeBreakdown[$mode] ?? 0, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td>Total</td>
                                <td class="text-end">{{ number_format($rangeTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Transaction List — per student (grouped) --}}
        <div class="card radius-10">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">Transactions</h5>
                <span class="badge bg-secondary">{{ $transactions->count() }} students</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="reportTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Admission No</th>
                                <th class="text-end">Amount (Rs)</th>
                                <th>Payment Modes</th>
                                <th>Last Payment Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $txn)
                            <tr data-feetypes="{{ $txn->fee_types }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="javascript:void(0)" class="fw-bold text-success student-link"
                                        data-student="{{ $txn->student->full_name ?? '-' }}"
                                        data-admission="{{ $txn->student->admission_no ?? '-' }}"
                                        data-rows="{{ json_encode($txn->detail->map(fn($d) => [
                                            'date' => \Carbon\Carbon::parse($d->transaction_date)->format('d-m-Y'),
                                            'fee_type' => $d->studentFee->feeType->name ?? '-',
                                            'mode' => str_replace('_', ' ', $d->payment_method),
                                            'account' => $d->bankAccount->bank_name ?? '-',
                                            'amount' => number_format($d->amount, 2),
                                            'collected_by' => $d->user->name ?? '-',
                                              ])) }}"
                                        data-pending="{{ json_encode($txn->pending->map(fn($f) => [
                                            'fee_type' => $f->feeType->name ?? '-',
                                            'amount' => number_format($f->amount, 2),
                                            'pending' => number_format($f->pending_amount, 2),
                                            'due_date' => \Carbon\Carbon::parse($f->due_date)->format('d-m-Y'),
                                            'status' => $f->status == 'partial' ? 'Partial' : 'Unpaid',
                                             ])) }}">
                                        {{ $txn->student->full_name ?? '-' }}
                                    </a>
                                    @if($txn->student?->trashed())
                                        <span class="badge bg-danger ms-1">Trashed</span>
                                    @endif
                                </td>
                                <td>{{ $txn->student->admission_no ?? '-' }}</td>
                                <td class="text-end fw-bold">{{ number_format($txn->amount, 2) }}</td>
                                <td><span class="badge bg-secondary text-uppercase">{{ $txn->modes }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($txn->last_date)->format('d-m-Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Is date range mein koi transaction nahi mili.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    {{-- /TAB 1 --}}

    {{-- ============ TAB 2: Due / Outstanding Fees ============ --}}
    <div class="tab-pane fade {{ ($dueFrom || $dueTo) ? 'show active' : '' }}" id="dueTab">

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card radius-10 border-0 h-100" style="background: #ffebee;">
                    <div class="card-body">
                        <p class="mb-1 text-muted small">Total Outstanding</p>
                        <h4 class="mb-0 text-danger">Rs {{ number_format($totalOutstanding, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card radius-10 border-0 h-100" style="background: #fff3e0;">
                    <div class="card-body">
                        <p class="mb-1 text-muted small">Overdue Amount</p>
                        <h4 class="mb-0 text-warning">Rs {{ number_format($totalOverdue, 2) }}</h4>
                        <small class="text-muted">{{ $overdueCount }} entries</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card radius-10 border-0 h-100" style="background: #f3e5f5;">
                    <div class="card-body">
                        <p class="mb-1 text-muted small">Partially Paid</p>
                        <h4 class="mb-0 text-purple">{{ $partialCount }} students</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter (due date range + fee type) --}}
        <div class="card radius-10 mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('fees.collect.report') }}" class="row g-2 align-items-end">
                    <input type="hidden" name="from" value="{{ $from }}">
                    <input type="hidden" name="to" value="{{ $to }}">
                    <div class="col-md-3">
                        <label class="form-label mb-1" for="due_from">Due From</label>
                        <input type="date" class="form-control form-control-sm" id="due_from" name="due_from" value="{{ $dueFrom }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1" for="due_to">Due To</label>
                        <input type="date" class="form-control form-control-sm" id="due_to" name="due_to" value="{{ $dueTo }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1" for="dueFeeTypeFilter">Fee Type</label>
                        <select id="dueFeeTypeFilter" class="form-select form-select-sm">
                            <option value="">-- All Fee Types --</option>
                            @foreach($feeTypes as $name)
                            <option value="{{ $name }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                        @if($dueFrom || $dueTo)
                        <a href="{{ route('fees.collect.report', ['from' => $from, 'to' => $to]) }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card radius-10">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">Outstanding Fees</h5>
                <span class="badge bg-secondary">{{ $dueByStudent->count() }} records</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dueTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Admission No</th>
                                <th class="text-end">Total Payable</th>
                                <th class="text-end">Total Pending</th>
                                <th>Nearest Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dueByStudent as $row)
                            <tr data-feetypes="{{ $row->fee_types }}" class="{{ $row->is_overdue ? 'table-danger' : '' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="javascript:void(0)" class="fw-semibold text-danger due-student-link"
                                        data-student="{{ $row->student->full_name ?? '-' }}"
                                        data-admission="{{ $row->student->admission_no ?? '-' }}"
                                        data-rows="{{ json_encode($row->detail->map(fn($f) => [
                    'fee_type' => $f->feeType->name ?? '-',
                    'amount' => number_format($f->amount, 2),
                    'paid' => number_format($f->paid_amount, 2),
                    'pending' => number_format($f->pending_amount, 2),
                    'due_date' => \Carbon\Carbon::parse($f->due_date)->format('d-m-Y'),
                    'status' => $f->status == 'paid' ? 'Paid' : ($f->status == 'partial' ? 'Partial' : ($f->is_overdue ? 'Overdue' : 'Unpaid')),
               ])) }}">
                                        {{ $row->student->full_name ?? '-' }}
                                    </a>
                                    @if($row->student?->trashed())
                                        <span class="badge bg-danger ms-1">Trashed</span>
                                    @endif
                                </td>
                                <td>{{ $row->student->admission_no ?? '-' }}</td>
                                <td class="text-end">{{ number_format($row->total_payable, 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format($row->total_pending, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->nearest_due_date)->format('d-m-Y') }}</td>
                                <td>
                                    @if($row->is_overdue)
                                    <span class="badge bg-danger">Overdue</span>
                                    @else
                                    <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No outstanding fees found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    {{-- /TAB 2 --}}

</div>
{{-- /tab-content --}}

@include('admin.fee_collection.report-modal')

@endsection

@push('scripts')
<script>
    var reportTable = $('#reportTable').DataTable({
        "pageLength": 25,
        "order": [],
        "language": {
            "search": "Search:"
        }
    });

    var dueTable = $('#dueTable').DataTable({
        "pageLength": 25,
        "order": [
            [4, 'desc']
        ],
        "language": {
            "search": "Search:"
        },
        "drawCallback": function(settings) {
            var api = this.api();
            api.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + (api.page() * api.page.len());
            });
        }
    });

    // Collection tab: Fee Type filter (attribute-based, since Fee Type column no longer shown)
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'reportTable') return true;
        var val = $('#collectionFeeTypeFilter').val();
        if (!val) return true;
        var row = reportTable.row(dataIndex).node();
        var feeTypes = ($(row).data('feetypes') || '').toString();
        return feeTypes.split('|').includes(val);
    });
    $('#collectionFeeTypeFilter').on('change', function() {
        reportTable.draw();
    });

    // Due tab: Fee Type filter
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'dueTable') return true;
        var val = $('#dueFeeTypeFilter').val();
        if (!val) return true;
        var row = dueTable.row(dataIndex).node();
        var feeTypes = ($(row).data('feetypes') || '').toString();
        return feeTypes.split('|').includes(val);
    });
    $('#dueFeeTypeFilter').on('change', function() {
        dueTable.draw();
    });

    // Collection tab: student click -> modal
    $(document).on('click', '.student-link', function() {
        var rows = $(this).data('rows');
        var pending = $(this).data('pending');

        $('#studentDetailModalHeader').removeClass('bg-warning').addClass('bg-success');
        $('#studentDetailModalLabel').text($(this).data('student') + ' (' + $(this).data('admission') + ') — Payment History');

        var thead = '<tr><th>Date</th><th>Fee Type</th><th>Mode</th><th>Account</th><th class="text-end">Amount</th><th>Collected By</th></tr>';
        var tbody = '';
        var totalPaid = 0;

        rows.forEach(function(r) {
            totalPaid += parseFloat(r.amount.toString().replace(/,/g, '')) || 0;
            tbody += '<tr><td>' + r.date + '</td><td>' + r.fee_type + '</td><td>' + r.mode + '</td><td>' + r.account + '</td><td class="text-end">' + r.amount + '</td><td>' + r.collected_by + '</td></tr>';
        });

        var totalPending = 0;
        if (pending && pending.length) {
            tbody += '<tr class="table-secondary"><td colspan="6" class="fw-bold">Due Pending Fees</td></tr>';
            pending.forEach(function(p) {
                totalPending += parseFloat(p.pending.toString().replace(/,/g, '')) || 0;
                tbody += '<tr><td>' + p.due_date + '</td><td>' + p.fee_type + '</td><td>-</td><td>-</td>' +
                    '<td class="text-end text-danger">' + p.pending + '</td>' +
                    '<td><span class="badge bg-warning text-dark">' + p.status + '</span></td></tr>';
            });
        }

        var tfoot = '<tr><td colspan="4" class="text-end">Total Paid</td><td class="text-end text-success">' +
            totalPaid.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + '</td><td></td></tr>';
        if (pending && pending.length) {
            tfoot += '<tr><td colspan="4" class="text-end">Total Pending</td><td class="text-end text-danger">' +
                totalPending.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + '</td><td></td></tr>';
        }

        $('#studentDetailTable thead').html(thead);
        $('#studentDetailTable tbody').html(tbody);
        $('#studentDetailTable tfoot').html(tfoot);
        new bootstrap.Modal(document.getElementById('studentDetailModal')).show();
    });

    // Due tab: student click -> modal
    $(document).on('click', '.due-student-link', function() {
        var rows = $(this).data('rows');

        $('#studentDetailModalHeader').removeClass('bg-success').addClass('bg-warning');
        $('#studentDetailModalLabel').text($(this).data('student') + ' (' + $(this).data('admission') + ') — Outstanding Breakdown');

        var thead = '<tr><th>Fee Type</th><th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Pending</th><th>Due Date</th><th>Status</th></tr>';
        var tbody = '';
        var totalAmount = 0,
            totalPaid = 0,
            totalPending = 0;

        rows.forEach(function(r) {
            totalAmount += parseFloat(r.amount.toString().replace(/,/g, '')) || 0;
            totalPaid += parseFloat(r.paid.toString().replace(/,/g, '')) || 0;
            totalPending += parseFloat(r.pending.toString().replace(/,/g, '')) || 0;

            var statusBadge = r.status === 'Paid' ?
                '<span class="badge bg-success">Paid</span>' :
                (r.status === 'Partial' ? '<span class="badge bg-purple">Partial</span>' : '<span class="badge bg-danger">' + r.status + '</span>');

            tbody += '<tr><td>' + r.fee_type + '</td><td class="text-end">' + r.amount + '</td><td class="text-end">' + r.paid + '</td><td class="text-end fw-bold">' + r.pending + '</td><td>' + r.due_date + '</td><td>' + statusBadge + '</td></tr>';
        });

        var tfoot = '<tr><td class="text-end">Total</td>' +
            '<td class="text-end">' + totalAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + '</td>' +
            '<td class="text-end text-success">' + totalPaid.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + '</td>' +
            '<td class="text-end text-danger">' + totalPending.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + '</td>' +
            '<td colspan="2"></td></tr>';

        $('#studentDetailTable thead').html(thead);
        $('#studentDetailTable tbody').html(tbody);
        $('#studentDetailTable tfoot').html(tfoot);
        new bootstrap.Modal(document.getElementById('studentDetailModal')).show();
    });
</script>
@endpush