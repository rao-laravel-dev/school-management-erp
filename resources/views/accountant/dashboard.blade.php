@extends('accountant.layout.app')
@section('content')

@php
	// Fees Collected: month-to-date vs pichle mahine ke same din (Admin dashboard wali rule)
	$collectedDelta = $collectedThis - $collectedLast;
	$collectedToday = count($collectedDaily) ? $collectedDaily[count($collectedDaily) - 1] : 0;
	$slipPercent = (int) $slipStats->total > 0 ? round(((int) $slipStats->paid / (int) $slipStats->total) * 100) : 0;
@endphp

<!-- Cards Row 1: Fees Collected / Pending Fees / Salary Slips / My Attendance -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 gy-4 mb-4">
	@can('access-fee-collections')
	<x-dashboard-stat title="Fees Collected (This Month)" :value="'Rs ' . number_format($collectedThis, 0)" icon="bx bx-wallet" color="success" spark-id="w-collected" :href="route('fees.collect.index')" href-label="Open Collect Fees">
		@if($collectedDelta == 0)
			<p class="mb-0 font-13 text-secondary">No change vs same days last month</p>
		@else
			<p class="mb-0 font-13 {{ $collectedDelta > 0 ? 'text-success' : 'text-danger' }}"><i class='bx {{ $collectedDelta > 0 ? 'bxs-up-arrow' : 'bxs-down-arrow' }} align-middle'></i> Rs {{ number_format(abs($collectedDelta), 0) }} vs same days last month</p>
		@endif
	</x-dashboard-stat>
	<x-dashboard-stat title="Pending Fees (Up to This Month)" :value="'Rs ' . number_format($pendingStats->amount, 0)" icon="bx bx-time-five" color="danger" spark-id="w-pending" :href="route('fees.collect.index')" href-label="Open Collect Fees">
		<p class="mb-0 font-13 text-secondary">{{ (int) $pendingStats->cnt }} unpaid / partial fee(s)</p>
	</x-dashboard-stat>
	@endcan
	@can('access-salary-slips')
	<x-dashboard-stat title="Salary Slips (This Month)" :value="(int) $slipStats->total > 0 ? (int) $slipStats->paid . ' paid / ' . (int) $slipStats->pending . ' pending' : '-'" icon="bx bx-receipt" color="primary" :percent="$slipPercent" :href="route('salary_slips.index')" href-label="Open Salary Slips">
		<p class="mb-0 font-13 text-secondary">{{ (int) $slipStats->total > 0 ? (int) $slipStats->total . ' slip(s), Rs ' . number_format($slipStats->net, 0) : 'No slips generated yet' }}</p>
	</x-dashboard-stat>
	@endcan
	@can('access-my-attendance')
	<x-dashboard-stat title="My Attendance" :value="$myAtt['marked'] > 0 ? ($myAtt['present'] + $myAtt['half_day']) . ' / ' . $myAtt['marked'] : '-'" icon="bx bx-calendar-check" color="warning" :percent="$myAtt['marked'] > 0 ? $myAtt['percent'] : 0" :href="route('my.attendance.index')" href-label="Open My Attendance Report">
		<p class="mb-0 font-13 text-secondary">{{ $myAtt['marked'] > 0 ? $myAtt['percent'] . '% this month' : 'Not marked yet' }}</p>
	</x-dashboard-stat>
	@endcan
</div><!--end row-->

<!-- Row 2: Fee Collection + Fee Status -->
@can('access-fee-collections')
<div class="row">
	<div class="col-12 col-lg-8 d-flex">
		<div class="card radius-10 w-100">
			<div class="card-header">
				<h6 class="mb-0">Fee Collection</h6>
			</div>
			<div class="card-body">
				<div class="chart-container-1">
					@if($sessionCollectedTotal > 0)
					<canvas id="chart1"></canvas>
					@else
					<div class="h-100 d-flex align-items-center justify-content-center text-secondary">{{ $currentSession ? 'No data yet' : 'No active session' }}</div>
					@endif
				</div>
			</div>
			<div class="row row-cols-1 row-cols-md-3 row-cols-xl-3 g-0 row-group text-center border-top">
				<div class="col">
					<div class="p-3">
						<h5 class="mb-0">Rs {{ number_format($sessionCollectedTotal, 0) }}</h5>
						<small class="mb-0">Session Total</small>
					</div>
				</div>
				<div class="col">
					<div class="p-3">
						<h5 class="mb-0">Rs {{ number_format($collectedThis, 0) }}</h5>
						<small class="mb-0">This Month</small>
					</div>
				</div>
				<div class="col">
					<div class="p-3">
						<h5 class="mb-0">Rs {{ number_format($collectedToday, 0) }}</h5>
						<small class="mb-0">Today</small>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-12 col-lg-4 d-flex">
		<div class="card radius-10 w-100">
			<div class="card-header">
				<h6 class="mb-0">Fee Status (Session)</h6>
			</div>
			<div class="card-body">
				<div class="chart-container-2">
					@if(array_sum($feeStatus) > 0)
					<canvas id="chart2"></canvas>
					@else
					<div class="h-100 d-flex align-items-center justify-content-center text-secondary">No data yet</div>
					@endif
				</div>
			</div>
			@php
				$statusBadges = ['Paid' => 'bg-success', 'Partial' => 'bg-warning text-dark', 'Unpaid' => 'bg-danger'];
			@endphp
			<ul class="list-group list-group-flush">
				@foreach($feeStatus as $statusLabel => $statusCount)
				<li class="list-group-item d-flex bg-transparent justify-content-between align-items-center{{ $loop->first ? ' border-top' : '' }}">{{ $statusLabel }} <span class="badge {{ $statusBadges[$statusLabel] }} rounded-pill">{{ $statusCount }}</span>
				</li>
				@endforeach
			</ul>
		</div>
	</div>
