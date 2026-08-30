@extends($current_layout)

@section('content')
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('admin.dashboard') }}"><i class='bx bx-home-alt'></i></a>
    <i class='bx bx-chevron-right mx-1'></i>
    <span>Attendance</span>
    <i class='bx bx-chevron-right mx-1'></i>
    <span class="fw-semibold">Staff Attendance</span>
</div>

<div class="row mb-3">
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-primary">
            <div class="card-body py-2">
                <small class="text-muted">Total</small>
                <h4 class="mb-0 text-primary">{{ $summary['total'] }}</h4>
            </div>
        </div>
    </div>
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
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Staff Attendance — {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</h5>
        @can('manage-staff-attendance')
        <a href="{{ route('staff_attendance.create', ['date' => $date]) }}" class="btn btn-sm btn-primary">
            <i class='bx bx-edit'></i> Mark Attendance
        </a>
        @endcan
    </div>

    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label" for="date">Date</label>
                <input type="date" name="date" id="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="role">Staff</label>
                <select name="role" id="role" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Select Staff Role</option>
                    @foreach($roles as $roleName)
                    <option value="{{ $roleName }}" {{ $roleFilter == $roleName ? 'selected' : '' }}>{{ ucfirst($roleName) }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped table-hover" id="staffIndexTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Staff ID</th>
                        <th>Staff Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Marked At</th>
                        <th>Time Out</th>
                        <th>Remarks</th>
                        <th>Marked By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $i => $record)
                    <tr>
                        <td>{{ $i + 1 }}</td>
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
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No attendance marked for this date yet.</td>
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
        $('#staffIndexTable').DataTable();
    });
</script>
@endpush