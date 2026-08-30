@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Fees Collection</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Bank Accounts</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <!-- LEFT: Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0" id="formTitle">Add Bank Account</h5>
            </div>
            <div class="card-body">
                <form id="bankAccountForm">
                    @csrf
                    <input type="hidden" id="bankAccountId" name="id">

                    <div class="mb-3">
                        <label class="form-label">Account Type <span class="text-danger">*</span></label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="bank_type" value="commercial" id="bt_commercial" checked>
                            <label class="form-check-label" for="bt_commercial">Commercial Bank</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="bank_type" value="microfinance_wallet" id="bt_wallet">
                            <label class="form-check-label" for="bt_wallet">Wallet / Microfinance</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="bank_type" value="cash" id="bt_cash">
                            <label class="form-check-label" for="bt_cash">Cash in Hand</label>
                        </div>
                        <div class="invalid-feedback d-block" id="error-bank_type"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="bank_name" name="bank_name" placeholder="e.g. HBL, Meezan, Easypaisa">
                        <div class="invalid-feedback" id="error-bank_name"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Account Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="account_title" name="account_title" placeholder="e.g. Mount Carmel School">
                        <div class="invalid-feedback" id="error-account_title"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Account Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="account_number" name="account_number">
                        <div class="invalid-feedback" id="error-account_number"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">IBAN</label>
                        <input type="text" class="form-control form-control-sm" id="iban" name="iban" placeholder="PKXXMEZN00000XXXXXXXX">
                        <div class="invalid-feedback" id="error-iban"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Branch Name</label>
                        <input type="text" class="form-control form-control-sm" id="branch_name" name="branch_name">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Branch Code</label>
                        <input type="text" class="form-control form-control-sm" id="branch_code" name="branch_code">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Opening Balance ($)</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" id="current_balance" name="current_balance" value="0">
                        <div class="invalid-feedback" id="error-current_balance"></div>
                    </div>

                    <button type="button" class="btn btn-secondary btn-sm" id="btnResetForm">Reset</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btnSubmitBankAccount">Save</button>
                </form>
            </div>
        </div>
    </div>

    <!-- RIGHT: Listing -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Bank Accounts List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="viewDataTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>Bank Name</th>
                                <th>Account Title</th>
                                <th>Account No</th>
                                <th>Balance</th>
                                <th width="100">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bankAccounts as $bank)
                            <tr id="bankRow{{ $bank->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge {{ $bank->bank_type === 'commercial' ? 'bg-primary' : ($bank->bank_type === 'cash' ? 'bg-success' : 'bg-info') }}">
                                        {{ $bank->bank_type === 'commercial' ? 'Bank' : ($bank->bank_type === 'cash' ? 'Cash' : 'Wallet') }}
                                    </span>
                                </td>
                                <td>
                                    <a href="javascript:void(0)" class="fw-bold text-success bank-view-link"
                                        data-bank_type="{{ $bank->bank_type }}"
                                        data-bank_name="{{ $bank->bank_name }}"
                                        data-account_title="{{ $bank->account_title }}"
                                        data-account_number="{{ $bank->account_number }}"
                                        data-iban="{{ $bank->iban ?? '-' }}"
                                        data-branch_name="{{ $bank->branch_name ?? '-' }}"
                                        data-branch_code="{{ $bank->branch_code ?? '-' }}"
                                        data-current_balance="{{ number_format($bank->current_balance ?? 0, 2) }}"
                                        data-created_at="{{ $bank->created_at ? $bank->created_at->format('d-m-Y') : '-' }}">
                                        {{ $bank->bank_name }}
                                    </a>
                                </td>
                                <td>{{ $bank->account_title }}</td>
                                <td>{{ $bank->account_number }}</td>
                                <td>{{ number_format($bank->current_balance ?? 0, 2) }}</td>
                                <td>
                                    <button type="button" class="btn btn-outline-warning btn-sm btnEditBankAccount px-3"
                                        data-id="{{ $bank->id }}"
                                        data-bank_type="{{ $bank->bank_type }}"
                                        data-bank_name="{{ $bank->bank_name }}"
                                        data-account_title="{{ $bank->account_title }}"
                                        data-account_number="{{ $bank->account_number }}"
                                        data-iban="{{ $bank->iban }}"
                                        data-branch_name="{{ $bank->branch_name }}"
                                        data-branch_code="{{ $bank->branch_code }}"
                                        data-current_balance="{{ $bank->current_balance }}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm delete-btn px-3" data-id="{{ $bank->id }}">
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
    </div>
</div>