</div><!--end row-->
@endcan

<!-- Row 3: Recent Fee Payments + My Leave Requests -->
<div class="row">
	@can('access-fee-collections')
	<div class="col-12 col-lg-8 d-flex">
		<div class="card radius-10 w-100">
			<div class="card-header">
				<div class="d-flex align-items-center">
					<h6 class="mb-0">Recent Fee Payments</h6>
					<a href="{{ route('fees.collect.index') }}" class="btn btn-sm btn-outline-primary ms-auto">Collect Fees</a>
				</div>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table id="recentPaymentsTable" class="table table-striped table-bordered align-middle table-sm w-100">
						<thead class="table-light">
							<tr>
								<th>Student</th>
								<th>Fee Type</th>
								<th>Date</th>
								<th>Amount</th>
								<th>Method</th>
							</tr>
						</thead>
						<tbody>
							@foreach($recentPayments as $pay)
							@php
								$payFee = $pay->studentFee;
								$payStudent = $payFee ? $payFee->student : null;
							@endphp
							<tr>
								<td>{{ $payStudent ? $payStudent->full_name : '-' }}</td>
								<td>{{ $payFee && $payFee->feeType ? $payFee->feeType->name : '-' }}</td>
								<td data-order="{{ $pay->transaction_date->format('Y-m-d') }}">{{ $pay->transaction_date->format('d-m-Y') }}</td>
								<td data-order="{{ (float) $pay->amount }}">Rs {{ number_format($pay->amount, 0) }}</td>
								<td>{{ ucfirst(str_replace('_', ' ', $pay->payment_method)) }}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	@endcan
	@can('access-leave-application')
	<div class="col-12 col-lg-4 d-flex">
		@include('partials.dashboard_leave_card')
	</div>
	@endcan
</div><!--end row-->

@endsection

@push('scripts')
<script src="{{ asset('backend/assets/plugins/apexcharts-bundle/js/apexcharts.min.js') }}"></script>
@include('partials.dashboard_sparkline')
<script>
$(document).ready(function () {
	// Recent payments: sirf latest 10, isliye paging/search band
	if ($('#recentPaymentsTable').length) {
		$('#recentPaymentsTable').DataTable({
			paging: false,
			searching: false,
			info: false,
			order: [],
			language: { emptyTable: 'No fee payments yet.' }
		});
	}


	// Daily sparkline labels: "1 Oct", "2 Oct" ...
	var monthName = @json(now()->format('M'));
	var collectedDaily = @json($collectedDaily);
	var dayLabels = [];
	for (var d = 1; d <= collectedDaily.length; d++) { dayLabels.push(d + ' ' + monthName); }
	dashSparkline('#w-collected', 'area', '#17a00e', 'Collected', collectedDaily, dayLabels, true);
	// Pending Fees: session ke har due mahine ka baqaya (thin bars)
	dashSparkline('#w-pending', 'bar', '#f41127', 'Pending', @json($pendingSeries), @json($pendingLabels), true);

	// Charts section (Chart.js): light/dark theme ke hisab se text + grid colors (html.dark-theme)
	function chartTheme() {
		var dark = $('html').hasClass('dark-theme');
		return {
			text: dark ? '#aab0b6' : '#6c757d',
			grid: dark ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.08)'
		};
	}

	// Fee Collection (session ke mahine)
	var feeChart = null;
	var feeEl = document.getElementById('chart1');
	if (feeEl) {
		var th = chartTheme();
		feeChart = new Chart(feeEl.getContext('2d'), {
			type: 'bar',
			data: {
				labels: @json($sessionLabels),
				datasets: [{ label: 'Collected', data: @json($sessionCollected), backgroundColor: '#17a00e', borderRadius: { topLeft: 20, topRight: 20, bottomLeft: 0, bottomRight: 0 }, borderSkipped: false, borderWidth: 0 }]
			},
			options: {
				maintainAspectRatio: false,
				// Ek hi series hai: admin chart (2 series, 0.5) ke ek bar jitni chaudai ke liye 0.25
				barPercentage: 0.25,
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

	// Fee Status: Paid / Partial / Unpaid
	var statusEl = document.getElementById('chart2');
	if (statusEl) {
		new Chart(statusEl.getContext('2d'), {
			type: 'doughnut',
			data: {
				labels: @json(array_keys($feeStatus)),
				datasets: [{
					data: @json(array_values($feeStatus)),
					backgroundColor: ['#17a00e', '#ffc107', '#f41127'],
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
		if (!feeChart) { return; }
		var t = chartTheme();
		feeChart.options.scales.x.ticks.color = t.text;
		feeChart.options.scales.x.grid.color = t.grid;
		feeChart.options.scales.y.ticks.color = t.text;
		feeChart.options.scales.y.grid.color = t.grid;
		feeChart.update();
	});
});
</script>
@endpush
