@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => $dashboardUrl],
    ['label' => 'Assignments', 'url' => '#'],
    ['label' => $homeworkTitle, 'url' => ''],
]" />

@isset($activeChild)
    @include('parent.partials.child_switcher')
@endisset

<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center hw-header">
        <h6 class="mb-0 text-uppercase">{{ $homeworkTitle }}</h6>
        <div class="d-flex gap-2">
            <span class="badge bg-success">Pending: {{ $pendingCount }}</span>
            <span class="badge bg-danger">Past Due: {{ $pastDueCount }}</span>
        </div>
    </div>
    <div class="card-body">
        @if($homeworks->isEmpty())
            <div class="alert alert-info mb-0">No homework assigned yet.</div>
        @else
        <div class="hw-filters d-flex flex-wrap gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-primary hw-filter" data-filter="all">All</button>
            <button type="button" class="btn btn-sm btn-outline-success hw-filter" data-filter="upcoming">Upcoming</button>
            <button type="button" class="btn btn-sm btn-outline-warning hw-filter" data-filter="today">Due Today</button>
            <button type="button" class="btn btn-sm btn-outline-danger hw-filter" data-filter="past">Past Due</button>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3" id="hwGrid">
            @foreach($homeworks as $hw)
            @php
                $dueDay = $hw->due_date->copy()->startOfDay();
                $today = now()->startOfDay();
                if ($dueDay->lt($today)) {
                    $status = 'past';
                } elseif ($dueDay->eq($today)) {
                    $status = 'today';
                } else {
                    $status = 'upcoming';
                }
            @endphp
            <div class="col hw-item" data-status="{{ $status }}">
                <div class="card h-100 hw-card hw-{{ $status }} mb-0">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div class="fw-bold hw-subject">{{ $hw->subject->name ?? '-' }}</div>
                            @if($status === 'past')
                                <span class="badge bg-danger">Past Due</span>
                            @elseif($status === 'today')
                                <span class="badge bg-warning text-dark">Due Today</span>
                            @else
                                <span class="badge bg-success">Upcoming</span>
                            @endif
                        </div>
                        <div class="hw-title">{{ $hw->title }}</div>
                        @if($hw->description)
                            <div class="small hw-desc" style="white-space: pre-line;">{{ $hw->description }}</div>
                        @endif
                        <div class="small hw-meta mt-2">
                            <div><i class='bx bx-calendar'></i> Assigned: {{ $hw->homework_date->format('d M, Y') }}</div>
                            <div><i class='bx bx-time-five'></i> Due: {{ $hw->due_date->format('d M, Y') }}</div>
                            @if($hw->createdBy)
                                <div><i class='bx bx-user'></i> {{ $hw->createdBy->name }}</div>
                            @endif
                        </div>
                        @if($hw->attachment)
                            <div class="mt-auto pt-3">
                                <a href="{{ $hw->attachment_url }}" target="_blank" class="btn btn-outline-primary btn-sm"><i class='bx bx-paperclip'></i> View Attachment</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="alert alert-info mb-0 mt-3 d-none" id="hwNoMatch">No homework in this filter.</div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).on('click', '.hw-filter', function() {
        let filter = $(this).data('filter');

        $('.hw-filter').each(function() {
            // active button = solid, others = outline (same colour as the status)
            let cls = $(this).attr('class').replace(/btn-(outline-)?(primary|success|warning|danger)/, '');
            let colour = {all: 'primary', upcoming: 'success', today: 'warning', past: 'danger'}[$(this).data('filter')];
            $(this).attr('class', cls + ' ' + ($(this).data('filter') === filter ? 'btn-' + colour : 'btn-outline-' + colour));
        });

        let shown = 0;
        $('.hw-item').each(function() {
            let match = filter === 'all' || $(this).data('status') === filter;
            $(this).toggleClass('d-none', !match);
            if (match) shown++;
        });
        $('#hwNoMatch').toggleClass('d-none', shown > 0);
    });
</script>
@endpush
