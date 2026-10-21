@extends('teacher.layout.app')

@section('content')
<div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Attendance</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Class Report</li>
                </ol>
            </nav>
        </div>
    </div>

   <div class="card radius-10">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <h5 class="mb-0">Attendance Report</h5>
            
            {{-- Filter Form (Right Side) --}}
            <form method="GET" action="{{ route('attendance.report') }}" class="row g-2 align-items-end">
                <div class="col-6 col-md-auto">
                    <label class="small text-muted">Start</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" max="{{ date('Y-m-d') }}">
                </div>
                <div class="col-6 col-md-auto">
                    <label class="small text-muted">End</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}" max="{{ date('Y-m-d') }}">
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-sm btn-warning px-3">Filter</button>
                </div>
            </form>
        </div>
        <hr>

        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="attendanceTable">
                <thead class="table-light">
                    <tr>
                        <th width="30">#</th>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Class/Section</th>
                        <th>Status</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Remarks</th>
                        <th>Marked By</th>
                    </tr>
                </thead>
                <tbody>
                    @php $index = 1; @endphp
                    @foreach($attendanceRecords as $date => $records)
                        @foreach($records as $item)
                        <tr>
                            <td>{{ $index++ }}</td>
                            <td>{{ \Carbon\Carbon::parse($date)->format('d M, Y') }}</td>
                            <td>{{ $item->enrollment->student->first_name ?? '' }} {{ $item->enrollment->student->last_name ?? '' }}</td>
                            <td>{{ $item->enrollment->schoolClass->name ?? '' }} - {{ $item->enrollment->section->name ?? '' }}</td>
                            <td>
                                @if($item->status == \App\Models\Attendance::STATUS_PRESENT) <span class="badge bg-success">Present</span>
                                @elseif($item->status == \App\Models\Attendance::STATUS_ABSENT) <span class="badge bg-danger">Absent</span>
                                @elseif($item->status == \App\Models\Attendance::STATUS_LATE) <span class="badge bg-warning text-dark">Late</span>
                                @elseif($item->status == \App\Models\Attendance::STATUS_HALF_DAY) <span class="badge bg-info text-dark">Half Day</span>
                                @elseif($item->status == \App\Models\Attendance::STATUS_LEAVE) <span class="badge bg-secondary">{{ strcasecmp(trim((string) $item->remarks), 'Holiday') === 0 ? 'Holiday' : 'Leave' }}</span>
                                @endif
                            </td>
                            <td>{{ $item->created_at ? $item->created_at->format('h:i A') : 'N/A' }}</td>
                            <td>{{ $item->time_out ?? 'N/A' }}</td>
                            <td>{{ $item->remarks ?? '-' }}</td>
                            <td>{{ $item->markedBy->name_with_role ?? '-' }}</td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#attendanceTable').DataTable();
    });
</script>
@endpush