@extends($current_layout)
@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Examination', 'url' => '#'],
    ['label' => 'Exam', 'url' => route('exam.index')],
]" />

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semi-bold text-primary">Manage Exam</h5>
        @can('manage-exams')
        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addExamModal">
            <i class='fas fa-plus'></i> Add Exam
        </button>
        @endcan
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="examTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Exam Name</th>
                        <th>Exam Type</th>
                        <th>Academic Year</th>
                        <th>Publish</th>
                        <th>Publish Result</th>
                        @can('manage-exams')
                        <th>Action</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @foreach($exams as $index => $exam)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $exam->name }}</td>
                        <td>{{ $exam->examType->name ?? '-' }}</td>
                        <td>{{ $exam->academicYear->name ?? '-' }}</td>
                        <td>
                            @if($exam->publish)
                            <span class="badge bg-success">Yes</span>
                            @else
                            <span class="badge bg-danger">No</span>
                            @endif
                        </td>
                        <td>
                            @if($exam->publish_result)
                            <span class="badge bg-success">Yes</span>
                            @else
                            <span class="badge bg-danger">No</span>
                            @endif
                        </td>
                        @can('manage-exams')
                        <td>
                            <button class="btn btn-sm btn-outline-info grades-btn px-3"
                                data-id="{{ $exam->id }}"
                                data-name="{{ $exam->name }}"
                                data-bs-toggle="modal" data-bs-target="#gradesModal">
                                <i class='bx bx-medal'></i> Grades
                            </button>

                            <button class="btn btn-sm btn-outline-primary edit-btn px-3"
                                data-id="{{ $exam->id }}"
                                data-name="{{ $exam->name }}"
                                data-exam_type_id="{{ $exam->exam_type_id }}"
                                data-academic_year_id="{{ $exam->academic_year_id }}"
                                data-publish="{{ $exam->publish ? 1 : 0 }}"
                                data-publish_result="{{ $exam->publish_result ? 1 : 0 }}">
                                <i class='bx bx-edit'></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-btn px-3" data-id="{{ $exam->id }}">
                                <i class='bx bx-trash'></i>
                            </button>
                        </td>
                        @endcan
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('admin.exam.modals')
@include('admin.exam.grades_modal')
@endsection

