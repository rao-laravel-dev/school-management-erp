@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Examination', 'url' => '#'],
    ['label' => 'Design Marksheet', 'url' => route('marksheet_template.index')],
]" />

<div class="row">
    {{-- LEFT: Form --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 fw-semi-bold text-primary" id="formTitle">Add Marksheet Template</h5>
            </div>
            <div class="card-body">
                <form id="marksheetTemplateForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="template_id" id="template_id">

                    <div class="mb-2">
                        <label class="form-label" for="template_name">Template Name <span class="text-danger">*</span></label>
                        <input type="text" name="template_name" id="template_name" class="form-control form-control-sm" placeholder="e.g. Monthly Test - August 2026">
                        <div class="invalid-feedback" data-field="template_name"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="exam_name">Exam Name <span class="text-danger">*</span></label>
                        <input type="text" name="exam_name" id="exam_name" class="form-control form-control-sm" placeholder="e.g. Monthly Test (August-2026)">
                        <div class="invalid-feedback" data-field="exam_name"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="school_name">School Name <span class="text-danger">*</span></label>
                        <input type="text" name="school_name" id="school_name" class="form-control form-control-sm" placeholder="e.g. Mount Carmel School">
                        <div class="invalid-feedback" data-field="school_name"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="exam_center">Exam Center <span class="text-danger">*</span></label>
                        <input type="text" name="exam_center" id="exam_center" class="form-control form-control-sm" placeholder="e.g. Main Campus">
                        <div class="invalid-feedback" data-field="exam_center"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="body_text">Body Text <span class="text-danger">*</span></label>
                        <textarea name="body_text" id="body_text" rows="3" class="form-control form-control-sm" placeholder="e.g. This is to certify that [name], S/D/O [father_name]...">{{ '' }}</textarea>
                        <div class="invalid-feedback" data-field="body_text"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="footer_text">Footer Text <span class="text-danger">*</span></label>
                        <textarea name="footer_text" id="footer_text" rows="2" class="form-control form-control-sm" placeholder="e.g. This is a computer-generated marksheet.">{{ '' }}</textarea>
                        <div class="invalid-feedback" data-field="footer_text"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="printing_date">Printing Date</label>
                        <input type="date" name="printing_date" id="printing_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
                        <div class="invalid-feedback" data-field="printing_date"></div>
                    </div>

                    {{-- Image uploads --}}
                    @foreach([
                    'header_image' => 'Header Image',
                    'left_logo' => 'Left Logo',
                    'right_logo' => 'Right Logo',
                    'left_sign' => 'Left Sign',
                    'middle_sign' => 'Middle Sign',
                    'right_sign' => 'Right Sign',
                    'background_image' => 'Background Image',
                    ] as $field => $label)
                    <div class="mb-2">
                        <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                        <input type="file" name="{{ $field }}" id="{{ $field }}" accept="image/*" class="form-control form-control-sm">
                        <div class="invalid-feedback" data-field="{{ $field }}"></div>
                        <div id="{{ $field }}_preview" class="mt-1"></div>
                    </div>
                    @endforeach

                    <hr>
                    <h6 class="fw-semi-bold text-primary">Design &amp; Branding</h6>

                    <div class="mb-2">
                        <label class="form-label d-block">Design</label>
                        @foreach($designs as $key => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="design" id="design_{{ $key }}" value="{{ $key }}" {{ $key === 'classic' ? 'checked' : '' }}>
                            <label class="form-check-label" for="design_{{ $key }}">{{ $label }}</label>
                        </div>
                        @endforeach
                        <div class="invalid-feedback" data-field="design"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="orientation">Orientation</label>
                        <select name="orientation" id="orientation" class="form-select form-select-sm">
                            @foreach($orientations as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" data-field="orientation"></div>
                    </div>

                    <div class="mb-2">
                        <div class="form-check form-switch mb-1">
                            <input class="form-check-input" type="checkbox" id="use_theme_color" checked>
                            <label class="form-check-label" for="use_theme_color">Use school theme colour</label>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small" for="primary_color">Main colour</label>
                                <input type="color" name="primary_color" id="primary_color" class="form-control form-control-color w-100" value="{{ $themePrimary }}" disabled>
                                <div class="invalid-feedback" data-field="primary_color"></div>
                            </div>
                            <div class="col-6">
                                <label class="form-label small" for="accent_color">Accent colour</label>
                                <input type="color" name="accent_color" id="accent_color" class="form-control form-control-color w-100" value="{{ $themeAccent }}" disabled>
                                <div class="invalid-feedback" data-field="accent_color"></div>
                            </div>
                        </div>
                        <small class="text-muted">Colours apply to the Modern and Formal designs. Classic keeps its black and white look.</small>
                    </div>

                    @foreach([
                    'show_school_logo' => 'School logo (from Site Settings)',
                    'show_head_signature' => 'Principal signature, name and title (from Site Settings)',
                    'show_stamp' => 'School stamp (from Site Settings)',
                    ] as $field => $label)
                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" name="{{ $field }}" id="{{ $field }}_toggle" checked>
                        <label class="form-check-label" for="{{ $field }}_toggle">{{ $label }}</label>
                    </div>
                    @endforeach

                    @if($missingBranding)
                    <div class="alert alert-warning small py-2 mt-2 mb-0">
                        Principal signature or head name is not set in Site Settings, so that part prints blank.
                        @can('access-site-settings')
                        <a href="{{ route('site_setting.edit') }}">Open Site Settings</a>
                        @endcan
                    </div>
                    @endif

                    <hr>
                    <h6 class="fw-semi-bold text-primary">Extra Sections (Modern and Formal)</h6>
                    @foreach([
                    'show_subject_details' => 'Subject table: Pass Marks, %, Grade, Pass/Fail',
                    'show_summary_boxes' => 'Summary boxes: Percentage, Grade, Result, Position',
                    'show_grade_scale' => 'Grade scale table',
                    'show_parent_sign' => 'Parent / Guardian signature',
                    ] as $field => $label)
                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" name="{{ $field }}" id="{{ $field }}_toggle" checked>
                        <label class="form-check-label" for="{{ $field }}_toggle">{{ $label }}</label>
                    </div>
                    @endforeach
                    <small class="text-muted">The Classic design does not use these.</small>

                    <hr>
                    <h6 class="fw-semi-bold text-primary">Fields to Show</h6>

                    @foreach([
                    'show_name' => 'Name',
                    'show_father_name' => 'Father Name',
                    'show_mother_name' => 'Mother Name',
                    'show_exam_session' => 'Exam Session',
                    'show_admission_no' => 'Admission No',
                    'show_division' => 'Division',
                    'show_rank' => 'Rank',
                    'show_roll_number' => 'Roll Number',
                    'show_photo' => 'Photo',
                    'show_class' => 'Class',
                    'show_section' => 'Section',
                    'show_dob' => 'Date Of Birth',
                    'show_remark' => 'Remark',
                    ] as $field => $label)
                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" name="{{ $field }}" id="{{ $field }}_toggle" checked>
                        <label class="form-check-label" for="{{ $field }}_toggle">{{ $label }}</label>
                    </div>
                    @endforeach

                    <div class="mt-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-danger btn-sm d-none" id="cancelEditBtn">Cancel</button>
                        <button type="submit" class="btn btn-success btn-sm" id="saveBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- RIGHT: Live preview (sample student, updates while you change the form) --}}
    <div class="col-md-7">
        <div class="card" style="position: sticky; top: 70px; z-index: 2;">
            <div class="card-header">
                <h5 class="mb-0 fw-semi-bold text-primary">Live Preview</h5>
            </div>
            <div class="card-body">
                <div id="marksheetPreviewWrap" style="overflow: hidden; background: #eee; border-radius: 4px;">
                    <iframe id="marksheetPreview" title="Marksheet preview" style="width: 794px; height: 1123px; border: 0; transform-origin: top left;"></iframe>
                </div>
                <small class="text-muted d-block mt-1">Sample student and marks. A picture you just picked shows here before saving, except the background image (it shows after Save).</small>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    {{-- Saved Templates List --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 fw-semi-bold text-primary">Marksheet Templates</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-sm" id="templatesTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>Template Name</th>
                            <th>Background Image</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($templates as $template)
                        <tr id="row_{{ $template->id }}">
                            <td>{{ $template->template_name }}</td>
                            <td>
                                @if($template->background_image)
                                <img src="{{ asset('uploads/marksheet_template/' . $template->background_image) }}" width="40" height="40" class="rounded">
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-primary btn-sm px-3 edit-btn" data-id="{{ $template->id }}">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm px-3 delete-btn" data-id="{{ $template->id }}">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {

        // ===== Show toastr for server-side errors =====
        @if($errors->any())
        toastr.error("{{ $errors->first() }}");
        @endif

        // ===== DataTable with custom empty text/alert box =====
        let table = $('#templatesTable').DataTable({
            @can('manage-marksheet-templates')
            columnDefs: [{
                orderable: false,
                targets: 2
            }],
            @endcan
            language: {
                emptyTable: `<div class="alert alert-danger d-flex align-items-center justify-content-center text-center mb-0" role="alert"><i class='bx bx-error-circle me-2'></i><span>No data available in the table</span></div>`
            }
        });

        const toggleFields = ['show_name', 'show_father_name', 'show_mother_name', 'show_exam_session',
            'show_admission_no', 'show_division', 'show_rank', 'show_roll_number', 'show_photo',
            'show_class', 'show_section', 'show_dob', 'show_remark',
            'show_school_logo', 'show_head_signature', 'show_stamp',
            'show_subject_details', 'show_summary_boxes', 'show_grade_scale', 'show_parent_sign'
        ];
        const brandingToggles = ['show_school_logo', 'show_head_signature', 'show_stamp'];
        const imageFields = ['header_image', 'left_logo', 'right_logo', 'left_sign', 'middle_sign', 'right_sign', 'background_image'];
        const THEME_PRIMARY = @json($themePrimary);
        const THEME_ACCENT = @json($themeAccent);
        const PREVIEW_URL = "{{ route('marksheet_template.live_preview') }}";

        // ===== Colours: "school theme colour" switch disables the two pickers (disabled = not sent = null in DB) =====
        function syncColors() {
            const useTheme = $('#use_theme_color').is(':checked');
            $('#primary_color, #accent_color').prop('disabled', useTheme);
            if (useTheme) {
                $('#primary_color').val(THEME_PRIMARY);
                $('#accent_color').val(THEME_ACCENT);
            }
        }
        $('#use_theme_color').on('change', syncColors);
        syncColors();

        // ===== Live preview: form values -> real print page with sample data, shown scaled inside the iframe =====
        let previewTimer = null;
        let previewToken = 0;
        const pendingUrls = {};

        function fitPreview() {
            const land = $('#orientation').val() === 'landscape';
            const frameW = land ? 1123 : 794;
            const frameH = land ? 794 : 1123;
            const wrap = $('#marksheetPreviewWrap');
            // Poora A4 page dikhe: card width AUR screen height dono mein fit (aspect ratio same), beech mein
            const maxH = Math.max(300, window.innerHeight - 160);
            const scale = Math.min(1, wrap.width() / frameW, maxH / frameH);
            $('#marksheetPreview').css({
                width: frameW + 'px',
                height: frameH + 'px',
                transform: 'scale(' + scale + ')',
                marginLeft: Math.max(0, (wrap.width() - frameW * scale) / 2) + 'px'
            });
            wrap.css('height', (frameH * scale) + 'px');
        }

        function applyPendingImages(frame) {
            const doc = frame.contentDocument;
            if (!doc) return;
            imageFields.forEach(function(f) {
                if (pendingUrls[f]) {
                    $(doc).find('img[data-preview-img="' + f + '"]').attr('src', pendingUrls[f]);
                }
            });
        }

        function loadPreview() {
            const token = ++previewToken;
            const data = $('#marksheetTemplateForm').serializeArray();
            imageFields.forEach(function(f) {
                if (pendingUrls[f]) data.push({ name: 'pending_images[]', value: f });
            });

            $.ajax({
                url: PREVIEW_URL,
                method: 'POST',
                data: $.param(data),
                dataType: 'html'
            }).done(function(html) {
                if (token !== previewToken) return; // purana reply ignore
                const frame = $('#marksheetPreview')[0];
                frame.onload = function() { applyPendingImages(frame); };
                fitPreview();
                frame.srcdoc = html;
            }).fail(function(xhr) {
                if (token !== previewToken) return;
                let msg = 'Preview could not be loaded.';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors)[0][0];
                }
                toastr.clear();
                toastr.error(msg);
            });
        }

        function schedulePreview() {
            clearTimeout(previewTimer);
            previewTimer = setTimeout(loadPreview, 350);
        }

        // Image chuni to browser mein hi dikhao (save se pehle)
        imageFields.forEach(function(f) {
            $('#' + f).on('change', function() {
                if (pendingUrls[f]) {
                    URL.revokeObjectURL(pendingUrls[f]);
                    delete pendingUrls[f];
                }
                if (this.files && this.files.length) {
                    pendingUrls[f] = URL.createObjectURL(this.files[0]);
                }
            });
        });

        $('#marksheetTemplateForm').on('input change', schedulePreview);
        $('#orientation').on('change', fitPreview); // orientation badle to page box turant naye size par
        $(window).on('resize', fitPreview);
        fitPreview();
        schedulePreview();

        function clearErrors() {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
        }

        function resetForm() {
            $('#marksheetTemplateForm')[0].reset();
            $('#template_id').val('');
            $('#formTitle').text('Add Marksheet Template');
            $('#saveBtn').text('Save');
            $('#cancelEditBtn').addClass('d-none');
            // old: sirf branding switches on, baqi off. Ab naye template mein saare Fields to Show bhi on
            toggleFields.forEach(f => $(`#${f}_toggle`).prop('checked', true));
            $('[id$="_preview"]').html('');
            Object.keys(pendingUrls).forEach(function(k) { URL.revokeObjectURL(pendingUrls[k]); delete pendingUrls[k]; });
            $('#use_theme_color').prop('checked', true);
            syncColors();
            clearErrors();
            schedulePreview();
        }

        // ===== Save / Update =====
        $('#marksheetTemplateForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors();

            let id = $('#template_id').val();
            let formData = new FormData(this);

            let url = id ?
                "{{ url('marksheet-template') }}/" + id + "/update" :
                "{{ route('marksheet_template.save') }}";

            if (id) formData.append('_method', 'PUT');

            $('#saveBtn').prop('disabled', true);

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 800);
                },
                error: function(xhr) {
                    console.error('Marksheet template save error:', xhr.status, xhr.responseJSON || xhr.responseText);

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        let errors = xhr.responseJSON.errors;
                        let firstMessage = null;

                        $.each(errors, function(key, msgs) {
                            $(`[name="${key}"]`).addClass('is-invalid');
                            $(`.invalid-feedback[data-field="${key}"]`).text(msgs[0]);
                            if (!firstMessage) firstMessage = msgs[0];
                        });

                        toastr.error(firstMessage || 'Please fill all required fields correctly!');
                    } else if (xhr.status === 403) {
                        toastr.error(xhr.responseJSON?.message || 'You are not authorized to perform this action.');
                    } else {
                        toastr.error(xhr.responseJSON?.message || 'Something went wrong! (status ' + xhr.status + ')');
                    }
                },
                complete: function() {
                    $('#saveBtn').prop('disabled', false);
                }
            });
        });

        // ===== Edit =====
        $(document).on('click', '.edit-btn', function() {
            let id = $(this).data('id');
            clearErrors();

            $.get("{{ url('marksheet-template') }}/" + id + "/edit", function(data) {
                $('#template_id').val(data.id);
                $('#template_name').val(data.template_name);
                $('#exam_name').val(data.exam_name);
                $('#school_name').val(data.school_name);
                $('#exam_center').val(data.exam_center);
                $('#body_text').val(data.body_text);
                $('#footer_text').val(data.footer_text);
                $('#printing_date').val(data.printing_date ? data.printing_date.split('T')[0] : '');

                toggleFields.forEach(f => $(`#${f}_toggle`).prop('checked', !!data[f]));

                $(`input[name="design"][value="${data.design || 'classic'}"]`).prop('checked', true);
                $('#orientation').val(data.orientation || 'portrait');
                $('#use_theme_color').prop('checked', !data.primary_color && !data.accent_color);
                syncColors();
                if (data.primary_color) $('#primary_color').val(data.primary_color);
                if (data.accent_color) $('#accent_color').val(data.accent_color);
                Object.keys(pendingUrls).forEach(function(k) { URL.revokeObjectURL(pendingUrls[k]); delete pendingUrls[k]; });
                imageFields.forEach(function(f) { $('#' + f).val(''); });
                schedulePreview();

                $('#formTitle').text('Edit Marksheet Template');
                $('#saveBtn').text('Update');
                $('#cancelEditBtn').removeClass('d-none');
                $('html, body').animate({
                    scrollTop: 0
                }, 300);
            }).fail(function(xhr) {
                toastr.error('Could not load template. (status ' + xhr.status + ')');
            });
        });

        $('#cancelEditBtn').on('click', resetForm);

        $('#cancelEditBtn').on('click', resetForm);

        // ===== Delete (SweetAlert2) =====
        $(document).on('click', '.delete-btn', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: 'This template will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('marksheet-template') }}/" + id + "/delete",
                        method: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        dataType: 'json',
                        success: function(res) {
                            toastr.success(res.message);
                            setTimeout(() => location.reload(), 800);
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'Something went wrong!');
                        }
                    });
                }
            });
        });

    });
</script>
@endpush