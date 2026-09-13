@extends('teacher.layout.app')

@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Syllabus Status', 'url' => '#'],
    ['label' => 'My Syllabus Status', 'url' => route('teacher.syllabus_status.index')],
]" />

@if(empty($homeroomData) && empty($teachingData))
    <div class="alert alert-warning">No subjects assigned yet.</div>
@else

@php
    // Rocker Admin ke light color set — card ko cycle se colorful banane ke liye
    $colorSet = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];

    // Pie chart ke liye hex colors (Chart.js ko CSS variable ki jagah direct hex chahiye hota hai)
    $pieColorSet = ['#0d6efd', '#198754', '#0dcaf0', '#ffc107', '#dc3545', '#6c757d'];

    // Tab colors (screenshot wale colored nav-tabs jaisa)
    $tab1Color = 'success'; // Class Overview
    $tab2Color = 'warning'; // Subject Overview
@endphp

{{-- ============================================ --}}
{{-- Colorful Nav Tabs (Rocker Admin style)        --}}
{{-- ============================================ --}}
<div class="card radius-10 border-0 shadow-sm mb-4">
    <div class="card-body pb-0">
        <ul class="nav nav-tabs custom-colored-tabs" id="syllabusTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active tab-{{ $tab1Color }}" id="homeroom-tab" data-bs-toggle="tab"
                    data-bs-target="#homeroom-pane" type="button" role="tab">
                    <i class="bx bx-buildings me-1"></i> Class Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link tab-{{ $tab2Color }}"
                    id="teaching-tab" data-bs-toggle="tab" data-bs-target="#teaching-pane" type="button" role="tab">
                    <i class="bx bx-book-open me-1"></i> Subject Overview
                </button>
            </li>
        </ul>
    </div>
</div>

<style>
    .custom-colored-tabs {
        border-bottom: 1px solid #e9ecef;
    }
    .custom-colored-tabs .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        color: #6c757d;
        font-weight: 500;
        padding: 0.75rem 1.25rem;
        background: transparent;
    }
    .custom-colored-tabs .nav-link:hover {
        border-bottom-color: #dee2e6;
    }
    .custom-colored-tabs .nav-link.tab-success.active {
        color: #198754;
        border-bottom-color: #198754;
        background: transparent;
    }
    .custom-colored-tabs .nav-link.tab-warning.active {
        color: #ffc107;
        border-bottom-color: #ffc107;
        background: transparent;
    }
    .custom-colored-tabs .nav-link.tab-primary.active {
        color: #0d6efd;
        border-bottom-color: #0d6efd;
        background: transparent;
    }
</style>

