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
                        <input class="form-check-input" type="checkbox" name="{{ $field }}" id="{{ $field }}_toggle">
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

    {{-- RIGHT: Saved Templates List --}}
    <div class="col-md-7">
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
            'show_class', 'show_section', 'show_dob', 'show_remark'
        ];

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
            toggleFields.forEach(f => $(`#${f}_toggle`).prop('checked', false));
            $('[id$="_preview"]').html('');
            clearErrors();
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