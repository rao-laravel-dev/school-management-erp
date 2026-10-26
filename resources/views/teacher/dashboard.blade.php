@extends('teacher.layout.app')
@section('content')

@php
    $isClassTeacher = $myClasses->isNotEmpty();
    $classLabel = $myClasses->map(fn($a) => optional($a->schoolClass)->name . '-' . optional($a->section)->name)->implode(', ');
    $attBadges = ['Present' => 'bg-success', 'Late' => 'bg-warning text-dark', 'Half Day' => 'bg-info text-dark', 'Absent' => 'bg-danger', 'Leave' => 'bg-primary'];
    // Card progress: aaj ke periods (khatam / total) aur class ki present %
    $periodsTotal = $offDayLabel ? 0 : $todayPeriods->count();
    $periodsPercent = $periodsTotal > 0 ? round($periodsDone / $periodsTotal * 100) : 0;
    $classPercent = ($classMarked && !$classHoliday && $rosterTotal > 0) ? min(100, round($classCounts['Present'] / $rosterTotal * 100)) : 0;
@endphp

@if($profileMissing)
<div class="alert alert-warning py-2 mb-3">Teacher profile not linked. Please contact the admin.</div>
@endif

<!-- Row 1: cards -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 gy-4 mb-4">
    <x-dashboard-stat title="Today's Classes" :value="$periodsTotal" icon="bx bx-time-five" color="info" :percent="$periodsPercent" :percent-label="$periodsTotal > 0 ? $periodsDone . ' of ' . $periodsTotal . ' periods done' : null">
        <p class="mb-0 font-13 text-secondary">{{ $offDayLabel ? 'Off day: ' . $offDayLabel : now()->format('l, d M') }}</p>
    </x-dashboard-stat>
    @php
        if (!$isClassTeacher) {
            $classValue = '-'; $classSub = 'No class assigned';
        } elseif ($classHoliday) {
            $classValue = 'Holiday'; $classSub = $classLabel;
        } elseif (!$classMarked) {
            $classValue = '- / ' . $rosterTotal; $classSub = 'Not marked yet · ' . $classLabel;
        } else {
            $classValue = $classCounts['Present'] . ' / ' . $rosterTotal; $classSub = 'Present today · ' . $classLabel;
        }
    @endphp
    <x-dashboard-stat title="My Class Today" :value="$classValue" icon="bx bxs-graduation" color="success" :percent="$classPercent">
        <p class="mb-0 font-13 text-secondary">{{ $classSub }}</p>
    </x-dashboard-stat>
    <x-dashboard-stat title="Homework Given" :value="$homeworkThisMonth" icon="bx bx-book-content" color="warning" spark-id="w-homework">
        <p class="mb-0 font-13 text-secondary">This month</p>
    </x-dashboard-stat>
    @can('access-my-attendance')
    <x-dashboard-stat title="My Attendance" :value="$myAtt['marked'] > 0 ? ($myAtt['present'] + $myAtt['half_day']) . ' / ' . $myAtt['marked'] : '-'" icon="bx bx-calendar-check" color="warning" :percent="$myAtt['marked'] > 0 ? $myAtt['percent'] : 0" :href="route('my.attendance.index')" href-label="Open My Attendance Report">
        <p class="mb-0 font-13 text-secondary">{{ $myAtt['marked'] > 0 ? $myAtt['percent'] . '% this month' : 'Not marked yet' }}</p>
    </x-dashboard-stat>
    @endcan
</div>
<!--end row-->

<!-- Row 2: my class attendance (session) + my class today -->
<div class="row">
    <div class="col-12 col-lg-8 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header">
                <h6 class="mb-0">My Class Attendance (Session)</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center ms-auto font-13 gap-2 mb-3">
                    <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #17a00e"></i>Present</span>
                    <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #f41127"></i>Absent</span>
                </div>
                <div class="chart-container-1">
                    @if($isClassTeacher && $sessionPresentTotal + $sessionAbsentTotal > 0)
                        <canvas id="teacherAttChart"></canvas>
                    @else
                        <div class="h-100 d-flex align-items-center justify-content-center text-secondary">{{ !$isClassTeacher ? 'No class assigned' : (count($sessionLabels) ? 'No data yet' : 'No active session') }}</div>
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
                <h6 class="mb-0">My Class{{ $isClassTeacher ? ' (' . $classLabel . ')' : '' }}</h6>
            </div>
            @if(!$isClassTeacher)
                <div class="card-body d-flex align-items-center justify-content-center text-secondary text-center">No class assigned</div>
            @else
                <div class="card-body">
                    <div class="chart-container-2">
                        @if(array_sum($classCounts) > 0)
                            <canvas id="teacherClassChart"></canvas>
                        @else
                            <div class="h-100 d-flex align-items-center justify-content-center text-secondary">{{ $classHoliday ? 'Holiday today' : 'Not marked yet' }}</div>
                        @endif
                    </div>
                </div>
                <ul class="list-group list-group-flush">
                    @foreach($classCounts as $attLabel => $attCount)
                    <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center{{ $loop->first ? ' border-top' : '' }}">{{ $attLabel }} <span class="badge {{ $attBadges[$attLabel] }} rounded-pill">{{ $attCount }}</span></li>
                    @endforeach
                </ul>
                <div class="card-body d-flex gap-2">
                    @can('mark-attendance')
                    @if(!$classMarked)
                    <a href="{{ route('attendance.create') }}" class="btn btn-sm btn-primary">Mark Attendance</a>
                    @endif
                    @endcan
                    @can('access-attendance')
                    <a href="{{ route('attendance.class_report') }}" class="btn btn-sm btn-outline-secondary">View Report</a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>
