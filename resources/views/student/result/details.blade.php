@extends($current_layout)

@section('content')
<x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => route('student.dashboard')],
    ['label' => 'Academic', 'url' => '#'],
    ['label' => 'Results / Grades', 'url' => route('student.results.index')],
    ['label' => $result->exam->name, 'url' => '']
]" />

<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="fw-semi-bold text-primary mb-0">{{ $result->exam->name }}</h5>
    <a href="{{ route('student.results.index') }}" class="btn btn-outline-secondary btn-sm px-3">
        <i class="bx bx-arrow-back"></i> Back to Results
    </a>
</div>

{{-- Result Summary / Student Profile Card --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="fw-semi-bold text-primary mb-0">Result Summary</h5>
        <span class="badge {{ $percentageBadge }} px-3 py-2 fs-6">Score: {{ number_format($result->percentage, 2) }}%</span>
    </div>
    <div class="card-body">
        <div class="row g-4 align-items-center">
            {{-- Student Image & Basic ID --}}
            <div class="col-md-3 text-center border-end">
                @if($enrollment->student && $enrollment->student->photo)
                <img src="{{ $enrollment->student->photo_url }}"
                    alt="Student Photo"
                    class="rounded-3 border shadow-sm"
                    style="width: 100px; height: 100px; object-fit: cover;">
                @else
                <div class="rounded-3 border d-flex align-items-center justify-content-center bg-secondary-subtle text-secondary mx-auto shadow-sm"
                    style="width: 100px; height: 100px;">
                    <i class="bx bx-user fs-1"></i>
                </div>
                @endif
                <div class="fw-bold mt-2 text-dark">{{ $enrollment->student->full_name ?? '-' }}</div>
                <div class="text-muted small">Roll No: <strong>{{ $enrollment->roll_no ?? '-' }}</strong></div>
            </div>

            {{-- Student Details Grid --}}
            <div class="col-md-9">
                <div class="row g-3">
                    <div class="col-md-4 col-6">
                        <span class="text-muted small d-block">Student Name</span>
                        <span class="fw-semibold text-dark">{{ $enrollment->student->full_name ?? '-' }}</span>
                    </div>
                    <div class="col-md-4 col-6">
                        <span class="text-muted small d-block">Father's Name</span>
                        <span class="fw-semibold text-dark">{{ $fatherName }}</span>
                    </div>
                    <div class="col-md-4 col-6">
                        <span class="text-muted small d-block">Admission No</span>
                        <span class="fw-semibold text-dark">{{ $admissionNo }}</span>
                    </div>
                    <div class="col-md-4 col-6">
                        <span class="text-muted small d-block">Exam Name</span>
                        <span class="fw-semibold text-primary">{{ $result->exam->name ?? '-' }}</span>
                    </div>
                    <div class="col-md-4 col-6">
                        <span class="text-muted small d-block">Class & Section</span>
                        <span class="fw-semibold text-dark">
                            {{ $enrollment->schoolClass->name ?? '-' }} {{ $enrollment->section->name ?? '' }}
                        </span>
                    </div>
                    <div class="col-md-4 col-6">
                        <span class="text-muted small d-block">Status</span>
                        @if(strcasecmp($result->status, 'Pass') == 0)
                        <span class="badge bg-success-subtle text-success px-3">Pass</span>
                        @else
                        <span class="badge bg-danger-subtle text-danger px-3">Fail</span>
                        @endif
                    </div>
                </div>

                <hr class="my-3">

                {{-- Quick Stats Row --}}
                <div class="row text-center g-2">
                    <div class="col-3">
                        <div class="text-muted small">Total Marks</div>
                        <div class="fw-bold fs-5 text-dark">{{ $result->total_marks }}</div>
                    </div>
                    <div class="col-3">
                        <div class="text-muted small">Obtained</div>
                        <div class="fw-bold fs-5 text-success">{{ $result->obtained_marks }}</div>
                    </div>
                    <div class="col-3">
                        <div class="text-muted small">Grade</div>
                        <div><span class="badge {{ $gradeBadge }} px-3">{{ $gradeName }}</span></div>
                    </div>
                    <div class="col-3">
                        <div class="text-muted small">Position</div>
                        <div><span class="badge bg-info-subtle text-info px-3">{{ $result->position ?? '-' }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Subject-wise Marks Table --}}
<div class="card">
    <div class="card-header">
        <h5 class="fw-semi-bold text-primary mb-0">Subject-wise Marks</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="subject-marks-table" class="table table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Total Marks</th>
                        <th>Passing Marks</th>
                        <th>Obtained Marks</th>
                        <th>Status</th>
                        <th>Remark</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subjectMarks as $mark)
                    @php
                    $schedule = $examSchedules[$mark->subject_id] ?? null;
                    $maxMarks = $schedule->max_marks ?? null;
                    $passingMarks = $schedule->passing_marks ?? null;
                    $obtained = $mark->marks_obtained;

                    $subjectPass = $passingMarks !== null && $obtained !== null
                    ? $obtained >= $passingMarks
                    : null;

                    $obtainedBadge = $subjectPass === null
                    ? 'bg-secondary-subtle text-secondary'
                    : ($subjectPass ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger');
                    @endphp
                    <tr>
                        <td>{{ $mark->subject->name ?? '-' }}</td>
                        <td><span class="badge bg-danger-subtle text-danger px-3">{{ $maxMarks ?? '-' }}</span></td>
                        <td><span class="badge bg-warning-subtle text-warning px-3">{{ $passingMarks ?? '-' }}</span></td>
                        <td><span class="badge {{ $obtainedBadge }} px-3">{{ $obtained ?? '-' }}</span></td>
                        <td>
                            @if($subjectPass === null)
                            <span class="badge bg-secondary-subtle text-secondary px-3">-</span>
                            @elseif($subjectPass)
                            <span class="badge bg-success-subtle text-success px-3">Pass</span>
                            @else
                            <span class="badge bg-danger-subtle text-danger px-3">Fail</span>
                            @endif
                        </td>
                        <td>{{ $mark->remark ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Subject-wise Performance Chart --}}
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="fw-semi-bold text-primary mb-0">Subject-wise Performance</h5>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-primary active" id="chartTypePie">
                <i class="bx bx-pie-chart-alt-2"></i> Pie
            </button>
            <button type="button" class="btn btn-outline-primary" id="chartTypeDoughnut">
                <i class="bx bx-donate-heart"></i> Doughnut
            </button>
        </div>
    </div>
    <div class="card-body d-flex justify-content-center">
        <div style="max-width: 420px; width: 100%;">
            <canvas id="subjectPerformanceChart"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    @if($errors->any())
    toastr.error("{{ $errors->first() }}");
    @endif

    $('#subject-marks-table').DataTable({
        columnDefs: [{
            orderable: false,
            targets: [4, 5]
        }],
        language: {
            emptyTable: '<div class="alert alert-danger d-flex align-items-center mb-0"><i class="bx bx-error-circle me-2"></i> No data available in the table</div>'
        }
    });

    // ---- Subject-wise Performance Chart (Pie / Doughnut) ----
    const subjectLabels = [
        @foreach($subjectMarks as $mark)
            "{{ $mark->subject->name ?? 'N/A' }}",
        @endforeach
    ];

    const subjectPercentages = [
        @foreach($subjectMarks as $mark)
            @php
                $schedule = $examSchedules[$mark->subject_id] ?? null;
                $maxMarks = $schedule->max_marks ?? null;
                $obtained = $mark->marks_obtained;
                $pct = ($maxMarks && $obtained !== null) ? round(($obtained / $maxMarks) * 100, 2) : 0;
            @endphp
            {{ $pct }},
        @endforeach
    ];

    function generateDynamicColors(count) {
        const colors = [];
        const saturation = 65;
        const lightness = 55;
        for (let i = 0; i < count; i++) {
            const hue = Math.round((360 / count) * i);
            colors.push(`hsl(${hue}, ${saturation}%, ${lightness}%)`);
        }
        return colors;
    }

    const subjectColors = generateDynamicColors(subjectLabels.length);

    const ctx = document.getElementById('subjectPerformanceChart').getContext('2d');
    let currentType = 'pie';

    const chartConfig = {
        type: currentType,
        data: {
            labels: subjectLabels,
            datasets: [{
                label: 'Percentage (%)',
                data: subjectPercentages,
                backgroundColor: subjectColors,
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 14, padding: 12 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + '%';
                        }
                    }
                }
            }
        }
    };

    let subjectChart = new Chart(ctx, chartConfig);

    function switchChartType(type) {
        currentType = type;
        subjectChart.destroy();
        chartConfig.type = type;
        subjectChart = new Chart(ctx, chartConfig);
    }

    document.getElementById('chartTypePie').addEventListener('click', function() {
        this.classList.add('active');
        document.getElementById('chartTypeDoughnut').classList.remove('active');
        switchChartType('pie');
    });

    document.getElementById('chartTypeDoughnut').addEventListener('click', function() {
        this.classList.add('active');
        document.getElementById('chartTypePie').classList.remove('active');
        switchChartType('doughnut');
    });
</script>
@endpush