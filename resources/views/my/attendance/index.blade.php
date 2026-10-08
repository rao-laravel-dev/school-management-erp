@extends($current_layout)
@section('content')

{{-- My Attendance: logged-in staff ki apni attendance (read-only) --}}
<x-breadcrumb :items="[
    ['label' => 'Attendance', 'url' => '#'],
    ['label' => 'My Attendance', 'url' => route('my.attendance.index')],
]" />

{{-- Summary cards --}}
<div class="row row-cols-2 row-cols-md-5 g-3 mb-3">
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-success mb-0">
            <div class="card-body py-2">
                <small class="text-muted">Present</small>
                <h4 class="mb-0 text-success">{{ $summary['present'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-danger mb-0">
            <div class="card-body py-2">
                <small class="text-muted">Absent</small>
                <h4 class="mb-0 text-danger">{{ $summary['absent'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-info mb-0">
            <div class="card-body py-2">
                <small class="text-muted">Half Day</small>
                <h4 class="mb-0 text-info">{{ $summary['half_day'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-warning mb-0">
            <div class="card-body py-2">
                <small class="text-muted">Leave</small>
                <h4 class="mb-0 text-warning">{{ $summary['leave'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-primary mb-0">
            <div class="card-body py-2">
                <small class="text-muted">Attendance %</small>
                <h4 class="mb-0 text-primary">{{ $summary['percentage'] }}%</h4>
            </div>
        </div>
    </div>
</div>

{{-- Filter + date-wise list --}}
<div class="card radius-10">
    <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
        <h5 class="mb-0 text-primary">My Attendance</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('my.attendance.index') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="small fw-bold" for="from_date">From Date</label>
                <input type="date" name="from_date" id="from_date" class="form-control form-control-sm" value="{{ $fromDate }}">
                @error('from_date') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-3">
                <label class="small fw-bold" for="to_date">To Date</label>
                <input type="date" name="to_date" id="to_date" class="form-control form-control-sm" value="{{ $toDate }}">
                @error('to_date') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-3">
                <label class="small fw-bold" for="status">Status</label>
                <select name="status" id="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="present" {{ $statusFilter == 'present' ? 'selected' : '' }}>Present</option>
                    <option value="absent" {{ $statusFilter == 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="half_day" {{ $statusFilter == 'half_day' ? 'selected' : '' }}>Half Day</option>
                    <option value="leave" {{ $statusFilter == 'leave' ? 'selected' : '' }}>Leave</option>
                </select>
                @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-primary me-2">Filter</button>
                <a href="{{ route('my.attendance.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table id="viewDataTable" class="table table-striped table-bordered align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Marked At</th>
                        <th>Time Out</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $i => $record)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td data-order="{{ $record->date->toDateString() }}">{{ $record->date->format('d-m-Y') }}</td>
                        <td>
                            <span class="badge bg-{{ ['present' => 'success', 'absent' => 'danger', 'half_day' => 'info', 'leave' => 'warning'][$record->status] ?? 'secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                            </span>
                        </td>
                        <td>{{ $record->marked_at ? $record->marked_at->format('d-m-Y h:i A') : '-' }}</td>
                        <td>{{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('h:i A') : '-' }}</td>
                        <td>{{ $record->remarks ?: '-' }}</td>
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
            language: {
                search: 'Search Date:',
                emptyTable: 'No attendance records found for this period'
            }
        });

        @if($errors->any())
        toastr.error(@json($errors->first()));
        @endif
    });
</script>
@endpush