@push('scripts')
<script>
    $(function() {

        // ===== Show toastr for server-side errors =====
        @if($errors->any())
        toastr.error("{{ $errors->first() }}");
        @endif

        // ===== DataTable with custom empty text/alert box =====
        $('#examTable').DataTable({
            @can('manage-exams')
            columnDefs: [{
                orderable: false,
                targets: 6
            }],
            @endcan
            language: {
                emptyTable: `<div class="alert alert-danger d-flex align-items-center justify-content-center text-center mb-0" role="alert"><i class='bx bx-error-circle me-2'></i><span>No data available in the table</span></div>`
            }
        });

        function clearErrors(prefix) {
            $('#' + prefix + 'ExamForm').find('.is-invalid').removeClass('is-invalid');
            $('#' + prefix + 'ExamForm').find('.invalid-feedback').remove();
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

        // ===== Add Modal: reset on close =====
        $('#addExamModal').on('hidden.bs.modal', function() {
            $('#addExamForm')[0].reset();
            $('#add_academic_year_id').val('{{ $currentAcademicYear->id ?? '
                ' }}');
            clearErrors('add');
        });

        // ===== Add Modal: submit =====
        $('#addExamForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors('add');

            $.ajax({
                url: "{{ route('exam.save') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#addExamModal').modal('hide');
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
            $('#edit_id').val($(this).data('id'));
            $('#edit_name').val($(this).data('name'));
            $('#edit_exam_type_id').val($(this).data('exam_type_id'));
            $('#edit_academic_year_id').val($(this).data('academic_year_id'));
            $('#edit_publish').prop('checked', $(this).data('publish') == 1);
            $('#edit_publish_result').prop('checked', $(this).data('publish_result') == 1);
            $('#editExamModal').modal('show');
        });

        // ===== Edit Modal: submit =====
        $('#editExamForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors('edit');

            let id = $('#edit_id').val();

            $.ajax({
                url: "{{ route('exam.update', ':id') }}".replace(':id', id), // ✅ named route, matches actual URL
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#editExamModal').modal('hide');
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
            let url = "{{ route('exam.delete', ':id') }}".replace(':id', id);

            Swal.fire({
                title: 'Are you sure?',
                text: "This Exam will be permanently deleted!",
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
                            toastr.error('Failed to delete Exam');
                        }
                    });
                }
            });
        });

        // ===== Grades Modal =====
        function resetGradeForm() {
            $('#gradeForm')[0].reset();
            $('#grade_id').val('');
            $('#remark').val('');
            $('#gradeFormSubmitBtn').html("<i class='bx bx-plus'></i> Add");
            $('#gradeForm').find('.is-invalid').removeClass('is-invalid');
            $('#gradeForm').find('.invalid-feedback').remove();
        }

        function loadGrades(examId) {
            let url = "{{ route('marks_grade.index', ['examId' => ':examId']) }}".replace(':examId', examId);

            $.get(url, function(res) {
                $('#grades_exam_name').text(res.exam_name);
                $('#grade_exam_id').val(examId);

                let rows = '';
                if (res.grades.length === 0) {
                    rows = `
                        <tr>
                            <td colspan="6">
                                <div class="alert alert-warning d-flex align-items-center justify-content-center text-center mb-0" role="alert">
                                    <i class='bx bx-error me-2'></i>
                                    <span>No grades added yet.</span>
                                </div>
                            </td>
                        </tr>`;
                } else {
                    $.each(res.grades, function(i, g) {
                        rows += `
                            <tr>
                                <td>${i + 1}</td>
                                <td><span class="badge bg-primary-subtle text-primary">${g.grade_name}</span></td>
                                <td>${g.remark ?? '-'}</td>
                                <td>${g.min_percentage}%</td>
                                <td>${g.max_percentage}%</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-grade-btn px-2"
                                        data-id="${g.id}" data-grade_name="${g.grade_name}" data-remark="${g.remark ?? ''}"
                                        data-min_percentage="${g.min_percentage}" data-max_percentage="${g.max_percentage}">
                                        <i class='bx bx-edit'></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-grade-btn px-2" data-id="${g.id}">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </td>
                            </tr>`;
                    });
                }
                $('#gradesTableBody').html(rows);
            });
        }

        $(document).on('click', '.grades-btn', function() {
            resetGradeForm();
            loadGrades($(this).data('id'));
        });

        $('#gradesModal').on('hidden.bs.modal', function() {
            resetGradeForm();
        });

        // ===== Grade form submit (handles both Add + Edit) =====
        $('#gradeForm').on('submit', function(e) {
            e.preventDefault();
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').remove();

            let id = $('#grade_id').val();
            let examId = $('#grade_exam_id').val();
            let isEdit = id !== '';

            let url = isEdit ?
                "{{ route('marks_grade.update', ['id' => ':id']) }}".replace(':id', id) :
                "{{ route('marks_grade.save') }}";

            $.ajax({
                url: url,
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.status === 'error') {
                        toastr.error(res.message);
                        return;
                    }
                    toastr.success(res.message);
                    resetGradeForm();
                    loadGrades(examId);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors || {
                            general: [xhr.responseJSON.message]
                        };
                        $.each(errors, function(key, value) {
                            let $field = $(`#${key}`);
                            $field.addClass('is-invalid');
                            $field.after(`<div class="invalid-feedback d-block">${value[0]}</div>`);
                            toastr.error(value[0]);
                        });
                    } else {
                        toastr.error('Something went wrong!');
                    }
                }
            });
        });

        // ===== Edit grade: populate form =====
        $(document).on('click', '.edit-grade-btn', function() {
            $('#grade_id').val($(this).data('id'));
            $('#grade_name').val($(this).data('grade_name'));
            $('#remark').val($(this).data('remark'));
            $('#min_percentage').val($(this).data('min_percentage'));
            $('#max_percentage').val($(this).data('max_percentage'));
            $('#gradeFormSubmitBtn').html("<i class='bx bx-check'></i> Update");
        });

        // ===== Delete grade =====
        $(document).on('click', '.delete-grade-btn', function() {
            let id = $(this).data('id');
            let examId = $('#grade_exam_id').val();
            let url = "{{ route('marks_grade.delete', ['id' => ':id']) }}".replace(':id', id);

            Swal.fire({
                title: 'Are you sure?',
                text: "This grade will be permanently deleted!",
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
                            toastr.success(res.message);
                            loadGrades(examId);
                        },
                        error: function() {
                            toastr.error('Failed to delete grade');
                        }
                    });
                }
            });
        });

    });
</script>
@endpush