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
                            <label class="form-label">Fee Generate Day <span class="text-danger">*</span></label>
                            <input type="number" min="1" max="28" name="fee_generate_day"
                                class="form-control form-control-sm @error('fee_generate_day') is-invalid @enderror"
                                value="{{ old('fee_generate_day', $billingSetting->fee_generate_day) }}" required>
                            @error('fee_generate_day')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="billing-setting-help">Monthly fees of a month are created from this day.</div>
                        </div>
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <label class="form-label">Fee Due Day <span class="text-danger">*</span></label>
                            <input type="number" min="1" max="28" name="fee_due_day"
                                class="form-control form-control-sm @error('fee_due_day') is-invalid @enderror"
                                value="{{ old('fee_due_day', $billingSetting->fee_due_day) }}" required>
                            @error('fee_due_day')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="billing-setting-help">Due date of each generated fee (day of that month).</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Salary Generate Day <span class="text-danger">*</span></label>
                            <input type="number" min="1" max="28" name="salary_generate_day"
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
                            <label class="form-label">Fees From Month</label>
                            <input type="month" name="fees_from_month"
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
                            <label class="form-label">Salary From Month</label>
                            <input type="month" name="salary_from_month"
                                class="form-control form-control-sm @error('salary_from_month') is-invalid @enderror"
                                value="{{ old('salary_from_month', $billingSetting->salary_from_month) }}">
                            @error('salary_from_month')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="billing-setting-help">Only completed months are generated, for active staff.</div>
                        </div>
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
                        <h6>Student Fees</h6>
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
                        <h6>Salary Slips</h6>
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
                ? 'Student fees for the selected month will be generated. Existing ones are skipped.'
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
