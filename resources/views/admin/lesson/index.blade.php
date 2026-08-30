@extends($current_layout)
@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Lesson Plan', 'url' => '#'],
    ['label' => 'Manage Lesson', 'url' => route('lesson.index')],
    ['label' => 'Lesson', 'url' => '#'],
]" />


<div class="card">
    <div class="card-header">
        <h5 class="mb-0 fw-semi-bold text-primary">Manage Lesson</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('lesson.index') }}">
            <d iv class="row g-2">
                <div class="col-md-3">
                    <label class="form-label" for="filter_class_id">Class</label>
                    <select name="class_id" id="filter_class_id" class="form-select form-select-sm @error('class_id') is-invalid @enderror">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id', request('class_id')) == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="filter_section_id">Section</label>
                    <select name="section_id" id="filter_section_id" class="form-select form-select-sm @error('section_id') is-invalid @enderror">
                        <option value="">Select Section</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="filter_subject_id">Subject</label>
                    <select name="subject_id" id="filter_subject_id" class="form-select form-select-sm @error('subject_id') is-invalid @enderror">
                        <option value="">Select Subject</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary me-2"><i class='bx bx-search'></i>Search</button>
                    <a href="{{ route('lesson.index') }}" class="btn btn-sm btn-warning"><i class='bx bx-undo'></i>Reset</a>
                </div>
            </d>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semi-bold text-primary">Lesson List</h5>
        @can('manage-lessons')
        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addLessonModal">
            <i class="bx bx-plus"></i> Add Lesson
        </button>
        @endcan
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="lessonTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Subject</th>
                        <th>Lesson Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lessons as $lesson)
                    <tr>
                        <td>{{ $lesson->schoolClass->name ?? '-' }}</td>
                        <td>{{ $lesson->section->name ?? '-' }}</td>
                        <td>{{ $lesson->subject->name ?? '-' }}</td>
                        <td>{{ $lesson->name }}</td>
                        <td>
                            @can('manage-lessons')
                            <button type="button"
                                class="btn btn-sm {{ $lesson->status ? 'btn-outline-success' : 'btn-outline-danger' }} status-toggle"
                                data-id="{{ $lesson->id }}"
                                data-url="{{ url('lesson/toggle-status') }}">
                                {{ $lesson->status ? 'Active' : 'Inactive' }}
                            </button>
                            @else
                            @if($lesson->status)
                            <span class="badge bg-outline-success">Active</span>
                            @else
                            <span class="badge bg-outline-danger">Inactive</span>
                            @endif
                            @endcan
                        </td>
                        <td>
                            @can('manage-lessons')
                            <button class="btn btn-sm btn-outline-primary edit-lesson px-3" data-id="{{ $lesson->id }}">
                                <i class='bx bx-edit'></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-lesson px-3" data-id="{{ $lesson->id }}">
                                <i class='bx bx-trash'></i>
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('admin.lesson.modals')
@endsection

