@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Payroll', 'url' => '#'],
    ['label' => 'My Salary Slips', 'url' => route('teacher.salary_slips.index')],
]" />

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semi-bold text-primary">My Salary Slips</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="salarySlipsTable" class="table table-bordered table-striped w-100">
                <thead>
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
                    </tr>
                </thead>
                <tbody>
                    @foreach($slips as $index => $slip)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($slip->month)->format('F Y') }}</td>
                        <td>{{ $slip->total_days ?? '-' }}</td>
                        <td>{{ $slip->present_days ?? '-' }}</td>
                        <td>{{ $slip->absent_days ?? '-' }}</td>
                        <td>{{ $slip->half_days ?? '-' }}</td>
                        <td>{{ $slip->leave_days ?? '-' }}</td>
                        <td>{{ number_format($slip->attendance_deduction ?? 0, 2) }}</td>
                        <td>{{ number_format($slip->net_salary ?? 0, 2) }}</td>
                        <td>
                            @if(($slip->status ?? '') === 'paid')
                                <span class="badge bg-success">Paid</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
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
        $('#salarySlipsTable').DataTable({
            order: [[1, 'desc']],
            language: {
                emptyTable: `<div class="alert alert-danger d-flex align-items-center justify-content-center text-center mb-0" role="alert"><i class='bx bx-error-circle me-2'></i><span>No salary slips generated yet</span></div>`
            }
        });
    });
</script>
@endpush