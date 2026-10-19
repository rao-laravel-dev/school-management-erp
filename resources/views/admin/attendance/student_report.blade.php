@extends($current_layout)

@section('content')
@php
    $studentName = $student ? trim($student->first_name . ' ' . $student->last_name) : '-';
    $classUrl = route('attendance.class_report', [$enrollment->class_id, $enrollment->section_id]);
@endphp
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('dashboard') }}"><i class='bx bx-home-alt'></i></a>
    <i class='bx bx-chevron-right mx-1'></i>
    <span>Attendance</span>
    <i class='bx bx-chevron-right mx-1'></i>
    <a href="{{ $classUrl }}">Class Report</a>
    <i class='bx bx-chevron-right mx-1'></i>
    <span class="fw-semibold">{{ $studentName }}</span>
</div>

{{-- Student info header --}}
<div class="card mb-3">
    <div class="card-body d-flex flex-wrap align-items-center gap-3">
        @if($student)
            <img src="{{ $student->photo_url }}" alt="{{ $studentName }}" class="rounded-circle border" style="width:56px;height:56px;object-fit:cover;">
        @endif
        <div class="flex-grow-1">
            <h5 class="mb-0">{{ $studentName }}</h5>
            <small class="text-muted">
                Adm# {{ optional($student)->admission_no ?: '-' }} &middot;
                {{ optional($enrollment->schoolClass)->name }} - {{ optional($enrollment->section)->name }} &middot;
                Roll No {{ $enrollment->roll_no ?: '-' }}
            </small>
        </div>
        <a href="{{ $classUrl }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-arrow-back"></i> Back to class report</a>
    </div>
</div>

{{-- Summary cards --}}
<div class="row g-3 mb-3">
    @foreach([
        ['Present', $summary['P'], 'success'],
        ['Absent', $summary['A'], 'danger'],
        ['Late', $summary['L'], 'warning'],
        ['Half Day', $summary['H'], 'info'],
        ['Leave', $summary['F'], 'secondary'],
    ] as $card)
        <div class="col-6 col-md">
            <div class="card radius-10 border-start border-3 border-0 border-{{ $card[2] }} mb-0">
                <div class="card-body py-2">
                    <small class="text-muted">{{ $card[0] }}</small>
                    <h4 class="mb-0 text-{{ $card[2] }}">{{ $card[1] }}</h4>
                </div>
            </div>
        </div>
    @endforeach
    @if($summary['HOL'] > 0)
        <div class="col-6 col-md">
            <div class="card radius-10 border-start border-3 border-0 border-dark mb-0">
                <div class="card-body py-2">
                    <small class="text-muted">Holiday</small>
                    <h4 class="mb-0">{{ $summary['HOL'] }}</h4>
                </div>
            </div>
        </div>
    @endif
    <div class="col-6 col-md">
        <div class="card radius-10 border-start border-3 border-0 border-primary mb-0">
            <div class="card-body py-2">
                <small class="text-muted">Attendance %</small>
                <h4 class="mb-0 text-primary">{{ $percent === null ? '-' : $percent . '%' }}</h4>
            </div>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="row mb-3">
    <div class="col-md-5 mb-3 mb-md-0">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Status Distribution</h6></div>
            <div class="card-body">
                <div style="position: relative; height: 320px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Month-wise Attendance</h6></div>
            <div class="card-body">
                <div style="position: relative; height: 320px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter + History --}}
<div class="card">
    <div class="card-header"><h5 class="mb-0">Attendance History</h5></div>
    <div class="card-body">
        <form method="GET" action="{{ route('attendance.student_report', $enrollment->id) }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label" for="from_date">From Date</label>
                <input type="date" name="from_date" id="from_date" class="form-control form-control-sm" value="{{ $filters['from'] }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="to_date">To Date</label>
                <input type="date" name="to_date" id="to_date" class="form-control form-control-sm" value="{{ $filters['to'] }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="status">Status</label>
                <select name="status" id="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    @foreach(['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'half_day' => 'Half Day', 'leave' => 'Leave'] as $key => $label)
                        <option value="{{ $key }}" {{ $filters['status'] === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-primary me-2">Filter</button>
                <a href="{{ route('attendance.student_report', $enrollment->id) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Remarks</th>
                        <th>Marked By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $r)
                        @php
                            $isHoliday = (int) $r->status === 4 && strtolower(trim((string) $r->remarks)) === 'holiday';
                            $badges = [1 => ['success', 'Present'], 0 => ['danger', 'Absent'], 2 => ['warning', 'Late'], 3 => ['info', 'Half Day'], 4 => ['secondary', 'Leave']];
                            $badge = $isHoliday ? ['dark', 'Holiday'] : (isset($badges[(int) $r->status]) ? $badges[(int) $r->status] : ['secondary', '-']);
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($r->attendance_date)->format('d-m-Y') }}</td>
                            <td><span class="badge bg-{{ $badge[0] }}">{{ $badge[1] }}</span></td>
                            <td>{{ $r->scanned_at ? \Carbon\Carbon::parse($r->scanned_at)->format('h:i A') : '-' }}</td>
                            <td>{{ $r->time_out ? \Carbon\Carbon::parse($r->time_out)->format('h:i A') : '-' }}</td>
                            <td>{{ $r->remarks ?: '-' }}</td>
                            <td>{{ optional($r->markedBy)->name_with_role ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No attendance records found for this range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $history->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.2.0/chartjs-plugin-datalabels.min.js"></script>
<script>
    const summary = @json($summary);
    const monthly = @json($monthly);
    const statusTotal = summary.P + summary.A + summary.L + summary.H + summary.F;

    // Left chart: status distribution (holiday shamil nahi)
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Absent', 'Late', 'Half Day', 'Leave'],
            datasets: [{
                data: [summary.P, summary.A, summary.L, summary.H, summary.F],
                backgroundColor: ['#198754', '#dc3545', '#ffc107', '#0dcaf0', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold' },
                    formatter: function (value) {
                        if (!statusTotal || value === 0) return '';
                        return Math.round((value / statusTotal) * 100) + '%';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    // Right chart: month-wise counts (datalabels plugin yahan register nahi)
    new Chart(document.getElementById('trendChart'), {
        type: 'bar',
        data: {
            labels: monthly.map(function (m) { return m.label; }),
            datasets: [
                { label: 'Present', backgroundColor: '#198754', data: monthly.map(function (m) { return m.P; }) },
                { label: 'Absent', backgroundColor: '#dc3545', data: monthly.map(function (m) { return m.A; }) },
                { label: 'Late', backgroundColor: '#ffc107', data: monthly.map(function (m) { return m.L; }) },
                { label: 'Half Day', backgroundColor: '#0dcaf0', data: monthly.map(function (m) { return m.H; }) },
                { label: 'Leave', backgroundColor: '#6c757d', data: monthly.map(function (m) { return m.F; }) }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
</script>
@endpush
