@extends($current_layout)

@section('content')
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('admin.dashboard') }}"><i class='bx bx-home-alt'></i></a>
    <i class='bx bx-chevron-right mx-1'></i>
    <a href="{{ route('staff_attendance.index') }}">Attendance</a>
    <i class='bx bx-chevron-right mx-1'></i>
    <span class="fw-semibold">{{ $staff->name }} — Attendance Detail</span>
</div>

{{-- Staff info header --}}
<div class="card mb-3">
    <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
            <i class='bx bx-user fs-3 text-primary'></i>
        </div>
        <div>
            <h5 class="mb-0">{{ $staff->name }}</h5>
            <small class="text-muted">
                {{ $staff->staff_code ?? '-' }} &middot;
                {{ ucfirst($staff->roles->pluck('name')->first() ?? '-') }}
            </small>
        </div>
    </div>
</div>

{{-- Summary cards --}}
<div class="row mb-3">
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-success">
            <div class="card-body py-2">
                <small class="text-muted">Present</small>
                <h4 class="mb-0 text-success">{{ $summary['present'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-danger">
            <div class="card-body py-2">
                <small class="text-muted">Absent</small>
                <h4 class="mb-0 text-danger">{{ $summary['absent'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-info">
            <div class="card-body py-2">
                <small class="text-muted">Half Day</small>
                <h4 class="mb-0 text-info">{{ $summary['half_day'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-warning">
            <div class="card-body py-2">
                <small class="text-muted">Leave</small>
                <h4 class="mb-0 text-warning">{{ $summary['leave'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-primary">
            <div class="card-body py-2">
                <small class="text-muted">Attendance %</small>
                <h4 class="mb-0 text-primary">{{ $summary['percentage'] }}%</h4>
            </div>
        </div>
    </div>
</div>

{{-- Charts Section --}}
<div class="row mb-3">
    {{-- Left side: Status Distribution with Toggle Button (Pie / Doughnut) --}}
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Status Distribution</h6>
                <div class="btn-group btn-group-sm" role="group" aria-label="Chart Type Toggle">
                    <button type="button" class="btn btn-outline-primary active" id="btnPie">Pie</button>
                    <button type="button" class="btn btn-outline-primary" id="btnDoughnut">Doughnut</button>
                </div>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 320px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Right side: Month-wise Trend with Toggle Button (Column Chart / Area Chart) --}}
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0" id="trendChartTitle">Month-wise Column Chart</h6>
                <div class="btn-group btn-group-sm" role="group" aria-label="Trend Chart Type Toggle">
                    <button type="button" class="btn btn-outline-primary active" id="btnColumn">Column</button>
                    <button type="button" class="btn btn-outline-primary" id="btnArea">Area</button>
                </div>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 320px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter + History table --}}
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Attendance History</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label" for="from_date">From Date</label>
                <input type="date" name="from_date" id="from_date" class="form-control form-control-sm" value="{{ $fromDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="to_date">To Date</label>
                <input type="date" name="to_date" id="to_date" class="form-control form-control-sm" value="{{ $toDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="status">Status</label>
                <select name="status" class="form-select form-select-sm" id="status">
                    <option value="">All Status</option>
                    <option value="present" {{ $statusFilter == 'present' ? 'selected' : '' }}>Present</option>
                    <option value="absent" {{ $statusFilter == 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="half_day" {{ $statusFilter == 'half_day' ? 'selected' : '' }}>Half Day</option>
                    <option value="leave" {{ $statusFilter == 'leave' ? 'selected' : '' }}>Leave</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-sm btn-primary me-2">Filter</button>
                <a href="{{ route('staff_attendance.show', $staff->id) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped table-hover" id="historyTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Marked At</th>
                        <th>Time Out</th>
                        <th>Remarks</th>
                        <th>Marked By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $i => $record)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->date)->format('d-m-Y') }}</td>
                        <td>
                            <span class="badge bg-{{ ['present'=>'success','absent'=>'danger','half_day'=>'info','leave'=>'warning'][$record->status] }}">
                                {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                            </span>
                        </td>
                        <td>{{ $record->marked_at ? $record->marked_at->format('d-m-Y h:i A') : '-' }}</td>
                        <td>{{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('h:i A') : '-' }}</td>
                        <td>{{ $record->remarks ?? '-' }}</td>
                        <td>{{ $record->markedBy->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.2.0/chartjs-plugin-datalabels.min.js"></script>
<script>
    $(document).ready(function() {
        $('#historyTable').DataTable();
    });

    // Left Chart: Status Distribution
    const statusTotal = {{ $summary['total'] }};
    const statusData = {
        labels: ['Present', 'Absent', 'Half Day', 'Leave'],
        datasets: [{
            data: [
                {{ $summary['present'] }},
                {{ $summary['absent'] }},
                {{ $summary['half_day'] }},
                {{ $summary['leave'] }}
            ],
            backgroundColor: ['#198754', '#dc3545', '#0dcaf0', '#ffc107']
        }]
    };

    const statusOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' },
            datalabels: {
                color: '#fff',
                font: { weight: 'bold' },
                formatter: function(value) {
                    if (!statusTotal || value === 0) return '';
                    return Math.round((value / statusTotal) * 100) + '%';
                }
            }
        }
    };

    let statusChartInstance = new Chart(document.getElementById('statusChart'), {
        type: 'pie',
        data: statusData,
        options: statusOptions,
        plugins: [ChartDataLabels]
    });

    $('#btnPie').click(function() {
        $(this).addClass('active btn-primary').removeClass('btn-outline-primary');
        $('#btnDoughnut').removeClass('active btn-primary').addClass('btn-outline-primary');
        statusChartInstance.destroy();
        statusChartInstance = new Chart(document.getElementById('statusChart'), {
            type: 'pie',
            data: statusData,
            options: statusOptions,
            plugins: [ChartDataLabels]
        });
    });

    $('#btnDoughnut').click(function() {
        $(this).addClass('active btn-primary').removeClass('btn-outline-primary');
        $('#btnPie').removeClass('active btn-primary').addClass('btn-outline-primary');
        statusChartInstance.destroy();
        statusChartInstance = new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: statusData,
            options: statusOptions,
            plugins: [ChartDataLabels]
        });
    });

    // Right Chart: Month-wise Trend (Column vs Area Toggle)
    const monthly = @json($monthly);
    const trendData = {
        labels: monthly.map(m => m.month),
        datasets: [
            { label: 'Present', data: monthly.map(m => m.present), backgroundColor: '#198754', borderColor: '#198754' },
            { label: 'Absent', data: monthly.map(m => m.absent), backgroundColor: '#dc3545', borderColor: '#dc3545' },
            { label: 'Half Day', data: monthly.map(m => m.half_day), backgroundColor: '#0dcaf0', borderColor: '#0dcaf0' },
            { label: 'Leave', data: monthly.map(m => m.leave), backgroundColor: '#ffc107', borderColor: '#ffc107' }
        ]
    };

    const columnOptions = {
        responsive: true,
        maintainAspectRatio: false,
        scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } },
        plugins: {
            legend: { position: 'bottom' },
            datalabels: { display: false }
        }
    };

    const areaOptions = {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } },
        plugins: {
            legend: { position: 'bottom' },
            datalabels: { display: false }
        }
    };

    let trendChartInstance = new Chart(document.getElementById('trendChart'), {
        type: 'bar',
        data: trendData,
        options: columnOptions,
        plugins: [ChartDataLabels]
    });

    $('#btnColumn').click(function() {
        $(this).addClass('active btn-primary').removeClass('btn-outline-primary');
        $('#btnArea').removeClass('active btn-primary').addClass('btn-outline-primary');
        $('#trendChartTitle').text('Month-wise Column Chart');
        trendChartInstance.destroy();

        // Revert datasets to bar configuration
        const barDatasets = trendData.datasets.map(d => ({
            label: d.label,
            data: d.data,
            backgroundColor: d.backgroundColor,
            borderColor: d.borderColor,
            fill: false
        }));

        trendChartInstance = new Chart(document.getElementById('trendChart'), {
            type: 'bar',
            data: { labels: trendData.labels, datasets: barDatasets },
            options: columnOptions,
            plugins: [ChartDataLabels]
        });
    });

    $('#btnArea').click(function() {
        $(this).addClass('active btn-primary').removeClass('btn-outline-primary');
        $('#btnColumn').removeClass('active btn-primary').addClass('btn-outline-primary');
        $('#trendChartTitle').text('Month-wise Area Chart');
        trendChartInstance.destroy();

        // Convert datasets to filled line/area configuration
        const areaDatasets = trendData.datasets.map(d => {
            let rgbaColor = d.backgroundColor;
            if (d.backgroundColor === '#198754') rgbaColor = 'rgba(25, 135, 84, 0.2)';
            else if (d.backgroundColor === '#dc3545') rgbaColor = 'rgba(220, 53, 69, 0.2)';
            else if (d.backgroundColor === '#0dcaf0') rgbaColor = 'rgba(13, 202, 240, 0.2)';
            else if (d.backgroundColor === '#ffc107') rgbaColor = 'rgba(255, 193, 7, 0.2)';

            return {
                label: d.label,
                data: d.data,
                borderColor: d.borderColor,
                backgroundColor: rgbaColor,
                fill: true,
                tension: 0.4
            };
        });

        trendChartInstance = new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: { labels: trendData.labels, datasets: areaDatasets },
            options: areaOptions,
            plugins: [ChartDataLabels]
        });
    });
</script>
@endpush