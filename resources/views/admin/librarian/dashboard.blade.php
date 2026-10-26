@extends('admin.librarian.layout.app')
@section('content')

@php
	// Issued / Returned: month-to-date vs pichle mahine ke same din (Admin dashboard wali rule); up = green dono ke liye
	$issuedDelta = $issuedThis - $issuedLast;
	$returnedDelta = $returnedThis - $returnedLast;
@endphp

<!-- Cards Row 1: Issued / Returned / Overdue / My Attendance -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 gy-4 mb-4">
	@can('access-book-issues')
	<x-dashboard-stat title="Books Issued (This Month)" :value="$issuedThis" icon="bx bx-book-reader" color="primary" spark-id="w-issued" :href="route('book_issue.index')" href-label="Open Issue / Return Book">
		@if($issuedDelta == 0)
			<p class="mb-0 font-13 text-secondary">No change vs same days last month</p>
		@else
			<p class="mb-0 font-13 {{ $issuedDelta > 0 ? 'text-success' : 'text-danger' }}"><i class='bx {{ $issuedDelta > 0 ? 'bxs-up-arrow' : 'bxs-down-arrow' }} align-middle'></i> {{ abs($issuedDelta) }} vs same days last month</p>
		@endif
	</x-dashboard-stat>
	<x-dashboard-stat title="Books Returned (This Month)" :value="$returnedThis" icon="bx bx-check-circle" color="success" spark-id="w-returned" :href="route('book_issue.index')" href-label="Open Issue / Return Book">
		@if($returnedDelta == 0)
			<p class="mb-0 font-13 text-secondary">No change vs same days last month</p>
		@else
			<p class="mb-0 font-13 {{ $returnedDelta > 0 ? 'text-success' : 'text-danger' }}"><i class='bx {{ $returnedDelta > 0 ? 'bxs-up-arrow' : 'bxs-down-arrow' }} align-middle'></i> {{ abs($returnedDelta) }} vs same days last month</p>
		@endif
	</x-dashboard-stat>
	<x-dashboard-stat title="Overdue" :value="(int) $issueStats->overdue" icon="bx bx-time-five" color="danger" spark-id="w-overdue" :href="route('book_issue.index')" href-label="Open Issue / Return Book">
		<p class="mb-0 font-13 text-secondary">Past due date</p>
	</x-dashboard-stat>
	@endcan
	@can('access-my-attendance')
	<x-dashboard-stat title="My Attendance" :value="$myAtt['marked'] > 0 ? ($myAtt['present'] + $myAtt['half_day']) . ' / ' . $myAtt['marked'] : '-'" icon="bx bx-calendar-check" color="warning" :percent="$myAtt['marked'] > 0 ? $myAtt['percent'] : 0" :href="route('my.attendance.index')" href-label="Open My Attendance Report">
		<p class="mb-0 font-13 text-secondary">{{ $myAtt['marked'] > 0 ? $myAtt['percent'] . '% this month' : 'Not marked yet' }}</p>
	</x-dashboard-stat>
	@endcan
</div><!--end row-->

<!-- Row 2: Issues vs Returns + Book Status -->
<div class="row">
	@can('access-book-issues')
	<div class="col-12 col-lg-8 d-flex">
		<div class="card radius-10 w-100">
			<div class="card-header">
				<h6 class="mb-0">Issues vs Returns</h6>
			</div>
			<div class="card-body">
				<div class="d-flex align-items-center ms-auto font-13 gap-2 mb-3">
					<span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #0d6efd"></i>Issued</span>
					<span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #17a00e"></i>Returned</span>
				</div>
				<div class="chart-container-1">
					@if($sessionIssuedTotal + $sessionReturnedTotal > 0)
					<canvas id="chart1"></canvas>
					@else
					<div class="h-100 d-flex align-items-center justify-content-center text-secondary">{{ $currentSession ? 'No data yet' : 'No active session' }}</div>
					@endif
				</div>
			</div>
			<div class="row row-cols-1 row-cols-md-3 row-cols-xl-3 g-0 row-group text-center border-top">
				<div class="col">
					<div class="p-3">
						<h5 class="mb-0">{{ $sessionIssuedTotal }}</h5>
						<small class="mb-0">Total Issued</small>
					</div>
				</div>
				<div class="col">
					<div class="p-3">
						<h5 class="mb-0">{{ $sessionReturnedTotal }}</h5>
						<small class="mb-0">Total Returned</small>
					</div>
				</div>
				<div class="col">
					<div class="p-3">
						<h5 class="mb-0">{{ (int) $issueStats->issued }}</h5>
						<small class="mb-0">Currently Out</small>
					</div>
				</div>
			</div>
		</div>
	</div>
	@endcan
	@canany(['access-books', 'access-book-issues'])
	<div class="col-12 col-lg-4 d-flex">
		<div class="card radius-10 w-100">
			<div class="card-header">
				<h6 class="mb-0">Book Status</h6>
			</div>
			<div class="card-body">
				<div class="chart-container-2">
					@if(array_sum($bookStatus) > 0)
					<canvas id="chart2"></canvas>
					@else
					<div class="h-100 d-flex align-items-center justify-content-center text-secondary">No data yet</div>
					@endif
				</div>
			</div>
			@php
				$statusBadges = ['Available' => 'bg-success', 'Issued' => 'bg-warning text-dark', 'Overdue' => 'bg-danger', 'Lost' => 'bg-secondary'];
			@endphp
			<ul class="list-group list-group-flush">
				@foreach($bookStatus as $statusLabel => $statusCount)
				<li class="list-group-item d-flex bg-transparent justify-content-between align-items-center{{ $loop->first ? ' border-top' : '' }}">{{ $statusLabel }} <span class="badge {{ $statusBadges[$statusLabel] }} rounded-pill">{{ $statusCount }}</span>
				</li>
				@endforeach
			</ul>
		</div>
	</div>
	@endcanany
