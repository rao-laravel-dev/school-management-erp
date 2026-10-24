@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Fees Collection', 'url' => route('fees.collect.index')],
    ['label' => 'Expenses', 'url' => route('expenses.index')],
    ['label' => 'Expense', 'url' => '#'],
]" />

<div class="row">
    <div class="col-xl-12 mx-auto">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bx bx-receipt me-2"></i>Expenses</h5>
                @can('manage-expenses')
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                    <i class="bx bx-plus me-1"></i> Add Expense
                </button>
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="expensesTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Bank Account</th>
                                <th width="180">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expenses as $expense)
                            <tr id="expense-row-{{ $expense->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $expense->title }}</td>
                                <td>{{ $expense->category->name ?? '-' }}</td>
                                <td class="fw-bold">{{ number_format($expense->amount, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M, Y') }}</td>
                                <td>
                                    @if($expense->status === 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $expense->bankAccount->bank_name ?? '-' }}</td>
                                <td>
                                    @can('manage-expenses')
                                        @if($expense->status === 'pending')
                                        
                                        <button class="btn btn-sm btn-outline-success btn-pay"
                                            data-id="{{ $expense->id }}"
                                            data-amount="{{ $expense->amount }}"
                                            data-title="{{ $expense->title }}">
                                            <i class="bx bx-check-circle"></i> Pay
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary btn-edit-expense px-3"
                                            data-id="{{ $expense->id }}"
                                            data-category="{{ $expense->expense_category_id }}"
                                            data-title="{{ $expense->title }}"
                                            data-description="{{ $expense->description }}"
                                            data-amount="{{ $expense->amount }}"
                                            data-date="{{ \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d') }}">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-delete px-3" data-id="{{ $expense->id }}">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
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

@include('admin.expenses.modals')

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
    $('#expensesTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        order: [],
        columnDefs: [{ orderable: false, targets: -1 }]
    });

    // Helper: clear validation errors
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

    // Modal band: fields + errors poore reset (Add date wapas aaj ki)
    $('#addExpenseModal, #editExpenseModal, #payExpenseModal').on('hidden.bs.modal', function () {
        const form = $(this).find('form');
        form[0].reset();
        clearErrors('#' + form.attr('id'));
        if (form.attr('id') === 'addExpenseForm') {
            $('#add_expense_date').val("{{ date('Y-m-d') }}");
        }
        form.find('button[type="submit"]').prop('disabled', false);
    });

    // Add Expense
    $('#addExpenseForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors('#addExpenseForm');
        const btn = $(this).find('button[type="submit"]').prop('disabled', true);
        $.ajax({
            url: "{{ route('expenses.store') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                toastr.success(res.message || 'Expense added.');
                setTimeout(() => location.reload(), 800);
            },
            error: function (xhr) {
                btn.prop('disabled', false);
                if (xhr.status === 422) {
                    showErrors('#addExpenseForm', xhr.responseJSON.errors);
                } else {
                    toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong.');
                }
            }
        });
    });

    // Open Edit Modal
    $(document).on('click', '.btn-edit-expense', function () {
        clearErrors('#editExpenseForm');
        $('#edit_expense_id').val($(this).data('id'));
        $('#edit_expense_category').val($(this).data('category'));
        $('#edit_expense_title').val($(this).data('title'));
        $('#edit_expense_description').val($(this).data('description'));
        $('#edit_expense_amount').val($(this).data('amount'));
        $('#edit_expense_date').val($(this).data('date'));
        $('#editExpenseModal').modal('show');
    });

    // Update Expense
    $('#editExpenseForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors('#editExpenseForm');
        const id = $('#edit_expense_id').val();
        const btn = $(this).find('button[type="submit"]').prop('disabled', true);
        $.ajax({
            url: `/expenses/update/${id}`,
            method: 'PUT',
            data: $(this).serialize(),
            success: function (res) {
                toastr.success(res.message || 'Expense updated.');
                setTimeout(() => location.reload(), 800);
            },
            error: function (xhr) {
                btn.prop('disabled', false);
                if (xhr.status === 422 && xhr.responseJSON.errors) {
                    showErrors('#editExpenseForm', xhr.responseJSON.errors);
                } else {
                    toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong.');
                }
            }
        });
    });

    // Open Pay Modal
    $(document).on('click', '.btn-pay', function () {
        $('#pay_expense_id').val($(this).data('id'));
        $('#pay_title_display').text($(this).data('title'));
        $('#pay_amount_display').text(parseFloat($(this).data('amount')).toFixed(2));
        $('#payExpenseModal').modal('show');
    });

    // Submit Pay
    $('#payExpenseForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors('#payExpenseForm');
        const id = $('#pay_expense_id').val();
        const btn = $(this).find('button[type="submit"]').prop('disabled', true);
        $.ajax({
            url: `/expenses/${id}/pay`,
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                toastr.success(res.message || 'Expense marked as paid.');
                setTimeout(() => location.reload(), 800);
            },
            error: function (xhr) {
                btn.prop('disabled', false);
                if (xhr.status === 422 && xhr.responseJSON.errors) {
                    showErrors('#payExpenseForm', xhr.responseJSON.errors);
                } else {
                    toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Error occurred');
                }
            }
        });
    });

    // Delete Expense (SweetAlert2 confirm)
    $(document).on('click', '.btn-delete', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This expense will be permanently deleted.",
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
                    url: `/expenses/delete/${id}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        toastr.success(res.message || 'Expense deleted.');
                        $(`#expense-row-${id}`).fadeOut(300, function () { $(this).remove(); });
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