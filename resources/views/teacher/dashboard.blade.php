@extends('teacher.layout.app')
@section('content')

@php
    $isClassTeacher = $myClasses->isNotEmpty();
    $classLabel = $myClasses->map(fn($a) => optional($a->schoolClass)->name . '-' . optional($a->section)->name)->implode(', ');
    $attBadges = ['Present' => 'bg-success', 'Late' => 'bg-warning text-dark', 'Half Day' => 'bg-info text-dark', 'Absent' => 'bg-danger', 'Leave' => 'bg-primary'];
    $leaveBadges = ['pending' => 'bg-warning text-dark', 'approved' => 'bg-success', 'rejected' => 'bg-danger'];
@endphp

@if($profileMissing)
<div class="alert alert-warning py-2 mb-3">Teacher profile not linked. Please contact the admin.</div>
@endif

<!-- Row 1: cards -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 gy-4 mb-4">
    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-info h-100 mb-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Today's Classes</p>
                        <h4 class="my-1 text-info">{{ $offDayLabel ? 0 : $todayPeriods->count() }}</h4>
                        <p class="mb-0 font-13 text-secondary">{{ $offDayLabel ? 'Off day: ' . $offDayLabel : now()->format('l, d M') }}</p>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto"><i class='bx bx-time-five'></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-success h-100 mb-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">My Class Today</p>
                        @if(!$isClassTeacher)
                            <h4 class="my-1 text-success">-</h4>
                            <p class="mb-0 font-13 text-secondary">No class assigned</p>
                        @elseif($classHoliday)
                            <h4 class="my-1 text-success">Holiday</h4>
                            <p class="mb-0 font-13 text-secondary">{{ $classLabel }}</p>
                        @elseif(!$classMarked)
                            <h4 class="my-1 text-success">- / {{ $rosterTotal }}</h4>
                            <p class="mb-0 font-13 text-secondary">Not marked yet &middot; {{ $classLabel }}</p>
                        @else
                            <h4 class="my-1 text-success">{{ $classCounts['Present'] }} / {{ $rosterTotal }}</h4>
                            <p class="mb-0 font-13 text-secondary">Present today &middot; {{ $classLabel }}</p>
                        @endif
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto"><i class='bx bxs-graduation'></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-warning h-100 mb-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Homework Given</p>
                        <h4 class="my-1 text-warning">{{ $homeworkThisMonth }}</h4>
                        <p class="mb-0 font-13 text-secondary">This month</p>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto"><i class='bx bx-book-content'></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-danger h-100 mb-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">My Attendance</p>
                        @if($myAtt['marked'] > 0)
                            <h4 class="my-1 text-danger">{{ $myAtt['present'] + $myAtt['half_day'] }} / {{ $myAtt['marked'] }}</h4>
                            <p class="mb-0 font-13 text-secondary">{{ $myAtt['percent'] }}% this month</p>
                        @else
                            <h4 class="my-1 text-danger">-</h4>
                            <p class="mb-0 font-13 text-secondary">Not marked yet</p>
                        @endif
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto"><i class='bx bx-calendar-check'></i></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end row-->

<!-- Row 2: timetable + my class -->
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

<!-- Row 3: homework, syllabus, leave -->
<div class="row">
    <div class="col-12 col-lg-4 d-flex">
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
    <div class="col-12 col-lg-4 d-flex">
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
    <div class="col-12 col-lg-4 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0">My Leave Requests</h6>
                    @can('access-leave-application')
                    <a href="{{ route('staffleaveapp.index') }}" class="btn btn-sm btn-outline-primary ms-auto">All</a>
                    @endcan
                </div>
            </div>
            @if($recentLeaves->isEmpty())
                <div class="card-body text-secondary text-center py-4">No leave requests</div>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($recentLeaves as $lv)
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-start gap-2">
                        <div style="min-width: 0;">
                            <div class="fw-semibold text-break">{{ ucfirst($lv->leave_type) }}</div>
                            <div class="font-13 text-secondary">{{ \Carbon\Carbon::parse($lv->from_date)->format('d M') }} - {{ \Carbon\Carbon::parse($lv->to_date)->format('d M Y') }} &middot; {{ rtrim(rtrim($lv->total_days, '0'), '.') }} day(s)</div>
                        </div>
                        <span class="badge {{ $leaveBadges[$lv->status] ?? 'bg-secondary' }} rounded-pill">{{ ucfirst($lv->status) }}</span>
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
<script>
// My Class doughnut (aaj ki attendance): Present / Late / Half Day / Absent / Leave
$(function () {
    var el = document.getElementById('teacherClassChart');
    if (!el) { return; }
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
});
</script>
@endpush
