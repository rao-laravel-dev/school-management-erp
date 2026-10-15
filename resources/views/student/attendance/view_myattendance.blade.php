@extends('student.layout.app')

@push('styles')
<style>
    /* Layout pehle se .page-content lagata hai; is view ka inner .page-content + .container-fluid dobara padding deta tha (~60px har side). Phone par hata diya */
    @media (max-width: 575.98px) {
        .page-content > .page-content { padding: 0; }
        .page-content > .page-content > .container-fluid { padding-left: 0; padding-right: 0; }
    }

    /* Ring: square wrapper (width ke saath scale, column se bahar nahi), % text bilkul beech mein */
    .att-ring { position: relative; width: clamp(90px, 32vw, 170px); max-width: 100%; aspect-ratio: 1; margin: 0 auto; min-height: 0; overflow: hidden; }
    /* canvas wrapper ke andar absolute: Chart.js ka size wrapper (card) ki height nahi badhata */
    .att-ring canvas { position: absolute; inset: 0; width: 100% !important; height: 100% !important; display: block; }
    .att-ring-center { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: clamp(1rem, 4.5vw, 1.5rem); font-weight: 700; line-height: 1; pointer-events: none; }

    /* Stat cards: 500px se neeche 1 per row (Bootstrap breakpoint nahi), 500px-lg 2 per row */
    @media (min-width: 500px) and (max-width: 991.98px) {
        .att-stat > .col { flex: 0 0 auto; width: 50%; }
    }
    /* Compact sizing sirf 500-575px par */
    @media (min-width: 500px) and (max-width: 575.98px) {
        .att-stat .card-body { padding: .6rem !important; }
        .att-stat .widgets-icons-2 { width: 32px !important; height: 32px !important; line-height: 32px !important; font-size: .9rem; }
        .att-stat h5 { font-size: 1.05rem; }
        .att-stat p { font-size: .72rem; }
    }

    /* Monthly header: prev | title | next, ek line mein */
    .att-nav-btn { width: 32px; height: 32px; padding: 0; flex: 0 0 auto; display: flex; align-items: center; justify-content: center; }
    .att-month-title { flex: 1 1 auto; min-width: 0; text-align: center; font-size: clamp(.9rem, 4.6vw, 1.25rem); white-space: nowrap; }
    @media (max-width: 399.98px) {
        .att-nav-btn { width: 28px; height: 28px; }
    }

    /* Monthly calendar grid (Mon-Sun) */
    .att-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: clamp(2px, 1.5vw, 6px); text-align: center; }
    .att-grid > div { min-width: 0; }
    .att-head { font-size: clamp(.6rem, 2.8vw, .75rem); font-weight: 600; color: #6c757d; padding-bottom: 4px; }
    .att-head .short { display: none; }
    @media (max-width: 399.98px) {
        .att-head .full { display: none; }
        .att-head .short { display: inline; }
    }
    .att-day { width: min(40px, 100%); aspect-ratio: 1; display: flex; align-items: center; justify-content: center; line-height: 1; border-radius: 50%; margin: 0 auto; font-size: clamp(.68rem, 3.2vw, .85rem); font-weight: 600; border: 2px solid transparent; }
    .att-present { background: #198754; color: #fff; }
    .att-absent { background: #dc3545; color: #fff; }
    .att-late { background: #ffc107; color: #000; }
    .att-halfday { background: #0dcaf0; color: #000; }
    .att-leave { background: #6c757d; color: #fff; }
    .att-holiday { background: #dee2e6; color: #495057; }
    .att-unmarked { border-color: #adb5bd; color: #6c757d; }
    .att-future, .att-none { color: #adb5bd; }
    .att-today { border-color: #0d6efd !important; box-shadow: 0 0 0 2px rgba(13, 110, 253, .25); }
    .att-dot { display: inline-block; width: 12px; height: 12px; border-radius: 50%; vertical-align: middle; margin-right: 4px; }

    /* Dark theme (html.dark-theme) */
    /* Today badge: dark mode mein light text (unmarked/holiday = transparent + visible border) */
    html.dark-theme .att-today-badge.bg-light { background-color: transparent !important; color: #ced4da !important; border: 1px solid #adb5bd !important; }
    html.dark-theme .att-today-badge.bg-warning { background-color: #9a7400 !important; color: #fff !important; }
    html.dark-theme .att-today-badge.bg-info { background-color: #0a7f94 !important; color: #fff !important; }
    /* Holiday letter "O" (legend + table cells) */
    .att-o { color: #212529; }
    html.dark-theme .att-o { color: #e9ecef; }
    html.dark-theme .att-head { color: #adb5bd; }
    html.dark-theme .att-holiday { background: #3a3f45; color: #ced4da; }
    html.dark-theme .att-unmarked { border-color: #6c757d; color: #adb5bd; }
    html.dark-theme .att-future, html.dark-theme .att-none { color: #6c757d; }
    html.dark-theme .att-year-table .weekoff-cell { background-color: #3a3520 !important; }
</style>
@endpush

@section('content')

@php
    // Status key -> badge rang (today card)
    $badgeClass = ['present' => 'bg-success', 'absent' => 'bg-danger', 'late' => 'bg-warning text-dark', 'halfday' => 'bg-info text-dark', 'leave' => 'bg-secondary', 'holiday' => 'bg-light text-dark border', 'unmarked' => 'bg-light text-dark border'];
    $fmtPercent = function ($p) { return $p === null ? '-' : ((float) $p) . '%'; };
@endphp

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Attendance</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">My Attendance</li>
            </ol>
        </nav>
    </div>
</div>

<div class="page-content">
    <div class="container-fluid">
        <!-- Today card -->
        <div class="card radius-10 shadow-sm mb-3">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <p class="mb-0 text-secondary small">Today</p>
                    <h5 class="mb-0">{{ $today['date']->format('l, d F Y') }}</h5>
                </div>
                <span class="badge att-today-badge fs-6 {{ $badgeClass[$today['key']] }}">{{ $today['label'] }}</span>
            </div>
        </div>

        <!-- Rings: selected month % and yearly % -->
        <div class="card radius-10 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-0">
                    <div class="col-6 text-center px-2">
                        <div class="att-ring">
                            <canvas id="monthRing"></canvas>
                            <div class="att-ring-center">{{ $fmtPercent($monthPercent) }}</div>
                        </div>
                        <p class="mt-2 mb-0 text-secondary small">{{ $selectedMonth->format('F Y') }}</p>
                    </div>
                    <div class="col-6 text-center px-2">
                        <div class="att-ring">
                            <canvas id="yearRing"></canvas>
                            <div class="att-ring-center">{{ $fmtPercent($yearPercent) }}</div>
                        </div>
                        <p class="mt-2 mb-0 text-secondary small">Yearly</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5 Cards Layout (selected month, numbers legend se) -->
        <div class="row g-3 row-cols-1 row-cols-lg-3 row-cols-xl-5 mb-3 att-stat">
            <!-- 1. Total Present (sirf status 1) -->
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small">Total Present</p>
                                <h5 class="my-0 text-success fw-bold">{{ $legend['present'] }}</h5>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto" style="width: 45px; height: 45px; line-height: 45px; text-align: center;">
                                <i class='bx bxs-check-circle'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 2. Total Absent -->
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-danger shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small">Total Absent</p>
                                <h5 class="my-0 text-danger fw-bold">{{ $legend['absent'] }}</h5>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto" style="width: 45px; height: 45px; line-height: 45px; text-align: center;">
                                <i class='bx bxs-x-circle'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 3. Total Late -->
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-warning shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small">Total Late</p>
                                <h5 class="my-0 text-warning fw-bold">{{ $legend['late'] }}</h5>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto" style="width: 45px; height: 45px; line-height: 45px; text-align: center;">
                                <i class='bx bxs-time'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 4. Total Half-Day -->
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-info shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small">Total Half-Day</p>
                                <h5 class="my-0 text-info fw-bold">{{ $legend['halfday'] }}</h5>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto" style="width: 45px; height: 45px; line-height: 45px; text-align: center;">
                                <i class='bx bxs-hourglass-bottom'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 5. Total Leaves (holiday shamil nahi) -->
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-secondary shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small">Total Leaves</p>
                                <h5 class="my-0 text-secondary fw-bold">{{ $legend['leave'] }}</h5>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-secondary text-secondary ms-auto" style="width: 45px; height: 45px; line-height: 45px; text-align: center;">
                                <i class='bx bxs-calendar-event'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card radius-10 border-top border-0 border-4 border-primary">
            <div class="card-body">
                <ul class="nav nav-tabs nav-fill mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-monthly" data-bs-toggle="tab" data-bs-target="#pane-monthly" type="button" role="tab">Monthly</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-yearly" data-bs-toggle="tab" data-bs-target="#pane-yearly" type="button" role="tab">Yearly</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Monthly: calendar -->
                    <div class="tab-pane fade show active" id="pane-monthly" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                            <a class="btn btn-sm btn-outline-secondary att-nav-btn {{ $prevMonth ? '' : 'disabled' }}" aria-label="Previous month"
                                href="{{ $prevMonth ? route('student.attendance.view', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) : '#' }}">
                                <i class="bx bx-chevron-left"></i>
                            </a>
                            <h5 class="mb-0 att-month-title"><span class="d-none d-md-inline">Attendance Overview: </span>{{ $selectedMonth->format('F Y') }}</h5>
                            <a class="btn btn-sm btn-outline-secondary att-nav-btn {{ $nextMonth ? '' : 'disabled' }}" aria-label="Next month"
                                href="{{ $nextMonth ? route('student.attendance.view', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) : '#' }}">
                                <i class="bx bx-chevron-right"></i>
                            </a>
                        </div>

                        <div class="att-grid mb-2">
                            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayName)
                                <div class="att-head"><span class="full">{{ $dayName }}</span><span class="short">{{ substr($dayName, 0, 1) }}</span></div>
                            @endforeach
                            @foreach($calendar as $week)
                                @foreach($week as $cell)
                                    @if($cell)
                                        <div>
                                            <div class="att-day att-{{ $cell['key'] }} {{ $cell['isToday'] ? 'att-today' : '' }}" title="{{ \Carbon\Carbon::parse($cell['date'])->format('d M Y') }}">{{ $cell['day'] }}</div>
                                        </div>
                                    @else
                                        <div></div>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>

                        <hr>
                        <div class="d-flex flex-wrap gap-2 gap-sm-3 small">
                            <span><span class="att-dot att-present"></span>Present: <strong>{{ $legend['present'] }}</strong></span>
                            <span><span class="att-dot att-absent"></span>Absent: <strong>{{ $legend['absent'] }}</strong></span>
                            <span><span class="att-dot att-late"></span>Late: <strong>{{ $legend['late'] }}</strong></span>
                            <span><span class="att-dot att-halfday"></span>Half-day: <strong>{{ $legend['halfday'] }}</strong></span>
                            <span><span class="att-dot att-leave"></span>Leave: <strong>{{ $legend['leave'] }}</strong></span>
                            <span><span class="att-dot att-holiday"></span>Holiday: <strong>{{ $legend['holiday'] }}</strong></span>
                        </div>
                    </div>

                    <!-- Yearly: bar chart + month x day table -->
                    <div class="tab-pane fade" id="pane-yearly" role="tabpanel">
                        <div class="mb-4" style="position: relative; width: 100%; height: clamp(200px, 45vw, 280px);">
                            <canvas id="yearBar"></canvas>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">Attendance Overview</h4>
                            <div class="small text-muted">
                                <strong>Legend:</strong>
                                <span class="text-success fw-bold">P</span>: Present,
                                <span class="text-warning fw-bold">L</span>: Late,
                                <span class="text-danger fw-bold">A</span>: Absent,
                                <span class="text-info fw-bold">H</span>: Half-Day,
                                <span class="text-secondary fw-bold">F</span>: Leave,
                                <span class="text-danger fw-bold">S</span>: Weekly Off,
                                <span class="att-o fw-bold">O</span>: Holiday
                            </div>
                        </div>

                        <hr>

                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="table table-bordered table-sm text-center align-middle att-year-table" style="font-size: 0.8rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 25px;">Month</th>
                                        @for($day = 1; $day <= 31; $day++) <th style="width: 25px;">{{ $day }}</th> @endfor
                                            <th class="bg-success text-white" style="width: 25px;">P</th>
                                            <th class="bg-danger text-white" style="width: 25px;">A</th>
                                            <th class="bg-warning text-white" style="width: 25px;">L</th>
                                            <th class="bg-info text-white" style="width: 25px;">H</th>
                                            <th class="bg-secondary text-white" style="width: 25px;">F</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($yearMonths as $ym)
                                    <tr>
                                        <td class="fw-bold bg-light">{{ $ym['label'] }} {{ substr($ym['year'], -2) }}</td>
                                        @for($day = 1; $day <= 31; $day++)
                                            @php $dk = isset($ym['days'][$day]) ? $ym['days'][$day] : null; @endphp
                                            {{-- Yahan style="background-color: #fff3cd;" ek light yellow shade hai (weekly off) --}}
                                            <td class="{{ $dk === 'weekoff' ? 'weekoff-cell' : '' }}" style="{{ $dk === 'weekoff' ? 'background-color: #fff3cd;' : '' }}">
                                                @if($dk === null)
                                                    {{-- is mahine mein ye din nahi --}}
                                                @elseif($dk === 'weekoff')
                                                    <span class="text-danger fw-bold">S</span>
                                                @elseif($dk === 'holiday')
                                                    <span class="att-o fw-bold">O</span>
                                                @elseif(in_array($dk, ['present', 'absent', 'late', 'halfday', 'leave'], true))
                                                    <span class="{{ ['present' => 'text-success', 'late' => 'text-warning', 'halfday' => 'text-info', 'absent' => 'text-danger', 'leave' => 'text-secondary'][$dk] }} fw-bold">
                                                        {{ ['present' => 'P', 'late' => 'L', 'halfday' => 'H', 'absent' => 'A', 'leave' => 'F'][$dk] }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endfor
                                        <!-- Month totals (cards/legend wali hi rule) -->
                                        <td class="fw-bold text-success">{{ $ym['counts']['P'] }}</td>
                                        <td class="fw-bold text-danger">{{ $ym['counts']['A'] }}</td>
                                        <td class="fw-bold text-warning">{{ $ym['counts']['L'] }}</td>
                                        <td class="fw-bold text-info">{{ $ym['counts']['H'] }}</td>
                                        <td class="fw-bold text-secondary">{{ $ym['counts']['F'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var monthPercent = @json($monthPercent);
        var yearPercent = @json($yearPercent);
        var barLabels = @json(collect($yearMonths)->map(function ($m) { return $m['label'] . ' ' . substr($m['year'], -2); })->values());
        var barData = @json(collect($yearMonths)->pluck('percent')->values());

        var charts = [];
        var barChart = null;

        function isDark() {
            return document.documentElement.classList.contains('dark-theme');
        }
        function trackColor() {
            return isDark() ? '#3a3f45' : '#dee2e6';
        }
        function textColor() {
            return isDark() ? '#ced4da' : '#495057';
        }

        // Ring: percent null ho to poora grey ring (tooltip off)
        function makeRing(id, percent) {
            var hasData = percent !== null;
            var chart = new Chart(document.getElementById(id), {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: hasData ? [percent, 100 - percent] : [1],
                        backgroundColor: hasData ? ['#198754', trackColor()] : [trackColor()],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: { legend: { display: false }, tooltip: { enabled: false } }
                }
            });
            charts.push({ chart: chart, hasData: hasData });
        }

        makeRing('monthRing', monthPercent);
        makeRing('yearRing', yearPercent);

        // Tooltip mouse ke paas (null month ka element y=0 par hota hai, default position wahin chipak jati thi); sirf ek dafa register
        if (!Chart.Tooltip.positioners.cursor) {
            Chart.Tooltip.positioners.cursor = function(elements, eventPosition) {
                return eventPosition;
            };
        }

        // Bar chart sirf Yearly tab khulne par (hidden canvas ka size 0 hota hai)
        function makeBar() {
            barChart = new Chart(document.getElementById('yearBar'), {
                type: 'bar',
                data: {
                    labels: barLabels,
                    datasets: [{
                        label: 'Attendance %',
                        data: barData,
                        backgroundColor: '#198754',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    // Poore month column par hover se tooltip (null month mein bar nahi hota, intersect:true kaam nahi karta)
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        y: { min: 0, max: 100, ticks: { color: textColor(), callback: function(v) { return v + '%'; } }, grid: { color: isDark() ? '#3a3f45' : '#e9ecef' } },
                        x: { ticks: { color: textColor() }, grid: { display: false } }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            position: 'cursor',
                            callbacks: {
                                label: function(ctx) {
                                    return (ctx.raw === null || ctx.raw === undefined) ? 'No data' : ctx.raw + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        document.getElementById('tab-yearly').addEventListener('shown.bs.tab', function() {
            if (!barChart) { makeBar(); }
        });

        // Theme toggle (html class badalti hai) ke baad charts ke rang update
        new MutationObserver(function() {
            charts.forEach(function(c) {
                var ds = c.chart.data.datasets[0];
                ds.backgroundColor = c.hasData ? ['#198754', trackColor()] : [trackColor()];
                c.chart.update();
            });
            if (barChart) {
                barChart.options.scales.y.ticks.color = textColor();
                barChart.options.scales.x.ticks.color = textColor();
                barChart.options.scales.y.grid.color = isDark() ? '#3a3f45' : '#e9ecef';
                barChart.update();
            }
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    });
</script>
@endpush
