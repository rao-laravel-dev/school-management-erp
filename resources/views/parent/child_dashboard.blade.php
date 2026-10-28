@extends('parent.layout.app')

@section('content')

@php
    $attBadges = ['Present' => 'bg-success', 'Late' => 'bg-warning text-dark', 'Half Day' => 'bg-info text-dark', 'Absent' => 'bg-danger', 'Leave' => 'bg-secondary'];
    // Card progress: aaj ke periods (khatam / total)
    $periodsTotal = $offDayLabel ? 0 : $todayPeriods->count();
    $periodsPercent = $periodsTotal > 0 ? round($periodsDone / $periodsTotal * 100) : 0;
    $latestResult = $recentResults->first();
    $latestPercent = $latestResult ? (float) $latestResult->percentage : 0;
    $halfDays = $monthCounts['Half Day'];
    $attSubtext = $monthDaysCounted > 0
        ? $monthDaysPresent . ' of ' . $monthDaysCounted . ' days present' . ($halfDays > 0 ? ' (' . $halfDays . ' half day)' : '')
        : 'Not marked yet';
@endphp

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Parent Dashboard</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item active">{{ $student->full_name }}'s Overview</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="{{ route('parent.students.dashboard', $student->id) }}" class="btn btn-outline-primary btn-sm px-3"><i class="bx bx-id-card"></i> Student Information</a>
    </div>
</div>

@include('parent.partials.child_switcher')

@if(!$enrollment)
    <div class="alert alert-warning mt-3">Not enrolled in the current session.</div>
@endif

<!-- Row 1: cards -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 gy-4 mb-4 mt-0">
    @can('access-parent-students')
    <x-dashboard-stat title="Attendance" :value="$monthAttPercent !== null ? $monthAttPercent . '%' : '-'" icon="bx bx-calendar-check" color="info" :percent="$monthAttPercent !== null ? $monthAttPercent : 0" :href="route('parent.students.dashboard', $student->id)" href-label="Open Student Information">
        <p class="mb-0 font-13 text-secondary">{{ $attSubtext }}</p>
    </x-dashboard-stat>
    @endcan
    @can('access-parent-timetable')
    <x-dashboard-stat title="Today's Classes" :value="$periodsTotal" icon="bx bx-time-five" color="success" :percent="$periodsPercent" :percent-label="$periodsTotal > 0 ? $periodsDone . ' of ' . $periodsTotal . ' periods done' : null" :href="route('parent.students.class_schedule.index', $student->id)" href-label="Open Class Schedule">
        <p class="mb-0 font-13 text-secondary">{{ $offDayLabel ? 'Off day: ' . $offDayLabel : now()->format('l, d-m-Y') }}</p>
    </x-dashboard-stat>
    @endcan
    @can('access-parent-homework')
    <x-dashboard-stat title="Pending Homework" :value="$pendingHomework" icon="bx bx-book-content" color="warning" spark-id="w-homework" :href="route('parent.students.homework.index', $student->id)" href-label="Open Homework">
        <p class="mb-0 font-13 text-secondary">{{ $pastDueHomework }} past due</p>
    </x-dashboard-stat>
    @endcan
    @can('access-parent-results')
    <x-dashboard-stat title="Latest Result" :value="$latestResult ? number_format($latestPercent, 2) . '% · ' . (optional($latestResult->grade)->grade_name ?: '-') : '-'" icon="bx bx-medal" color="primary" :percent="$latestPercent" :href="route('parent.students.results.index', $student->id)" href-label="Open Results">
        <p class="mb-0 font-13 text-secondary">{{ $latestResult ? $latestResult->exam->name : 'No published results' }}</p>
    </x-dashboard-stat>
    @endcan
</div>
<!--end row-->

