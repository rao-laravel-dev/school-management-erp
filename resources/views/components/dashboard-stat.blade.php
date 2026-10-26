{{-- Shared stat card for every role dashboard. Bottom element (sparkline or progress bar) is pinned to the card bottom (.dash-stat in app.css) --}}
@props(['title', 'value', 'icon', 'color' => 'primary', 'href' => null, 'hrefLabel' => null, 'sparkId' => null, 'percent' => null, 'percentLabel' => null])

<div class="col">
    <div class="card radius-10 h-100 mb-0 dash-stat">
        <div class="card-body">
            <div class="d-flex align-items-center dash-stat-top">
                <div class="dash-stat-text">
                    <p class="mb-0 text-secondary">{{ $title }}</p>
                    <h4 class="my-1">{{ $value }}</h4>
                    {{ $slot }}
                </div>
                <div class="widgets-icons bg-light-{{ $color }} text-{{ $color }} ms-auto"><i class='{{ $icon }}'></i></div>
            </div>
            @if($sparkId)
                <div class="dash-stat-bottom">
                    <div id="{{ $sparkId }}" class="dash-stat-spark"></div>
                </div>
            @elseif($percent !== null)
                <div class="dash-stat-bottom">
                    @if($percentLabel)
                        <p class="mb-1 font-13 text-secondary">{{ $percentLabel }}</p>
                    @endif
                    <div class="progress radius-10" style="height: 4.5px;">
                        <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ max(0, min(100, (float) $percent)) }}%"></div>
                    </div>
                </div>
            @endif
            @if($href)
                <a href="{{ $href }}" class="stretched-link" aria-label="{{ $hrefLabel ?: $title }}"></a>
            @endif
        </div>
    </div>
</div>