@push('scripts')
<script>
    $(function() {

        // ===== Show toastr for server-side errors =====
        @if($errors->any())
        toastr.error("{{ $errors->first() }}");
        @endif

        // ===== DataTable with custom empty text/alert box =====
        let table = $('#lessonTable').DataTable({
            language: {
                emptyTable: `<div class="alert alert-danger d-flex align-items-center justify-content-center text-center mb-0" role="alert"><i class='bx bx-error-circle me-2'></i><span>No data available in the table</span></div>`
            }
        });

        // ===== Filter cascading dropdowns =====
        $('#filter_class_id').on('change', function() {
            let classId = $(this).val();
            $('#filter_section_id').html('<option value="">Select Section</option>');
            $('#filter_subject_id').html('<option value="">Select Subject</option>');
            if (!classId) return;

            $.get(`{{ url('lesson/get-sections') }}/${classId}`, function(data) {
                data.forEach(s => $('#filter_section_id').append(`<option value="${s.id}">${s.name}</option>`));
            });
            $.get(`{{ url('lesson/get-subjects') }}/${classId}`, function(data) {
                data.forEach(s => $('#filter_subject_id').append(`<option value="${s.id}">${s.name}</option>`));
            });
        });

        // ===== Add Modal: cascading dropdowns =====
        $('#add_class_id').on('change', function() {
            let classId = $(this).val();
            $('#add_section_id').html('<option value="">Select Section</option>');
            $('#add_subject_id').html('<option value="">Select Subject</option>');
            if (!classId) return;

            $.get(`{{ url('lesson/get-sections') }}/${classId}`, function(data) {
                data.forEach(s => $('#add_section_id').append(`<option value="${s.id}">${s.name}</option>`));
            });
            $.get(`{{ url('lesson/get-subjects') }}/${classId}`, function(data) {
                data.forEach(s => $('#add_subject_id').append(`<option value="${s.id}">${s.name}</option>`));
            });
        });

        // ===== Add Modal: dynamic rows (Add More) =====
        $('#btn_add_more').on('click', function() {
            let row = `
            <div class="input-group mb-2 lesson-name-row">
                <input type="text" name="name[]" class="form-control form-control-sm lesson-name-input" placeholder="Lesson Name">
                <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="bx bx-trash"></i></button>
            </div>`;
            $('#lesson_name_rows').append(row);
        });

        $(document).on('click', '.btn-remove-row', function() {
            if ($('#lesson_name_rows .lesson-name-row').length > 1) {
                $(this).closest('.lesson-name-row').remove();
            }
        });

        // ===== Add Modal: reset on close =====
        $('#addLessonModal').on('hidden.bs.modal', function() {
            $('#addLessonForm')[0].reset();
            $('#add_section_id, #add_subject_id').html('<option value="">Select</option>');
            $('#lesson_name_rows').html(`
            <div class="input-group mb-2 lesson-name-row">
                <input type="text" name="name[]" class="form-control form-control-sm lesson-name-input" placeholder="Lesson Name">
                <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="bx bx-trash"></i></button>
            </div>`);
            $('#addLessonForm .is-invalid').removeClass('is-invalid');
        });

        // ===== Add Modal: submit =====
        $('#addLessonForm').on('submit', function(e) {
            e.preventDefault();
            $(this).find('.is-invalid').removeClass('is-invalid');

            $.ajax({
                url: "{{ route('lesson.save') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#addLessonModal').modal('hide');
                    toastr.success(res.message);
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        toastr.error("Please fill all required fields correctly!");

                        // Loop through all validation errors and highlight fields
                        $.each(errors, function(key, value) {
                            // Target both direct name attributes and array formats
                            $(`[name="${key}"], [name="${key}[]"], #${key}, #add_${key}`).addClass('is-invalid');

                            // Special handling if error is on name array
                            if (key.includes('name')) {
                                $('.lesson-name-input').addClass('is-invalid');
                            }
                        });
                    } else {
                        toastr.error('Something went wrong!');
                    }
                }
            });
        });

        // ===== Edit Modal: open + prefill =====
        $(document).on('click', '.edit-lesson', function() {
            let id = $(this).data('id');
            $('#editLessonForm').find('.is-invalid').removeClass('is-invalid');

            $.get(`{{ url('lesson/edit') }}/${id}`, function(lesson) {
                $('#edit_id').val(lesson.id);
                $('#edit_class_id').val(lesson.class_id);

                $.get(`{{ url('lesson/get-sections') }}/${lesson.class_id}`, function(data) {
                    $('#edit_section_id').html('<option value="">Select Section</option>');
                    data.forEach(s => $('#edit_section_id').append(`<option value="${s.id}">${s.name}</option>`));
                    $('#edit_section_id').val(lesson.section_id);
                });
                $.get(`{{ url('lesson/get-subjects') }}/${lesson.class_id}`, function(data) {
                    $('#edit_subject_id').html('<option value="">Select Subject</option>');
                    data.forEach(s => $('#edit_subject_id').append(`<option value="${s.id}">${s.name}</option>`));
                    $('#edit_subject_id').val(lesson.subject_id);
                });

                $('#edit_name').val(lesson.name);
                $('#editLessonModal').modal('show');
            });
        });

        // ===== Edit Modal: cascading dropdowns (manual change only) =====
        $('#edit_class_id').on('change', function() {
            let classId = $(this).val();
            $('#edit_section_id').html('<option value="">Select Section</option>');
            $('#edit_subject_id').html('<option value="">Select Subject</option>');
            if (!classId) return;

            $.get(`{{ url('lesson/get-sections') }}/${classId}`, function(data) {
                data.forEach(s => $('#edit_section_id').append(`<option value="${s.id}">${s.name}</option>`));
            });
            $.get(`{{ url('lesson/get-subjects') }}/${classId}`, function(data) {
                data.forEach(s => $('#edit_subject_id').append(`<option value="${s.id}">${s.name}</option>`));
            });
        });

        // ===== Edit Modal: submit =====
        $('#editLessonForm').on('submit', function(e) {
            e.preventDefault();
            $(this).find('.is-invalid').removeClass('is-invalid');

            $.ajax({
                url: `{{ url('lesson/save') }}`,
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#editLessonModal').modal('hide');
                    toastr.success(res.message);
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        toastr.error("Please fill all required fields correctly!");

                        $.each(errors, function(key, value) {
                            $(`[name="${key}"], #edit_${key}`).addClass('is-invalid');
                        });
                    } else {
                        toastr.error('Something went wrong!');
                    }
                }
            });
        });

        // ===== Status Toggle =====
        $(document).on('click', '.status-toggle', function() {
            let btn = $(this);
            let id = btn.data('id');
            let url = btn.data('url');

            $.ajax({
                url: `${url}/${id}`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.status) {
                        btn.removeClass('btn-danger').addClass('btn-success').text('Active');
                    } else {
                        btn.removeClass('btn-success').addClass('btn-danger').text('Inactive');
                    }
                    toastr.success(res.status ? 'Status Active Successfully' : 'Status Inactive Successfully');
                },
                error: function() {
                    toastr.error('Failed to update status');
                }
            });
        });

        // ===== Delete =====
        $(document).on('click', '.delete-lesson', function() {
            let id = $(this).data('id');
            let url = `{{ url('lesson/delete') }}/${id}`;

            Swal.fire({
                title: 'Are you sure?',
                text: "This lesson will be permanently deleted!",
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
                            location.reload();
                        },
                        error: function() {
                            toastr.error('Failed to delete lesson');
                        }
                    });
                }
            });
        });

    });
</script>
@endpush