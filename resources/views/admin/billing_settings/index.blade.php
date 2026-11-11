@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Fees Collection</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Billing Settings</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl">
        <div class="card radius-10 billing-setting-card">
            <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 bg-transparent py-3">
                <h5 class="mb-0 text-primary">Billing Settings</h5>
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
                    <label for="billingSettingYear" class="form-label mb-0 text-nowrap">Academic Year</label>
                    <select id="billingSettingYear" class="form-select form-select-sm billing-setting-year">
                        @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ $academicYear && $academicYear->id == $year->id ? 'selected' : '' }}>
                            {{ $year->name }}{{ $year->is_current ? ' (Current)' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="card-body">
                @if(!$academicYear)
                <div class="alert alert-warning mb-0">No academic year found. Please add one first.</div>
                @else
                <form action="{{ route('billing_settings.update') }}" method="POST" id="billingSettingForm" novalidate>
                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">

                    <div class="row mb-3">
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <label class="form-label" for="fee_generate_day">Fee Generate Day <span class="text-danger">*</span></label>
                            <input type="number" min="1" max="28" name="fee_generate_day" id="fee_generate_day"
                                class="form-control form-control-sm @error('fee_generate_day') is-invalid @enderror"
                                value="{{ old('fee_generate_day', $billingSetting->fee_generate_day) }}" required>
                            @error('fee_generate_day')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="billing-setting-help">Monthly fees of a month are created from this day.</div>
                        </div>
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <label class="form-label" for="fee_due_day">Fee Due Day <span class="text-danger">*</span></label>
                            <input type="number" min="1" max="28" name="fee_due_day" id="fee_due_day"
                                class="form-control form-control-sm @error('fee_due_day') is-invalid @enderror"
                                value="{{ old('fee_due_day', $billingSetting->fee_due_day) }}" required>
                            @error('fee_due_day')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="billing-setting-help">Due date of each generated fee (day of that month).</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="salary_generate_day">Salary Generate Day <span class="text-danger">*</span></label>
                            <input type="number" min="1" max="28" name="salary_generate_day" id="salary_generate_day"
                                class="form-control form-control-sm @error('salary_generate_day') is-invalid @enderror"
                                value="{{ old('salary_generate_day', $billingSetting->salary_generate_day) }}" required>
                            @error('salary_generate_day')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="billing-setting-help">Slips of the previous month are created from this day.</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12 col-md-6 mb-3 mb-md-0">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="billingSettingAutoFees"
                                    name="auto_fees" value="1" {{ old('auto_fees', $billingSetting->auto_fees) ? 'checked' : '' }}>
                                <label class="form-check-label" for="billingSettingAutoFees">Auto generate student fees</label>
                            </div>
                            <label class="form-label" for="fees_from_month">Fees From Month</label>
                            <input type="month" name="fees_from_month" id="fees_from_month"
                                class="form-control form-control-sm @error('fees_from_month') is-invalid @enderror"
                                value="{{ old('fees_from_month', $billingSetting->fees_from_month) }}">
                            @error('fees_from_month')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="billing-setting-help">Empty = current month when the switch is turned on. Catch-up never goes earlier.</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="billingSettingAutoSalary"
                                    name="auto_salary" value="1" {{ old('auto_salary', $billingSetting->auto_salary) ? 'checked' : '' }}>
                                <label class="form-check-label" for="billingSettingAutoSalary">Auto generate salary slips</label>
                            </div>
                            <label class="form-label" for="salary_from_month">Salary From Month</label>
                            <input type="month" name="salary_from_month" id="salary_from_month"
                                class="form-control form-control-sm @error('salary_from_month') is-invalid @enderror"
                                value="{{ old('salary_from_month', $billingSetting->salary_from_month) }}">
                            @error('salary_from_month')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="billing-setting-help">Only completed months are generated, for active staff.</div>
                        </div>
                    </div>

                    <h6 class="text-primary mb-3">Vouchers</h6>
                    <div class="row mb-3">
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <label class="form-label" for="late_fee_amount">Late Fee (Rs.) <span class="text-danger">*</span></label>
                            <input type="number" min="0" step="0.01" name="late_fee_amount" id="late_fee_amount"
                                class="form-control form-control-sm @error('late_fee_amount') is-invalid @enderror"
                                value="{{ old('late_fee_amount', $billingSetting->late_fee_amount) }}" required>
                            @error('late_fee_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="billing-setting-help">One fixed fine per student per month after the due date. 0 = no late fee.</div>
                        </div>
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <label class="form-label" for="scholarship_category_id">Scholarship Category</label>
                            <select name="scholarship_category_id" id="scholarship_category_id" class="form-select form-select-sm @error('scholarship_category_id') is-invalid @enderror">
                                <option value="">None</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('scholarship_category_id', $billingSetting->scholarship_category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('scholarship_category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="billing-setting-help">Discounts of this category cannot be combined with a sibling discount.</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="voucher_bank_account_id">Voucher Bank</label>
                            <select name="voucher_bank_account_id" id="voucher_bank_account_id" class="form-select form-select-sm @error('voucher_bank_account_id') is-invalid @enderror">
                                <option value="">No bank (2 copies, pay at school office)</option>
                                @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}" {{ old('voucher_bank_account_id', $billingSetting->voucher_bank_account_id) == $bank->id ? 'selected' : '' }}>{{ $bank->bank_name }} - {{ $bank->account_title }}</option>
                                @endforeach
                            </select>
                            @error('voucher_bank_account_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="billing-setting-help">Bank selected = 3 copies (Student / School / Bank).</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label" for="voucher_note">Voucher Note</label>
                            <textarea name="voucher_note" id="voucher_note" rows="2" maxlength="1000"
                                class="form-control form-control-sm @error('voucher_note') is-invalid @enderror">{{ old('voucher_note', $billingSetting->voucher_note) }}</textarea>
                            @error('voucher_note')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h6 class="text-primary mb-3">1Bill</h6>
                    <div class="row mb-3">
                        <div class="col-12 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="billingSettingOnebill"
                                    name="onebill_enabled" value="1" {{ old('onebill_enabled', $billingSetting->onebill_enabled) ? 'checked' : '' }}>
                                <label class="form-check-label" for="billingSettingOnebill">Enable 1Bill</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label" for="onebill_bank">Bank</label>
                            <input type="text" name="onebill_bank" id="onebill_bank" maxlength="100"
                                class="form-control form-control-sm @error('onebill_bank') is-invalid @enderror"
                                value="{{ old('onebill_bank', $billingSetting->onebill_bank) }}">
                            @error('onebill_bank')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label" for="onebill_biller_code">Biller Code</label>
                            <input type="text" name="onebill_biller_code" id="onebill_biller_code" maxlength="100"
                                class="form-control form-control-sm @error('onebill_biller_code') is-invalid @enderror"
                                value="{{ old('onebill_biller_code', $billingSetting->onebill_biller_code) }}">
                            @error('onebill_biller_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @foreach(['onebill_username' => 'Username', 'onebill_password' => 'Password', 'onebill_api_key' => 'API Key'] as $secretField => $secretLabel)
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <label class="form-label" for="{{ $secretField }}">{{ $secretLabel }}</label>
                            <div class="input-group input-group-sm">
                                <input type="password" name="{{ $secretField }}" id="{{ $secretField }}" maxlength="255" autocomplete="new-password"
                                    class="form-control form-control-sm @error($secretField) is-invalid @enderror" value="">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="{{ $secretField }}">
                                    <i class="bx bx-hide" id="{{ $secretField }}_icon"></i>
                                </button>
                            </div>
                            @error($secretField)
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @if($onebillStatus[$secretField] === 'saved')
                            <div class="billing-setting-help">Saved. Leave blank to keep it.</div>
                            @elseif($onebillStatus[$secretField] === 'unreadable')
                            <div class="billing-setting-help text-danger">Saved value cannot be read on this site (different app key). Enter it again.</div>
                            @else
                            <div class="billing-setting-help">Not set.</div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    @can('manage-billing-settings')
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="bx bx-check"></i> Save
                    </button>
                    @endcan
                </form>
                @endif
            </div>
        </div>

        @can('manage-billing-settings')
        @if($academicYear && $billingSetting->exists)
        <div class="card radius-10 billing-setting-card">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0 text-primary">Generate Now</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-6 mb-4 mb-md-0 billing-setting-block">
                        <h6><label for="billingSettingFeesMonth" class="mb-0">Student Fees</label></h6>
                        <div class="d-flex gap-2 mb-2">
                            <select id="billingSettingFeesMonth" class="form-select form-select-sm">
                                @foreach($feeMonths as $m)
                                <option value="{{ $m }}" {{ $m == $feeMonthDefault ? 'selected' : '' }}>{{ \Carbon\Carbon::parse($m . '-01')->format('F Y') }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-success btn-sm text-nowrap billing-setting-generate" data-type="fees">Generate Now</button>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm billing-setting-catch-up" data-type="fees">Catch up missed months</button>
                    </div>
                    <div class="col-12 col-md-6 billing-setting-block">
                        <h6><label for="billingSettingSalaryMonth" class="mb-0">Salary Slips</label></h6>
                        <div class="d-flex gap-2 mb-2">
                            <select id="billingSettingSalaryMonth" class="form-select form-select-sm">
                                @foreach($salaryMonths as $m)
                                <option value="{{ $m }}" {{ $m == $salaryMonthDefault ? 'selected' : '' }}>{{ \Carbon\Carbon::parse($m . '-01')->format('F Y') }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-success btn-sm text-nowrap billing-setting-generate" data-type="salary">Generate Now</button>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm billing-setting-catch-up" data-type="salary">Catch up missed months</button>
                    </div>
                </div>
                <div class="alert alert-info py-2 mt-3 mb-0">
                    <i class="bx bx-info-circle"></i>
                    Running a month twice creates nothing new. One-time fees are never generated here.
                </div>
            </div>
        </div>
        @else
        @if($academicYear)
        <div class="alert alert-info">Save the settings once to enable Generate Now for this academic year.</div>
        @endif
        @endif
        @endcan
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const billingSettingIndexUrl = @json(route('billing_settings.index'));
        const billingSettingGenerateUrl = @json(route('billing_settings.generate_now'));
        const billingSettingCatchUpUrl = @json(route('billing_settings.catch_up'));
        const billingSettingUnmarkedUrl = @json(route('billing_settings.unmarked_days'));
        const billingSettingYearId = @json($academicYear ? $academicYear->id : null);

        @if(session('success'))
        toastr.success(@json(session('success')));
        @endif

        @if($errors->any())
        toastr.error(@json($errors->first()), 'Validation Error');
        @endif

        // Field edit hote hi uski validation state hata do
        $('#billingSettingForm').on('input change', '.is-invalid', function() {
            $(this).removeClass('is-invalid').siblings('.invalid-feedback').hide();
            $(this).closest('.input-group').siblings('.invalid-feedback').hide(); // eye toggle wale input-group ke bahar ka error
        });

        // Password show/hide eye toggle (student edit page jaisa): sirf us input ka type badalta hai
        $('.toggle-password').on('click', function() {
            var target = $('#' + $(this).data('target'));
            var icon = $(this).find('i');

            if (target.attr('type') === 'password') {
                target.attr('type', 'text');
                icon.removeClass('bx-hide').addClass('bx-show');
            } else {
                target.attr('type', 'password');
                icon.removeClass('bx-show').addClass('bx-hide');
            }
        });

        $('#billingSettingYear').on('change', function() {
            window.location.href = billingSettingIndexUrl + '?academic_year_id=' + $(this).val();
        });

        function billingSettingMonthSelect(type) {
            return type === 'fees' ? $('#billingSettingFeesMonth') : $('#billingSettingSalaryMonth');
        }

        function billingSettingPost(url, data) {
            $.post(url, data).done(function(res) {
                toastr.success(res.message);
            }).fail(function(xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Something went wrong.';
                toastr.error(message);
            });
        }

        function billingSettingConfirm(text, html, onConfirm) {
            Swal.fire({
                title: 'Are you sure?',
                text: text,
                html: html,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Generate',
                cancelButtonText: 'Cancel',
                buttonsStyling: false,
                customClass: {
                    popup: 'fee-assignment-swal',
                    confirmButton: 'btn btn-success btn-sm',
                    cancelButton: 'btn btn-secondary btn-sm'
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    onConfirm();
                }
            });
        }

        $('.billing-setting-generate').on('click', function() {
            const type = $(this).data('type');
            const month = billingSettingMonthSelect(type).val();
            if (!month) {
                toastr.error('The Month field is required.');
                return;
            }
            const data = { academic_year_id: billingSettingYearId, type: type, month: month };
            const text = type === 'fees'
                ? 'Student fees and fee vouchers for the selected month will be generated. Existing ones are skipped.'
                : 'Salary slips for the selected month will be generated for active staff.';

            if (type === 'fees') {
                billingSettingConfirm(text, undefined, function() {
                    billingSettingPost(billingSettingGenerateUrl, data);
                });
                return;
            }

            // Salary: pehle us month ke un-marked attendance din dikhao
            $.get(billingSettingUnmarkedUrl, { month: month }, function(res) {
                let warning;
                if (res.days.length > 0) {
                    warning = '<p>' + text + '</p>' +
                        '<p class="text-danger mb-1">' + res.headline + '</p>' +
                        '<p class="text-danger mb-0">' + res.dates + '</p>';
                }
                billingSettingConfirm(text, warning, function() {
                    billingSettingPost(billingSettingGenerateUrl, data);
                });
            }).fail(function(xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Could not check unmarked attendance days. Please try again.';
                toastr.error(message);
            });
        });

        $('.billing-setting-catch-up').on('click', function() {
            const type = $(this).data('type');
            const text = 'All months that are due since the From Month will be generated. Existing ones are skipped.';
            billingSettingConfirm(text, undefined, function() {
                billingSettingPost(billingSettingCatchUpUrl, { academic_year_id: billingSettingYearId, type: type });
            });
        });
    });
</script>
@endpush
