{{-- Add Lesson Plan Modal --}}
<div class="modal fade" id="addLessonPlanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white">Add Lesson Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addLessonPlanForm">
                @csrf
                <input type="hidden" name="class_timetable_id" id="add_ct_id">
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label" for="add_lesson_id">Lesson <span class="text-danger">*</span></label>
                            <select name="lesson_id" id="add_lesson_id" class="form-select form-select-sm">
                                <option value="">-- Select Lesson --</option>
                            </select>
                            <div class="invalid-feedback" id="add_err_lesson_id"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="add_topic_id">Topic</label>
                            <select name="topic_id" id="add_topic_id" class="form-select form-select-sm">
                                <option value="">-- Select Topic --</option>
                            </select>
                            <div class="invalid-feedback" id="add_err_topic_id"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="add_sub_topic">Sub Topic</label>
                            <input type="text" name="sub_topic" id="add_sub_topic" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="add_err_sub_topic"></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="add_date">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="add_date" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="add_err_date"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="add_time_from">Time From</label>
                            <input type="time" name="time_from" id="add_time_from" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="add_err_time_from"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="add_time_to">Time To</label>
                            <input type="time" name="time_to" id="add_time_to" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="add_err_time_to"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="add_youtube_url">YouTube URL</label>
                            <input type="text" name="youtube_url" id="add_youtube_url" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="add_err_youtube_url"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="add_lecture_video">Lecture Video</label>
                            <input type="file" name="lecture_video" id="add_lecture_video" class="form-control form-control-sm" accept=".mp4,.mov,.avi,.wmv,.webm,.mkv">
                            <div class="invalid-feedback" id="add_err_lecture_video"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="add_attachment">Attachment</label>
                            <input type="file" name="attachment" id="add_attachment" class="form-control form-control-sm" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.zip">
                            <div class="invalid-feedback" id="add_err_attachment"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="add_teaching_method">Teaching Method</label>
                            <input type="text" name="teaching_method" id="add_teaching_method" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="add_err_teaching_method"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="add_general_objectives">General Objectives</label>
                            <textarea name="general_objectives" id="add_general_objectives" class="form-control form-control-sm" rows="2"></textarea>
                            <div class="invalid-feedback" id="add_err_general_objectives"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="add_previous_knowledge">Previous Knowledge</label>
                            <textarea name="previous_knowledge" id="add_previous_knowledge" class="form-control form-control-sm" rows="2"></textarea>
                            <div class="invalid-feedback" id="add_err_previous_knowledge"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="add_comprehensive_questions">Comprehensive Questions</label>
                            <textarea name="comprehensive_questions" id="add_comprehensive_questions" class="form-control form-control-sm" rows="2"></textarea>
                            <div class="invalid-feedback" id="add_err_comprehensive_questions"></div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0" for="add_presentation">Presentation</label>
                                <button type="button" class="btn btn-sm btn-primary add-image-btn" data-target="add_presentation">
                                    <i class='bx bx-image-add'></i> Add Image
                                </button>
                            </div>
                            <input type="file" class="d-none presentation-image-input" id="add_presentation_image" accept="image/*">
                            <textarea name="presentation" id="add_presentation" class="form-control form-control-sm mt-1" rows="4"></textarea>
                            <div id="add_presentation_preview" class="d-flex flex-wrap gap-2 mt-2"></div>
                            <div class="invalid-feedback" id="add_err_presentation"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Lesson Plan Modal --}}
