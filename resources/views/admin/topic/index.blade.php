@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Lesson Plan', 'url' => '#'],
    ['label' => 'Manage Topic', 'url' => route('topic.index')],
    ['label' => 'Topic', 'url' => '#'],
]" />

<!-- Criteria Card -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 fw-semi-bold text-primary">Manage Topic</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('topic.index') }}">
            <div class="row g-2">
                <!-- Class Dropdown -->
                <div class="col-md-2">
                    <label class="form-label" for="filter_class_id">Class</label>
                    <select name="class_id" id="filter_class_id" class="form-select form-select-sm @error('filter') is-invalid @enderror">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Section Dropdown -->
                <div class="col-md-2">
                    <label class="form-label" for="filter_section_id">Section</label>
                    <select name="section_id" id="filter_section_id" class="form-select form-select-sm @error('filter') is-invalid @enderror">
                        <option value="">Select Section</option>
                    </select>
                </div>

                <!-- Subject Dropdown -->
                <div class="col-md-2">
                    <label class="form-label" for="filter_subject_id">Subject</label>
                    <select name="subject_id" id="filter_subject_id" class="form-select form-select-sm @error('filter') is-invalid @enderror">
                        <option value="">Select Subject</option>
                    </select>
                </div>

                <!-- Lesson Dropdown -->
                <div class="col-md-3">
                    <label class="form-label" for="filter_lesson_id">Lesson</label>
                    <select name="lesson_id" id="filter_lesson_id" class="form-select form-select-sm @error('filter') is-invalid @enderror">
                        <option value="">Select Lesson</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-info me-2"><i class="bx bx-search"></i>Search</button>
                    <a href="{{ route('topic.index') }}" class="btn btn-sm btn-warning"><i class="bx bx-undo"></i>Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- List Card -->
<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semi-bold text-primary">Topic List</h5>
        @can('manage-topics')
        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addTopicModal">
            <i class="bx bx-plus"></i> Add Topic
        </button>
        @endcan
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="topicTable" class="table table-bordered table-striped table-sm w-100">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Subject</th>
                        <th>Lesson</th>
                        <th>Topic Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topics as $topic)
                    <tr>
                        <td>{{ $topic->schoolClass->name ?? '-' }}</td>
                        <td>{{ $topic->section->name ?? '-' }}</td>
                        <td>{{ $topic->subject->name ?? '-' }}</td>
                        <td>{{ $topic->lesson->name ?? '-' }}</td>
                        <td>{{ $topic->name }}</td>
                        <td>
                            @can('manage-topics')
                            <button type="button"
                                class="btn btn-sm {{ $topic->status ? 'btn-outline-success' : 'btn-outline-danger' }} status-toggle"
                                data-id="{{ $topic->id }}"
                                data-url="{{ url('topic/toggle-status') }}">
                                {{ $topic->status ? 'Active' : 'Inactive' }}
                            </button>
                            @else
                            @if($topic->status)
                            <span class="badge bg-outline-success">Active</span>
                            @else
                            <span class="badge bg-outline-danger">Inactive</span>
                            @endif
                            @endcan
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary edit-topic px-3" data-id="{{ $topic->id }}">
                                <i class='bx bx-edit'></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-topic px-3" data-id="{{ $topic->id }}">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('admin.topic.modals')
@endsection

