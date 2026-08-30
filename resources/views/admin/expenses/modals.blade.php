{{-- Add Expense Modal --}}
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addExpenseForm">
                @csrf
                <div class="modal-header bg-success">
                    <h5 class="modal-title"><i class="bx bx-plus-circle me-1"></i> Add Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_expense_category" class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="expense_category_id" id="add_expense_category" class="form-select form-select-sm">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="add_expense_title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="add_expense_title" class="form-control form-control-sm">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="add_expense_description" class="form-label">Description</label>
                        <textarea name="description" id="add_expense_description" class="form-control form-control-sm" rows="2"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="add_expense_amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" id="add_expense_amount" class="form-control form-control-sm">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="add_expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                        <input type="date" name="expense_date" id="add_expense_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Expense Modal --}}
<div class="modal fade" id="editExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editExpenseForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_expense_id">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bx bx-edit me-1"></i> Edit Expense</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_expense_category" class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="expense_category_id" id="edit_expense_category" class="form-select form-select-sm">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_expense_title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="edit_expense_title" class="form-control form-control-sm">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_expense_description" class="form-label">Description</label>
                        <textarea name="description" id="edit_expense_description" class="form-control form-control-sm" rows="2"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_expense_amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" id="edit_expense_amount" class="form-control form-control-sm">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                        <input type="date" name="expense_date" id="edit_expense_date" class="form-control form-control-sm">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Pay Modal --}}
<div class="modal fade" id="payExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="payExpenseForm">
                @csrf
                <input type="hidden" name="expense_id" id="pay_expense_id">
                <div class="modal-header bg-info">
                    <h5 class="modal-title"><i class="bx bx-money me-1"></i> Pay Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Paying for: <strong id="pay_title_display"></strong></p>
                    <p class="mb-3">Amount: <span class="badge bg-warning fs-6 text-dark" id="pay_amount_display"></span></p>
                    <div class="mb-3">
                        <label for="pay_bank_account" class="form-label">Bank Account <span class="text-danger">*</span></label>
                        <select name="bank_account_id" id="pay_bank_account" class="form-select form-select-sm">
                            <option value="">-- Select Account --</option>
                            @foreach($bankAccounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->bank_name }} (Balance: {{ number_format($acc->current_balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="pay_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" id="pay_method" class="form-select form-select-sm">
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="easypaisa">Easypaisa</option>
                            <option value="jazzcash">JazzCash</option>
                            <option value="card">Card</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">Mark as Paid</button>
                </div>
            </form>
        </div>
    </div>
</div>