@extends($current_layout)
@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Examination', 'url' => '#'],
    ['label' => 'Exam Schedule', 'url' => route('exam_schedule.index')],
]" />

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Exam</label>
                <select id="filter_exam_id" class="form-select form-select-sm">
                    <option value="">Select Exam</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ $selectedExamId == $exam->id ? 'selected' : '' }}>
                        {{ $exam->name }} ({{ $exam->examType->name ?? '-' }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Class</label>
                <select id="filter_class_id" class="form-select form-select-sm">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semi-bold text-primary">Exam Schedule</h5>
        @can('manage-exam-schedules')
        <button type="button" class="btn btn-sm btn-success" id="addScheduleBtn"
            data-bs-toggle="modal" data-bs-target="#addScheduleModal"
            {{ (!$selectedExamId || !$selectedClassId) ? 'disabled' : '' }}>
            <i class='fas fa-plus'></i> Add Schedule
        </button>
        @endcan
    </div>
    <div class="card-body">
        @if(!$selectedExamId || !$selectedClassId)
        <div class="alert alert-info mb-0">Please select an Exam and a Class above to view/add its schedule.</div>
        @else
        <div class="table-responsive">
            <table id="scheduleTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Time From</th>
                        <th>Time To</th>
                        <th>Max Marks</th>
                        <th>Passing Marks</th>
                        @can('manage-exam-schedules')
                        <th>Action</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $index => $schedule)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><span class="badge bg-success-subtle text-success">{{ $schedule->subject->name ?? '-' }}</span></td>
                        <td><span class="badge bg-info-subtle text-info">{{ \Carbon\Carbon::parse($schedule->date)->format('d-M-Y') }}</span></td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ \Carbon\Carbon::parse($schedule->time_from)->format('h:i A') }}</span></td>
                        <td><span class="badge bg-danger-subtle text-danger">{{ \Carbon\Carbon::parse($schedule->time_to)->format('h:i A') }}</span></td>
                        <td>{{ $schedule->max_marks }}</td>
                        <td>{{ $schedule->passing_marks }}</td>
                        @can('manage-exam-schedules')
                        <td>
                            <button class="btn btn-sm btn-outline-primary edit-btn px-3"
                                data-id="{{ $schedule->id }}"
                                data-subject_id="{{ $schedule->subject_id }}"
                                data-subject_name="{{ $schedule->subject->name ?? '' }}"
                                data-date="{{ \Carbon\Carbon::parse($schedule->date)->format('Y-m-d') }}"
                                data-time_from="{{ \Carbon\Carbon::parse($schedule->time_from)->format('H:i') }}"
                                data-time_to="{{ \Carbon\Carbon::parse($schedule->time_to)->format('H:i') }}"
                                data-max_marks="{{ $schedule->max_marks }}"
                                data-passing_marks="{{ $schedule->passing_marks }}">
                                <i class='bx bx-edit'></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-btn px-3" data-id="{{ $schedule->id }}">
                                <i class='bx bx-trash'></i>
                            </button>
                        </td>
                        @endcan
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@include('admin.exam_schedule.modals')
@endsection

