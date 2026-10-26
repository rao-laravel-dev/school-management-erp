@extends('admin.layout.app')
@section('content')


<!-- Cards Row 1: Revenue / Expense / Users / Session -->
@php
    // Revenue ▲ green; Expense ▲ red (kharcha barhna bura hai)
    $revDelta = $finance->rev_this - $finance->rev_last;
    $expDelta = $finance->exp_this - $finance->exp_last;

    // Session progress: start_date -> end_date me se kitne din guzre (0-100 clamp)
    $sessionText = '-';
    $sessionPercent = 0;
    if ($currentSession && $currentSession->start_date && $currentSession->end_date) {
        $sStart = \Carbon\Carbon::parse($currentSession->start_date)->startOfDay();
        $sEnd = \Carbon\Carbon::parse($currentSession->end_date)->startOfDay();
        $sToday = now()->startOfDay();
        $sTotal = (int) $sStart->diffInDays($sEnd);
        if ($sTotal > 0) {
            if ($sToday->lt($sStart)) {
                $sessionText = 'Not started';
            } elseif ($sToday->gt($sEnd)) {
                $sessionText = 'Session ended';
                $sessionPercent = 100;
            } else {
                $sessionPercent = max(0, min(100, (int) round($sStart->diffInDays($sToday) / $sTotal * 100)));
                $sessionText = $sessionPercent . '% complete · ' . (int) $sToday->diffInDays($sEnd) . ' days left';
            }
        }
    }
@endphp
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 gy-4 mb-4">
    <x-dashboard-stat title="Total Revenue" :value="'Rs ' . number_format($finance->rev_this)" icon="bx bxs-wallet" color="success" spark-id="w-revenue">
        @if($revDelta == 0)
            <p class="mb-0 font-13 text-secondary">No change vs same days last month</p>
        @else
            <p class="mb-0 font-13 {{ $revDelta > 0 ? 'text-success' : 'text-danger' }}"><i class='bx {{ $revDelta > 0 ? 'bxs-up-arrow' : 'bxs-down-arrow' }} align-middle'></i> Rs {{ number_format(abs($revDelta)) }} vs same days last month</p>
        @endif
    </x-dashboard-stat>

    <x-dashboard-stat title="Total Expense" :value="'Rs ' . number_format($finance->exp_this)" icon="bx bxs-credit-card" color="danger" spark-id="w-expense">
        @if($expDelta == 0)
            <p class="mb-0 font-13 text-secondary">No change vs same days last month</p>
        @else
            <p class="mb-0 font-13 {{ $expDelta > 0 ? 'text-danger' : 'text-success' }}"><i class='bx {{ $expDelta > 0 ? 'bxs-up-arrow' : 'bxs-down-arrow' }} align-middle'></i> Rs {{ number_format(abs($expDelta)) }} vs same days last month</p>
        @endif
    </x-dashboard-stat>

    <x-dashboard-stat title="Active Users" :value="$activeUsersCount" icon="bx bxs-user-badge" color="info" spark-id="w-users">
        <p class="mb-0 font-13 text-success">+{{ $newUsersThisMonth }} new this month</p>
    </x-dashboard-stat>

    <x-dashboard-stat title="Session" :value="$currentSession ? $currentSession->name : '-'" icon="bx bx-calendar-event" color="warning" :percent="$sessionPercent" :percent-label="$sessionText">
        <p class="mb-0 font-13 text-secondary">Active {{ $sessionCounts->active }} / Total {{ $sessionCounts->total }}</p>
        @if($currentSession)
            <p class="mb-0 font-13 text-secondary">{{ $currentSession->start_date ? \Carbon\Carbon::parse($currentSession->start_date)->format('d M Y') : '-' }} - {{ $currentSession->end_date ? \Carbon\Carbon::parse($currentSession->end_date)->format('d M Y') : '-' }}</p>
        @else
            <p class="mb-0 font-13 text-secondary">No active session</p>
        @endif
    </x-dashboard-stat>
