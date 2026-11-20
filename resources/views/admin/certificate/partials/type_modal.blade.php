{{-- Shared fields for Add/Edit type modal. $p = id prefix ('add' ya 'edit_{key}'), $t = type array or null (add), $f = is this the form that failed --}}
@php
    $isCustom = !$t || $t['custom'];
    $val = fn ($n, $d = '') => $f ? old($n, $d) : $d;
    $inv = fn ($n) => ($f && $errors->has($n)) ? 'is-invalid' : '';
    $msg = fn ($n) => $f ? $errors->first($n) : '';
    $selFields = $f ? (array) old('fields', []) : ($t ? $t['fields'] : []);
    $reqFields = $f ? (array) old('required', []) : ($t ? $t['required'] : []);
    $isActive = $f ? (bool) old('active', 1) : ($t ? $t['active'] : true);
@endphp

<div class="row">
    <div class="col-md-8 mb-3">
        <label for="{{ $p }}_title" class="small fw-bold">Title *</label>
        <input type="text" id="{{ $p }}_title" name="title" maxlength="150" data-label="Title" data-required="1"
            placeholder="e.g. Migration Certificate" class="form-control form-control-sm {{ $inv('title') }}" value="{{ $val('title', $t ? $t['title'] : '') }}">
        <div class="invalid-feedback">{{ $msg('title') }}</div>
    </div>

    <div class="col-md-4 mb-3">
        <label for="{{ $p }}_prefix" class="small fw-bold">Prefix {{ !$t ? '*' : '' }}</label>
        @if(!$t)
        <input type="text" id="{{ $p }}_prefix" name="prefix" maxlength="5" data-label="Prefix" data-required="1"
            placeholder="e.g. MIG" style="text-transform: uppercase;" class="form-control form-control-sm {{ $inv('prefix') }}" value="{{ $val('prefix') }}">
        <div class="invalid-feedback">{{ $msg('prefix') }}</div>
        <small class="text-muted">2 to 5 capital letters, cannot be changed later</small>
        @else
        {{-- Issued numbers keep their prefix, so it is shown only --}}
        {{-- <input type="text" id="{{ $p }}_prefix" class="form-control form-control-sm" value="{{ $t['prefix'] }}" readonly> --}}
        <input type="text" id="{{ $p }}_prefix" class="form-control form-control-sm bg-light fw-bold" value="{{ $t['prefix'] }}" readonly>
        @endif
    </div>

    <div class="col-12 mb-3">
        <label for="{{ $p }}_body" class="small fw-bold">Default Wording *</label>
        <textarea id="{{ $p }}_body" name="body" rows="7" maxlength="5000" data-label="Wording" data-required="1"
            class="form-control form-control-sm {{ $inv('body') }}">{{ $val('body', $t ? $t['body'] : '') }}</textarea>
        <div class="invalid-feedback">{{ $msg('body') }}</div>
        <details class="small mt-1">
            <summary class="text-primary" style="cursor:pointer;">Placeholders you can use</summary>
            <div class="text-muted mt-1">
                [student_name] [father_name] [mother_name] [gr_no] [dob] [dob_words] [admission_date] [class] [section] [roll_no] [session]
                [son_daughter] [his_her] [he_she] [him_her] [His_Her] [He_She] [school_name] [issue_date] [certificate_no]
                @if($isCustom) (and the fields you tick below, e.g. [reason]) @else [leaving_date] [reason] [conduct] [exam] [result] [purpose] [event] [position] [remarks] @endif
            </div>
        </details>
    </div>

    {{-- Fields: custom types choose them, built-in types have a fixed set --}}
    <div class="col-12 mb-3">
        <label class="small fw-bold">Fields asked when issuing</label>
        @if($isCustom)
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0 certificate-type-fields">
                <thead class="table-light">
                    <tr><th>Field</th><th class="text-center">Show</th><th class="text-center">Required</th></tr>
                </thead>
                <tbody>
                    @foreach($fieldMeta as $fk => $meta)
                    <tr>
                        <td><label for="{{ $p }}_f_{{ $fk }}" class="mb-0">{{ $meta[0] }} <span class="text-muted">[{{ $fk }}]</span></label></td>
                        <td class="text-center"><input type="checkbox" class="form-check-input certificate-type-show" id="{{ $p }}_f_{{ $fk }}" name="fields[]" value="{{ $fk }}" {{ in_array($fk, $selFields, true) ? 'checked' : '' }}></td>
                        <td class="text-center"><input type="checkbox" class="form-check-input certificate-type-req" id="{{ $p }}_r_{{ $fk }}" name="required[]" value="{{ $fk }}" aria-label="{{ $meta[0] }} required" {{ in_array($fk, $reqFields, true) ? 'checked' : '' }}></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div>
            @forelse($t['fields'] as $fk)
            <span class="badge bg-secondary">{{ $fieldMeta[$fk][0] }}{{ in_array($fk, $t['required'], true) ? ' *' : '' }}</span>
            @empty
            <span class="text-muted small">None</span>
            @endforelse
        </div>
        <small class="text-muted">Built-in types keep their fields; only the title, wording and status can change.</small>
        @endif
    </div>

    <div class="col-12 mb-1">
        <input type="hidden" name="active" value="0">
        <div class="form-check form-switch">
            <input class="form-check-input {{ $inv('active') }}" type="checkbox" role="switch" id="{{ $p }}_active" name="active" value="1" {{ $isActive ? 'checked' : '' }}>
            <label class="form-check-label small fw-bold" for="{{ $p }}_active">Active (available on the Issue page)</label>
        </div>
        @if($msg('active'))<div class="text-danger small">{{ $msg('active') }}</div>@endif
        <small class="text-muted">Inactive types cannot be issued; certificates already issued are not affected.</small>
    </div>
</div>