@can('access-parent-students')
<!-- Row 2: session attendance + this month -->
<div class="row">
    <div class="col-12 col-lg-8 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header">
                <h6 class="mb-0">Attendance (Session)</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center ms-auto font-13 gap-2 mb-3">
                    <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #17a00e"></i>Present</span>
                    <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #f41127"></i>Absent</span>
                </div>
                <div class="chart-container-1">
                    @if($sessionPresentTotal + $sessionAbsentTotal > 0)
                        <canvas id="childAttChart"></canvas>
                    @else
                        <div class="h-100 d-flex align-items-center justify-content-center text-secondary">{{ count($sessionLabels) ? 'No data yet' : 'No active session' }}</div>
                    @endif
                </div>
            </div>
            <div class="row row-cols-1 row-cols-md-3 row-cols-xl-3 g-0 row-group text-center border-top">
                <div class="col">
                    <div class="p-3">
                        <h5 class="mb-0">{{ $sessionPresentTotal }}</h5>
                        <small class="mb-0">Session Present</small>
                    </div>
                </div>
                <div class="col">
                    <div class="p-3">
                        <h5 class="mb-0">{{ $sessionAbsentTotal }}</h5>
                        <small class="mb-0">Session Absent</small>
                    </div>
                </div>
                <div class="col">
                    <div class="p-3">
                        <h5 class="mb-0">{{ $sessionAttPercent }}%</h5>
                        <small class="mb-0">Attendance</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header">
                <h6 class="mb-0">This Month</h6>
            </div>
            <div class="card-body">
                <div class="chart-container-2">
                    @if(array_sum($monthCounts) > 0)
                        <canvas id="childMonthChart"></canvas>
                    @else
                        <div class="h-100 d-flex align-items-center justify-content-center text-secondary">Not marked yet</div>
                    @endif
                </div>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($monthCounts as $attLabel => $attCount)
                <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center{{ $loop->first ? ' border-top' : '' }}">{{ $attLabel }} <span class="badge {{ $attBadges[$attLabel] }} rounded-pill">{{ $attCount }}</span></li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
<!--end row-->
@endcan

<!-- Row 3: timetable + recent results -->
<div class="row">
    @can('access-parent-timetable')
    <div class="col-12 col-lg-8 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0">Today's Timetable</h6>
                    <a href="{{ route('parent.students.class_schedule.index', $student->id) }}" class="btn btn-sm btn-outline-primary ms-auto">Class Schedule</a>
                </div>
            </div>
            <div class="card-body">
                @if($offDayLabel)
                    <div class="text-secondary text-center py-4">Off day: {{ $offDayLabel }}</div>
                @elseif($todayPeriods->isEmpty())
                    <div class="text-secondary text-center py-4">No classes today</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Time</th>
                                    <th>Subject</th>
                                    <th>Teacher</th>
                                    <th>Room</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todayPeriods as $p)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="text-nowrap">{{ $p->time_from_formatted }} - {{ $p->time_to_formatted }}</td>
                                    <td>{{ optional($p->subject)->name }}</td>
                                    <td>{{ $p->teacher ? trim($p->teacher->first_name . ' ' . $p->teacher->last_name) : '-' }}</td>
                                    <td>{{ $roomNo ?: '-' }}</td>
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
    @can('access-parent-results')
    <div class="col-12 col-lg-4 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0">Recent Results</h6>
                    <a href="{{ route('parent.students.results.index', $student->id) }}" class="btn btn-sm btn-outline-primary ms-auto">All</a>
                </div>
            </div>
            @if($recentResults->isEmpty())
                <div class="card-body text-secondary text-center py-4">No published results</div>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($recentResults as $res)
                    @php
                        $resPercent = (float) $res->percentage;
                        $resBadge = $resPercent >= 80 ? 'bg-success' : ($resPercent >= 60 ? 'bg-primary' : ($resPercent >= 50 ? 'bg-warning text-dark' : 'bg-danger'));
                    @endphp
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center gap-2">
                        <div style="min-width: 0;">
                            <div class="fw-semibold text-break">{{ $res->exam->name }}</div>
                            <div class="font-13 text-secondary">{{ optional($res->exam->examType)->name ?: '-' }}</div>
                        </div>
                        <div class="text-nowrap">
                            <span class="badge {{ $resBadge }} rounded-pill">{{ number_format($resPercent, 2) }}%</span>
                            <span class="badge bg-secondary rounded-pill">{{ optional($res->grade)->grade_name ?: '-' }}</span>
                        </div>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
    @endcan
