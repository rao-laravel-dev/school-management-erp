{{-- ADD MODAL --}}
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addScheduleForm">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Add Exam Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_subject_id" class="form-label">Subject</label>
                        <select class="form-select form-select-sm" id="add_subject_id" name="subject_id">
                            <option value="">Select Subject</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add_date" class="form-label">Date</label>
                        <input type="date" class="form-control form-control-sm" id="add_date" name="date">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="add_time_from" class="form-label">Time From</label>
                            <input type="time" class="form-control form-control-sm" id="add_time_from" name="time_from">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="add_time_to" class="form-label">Time To</label>
                            <input type="time" class="form-control form-control-sm" id="add_time_to" name="time_to">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="add_max_marks" class="form-label">Max Marks</label>
                            <input type="number" class="form-control form-control-sm" id="add_max_marks" name="max_marks">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="add_passing_marks" class="form-label">Passing Marks</label>
                            <input type="number" class="form-control form-control-sm" id="add_passing_marks" name="passing_marks">
                        </div>
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
<div class="modal fade" id="editScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editScheduleForm">
                @csrf
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Exam Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_subject_id" class="form-label">Subject</label>
                        <select class="form-select form-select-sm" id="edit_subject_id" name="subject_id">
                            <option value="">Select Subject</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_date" class="form-label">Date</label>
                        <input type="date" class="form-control form-control-sm" id="edit_date" name="date">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="edit_time_from" class="form-label">Time From</label>
                            <input type="time" class="form-control form-control-sm" id="edit_time_from" name="time_from">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="edit_time_to" class="form-label">Time To</label>
                            <input type="time" class="form-control form-control-sm" id="edit_time_to" name="time_to">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="edit_max_marks" class="form-label">Max Marks</label>
                            <input type="number" class="form-control form-control-sm" id="edit_max_marks" name="max_marks">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="edit_passing_marks" class="form-label">Passing Marks</label>
                            <input type="number" class="form-control form-control-sm" id="edit_passing_marks" name="passing_marks">
                        </div>
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