{{-- ADD MODAL --}}
<div class="modal fade" id="addExamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addExamForm">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Add Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_name" class="form-label">Exam Name</label>
                        <input type="text" class="form-control form-control-sm" id="add_name" name="name" placeholder="e.g. Mid Term Exam 2026">
                    </div>
                    <div class="mb-3">
                        <label for="add_exam_type_id" class="form-label">Exam Type</label>
                        <select class="form-select form-select-sm" id="add_exam_type_id" name="exam_type_id">
                            <option value="">Select Exam Type</option>
                            @foreach($examTypes as $examType)
                            <option value="{{ $examType->id }}">{{ $examType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add_academic_year_id" class="form-label">Academic Year</label>
                        <select class="form-select form-select-sm" id="add_academic_year_id" name="academic_year_id">
                            <option value="">Select Academic Year</option>
                            @foreach($academicYears as $academicYear)
                            <option value="{{ $academicYear->id }}" {{ optional($currentAcademicYear)->id == $academicYear->id ? 'selected' : '' }}>
                                {{ $academicYear->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="add_publish" name="publish" value="1">
                        <label class="form-check-label" for="add_publish">Publish Exam</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="add_publish_result" name="publish_result" value="1">
                        <label class="form-check-label" for="add_publish_result">Publish Result</label>
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
<div class="modal fade" id="editExamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editExamForm">
                @csrf
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Exam Name</label>
                        <input type="text" class="form-control form-control-sm" id="edit_name" name="name">
                    </div>
                    <div class="mb-3">
                        <label for="edit_exam_type_id" class="form-label">Exam Type</label>
                        <select class="form-select form-select-sm" id="edit_exam_type_id" name="exam_type_id">
                            <option value="">Select Exam Type</option>
                            @foreach($examTypes as $examType)
                            <option value="{{ $examType->id }}">{{ $examType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_academic_year_id" class="form-label">Academic Year</label>
                        <select class="form-select form-select-sm" id="edit_academic_year_id" name="academic_year_id">
                            <option value="">Select Academic Year</option>
                            @foreach($academicYears as $academicYear)
                            <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="edit_publish" name="publish" value="1">
                        <label class="form-check-label" for="edit_publish">Publish Exam</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="edit_publish_result" name="publish_result" value="1">
                        <label class="form-check-label" for="edit_publish_result">Publish Result</label>
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