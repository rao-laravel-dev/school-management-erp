{{-- My Leave Requests card (same on every staff dashboard). Needs $recentLeaves; wrap in @can('access-leave-application') and a col --}}
@php
	$leaveBadges = ['pending' => 'bg-warning text-dark', 'approved' => 'bg-success', 'rejected' => 'bg-danger'];
@endphp
<div class="card radius-10 w-100">
	<div class="card-header">
		<div class="d-flex align-items-center">
			<h6 class="mb-0">My Leave Requests</h6>
			<a href="{{ route('staffleaveapp.index') }}" class="btn btn-sm btn-outline-primary ms-auto">All</a>
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
					<div class="font-13 text-secondary">{{ \Carbon\Carbon::parse($lv->from_date)->format('d-m-Y') }} - {{ \Carbon\Carbon::parse($lv->to_date)->format('d-m-Y') }} &middot; {{ rtrim(rtrim($lv->total_days, '0'), '.') }} day(s)</div>
				</div>
				<span class="badge {{ isset($leaveBadges[$lv->status]) ? $leaveBadges[$lv->status] : 'bg-secondary' }} rounded-pill">{{ ucfirst($lv->status) }}</span>
			</li>
			@endforeach
		</ul>
	@endif
</div>