<div class="modal fade" id="editLessonPlanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Edit Lesson Plan</h5>
                <button type="button" class="btn-close " data-bs-dismiss="modal"></button>
            </div>
            <form id="editLessonPlanForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="lesson_plan_id" id="edit_lp_id">
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label" for="edit_lesson_id">Lesson <span class="text-danger">*</span></label>
                            <select name="lesson_id" id="edit_lesson_id" class="form-select form-select-sm">
                                <option value="">-- Select Lesson --</option>
                            </select>
                            <div class="invalid-feedback" id="edit_err_lesson_id"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="edit_topic_id">Topic</label>
                            <select name="topic_id" id="edit_topic_id" class="form-select form-select-sm">
                                <option value="">-- Select Topic --</option>
                            </select>
                            <div class="invalid-feedback" id="edit_err_topic_id"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="edit_sub_topic">Sub Topic</label>
                            <input type="text" name="sub_topic" id="edit_sub_topic" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="edit_err_sub_topic"></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="edit_date">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="edit_date" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="edit_err_date"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="edit_time_from">Time From</label>
                            <input type="time" name="time_from" id="edit_time_from" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="edit_err_time_from"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="edit_time_to">Time To</label>
                            <input type="time" name="time_to" id="edit_time_to" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="edit_err_time_to"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="edit_youtube_url">YouTube URL</label>
                            <input type="text" name="youtube_url" id="edit_youtube_url" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="edit_err_youtube_url"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="edit_lecture_video">Lecture Video</label>
                            <input type="file" name="lecture_video" id="edit_lecture_video" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="edit_err_lecture_video"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="edit_attachment">Attachment</label>
                            <input type="file" name="attachment" id="edit_attachment" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="edit_err_attachment"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="edit_teaching_method">Teaching Method</label>
                            <input type="text" name="teaching_method" id="edit_teaching_method" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="edit_err_teaching_method"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_general_objectives">General Objectives</label>
                            <textarea name="general_objectives" id="edit_general_objectives" class="form-control form-control-sm" rows="2"></textarea>
                            <div class="invalid-feedback" id="edit_err_general_objectives"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="edit_previous_knowledge">Previous Knowledge</label>
                            <textarea name="previous_knowledge" id="edit_previous_knowledge" class="form-control form-control-sm" rows="2"></textarea>
                            <div class="invalid-feedback" id="edit_err_previous_knowledge"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_comprehensive_questions">Comprehensive Questions</label>
                            <textarea name="comprehensive_questions" id="edit_comprehensive_questions" class="form-control form-control-sm" rows="2"></textarea>
                            <div class="invalid-feedback" id="edit_err_comprehensive_questions"></div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0" for="edit_presentation">Presentation</label>
                                <button type="button" class="btn btn-sm btn-primary add-image-btn" data-target="edit_presentation">
                                    <i class='bx bx-image-add'></i> Add Image
                                </button>
                            </div>
                            <input type="file" class="d-none presentation-image-input" id="edit_presentation_image" accept="image/*">
                            <textarea name="presentation" id="edit_presentation" class="form-control form-control-sm mt-1" rows="4"></textarea>
                            <div id="edit_presentation_preview" class="d-flex flex-wrap gap-2 mt-2"></div>
                            <div class="invalid-feedback" id="edit_err_presentation"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm btn-warning">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View Lesson Plan Modal --}}
<div class="modal fade" id="viewLessonPlanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white">View Lesson Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewLessonPlanBody">
                {{-- filled via AJAX --}}
            </div>
        </div>
    </div>
</div>

<script>
    // ---- Add Image (Presentation field) ----
    // Click "Add Image" -> opens hidden file input for that modal
    $(document).on('click', '.add-image-btn', function () {
        const targetId = $(this).data('target'); // add_presentation / edit_presentation
        $('#' + targetId + '_image').trigger('click');
    });

    // On file select -> upload and insert an <img> tag into the Presentation textarea
    $(document).on('change', '.presentation-image-input', function () {
        const file = this.files[0];
        if (!file) return;

        const inputId = $(this).attr('id');            // add_presentation_image / edit_presentation_image
        const prefix = inputId.replace('_image', '');   // add_presentation / edit_presentation
        const textarea = $('#' + prefix);
        const preview = $('#' + prefix + '_preview');

        const formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('image', file);

        $.ajax({
            url: "{{ route('lesson_plan.upload_image') }}", // add this route + controller method
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                // Insert image tag at cursor position in textarea
                const imgTag = `<img src="${res.url}" style="max-width:100%;">`;
                const el = textarea.get(0);
                const start = el.selectionStart ?? el.value.length;
                const end = el.selectionEnd ?? el.value.length;
                el.value = el.value.substring(0, start) + imgTag + el.value.substring(end);

                // small visual thumbnail so user sees what was added
                preview.append(`<img src="${res.url}" style="height:60px;border:1px solid #ddd;border-radius:4px;">`);

                $(this).val('');
            }.bind(this),
            error: function () {
                toastr.error('Image upload failed.');
            }
        });
    });
</script>