<div class="tab-content" id="syllabusTabsContent">

    {{-- ============================================ --}}
    {{-- TAB 1: HOMEROOM CLASS OVERVIEW (all subjects) --}}
    {{-- ============================================ --}}
    <div class="tab-pane fade show active" id="homeroom-pane" role="tabpanel">
    @if(empty($homeroomData))
        <div class="alert alert-light border text-muted mb-0">
            <i class="bx bx-info-circle me-1"></i> You are not assigned as homeroom teacher for any class.
        </div>
    @else

        {{-- Performance Charts: Bar + Pie side by side --}}
        <div class="row mb-4">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="card border shadow-none radius-10 h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">Class Syllabus Progress — Subject Wise</h6>
                        <canvas id="homeroomChart" height="90"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border shadow-none radius-10 h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">Overall Completion — Pie</h6>
                        <canvas id="homeroomPieChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Colorful Cards Grid --}}
        <div class="row">
            @foreach($homeroomData as $index => $data)
            @php
                $cardId = 'hr-' . $index;
                $color = $colorSet[$index % count($colorSet)];
            @endphp
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-sm radius-10 overflow-hidden">
                    <div class="bg-light-{{ $color }} border-bottom border-{{ $color }} border-opacity-25 p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 fw-bold text-{{ $color }}">{{ $data['subject'] }}</h6>
                            <small class="text-muted">{{ $data['class'] }} - {{ $data['section'] }}</small>
                        </div>
                        <span class="badge bg-{{ $color }} fs-6">{{ $data['percentage'] }}%</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-{{ $color }}" role="progressbar"
                                style="width: {{ $data['percentage'] }}%;"
                                aria-valuenow="{{ $data['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-{{ $color }} w-100"
                            data-bs-toggle="modal" data-bs-target="#modal-{{ $cardId }}">
                            <i class="bx bx-detail"></i> View Lessons
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal: Lesson/Topic Details --}}
            <div class="modal fade" id="modal-{{ $cardId }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-light-{{ $color }}">
                            <h5 class="modal-title text-{{ $color }}">{{ $data['subject'] }} <small class="text-muted">({{ $data['class'] }} - {{ $data['section'] }})</small></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            @if($data['lessons']->isEmpty())
                                <p class="text-muted mb-0">No lessons added yet for this subject.</p>
                            @else
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Lesson / Topic</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['lessons'] as $lesson)
                                    <tr>
                                        <td colspan="3"><strong>{{ $lesson->name }}</strong></td>
                                    </tr>
                                    @foreach($lesson->topics as $tIndex => $topic)
                                    <tr>
                                        <td></td>
                                        <td class="ps-4">{{ $loop->parent->iteration }}.{{ $tIndex + 1 }} {{ $topic->name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $topic->is_completed ? 'success' : 'warning' }}">
                                                {{ $topic->is_completed ? 'Completed' : 'Incomplete' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
    </div>

    {{-- ============================================ --}}
    {{-- TAB 2: SUBJECT OVERVIEW (period/timetable based) --}}
    {{-- ============================================ --}}
    <div class="tab-pane fade" id="teaching-pane" role="tabpanel">
    @if(empty($teachingData))
        <div class="alert alert-danger border text-muted mb-0">
            <i class="bx bx-info-circle me-1"></i> No period-based subjects found in your timetable.
        </div>
    @else

        {{-- Performance Charts: Bar + Pie side by side --}}
        <div class="row mb-4">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="card border shadow-none radius-10 h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">My Teaching Progress — Subject Wise</h6>
                        <canvas id="teachingChart" height="90"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border shadow-none radius-10 h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">Overall Completion — Pie</h6>
                        <canvas id="teachingPieChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Colorful Cards Grid --}}
        <div class="row">
            @foreach($teachingData as $index => $data)
            @php
                $cardId = 'tc-' . $index;
                $color = $colorSet[$index % count($colorSet)];
            @endphp
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-sm radius-10 overflow-hidden">
                    <div class="bg-light-{{ $color }} border-bottom border-{{ $color }} border-opacity-25 p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 fw-bold text-{{ $color }}">{{ $data['subject'] }}</h6>
                            <small class="text-muted">{{ $data['class'] }} - {{ $data['section'] }}</small>
                        </div>
                        <span class="badge bg-{{ $color }} fs-6">{{ $data['percentage'] }}%</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-{{ $color }}" role="progressbar"
                                style="width: {{ $data['percentage'] }}%;"
                                aria-valuenow="{{ $data['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-{{ $color }} w-100"
                            data-bs-toggle="modal" data-bs-target="#modal-{{ $cardId }}">
                            <i class="bx bx-detail"></i> View Lessons
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal: Lesson/Topic Details --}}
            <div class="modal fade" id="modal-{{ $cardId }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-light-{{ $color }}">
                            <h5 class="modal-title text-{{ $color }}">{{ $data['subject'] }} <small class="text-muted">({{ $data['class'] }} - {{ $data['section'] }})</small></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            @if($data['lessons']->isEmpty())
                                <p class="text-muted mb-0">No lessons added yet for this subject.</p>
                            @else
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Lesson / Topic</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['lessons'] as $lesson)
                                    <tr>
                                        <td colspan="3"><strong>{{ $lesson->name }}</strong></td>
                                    </tr>
                                    @foreach($lesson->topics as $tIndex => $topic)
                                    <tr>
                                        <td></td>
                                        <td class="ps-4">{{ $loop->parent->iteration }}.{{ $tIndex + 1 }} {{ $topic->name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $topic->is_completed ? 'success' : 'warning' }}">
                                                {{ $topic->is_completed ? 'Completed' : 'Incomplete' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
    </div>

</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const pieColors = {!! json_encode($pieColorSet ?? []) !!};

        // ============================================
        // HOMEROOM (Class Overview) charts — Bar + Pie
        // ============================================
        @if(!empty($homeroomData))
        const hrLabels = {!! json_encode(collect($homeroomData)->pluck('subject')) !!};
        const hrValues = {!! json_encode(collect($homeroomData)->pluck('percentage')) !!};

        const hrCtx = document.getElementById('homeroomChart');
        if (hrCtx) {
            new Chart(hrCtx, {
                type: 'bar',
                data: {
                    labels: hrLabels,
                    datasets: [{
                        label: 'Completion %',
                        data: hrValues,
                        backgroundColor: '#28a745',
                        borderRadius: 6,
                        maxBarThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } }
                }
            });
        }

        const hrPieCtx = document.getElementById('homeroomPieChart');
        if (hrPieCtx) {
            new Chart(hrPieCtx, {
                type: 'pie',
                data: {
                    labels: hrLabels,
                    datasets: [{
                        data: hrValues,
                        backgroundColor: hrLabels.map((_, i) => pieColors[i % pieColors.length])
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ctx.label + ': ' + ctx.parsed + '%'
                            }
                        }
                    }
                }
            });
        }
        @endif

        // ============================================
        // TEACHING (Subject Overview) charts — Bar + Pie
        // ============================================
        @if(!empty($teachingData))
        const tcLabels = {!! json_encode(collect($teachingData)->pluck('subject')) !!};
        const tcValues = {!! json_encode(collect($teachingData)->pluck('percentage')) !!};

        const tcCtx = document.getElementById('teachingChart');
        if (tcCtx) {
            new Chart(tcCtx, {
                type: 'bar',
                data: {
                    labels: tcLabels,
                    datasets: [{
                        label: 'Completion %',
                        data: tcValues,
                        backgroundColor: '#0d6efd',
                        borderRadius: 6,
                        maxBarThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } }
                }
            });
        }

        const tcPieCtx = document.getElementById('teachingPieChart');
        if (tcPieCtx) {
            new Chart(tcPieCtx, {
                type: 'pie',
                data: {
                    labels: tcLabels,
                    datasets: [{
                        data: tcValues,
                        backgroundColor: tcLabels.map((_, i) => pieColors[i % pieColors.length])
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ctx.label + ': ' + ctx.parsed + '%'
                            }
                        }
                    }
                }
            });
        }
        @endif

    });
</script>
@endpush