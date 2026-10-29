{{-- Basic verify card: school + name + class/staff id + valid badge (no photo/phone/address/parent) --}}
<div class="text-center mb-3">
    @if($school->logo)
        <img src="{{ asset('uploads/site_setting/' . $school->logo) }}" alt="" style="max-height: 70px;" class="mb-2">
    @endif
    <h5 class="fw-bold mb-0">{{ $school->school_name }}</h5>
</div>

@if(!$card)
    <div class="alert alert-danger text-center mb-0">Card not found.</div>
@else
    <div class="text-center mb-3">
        @if($card['valid'])
            <span class="badge bg-success fs-6">Valid card</span>
        @else
            <span class="badge bg-danger fs-6">Inactive card</span>
        @endif
    </div>
    <ul class="list-group">
        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Name</span><strong>{{ $card['name'] }}</strong></li>
        @if($card['type'] === 'student')
            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Class-Section</span><strong>{{ $card['class'] }}</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Admission No</span><strong>{{ $card['id_no'] }}</strong></li>
        @else
            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Staff ID</span><strong>{{ $card['id_no'] }}</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Role</span><strong>{{ $card['role'] }}</strong></li>
        @endif
    </ul>
@endif
