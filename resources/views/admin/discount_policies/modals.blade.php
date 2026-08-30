{{-- 1. ADD DISCOUNT POLICY MODAL --}}
<div class="modal fade" id="discountPolicyAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Add Discount Policy</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="discountPolicyForm" method="POST" action="{{ route('discount_policies.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="name">Policy Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control form-control-sm @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Sibling 2nd Child Discount">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="policy_type">Policy Type <span class="text-danger">*</span></label>
                            <select name="policy_type" id="policy_type" class="form-control form-control-sm @error('policy_type') is-invalid @enderror" onchange="togglePolicyFields('policy_type', 'category_id_container', 'trigger_value_container')">
                                <option value="">-- Select --</option>
                                <option value="category" {{ old('policy_type') == 'category' ? 'selected' : '' }}>Category Based (Staff/Scholarship/etc)</option>
                                <option value="sibling" {{ old('policy_type') == 'sibling' ? 'selected' : '' }}>Sibling</option>
                                <option value="other" {{ old('policy_type') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('policy_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6" id="category_id_container" style="display:{{ old('policy_type') == 'category' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold" for="category_id">Student Category</label>
                            <select name="category_id" id="category_id" class="form-control form-control-sm @error('category_id') is-invalid @enderror">
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6" id="trigger_value_container" style="display:{{ old('policy_type') == 'sibling' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold" for="trigger_value">Trigger Value (Child Number)</label>
                            <input type="number" name="trigger_value" id="trigger_value" class="form-control form-control-sm @error('trigger_value') is-invalid @enderror" value="{{ old('trigger_value') }}" placeholder="e.g. 2 (2nd child)" min="1">
                            @error('trigger_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="discount_type">Discount Type <span class="text-danger">*</span></label>
                            <select name="discount_type" id="discount_type" class="form-control form-control-sm @error('discount_type') is-invalid @enderror">
                                <option value="">-- Select --</option>
                                <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount (Rs)</option>
                            </select>
                            @error('discount_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="discount_value">Discount Value <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="discount_value" id="discount_value" class="form-control form-control-sm @error('discount_value') is-invalid @enderror" value="{{ old('discount_value') }}" placeholder="e.g. 20">
                            @error('discount_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="fee_type_id">Applies To Fee Type</label>
                            <select name="fee_type_id" id="fee_type_id" class="form-control form-control-sm @error('fee_type_id') is-invalid @enderror">
                                <option value="">-- All Fee Types --</option>
                                @foreach($feeTypes as $feeType)
                                <option value="{{ $feeType->id }}" {{ old('fee_type_id') == $feeType->id ? 'selected' : '' }}>{{ $feeType->name }}</option>
                                @endforeach
                            </select>
                            @error('fee_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
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

{{-- 2. EDIT DISCOUNT POLICY MODALS --}}
@foreach($policies as $policy)
<div class="modal fade" id="discountPolicyEdit-{{ $policy->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Discount Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editDiscountForm-{{ $policy->id }}" method="POST" action="{{ route('discount_policies.update', $policy->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Policy Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ $policy->name }}" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Policy Type <span class="text-danger">*</span></label>
                            <select name="policy_type" id="edit_policy_type_{{ $policy->id }}" class="form-control form-control-sm" onchange="togglePolicyFields('edit_policy_type_{{ $policy->id }}', 'edit_category_container_{{ $policy->id }}', 'edit_trigger_container_{{ $policy->id }}')">
                                <option value="category" {{ $policy->policy_type == 'category' ? 'selected' : '' }}>Category Based</option>
                                <option value="sibling" {{ $policy->policy_type == 'sibling' ? 'selected' : '' }}>Sibling</option>
                                <option value="other" {{ $policy->policy_type == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="edit_category_container_{{ $policy->id }}" style="display:{{ $policy->policy_type == 'category' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold">Student Category</label>
                            <select name="category_id" class="form-control form-control-sm">
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $policy->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6" id="edit_trigger_container_{{ $policy->id }}" style="display:{{ $policy->policy_type == 'sibling' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold">Trigger Value (Child Number)</label>
                            <input type="number" name="trigger_value" value="{{ $policy->trigger_value }}" class="form-control form-control-sm" min="1">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Discount Type <span class="text-danger">*</span></label>
                            <select name="discount_type" class="form-control form-control-sm">
                                <option value="percentage" {{ $policy->discount_type == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed" {{ $policy->discount_type == 'fixed' ? 'selected' : '' }}>Fixed Amount (Rs)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Discount Value <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="discount_value" value="{{ $policy->discount_value }}" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Applies To Fee Type</label>
                            <select name="fee_type_id" class="form-control form-control-sm">
                                <option value="">-- All Fee Types --</option>
                                @foreach($feeTypes as $feeType)
                                <option value="{{ $feeType->id }}" {{ $policy->fee_type_id == $feeType->id ? 'selected' : '' }}>{{ $feeType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm"><i class="bx bx-save me-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    function togglePolicyFields(selectId, categoryContainerId, triggerContainerId) {
        let val = document.getElementById(selectId).value;
        document.getElementById(categoryContainerId).style.display = val === 'category' ? 'block' : 'none';
        document.getElementById(triggerContainerId).style.display = val === 'sibling' ? 'block' : 'none';
    }

    $(document).ready(function() {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // Add Form AJAX
        $('#discountPolicyForm').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    $('#discountPolicyAdd').modal('hide');
                    toastr.success(response.success || "Added successfully!");
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        showValidationErrors(form, xhr.responseJSON.errors);
                    } else {
                        toastr.error("An unexpected error occurred!");
                    }
                }
            });
        });

        // Edit Form AJAX
        $(document).on('submit', '[id^="editDiscountForm-"]', function(e) {
            e.preventDefault();
            let form = $(this);
            let id = form.attr('id').replace('editDiscountForm-', '');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    $('#discountPolicyEdit-' + id).modal('hide');
                    toastr.success(response.message || "Updated successfully!");
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        showValidationErrors(form, xhr.responseJSON.errors);
                    } else {
                        toastr.error("Update failed!");
                    }
                }
            });
        });

        function showValidationErrors(form, errors) {
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').remove();
            let isFirst = true;
            $.each(errors, function(key, messages) {
                let field = form.find('[name="' + key + '"]');
                if (field.length) {
                    field.addClass('is-invalid');
                    field.after('<div class="invalid-feedback d-block">' + messages[0] + '</div>');
                    if (isFirst) {
                        field.focus();
                        toastr.error(messages[0]);
                        isFirst = false;
                    }
                }
            });
        }

        $(document).on('hidden.bs.modal', '.modal', function() {
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').remove();
        });
    });
</script>
@endpush