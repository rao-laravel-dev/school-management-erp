@extends($current_layout)
@section('title', 'Issue Certificate')
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Certificates', 'url' => '#'],
    ['label' => 'Issue Certificate', 'url' => route('certificate.create')],
]" />

@if($missingHead)
<div class="alert alert-warning py-2 small">
    Principal signature or head name is not set, so the certificate will print without it.
    @can('access-site-settings')
    <a href="{{ route('site_setting.edit') }}">Open Site Settings</a>
    @endcan
</div>
@endif

<div class="row">
    {{-- Left: form --}}
    <div class="col-lg-5">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0 text-primary">Issue Certificate</h5>
            </div>
            <div class="card-body">
                <form id="certificateForm" method="POST" action="{{ route('certificate.store') }}" novalidate autocomplete="off">
                    @csrf
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="certificateType" class="small fw-bold">Certificate Type *</label>
                            <select id="certificateType" name="type" data-label="Certificate Type" data-required="1"
                                class="form-select form-select-sm @error('type') is-invalid @enderror">
                                <option value="">Select type</option>
                                @foreach($types as $key => $t)
                                <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $t['title'] }}</option>
                                @endforeach
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            {{-- old: <div id="certificateSlcNote" class="small text-warning mt-1">Printing only: the student's status does not change yet.</div> --}}
                            <div id="certificateSlcNote" class="small mt-1" style="display:none;">
                                <div class="text-warning">Issuing this certificate marks an active student as School Left (login blocked).</div>
                                <div id="certificateSlcBox" class="mt-1"></div>
                            </div>
                            <input type="hidden" name="confirm_left" id="certificateConfirmLeft" value="0">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="certificateSession" class="small fw-bold">Session *</label>
                            {{-- session / class / section are posted only so the selection survives a failed save (the server ignores them) --}}
                            <select id="certificateSession" name="session_id" data-label="Session" data-required="1" class="form-select form-select-sm">
                                @foreach($sessions as $s)
                                <option value="{{ $s->id }}" {{ (old('session_id') ? old('session_id') == $s->id : $s->is_current) ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="certificateClass" class="small fw-bold">Class *</label>
                            <select id="certificateClass" name="class_id" data-label="Class" data-required="1" class="form-select form-select-sm">
                                <option value="">Select class</option>
                                @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="certificateSection" class="small fw-bold">Section *</label>
                            <select id="certificateSection" name="section_id" data-label="Section" data-required="1" class="form-select form-select-sm">
                                <option value="">Select section</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="certificateStudent" class="small fw-bold">Student *</label>
                            <select id="certificateStudent" name="enrollment_id" data-label="Student" data-required="1"
                                class="form-select form-select-sm @error('enrollment_id') is-invalid @enderror">
                                <option value="">Select student</option>
                            </select>
                            @error('enrollment_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="certificateIssueDate" class="small fw-bold">Issue Date *</label>
                            <input type="date" id="certificateIssueDate" name="issue_date" data-label="Issue Date" data-required="1"
                                class="form-control form-control-sm @error('issue_date') is-invalid @enderror" value="{{ old('issue_date', now()->toDateString()) }}">
                            @error('issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="certificateOrientation" class="small fw-bold">Orientation *</label>
                            <select id="certificateOrientation" name="orientation" data-label="Orientation" data-required="1"
                                class="form-select form-select-sm @error('orientation') is-invalid @enderror">
                                @foreach($orientations as $k => $label)
                                <option value="{{ $k }}" {{ old('orientation', 'portrait') === $k ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('orientation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="certificateDesign" class="small fw-bold">Design *</label>
                            <select id="certificateDesign" name="design" data-label="Design" data-required="1"
                                class="form-select form-select-sm @error('design') is-invalid @enderror">
                                @foreach($designs as $k => $label)
                                <option value="{{ $k }}" {{ old('design', 'classic') === $k ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('design')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Per-certificate fields: only the chosen type's fields are shown (hidden ones are disabled, not posted) --}}
                        @foreach($fieldMeta as $key => $meta)
                        <div class="col-md-6 mb-3 certificate-field" data-field="{{ $key }}" style="display:none;">
                            <label for="certificateField_{{ $key }}" class="small fw-bold">{{ $meta[0] }} <span class="certificate-field-star"></span></label>
                            <input type="{{ $meta[1] }}" id="certificateField_{{ $key }}" name="{{ $key }}" data-label="{{ $meta[0] }}" disabled
                                @if($meta[2] > 0) maxlength="{{ $meta[2] }}" @endif
                                @if($key === 'conduct') list="certificateConductList" @endif
                                class="form-control form-control-sm @error($key) is-invalid @enderror" value="{{ old($key) }}">
                            @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @endforeach
                        <datalist id="certificateConductList">
                            @foreach($conductOptions as $o)<option value="{{ $o }}">@endforeach
                        </datalist>

                        <div class="col-12 mb-3">
                            <label for="certificateTitle" class="small fw-bold">Title *</label>
                            <input type="text" id="certificateTitle" name="title" maxlength="150" data-label="Title" data-required="1"
                                class="form-control form-control-sm @error('title') is-invalid @enderror" value="{{ old('title') }}">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label for="certificateBody" class="small fw-bold">Wording *</label>
                            <textarea id="certificateBody" name="body" rows="9" maxlength="5000" data-label="Wording" data-required="1"
                                class="form-control form-control-sm @error('body') is-invalid @enderror">{{ old('body') }}</textarea>
                            @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Edits here apply to this certificate only.</small>
                            <details class="small mt-1">
                                <summary class="text-primary" style="cursor:pointer;">Placeholders you can use</summary>
                                <div class="text-muted mt-1">
                                    [student_name] [father_name] [mother_name] [gr_no] [dob] [dob_words] [admission_date] [class] [section] [roll_no] [session]
                                    [son_daughter] [his_her] [he_she] [him_her] [His_Her] [He_She] [school_name] [issue_date] [certificate_no]
                                    [leaving_date] [reason] [conduct] [exam] [result] [purpose] [event] [position] [remarks]
                                </div>
                            </details>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success btn-sm px-4">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Right: live preview (the real print page for an unsaved certificate) --}}
    <div class="col-lg-7">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0 text-primary">Preview</h5>
                <small class="text-muted">Sample student until a student is selected</small>
            </div>
            <div class="card-body p-2">
                <iframe id="certificatePreview" title="Certificate preview" style="width:100%; height:520px; border:0; background:#eee;"></iframe>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const CERT_TYPES = @json($typesJs);
    const SECTIONS_URL = "{{ route('certificate.get_sections', ':id') }}";
    const STUDENTS_URL = "{{ route('certificate.get_students') }}";
    const PREVIEW_URL = "{{ route('certificate.live_preview') }}";
    const SLC_URL = "{{ route('certificate.slc_check') }}";
    const OLD_ENROLLMENT = @json(old('enrollment_id'));
    const OLD_SECTION = @json(old('section_id'));
    const OLD_TYPE = @json(old('type'));
    const $form = $('#certificateForm');

    // ---- type change: title, wording and the type's fields ----
    function applyType(keepText) {
        const key = $('#certificateType').val();
        const t = CERT_TYPES[key];
        $('#certificateSlcNote').toggle(key === 'school_leaving');
        $('.certificate-field').each(function() {
            const $box = $(this);
            const f = $box.data('field');
            const show = !!t && t.fields.indexOf(f) !== -1;
            const req = show && t.required.indexOf(f) !== -1;
            $box.toggle(show);
            const $in = $box.find('input').prop('disabled', !show).attr('data-required', req ? '1' : '0');
            $box.find('.certificate-field-star').text(req ? '*' : '(optional)');
            if (!show) { $in.val(''); }
        });
        if (t && !keepText) {
            $('#certificateTitle').val(t.title);
            $('#certificateBody').val(t.body);
        }
    }

    // ---- class -> sections -> students ----
    let secToken = 0, stuToken = 0;
    function loadSections(selected) {
        const classId = $('#certificateClass').val();
        const $sec = $('#certificateSection').html('<option value="">Select section</option>');
        $('#certificateStudent').html('<option value="">Select student</option>');
        if (!classId) { return; }
        const token = ++secToken;
        $.get(SECTIONS_URL.replace(':id', classId)).done(function(rows) {
            if (token !== secToken) { return; }
            rows.forEach(function(r) { $sec.append($('<option>').val(r.id).text(r.name)); });
            if (selected) { $sec.val(selected); loadStudents(OLD_ENROLLMENT); }
        }).fail(function() { toastr.error('Could not load sections.'); });
    }
    function loadStudents(selected) {
        const $stu = $('#certificateStudent').html('<option value="">Select student</option>');
        const q = { session_id: $('#certificateSession').val(), class_id: $('#certificateClass').val(), section_id: $('#certificateSection').val() };
        if (!q.session_id || !q.class_id || !q.section_id) { return; }
        const token = ++stuToken;
        $.get(STUDENTS_URL, q).done(function(rows) {
            if (token !== stuToken) { return; }
            rows.forEach(function(r) { $stu.append($('<option>').val(r.id).text(r.text)); });
            if (selected) { $stu.val(String(selected)); }
            checkSlc();
            refreshPreview();
        }).fail(function() { toastr.error('Could not load students.'); });
    }
    $('#certificateClass').on('change', function() { loadSections(); });
    $('#certificateSection, #certificateSession').on('change', function() { loadStudents(); });

    // ---- live preview: server renders the real print page for an unsaved certificate ----
    let previewToken = 0, previewTimer = null;
    function refreshPreview() {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(function() {
            if (!$('#certificateType').val()) { $('#certificatePreview')[0].srcdoc = ''; return; }
            const token = ++previewToken;
            const payload = {};
            $form.serializeArray().forEach(function(f) { if (f.name !== '_token') { payload[f.name] = f.value; } });
            const frame = $('#certificatePreview')[0];
            $.post(PREVIEW_URL, payload).done(function(html) {
                if (token !== previewToken) { return; }
                frame.onload = function() {
                    const doc = frame.contentDocument;
                    const page = doc.querySelector('.cert-page');
                    if (!page) { return; }
                    const zoom = Math.min(1, frame.clientWidth / (page.offsetWidth + 40));
                    doc.body.style.zoom = zoom;
                    frame.style.height = Math.ceil(page.offsetHeight * zoom + 50) + 'px';
                };
                frame.srcdoc = html;
            }).fail(function() { /* silent: validation of the form shows the real errors on Save */ });
        }, 350);
    }
    // ---- SLC: student School Left hoga? unpaid fees? (warning only, server decides again on save) ----
    let slcState = null, slcToken = 0;
    function checkSlc() {
        slcState = null;
        $('#certificateConfirmLeft').val('0');
        const $box = $('#certificateSlcBox').empty();
        const enrollmentId = $('#certificateStudent').val();
        if ($('#certificateType').val() !== 'school_leaving' || !enrollmentId) { return; }
        const token = ++slcToken;
        $.get(SLC_URL, { enrollment_id: enrollmentId }).done(function(res) {
            if (token !== slcToken || !res.success) { return; }
            slcState = res;
            if (res.already_left) {
                // old: $('<div class="text-secondary">')
                $box.append($('<div class="text-danger fw-bold">').css('font-size', '1rem').text('Already School Left: the status will not change.'));
            } else if (!res.will_mark_left) {
                $box.append($('<div class="text-secondary">').text('This student is not active: the status will not change.'));
            } else {
                $box.append($('<div class="text-danger fw-bold">').css('font-size', '1rem').text('This student will be marked School Left.'));
            }
            if (res.unpaid_total > 0) {
                $box.append($('<div class="text-warning">').text('Rs. ' + res.unpaid_label + ' unpaid in ' + res.unpaid_count + ' fee row(s) (warning only).'));
            }
        }).fail(function() { toastr.error('Could not check the student status.'); });
    }
    $('#certificateStudent').on('change', checkSlc);

    $('#certificateType').on('change', function() { applyType(false); checkSlc(); refreshPreview(); });
    $form.on('input change', 'input, select, textarea', refreshPreview);

    // ---- submit check (UX only, the server validates again) ----
    function setFieldError($el, msg) {
        $el.addClass('is-invalid');
        $el.next('.invalid-feedback').remove();
        $('<div class="invalid-feedback">').text(msg).insertAfter($el);
    }
    $form.on('submit', function(e) {
        const missing = [];
        $form.find('[data-required="1"]:enabled').each(function() {
            if (!$.trim($(this).val())) { missing.push({ $el: $(this), msg: $(this).data('label') + ' is required' }); }
        });
        if (missing.length) {
            e.preventDefault();
            missing.forEach(function(m) { setFieldError(m.$el, m.msg); });
            toastr.clear();
            toastr.error(missing[0].msg);
            missing[0].$el.trigger('focus');
            return;
        }

        // SLC: active student School Left hone se pehle confirm (server bhi confirm_left check karta hai)
        if ($('#certificateType').val() === 'school_leaving' && slcState && slcState.will_mark_left && $('#certificateConfirmLeft').val() !== '1') {
            e.preventDefault();
            const name = $('<div>').text($('#certificateStudent option:selected').text()).html();
            let html = '<b>' + name + '</b><br>This student will be marked School Left. Their login will be blocked.';
            if (slcState.unpaid_total > 0) {
                html += '<br><span class="text-warning">Warning: Rs. ' + $('<div>').text(slcState.unpaid_label).html() + ' unpaid fees.</span>';
            }
            Swal.fire({
                title: 'Issue School Leaving Certificate?',
                html: html,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, issue SLC',
                cancelButtonText: 'Cancel',
                buttonsStyling: false,
                customClass: { popup: 'fee-assignment-swal', confirmButton: 'btn btn-danger btn-sm', cancelButton: 'btn btn-secondary btn-sm' }
            }).then(function(result) {
                if (!result.isConfirmed) { return; }
                $('#certificateConfirmLeft').val('1');
                $form[0].submit(); // native submit: yeh handler dobara nahi chalta
            });
        }
    });
    $form.on('input change', '.is-invalid', function() { $(this).removeClass('is-invalid').next('.invalid-feedback').remove(); });

    // ---- server errors / flash ----
    @if($errors->any())
    toastr.error(@json($errors->first()));
    @endif
    @if(Session::has('error')) toastr.error(@json(Session::get('error'))); @endif
    @if(Session::has('success')) toastr.success(@json(Session::get('success'))); @endif

    // Failed save: put the type fields back (typed values stay, text is not overwritten)
    if (OLD_TYPE) { applyType(true); }
    if ($('#certificateClass').val()) { loadSections(OLD_SECTION); }
    refreshPreview();
});
</script>
@endpush