<!--end row-->

<!-- Row 3: timetable + leave -->
<div class="row">
    <div class="col-12 col-lg-8 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0">Today's Timetable</h6>
                    @can('access-my-timetable')
                    <a href="{{ route('teacher.timetable.index') }}" class="btn btn-sm btn-outline-primary ms-auto">My Timetable</a>
                    @endcan
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
                                    <th>Class - Section</th>
                                    <th>Subject</th>
                                    <th>Room</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todayPeriods as $p)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="text-nowrap">{{ $p->time_from_formatted }} - {{ $p->time_to_formatted }}</td>
                                    <td>{{ optional($p->schoolClass)->name }} - {{ optional($p->section)->name }}</td>
                                    <td>{{ optional($p->subject)->name }}</td>
                                    <td>{{ optional($p->room)->name ?: '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @can('access-leave-application')
    <div class="col-12 col-lg-4 d-flex">
        @include('partials.dashboard_leave_card')
    </div>
    @endcan
</div>
<!--end row-->

<!-- Row 4: homework + syllabus -->
<div class="row">
    <div class="col-12 col-lg-6 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0">Recent Homework</h6>
                    @can('access-home-works')
                    <a href="{{ route('home_work.index') }}" class="btn btn-sm btn-outline-primary ms-auto">Manage</a>
                    @endcan
                </div>
            </div>
            @if($recentHomework->isEmpty())
                <div class="card-body text-secondary text-center py-4">No homework given yet</div>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($recentHomework as $hw)
                    <li class="list-group-item bg-transparent">
                        <div class="fw-semibold text-break">{{ $hw->title }}</div>
                        <div class="font-13 text-secondary text-break">{{ optional($hw->class)->name }} - {{ optional($hw->section)->name }} &middot; {{ optional($hw->subject)->name }}</div>
                        <div class="font-13 text-secondary">Given {{ $hw->homework_date->format('d M') }} &middot; Due {{ $hw->due_date->format('d M') }}</div>
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
                    <h6 class="mb-0">Syllabus Progress</h6>
                    @can('access-my-syllabus-status')
                    <a href="{{ route('teacher.syllabus_status.index') }}" class="btn btn-sm btn-outline-primary ms-auto">Details</a>
                    @endcan
                </div>
            </div>
            @if($syllabus->isEmpty())
                <div class="card-body text-secondary text-center py-4">No subjects in timetable</div>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($syllabus as $s)
                    <li class="list-group-item bg-transparent">
                        <div class="d-flex justify-content-between gap-2">
                            <span class="text-break">{{ $s['label'] }}</span>
                            <span class="text-secondary text-nowrap font-13">{{ $s['total'] > 0 ? $s['done'] . '/' . $s['total'] . ' topics' : 'No topics yet' }}</span>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $s['percent'] }}%"></div>
                        </div>
                        <div class="font-13 text-secondary mt-1">{{ $s['percent'] }}%</div>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
<!--end row-->

@endsection

@push('scripts')
<script src="{{ asset('backend/assets/plugins/apexcharts-bundle/js/apexcharts.min.js') }}"></script>
@include('partials.dashboard_sparkline')
<script>
$(function () {
    // Homework Given sparkline: pichle 12 mahine
    dashSparkline('#w-homework', 'bar', '#ffc107', 'Homework', @json($homeworkSeries), @json($monthLabels), false);

    // Chart.js: light/dark theme ke hisab se text + grid colors (html.dark-theme)
    function chartTheme() {
        var dark = $('html').hasClass('dark-theme');
        return {
            text: dark ? '#aab0b6' : '#6c757d',
            grid: dark ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.08)'
        };
    }

    // My Class Attendance (session ke mahine): Present vs Absent
    var attChart = null;
    var attEl = document.getElementById('teacherAttChart');
    if (attEl) {
        var th = chartTheme();
        attChart = new Chart(attEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($sessionLabels),
                datasets: [
                    { label: 'Present', data: @json($sessionPresent), backgroundColor: '#17a00e', borderRadius: { topLeft: 20, topRight: 20, bottomLeft: 0, bottomRight: 0 }, borderSkipped: false, borderWidth: 0 },
                    { label: 'Absent', data: @json($sessionAbsent), backgroundColor: '#f41127', borderRadius: { topLeft: 20, topRight: 20, bottomLeft: 0, bottomRight: 0 }, borderSkipped: false, borderWidth: 0 }
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

    // My Class doughnut (aaj ki attendance): Present / Late / Half Day / Absent / Leave
    var el = document.getElementById('teacherClassChart');
    if (el) {
        new Chart(el.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: @json(array_keys($classCounts)),
                datasets: [{
                    data: @json(array_values($classCounts)),
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
