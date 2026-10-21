{{-- Shared attendance stat card. Params: label, value, color (bootstrap name), optional id (JS counter) --}}
<div class="card radius-10 border-start border-0 border-3 border-{{ $color }} shadow-sm att-stat-card">
    <div class="card-body">
        <span class="att-stat-label">{{ $label }}</span>
        <h4 class="att-stat-value text-{{ $color }}"@if(!empty($id)) id="{{ $id }}"@endif>{{ $value }}</h4>
    </div>
</div>
