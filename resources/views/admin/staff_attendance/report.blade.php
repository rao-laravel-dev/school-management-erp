@extends($current_layout)

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Staff Attendance</div>
    <div class="ps-3">
        <nav>
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('staff_attendance.index') }}">Attendance</a></li>
                <li class="breadcrumb-item active">Staff Attendance Report</li>
            </ol>
        </nav>
    </div>
</div>

<div class="att-stats">
    @include('admin.attendance.partials.stat_card', ['label' => 'Present', 'value' => $summary['present'], 'color' => 'success'])
    @include('admin.attendance.partials.stat_card', ['label' => 'Absent', 'value' => $summary['absent'], 'color' => 'danger'])
    @include('admin.attendance.partials.stat_card', ['label' => 'Half Day', 'value' => $summary['half_day'], 'color' => 'info'])
    @include('admin.attendance.partials.stat_card', ['label' => 'Leave', 'value' => $summary['leave'], 'color' => 'secondary'])
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
                        <td>
                            {{ $record->staff->name ?? '-' }}
                            @if($record->staff && $record->staff->isStaffTrashed())
                                <span class="badge bg-danger ms-1">Trashed</span>
                            @endif
                        </td>
                        <td>{{ $record->staff ? $record->staff->roles->pluck('name')->implode(', ') : '-' }}</td>
                        <td>
                            <span class="badge bg-{{ ['present'=>'success','absent'=>'danger','half_day'=>'info','leave'=>'secondary'][$record->status] }}">
                                {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                            </span>
                        </td>
                        <td>{{ $record->marked_at ? $record->marked_at->format('d-m-Y h:i A') : '-' }}</td> {{-- NEW --}}
                        <td>{{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('h:i A') : '-' }}</td>
                        <td>{{ $record->remarks ?? '-' }}</td>
                        <td>{{ $record->markedBy->name_with_role ?? '-' }}</td>
                        <td>
                            @can('mark-staff-attendance')
                            @php $staffRole = $record->staff ? $record->staff->roles->pluck('name')->first() : null; @endphp
                            @if($staffRole && $roles->contains($staffRole))
                            <a href="{{ route('staff_attendance.edit', ['date' => $record->date->format('Y-m-d'), 'role' => $staffRole]) }}" class="btn btn-sm btn-outline-primary">
                                <i class='bx bx-edit'></i>
                            </a>
                            @else
                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="No staff role, cannot edit">
                                <i class='bx bx-edit'></i>
                            </button>
                            @endif
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