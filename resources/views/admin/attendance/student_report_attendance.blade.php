@extends($current_layout)

@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Attendance</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Attendance Report</li>
            </ol>
        </nav>
    </div>
</div>

<div class="page-content">
    <div class="container-fluid">
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-3">
            <div class="col"><div class="card radius-10 border-start border-0 border-3 border-success shadow-sm"><div class="card-body p-3"><div class="d-flex align-items-center"><div><p class="mb-0 text-secondary small">Total Present</p><h5 class="my-0 text-success fw-bold">{{ $attendanceRecords->whereIn('status', [1, 2])->count() }}</h5></div><div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto"><i class='bx bxs-check-circle'></i></div></div></div></div></div>
            <div class="col"><div class="card radius-10 border-start border-0 border-3 border-danger shadow-sm"><div class="card-body p-3"><div class="d-flex align-items-center"><div><p class="mb-0 text-secondary small">Total Absent</p><h5 class="my-0 text-danger fw-bold">{{ $attendanceRecords->where('status', 0)->count() }}</h5></div><div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto"><i class='bx bxs-x-circle'></i></div></div></div></div></div>
            <div class="col"><div class="card radius-10 border-start border-0 border-3 border-warning shadow-sm"><div class="card-body p-3"><div class="d-flex align-items-center"><div><p class="mb-0 text-secondary small">Late / Half-Day</p><h5 class="my-0 text-warning fw-bold">{{ $attendanceRecords->whereIn('status', [2, 3])->count() }}</h5></div><div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto"><i class='bx bxs-time'></i></div></div></div></div></div>
            <div class="col"><div class="card radius-10 border-start border-0 border-3 border-info shadow-sm"><div class="card-body p-3"><div class="d-flex align-items-center"><div><p class="mb-0 text-secondary small">Total Leaves</p><h5 class="my-0 text-info fw-bold">{{ $attendanceRecords->where('status', 4)->count() }}</h5></div><div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto"><i class='bx bxs-calendar-event'></i></div></div></div></div></div>
        </div>

        <div class="card radius-10 mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('student.attendance.report') }}" class="row align-items-end g-3">
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-8 text-md-end">
                        <button type="submit" class="btn btn-primary px-4"><i class="bx bx-filter-alt"></i> Filter</button>
                        <a href="?start_date={{ now()->format('Y-m-d') }}&end_date={{ now()->format('Y-m-d') }}" class="btn btn-outline-dark">Today</a>
                        <a href="?start_date={{ now()->subWeek()->format('Y-m-d') }}&end_date={{ now()->format('Y-m-d') }}" class="btn btn-outline-dark">1 Week</a>
                        <a href="?start_date={{ now()->subMonths(3)->format('Y-m-d') }}&end_date={{ now()->format('Y-m-d') }}" class="btn btn-outline-dark">3 Months</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card radius-10 border-top border-0 border-4 border-primary">
            <div class="card-body">
                <h4 class="mb-3">Attendance Report Details</h4>
                <div class="table-responsive">
                    <table id="datatable" class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Adm No</th>
                                <th>Roll No</th>
                                <th>Parent Phone</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Group</th>
                                <th>Status</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendanceRecords as $item)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($item->attendance_date)->format('d M, Y') }}</td>
                                <td>{{ $item->enrollment->student->first_name ?? '' }} {{ $item->enrollment->student->last_name ?? '' }}</td>
                                <td>{{ $item->enrollment->student->admission_no ?? 'N/A' }}</td>
                                <td>{{ $item->enrollment->roll_no ?? 'N/A' }}</td>
                                <td>{{ $item->enrollment->student->parent->father_phone ?? 'N/A' }}</td>
                                <td>{{ $item->enrollment->schoolClass->name ?? 'N/A' }}</td>
                                <td>{{ $item->enrollment->section->name ?? 'N/A' }}</td>
                                <td>{{ $item->enrollment->group->name ?? '-' }}</td>
                                <td>
                                    @if($item->status == 1) <span class="badge bg-success-subtle text-success">Present</span>
                                    @elseif($item->status == 2) <span class="badge bg-info-subtle text-info">Late</span>
                                    @elseif($item->status == 3) <span class="badge bg-warning-subtle text-warning">Half-Day</span>
                                    @elseif($item->status == 0) <span class="badge bg-danger-subtle text-danger">Absent</span>
                                    @else <span class="badge bg-secondary-subtle text-secondary">Leave</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('h:i A') }}</td>
                                <td>{{ $item->time_out ? \Carbon\Carbon::parse($item->time_out)->format('h:i A') : 'N/A' }}</td>
                                <td>{{ $item->remarks ?? '-' }}</td>
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