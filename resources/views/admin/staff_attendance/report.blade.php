@extends($current_layout)

@section('content')
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('admin.dashboard') }}"><i class='bx bx-home-alt'></i></a>
    <i class='bx bx-chevron-right mx-1'></i>
    <a href="{{ route('staff_attendance.index') }}">Attendance</a>
    <i class='bx bx-chevron-right mx-1'></i>
    <span class="fw-semibold">Staff Attendance Report</span>
</div>

<div class="row mb-3">
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-success">
            <div class="card-body py-2">
                <small class="text-muted">Present</small>
                <h4 class="mb-0 text-success">{{ $summary['present'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-danger">
            <div class="card-body py-2">
                <small class="text-muted">Absent</small>
                <h4 class="mb-0 text-danger">{{ $summary['absent'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-info">
            <div class="card-body py-2">
                <small class="text-muted">Half Day</small>
                <h4 class="mb-0 text-info">{{ $summary['half_day'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-warning">
            <div class="card-body py-2">
                <small class="text-muted">Leave</small>
                <h4 class="mb-0 text-warning">{{ $summary['leave'] }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Attendance Report</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label" for="from_date">From Date</label>
                <input type="date" name="from_date" id="from_date" class="form-control form-control-sm" value="{{ $fromDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="to_date">To Date</label>
                <input type="date" name="to_date" id="to_date" class="form-control form-control-sm" value="{{ $toDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="role">Staff</label>
                <select name="role" class="form-select form-select-sm" id="role">
                    <option value="">All Staff</option>
                    @foreach($roles as $roleName)
                    <option value="{{ $roleName }}" {{ $roleFilter == $roleName ? 'selected' : '' }}>{{ ucfirst($roleName) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-sm btn-primary">Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped table-hover" id="reportTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Staff ID</th>
                        <th>Staff Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Marked At</th>
                        <th>Time Out</th>
                        <th>Remarks</th>
                        <th>Marked By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $i => $record)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->date)->format('d-m-Y') }}</td>
                        <td>{{ $record->staff->staff_code ?? '-' }}</td>
                        <td>{{ $record->staff->name ?? '-' }}</td>
                        <td>{{ $record->staff->roles->pluck('name')->implode(', ') ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ ['present'=>'success','absent'=>'danger','half_day'=>'info','leave'=>'warning'][$record->status] }}">
                                {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                            </span>
                        </td>
                        <td>{{ $record->marked_at ? $record->marked_at->format('d-m-Y h:i A') : '-' }}</td> {{-- NEW --}}
                        <td>{{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('h:i A') : '-' }}</td>
                        <td>{{ $record->remarks ?? '-' }}</td>
                        <td>{{ $record->markedBy->name ?? '-' }}</td>
                        <td>
                            @can('manage-staff-attendance')
                            <a href="{{ route('staff_attendance.edit', ['date' => $record->date, 'role' => $record->staff->roles->pluck('name')->first()]) }}" class="btn btn-sm btn-outline-primary">
                                <i class='bx bx-edit'></i>
                            </a>
                            @endcan
                            <a href="{{ route('staff_attendance.show', $record->user_id) }}" class="btn btn-sm btn-outline-info">
                                <i class='bx bx-show'></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center">No records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#reportTable').DataTable();
    });
</script>
@endpush