</div>
<!--end row-->

<!-- Row 4: upcoming homework + class teacher -->
<div class="row">
    @can('access-parent-homework')
    <div class="col-12 col-lg-6 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0">Upcoming Homework</h6>
                    <a href="{{ route('parent.students.homework.index', $student->id) }}" class="btn btn-sm btn-outline-primary ms-auto">All</a>
                </div>
            </div>
            @if($upcomingHomework->isEmpty())
                <div class="card-body text-secondary text-center py-4">No pending homework</div>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($upcomingHomework as $hw)
                    <li class="list-group-item bg-transparent">
                        <div class="fw-semibold text-break">{{ $hw->title }}</div>
                        <div class="font-13 text-secondary text-break">{{ optional($hw->subject)->name }}</div>
                        <div class="font-13 text-secondary">Given {{ $hw->homework_date->format('d-m-Y') }} &middot; Due {{ $hw->due_date->format('d-m-Y') }}</div>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
    @endcan
    @can('access-parent-students')
    <div class="col-12 col-lg-6 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header">
                <h6 class="mb-0">Class Teacher</h6>
            </div>
            @if($assignedTeacher)
                <div class="card-body">
                    <div class="fw-semibold">{{ ucfirst($assignedTeacher->first_name) }} {{ ucfirst($assignedTeacher->last_name) }}</div>
                    <div class="font-13 text-secondary">{{ $assignedTeacher->class_name }} - {{ $assignedTeacher->section_name }}</div>
                </div>
            @else
                <div class="card-body text-secondary text-center py-4">No class teacher assigned</div>
            @endif
        </div>
    </div>
    @endcan
</div>
<!--end row-->

@endsection

@push('scripts')
    <script src="{{ asset('backend/assets/plugins/chartjs/js/chart.js') }}"></script>
    @include('partials.dashboard_sparkline')
    <script>
    $(function () {
        // Pending Homework sparkline: pichle 12 mahine
        dashSparkline('#w-homework', 'bar', '#ffc107', 'Homework', @json($homeworkSeries), @json($homeworkMonthLabels), false);

        // Chart.js: light/dark theme ke hisab se text + grid colors (html.dark-theme)
        function chartTheme() {
            var dark = $('html').hasClass('dark-theme');
            return {
                text: dark ? '#aab0b6' : '#6c757d',
                grid: dark ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.08)'
            };
        }

        // Attendance (Session): Present vs Absent per mahina
        var attChart = null;
        var attEl = document.getElementById('childAttChart');
        if (attEl) {
            var th = chartTheme();
            attChart = new Chart(attEl.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($sessionLabels),
                    datasets: [
                        { label: 'Present', data: @json($sessionPresent), barPercentage: 0.5, categoryPercentage: 0.8, backgroundColor: '#17a00e', borderRadius: { topLeft: 20, topRight: 20, bottomLeft: 0, bottomRight: 0 }, borderSkipped: false, borderWidth: 0 },
                        { label: 'Absent', data: @json($sessionAbsent), barPercentage: 0.5, categoryPercentage: 0.8, backgroundColor: '#f41127', borderRadius: { topLeft: 20, topRight: 20, bottomLeft: 0, bottomRight: 0 }, borderSkipped: false, borderWidth: 0 }
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

        // This Month doughnut: Present / Late / Half Day / Absent / Leave
        var el = document.getElementById('childMonthChart');
        if (el) {
            new Chart(el.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: @json(array_keys($monthCounts)),
                    datasets: [{
                        data: @json(array_values($monthCounts)),
                        backgroundColor: ['#17a00e', '#ffc107', '#0dcaf0', '#f41127', '#6c757d'],
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
            if (!attChart) { return; }
            var t = chartTheme();
            attChart.options.scales.x.ticks.color = t.text;
            attChart.options.scales.x.grid.color = t.grid;
            attChart.options.scales.y.ticks.color = t.text;
            attChart.options.scales.y.grid.color = t.grid;
            attChart.update();
        });
    });
    </script>
@endpush
