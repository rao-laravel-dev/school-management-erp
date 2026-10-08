@extends($current_layout)
@section('content')

{{-- My Salary Slips: har staff role, sirf apni slips (read-only) --}}
<x-breadcrumb :items="[
    ['label' => 'Payroll', 'url' => '#'],
    ['label' => 'My Salary Slips', 'url' => route('my.salary_slips.index')],
]" />

<div class="card radius-10">
    <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
        <h5 class="mb-0 text-primary">My Salary Slips</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="viewDataTable" class="table table-striped table-bordered align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Month</th>
                        <th>Total Days</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Half Day</th>
                        <th>Leave</th>
                        <th>Attendance Deduction</th>
                        <th>Net Salary</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($slips as $index => $slip)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td data-order="{{ $slip->month }}">{{ \Carbon\Carbon::parse($slip->month)->format('F Y') }}</td>
                        <td>{{ $slip->total_days ?? '-' }}</td>
                        <td>{{ $slip->present_days ?? '-' }}</td>
                        <td>{{ $slip->absent_days ?? '-' }}</td>
                        <td>{{ $slip->half_days ?? '-' }}</td>
                        <td>{{ $slip->leave_days ?? '-' }}</td>
                        <td>{{ number_format($slip->attendance_deduction ?? 0, 2) }}</td>
                        <td>{{ number_format($slip->net_salary ?? 0, 2) }}</td>
                        <td>
                            @if($slip->status === 'paid')
                                <span class="badge bg-success">Paid</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('my.salary_slips.show', $slip->id) }}" class="btn btn-sm btn-outline-primary" title="View / Print">
                                <i class="bx bx-show"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {
        $('#viewDataTable').DataTable({
            pageLength: 10,
            order: [[1, 'desc']],
            columnDefs: [{ orderable: false, targets: -1 }],
            language: {
                search: 'Search Slip:',
                emptyTable: 'No salary slips generated yet'
            }
        });
    });
</script>
@endpush
