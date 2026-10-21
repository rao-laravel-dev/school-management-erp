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
    <div class="col">
        <div class="card radius-10 h-100 mb-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Total Revenue</p>
                        <h4 class="my-1">Rs {{ number_format($finance->rev_this) }}</h4>
                        @if($revDelta == 0)
                            <p class="mb-0 font-13 text-secondary">No change vs same days last month</p>
                        @else
                            <p class="mb-0 font-13 {{ $revDelta > 0 ? 'text-success' : 'text-danger' }}"><i class='bx {{ $revDelta > 0 ? 'bxs-up-arrow' : 'bxs-down-arrow' }} align-middle'></i> Rs {{ number_format(abs($revDelta)) }} vs same days last month</p>
                        @endif
                    </div>
                    <div class="widgets-icons bg-light-success text-success ms-auto"><i class='bx bxs-wallet'></i></div>
                </div>
                <div id="w-revenue" class="mt-2"></div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card radius-10 h-100 mb-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Total Expense</p>
                        <h4 class="my-1">Rs {{ number_format($finance->exp_this) }}</h4>
                        @if($expDelta == 0)
                            <p class="mb-0 font-13 text-secondary">No change vs same days last month</p>
                        @else
                            <p class="mb-0 font-13 {{ $expDelta > 0 ? 'text-danger' : 'text-success' }}"><i class='bx {{ $expDelta > 0 ? 'bxs-up-arrow' : 'bxs-down-arrow' }} align-middle'></i> Rs {{ number_format(abs($expDelta)) }} vs same days last month</p>
                        @endif
                    </div>
                    <div class="widgets-icons bg-light-danger text-danger ms-auto"><i class='bx bxs-credit-card'></i></div>
                </div>
                <div id="w-expense" class="mt-2"></div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card radius-10 h-100 mb-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Active Users</p>
                        <h4 class="my-1">{{ $activeUsersCount }}</h4>
                        <p class="mb-0 font-13 text-success">+{{ $newUsersThisMonth }} new this month</p>
                    </div>
                    <div class="widgets-icons bg-light-info text-info ms-auto"><i class='bx bxs-user-badge'></i></div>
                </div>
                <div id="w-users" class="mt-2"></div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card radius-10 h-100 mb-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Session</p>
                        @if($currentSession)
                            <h4 class="my-1">{{ $currentSession->name }}</h4>
                            <p class="mb-0 font-13 text-secondary">Active {{ $sessionCounts->active }} / Total {{ $sessionCounts->total }}</p>
                            <p class="mb-0 font-13 text-secondary">{{ $currentSession->start_date ? \Carbon\Carbon::parse($currentSession->start_date)->format('d M Y') : '-' }} - {{ $currentSession->end_date ? \Carbon\Carbon::parse($currentSession->end_date)->format('d M Y') : '-' }}</p>
                        @else
                            <h4 class="my-1">-</h4>
                            <p class="mb-0 font-13 text-secondary">Active {{ $sessionCounts->active }} / Total {{ $sessionCounts->total }}</p>
                            <p class="mb-0 font-13 text-secondary">No active session</p>
                        @endif
                    </div>
                    <div class="widgets-icons bg-light-warning text-warning ms-auto"><i class='bx bx-calendar-event'></i></div>
                </div>
                <p class="mb-1 mt-3 font-13 text-secondary">{{ $sessionText }}</p>
                <div class="progress radius-10" style="height: 4.5px;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $sessionPercent }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end row-->

				<div class="row">
                   <div class="col-12 col-lg-8 d-flex">
                      <div class="card radius-10 w-100">
						<div class="card-header">
							<div class="d-flex align-items-center">
								<div>
									<h6 class="mb-0">Sales Overview</h6>
								</div>
								<div class="dropdown ms-auto">
									<a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
									</a>
									<ul class="dropdown-menu">
										<li><a class="dropdown-item" href="javascript:;">Action</a>
										</li>
										<li><a class="dropdown-item" href="javascript:;">Another action</a>
										</li>
										<li>
											<hr class="dropdown-divider">
										</li>
										<li><a class="dropdown-item" href="javascript:;">Something else here</a>
										</li>
									</ul>
								</div>
							</div>
						</div>
						  <div class="card-body">
							<div class="d-flex align-items-center ms-auto font-13 gap-2 mb-3">
								<span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #14abef"></i>Sales</span>
								<span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #ffc107"></i>Visits</span>
							</div>
							<div class="chart-container-1">
								<canvas id="chart1"></canvas>
							  </div>
						  </div>
						  <div class="row row-cols-1 row-cols-md-3 row-cols-xl-3 g-0 row-group text-center border-top">
							<div class="col">
							  <div class="p-3">
								<h5 class="mb-0">24.15M</h5>
								<small class="mb-0">Overall Visitor <span> <i class="bx bx-up-arrow-alt align-middle"></i> 2.43%</span></small>
							  </div>
							</div>
							<div class="col">
							  <div class="p-3">
								<h5 class="mb-0">12:38</h5>
								<small class="mb-0">Visitor Duration <span> <i class="bx bx-up-arrow-alt align-middle"></i> 12.65%</span></small>
							  </div>
							</div>
							<div class="col">
							  <div class="p-3">
								<h5 class="mb-0">639.82</h5>
								<small class="mb-0">Pages/Visit <span> <i class="bx bx-up-arrow-alt align-middle"></i> 5.62%</span></small>
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
									<h6 class="mb-0">Trending Products</h6>
								</div>
								<div class="dropdown ms-auto">
									<a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
									</a>
									<ul class="dropdown-menu">
										<li><a class="dropdown-item" href="javascript:;">Action</a>
										</li>
										<li><a class="dropdown-item" href="javascript:;">Another action</a>
										</li>
										<li>
											<hr class="dropdown-divider">
										</li>
										<li><a class="dropdown-item" href="javascript:;">Something else here</a>
										</li>
									</ul>
								</div>
							</div>
						</div>
						   <div class="card-body">
							<div class="chart-container-2">
								<canvas id="chart2"></canvas>
							  </div>
						   </div>
						   <ul class="list-group list-group-flush">
							<li class="list-group-item d-flex bg-transparent justify-content-between align-items-center border-top">Jeans <span class="badge bg-success rounded-pill">25</span>
							</li>
							<li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">T-Shirts <span class="badge bg-danger rounded-pill">10</span>
							</li>
							<li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">Shoes <span class="badge bg-primary rounded-pill">65</span>
							</li>
							<li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">Lingerie <span class="badge bg-warning text-dark rounded-pill">14</span>
							</li>
						</ul>
					   </div>
				   </div>
				</div><!--end row-->

