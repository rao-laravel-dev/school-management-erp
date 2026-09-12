{{-- ADD CLASS MODAL (Success Theme) --}}
<div class="modal fade" id="addClassModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="addClassForm" action="{{ route('classes.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bx bx-plus-circle"></i> Add New Class</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Class Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Grade 10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Numeric Name <span class="text-danger">*</span></label>
                        <input type="number" name="numeric_name" class="form-control form-control-sm" placeholder="e.g. 10">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="has_subjects" id="has_subjects" value="1" checked>
                            <label class="form-check-label small" for="has_subjects">This class has subject-wise teachers</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description (Optional)</label>
                        <textarea name="description" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <div class="mb-1">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="status" value="1" checked>
                            <label class="form-check-label small" for="status">Active Status</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bx bx-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT CLASS MODAL (Info Theme) --}}
<div class="modal fade" id="editClassModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editClassForm" method="POST">
                @csrf
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bx bx-edit"></i> Edit Class</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Class Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control form-control-sm">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Numeric Name <span class="text-danger">*</span></label>
                        <input type="number" name="numeric_name" id="edit_numeric_name" class="form-control form-control-sm">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="has_subjects" id="edit_has_subjects" value="1">
                            <label class="form-check-label small" for="edit_has_subjects">This class has subject-wise teachers</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description (Optional)</label>
                        <textarea name="description" id="edit_description" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <div class="mb-1">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="edit_status" value="1">
                            <label class="form-check-label small" for="edit_status">Active Status</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-sm text-white"><i class="bx bx-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>