@extends($current_layout)

@section('content')

<x-breadcrumb :items="[
        ['label' => 'Examination', 'url' => '#'],
        ['label' => 'Design Report Template', 'url' => route('report_card_template.index')],
    ]" />

<div class="row">

    {{-- LEFT: Add / Edit form --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h5 class="fw-semi-bold text-primary mb-0" id="formTitle">Add Report Card Template</h5>
            </div>
            <div class="card-body">
                <form id="reportCardTemplateForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="template_id">

                    <div class="mb-2">
                        <label class="form-label" for="template_name">Template Name <span class="text-danger">*</span></label>
                        <input type="text" name="template_name" id="template_name"
                               class="form-control form-control-sm" placeholder="e.g. Junior Report Card">
                        <div class="invalid-feedback" id="template_name_error"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="body_text">Body Text <span class="text-danger">*</span></label>
                        <textarea name="body_text" id="body_text" rows="3" class="form-control form-control-sm"
                                  placeholder="Use tags like [name] [father_name] [class] [section] [roll_number]"></textarea>
                        <div class="invalid-feedback" id="body_text_error"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="footer_text">Footer Text <span class="text-danger">*</span></label>
                        <textarea name="footer_text" id="footer_text" rows="2" class="form-control form-control-sm"
                                  placeholder="e.g. This is a computer generated report card and does not require a signature."></textarea>
                        <div class="invalid-feedback" id="footer_text_error"></div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label" for="header_image">Header Image</label>
                            <input type="file" name="header_image" id="header_image" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="header_image_error"></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="background_image">Background Image</label>
                            <input type="file" name="background_image" id="background_image" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="background_image_error"></div>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label" for="left_sign">Left Sign</label>
                            <input type="file" name="left_sign" id="left_sign" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="left_sign_error"></div>
                        </div>
                        <div class="col-4">
                            <label class="form-label" for="middle_sign">Middle Sign</label>
                            <input type="file" name="middle_sign" id="middle_sign" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="middle_sign_error"></div>
                        </div>
                        <div class="col-4">
                            <label class="form-label" for="right_sign">Right Sign</label>
                            <input type="file" name="right_sign" id="right_sign" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="right_sign_error"></div>
                        </div>
                    </div>

                    <hr>
                    <label class="form-label fw-semi-bold">Student Info Fields</label>
                    <div class="row mb-2">
                        @foreach([
                            'show_name' => 'Name', 'show_father_name' => 'Father Name',
                            'show_mother_name' => 'Mother Name', 'show_admission_no' => 'Admission No',
                            'show_roll_number' => 'Roll Number', 'show_photo' => 'Photo',
                            'show_class' => 'Class', 'show_section' => 'Section', 'show_dob' => 'Date Of Birth',
                        ] as $field => $label)
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="{{ $field }}" id="{{ $field }}">
                                    <label class="form-check-label" style="cursor:pointer;" for="{{ $field }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <hr>
                    <label class="form-label fw-semi-bold">Report Card Sections</label>
                    <div class="row mb-3">
                        @foreach([
                            'show_attendance_summary' => 'Attendance Summary',
                            'show_co_curricular' => 'Co-Curricular',
                            'show_behavioral' => 'Behavioral',
                            'show_class_teacher_remark' => 'Class Teacher Remark',
                        ] as $field => $label)
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="{{ $field }}" id="{{ $field }}">
                                    <label class="form-check-label" style="cursor:pointer;" for="{{ $field }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-end">
                        <button type="button" id="resetFormBtn" class="btn btn-danger btn-sm px-3">Reset</button>
                        <button type="submit" class="btn btn-success btn-sm px-3" id="submitBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- RIGHT: Sample templates list --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h5 class="fw-semi-bold text-primary mb-0">Sample Templates</h5>
            </div>
            <div class="card-body">
                <table id="reportCardTemplateTable" class="table table-sm table-bordered w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Template Name</th>
                            <th>Background</th>
                            @can('manage-report-card-template')
                                <th>Action</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report_card_templates as $i => $t)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $t->template_name }}</td>
                                <td>
                                    @if($t->background_image)
                                        <img src="{{ asset('uploads/report_card_template/' . $t->background_image) }}" width="40" height="30" class="rounded" style="border:1px solid #adb5bd; object-fit:cover;">
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                @can('manage-report-card-template')
                                    <td>
                                        <button type="button" class="btn btn-outline-primary btn-sm px-3 edit-btn" data-id="{{ $t->id }}">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm px-3 delete-btn" data-id="{{ $t->id }}">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </td>
                                @endcan
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
    $(function () {

        @if($errors->any())
            toastr.error("{{ $errors->first() }}");
        @endif

        $('#reportCardTemplateTable').DataTable({
            @can('manage-report-card-template')
            columnDefs: [{ orderable: false, targets: -1 }],
            @endcan
            language: {
                emptyTable: '<div class="alert alert-danger text-center"><i class="bx bx-error-circle"></i> No data available in the table</div>'
            }
        });

        const toggleFields = [
            'show_name', 'show_father_name', 'show_mother_name',
            'show_admission_no', 'show_roll_number', 'show_photo',
            'show_class', 'show_section', 'show_dob',
            'show_attendance_summary', 'show_co_curricular',
            'show_behavioral', 'show_class_teacher_remark'
        ];

        function clearErrors() {
            $('#reportCardTemplateForm').find('.is-invalid').removeClass('is-invalid');
            $('#reportCardTemplateForm').find('.invalid-feedback').text('');
        }

        function resetForm() {
            $('#reportCardTemplateForm')[0].reset();
            $('#template_id').val('');
            $('#formTitle').text('Add Report Card Template');
            $('#submitBtn').text('Save');
            clearErrors();
        }

        $('#resetFormBtn').on('click', resetForm);

        // ---------- SAVE / UPDATE (same form, mode decided by whether id is set) ----------
        $('#reportCardTemplateForm').on('submit', function (e) {
            e.preventDefault();
            clearErrors();

            const id = $('#template_id').val();
            const formData = new FormData(this);
            toggleFields.forEach(f => formData.set(f, $(`#${f}`).is(':checked') ? 1 : 0));

            let url = "{{ route('report_card_template.save') }}";
            if (id) {
                url = `{{ url('report-card-template') }}/${id}`;
                formData.append('_method', 'PUT'); // file upload -> POST + spoofed method
            }

            const btn = $('#submitBtn').prop('disabled', true);

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (res) {
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 800);
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function (key, messages) {
                            $(`#${key}`).addClass('is-invalid');
                            $(`#${key}_error`).text(messages[0]);
                        });
                        toastr.error(Object.values(xhr.responseJSON.errors)[0][0]);
                    } else {
                        console.error(xhr);
                        toastr.error('Something went wrong.');
                    }
                },
                complete: function () { btn.prop('disabled', false); }
            });
        });

        // ---------- EDIT: populate same form ----------
        $(document).on('click', '.edit-btn', function () {
            const id = $(this).data('id');
            clearErrors();

            $.get(`{{ url('report-card-template') }}/${id}/edit`, function (data) {
                $('#template_id').val(data.id);
                $('#template_name').val(data.template_name);
                $('#body_text').val(data.body_text);
                $('#footer_text').val(data.footer_text);

                toggleFields.forEach(f => $(`#${f}`).prop('checked', !!data[f]));

                $('#formTitle').text('Edit Report Card Template');
                $('#submitBtn').text('Update');

                $('html, body').animate({ scrollTop: 0 }, 300);
            }).fail(function () {
                toastr.error('Unable to load template.');
            });
        });

        // ---------- DELETE ----------
        $(document).on('click', '.delete-btn', function () {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: 'This template will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('report-card-template') }}/${id}`,
                        method: 'DELETE',
                        dataType: 'json',
                        success: function (res) {
                            toastr.success(res.message);
                            setTimeout(() => location.reload(), 800);
                        },
                        error: function () {
                            toastr.error('Unable to delete template.');
                        }
                    });
                }
            });
        });

    });
</script>
@endpush