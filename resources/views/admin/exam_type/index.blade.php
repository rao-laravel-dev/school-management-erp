@extends($current_layout)
@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Examination', 'url' => '#'],
    ['label' => 'Exam Type', 'url' => route('exam_type.index')],
]" />

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semi-bold text-primary">Manage Exam Type</h5>
        @can('manage-exam-types')
        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addExamTypeModal">
            <i class='bx bx-plus'></i> Add Exam Type
        </button>
        @endcan
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="examTypeTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Exam Type Name</th>
                        <th>Result Scope</th>
                        @can('manage-exam-types')
                        <th>Action</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @foreach($examTypes as $index => $examType)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $examType->name }}</td>
                        <td><span class="badge bg-{{ $examType->result_scope == 'cumulative' ? 'warning text-dark' : 'success text-dark' }}">{{ ucfirst($examType->result_scope) }}</span></td>
                        @can('manage-exam-types')
                        <td>
                            <button class="btn btn-sm btn-outline-primary edit-btn px-3" data-id="{{ $examType->id }}" data-name="{{ $examType->name }}" data-result_scope="{{ $examType->result_scope }}">
                                <i class='bx bx-edit'></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-btn px-3" data-id="{{ $examType->id }}">
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

@include('admin.exam_type.modals')
@endsection

@push('scripts')
<script>
    $(function() {

        @if($errors->any())
        toastr.error("{{ $errors->first() }}");
        @endif

        let table = $('#examTypeTable').DataTable({
            @can('manage-exam-types')
            columnDefs: [{
                orderable: false,
                targets: 3
            }],
            @endcan
            language: {
                emptyTable: `<div class="alert alert-danger d-flex align-items-center justify-content-center text-center mb-0" role="alert"><i class='bx bx-error-circle me-2'></i><span>No data available in the table</span></div>`
            }
        });

        $('#addExamTypeModal').on('hidden.bs.modal', function() {
            $('#addExamTypeForm')[0].reset();
            $('#addExamTypeForm .is-invalid').removeClass('is-invalid');
            $('#addExamTypeForm .invalid-feedback').remove();
        });

        $('#addExamTypeForm').on('submit', function(e) {
            e.preventDefault();
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').remove();

            $.ajax({
                url: "{{ route('exam_type.save') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#addExamTypeModal').modal('hide');
                    toastr.success(res.message);
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            let $field = $(`[name="${key}"], #add_${key}`);
                            $field.addClass('is-invalid');
                            $field.siblings('.invalid-feedback').remove();
                            $field.after(`<div class="invalid-feedback d-block">${value[0]}</div>`);
                            toastr.error(value[0]);
                        });
                    } else {
                        toastr.error('Something went wrong!');
                    }
                }
            });
        });

        // Edit Modal: open + prefill
        $(document).on('click', '.edit-btn', function() {
            $('#editExamTypeForm').find('.is-invalid').removeClass('is-invalid');
            $('#editExamTypeForm').find('.invalid-feedback').remove();
            $('#edit_id').val($(this).data('id'));
            $('#edit_name').val($(this).data('name'));
            $('#edit_result_scope').val($(this).data('result_scope'));
            $('#editExamTypeModal').modal('show');
        });

        // Edit Modal: submit
        $('#editExamTypeForm').on('submit', function(e) {
            e.preventDefault();
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').remove();

            let id = $('#edit_id').val();

            // Laravel named route use karein taake URL hamesha theek bane
            let url = "{{ route('exam_type.update', ':id') }}";
            url = url.replace(':id', id);

            $.ajax({
                url: url,
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#editExamTypeModal').modal('hide');
                    toastr.success(res.message);
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            let $field = $(`[name="${key}"], #edit_${key}`);
                            $field.addClass('is-invalid');
                            $field.siblings('.invalid-feedback').remove();
                            $field.after(`<div class="invalid-feedback d-block">${value[0]}</div>`);
                            toastr.error(value[0]);
                        });
                    } else {
                        toastr.error('Something went wrong!');
                    }
                }
            });
        });

        $(document).on('click', '.delete-btn', function() {
            let id = $(this).data('id');
            let url = `{{ url('admin/exam_type/delete') }}/${id}`;

            Swal.fire({
                title: 'Are you sure?',
                text: "This Exam Type will be permanently deleted!",
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
                            toastr.error('Failed to delete Exam Type');
                        }
                    });
                }
            });
        });

    });
</script>
@endpush