</div>
<!--end row-->

				<div class="row">
                   <div class="col-12 col-lg-8 d-flex">
                      <div class="card radius-10 w-100">
						<div class="card-header">
							<div class="d-flex align-items-center">
								<div>
									<h6 class="mb-0">Revenue vs Expense</h6>
								</div>
							</div>
						</div>
						  <div class="card-body">
							<div class="d-flex align-items-center ms-auto font-13 gap-2 mb-3">
								<span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #17a00e"></i>Revenue</span>
								<span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #f41127"></i>Expense</span>
							</div>
							<div class="chart-container-1">
								@if($sessionRevenueTotal + $sessionExpenseTotal > 0)
								<canvas id="chart1"></canvas>
								@else
								<div class="h-100 d-flex align-items-center justify-content-center text-secondary">{{ $currentSession ? 'No data yet' : 'No active session' }}</div>
								@endif
							  </div>
						  </div>
						  <div class="row row-cols-1 row-cols-md-3 row-cols-xl-3 g-0 row-group text-center border-top">
							<div class="col">
							  <div class="p-3">
								<h5 class="mb-0">Rs {{ number_format($sessionRevenueTotal) }}</h5>
								<small class="mb-0">Session Total Revenue</small>
							  </div>
							</div>
							<div class="col">
							  <div class="p-3">
								<h5 class="mb-0">Rs {{ number_format($sessionExpenseTotal) }}</h5>
								<small class="mb-0">Session Total Expense</small>
							  </div>
							</div>
							<div class="col">
							  <div class="p-3">
								<h5 class="mb-0 {{ $sessionNet >= 0 ? 'text-success' : 'text-danger' }}">{{ $sessionNet < 0 ? '-' : '' }}Rs {{ number_format(abs($sessionNet)) }}</h5>
								<small class="mb-0">Net (Revenue - Expense)</small>
							  </div>
							</div>
						  </div>
					  </div>
				   </div>
				   <div class="col-12 col-lg-4 d-flex">
                       <div class="card radius-10 w-100">
						<div class="card-header">
							<div class="d-flex align-items-center">
								<div>
									<h6 class="mb-0">Student Attendance (This Month)</h6>
								</div>
							</div>
						</div>
						   <div class="card-body">
							<div class="chart-container-2">
								@if(array_sum($attendanceCounts) > 0)
								<canvas id="chart2"></canvas>
								@else
								<div class="h-100 d-flex align-items-center justify-content-center text-secondary">No data yet</div>
								@endif
							  </div>
						   </div>
						   @php
						       $attBadges = ['Present' => 'bg-success', 'Late' => 'bg-warning text-dark', 'Half Day' => 'bg-info text-dark', 'Absent' => 'bg-danger', 'Leave' => 'bg-primary'];
						   @endphp
						   <ul class="list-group list-group-flush">
							@foreach($attendanceCounts as $attLabel => $attCount)
							<li class="list-group-item d-flex bg-transparent justify-content-between align-items-center{{ $loop->first ? ' border-top' : '' }}">{{ $attLabel }} <span class="badge {{ $attBadges[$attLabel] }} rounded-pill">{{ $attCount }}</span>
							</li>
							@endforeach
						</ul>
					   </div>
				   </div>
				</div><!--end row-->

<!-- Cards Row 2: Students + Staff (today attendance) -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-5 gy-4 mb-4">
    <x-dashboard-stat title="Students" :value="$presentStudents . ' / ' . $totalStudents" icon="bx bxs-graduation" color="success" :percent="$studentPercent">
        <p class="mb-0 font-13 text-secondary">Present today</p>
    </x-dashboard-stat>

    @foreach($staffCards as $card)
    <x-dashboard-stat :title="$card['label']" :value="$card['present'] . ' / ' . $card['total']" :icon="$card['icon']" :color="$card['color']" :percent="$card['marked'] ? $card['percent'] : 0">
        <p class="mb-0 font-13 text-secondary">{{ $card['marked'] ? 'Present today' : 'Not marked yet' }}</p>
    </x-dashboard-stat>
    @endforeach
</div>

@endsection

{{-- --- DASHBOARD CHARTS INJECTED HERE SAFELY --- --}}
@push('scripts')
    <script src="{{ asset('backend/assets/plugins/chartjs/js/chart.js') }}"></script>
    @include('partials.dashboard_sparkline')
    <script>
    // Row 1 sparklines (ApexCharts, layout me pehle se loaded): pichle 12 mahine
    $(function () {
        var labels = @json($monthLabels);

        dashSparkline('#w-revenue', 'area', '#17a00e', 'Revenue', @json($revenueSeries), labels, true);
        dashSparkline('#w-expense', 'area', '#f41127', 'Expense', @json($expenseSeries), labels, true);
        dashSparkline('#w-users', 'area', '#0dcaf0', 'New users', @json($usersSeries), labels, false);

        // Charts section (Chart.js): light/dark theme ke hisab se text + grid colors (html.dark-theme)
        function chartTheme() {
            var dark = $('html').hasClass('dark-theme');
            return {
                text: dark ? '#aab0b6' : '#6c757d',
                grid: dark ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.08)'
            };
        }

        // Revenue vs Expense (session ke mahine)
        var revExpChart = null;
        var revExpEl = document.getElementById('chart1');
        if (revExpEl) {
            var th = chartTheme();
            revExpChart = new Chart(revExpEl.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($sessionLabels),
                    datasets: [
                        { label: 'Revenue', data: @json($sessionRevenue), backgroundColor: '#17a00e', borderRadius: { topLeft: 20, topRight: 20, bottomLeft: 0, bottomRight: 0 }, borderSkipped: false, borderWidth: 0 },
                        { label: 'Expense', data: @json($sessionExpense), backgroundColor: '#f41127', borderRadius: { topLeft: 20, topRight: 20, bottomLeft: 0, bottomRight: 0 }, borderSkipped: false, borderWidth: 0 }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    barPercentage: 0.5,
                    categoryPercentage: 0.8,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) { return ctx.dataset.label + ': Rs ' + Number(ctx.parsed.y).toLocaleString(); }
                            }
                        }
                    },
                    scales: {
                        x: { ticks: { color: th.text }, grid: { color: th.grid } },
                        y: {
                            beginAtZero: true,
                            ticks: { color: th.text, callback: function (v) { return 'Rs ' + Number(v).toLocaleString(); } },
                            grid: { color: th.grid }
                        }
                    }
                }
            });
        }

        // Student Attendance (is mahine): Present / Late / Half Day / Absent / Leave
        var attEl = document.getElementById('chart2');
        if (attEl) {
            new Chart(attEl.getContext('2d'), {
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
            if (!revExpChart) { return; }
            var t = chartTheme();
            revExpChart.options.scales.x.ticks.color = t.text;
            revExpChart.options.scales.x.grid.color = t.grid;
            revExpChart.options.scales.y.ticks.color = t.text;
            revExpChart.options.scales.y.grid.color = t.grid;
            revExpChart.update();
        });
    });
    </script>
@endpush