@push('scripts')
<script>
    $(function() {

        const selectedExamId = "{{ $selectedExamId }}";
        const selectedClassId = "{{ $selectedClassId }}";

        // ===== Show toastr for server-side errors =====
        @if($errors->any())
        toastr.error("{{ $errors->first() }}");
        @endif

        // ===== DataTable =====
        @if($selectedExamId && $selectedClassId)
        $('#scheduleTable').DataTable({
            @can('manage-exam-schedules')
            columnDefs: [{
                orderable: false,
                targets: 7
            }],
            @endcan
            language: {
                emptyTable: `<div class="alert alert-danger d-flex align-items-center justify-content-center text-center mb-0" role="alert"><i class='bx bx-error-circle me-2'></i><span>No data available in the table</span></div>`
            }
        });
        @endif

        // ===== Filter: reload page on Exam/Class change =====
        function reloadWithFilters() {
            let examId = $('#filter_exam_id').val();
            let classId = $('#filter_class_id').val();
            if (examId && classId) {
                window.location = "{{ route('exam_schedule.index') }}?exam_id=" + examId + "&class_id=" + classId;
            }
        }
        $('#filter_exam_id, #filter_class_id').on('change', reloadWithFilters);

        function clearErrors(prefix) {
            $('#' + prefix + 'ScheduleForm').find('.is-invalid').removeClass('is-invalid');
            $('#' + prefix + 'ScheduleForm').find('.invalid-feedback').remove();
        }

        function showErrors(prefix, errors) {
            $.each(errors, function(key, value) {
                let $field = $(`[name="${key}"], #${prefix}_${key}`);
                $field.addClass('is-invalid');
                $field.siblings('.invalid-feedback').remove();
                $field.after(`<div class="invalid-feedback d-block">${value[0]}</div>`);
                toastr.error(value[0]);
            });
        }

        // ===== Load subjects into a select (excludes already-scheduled ones) =====
        function loadSubjects(prefix, excludeId = null) {
            let $select = $('#' + prefix + '_subject_id');
            $select.html('<option value="">Loading...</option>');

            let url = "{{ route('exam_schedule.get_subjects', ['classId' => ':classId']) }}"
                .replace(':classId', selectedClassId);

            $.get(url, {
                exam_id: selectedExamId,
                exclude_id: excludeId
            }, function(subjects) {
                let options = '<option value="">Select Subject</option>';
                $.each(subjects, function(i, subject) {
                    options += `<option value="${subject.id}">${subject.name}</option>`;
                });
                $select.html(options);
            });
        }

        // ===== Load default marks when subject changes =====
        function bindDefaultMarks(prefix) {
            $('#' + prefix + '_subject_id').off('change').on('change', function() {
                let subjectId = $(this).val();
                if (!subjectId) return;

                let url = "{{ route('exam_schedule.get_default_marks', ['classId' => ':classId', 'subjectId' => ':subjectId']) }}"
                    .replace(':classId', selectedClassId)
                    .replace(':subjectId', subjectId);

                $.get(url, function(res) {
                    $('#' + prefix + '_max_marks').val(res.max_marks);
                    $('#' + prefix + '_passing_marks').val(res.passing_marks);
                });
            });
        }

        // ===== Add Modal: reset + load subjects on open =====
        $('#addScheduleModal').on('show.bs.modal', function() {
            $('#addScheduleForm')[0].reset();
            clearErrors('add');
            loadSubjects('add');
            bindDefaultMarks('add');
        });

        // ===== Add Modal: submit =====
        $('#addScheduleForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors('add');

            let formData = $(this).serialize() +
                '&exam_id=' + selectedExamId + '&class_id=' + selectedClassId;

            $.ajax({
                url: "{{ route('exam_schedule.save') }}",
                method: 'POST',
                data: formData,
                success: function(res) {
                    $('#addScheduleModal').modal('hide');
                    toastr.success(res.message);
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        showErrors('add', xhr.responseJSON.errors);
                    } else {
                        toastr.error('Something went wrong!');
                    }
                }
            });
        });

        // ===== Edit Modal: open + prefill =====
        $(document).on('click', '.edit-btn', function() {
            clearErrors('edit');
            let id = $(this).data('id');

            $('#edit_id').val(id);
            $('#edit_date').val($(this).data('date'));
            $('#edit_time_from').val($(this).data('time_from'));
            $('#edit_time_to').val($(this).data('time_to'));
            $('#edit_max_marks').val($(this).data('max_marks'));
            $('#edit_passing_marks').val($(this).data('passing_marks'));

            let currentSubjectId = $(this).data('subject_id');
            let currentSubjectName = $(this).data('subject_name');

            $('#edit_subject_id').html(`<option value="">Loading...</option>`);

            // 👉 YAHAN jaata hai wo code jo aapne paste kiya
            let url = "{{ route('exam_schedule.get_subjects', ['classId' => ':classId']) }}"
                .replace(':classId', selectedClassId);

            $.get(url, {
                exam_id: selectedExamId,
                exclude_id: id
            }, function(subjects) {
                let options = `<option value="${currentSubjectId}" selected>${currentSubjectName}</option>`;
                $.each(subjects, function(i, subject) {
                    options += `<option value="${subject.id}">${subject.name}</option>`;
                });
                $('#edit_subject_id').html(options);
            });

            bindDefaultMarks('edit');
            $('#editScheduleModal').modal('show');
        });

        // ===== Edit Modal: submit =====
        $('#editScheduleForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors('edit');

            let id = $('#edit_id').val();
            
            // Explicitly sabhi required fields ka data combine karein taake koi field miss na ho
            let formData = {
                _token: '{{ csrf_token() }}',
                exam_id: selectedExamId,
                class_id: selectedClassId,
                subject_id: $('#edit_subject_id').val(),
                date: $('#edit_date').val(),
                time_from: $('#edit_time_from').val(),
                time_to: $('#edit_time_to').val(),
                max_marks: $('#edit_max_marks').val(),
                passing_marks: $('#edit_passing_marks').val()
            };

            $.ajax({
                url: "{{ route('exam_schedule.update', ['id' => ':id']) }}".replace(':id', id),
                method: 'POST',
                data: formData,
                success: function(res) {
                    $('#editScheduleModal').modal('hide');
                    toastr.success(res.message);
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        showErrors('edit', xhr.responseJSON.errors);
                    } else {
                        toastr.error('Something went wrong!');
                    }
                }
            });
        });

        // ===== Delete =====
        $(document).on('click', '.delete-btn', function() {
            let id = $(this).data('id');
            let url = "{{ route('exam_schedule.delete', ['id' => ':id']) }}".replace(':id', id);

            Swal.fire({
                title: 'Are you sure?',
                text: "This Exam Schedule entry will be permanently deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                toastr.success(res.message);
                                location.reload();
                            } else {
                                toastr.error(res.message);
                            }
                        },
                        error: function() {
                            toastr.error('Failed to delete Exam Schedule');
                        }
                    });
                }
            });
        });

    });
</script>
@endpush