@push('scripts')
<script>
    $(function() {

        // ===== Show toastr for server-side errors =====
        @if($errors->any())
        toastr.error("{{ $errors->first() }}");
        @endif

        // ===== DataTable (plain client-side) =====
        let table = $('#topicTable').DataTable({
            language: {
                emptyTable: `<div class="alert alert-danger d-flex align-items-center justify-content-center text-center mb-0" role="alert"><i class="bi bi-exclamation-triangle-fill me-2"></i><span>No data available in the table</span></div>`
            }
        });

        // ===== Filter cascading dropdowns =====
        $('#filter_class_id').on('change', function() {
            let classId = $(this).val();
            $('#filter_section_id').html('<option value="">Select Section</option>');
            $('#filter_subject_id').html('<option value="">Select Subject</option>');
            $('#filter_lesson_id').html('<option value="">Select Lesson</option>');
            if (!classId) return;

            $.get(`{{ url('topic/get-sections') }}/${classId}`, function(data) {
                data.forEach(s => $('#filter_section_id').append(`<option value="${s.id}">${s.name}</option>`));
            });
            $.get(`{{ url('topic/get-subjects') }}/${classId}`, function(data) {
                data.forEach(s => $('#filter_subject_id').append(`<option value="${s.id}">${s.name}</option>`));
            });
        });

        $('#filter_subject_id').on('change', function() {
            let subjectId = $(this).val();
            $('#filter_lesson_id').html('<option value="">Select Lesson</option>');
            if (!subjectId) return;

            $.get(`{{ url('topic/get-lessons') }}/${subjectId}`, function(data) {
                data.forEach(l => $('#filter_lesson_id').append(`<option value="${l.id}">${l.name}</option>`));
            });
        });

        // ===== Add Modal: cascading dropdowns =====
        $('#add_class_id').on('change', function() {
            let classId = $(this).val();
            $('#add_section_id').html('<option value="">Select</option>');
            $('#add_subject_id').html('<option value="">Select</option>');
            $('#add_lesson_id').html('<option value="">Select</option>');
            if (!classId) return;

            $.get(`{{ url('topic/get-sections') }}/${classId}`, function(data) {
                data.forEach(s => $('#add_section_id').append(`<option value="${s.id}">${s.name}</option>`));
            });
            $.get(`{{ url('topic/get-subjects') }}/${classId}`, function(data) {
                data.forEach(s => $('#add_subject_id').append(`<option value="${s.id}">${s.name}</option>`));
            });
        });

        $('#add_subject_id').on('change', function() {
            let subjectId = $(this).val();
            $('#add_lesson_id').html('<option value="">Select</option>');
            if (!subjectId) return;

            $.get(`{{ url('topic/get-lessons') }}/${subjectId}`, function(data) {
                data.forEach(l => $('#add_lesson_id').append(`<option value="${l.id}">${l.name}</option>`));
            });
        });

        // ===== Add Modal: dynamic rows (Add More) =====
        $('#btn_add_more').on('click', function() {
            let row = `
                <div class="input-group mb-2 topic-name-row">
                    <input type="text" name="name[]" class="form-control form-control-sm topic-name-input" placeholder="Topic Name">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="bx bx-trash"></i></button>
                </div>`;
            $('#topic_name_rows').append(row);
        });

        $(document).on('click', '.btn-remove-row', function() {
            if ($('#topic_name_rows .topic-name-row').length > 1) {
                $(this).closest('.topic-name-row').remove();
            }
        });

        // ===== Add Modal: reset on close =====
        $('#addTopicModal').on('hidden.bs.modal', function() {
            $('#addTopicForm')[0].reset();
            $('#add_section_id, #add_subject_id, #add_lesson_id').html('<option value="">Select</option>');
            $('#topic_name_rows').html(`
                <div class="input-group mb-2 topic-name-row">
                    <input type="text" name="name[]" class="form-control form-control-sm topic-name-input" placeholder="Topic Name">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="bx bx-trash"></i></button>
                </div>`);
            $('#addTopicForm .is-invalid').removeClass('is-invalid');
        });

        // ===== Add Modal: submit =====
        $('#addTopicForm').on('submit', function(e) {
            e.preventDefault();
            $(this).find('.is-invalid').removeClass('is-invalid');

            $.ajax({
                url: "{{ route('topic.save') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#addTopicModal').modal('hide');
                    toastr.success(res.message);
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach(function(key) {
                            let field = key.includes('name') ? '.topic-name-input' : `[name="${key}"]`;
                            $(`#addTopicModal ${field}`).first().addClass('is-invalid');
                        });
                    }
                }
            });
        });

        // ===== Edit Modal: open + prefill =====
        $(document).on('click', '.edit-topic', function() {
            let id = $(this).data('id');

            $.get(`{{ url('topic/edit') }}/${id}`, function(topic) {
                $('#edit_id').val(topic.id);
                $('#edit_class_id').val(topic.class_id);

                $.get(`{{ url('topic/get-sections') }}/${topic.class_id}`, function(data) {
                    $('#edit_section_id').html('<option value="">Select</option>');
                    data.forEach(s => $('#edit_section_id').append(`<option value="${s.id}">${s.name}</option>`));
                    $('#edit_section_id').val(topic.section_id);
                });
                $.get(`{{ url('topic/get-subjects') }}/${topic.class_id}`, function(data) {
                    $('#edit_subject_id').html('<option value="">Select</option>');
                    data.forEach(s => $('#edit_subject_id').append(`<option value="${s.id}">${s.name}</option>`));
                    $('#edit_subject_id').val(topic.subject_id);

                    $.get(`{{ url('topic/get-lessons') }}/${topic.subject_id}`, function(lessons) {
                        $('#edit_lesson_id').html('<option value="">Select</option>');
                        lessons.forEach(l => $('#edit_lesson_id').append(`<option value="${l.id}">${l.name}</option>`));
                        $('#edit_lesson_id').val(topic.lesson_id);
                    });
                });

                $('#edit_name').val(topic.name);
                $('#editTopicModal').modal('show');
            });
        });

        // ===== Edit Modal: cascading dropdowns =====
        $('#edit_class_id').on('change', function() {
            let classId = $(this).val();
            $('#edit_section_id').html('<option value="">Select</option>');
            $('#edit_subject_id').html('<option value="">Select</option>');
            $('#edit_lesson_id').html('<option value="">Select</option>');
            if (!classId) return;

            $.get(`{{ url('topic/get-sections') }}/${classId}`, function(data) {
                data.forEach(s => $('#edit_section_id').append(`<option value="${s.id}">${s.name}</option>`));
            });
            $.get(`{{ url('topic/get-subjects') }}/${classId}`, function(data) {
                data.forEach(s => $('#edit_subject_id').append(`<option value="${s.id}">${s.name}</option>`));
            });
        });

        $('#edit_subject_id').on('change', function() {
            let subjectId = $(this).val();
            $('#edit_lesson_id').html('<option value="">Select</option>');
            if (!subjectId) return;

            $.get(`{{ url('topic/get-lessons') }}/${subjectId}`, function(data) {
                data.forEach(l => $('#edit_lesson_id').append(`<option value="${l.id}">${l.name}</option>`));
            });
        });

        // ===== Edit Modal: submit =====
        $('#editTopicForm').on('submit', function(e) {
            e.preventDefault();
            $(this).find('.is-invalid').removeClass('is-invalid');

            $.ajax({
                url: "{{ route('topic.save') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#editTopicModal').modal('hide');
                    toastr.success(res.message);
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach(function(key) {
                            $(`#edit_${key}`).addClass('is-invalid');
                        });
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
                        btn.removeClass('btn-outline-danger').addClass('btn-outline-success').text('Active');
                    } else {
                        btn.removeClass('btn-outline-success').addClass('btn-outline-danger').text('Inactive');
                    }
                    toastr.success(res.status ? 'Status Active Successfully' : 'Status Inactive Successfully');
                },
                error: function() {
                    toastr.error('Failed to update status');
                }
            });
        });

        // ===== Delete =====
        $(document).on('click', '.delete-topic', function() {
            let id = $(this).data('id');
            let url = `{{ url('topic/delete') }}/${id}`;

            Swal.fire({
                title: 'Are you sure?',
                text: "This topic will be permanently deleted!",
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
                            toastr.error('Failed to delete topic');
                        }
                    });
                }
            });
        });

    });
</script>
@endpush