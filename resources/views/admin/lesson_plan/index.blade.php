@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Lesson Plan', 'url' => '#'],
    ['label' => 'Manage Lesson Plan', 'url' => route('lesson_plan.index')],
    ['label' => 'Manage Lesson Plan', 'url' => '#'],
]" />

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 fw-semi-bold text-primary">Select Criteria</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label class="form-label" for="teacher_select">Select Teacher <span class="text-danger">*</span></label>
                <select id="teacher_select" class="form-select form-select-sm">
                    <option value="">-- Select Teacher --</option>
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div id="resultWrapper" class="mt-3">
    <div class="alert alert-info mb-0">Please select a teacher to view their weekly schedule.</div>
</div>

@include('admin.lesson_plan.modals')

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
            /* har day ki fixed minimum width */
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

    /* Jab 2+ buttons hon (View/Edit/Delete), tab equal-width */
    .lp-card .lp-actions .btn:not(:only-child) {
        flex: 1 1 0;
        min-width: 0;
    }

    /* Add button (akela hota hai) apni natural width le, full-stretch na ho */
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

        function slotCard(slot) {
            const hasPlan = slot.lesson_plan_id !== null;
            const actions = hasPlan ?
                `<button class="btn btn-sm btn-outline-info view-btn" data-id="${slot.lesson_plan_id}"><i class='bx bx-show'></i></button>
                <button class="btn btn-sm btn-outline-warning edit-btn" data-id="${slot.lesson_plan_id}"><i class='bx bx-pencil'></i></button>
                <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${slot.lesson_plan_id}"><i class='bx bx-trash'></i></button>` :
                `<button class="btn btn-sm btn-outline-success add-btn"
                    data-ct="${slot.class_timetable_id}"
                    data-date="${slot.date}"
                    data-time-from="${slot.time_from}"
                    data-time-to="${slot.time_to}"
                    data-class="${slot.class_id}"
                    data-section="${slot.section_id}"
                    data-subject="${slot.subject_id}">
                    <i class='bx bx-plus'></i> Add</button>`;

            function formatTime(t) {
                if (!t) return '-';
                const [h, m] = t.split(':');
                const hour = parseInt(h, 10);
                const ampm = hour >= 12 ? 'PM' : 'AM';
                const hour12 = hour % 12 === 0 ? 12 : hour % 12;
                return `${hour12}:${m} ${ampm}`;
            }

            return `
        <div class="lp-card">
            <div class="lp-subject"><i class='bx bx-book-content'></i><span>${slot.subject}</span></div>
            <div class="lp-meta"><i class='bx bx-chalkboard'></i><span>Class: ${slot.class}</span></div>
            <div class="lp-meta"><i class='bx bx-collection'></i><span>Section: ${slot.section}</span></div>
            <div class="lp-meta"><i class='bx bx-time-five'></i><span>${formatTime(slot.time_from)} - ${formatTime(slot.time_to)}</span></div>
            <div class="lp-actions">${actions}</div>
        </div>`;
        }

        function loadSlots(teacherId) {
            $.get(`{{ route('lesson_plan.get_slots', ':teacherId') }}`.replace(':teacherId', teacherId))
                .done(function(res) {
                    if (!res.success) {
                        toastr.error('Unable to load schedule.');
                        return;
                    }

                    let html = `<div class="card"><div class="card-header"><h6 class="mb-0 fw-semi-bold text-primary fs-5">Weekly Schedule</h6></div><div class="card-body"><div class="lp-week-wrapper">`;

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

        $('#teacher_select').on('change', function() {
            const teacherId = $(this).val();
            if (!teacherId) {
                $('#resultWrapper').html('<div class="alert alert-info mb-0">Please select a teacher to view their weekly schedule.</div>');
                return;
            }
            loadSlots(teacherId);
        });

        $(document).on('click', '.add-btn', function() {
            $('#addLessonPlanForm')[0].reset();
            $('#add_ct_id').val($(this).data('ct'));
            $('#add_date').val($(this).data('date'));
            $('#add_time_from').val($(this).data('time-from'));
            $('#add_time_to').val($(this).data('time-to'));

            const classId = $(this).data('class');
            const sectionId = $(this).data('section');
            const subjectId = $(this).data('subject');

            $('#add_lesson_id').html('<option value="">-- Select Lesson --</option>');
            $('#add_topic_id').html('<option value="">-- Select Topic --</option>');

            $.get(`{{ route('lesson_plan.get_lessons') }}`, {
                class_id: classId,
                section_id: sectionId,
                subject_id: subjectId
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
            if (!lessonId) return;

            $.get(`{{ route('lesson_plan.get_topics', ':id') }}`.replace(':id', lessonId), function(res) {
                res.data.forEach(t => {
                    $('#add_topic_id').append(`<option value="${t.id}">${t.name}</option>`);
                });
            });
        });

        $(document).on('click', '.edit-btn', function() {
            const id = $(this).data('id');

            $.get(`{{ route('lesson_plan.edit', ':id') }}`.replace(':id', id), function(res) {
                const lp = res.data ?? res; // aapka edit() sirf model return karta hai, wrapper nahi

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
                syncPresentationPreview('edit_presentation'); // <-- YE NAYI LINE ADD KARO

                // Lessons load karo isi class/section/subject ke liye, phir current lesson select karo
                $('#edit_lesson_id').html('<option value="">-- Select Lesson --</option>');
                $('#edit_topic_id').html('<option value="">-- Select Topic --</option>');

                $.get(`{{ route('lesson_plan.get_lessons') }}`, {
                    class_id: lp.class_id,
                    section_id: lp.section_id,
                    subject_id: lp.subject_id
                }, function(lessonRes) {
                    lessonRes.data.forEach(l => {
                        $('#edit_lesson_id').append(`<option value="${l.id}">${l.name}</option>`);
                    });
                    $('#edit_lesson_id').val(lp.lesson_id);

                    // Topics load karo, phir current topic select karo
                    if (lp.lesson_id) {
                        $.get(`{{ route('lesson_plan.get_topics', ':id') }}`.replace(':id', lp.lesson_id), function(topicRes) {
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

            $.get(`{{ route('lesson_plan.get_topics', ':id') }}`.replace(':id', lessonId), function(res) {
                res.data.forEach(t => {
                    $('#edit_topic_id').append(`<option value="${t.id}">${t.name}</option>`);
                });
            });
        });


        $(document).on('submit', '#lpCommentForm', function(e) {
            e.preventDefault();
            const form = $(this);

            $.ajax({
                url: '{{ route("lesson_plan.comment.save") }}',
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
            $.get(`{{ route('lesson_plan.view', ':id') }}`.replace(':id', id), function(res) {
                $('#viewLessonPlanBody').html(res.html);
                $('#viewLessonPlanModal').modal('show');
            });
        });

        // ---- Add Image (Presentation field) ----
        $(document).on('click', '.add-image-btn', function() {
            const targetId = $(this).data('target');
            $('#' + targetId + '_image').trigger('click');
        });

        // Textarea ke content se preview thumbnails rebuild karta hai (sync ka source of truth)
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
                url: "{{ route('lesson_plan.upload_image') }}",
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

        // Jab bhi textarea manually edit ho (embed delete/paste), preview turant sync ho
        $(document).on('input', 'textarea[id$="_presentation"], #add_presentation, #edit_presentation', function() {
            syncPresentationPreview($(this).attr('id'));
        });
        $('#addLessonPlanForm').on('submit', function(e) {
            e.preventDefault();
            $('#addLessonPlanForm .form-control, #addLessonPlanForm .form-select').removeClass('is-invalid');
            $('#addLessonPlanForm .invalid-feedback').text('');

            $.ajax({
                url: '{{ route("lesson_plan.save") }}',
                method: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function() {
                    toastr.success('Lesson plan added successfully.');
                    $('#addLessonPlanModal').modal('hide');
                    loadSlots($('#teacher_select').val());
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function(field, messages) {
                            $(`#add_${field}`).addClass('is-invalid');
                            $(`#add_err_${field}`).text(messages[0]);
                        });
                        toastr.error('Please fix the highlighted fields.');
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
                url: `{{ route('lesson_plan.update', ':id') }}`.replace(':id', lpId), // route name confirm karni hogi
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function() {
                    toastr.success('Lesson plan updated successfully.');
                    $('#editLessonPlanModal').modal('hide');
                    loadSlots($('#teacher_select').val());
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function(field, messages) {
                            $(`#edit_${field}`).addClass('is-invalid');
                            $(`#edit_err_${field}`).text(messages[0]);
                        });
                        toastr.error('Please fix the highlighted fields.');
                    } else {
                        toastr.error('Something went wrong.');
                    }
                }
            });
        });

        $(document).on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'This lesson plan will be deleted permanently.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ route('lesson_plan.delete', ':id') }}`.replace(':id', id),
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            toastr.success('Lesson plan deleted successfully.');
                            loadSlots($('#teacher_select').val());
                        },
                        error: function() {
                            toastr.error('Something went wrong.');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush