{{-- Parent child pages: which child is open + Switch Student (stays on the same page type) --}}
<div class="card mt-3 child-switcher">
    <div class="card-body d-flex align-items-center gap-3 flex-wrap py-2">
        <img src="{{ $activeChild->photo_url ?? asset('assets/images/avatars/avatar-1.png') }}" class="rounded-circle border" width="44" height="44" style="object-fit: cover;" alt="Student Photo">
        <div class="child-switcher-info">
            <div class="fw-bold child-switcher-name">{{ $activeChild->full_name }}</div>
            <div class="small text-secondary child-switcher-class">
                @if($activeEnrollment)
                    Class: {{ $activeEnrollment->schoolClass->name ?? 'N/A' }} | Section: {{ $activeEnrollment->section->name ?? 'N/A' }}
                @else
                    No active class
                @endif
            </div>
        </div>
        <div class="ms-auto d-flex align-items-center gap-2 child-switcher-actions">
            @if($siblings->count() > 1)
            <div class="dropdown">
                <button class="btn btn-outline-primary btn-sm dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-refresh"></i> Switch Student
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    <li><h6 class="dropdown-header text-secondary">Select Child</h6></li>
                    @foreach($siblings as $sibling)
                        @if($sibling->id != $activeChild->id)
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route($switchRoute, $sibling->id) }}">
                                <img src="{{ $sibling->photo_url ?? asset('assets/images/avatars/avatar-1.png') }}" class="rounded-circle border" width="25" height="25" style="object-fit: cover;" alt="">
                                <span>{{ $sibling->full_name }}</span>
                            </a>
                        </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            @endif
            <a href="{{ route('parent.dashboard') }}" class="btn btn-primary btn-sm px-3"><i class="bx bx-arrow-back"></i> Back to Kids</a>
        </div>
    </div>
</div>
