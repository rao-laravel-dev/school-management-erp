@extends($current_layout)

@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Attendance</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('adminstudent.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">My Attendance</li>
            </ol>
        </nav>
    </div>
</div>

<div class="page-content">
    <div class="container-fluid">
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-3 align-items-center">

            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small">Total Present</p>
                                <h5 class="my-0 text-success fw-bold">{{ $allAttendance->whereIn('status', [1, 2])->count() }}</h5>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto" style="width: 45px; height: 45px; line-height: 45px; text-align: center;">
                                <i class='bx bxs-check-circle'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-danger shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small">Total Absent</p>
                                <h5 class="my-0 text-danger fw-bold">{{ $allAttendance->where('status', 0)->count() }}</h5>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto" style="width: 45px; height: 45px; line-height: 45px; text-align: center;">
                                <i class='bx bxs-x-circle'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-warning shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small">Late / Half-Day</p>
                                <h5 class="my-0 text-warning fw-bold">{{ $allAttendance->whereIn('status', [2, 3])->count() }}</h5>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto" style="width: 45px; height: 45px; line-height: 45px; text-align: center;">
                                <i class='bx bxs-time'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-info shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small">Total Leaves</p>
                                <h5 class="my-0 text-info fw-bold">{{ $allAttendance->where('status', 4)->count() }}</h5>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto" style="width: 45px; height: 45px; line-height: 45px; text-align: center;">
                                <i class='bx bxs-calendar-event'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="card radius-10 border-top border-0 border-4 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Attendance Report: {{ now()->format('F, Y') }}</h4>
                    <span class="text-muted small">Showing records for current month</span>
                </div>
                <div class="table-responsive">
                    <table id="datatable" class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Admission No</th>
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
                            @foreach($allAttendance as $item)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($item->attendance_date)->format('d M, Y') }}</td>

                                <td>{{ $item->enrollment->student->first_name ?? '' }} {{ $item->enrollment->student->last_name ?? '' }}</td>
                                <td>{{ $item->enrollment->student->admission_no ?? 'N/A' }}</td>

                                {{-- Roll No: Enrollment table se fetch kiya gaya hai --}}
                                <td>{{ $item->enrollment->roll_no ?? 'N/A' }}</td>

                                {{-- Parent Phone: Student ke through Parent relationship se --}}
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
                                <td>
                                    @if($item->status == 0)
                                    <span class="text-muted">-</span>
                                    @elseif($item->time_out)
                                    {{ \Carbon\Carbon::parse($item->time_out)->format('h:i A') }}
                                    @else
                                    <span class="badge bg-light text-dark">In School</span>
                                    @endif
                                </td>

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