</div><!--end row-->

<!-- Row 3: Recent Book Issues + Library Members + My Leave Requests -->
<div class="row">
	@can('access-book-issues')
	<div class="col-12 col-lg-6 d-flex">
		<div class="card radius-10 w-100">
			<div class="card-header">
				<div class="d-flex align-items-center">
					<div>
						<h6 class="mb-0">Recent Book Issues</h6>
					</div>
					<a href="{{ route('book_issue.index') }}" class="btn btn-sm btn-outline-primary ms-auto">View All</a>
				</div>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table id="recentIssuesTable" class="table table-striped table-bordered align-middle table-sm w-100">
						<thead class="table-light">
							<tr>
								<th>Member</th>
								<th>Book</th>
								<th>Issue Date</th>
								<th>Due Date</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							@foreach($recentIssues as $issue)
							@php
								$member = $issue->libraryMember;
								$memberName = '-';
								if ($member) {
									$memberName = $member->member_type === 'student'
										? ($studentNames[$member->member_id] ?? '-')
										: ($staffNames[$member->member_id] ?? '-');
								}
								$isOverdue = in_array($issue->status, ['issued', 'overdue']) && $issue->due_date < $today;
								if ($issue->status === 'returned') {
									$badge = 'bg-success'; $label = 'Returned';
								} elseif ($issue->status === 'lost') {
									$badge = 'bg-secondary'; $label = 'Lost';
								} elseif ($isOverdue) {
									$badge = 'bg-danger'; $label = 'Overdue';
								} else {
									$badge = 'bg-warning text-dark'; $label = 'Issued';
								}
							@endphp
							<tr>
								<td>
									{{ $memberName }}
									@if($member)<br><small class="text-secondary">{{ ucfirst($member->member_type) }}</small>@endif
								</td>
								<td>{{ $issue->book->title ?? '-' }}</td>
								<td data-order="{{ \Carbon\Carbon::parse($issue->issue_date)->format('Y-m-d') }}">{{ \Carbon\Carbon::parse($issue->issue_date)->format('d-m-Y') }}</td>
								<td data-order="{{ \Carbon\Carbon::parse($issue->due_date)->format('Y-m-d') }}" class="{{ $isOverdue ? 'text-danger fw-semibold' : '' }}">{{ \Carbon\Carbon::parse($issue->due_date)->format('d-m-Y') }}</td>
								<td><span class="badge {{ $badge }}">{{ $label }}</span></td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	@endcan
	@can('access-library-members')
	<div class="col-12 col-lg-3 d-flex">
		<div class="card radius-10 w-100">
			<div class="card-header">
				<h6 class="mb-0">Library Members</h6>
			</div>
			<div class="card-body">
				<div style="position: relative; height: 180px;">
					@if((int) $memberStats->total > 0)
					<canvas id="chart3"></canvas>
					@else
					<div class="h-100 d-flex align-items-center justify-content-center text-secondary">No members yet</div>
					@endif
				</div>
			</div>
			<ul class="list-group list-group-flush border-top">
				<li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
					<a href="{{ route('library_settings.members.student') }}" class="text-reset text-decoration-none">Student Members</a>
					<span class="badge bg-primary rounded-pill">{{ (int) $memberStats->students }}</span>
				</li>
				<li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
					<a href="{{ route('library_settings.members.staff') }}" class="text-reset text-decoration-none">Staff Members</a>
					<span class="badge bg-success rounded-pill">{{ (int) $memberStats->staff }}</span>
				</li>
				<li class="list-group-item d-flex bg-transparent justify-content-between align-items-center fw-semibold">
					Total Members
					<span class="badge bg-secondary rounded-pill">{{ (int) $memberStats->total }}</span>
				</li>
			</ul>
		</div>
	</div>
	@endcan
	@can('access-leave-application')
	<div class="col-12 col-lg-3 d-flex">
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
	// Recent issues: sirf latest 10, isliye paging/search band
	if ($('#recentIssuesTable').length) {
		$('#recentIssuesTable').DataTable({
			paging: false,
			searching: false,
			info: false,
			order: [],
			language: { emptyTable: 'No book issues yet.' }
		});
	}


	// Daily sparkline labels: "1 Oct", "2 Oct" ...
	var monthName = @json(now()->format('M'));
	var issuedDaily = @json($issuedDaily);
	var returnedDaily = @json($returnedDaily);
	var dayLabels = [];
	for (var d = 1; d <= issuedDaily.length; d++) { dayLabels.push(d + ' ' + monthName); }

	dashSparkline('#w-issued', 'area', '#0d6efd', 'Issued', issuedDaily, dayLabels, false);
	dashSparkline('#w-returned', 'area', '#17a00e', 'Returned', returnedDaily, dayLabels, false);
	dashSparkline('#w-overdue', 'bar', '#f41127', 'Overdue', @json($overdueSeries), @json($monthLabels), false);

	// Charts section (Chart.js): light/dark theme ke hisab se text + grid colors (html.dark-theme)
	function chartTheme() {
		var dark = $('html').hasClass('dark-theme');
		return {
			text: dark ? '#aab0b6' : '#6c757d',
			grid: dark ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.08)'
		};
	}

	// Issues vs Returns (session ke mahine)
	var issueChart = null;
	var issueEl = document.getElementById('chart1');
	if (issueEl) {
		var th = chartTheme();
		issueChart = new Chart(issueEl.getContext('2d'), {
			type: 'bar',
			data: {
				labels: @json($sessionLabels),
				datasets: [
					{ label: 'Issued', data: @json($sessionIssued), backgroundColor: '#0d6efd', borderRadius: { topLeft: 20, topRight: 20, bottomLeft: 0, bottomRight: 0 }, borderSkipped: false, borderWidth: 0 },
					{ label: 'Returned', data: @json($sessionReturned), backgroundColor: '#17a00e', borderRadius: { topLeft: 20, topRight: 20, bottomLeft: 0, bottomRight: 0 }, borderSkipped: false, borderWidth: 0 }
				]
			},
			options: {
				maintainAspectRatio: false,
				barPercentage: 0.5,
				categoryPercentage: 0.8,
				plugins: { legend: { display: false } },
				scales: {
					x: { ticks: { color: th.text }, grid: { color: th.grid } },
					y: {
						beginAtZero: true,
						ticks: { color: th.text, precision: 0 },
						grid: { color: th.grid }
					}
				}
			}
		});
	}

	// Book Status: Available / Issued / Overdue / Lost
	var statusEl = document.getElementById('chart2');
	if (statusEl) {
		new Chart(statusEl.getContext('2d'), {
			type: 'doughnut',
			data: {
				labels: @json(array_keys($bookStatus)),
				datasets: [{
					data: @json(array_values($bookStatus)),
					backgroundColor: ['#17a00e', '#ffc107', '#f41127', '#6c757d'],
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

	// Library Members: Student vs Staff (Book Status wali doughnut config, colors list badges ke saath)
	var membersEl = document.getElementById('chart3');
	if (membersEl) {
		new Chart(membersEl.getContext('2d'), {
			type: 'doughnut',
			data: {
				labels: ['Student Members', 'Staff Members'],
				datasets: [{
					data: [{{ (int) $memberStats->students }}, {{ (int) $memberStats->staff }}],
					backgroundColor: ['#0d6efd', '#17a00e'],
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
		if (!issueChart) { return; }
		var t = chartTheme();
		issueChart.options.scales.x.ticks.color = t.text;
		issueChart.options.scales.x.grid.color = t.grid;
		issueChart.options.scales.y.ticks.color = t.text;
		issueChart.options.scales.y.grid.color = t.grid;
		issueChart.update();
	});
});
</script>
@endpush
