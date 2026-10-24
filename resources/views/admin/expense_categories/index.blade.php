@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Fees Collection', 'url' => route('fees.collect.index')],
    ['label' => 'Expenses', 'url' => route('expenses.index')],
    ['label' => 'Expense Categories', 'url' => '#'],
]" />

<div class="row">
    <div class="col-xl-12 mx-auto">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bx bx-category me-2"></i>Expense Categories</h5>
                @can('manage-expense-categories')
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bx bx-plus me-1"></i> Add Category
                </button>
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="categoriesTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th width="140">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $cat)
                            <tr id="cat-row-{{ $cat->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $cat->name }}</td>
                                <td>
                                    @can('manage-expense-categories')
                                    <button class="btn btn-sm btn-outline-primary btn-edit-category"
                                        data-id="{{ $cat->id }}"
                                        data-name="{{ $cat->name }}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-delete-category" data-id="{{ $cat->id }}">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

@include('admin.expense_categories.modals')

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000"
    };

    // DataTable init
    $('#categoriesTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        order: [],
        columnDefs: [{ orderable: false, targets: -1 }]
    });

    // Helper: clear validation errors from a form
    function clearErrors(formSelector) {
        $(formSelector).find('.is-invalid').removeClass('is-invalid');
        $(formSelector).find('.invalid-feedback').text('');
    }

    // Helper: show validation errors under fields; toastr sirf pehla error
    function showErrors(formSelector, errors) {
        let firstMsg = '';
        Object.keys(errors).forEach(function (field) {
            const input = $(formSelector).find(`[name="${field}"]`);
            input.addClass('is-invalid');
            input.siblings('.invalid-feedback').text(errors[field][0]);
            if (!firstMsg) firstMsg = errors[field][0];
        });
        toastr.clear();
        if (firstMsg) toastr.error($('<div>').text(firstMsg).html());
    }

    // Typing/change par us field ka error saaf
    $(document).on('input change', '.modal form .is-invalid', function () {
        $(this).removeClass('is-invalid').siblings('.invalid-feedback').text('');
    });

    // Modal band: fields + errors poore reset
    $('#addCategoryModal, #editCategoryModal').on('hidden.bs.modal', function () {
        const form = $(this).find('form');
        form[0].reset();
        clearErrors('#' + form.attr('id'));
        form.find('button[type="submit"]').prop('disabled', false);
    });

    // Add Category
    $('#addCategoryForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors('#addCategoryForm');
        const btn = $(this).find('button[type="submit"]').prop('disabled', true);
        $.ajax({
            url: "{{ route('expense_categories.store') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                toastr.success(res.message || 'Category added.');
                setTimeout(() => location.reload(), 800);
            },
            error: function (xhr) {
                btn.prop('disabled', false);
                if (xhr.status === 422) {
                    showErrors('#addCategoryForm', xhr.responseJSON.errors);
                } else {
                    toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong.');
                }
            }
        });
    });

    // Open Edit Modal
    $(document).on('click', '.btn-edit-category', function () {
        clearErrors('#editCategoryForm');
        $('#edit_category_id').val($(this).data('id'));
        $('#edit_category_name').val($(this).data('name'));
        $('#editCategoryModal').modal('show');
    });

    // Update Category
    $('#editCategoryForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors('#editCategoryForm');
        const id = $('#edit_category_id').val();
        const btn = $(this).find('button[type="submit"]').prop('disabled', true);
        $.ajax({
            url: `/expense-categories/update/${id}`,
            method: 'PUT',
            data: $(this).serialize(),
            success: function (res) {
                toastr.success(res.message || 'Category updated.');
                setTimeout(() => location.reload(), 800);
            },
            error: function (xhr) {
                btn.prop('disabled', false);
                if (xhr.status === 422) {
                    showErrors('#editCategoryForm', xhr.responseJSON.errors);
                } else {
                    toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong.');
                }
            }
        });
    });

    // Delete Category (SweetAlert2 confirm)
    $(document).on('click', '.btn-delete-category', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This category will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            // fee-assignment-swal = app.css dark mode popup + buttons gap
            buttonsStyling: false,
            customClass: {
                popup: 'fee-assignment-swal',
                confirmButton: 'btn btn-danger btn-sm',
                cancelButton: 'btn btn-secondary btn-sm'
            },
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/expense-categories/delete/${id}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        toastr.success(res.message || 'Category deleted.');
                        $(`#cat-row-${id}`).fadeOut(300, function () { $(this).remove(); });
                    },
                    error: function (xhr) {
                        toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Error occurred');
                    }
                });
            }
        });
    });

});
</script>
@endpush