<!-- Cards Row 2: Students + Staff (today attendance) -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-5">
    <div class="col">
        <div class="card radius-10">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Students</p>
                        <h4 class="my-1">{{ $presentStudents }} / {{ $totalStudents }}</h4>
                        <p class="mb-0 font-13 text-secondary">Present today</p>
                    </div>
                    <div class="widgets-icons bg-light-success text-success ms-auto"><i class='bx bxs-graduation'></i></div>
                </div>
                <div class="progress mt-3" style="height: 4.5px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $studentPercent }}%"></div>
                </div>
            </div>
        </div>
    </div>

    @foreach($staffCards as $card)
    <div class="col">
        <div class="card radius-10">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">{{ $card['label'] }}</p>
                        <h4 class="my-1">{{ $card['present'] }} / {{ $card['total'] }}</h4>
                        <p class="mb-0 font-13 text-secondary">{{ $card['marked'] ? 'Present today' : 'Not marked yet' }}</p>
                    </div>
                    <div class="widgets-icons bg-light-{{ $card['color'] }} text-{{ $card['color'] }} ms-auto"><i class='{{ $card['icon'] }}'></i></div>
                </div>
                <div class="progress mt-3" style="height: 4.5px;">
                    <div class="progress-bar bg-{{ $card['color'] }}" role="progressbar" style="width: {{ $card['marked'] ? $card['percent'] : 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
<!--end row-->

@endsection

{{-- --- DASHBOARD CHARTS INJECTED HERE SAFELY --- --}}
@push('scripts')
    <script src="{{ asset('backend/assets/plugins/chartjs/js/chart.js') }}"></script>
    <script src="{{ asset('backend/assets/js/dashboard-charts.js') }}"></script> {{-- Naya Naam --}}
    <script>
    // Row 1 sparklines (ApexCharts, layout me pehle se loaded): pichle 12 mahine
    $(function () {
        var labels = @json($monthLabels);

        function sparkline(selector, type, color, name, data, isMoney) {
            var options = {
                series: [{ name: name, data: data }],
                chart: { type: type, height: 65, toolbar: { show: false }, zoom: { enabled: false }, sparkline: { enabled: true } },
                stroke: { show: true, width: type === 'bar' ? 0 : 2.4, curve: 'smooth' },
                // ApexCharts v3.19.0: borderRadius bar par supported nahi, endingShape 'rounded' hi chalta hai
                plotOptions: { bar: { horizontal: false, columnWidth: '45%', endingShape: 'rounded' } },
                dataLabels: { enabled: false },
                colors: [color],
                xaxis: { categories: labels },
                fill: { opacity: type === 'area' ? 0.35 : 1 },
                tooltip: {
                    theme: 'dark',
                    fixed: { enabled: false },
                    x: { show: true },
                    y: {
                        title: { formatter: function () { return ''; } },
                        formatter: function (v) { return isMoney ? 'Rs ' + Number(v).toLocaleString() : v; }
                    },
                    marker: { show: false }
                }
            };
            new ApexCharts(document.querySelector(selector), options).render();
        }

        sparkline('#w-revenue', 'area', '#17a00e', 'Revenue', @json($revenueSeries), true);
        sparkline('#w-expense', 'area', '#f41127', 'Expense', @json($expenseSeries), true);
        sparkline('#w-users', 'bar', '#0dcaf0', 'New users', @json($usersSeries), false);
    });
    </script>
@endpush