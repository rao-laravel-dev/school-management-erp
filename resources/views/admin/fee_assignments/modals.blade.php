{{-- ADD MODAL --}}
<div class="modal fade" id="feeAssignmentAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="feeAssignmentForm" method="POST" action="{{ route('fee_assignments.store') }}" novalidate>
                @csrf
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">Assign Fee</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.fee_assignments.fields')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm" id="feeAssignmentSaveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT TITLE MODAL --}}
<div class="modal fade" id="feeAssignmentEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="feeAssignmentEditForm" method="POST" novalidate>
                @csrf
                <input type="hidden" id="feeAssignmentEditId">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Fee Assignment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Sirf Title edit hota hai; baqi details read-only --}}
                    <dl class="row mb-2 fee-assignment-note">
                        <dt class="col-5 fw-normal">Fee Type</dt>
                        <dd class="col-7 mb-1 fw-bold" id="feeAssignmentEditFeeType"></dd>
                        <dt class="col-5 fw-normal">Amount</dt>
                        <dd class="col-7 mb-1 fw-bold" id="feeAssignmentEditAmount"></dd>
                        <dt class="col-5 fw-normal" id="feeAssignmentEditDueDateLabel">Due Date</dt>
                        <dd class="col-7 mb-1 fw-bold" id="feeAssignmentEditDueDate"></dd>
                        <dt class="col-5 fw-normal">Target</dt>
                        <dd class="col-7 mb-1 fw-bold" id="feeAssignmentEditTarget"></dd>
                        <dt class="col-5 fw-normal">Students</dt>
                        <dd class="col-7 mb-1 fw-bold" id="feeAssignmentEditStudents"></dd>
                    </dl>
                    <div class="fee-assignment-note mb-3" id="feeAssignmentEditNote"></div>
                    <label class="form-label" for="feeAssignmentEditTitle">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="feeAssignmentEditTitle" class="form-control form-control-sm" maxlength="150">
                    <div class="invalid-feedback" id="feeAssignmentEditError-title"></div>
                    {{-- Sirf recurring --}}
                    <div id="feeAssignmentEditEndBox" class="mt-3" style="display:none;">
                        <label class="form-label" for="feeAssignmentEditEndMonth">End Month</label>
                        <input type="month" name="end_month" id="feeAssignmentEditEndMonth" class="form-control form-control-sm">
                        <div class="invalid-feedback" id="feeAssignmentEditError-end_month"></div>
                        <div class="form-text">Leave empty for no end. Unpaid rows after the End Month are removed.</div>
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