{{-- Modal Details View --}}
<div class="modal fade" id="bankDetailModal" tabindex="-1" aria-labelledby="bankDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white" id="bankDetailModalLabel">Bank Account Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                {{-- Balance highlight strip --}}
                <div class="card radius-10 border-0 mb-3" style="background:#e8f5e9;">
                    <div class="card-body py-2 d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Current Balance</span>
                        <h4 class="mb-0 text-success" id="modal_current_balance">Rs 0.00</h4>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <tbody>
                            <tr>
                                <th class="table-light" width="30%">Account Type</th>
                                <td id="modal_bank_type"></td>
                                <th class="table-light" width="30%">Bank Name</th>
                                <td id="modal_bank_name"></td>
                            </tr>
                            <tr>
                                <th class="table-light">Account Title</th>
                                <td id="modal_account_title"></td>
                                <th class="table-light">Account Number</th>
                                <td id="modal_account_number"></td>
                            </tr>
                            <tr>
                                <th class="table-light">IBAN</th>
                                <td id="modal_iban"></td>
                                <th class="table-light">Branch Name</th>
                                <td id="modal_branch_name"></td>
                            </tr>
                            <tr>
                                <th class="table-light">Branch Code</th>
                                <td id="modal_branch_code"></td>
                                <th class="table-light">Created On</th>
                                <td id="modal_created_at"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        $('#viewDataTable').DataTable({
            "pageLength": 10,
            "ordering": true
        });

        const $form = $('#bankAccountForm');
        const $btnSubmit = $('#btnSubmitBankAccount');
        const $formTitle = $('#formTitle');
        const $bankAccountId = $('#bankAccountId');

        function clearErrors() {
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.invalid-feedback').text('');
        }

        function resetForm() {
            $form[0].reset();
            $bankAccountId.val('');
            clearErrors();
            $formTitle.text('Add Bank Account');
            $btnSubmit.text('Save');
        }

        $('#btnResetForm').on('click', resetForm);

        $form.on('submit', function(e) {
            e.preventDefault();
            clearErrors();

            const id = $bankAccountId.val();
            const url = id ?
                "{{ route('bank_accounts.update', ':id') }}".replace(':id', id) :
                "{{ route('bank_accounts.store') }}";

            $.ajax({
                url: url,
                type: 'POST',
                data: $form.serialize(),
                success: function(res) {
                    toastr.success(res.message || 'Saved successfully.');
                    resetForm();
                    setTimeout(() => location.reload(), 800);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            $('#' + field).addClass('is-invalid');
                            $('#error-' + field).text(messages[0]);
                        });
                    } else {
                        toastr.error('Something went wrong.');
                    }
                }
            });
        });

        $(document).on('click', '.btnEditBankAccount', function() {
            const data = $(this).data();
            $bankAccountId.val(data.id);
            $('input[name=bank_type][value=' + data.bank_type + ']').prop('checked', true);
            $('#bank_name').val(data.bank_name);
            $('#account_title').val(data.account_title);
            $('#account_number').val(data.account_number);
            $('#iban').val(data.iban);
            $('#branch_name').val(data.branch_name);
            $('#branch_code').val(data.branch_code);
            $('#current_balance').val(data.current_balance);

            clearErrors();
            $formTitle.text('Edit Bank Account');
            $btnSubmit.text('Update');
            $('html, body').animate({
                scrollTop: 0
            }, 300);
        });

        $(document).on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            const $row = $('#bankRow' + id);

            Swal.fire({
                title: 'Are you sure?',
                text: "This bank account will be permanently deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('bank_accounts.delete', ':id') }}".replace(':id', id),
                        type: 'DELETE',
                        success: function(res) {
                            $row.fadeOut(300, function() {
                                $(this).remove();
                            });
                            toastr.success(res.message || 'Deleted successfully.');
                        },
                        error: function() {
                            toastr.error('Failed to delete. Please try again.');
                        }
                    });
                }
            });
        });

        // Bank detail view modal
        $(document).on('click', '.bank-view-link', function() {
            var d = $(this).data();

            var typeLabel = d.bank_type === 'commercial' ? 'Commercial Bank' :
                (d.bank_type === 'cash' ? 'Cash in Hand' : 'Wallet / Microfinance');

            $('#modal_bank_type').text(typeLabel);
            $('#modal_bank_name').text(d.bank_name);
            $('#modal_account_title').text(d.account_title);
            $('#modal_account_number').text(d.account_number);
            $('#modal_iban').text(d.iban || '-');
            $('#modal_branch_name').text(d.branch_name || '-');
            $('#modal_branch_code').text(d.branch_code || '-');
            $('#modal_current_balance').text('Rs ' + d.current_balance);
            $('#modal_created_at').text(d.created_at);

            new bootstrap.Modal(document.getElementById('bankDetailModal')).show();
        });
    });
</script>
@endpush