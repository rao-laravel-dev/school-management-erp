@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Lesson Plan', 'url' => '#'],
    ['label' => 'Manage Lesson Plan', 'url' => '#'],
]" />

<div id="resultWrapper" class="mt-3">
    <div class="alert alert-info mb-0">Loading your weekly schedule...</div>
</div>

@include('teacher.lesson_plan.modals')

@endsection

@push('styles')
<style>
    .lp-week-wrapper {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 12px;
    }

    @media (max-width: 991px) {
        .lp-week-wrapper {
            display: flex;
            overflow-x: auto;
            gap: 12px;
            padding-bottom: 8px;
        }

        .lp-day-col {
            flex: 0 0 220px;
            min-width: 220px;
        }
    }

    .lp-card {
        border: 1px solid #e5e7eb;
        border-left: 4px solid var(--lp-day-color, #6c757d);
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 10px;
        background: #fff;
    }

    .lp-card .lp-subject {
        font-weight: 600;
        font-size: 13px;
        color: #212529;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .lp-card .lp-meta {
        font-size: 12px;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 4px;
    }

    .lp-card .lp-meta i,
    .lp-card .lp-subject i {
        color: var(--lp-day-color, #6c757d);
        font-size: 14px;
    }

    .lp-card .lp-actions {
        margin-top: 8px;
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .lp-card .lp-actions .btn:not(:only-child) {
        flex: 1 1 0;
        min-width: 0;
    }

    .lp-card .lp-actions .btn:only-child {
        flex: 0 0 auto;
    }

    .lp-card .lp-actions .btn {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 32px;
        padding: 0 12px;
        line-height: 1;
    }

    .lp-card .lp-actions .btn i {
        font-size: 16px;
        line-height: 1;
    }

    .lp-not-scheduled {
        border: 1px dashed #dc3545;
        border-left: 4px solid #dc3545;
        border-radius: 8px;
        padding: 14px 12px;
        text-align: center;
        color: #dc3545;
        font-size: 12px;
        font-weight: 500;
        background: #fff5f5;
    }

    .lp-not-scheduled i {
        font-size: 16px;
        display: block;
        margin-bottom: 4px;
    }

    .lp-day-header {
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 10px;
        padding-bottom: 6px;
        border-bottom: 2px solid var(--lp-day-color, #6c757d);
        color: var(--lp-day-color, #6c757d);
    }

    .lp-presentation-content img {
        max-width: 100%;
        max-height: 350px;
        width: auto;
        height: auto;
        object-fit: contain;
        display: block;
        margin-bottom: 10px;
        border-radius: 4px;
        border: 1px solid #eee;
    }

    /* quick-add row next to Lesson / Topic selects */
    .lp-select-row {
        display: flex;
        gap: 6px;
        align-items: flex-start;
    }

    .lp-select-row select {
        flex: 1 1 auto;
    }

    .lp-select-row .btn {
        flex: 0 0 auto;
        white-space: nowrap;
    }

    .lp-quick-add-box {
        display: none;
        margin-top: 6px;
        gap: 6px;
    }

    .lp-quick-add-box.show {
        display: flex;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {

        const dayColors = {
            'Monday': '#0d6efd',
            'Tuesday': '#198754',
            'Wednesday': '#fd7e14',
            'Thursday': '#6610f2',
            'Friday': '#dc3545',
            'Saturday': '#0dcaf0',
            'Sunday': '#6c757d'
        };

        function formatTime(t) {
            if (!t) return '-';
            const [h, m] = t.split(':');
            const hour = parseInt(h, 10);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const hour12 = hour % 12 === 0 ? 12 : hour % 12;
            return `${hour12}:${m} ${ampm}`;
        }

        function slotCard(slot) {
            const hasPlan = slot.lesson_plan_id !== null;
            // Teacher: View + Edit only, no Delete
            const actions = hasPlan ?
                `<button class="btn btn-sm btn-outline-info view-btn" data-id="${slot.lesson_plan_id}"><i class='bx bx-show'></i></button>
                <button class="btn btn-sm btn-outline-warning edit-btn" data-id="${slot.lesson_plan_id}"><i class='bx bx-pencil'></i></button>` :
                `<button class="btn btn-sm btn-outline-success add-btn"
                    data-ct="${slot.class_timetable_id}"
                    data-date="${slot.date}"
                    data-time-from="${slot.time_from}"
                    data-time-to="${slot.time_to}"
                    data-class="${slot.class_id}"
                    data-section="${slot.section_id}"
                    data-subject="${slot.subject_id}">
                    <i class='bx bx-plus'></i> Add</button>`;

            return `
        <div class="lp-card">
            <div class="lp-subject"><i class='bx bx-book-content'></i><span>${slot.subject}</span></div>
            <div class="lp-meta"><i class='bx bx-chalkboard'></i><span>Class: ${slot.class}</span></div>
            <div class="lp-meta"><i class='bx bx-collection'></i><span>Section: ${slot.section}</span></div>
            <div class="lp-meta"><i class='bx bx-time-five'></i><span>${formatTime(slot.time_from)} - ${formatTime(slot.time_to)}</span></div>
            <div class="lp-actions">${actions}</div>
        </div>`;
        }

        function loadSlots() {
            $.get(`{{ route('teacher.lesson_plan.get_slots') }}`)
                .done(function(res) {
                    if (!res.success) {
                        toastr.error('Unable to load schedule.');
                        return;
                    }

                    let html = `<div class="card"><div class="card-header"><h6 class="mb-0 fw-semi-bold text-primary fs-5">My Weekly Schedule</h6></div><div class="card-body"><div class="lp-week-wrapper">`;

                    Object.keys(res.grid).forEach(day => {
                        const color = dayColors[day];
                        html += `<div class="lp-day-col" style="--lp-day-color:${color}">
                                <div class="lp-day-header">${day}</div><div>`;

                        if (res.grid[day].length === 0) {
                            html += `<div class="lp-not-scheduled"><i class='bx bx-x-circle'></i>Not Scheduled</div>`;
                        } else {
                            res.grid[day].forEach(slot => html += slotCard(slot));
                        }
                        html += `</div></div>`;
                    });

                    html += `</div></div></div>`;
                    $('#resultWrapper').html(html);
                })
                .fail(function() {
                    toastr.error('Unable to load schedule.');
                });
        }

        // auto-load on page open — no teacher select needed
        loadSlots();

        function resetQuickAdd(prefix) {
            $(`#${prefix}_quick_box`).removeClass('show').hide();
            $(`#${prefix}_quick_name`).val('');
        }

        $(document).on('click', '.add-btn', function() {
            $('#addLessonPlanForm')[0].reset();
            $('#add_ct_id').val($(this).data('ct'));
            $('#add_date').val($(this).data('date'));
            $('#add_time_from').val($(this).data('time-from'));
            $('#add_time_to').val($(this).data('time-to'));
            $('#add_class_id').val($(this).data('class'));
            $('#add_section_id').val($(this).data('section'));
            $('#add_subject_id').val($(this).data('subject'));

            $('#add_lesson_id').html('<option value="">-- Select Lesson --</option>');
            $('#add_topic_id').html('<option value="">-- Select Topic --</option>');
            resetQuickAdd('add_lesson');
            resetQuickAdd('add_topic');

            $.get(`{{ route('teacher.lesson_plan.get_lessons') }}`, {
                class_id: $(this).data('class'),
                section_id: $(this).data('section'),
                subject_id: $(this).data('subject')
            }, function(res) {
                res.data.forEach(l => {
                    $('#add_lesson_id').append(`<option value="${l.id}">${l.name}</option>`);
                });
            });

            $('#addLessonPlanModal').modal('show');
        });

        $(document).on('change', '#add_lesson_id', function() {
            const lessonId = $(this).val();
            $('#add_topic_id').html('<option value="">-- Select Topic --</option>');
            resetQuickAdd('add_topic');
            if (!lessonId) return;

            $.get(`{{ route('teacher.lesson_plan.get_topics', ':id') }}`.replace(':id', lessonId), function(res) {
                res.data.forEach(t => {
                    $('#add_topic_id').append(`<option value="${t.id}">${t.name}</option>`);
                });
            });
        });

        // ---- Quick add Lesson ----
        $(document).on('click', '#add_lesson_quick_toggle', function() {
            $('#add_lesson_quick_box').addClass('show').show();
        });

        $(document).on('click', '#add_lesson_quick_cancel', function() {
            resetQuickAdd('add_lesson');
        });

        $(document).on('click', '#add_lesson_quick_save', function() {
            const name = $('#add_lesson_quick_name').val().trim();
            const ctId = $('#add_ct_id').val();
            if (!name) { toastr.error('Please enter a lesson name.'); return; }
            if (!ctId) { toastr.error('Slot not selected.'); return; }

            $.post(`{{ route('teacher.lesson_plan.quick_lesson') }}`, {
                _token: '{{ csrf_token() }}',
                class_timetable_id: ctId,
                name: name
            }, function(res) {
                if (res.success) {
                    $('#add_lesson_id').append(`<option value="${res.lesson.id}" selected>${res.lesson.name}</option>`);
                    $('#add_lesson_id').trigger('change');
                    resetQuickAdd('add_lesson');
                    toastr.success('Lesson added.');
                }
            }).fail(function() {
                toastr.error('Unable to add lesson.');
            });
        });

        // ---- Quick add Topic ----
        $(document).on('click', '#add_topic_quick_toggle', function() {
            const lessonId = $('#add_lesson_id').val();
            if (!lessonId) { toastr.error('Select a lesson first.'); return; }
            $('#add_topic_quick_box').addClass('show').show();
        });

        $(document).on('click', '#add_topic_quick_cancel', function() {
            resetQuickAdd('add_topic');
        });

        $(document).on('click', '#add_topic_quick_save', function() {
            const name = $('#add_topic_quick_name').val().trim();
            const ctId = $('#add_ct_id').val();
            const lessonId = $('#add_lesson_id').val();
            if (!name) { toastr.error('Please enter a topic name.'); return; }
            if (!lessonId) { toastr.error('Select a lesson first.'); return; }

            $.post(`{{ route('teacher.lesson_plan.quick_topic') }}`, {
                _token: '{{ csrf_token() }}',
                class_timetable_id: ctId,
                lesson_id: lessonId,
                name: name
            }, function(res) {
                if (res.success) {
                    $('#add_topic_id').append(`<option value="${res.topic.id}" selected>${res.topic.name}</option>`);
                    resetQuickAdd('add_topic');
                    toastr.success('Topic added.');
                }
            }).fail(function() {
                toastr.error('Unable to add topic.');
            });
        });

        $(document).on('click', '.edit-btn', function() {
            const id = $(this).data('id');

            $.get(`{{ route('teacher.lesson_plan.edit', ':id') }}`.replace(':id', id), function(res) {
                const lp = res.data ?? res;

                $('#edit_lp_id').val(lp.id);
                $('#edit_sub_topic').val(lp.sub_topic);
                $('#edit_date').val(lp.date);
                $('#edit_time_from').val(lp.time_from);
                $('#edit_time_to').val(lp.time_to);
                $('#edit_youtube_url').val(lp.youtube_url);
                $('#edit_teaching_method').val(lp.teaching_method);
                $('#edit_general_objectives').val(lp.general_objectives);
                $('#edit_previous_knowledge').val(lp.previous_knowledge);
                $('#edit_comprehensive_questions').val(lp.comprehensive_questions);
                $('#edit_presentation').val(lp.presentation);
                syncPresentationPreview('edit_presentation');

                $('#edit_lesson_id').html('<option value="">-- Select Lesson --</option>');
                $('#edit_topic_id').html('<option value="">-- Select Topic --</option>');

                $.get(`{{ route('teacher.lesson_plan.get_lessons') }}`, {
                    class_id: lp.class_id,
                    section_id: lp.section_id,
                    subject_id: lp.subject_id
                }, function(lessonRes) {
                    lessonRes.data.forEach(l => {
                        $('#edit_lesson_id').append(`<option value="${l.id}">${l.name}</option>`);
                    });
                    $('#edit_lesson_id').val(lp.lesson_id);

                    if (lp.lesson_id) {
                        $.get(`{{ route('teacher.lesson_plan.get_topics', ':id') }}`.replace(':id', lp.lesson_id), function(topicRes) {
                            topicRes.data.forEach(t => {
                                $('#edit_topic_id').append(`<option value="${t.id}">${t.name}</option>`);
                            });
                            $('#edit_topic_id').val(lp.topic_id);
                        });
                    }
                });

                $('#editLessonPlanModal').modal('show');
            });
        });

        $(document).on('change', '#edit_lesson_id', function() {
            const lessonId = $(this).val();
            $('#edit_topic_id').html('<option value="">-- Select Topic --</option>');
            if (!lessonId) return;

            $.get(`{{ route('teacher.lesson_plan.get_topics', ':id') }}`.replace(':id', lessonId), function(res) {
                res.data.forEach(t => {
                    $('#edit_topic_id').append(`<option value="${t.id}">${t.name}</option>`);
                });
            });
        });

        $(document).on('submit', '#lpCommentForm', function(e) {
            e.preventDefault();
            const form = $(this);

            $.ajax({
                url: '{{ route("teacher.lesson_plan.comment.save") }}',
                method: 'POST',
                data: form.serialize(),
                success: function(res) {
                    $('#lp_comments_list .text-muted.small:contains("No comments yet.")').remove();
                    $('#lp_comments_list').append(`
                <div class="border-bottom pb-2 mb-2">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold small">${res.comment.user_name}</span>
                        <span class="text-muted small">${res.comment.created_at}</span>
                    </div>
                    <div class="small">${res.comment.comment}</div>
                </div>
            `);
                    form.find('input[name="comment"]').val('');
                },
                error: function() {
                    toastr.error('Unable to add comment.');
                }
            });
        });

        $(document).on('click', '.view-btn', function() {
            const id = $(this).data('id');
            $.get(`{{ route('teacher.lesson_plan.view', ':id') }}`.replace(':id', id), function(res) {
                $('#viewLessonPlanBody').html(res.html);
                $('#viewLessonPlanModal').modal('show');
            });
        });

        $(document).on('click', '.add-image-btn', function() {
            const targetId = $(this).data('target');
            $('#' + targetId + '_image').trigger('click');
        });

        function syncPresentationPreview(prefix) {
            const textarea = $('#' + prefix);
            const preview = $('#' + prefix + '_preview');
            const html = textarea.val();

            const srcs = [];
            const regex = /<img[^>]+src=["']([^"']+)["']/g;
            let match;
            while ((match = regex.exec(html)) !== null) {
                srcs.push(match[1]);
            }

            preview.empty();
            srcs.forEach(src => {
                preview.append(`<img src="${src}" style="height:60px;border:1px solid #ddd;border-radius:4px;">`);
            });
        }

        $(document).on('change', '.presentation-image-input', function() {
            const file = this.files[0];
            if (!file) return;

            const inputId = $(this).attr('id');
            const prefix = inputId.replace('_image', '');
            const textarea = $('#' + prefix);
            const fileInput = $(this);

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('image', file);

            $.ajax({
                url: "{{ route('teacher.lesson_plan.upload_image') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    const imgTag = `<img src="${res.url}" style="max-width:100%;">`;
                    const el = textarea.get(0);
                    const start = el.selectionStart ?? el.value.length;
                    const end = el.selectionEnd ?? el.value.length;
                    el.value = el.value.substring(0, start) + imgTag + el.value.substring(end);
                    syncPresentationPreview(prefix);
                    fileInput.val('');
                },
                error: function() {
                    toastr.error('Image upload failed.');
                }
            });
        });

        $(document).on('input', 'textarea[id$="_presentation"], #add_presentation, #edit_presentation', function() {
            syncPresentationPreview($(this).attr('id'));
        });

        $('#addLessonPlanForm').on('submit', function(e) {
            e.preventDefault();
            $('#addLessonPlanForm .form-control, #addLessonPlanForm .form-select').removeClass('is-invalid');
            $('#addLessonPlanForm .invalid-feedback').text('');

            $.ajax({
                url: '{{ route("teacher.lesson_plan.save") }}',
                method: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function() {
                    toastr.success('Lesson plan added successfully.');
                    $('#addLessonPlanModal').modal('hide');
                    loadSlots();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function(field, messages) {
                            $(`#add_${field}`).addClass('is-invalid');
                            $(`#add_err_${field}`).text(messages[0]);
                        });
                        toastr.error('Please fix the highlighted fields.');
                    } else if (xhr.status === 403) {
                        toastr.error('You are not allowed to do this.');
                    } else {
                        toastr.error('Something went wrong.');
                    }
                }
            });
        });

        $('#editLessonPlanForm').on('submit', function(e) {
            e.preventDefault();
            $('#editLessonPlanForm .form-control, #editLessonPlanForm .form-select').removeClass('is-invalid');
            $('#editLessonPlanForm .invalid-feedback').text('');

            const lpId = $('#edit_lp_id').val();
            const formData = new FormData(this);
            formData.append('_method', 'PUT');

            $.ajax({
                url: `{{ route('teacher.lesson_plan.update', ':id') }}`.replace(':id', lpId),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function() {
                    toastr.success('Lesson plan updated successfully.');
                    $('#editLessonPlanModal').modal('hide');
                    loadSlots();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function(field, messages) {
                            $(`#edit_${field}`).addClass('is-invalid');
                            $(`#edit_err_${field}`).text(messages[0]);
                        });
                        toastr.error('Please fix the highlighted fields.');
                    } else if (xhr.status === 403) {
                        toastr.error('You can only edit your own lesson plans.');
                    } else {
                        toastr.error('Something went wrong.');
                    }
                }
            });
        });

        // NOTE: no delete handler here — teacher cannot delete lesson plans.
    });
</script>
@endpush