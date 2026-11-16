@extends($current_layout)

@section('content')
<x-breadcrumb :items="[
    ['label' => 'Examinations', 'url' => '#'],
    ['label' => 'Staff ID Card Template', 'url' => route('staff_id_card_template.index')],
]" />

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h5 class="fw-semi-bold text-primary mb-0" id="form-title">Add Staff ID Card Template</h5>
            </div>
            <div class="card-body">
                <form id="templateForm" action="{{ route('staff_id_card_template.save') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="form_method" value="POST">
                    <input type="hidden" id="template_id" value="">

                    <div class="mb-2">
                        <label for="title" class="form-label">Template Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" placeholder="e.g. Sample Staff ID Card"
                               class="form-control form-control-sm">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Background Image</label>
                        <input type="file" name="background_image" accept="image/*" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Logo</label>
                        <input type="file" name="logo" accept="image/*" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Signature</label>
                        <input type="file" name="signature" accept="image/*" class="form-control form-control-sm">
                    </div>

                    <div class="mb-2">
                        <label for="school_name" class="form-label">School Name <span class="text-danger">*</span></label>
                        <input type="text" name="school_name" id="school_name" placeholder="e.g. Smart School"
                               class="form-control form-control-sm">
                    </div>

                    <div class="mb-2">
                        <label for="address_phone_email" class="form-label">Address / Phone / Email</label>
                        <input type="text" name="address_phone_email" id="address_phone_email"
                               placeholder="e.g. 110 Kings Street, CA | 456542 | mount@gmail.com"
                               class="form-control form-control-sm">
                    </div>

                    <div class="mb-2">
                        <label for="header_color" class="form-label">Header Color</label>
                        <input type="color" name="header_color" id="header_color" value="#4b3bdb"
                               class="form-control form-control-sm form-control-color">
                    </div>

                    {{-- T338: second brand colour + 8 one-click colour pairs (Main / Accent) --}}
                    <div class="mb-2">
                        <label for="accent_color" class="form-label">Accent Color</label>
                        <input type="color" name="accent_color" id="accent_color" value="#ffb020"
                               class="form-control form-control-sm form-control-color">
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Colour Presets</label>
                        <div id="colorPresets" class="d-flex flex-wrap gap-2"></div>
                    </div>

                    <div class="mb-3">
                        <label for="design_type" class="form-label">Design Type <span class="text-danger">*</span></label>
                        <select name="design_type" id="design_type" class="form-select form-select-sm">
                            <option value="horizontal">Horizontal</option>
                            <option value="vertical">Vertical</option>
                        </select>
                    </div>

                    {{-- Card Design: all 3 shown; designs this user lacks are disabled (not hidden) so an existing template's design stays visible --}}
                    <div class="mb-3">
                        <label for="design" class="form-label">Card Design <span class="text-danger">*</span></label>
                        <select name="design" id="design" class="form-select form-select-sm">
                            @foreach([1, 2, 3] as $d)
                            <option value="{{ $d }}" @cannot('access-id-card-design-' . $d) disabled @endcannot>Design {{ $d }}</option>
                            @endforeach
                        </select>
                    </div>

                    <hr>
                    <p class="fw-semi-bold mb-2">Fields to Show on Card</p>
                    <div class="row">
                        @php
                            $fields = [
                                'show_staff_name' => 'Staff Name',
                                'show_staff_id' => 'Staff ID',
                                'show_designation' => 'Designation',
                                'show_department' => 'Department',
                                'show_father_name' => "Father Name",
                                'show_mother_name' => "Mother Name",
                                'show_date_of_joining' => 'Date Of Joining',
                                'show_current_address' => 'Current Address',
                                'show_phone' => 'Phone',
                                'show_dob' => 'Date Of Birth',
                                'show_qr_code' => 'QR Code',
                                'show_barcode' => 'Barcode',
                            ];
                        @endphp
                        @foreach($fields as $key => $label)
                            <div class="col-6 mb-2 form-check form-switch">
                                <input class="form-check-input toggle-field" type="checkbox" name="{{ $key }}" id="{{ $key }}" value="1">
                                <label class="form-check-label" for="{{ $key }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 text-end">
                        <button type="button" class="btn btn-sm btn-danger" id="cancelEdit" style="display:none;">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success px-4" id="submitBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        {{-- T338: live preview of the real card (front + back) with sample data; nothing is saved --}}
        <div class="card mb-3">
            <div class="card-header bg-transparent">
                <h5 class="fw-semi-bold text-primary mb-0">Live Preview</h5>
            </div>
            <div class="card-body p-2">
                <iframe id="cardPreview" style="width:100%; height:260px; border:0;" title="Live Preview"></iframe>
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <h5 class="fw-semi-bold text-primary mb-0">Staff ID Card Template List</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm" id="templateTable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Background</th>
                            <th>Design Type</th>
                            <th>Design</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                        <tr>
                            <td>{{ $template->title }}</td>
                            <td>
                                @if($template->background_image)
                                    <img src="{{ asset('uploads/staff_id_card_template/' . $template->background_image) }}" style="height:35px;">
                                @endif
                            </td>
                            <td>{{ ucfirst($template->design_type) }}</td>
                            <td>Design {{ $template->design }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-secondary viewBtn"
    data-id="{{ $template->id }}"
    data-img="{{ $template->design_type === 'vertical'
        ? asset('uploads/id-card-images/staff-id-card-template-vertical.jpg')
        : asset('uploads/id-card-images/staff-id-card-template-horizontal.jpg') }}">
    <i class="bx bx-show"></i>
</button>
                                @can('access-id-card-design-' . $template->design)
                                <button class="btn btn-sm btn-outline-primary editBtn" data-id="{{ $template->id }}"><i class="bx bx-edit"></i></button>
                                <a href="{{ route('staff_id_card_template.delete', $template->id) }}" class="btn btn-sm btn-outline-danger deleteBtn"><i class="bx bx-trash"></i></a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">
                                <div class="alert alert-danger d-flex align-items-center gap-2 mb-0">
                                    <i class="bx bx-error-circle"></i> No templates found.
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cardPreviewModal" tabindex="-1">
  {{-- old: <div class="modal-dialog modal-dialog-centered"> (T338: modal-lg so the front + back pair is readable) --}}
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">ID Card Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        {{-- old: <img id="previewImg" src="" class="img-fluid rounded"> (fixed screenshot) --}}
        <iframe id="previewFrame" class="rounded" style="width:100%; height:260px; border:0;" title="ID Card Preview"></iframe>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    @if($errors->any())
        toastr.error("{{ $errors->first() }}", 'Validation Error');
    @endif
    @if(session('toastr-success'))
        toastr.success("{{ session('toastr-success') }}");
    @endif

    const templates = @json($templates->keyBy('id'));

    $('.editBtn').on('click', function() {
        const id = $(this).data('id');
        const t = templates[id];

        $('#form-title').text('Edit Staff ID Card Template');
        $('#template_id').val(id);
        $('#form_method').val('PUT');
        $('#templateForm').attr('action', '/staff-id-card-template/update/' + id);
        $('#submitBtn').text('Update');
        $('#cancelEdit').show();

        $('#title').val(t.title);
        $('#school_name').val(t.school_name);
        $('#address_phone_email').val(t.address_phone_email);
        $('#header_color').val(t.header_color ?? '#4b3bdb');
        $('#accent_color').val(t.accent_color ?? '#ffb020');
        $('#design_type').val(t.design_type);
        $('#design').val(t.design);

        $('.toggle-field').each(function() {
            const name = $(this).attr('name');
            $(this).prop('checked', !!t[name]);
        });

        refreshPreview();
        $('html, body').animate({ scrollTop: 0 }, 300);
    });

    $('#cancelEdit').on('click', function() {
        $('#templateForm')[0].reset();
        $('#form-title').text('Add Staff ID Card Template');
        $('#form_method').val('POST');
        $('#templateForm').attr('action', "{{ route('staff_id_card_template.save') }}");
        $('#submitBtn').text('Save');
        $(this).hide();
        $('#template_id').val('');
        setTimeout(refreshPreview, 0); // after the form reset has applied
    });

    $('.deleteBtn').on('click', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        Swal.fire({
            title: 'Are you sure?',
            text: "This template will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });

    // old: $('.viewBtn') copied data-img (fixed screenshot) into #previewImg
    // T338: eye button shows that template's real card (saved values -> same livePreview route)
    $('.viewBtn').on('click', function() {
        const t = templates[$(this).data('id')];
        const payload = {
            _token: $('#templateForm [name=_token]').val(), template_id: t.id,
            school_name: t.school_name, address_phone_email: t.address_phone_email,
            header_color: t.header_color, accent_color: t.accent_color, design_type: t.design_type, design: t.design
        };
        $('.toggle-field').each(function() { const n = $(this).attr('name'); if (t[n]) payload[n] = 1; });
        $('#previewFrame').removeAttr('srcdoc');
        new bootstrap.Modal(document.getElementById('cardPreviewModal')).show();
        renderCard('#previewFrame', payload);
    });

    // ---- T338: colour presets (Main / Accent), JS only ----
    const PRESETS = [
        ['Indigo Amber', '#4b3bdb', '#ffb020'], ['Royal Gold', '#1e3a8a', '#f5b700'],
        ['Emerald Lime', '#047857', '#a3e635'], ['Maroon Gold', '#7f1d1d', '#d4a017'],
        ['Navy Sky', '#0f172a', '#38bdf8'], ['Teal Coral', '#0f766e', '#fb7185'],
        ['Purple Pink', '#6d28d9', '#f472b6'], ['Sunflower Charcoal', '#facc15', '#1f2937']
    ];
    PRESETS.forEach(function(p) {
        $('<button type="button" class="btn p-0 border" style="width:34px;height:34px;overflow:hidden;"></button>')
            .attr('title', p[0])
            .css('background', 'linear-gradient(135deg, ' + p[1] + ' 50%, ' + p[2] + ' 50%)')
            .on('click', function() {
                $('#header_color').val(p[1]);
                $('#accent_color').val(p[2]).trigger('change');
            })
            .appendTo('#colorPresets');
    });

    // ---- T338: live preview = the real card view rendered by the server with sample data ----
    function renderCard(frameSel, payload) {
        const frame = $(frameSel)[0];
        $.post("{{ route('staff_id_card_template.live_preview') }}", payload).done(function(html) {
            frame.onload = function() {
                const doc = frame.contentDocument;
                const pair = doc.querySelector('.card-pair');
                if (!pair) return;
                const zoom = Math.min(1, frame.clientWidth / (pair.offsetWidth + 60));
                doc.body.style.zoom = zoom;
                frame.style.height = Math.ceil(pair.offsetHeight * zoom + 70) + 'px';
            };
            frame.srcdoc = html;
        });
    }

    let previewTimer = null;
    function refreshPreview() {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(function() {
            const payload = {};
            $('#templateForm').serializeArray().forEach(function(f) { if (f.name !== '_method') payload[f.name] = f.value; });
            payload.template_id = $('#template_id').val();
            renderCard('#cardPreview', payload);
        }, 350);
    }
    $('#templateForm').on('input change', 'input:not([type=file]), select', refreshPreview);
    refreshPreview();
});
</script>
@endpush