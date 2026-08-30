<!-- Add Modal -->
<div class="modal fade" id="addAreaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addAreaForm">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Add Skill Assessment Area</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="add_category_id" class="form-control form-control-sm">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="add_category_id_error"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="add_name" class="form-control form-control-sm" placeholder="e.g. Discipline, Sports">
                        <div class="invalid-feedback" id="add_name_error"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label>Status</label>
                        <select name="status" id="add_status" class="form-control form-control-sm">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success btn-sm" id="addSubmitBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editAreaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editAreaForm">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Edit Skill Assessment Area</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_area_id" name="area_id">

                    <div class="form-group mb-3">
                        <label>Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="edit_category_id" class="form-control form-control-sm">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="edit_category_id_error"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control form-control-sm">
                        <div class="invalid-feedback" id="edit_name_error"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label>Status</label>
                        <select name="status" id="edit_status" class="form-control form-control-sm">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning btn-sm" id="editSubmitBtn">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function resetAddForm() {
    $('#addAreaForm')[0].reset();
    $('#addAreaForm .invalid-feedback').text('');
    $('#addAreaForm .is-invalid').removeClass('is-invalid');
}

$('#addAreaForm').on('submit', function (e) {
    e.preventDefault();
    $('#addAreaForm .invalid-feedback').text('');
    $('#addAreaForm .is-invalid').removeClass('is-invalid');
    $('#addSubmitBtn').prop('disabled', true);

    $.ajax({
        url: "{{ route('skill_assessment_areas.store') }}",
        method: 'POST',
        dataType: 'json',
        data: $(this).serialize(),
        success: function (res) {
            if (res.success) {
                toastr.success(res.message);
                $('#addAreaModal').modal('hide');
                setTimeout(() => location.reload(), 800);
            } else {
                toastr.error(res.message);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                let first = Object.values(errors)[0][0];
                toastr.error(first);
                $.each(errors, function (field, messages) {
                    $('#add_' + field).addClass('is-invalid');
                    $('#add_' + field + '_error').text(messages[0]);
                });
            } else {
                toastr.error('Something went wrong.');
                console.error(xhr);
            }
        },
        complete: function () {
            $('#addSubmitBtn').prop('disabled', false);
        }
    });
});

function editArea(id) {
    $('#editAreaForm .invalid-feedback').text('');
    $('#editAreaForm .is-invalid').removeClass('is-invalid');

    $.get("{{ url('skill-assessment-areas') }}/" + id, function (data) {
        $('#edit_area_id').val(data.id);
        $('#edit_category_id').val(data.category_id);
        $('#edit_name').val(data.name);
        $('#edit_status').val(data.status ? 1 : 0);
        $('#editAreaModal').modal('show');
    }).fail(function () {
        toastr.error('Unable to load area.');
    });
}

$('#editAreaForm').on('submit', function (e) {
    e.preventDefault();
    $('#editAreaForm .invalid-feedback').text('');
    $('#editAreaForm .is-invalid').removeClass('is-invalid');

    let id = $('#edit_area_id').val();
    $('#editSubmitBtn').prop('disabled', true);

    $.ajax({
        url: "{{ url('skill-assessment-areas') }}/" + id,
        method: 'PUT',
        dataType: 'json',
        data: $(this).serialize(),
        success: function (res) {
            if (res.success) {
                toastr.success(res.message);
                $('#editAreaModal').modal('hide');
                setTimeout(() => location.reload(), 800);
            } else {
                toastr.error(res.message);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                let first = Object.values(errors)[0][0];
                toastr.error(first);
                $.each(errors, function (field, messages) {
                    $('#edit_' + field).addClass('is-invalid');
                    $('#edit_' + field + '_error').text(messages[0]);
                });
            } else {
                toastr.error('Something went wrong.');
                console.error(xhr);
            }
        },
        complete: function () {
            $('#editSubmitBtn').prop('disabled', false);
        }
    });
});
</script>
@endpush