{{-- Add form fields (included in modals.blade.php) --}}
<div class="row g-3">
    <div class="col-12 col-md-6">
        <label class="form-label" for="feeAssignmentYear">Academic Year <span class="text-danger">*</span></label>
        <select name="academic_year_id" id="feeAssignmentYear" class="form-select form-select-sm">
            @foreach($academicYears as $year)
            <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>{{ $year->name }}{{ $year->is_current ? ' (Current)' : '' }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback" id="feeAssignmentError-academic_year_id"></div>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label" for="feeAssignmentFeeType">Fee Type <span class="text-danger">*</span></label>
        <select name="fee_type_id" id="feeAssignmentFeeType" class="form-select form-select-sm">
            <option value="">Select fee type</option>
            @foreach($feeTypes as $feeType)
            <option value="{{ $feeType->id }}" data-kind="{{ \App\Models\FeeAssignment::kindForFeeType($feeType) }}">{{ $feeType->name }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback" id="feeAssignmentError-fee_type_id"></div>
        <div class="fee-assignment-note mt-1" id="feeAssignmentFeeTypeNote"></div>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label" for="feeAssignmentTitle">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" id="feeAssignmentTitle" class="form-control form-control-sm" maxlength="150" placeholder="e.g. Eid Party">
        <div class="invalid-feedback" id="feeAssignmentError-title"></div>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label" for="feeAssignmentAmount">Amount <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0.01" name="amount" id="feeAssignmentAmount" class="form-control form-control-sm">
        <div class="invalid-feedback" id="feeAssignmentError-amount"></div>
    </div>
    <div class="col-12 col-md-6" id="feeAssignmentDueDateBox">
        <label class="form-label" for="feeAssignmentDueDate">Due Date <span class="text-danger">*</span></label>
        <input type="date" name="due_date" id="feeAssignmentDueDate" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
        <div class="invalid-feedback" id="feeAssignmentError-due_date"></div>
    </div>
    {{-- Recurring (Tuition) ke liye; one-time mein hidden + disabled --}}
    <div class="col-12 col-md-3 fee-assignment-month-box" style="display:none;">
        <label class="form-label" for="feeAssignmentStartMonth">Start Month <span class="text-danger">*</span></label>
        <input type="month" name="start_month" id="feeAssignmentStartMonth" class="form-control form-control-sm" disabled>
        <div class="invalid-feedback" id="feeAssignmentError-start_month"></div>
    </div>
    <div class="col-12 col-md-3 fee-assignment-month-box" style="display:none;">
        <label class="form-label" for="feeAssignmentEndMonth">End Month</label>
        <input type="month" name="end_month" id="feeAssignmentEndMonth" class="form-control form-control-sm" disabled>
        <div class="invalid-feedback" id="feeAssignmentError-end_month"></div>
    </div>

    <div class="col-12">
        <label class="form-label d-block" for="feeAssignmentTargetSchool">Who gets this fee? <span class="text-danger">*</span></label>
        <div class="d-flex flex-wrap gap-3">
            <div class="form-check">
                <input class="form-check-input fee-assignment-target" type="radio" name="target_type" id="feeAssignmentTargetSchool" value="school" checked>
                <label class="form-check-label" for="feeAssignmentTargetSchool">Whole school</label>
            </div>
            <div class="form-check">
                <input class="form-check-input fee-assignment-target" type="radio" name="target_type" id="feeAssignmentTargetClasses" value="classes">
                <label class="form-check-label" for="feeAssignmentTargetClasses">Classes</label>
            </div>
            <div class="form-check">
                <input class="form-check-input fee-assignment-target" type="radio" name="target_type" id="feeAssignmentTargetSection" value="section">
                <label class="form-check-label" for="feeAssignmentTargetSection">Class + Section</label>
            </div>
            <div class="form-check">
                <input class="form-check-input fee-assignment-target" type="radio" name="target_type" id="feeAssignmentTargetStudents" value="students">
                <label class="form-check-label" for="feeAssignmentTargetStudents">Selected students</label>
            </div>
        </div>
        <div class="invalid-feedback d-block" id="feeAssignmentError-target_type"></div>
    </div>

    <div class="col-12 fee-assignment-block" data-target="classes" style="display:none;">
        <label class="form-label" for="feeAssignmentClasses">Classes <span class="text-danger">*</span></label>
        <select name="class_ids[]" id="feeAssignmentClasses" class="form-select form-select-sm" multiple disabled>
            @foreach($classes as $class)
            <option value="{{ $class->id }}">{{ $class->name }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback d-block" id="feeAssignmentError-class_ids"></div>
    </div>

    <div class="col-12 col-md-6 fee-assignment-block" data-target="section" style="display:none;">
        <label class="form-label" for="feeAssignmentClass">Class <span class="text-danger">*</span></label>
        <select name="class_id" id="feeAssignmentClass" class="form-select form-select-sm" disabled>
            <option value="">Select class</option>
            @foreach($classes as $class)
            <option value="{{ $class->id }}">{{ $class->name }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback d-block" id="feeAssignmentError-class_id"></div>
    </div>
    <div class="col-12 col-md-6 fee-assignment-block" data-target="section" style="display:none;">
        <label class="form-label" for="feeAssignmentSection">Section <span class="text-danger">*</span></label>
        <select name="section_id" id="feeAssignmentSection" class="form-select form-select-sm" disabled>
            <option value="">Select section</option>
        </select>
        <div class="invalid-feedback d-block" id="feeAssignmentError-section_id"></div>
    </div>

    <div class="col-12 fee-assignment-block" data-target="students" style="display:none;">
        <label class="form-label" for="feeAssignmentStudents">Students <span class="text-danger">*</span></label>
        <select name="student_ids[]" id="feeAssignmentStudents" class="form-select form-select-sm" multiple disabled></select>
        <div class="form-text">Type a name or admission number. Only active students of the selected year are listed.</div>
        <div class="invalid-feedback d-block" id="feeAssignmentError-student_ids"></div>
    </div>

    <div class="col-12">
        <button type="button" class="btn btn-outline-primary btn-sm" id="feeAssignmentPreviewBtn">Preview students</button>
        <div id="feeAssignmentPreviewBox" class="mt-2 small"></div>
    </div>
</div>
