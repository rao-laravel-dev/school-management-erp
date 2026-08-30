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

                    <div class="mb-3">
                        <label for="design_type" class="form-label">Design Type <span class="text-danger">*</span></label>
                        <select name="design_type" id="design_type" class="form-select form-select-sm">
                            <option value="horizontal">Horizontal</option>
                            <option value="vertical">Vertical</option>
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
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-secondary viewBtn"
    data-img="{{ $template->design_type === 'vertical'
        ? asset('uploads/id-card-images/staff-id-card-template-vertical.jpg')
        : asset('uploads/id-card-images/staff-id-card-template-horizontal.jpg') }}">
    <i class="bx bx-show"></i>
</button>
                                <button class="btn btn-sm btn-outline-primary editBtn" data-id="{{ $template->id }}"><i class="bx bx-edit"></i></button>
                                <a href="{{ route('staff_id_card_template.delete', $template->id) }}" class="btn btn-sm btn-outline-danger deleteBtn"><i class="bx bx-trash"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">
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
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">ID Card Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <img id="previewImg" src="" class="img-fluid rounded">
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
        $('#design_type').val(t.design_type);

        $('.toggle-field').each(function() {
            const name = $(this).attr('name');
            $(this).prop('checked', !!t[name]);
        });

        $('html, body').animate({ scrollTop: 0 }, 300);
    });

    $('#cancelEdit').on('click', function() {
        $('#templateForm')[0].reset();
        $('#form-title').text('Add Staff ID Card Template');
        $('#form_method').val('POST');
        $('#templateForm').attr('action', "{{ route('staff_id_card_template.save') }}");
        $('#submitBtn').text('Save');
        $(this).hide();
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

    $('.viewBtn').on('click', function() {
        $('#previewImg').attr('src', $(this).data('img'));
        new bootstrap.Modal(document.getElementById('cardPreviewModal')).show();
    });
});
</script>
@endpush