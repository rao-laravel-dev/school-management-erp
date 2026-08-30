

{{-- ADD MODAL --}}
<div class="modal fade" id="addExamTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addExamTypeForm">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Add Exam Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_name" class="form-label">Exam Type Name</label>
                        <input type="text" class="form-control form-control-sm" id="add_name" name="name" placeholder="e.g. Mid Term, Final Term">
                    </div>
                    <div class="mb-3">
                        <label for="add_result_scope" class="form-label">Result Scope</label>
                        <select class="form-control form-control-sm" id="add_result_scope" name="result_scope">
                            <option value="single">Single</option>
                            <option value="cumulative">Cumulative</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="editExamTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editExamTypeForm">
                @csrf
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Exam Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Exam Type Name</label>
                        <input type="text" class="form-control form-control-sm" id="edit_name" name="name">
                    </div>
                    <div class="mb-3">
                        <label for="edit_result_scope" class="form-label">Result Scope</label>
                        <select class="form-control form-control-sm" id="edit_result_scope" name="result_scope">
                            <option value="single">Single</option>
                            <option value="cumulative">Cumulative</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>