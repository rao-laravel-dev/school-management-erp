@extends($current_layout)

@section('title', 'Book Issue Reports')

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Library', 'url' => '#'],
    ['label' => 'Book Issue', 'url' => route('book_issue.index')],
    ['label' => 'Reports', 'url' => '#'],
]" />

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Book Issue Reports</h4>
        <a href="{{ route('book_issue.index') }}" class="btn btn-sm btn-secondary">
            <i class="bx bx-arrow-back"></i> Back to Book Issues
        </a>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0"><i class="bx bx-filter-alt"></i> Filters</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label form-label-sm">Date From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm">Date To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm">Member Type</label>
                    <select name="member_type" id="member_type" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="student">Student</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm">Category</label>
                    <select name="category_id" id="category_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-sm btn-success w-100">
                        <i class="bx bx-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-3" id="summaryCards">
        <div class="col-md-3 mb-2">
            <div class="card radius-10 border-start border-0 border-3 border-warning shadow-sm bg-primary bg-gradient text-white">
                <div class="card-body">
                    <small>Total Issued</small>
                    <h3 class="mb-0" id="cardTotalIssued">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card radius-10 border-start border-0 border-3 border-danger shadow-sm bg-success bg-gradient text-white">
                <div class="card-body">
                    <small>Total Returned</small>
                    <h3 class="mb-0" id="cardTotalReturned">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm bg-danger bg-gradient text-white">
                <div class="card-body">
                    <small>Total Overdue</small>
                    <h3 class="mb-0" id="cardTotalOverdue">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card radius-10 border-start border-0 border-3 border-primary shadow-sm bg-warning bg-gradient text-dark">
                <div class="card-body">
                    <small>Total Fine</small>
                    <h3 class="mb-0" id="cardTotalFine">0.00</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Report Results</h5>
        </div>
        <div class="card-body">

            <table class="table table-bordered table-striped w-100" id="reportTable">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Book</th>
                        <th>Category</th>
                        <th>Member</th>
                        <th>Type</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Fine</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    $(function() {
        const dataUrl = "{{ route('book_issue.reports.data') }}";

        // 👇 Current month ki 1st date aur Aaj ki date set karne ka code
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');

        const firstDay = `${year}-${month}-01`;
        const today = `${year}-${month}-${day}`;

        $('#date_from').val(firstDay);
        $('#date_to').val(today);

        function statusBadge(status) {
            const map = {
                issued: 'bg-primary',
                returned: 'bg-success',
                overdue: 'bg-danger',
                lost: 'bg-dark',
            };
            const cls = map[status] ?? 'bg-secondary';
            return `<span class="badge ${cls}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
        }

        let table = $('#reportTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: dataUrl,
                data: function(d) {
                    d.date_from = $('#date_from').val();
                    d.date_to = $('#date_to').val();
                    d.member_type = $('#member_type').val();
                    d.category_id = $('#category_id').val();
                },
                dataSrc: function(json) {
                    // Summary cards update
                    if (json.summary) {
                        $('#cardTotalIssued').text(json.summary.total_issued);
                        $('#cardTotalReturned').text(json.summary.total_returned);
                        $('#cardTotalOverdue').text(json.summary.total_overdue);
                        $('#cardTotalFine').text(parseFloat(json.summary.total_fine).toFixed(2));
                    }

                    // 👇 Yahan safe fallback add kar diya hai
                    return json.issues || [];
                }
            },
            columns: [
                {
                    data: null,
                    searchable: false,
                    orderable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { data: 'book_title', name: 'book_title' },
                { data: 'category', name: 'category' },
                {
                    data: 'member_name',
                    name: 'member_name',
                    render: function(data) {
                        return `<span class="fw-semibold text-primary">${data}</span>`;
                    }
                },
                { data: 'member_type', name: 'member_type' },
                { data: 'issue_date', name: 'issue_date' },
                { data: 'due_date', name: 'due_date' },
                { data: 'return_date', name: 'return_date', defaultContent: '-' },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data) {
                        return statusBadge(data);
                    }
                },
                {
                    data: 'fine_amount',
                    name: 'fine_amount',
                    render: function(data) {
                        return parseFloat(data || 0).toFixed(2);
                    }
                }
            ],
            pageLength: 10,
            ordering: true,
            language: {
                emptyTable: `<div class="alert alert-danger m-2 text-center" role="alert">
                                <i class="bx bx-error-circle me-1"></i> No book issue records found for the selected filters!
                             </div>`,
                zeroRecords: `<div class="alert alert-danger m-2 text-center" role="alert">
                                <i class="bx bx-error-circle me-1"></i> No book issue records found for the selected filters!
                             </div>`
            }
        });

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            table.ajax.reload();
        });
    });
</script>
@endpush