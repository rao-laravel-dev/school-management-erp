@extends('reception.layout.app')

@section('content')

@php
    $attBadges = ['Present' => 'bg-success', 'Late' => 'bg-warning text-dark', 'Half Day' => 'bg-info text-dark', 'Absent' => 'bg-danger', 'Leave' => 'bg-primary'];
    $enquiryBadges = ['pending' => 'bg-warning text-dark', 'active' => 'bg-success', 'inactive' => 'bg-danger'];
    $complaintBadges = ['pending' => 'bg-warning text-dark', 'in_progress' => 'bg-info text-dark', 'resolved' => 'bg-success'];
    // Open Complaints card progress: kitni complaints resolved hain
    $complaintPercent = (int) $complaintStats->total_c > 0 ? round((int) $complaintStats->resolved_c / (int) $complaintStats->total_c * 100) : 0;
@endphp

<!-- Row 1: cards -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 gy-4 mb-4">
    @can('access-front-office')
    <x-dashboard-stat title="Visitors Today" :value="(int) $visitorStats->c" icon="bx bx-walk" color="info" spark-id="w-visitors" :href="route('visitor-book.index')" href-label="Open Visitor Book">
        <p class="mb-0 font-13 text-secondary">{{ (int) $visitorStats->p }} person(s) &middot; {{ (int) $visitorStats->inside }} still inside</p>
    </x-dashboard-stat>
    <x-dashboard-stat title="Admission Enquiries" :value="(int) $enquiryStats->pending" icon="bx bx-user-plus" color="warning" spark-id="w-enquiries" :href="route('admission-enquiry.index')" href-label="Open Admission Enquiry">
        <p class="mb-0 font-13 text-secondary">Pending &middot; {{ (int) $enquiryStats->due }} due today &middot; {{ (int) $enquiryStats->overdue }} overdue</p>
    </x-dashboard-stat>
    <x-dashboard-stat title="Open Complaints" :value="(int) $complaintStats->open_c" icon="bx bx-message-error" color="danger" :percent="$complaintPercent" :percent-label="$complaintPercent . '% resolved'" :href="route('complain.index')" href-label="Open Complaints">
        <p class="mb-0 font-13 text-secondary">{{ (int) $complaintStats->today_c }} received today</p>
    </x-dashboard-stat>
    @endcan
    @can('access-my-attendance')
    <x-dashboard-stat title="My Attendance" :value="$myAtt['marked'] > 0 ? ($myAtt['present'] + $myAtt['half_day']) . ' / ' . $myAtt['marked'] : '-'" icon="bx bx-calendar-check" color="warning" :percent="$myAtt['marked'] > 0 ? $myAtt['percent'] : 0" :href="route('my.attendance.index')" href-label="Open My Attendance Report">
        <p class="mb-0 font-13 text-secondary">{{ $myAtt['marked'] > 0 ? $myAtt['percent'] . '% this month' : 'Not marked yet' }}</p>
    </x-dashboard-stat>
    @endcan
</div>
<!--end row-->

<!-- Row 2: visitors vs enquiries + attendance today -->
<div class="row">
    @can('access-front-office')
    <div class="col-12 col-lg-8 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header">
                <h6 class="mb-0">Visitors vs Enquiries</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center ms-auto font-13 gap-2 mb-3">
                    <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #0d6efd"></i>Visitors</span>
                    <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #fd7e14"></i>Enquiries</span>
                </div>
                <div class="chart-container-1">
                    @if($sessionVisitorsTotal + $sessionEnquiriesTotal > 0)
                        <canvas id="receptionVisitChart"></canvas>
                    @else
                        <div class="h-100 d-flex align-items-center justify-content-center text-secondary">{{ count($sessionLabels) ? 'No data yet' : 'No active session' }}</div>
                    @endif
                </div>
            </div>
            <div class="row row-cols-1 row-cols-md-3 row-cols-xl-3 g-0 row-group text-center border-top">
                <div class="col">
                    <div class="p-3">
                        <h5 class="mb-0">{{ $sessionVisitorsTotal }}</h5>
                        <small class="mb-0">Session Visitors</small>
                    </div>
                </div>
                <div class="col">
                    <div class="p-3">
                        <h5 class="mb-0">{{ $sessionEnquiriesTotal }}</h5>
                        <small class="mb-0">Session Enquiries</small>
                    </div>
                </div>
                <div class="col">
                    <div class="p-3">
                        <h5 class="mb-0">{{ (int) $enquiryStats->pending }}</h5>
                        <small class="mb-0">Pending Enquiries</small>
                    </div>
                </div>
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

