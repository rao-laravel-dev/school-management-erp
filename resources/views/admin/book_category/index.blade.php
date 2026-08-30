@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Library', 'url' => '#'],
    ['label' => 'Book Category', 'url' => route('book_category.index')],
]" />

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0 text-primary">Book Categories Directory</h5>
                @can('manage-book-categories')
                <button type="button" class="btn btn-success btn-sm shadow-sm px-5" id="btnAddCategory" data-bs-toggle="modal" data-bs-target="#categoryModal">
                    Add
                </button>
                @endcan
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="viewDataTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                @can('manage-book-categories')
                                <th width="120">Action</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookCategories as $index => $category)
                            <tr id="categoryRow{{ $category->id }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->description ?? '-' }}</td>
                                <td>
                                    @can('manage-book-categories')
                                    <a href="{{ route('book_category.toggle_status', $category->id) }}"
                                       class="btn btn-sm {{ $category->status ? 'btn-outline-success' : 'btn-outline-danger' }} confirm-toggle px-3">
                                        {{ $category->status ? 'Active' : 'Inactive' }}
                                    </a>
                                    @else
                                    <span class="badge {{ $category->status ? 'bg-success' : 'bg-danger' }}">
                                        {{ $category->status ? 'Active' : 'Inactive' }}
                                    </span>
                                    @endcan
                                </td>

                                @can('manage-book-categories')
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3 btnEditCategory"
                                        data-id="{{ $category->id }}"
                                        data-name="{{ $category->name }}"
                                        data-description="{{ $category->description }}"
                                        data-bs-toggle="modal" data-bs-target="#categoryModal">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm px-3 delete-btn" data-id="{{ $category->id }}">
                                        <i class="bx bx-trash"></i>
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
    </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="categoryForm">
                @csrf
                <input type="hidden" id="categoryId" name="id">

                <div class="modal-header bg-success" id="categoryModalHeader">
                    <h5 class="modal-title" id="categoryModalLabel">Add Book Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="name" name="name">
                        <div class="invalid-feedback" id="error-name"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control form-control-sm" id="description" name="description" rows="2"></textarea>
                        <div class="invalid-feedback" id="error-description"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm" id="btnSubmitCategory">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {

        // 1. Initialize DataTable
        var table = $('#viewDataTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "language": {
                "search": "Search Category:"
            }
        });

        // Show toastr for redirect-based actions (status toggle uses session flash, not AJAX)
        @if(session('success'))
            toastr.success("{{ session('success') }}");
        @endif
        @if(session('error'))
            toastr.error("{{ session('error') }}");
        @endif

        let isEdit = false;

        // Reset modal on Add button click
        $('#btnAddCategory').on('click', function() {
            isEdit = false;
            $('#categoryForm')[0].reset();
            $('#categoryId').val('');
            $('#categoryForm').find('.is-invalid').removeClass('is-invalid');
            $('#categoryForm').find('.invalid-feedback').text('');
            $('#categoryModalLabel').text('Add Book Category');
            $('#btnSubmitCategory').text('Save');
            $('#categoryModalHeader').removeClass('bg-warning').addClass('bg-success');
        });

        // Fill modal on Edit button click
        $(document).on('click', '.btnEditCategory', function() {
            isEdit = true;
            $('#categoryForm').find('.is-invalid').removeClass('is-invalid');
            $('#categoryForm').find('.invalid-feedback').text('');
            $('#categoryModalLabel').text('Edit Book Category');
            $('#btnSubmitCategory').text('Update');

            $('#categoryId').val($(this).data('id'));
            $('#name').val($(this).data('name'));
            $('#description').val($(this).data('description'));
            $('#categoryModalHeader').removeClass('bg-success').addClass('bg-warning');
        });

        // Submit form (Add or Update)
        $('#categoryForm').on('submit', function(e) {
            e.preventDefault();

            // Reset previous error states
            $('#categoryForm').find('.is-invalid').removeClass('is-invalid');
            $('#categoryForm').find('.invalid-feedback').text('');

            let id = $('#categoryId').val();
            let url = isEdit ?
                "{{ route('book_category.update', ':id') }}".replace(':id', id) :
                "{{ route('book_category.store') }}";

            $.ajax({
                url: url,
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#categoryModal').modal('hide');
                    toastr.success(res.message ?? 'Saved successfully');
                    setTimeout(() => location.reload(), 800);
                },
                error: function(xhr) {
                    console.log('Status:', xhr.status);
                    console.log('Response:', xhr.responseText);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let firstField = Object.keys(errors)[0];
                        let firstMessage = errors[firstField][0];

                        $.each(errors, function(field, messages) {
                            $('#' + field).addClass('is-invalid');
                            $('#error-' + field).text(messages[0]);
                        });

                        let fieldLabel = firstField.charAt(0).toUpperCase() + firstField.slice(1);
                        toastr.error(fieldLabel + ': ' + firstMessage);
                    } else {
                        toastr.error('Error ' + xhr.status + ': ' + (xhr.responseJSON?.message ?? 'Something went wrong'));
                    }
                }
            });
        });

        // Delete Confirmation
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            let url = "{{ route('book_category.delete', ':id') }}".replace(':id', id);

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            toastr.success(res.message ?? 'Deleted successfully');
                            setTimeout(() => location.reload(), 800);
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON?.message ?? 'Something went wrong';
                            toastr.error(msg);
                        }
                    });
                }
            });
        });

    });
</script>
@endpush