<!-- Add Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addCategoryForm">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Add Skill Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="add_name" class="form-control form-control-sm" placeholder="e.g. Co-Curricular">
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
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCategoryForm">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Edit Skill Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_category_id" name="category_id">

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
    $('#addCategoryForm')[0].reset();
    $('#addCategoryForm .invalid-feedback').text('');
    $('#addCategoryForm .is-invalid').removeClass('is-invalid');
}

$('#addCategoryForm').on('submit', function (e) {
    e.preventDefault();
    $('#addCategoryForm .invalid-feedback').text('');
    $('#addCategoryForm .is-invalid').removeClass('is-invalid');
    $('#addSubmitBtn').prop('disabled', true);

    $.ajax({
        url: "{{ route('skill_categories.store') }}",
        method: 'POST',
        dataType: 'json',
        data: $(this).serialize(),
        success: function (res) {
            if (res.success) {
                toastr.success(res.message);
                $('#addCategoryModal').modal('hide');
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

function editCategory(id) {
    $('#editCategoryForm .invalid-feedback').text('');
    $('#editCategoryForm .is-invalid').removeClass('is-invalid');

    $.get("{{ url('skill-categories') }}/" + id, function (data) {
        $('#edit_category_id').val(data.id);
        $('#edit_name').val(data.name);
        $('#edit_status').val(data.status ? 1 : 0);
        $('#editCategoryModal').modal('show');
    }).fail(function () {
        toastr.error('Unable to load category.');
    });
}

$('#editCategoryForm').on('submit', function (e) {
    e.preventDefault();
    $('#editCategoryForm .invalid-feedback').text('');
    $('#editCategoryForm .is-invalid').removeClass('is-invalid');

    let id = $('#edit_category_id').val();
    $('#editSubmitBtn').prop('disabled', true);

    $.ajax({
        url: "{{ url('skill-categories') }}/" + id,
        method: 'PUT',
        dataType: 'json',
        data: $(this).serialize(),
        success: function (res) {
            if (res.success) {
                toastr.success(res.message);
                $('#editCategoryModal').modal('hide');
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