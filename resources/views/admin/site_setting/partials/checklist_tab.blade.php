{{-- T340b: Setup checklist tab. Data comes from SetupChecklistService::summary() ($checklist); required steps drive the 3 boxes (same numbers as the dashboard card). --}}
@php
    $remaining = $checklist['total'] - $checklist['done'];
    $boxes = [
        ['Tasks', $checklist['total'], 'bx bx-task'],
        ['Completed', $checklist['done'], 'bx bx-check-circle'],
        ['Remaining', $remaining, 'bx bx-time-five'],
    ];
    $recDone = count(array_filter($checklist['recommended'], fn($s) => $s['done']));
    $groups = [
        ['Required steps', $checklist['required'], $checklist['done'] . ' of ' . $checklist['total'] . ' done'],
        ['Recommended (optional)', $checklist['recommended'], $recDone . ' of ' . count($checklist['recommended']) . ' done'],
    ];
@endphp

<div class="row g-3 mb-3">
    @foreach($boxes as [$label, $value, $icon])
    <div class="col-md-4">
        <div class="border rounded px-3 py-3 text-center ss-tile">
            <div class="fw-bold fs-3 lh-1">{{ $value }}</div>
            <small class="text-muted"><i class="{{ $icon }}"></i> {{ $label }}</small>
        </div>
    </div>
    @endforeach
</div>

<div class="card">
    <div class="card-body">
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $checklist['percent'] }}%;" aria-valuenow="{{ $checklist['percent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        @if($checklist['complete'])
        <div class="alert alert-success py-2 mt-3 mb-0">All required setup steps are done.</div>
        @endif
    </div>
</div>

@foreach($groups as [$title, $steps, $note])
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="fw-semi-bold text-primary mb-0">{{ $title }}</h5>
        <span class="text-secondary small">{{ $note }}</span>
    </div>
    <ul class="list-group list-group-flush">
        @foreach($steps as $step)
        <li class="list-group-item d-flex justify-content-between align-items-center gap-3">
            <span>
                @if($step['done'])
                <i class="bx bxs-check-circle align-middle text-success"></i>
                @else
                <i class="bx bx-circle align-middle text-secondary"></i>
                @endif
                {{ $step['label'] }}
            </span>
            <span class="d-flex align-items-center gap-2">
                @if($step['done'])
                <span class="badge bg-success">Done</span>
                @else
                <span class="badge bg-warning text-dark">Pending</span>
                @if($step['url'])
                <a href="{{ $step['url'] }}" class="btn btn-sm btn-outline-primary">Open</a>
                @endif
                @endif
            </span>
        </li>
        @endforeach
    </ul>
</div>
@endforeach
