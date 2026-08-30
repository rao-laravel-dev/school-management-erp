@extends('parent.layout.app')

@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
    <div class="breadcrumb-title pe-3">Dashboard</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item active" aria-current="page">Select Student to View Dashboard</li>
            </ol>
        </nav>
    </div>
</div>

<style>
    /* Desktop: Grid Layout */
    .student-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        padding: 10px;
        justify-content: flex-start;
    }
    .student-card-wrapper {
        flex: 0 0 calc(25% - 20px); /* 4 cards per row */
        max-width: calc(25% - 20px);
    }

    /* Tablet: 2 or 3 cards per row */
    @media (max-width: 1024px) {
        .student-card-wrapper { flex: 0 0 calc(33.33% - 20px); max-width: calc(33.33% - 20px); }
    }

    /* Mobile: Single Card Slider */
    @media (max-width: 768px) {
        .student-container {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            padding: 10px 5px;
            gap: 10px;
            scrollbar-width: none;
        }
        .student-container::-webkit-scrollbar { display: none; }
        
        .student-card-wrapper {
            flex: 0 0 100%; /* 95% width so the next card is hidden */
            max-width: 100%;
            scroll-snap-align: center;
        }
    }
</style>

<div class="student-container">
    @foreach($children as $child)
        @php
            // Logic ensure kar rahi hai ke har variable defined ho
            $enrollment = $child->currentEnrollment;
            $num = $enrollment->schoolClass->numeric_name ?? 0;
            $className = $enrollment->schoolClass->name ?? 'N/A';
            $sectionName = $enrollment->section->name ?? 'N/A';
            
            $btnClass = 'btn-primary';
            if ($num == 11) { $btnClass = 'btn-success'; } 
            elseif ($num == 22) { $btnClass = 'btn-info'; } 
            elseif ($num == 33) { $btnClass = 'btn-warning'; }
            elseif ($num == 44) { $btnClass = 'btn-danger'; }
            elseif ($num == 55) { $btnClass = 'btn-secondary'; }
            elseif ($num >= 1 && $num <= 5) { $btnClass = 'btn-primary'; }
            elseif ($num >= 6 && $num <= 8) { $btnClass = 'btn-dark'; }
            elseif ($num >= 9 && $num <= 10) { $btnClass = 'btn-outline-primary'; }
        @endphp

        <div class="student-card-wrapper">
            {{-- View Profile Button --}}
            <div style="margin-bottom: 10px;">
                <a href="{{ route('parent.students.dashboard', $child->id) }}" 
                   class="btn {{ $btnClass }} shadow-sm text-white" 
                   style="display: flex; align-items: center; border-radius: 15px; padding: 12px; font-weight: 500; width: 100%;">
                    <i class='bx bx-user' style="margin-right: 8px;"></i>
                    <span style="flex-grow: 1; text-align: center;">View Profile</span>
                    <i class='bx bx-right-arrow-alt' style="margin-left: 8px;"></i>
                </a>
            </div>

            {{-- Main Card --}}
            <div class="card radius-15 shadow-sm">
                <div class="card-body text-center p-4">
                    <img src="{{ $child->photo_url ?? asset('assets/images/default-avatar.png') }}" 
                         width="110" height="110" class="rounded-circle shadow border border-3 border-white" alt="Student Photo">
                    
                    <h5 class="mt-4 mb-0 font-weight-bold">{{ $child->full_name }}</h5>
                    <p class="text-secondary mb-3">
                        Class: {{ $className }} | Section: {{ $sectionName }}
                    </p>

                    <div class="d-grid">
                        <a href="{{ route('parent.students.dashboard', $child->id) }}" 
                           class="btn {{ $btnClass }} text-white" style="border-radius: 50px;">
                            <i class='bx bxs-dashboard'></i> View Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

{{-- --- DASHBOARD CHARTS INJECTED HERE SAFELY --- --}}
@push('scripts')
<script src="{{ asset('backend/assets/plugins/chartjs/js/chart.js') }}"></script>
<script src="{{ asset('backend/assets/js/dashboard-charts.js') }}"></script> {{-- Naya Naam --}}
@endpush