<!-- Row 3: today's visitors + leave -->
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
    @can('access-leave-application')
    <div class="col-12 col-lg-4 d-flex">
        @include('partials.dashboard_leave_card')
    </div>
    @endcan
</div>
<!--end row-->

<!-- Row 4: follow-ups + complaints -->
<div class="row">
    @can('access-front-office')
    <div class="col-12 col-lg-6 d-flex">
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
    <div class="col-12 col-lg-6 d-flex">
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

</div>
<!--end row-->

@endsection

@push('scripts')
<script src="{{ asset('backend/assets/plugins/apexcharts-bundle/js/apexcharts.min.js') }}"></script>
@include('partials.dashboard_sparkline')
<script>
$(function () {
    // Row 1 sparklines: visitors (is mahine ke roz), enquiries (pichle 12 mahine)
    var visitorDaily = @json($visitorDaily);
    var dayLabels = [];
    for (var d = 1; d <= visitorDaily.length; d++) { dayLabels.push(d + ' ' + @json(now()->format('M'))); }
    dashSparkline('#w-visitors', 'area', '#0dcaf0', 'Visitors', visitorDaily, dayLabels, false);
    dashSparkline('#w-enquiries', 'bar', '#ffc107', 'Enquiries', @json($enquirySeries), @json($monthLabels), false);

    // Chart.js: light/dark theme ke hisab se text + grid colors (html.dark-theme)
    function chartTheme() {
        var dark = $('html').hasClass('dark-theme');
        return {
            text: dark ? '#aab0b6' : '#6c757d',
            grid: dark ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.08)'
        };
    }

    // Visitors vs Enquiries (session ke mahine)
    var visitChart = null;
    var visitEl = document.getElementById('receptionVisitChart');
    if (visitEl) {
        var th = chartTheme();
        visitChart = new Chart(visitEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($sessionLabels),
                datasets: [
                    { label: 'Visitors', data: @json($sessionVisitors), backgroundColor: '#0d6efd', borderRadius: { topLeft: 20, topRight: 20, bottomLeft: 0, bottomRight: 0 }, borderSkipped: false, borderWidth: 0 },
                    { label: 'Enquiries', data: @json($sessionEnquiries), backgroundColor: '#fd7e14', borderRadius: { topLeft: 20, topRight: 20, bottomLeft: 0, bottomRight: 0 }, borderSkipped: false, borderWidth: 0 }
                ]
            },
            options: {
                maintainAspectRatio: false,
                barPercentage: 0.5,
                categoryPercentage: 0.8,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: th.text }, grid: { color: th.grid } },
                    y: { beginAtZero: true, ticks: { color: th.text, precision: 0 }, grid: { color: th.grid } }
                }
            }
        });
    }

    // Attendance Today doughnut: Present / Late / Half Day / Absent / Leave
    var el = document.getElementById('receptionAttendanceChart');
    if (el) {
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
    }

    // Theme toggle (app.js pehle html class badalta hai): bar chart ke colors dobara lagao
    $('.dark-mode').on('click', function () {
        if (!visitChart) { return; }
        var t = chartTheme();
        visitChart.options.scales.x.ticks.color = t.text;
        visitChart.options.scales.x.grid.color = t.grid;
        visitChart.options.scales.y.ticks.color = t.text;
        visitChart.options.scales.y.grid.color = t.grid;
        visitChart.update();
    });
});
</script>
@endpush
