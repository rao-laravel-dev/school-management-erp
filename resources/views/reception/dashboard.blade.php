@extends('reception.layout.app')

@section('content')

@php
    $attBadges = ['Present' => 'bg-success', 'Late' => 'bg-warning text-dark', 'Half Day' => 'bg-info text-dark', 'Absent' => 'bg-danger', 'Leave' => 'bg-primary'];
    $leaveBadges = ['pending' => 'bg-warning text-dark', 'approved' => 'bg-success', 'rejected' => 'bg-danger'];
    $enquiryBadges = ['pending' => 'bg-warning text-dark', 'active' => 'bg-success', 'inactive' => 'bg-danger'];
    $complaintBadges = ['pending' => 'bg-warning text-dark', 'in_progress' => 'bg-info text-dark', 'resolved' => 'bg-success'];
@endphp

<!-- Row 1: cards -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 gy-4 mb-4">
    @can('access-front-office')
    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-info h-100 mb-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Visitors Today</p>
                        <h4 class="my-1 text-info">{{ (int) $visitorStats->c }}</h4>
                        <p class="mb-0 font-13 text-secondary">{{ (int) $visitorStats->p }} person(s) &middot; {{ (int) $visitorStats->inside }} still inside</p>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto"><i class='bx bx-walk'></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-warning h-100 mb-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Admission Enquiries</p>
                        <h4 class="my-1 text-warning">{{ (int) $enquiryStats->pending }}</h4>
                        <p class="mb-0 font-13 text-secondary">Pending &middot; {{ (int) $enquiryStats->due }} due today &middot; {{ (int) $enquiryStats->overdue }} overdue</p>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto"><i class='bx bx-user-plus'></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-danger h-100 mb-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Open Complaints</p>
                        <h4 class="my-1 text-danger">{{ (int) $complaintStats->open_c }}</h4>
                        <p class="mb-0 font-13 text-secondary">{{ (int) $complaintStats->today_c }} received today</p>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto"><i class='bx bx-message-error'></i></div>
                </div>
            </div>
        </div>
    </div>
    @endcan
    @can('access-my-attendance')
    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-success h-100 mb-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">My Attendance</p>
                        @if($myAtt['marked'] > 0)
                            <h4 class="my-1 text-success">{{ $myAtt['present'] + $myAtt['half_day'] }} / {{ $myAtt['marked'] }}</h4>
                            <p class="mb-0 font-13 text-secondary">{{ $myAtt['percent'] }}% this month</p>
                        @else
                            <h4 class="my-1 text-success">-</h4>
                            <p class="mb-0 font-13 text-secondary">Not marked yet</p>
                        @endif
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto"><i class='bx bx-calendar-check'></i></div>
                </div>
            </div>
        </div>
    </div>
    @endcan
</div>
<!--end row-->

<!-- Row 2: today's visitors + attendance today -->
<div class="row">
    @can('access-front-office')
    <div class="col-12 col-lg-8 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0">Today's Visitors</h6>
                    <a href="{{ route('visitor-book.index') }}" class="btn btn-sm btn-outline-primary ms-auto">Visitor Book</a>
                </div>
            </div>
            <div class="card-body">
                @if($todayVisitors->isEmpty())
                    <div class="text-secondary text-center py-4">No visitors today</div>
                @else
                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Purpose</th>
                                    <th>Meeting With</th>
                                    <th>In</th>
                                    <th>Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todayVisitors as $v)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ ucwords($v->name) }}</td>
                                    <td>{{ ucwords(optional($v->purpose)->name ?: '-') }}</td>
                                    <td>{{ $v->meeting_with_type ? ucfirst($v->meeting_with_type) : '-' }}</td>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($v->in_time)->format('h:i a') }}</td>
                                    <td class="text-nowrap">
                                        @if($v->out_time)
                                            {{ \Carbon\Carbon::parse($v->out_time)->format('h:i a') }}
                                        @else
                                            <span class="badge bg-success rounded-pill">Inside</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endcan
    @canany(['access-attendance', 'access-staff-attendance'])
    <div class="col-12 col-lg-4 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header">
                <h6 class="mb-0">Attendance Today</h6>
            </div>
            @can('access-attendance')
            <div class="card-body">
                <div class="chart-container-2">
                    @if(array_sum($attendanceCounts) > 0)
                        <canvas id="receptionAttendanceChart"></canvas>
                    @else
                        <div class="h-100 d-flex align-items-center justify-content-center text-secondary">{{ $studentsHoliday ? 'Holiday today' : 'Not marked yet' }}</div>
                    @endif
                </div>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($attendanceCounts as $attLabel => $attCount)
                <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center{{ $loop->first ? ' border-top' : '' }}">{{ $attLabel }} <span class="badge {{ $attBadges[$attLabel] }} rounded-pill">{{ $attCount }}</span></li>
                @endforeach
            </ul>
            @endcan
            @can('access-staff-attendance')
            <div class="card-body border-top">
                <div class="d-flex justify-content-between"><span class="text-secondary">Staff marked</span><span class="fw-semibold">{{ $staffMarked }} / {{ $staffTotal }}</span></div>
            </div>
            @endcan
            <div class="card-body d-flex flex-wrap gap-2 pt-0">
                @can('access-scan-attendance')
                <a href="{{ route('attendance.scan') }}" class="btn btn-sm btn-primary">Scan Attendance</a>
                @endcan
                @can('mark-staff-attendance')
                @if($staffMarked < $staffTotal)
                <a href="{{ route('staff_attendance.create') }}" class="btn btn-sm btn-outline-primary">Mark Staff Attendance</a>
                @endif
                @endcan
                @can('access-attendance')
                <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-secondary">Class Report</a>
                @endcan
            </div>
        </div>
    </div>
    @endcanany
