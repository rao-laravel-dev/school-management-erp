{{--
    Shared fields for Add/Edit modal.
    $p = id prefix ('add' ya 'edit_{id}'), $room = Room|null, $f = kya yeh wahi form hai jo validation fail hua
--}}
@php
    $val = fn ($key) => $f ? old($key) : ($room ? $room->{$key} : '');
    // Server error: sirf usi modal mein jo submit hua tha (Add/Edit mix na hon)
    $inv = fn ($key) => $f && $errors->has($key) ? 'is-invalid' : '';
    $msg = fn ($key) => $f ? $errors->first($key) : '';
    $selClass = $val('school_class_id');
    $selSection = $val('section_id');
    $selType = $val('type');
    $selFloor = $val('floor');
    $selStatus = $f ? old('status') : ($room ? (int) $room->status : 1);
@endphp
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="{{ $p }}_room_no" class="small fw-bold">Room No. *</label>
        <input type="text" id="{{ $p }}_room_no" name="room_no" data-label="Room No." data-required="1" placeholder="e.g. 101 or R-12"
            class="form-control form-control-sm {{ $inv('room_no') }}" value="{{ $val('room_no') }}">
        <div class="invalid-feedback">{{ $msg('room_no') }}</div>
    </div>
    <div class="col-md-6 mb-3">
        <label for="{{ $p }}_name" class="small fw-bold">Name</label>
        <input type="text" id="{{ $p }}_name" name="name" placeholder="e.g. Science Lab, Room A" class="form-control form-control-sm {{ $inv('name') }}" value="{{ $val('name') }}">
        <div class="invalid-feedback">{{ $msg('name') }}</div>
    </div>
    <div class="col-md-6 mb-3">
        <label for="{{ $p }}_building" class="small fw-bold">Building</label>
        <input type="text" id="{{ $p }}_building" name="building" placeholder="e.g. Main Block, Block B" class="form-control form-control-sm {{ $inv('building') }}" value="{{ $val('building') }}">
        <div class="invalid-feedback">{{ $msg('building') }}</div>
    </div>
    <div class="col-md-6 mb-3">
        <label for="{{ $p }}_floor" class="small fw-bold">Floor</label>
        <select id="{{ $p }}_floor" name="floor" class="form-select form-select-sm {{ $inv('floor') }}">
            <option value="">Select Floor</option>
            {{-- Purana free-text floor jo list mein nahi, wo bhi option bane taake update par data na khoye --}}
            @if($selFloor !== null && $selFloor !== '' && !in_array($selFloor, $floors, true))
            <option value="{{ $selFloor }}" selected>{{ $selFloor }}</option>
            @endif
            @foreach($floors as $floor)
            <option value="{{ $floor }}" {{ (string) $selFloor === (string) $floor ? 'selected' : '' }}>{{ $floor }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback">{{ $msg('floor') }}</div>
    </div>
    <div class="col-md-6 mb-3">
        <label for="{{ $p }}_capacity" class="small fw-bold">Capacity</label>
        <input type="number" min="1" id="{{ $p }}_capacity" name="capacity" placeholder="e.g. 40 (number of students)" class="form-control form-control-sm {{ $inv('capacity') }}" value="{{ $val('capacity') }}">
        <div class="invalid-feedback">{{ $msg('capacity') }}</div>
    </div>
    <div class="col-md-6 mb-3">
        <label for="{{ $p }}_type" class="small fw-bold">Type</label>
        <select id="{{ $p }}_type" name="type" class="form-select form-select-sm {{ $inv('type') }}">
            <option value="">Select Type</option>
            @foreach($types as $key => $label)
            <option value="{{ $key }}" {{ (string) $selType === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback">{{ $msg('type') }}</div>
    </div>
    <div class="col-md-6 mb-3">
        <label for="{{ $p }}_school_class_id" class="small fw-bold">Assigned Class <span class="text-muted fw-normal">(optional)</span></label>
        <select id="{{ $p }}_school_class_id" name="school_class_id" data-label="Class"
            class="form-select form-select-sm room-class {{ $inv('school_class_id') }}">
            <option value="">Select Class (optional)</option>
            @foreach($classes as $class)
            <option value="{{ $class->id }}" {{ (string) $selClass === (string) $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback">{{ $msg('school_class_id') }}</div>
    </div>
    <div class="col-md-6 mb-3">
        <label for="{{ $p }}_section_id" class="small fw-bold">Assigned Section</label>
        <select id="{{ $p }}_section_id" name="section_id" data-label="Section" data-selected="{{ $selSection }}"
            class="form-select form-select-sm room-section {{ $inv('section_id') }}">
            <option value="">{{ $selClass ? 'Select Section' : 'Select Class first' }}</option>
        </select>
        <div class="invalid-feedback">{{ $msg('section_id') }}</div>
    </div>
    <div class="col-md-6 mb-3">
        <label for="{{ $p }}_status" class="small fw-bold">Status</label>
        <select id="{{ $p }}_status" name="status" class="form-select form-select-sm {{ $inv('status') }}">
            <option value="1" {{ (string) $selStatus === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ (string) $selStatus === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
        <div class="invalid-feedback">{{ $msg('status') }}</div>
    </div>
</div>
