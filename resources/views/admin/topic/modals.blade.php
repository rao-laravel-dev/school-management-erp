<!-- ================= ADD TOPIC MODAL ================= -->
<div class="modal fade" id="addTopicModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addTopicForm">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Add Topic</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Class <span class="text-danger">*</span></label>
                            <select id="add_class_id" name="class_id" class="form-select form-select-sm">
                                <option value="">Select</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a class.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Section <span class="text-danger">*</span></label>
                            <select id="add_section_id" name="section_id" class="form-select form-select-sm">
                                <option value="">Select</option>
                            </select>
                            <div class="invalid-feedback">Please select a section.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <select id="add_subject_id" name="subject_id" class="form-select form-select-sm">
                                <option value="">Select</option>
                            </select>
                            <div class="invalid-feedback">Please select a subject.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Lesson <span class="text-danger">*</span></label>
                            <select id="add_lesson_id" name="lesson_id" class="form-select form-select-sm">
                                <option value="">Select</option>
                            </select>
                            <div class="invalid-feedback">Please select a lesson.</div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">Topic Name <span class="text-danger">*</span></label>
                        <button type="button" id="btn_add_more" class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-plus"></i> Add More
                        </button>
                    </div>
                    <div id="topic_name_rows">
                        <div class="input-group mb-2 topic-name-row">
                            <input type="text" name="name[]" class="form-control form-control-sm topic-name-input" placeholder="Topic Name">
                            <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="bx bx-trash"></i></button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= EDIT TOPIC MODAL ================= -->
<div class="modal fade" id="editTopicModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editTopicForm">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Class <span class="text-danger">*</span></label>
                            <select id="edit_class_id" name="class_id" class="form-select form-select-sm">
                                <option value="">Select</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a class.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Section <span class="text-danger">*</span></label>
                            <select id="edit_section_id" name="section_id" class="form-select form-select-sm">
                                <option value="">Select</option>
                            </select>
                            <div class="invalid-feedback">Please select a section.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <select id="edit_subject_id" name="subject_id" class="form-select form-select-sm">
                                <option value="">Select</option>
                            </select>
                            <div class="invalid-feedback">Please select a subject.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Lesson <span class="text-danger">*</span></label>
                            <select id="edit_lesson_id" name="lesson_id" class="form-select form-select-sm">
                                <option value="">Select</option>
                            </select>
                            <div class="invalid-feedback">Please select a lesson.</div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-2">
                        <label class="form-label">Topic Name <span class="text-danger">*</span></label>
                        <input type="text" id="edit_name" name="name[]" class="form-control form-control-sm">
                        <div class="invalid-feedback">Please enter a topic name.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>