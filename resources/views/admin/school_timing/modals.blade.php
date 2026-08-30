{{-- ADD MODAL --}}
<div class="modal fade" id="addTimingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addTimingForm">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Add School Timing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addSeasonName" class="form-label">Season Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="addSeasonName" name="season_name" placeholder="e.g. Summer, Winter, Exam Days">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="addStartTime" class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control form-control-sm" id="addStartTime" name="start_time">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="addEndTime" class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control form-control-sm" id="addEndTime" name="end_time">
                        </div>
                    </div>
                    <div class="mb-1">
                        <label for="addGraceMinutes" class="form-label">Late Grace Period (minutes)</label>
                        <input type="number" class="form-control form-control-sm" id="addGraceMinutes" name="late_grace_minutes" value="0" min="0" max="120">
                        <small class="text-muted">Start time ke kitni der baad tak "Late" nahi maana jayega</small>
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
<div class="modal fade" id="editTimingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editTimingForm">
                @csrf
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit School Timing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editSeasonName" class="form-label">Season Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="editSeasonName" name="season_name">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editStartTime" class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control form-control-sm" id="editStartTime" name="start_time">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editEndTime" class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control form-control-sm" id="editEndTime" name="end_time">
                        </div>
                    </div>
                    <div class="mb-1">
                        <label for="editGraceMinutes" class="form-label">Late Grace Period (minutes)</label>
                        <input type="number" class="form-control form-control-sm" id="editGraceMinutes" name="late_grace_minutes" min="0" max="120">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>