</div>
<!--end row-->

<!-- Row 3: follow-ups, complaints, leave -->
<div class="row">
    @can('access-front-office')
    <div class="col-12 col-lg-4 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0">Follow-ups Due Today</h6>
                    <a href="{{ route('admission-enquiry.index') }}" class="btn btn-sm btn-outline-primary ms-auto">All</a>
                </div>
            </div>
            @if($dueEnquiries->isEmpty())
                <div class="card-body text-secondary text-center py-4">No follow-ups today</div>
            @else
                <ul class="list-group list-group-flush" style="max-height: 320px; overflow-y: auto;">
                    @foreach($dueEnquiries as $en)
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-start gap-2">
                        <div style="min-width: 0;">
                            <div class="fw-semibold text-break">{{ $en->name }}</div>
                            <div class="font-13 text-secondary text-break">{{ $en->phone }} &middot; {{ optional($en->purpose)->name ?: '-' }}</div>
                        </div>
                        <span class="badge {{ isset($enquiryBadges[$en->status]) ? $enquiryBadges[$en->status] : 'bg-secondary' }} rounded-pill">{{ ucfirst($en->status) }}</span>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
    <div class="col-12 col-lg-4 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0">Complaints Today</h6>
                    <a href="{{ route('complain.index') }}" class="btn btn-sm btn-outline-primary ms-auto">All</a>
                </div>
            </div>
            @if($todayComplaints->isEmpty())
                <div class="card-body text-secondary text-center py-4">No complaints today</div>
            @else
                <ul class="list-group list-group-flush" style="max-height: 320px; overflow-y: auto;">
                    @foreach($todayComplaints as $cp)
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-start gap-2">
                        <div style="min-width: 0;">
                            <div class="fw-semibold text-break">{{ $cp->complaint_by }}</div>
                            <div class="font-13 text-secondary text-break">{{ ucwords(optional($cp->complaintType)->name ?: '-') }}</div>
                        </div>
                        <span class="badge {{ isset($complaintBadges[$cp->status]) ? $complaintBadges[$cp->status] : 'bg-secondary' }} rounded-pill">{{ ucfirst(str_replace('_', ' ', $cp->status)) }}</span>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
    @endcan
    @can('access-leave-application')
    <div class="col-12 col-lg-4 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0">My Pending Leave</h6>
                    <a href="{{ route('staffleaveapp.index') }}" class="btn btn-sm btn-outline-primary ms-auto">All</a>
                </div>
            </div>
            @if($pendingLeaves->isEmpty())
                <div class="card-body text-secondary text-center py-4">No pending leave requests</div>
            @else
                <ul class="list-group list-group-flush" style="max-height: 320px; overflow-y: auto;">
                    @foreach($pendingLeaves as $lv)
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-start gap-2">
                        <div style="min-width: 0;">
                            <div class="fw-semibold text-break">{{ ucfirst($lv->leave_type) }}</div>
                            <div class="font-13 text-secondary">{{ \Carbon\Carbon::parse($lv->from_date)->format('d M') }} - {{ \Carbon\Carbon::parse($lv->to_date)->format('d M Y') }} &middot; {{ rtrim(rtrim($lv->total_days, '0'), '.') }} day(s)</div>
                        </div>
                        <span class="badge {{ isset($leaveBadges[$lv->status]) ? $leaveBadges[$lv->status] : 'bg-secondary' }} rounded-pill">{{ ucfirst($lv->status) }}</span>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
    @endcan
</div>
<!--end row-->

@endsection

@push('scripts')
<script>
// Attendance Today doughnut: Present / Late / Half Day / Absent / Leave
$(function () {
    var el = document.getElementById('receptionAttendanceChart');
    if (!el) { return; }
    new Chart(el.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: @json(array_keys($attendanceCounts)),
            datasets: [{
                data: @json(array_values($attendanceCounts)),
                backgroundColor: ['#17a00e', '#ffc107', '#0dcaf0', '#f41127', '#0d6efd'],
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: 82,
            plugins: { legend: { display: false } }
        }
    });
});
</script>
@endpush
