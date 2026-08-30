@extends($current_layout)

@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Accountant Management</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Accountant</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card shadow-none border">
    <div class="card-body">
        {{-- Updated Form Action and Method for Edit --}}
        <form action="{{ route('accountant.update', $accountant->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="role" value="accountant">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 text-primary">Edit Accountant Information</h5>
                <a href="{{ route('accountant.index') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-list-ul"></i> Accountant List</a>
            </div>
            <hr />

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="joining_date" class="form-label">Date of Joining</label>
                    <input type="date" name="joining_date" id="joining_date" class="form-control"
                        value="{{ old('joining_date', $accountant->joining_date ? \Carbon\Carbon::parse($accountant->joining_date)->format('Y-m-d') : '') }}">
                </div>

                <div class="col-md-3">
                    <label for="accountant_id" class="form-label">Accountant ID *</label>
                    <input type="text" name="accountant_id" id="accountant_id_field" class="form-control" value="{{ old('accountant_id', $accountant->accountant_id) }}" readonly>
                </div>

                <div class="col-md-3">
                    <label for="first_name" class="form-label">First Name *</label>
                    <input type="text" name="first_name" id="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $accountant->first_name) }}">
                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="last_name" class="form-label">Last Name *</label>
                    <input type="text" name="last_name" id="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $accountant->last_name) }}">
                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="father_name" class="form-label">Father Name *</label>
                    <input type="text" name="father_name" id="father_name" class="form-control @error('father_name') is-invalid @enderror" value="{{ old('father_name', $accountant->father_name) }}">
                    @error('father_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="mother_name" class="form-label">Mother Name</label>
                    <input type="text" name="mother_name" id="mother_name" class="form-control" value="{{ old('mother_name', $accountant->mother_name) }}">
                </div>
                <div class="col-md-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $accountant->email) }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="cnic" class="form-label">CNIC *</label>
                    <input type="text" name="cnic" id="cnic" class="form-control @error('cnic') is-invalid @enderror" value="{{ old('cnic', $accountant->cnic) }}" placeholder="xxxxx-xxxxxxx-x">
                    @error('cnic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="gender" class="form-label">Gender *</label>
                    <select name="gender" id="gender" class="form-select">
                        <option value="male" {{ old('gender', $accountant->gender) == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $accountant->gender) == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="dob" class="form-label">Date of Birth *</label>
                    <input type="date" name="dob" id="dob" class="form-control"
                        value="{{ old('dob', $accountant->dob ? \Carbon\Carbon::parse($accountant->dob)->format('Y-m-d') : '') }}">
                    @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="phone" class="form-label">Phone *</label>
                    <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $accountant->phone) }}" placeholder="03xx-xxxxxxx">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="marital_status" class="form-label">Marital Status</label>
                    <select name="marital_status" id="marital_status" class="form-select">
                        <option value="single" {{ old('marital_status', $accountant->marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                        <option value="married" {{ old('marital_status', $accountant->marital_status) == 'married' ? 'selected' : '' }}>Married</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-secondary small fw-semibold">Photo</label>
                    <div class="d-flex align-items-center gap-2">
                        <div class="flex-grow-1">
                            <input type="file" name="photo" id="photo" class="form-control" accept="image/*" onchange="previewImage(this)">
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Max size 2MB</small>
                        </div>
                        <div class="avatar-preview-wrapper border rounded bg-white p-1" style="width: 100px; height: 100px;">
                            {{-- Check if photo exists in database, use image_ae0f08.png logic if needed --}}
                            <img id="imagePreview"
                                src="{{ !empty($accountant->photo) ? asset('uploads/accountants/'.$accountant->photo) : asset('uploads/no_image.jpg') }}"
                                alt="Preview"
                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="work_experience" class="form-label">Work Experience</label>
                    <input type="text" name="work_experience" id="work_experience" class="form-control" value="{{ old('work_experience', $accountant->work_experience) }}">
                </div>
                <div class="col-md-4">
                    <label for="qualification" class="form-label">Qualification</label>
                    <input type="text" name="qualification" id="qualification" class="form-control" value="{{ old('qualification', $accountant->qualification) }}">
                </div>
                <div class="col-md-6">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" name="address" id="address" class="form-control" value="{{ old('address', $accountant->address) }}">
                </div>
                <div class="col-md-6">
                    <label for="permanent_address" class="form-label">Permanent Address</label>
                    <input type="text" name="permanent_address" id="permanent_address" class="form-control" value="{{ old('permanent_address', $accountant->permanent_address) }}">
                </div>
            </div>

            {{-- END-PART 1: BASIC INFORMATION --}}


            <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-4 d-flex justify-content-between align-items-center px-3" data-bs-toggle="collapse" data-bs-target="#moreDetails">
                <span>Add More Details</span> <i class="bx bx-plus"></i>
            </button>

            <div class="collapse" id="moreDetails">
                <div class="card p-4 mb-4 shadow-sm">

                    {{-- EMERGENCY CONTACT INFORMATION --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <h6 class="text-primary border-bottom pb-2 mt-3">Emergency Contact Information</h6>
                        </div>

                        {{-- Emergency Name --}}
                        <div class="col-md-4">
                            <label for="emergency_name" class="form-label">Person Name</label>
                            <input type="text" name="emergency_name" id="emergency_name" class="form-control @error('emergency_name') is-invalid @enderror" value="{{ old('emergency_name', $accountant->emergency_name) }}" placeholder="Enter name">
                            @error('emergency_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Emergency Relation --}}
                        <div class="col-md-4">
                            <label for="emergency_relation" class="form-label">Relation</label>
                            <select name="emergency_relation" id="emergency_relation" class="form-select">
                                <option value="">Select Relation</option>
                                <option value="father" {{ old('emergency_relation', $accountant->emergency_relation) == 'father' ? 'selected' : '' }}>Father</option>
                                <option value="mother" {{ old('emergency_relation', $accountant->emergency_relation) == 'mother' ? 'selected' : '' }}>Mother</option>
                                <option value="spouse" {{ old('emergency_relation', $accountant->emergency_relation) == 'spouse' ? 'selected' : '' }}>Spouse</option>
                                <option value="brother" {{ old('emergency_relation', $accountant->emergency_relation) == 'brother' ? 'selected' : '' }}>Brother</option>
                                <option value="friend" {{ old('emergency_relation', $accountant->emergency_relation) == 'friend' ? 'selected' : '' }}>Friend</option>
                                <option value="other" {{ old('emergency_relation', $accountant->emergency_relation) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        {{-- Emergency Phone --}}
                        <div class="col-md-4">
                            <label for="emergency_phone" class="form-label">Phone Number</label>
                            <input type="tel" name="emergency_phone" id="emergency_phone" class="form-control @error('emergency_phone') is-invalid @enderror" value="{{ old('emergency_phone', $accountant->emergency_phone) }}" placeholder="03xx-xxxxxxx">
                            @error('emergency_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    {{-- PAYROLL SECTION --}}
                    <div class="row mb-2">
                        <div class="col-12">
                            <h5 class="text-primary border-bottom pb-2">Payroll</h5>
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="basic_salary" class="form-label">Basic Salary *</label>
                            {{-- $accountant->salary (Jo aapne Controller mein pass kiya hai) --}}
                            <input type="number" name="basic_salary" id="basic_salary"
                                class="form-control @error('basic_salary') is-invalid @enderror"
                                value="{{ old('basic_salary', optional($accountant->salary)->basic_salary) }}">
                            @error('basic_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="contract_type" class="form-label">Contract Type</label>
                            <select name="contract_type" id="contract_type" class="form-select">
                                <option value="permanent" {{ old('contract_type', $accountant->contract_type) == 'permanent' ? 'selected' : '' }}>Permanent</option>
                                <option value="contract" {{ old('contract_type', $accountant->contract_type) == 'contract' ? 'selected' : '' }}>Contract</option>
                            </select>
                        </div>
                        <div class="col-md-4"><label for="work_shift" class="form-label">Work Shift</label><input type="text" name="work_shift" id="work_shift" class="form-control" value="{{ old('work_shift', $accountant->work_shift) }}"></div>
                    </div>



                    {{-- NEW UPDATED BANK DETAILS SECTION --}}
                    <div class="row mb-2">
                        <div class="col-12">
                            <h5 class="text-primary border-bottom pb-2">Bank Details</h5>
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        {{-- Sahi Bootstrap Classes k Sath Bank Type Radio Buttons --}}
                        <div class="col-md-12 mb-2">
                            <label class="form-check-label d-block fw-bold mb-2">Bank Account Type *</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bank_type" id="bank_type_commercial" value="commercial" {{ old('bank_type', $bankDetail->bank_type ?? '') == 'commercial' ? 'checked' : '' }}>
                                <label class="form-check-label" for="bank_type_commercial">Commercial Bank (HBL, Meezan, etc.)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bank_type" id="bank_type_wallet" value="microfinance_wallet" {{ old('bank_type', $bankDetail->bank_type ?? '') == 'microfinance_wallet' ? 'checked' : '' }}>
                                <label class="form-check-label" for="bank_type_wallet">Digital Wallet / Microfinance (Easypaisa, JazzCash, etc.)</label>
                            </div>
                        </div>

                        {{-- Account Title --}}
                        <div class="col-md-4">
                            <label for="account_title" class="form-label">Account Title</label>
                            <input type="text" name="account_title" id="account_title" class="form-control @error('account_title') is-invalid @enderror" value="{{ old('account_title', $bankDetail->account_title ?? '') }}" placeholder="e.g., John Doe">
                            @error('account_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Account / Mobile Number --}}
                        <div class="col-md-4">
                            <label for="account_number" id="acc_no_label" class="form-label">Account Number</label>
                            <input type="text" name="account_number" id="account_number" class="form-control @error('account_number') is-invalid @enderror" value="{{ old('account_number', $bankDetail->account_number ?? '') }}" placeholder="Account or Mobile No">
                            @error('account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Bank Name Dropdown --}}
                        <div class="col-md-4">
                            <label for="bank_name" class="form-label">Bank Name</label>
                            <select name="bank_name" id="bank_name" class="form-select @error('bank_name') is-invalid @enderror">
                                <option value="">Select Bank / Wallet</option>
                                <optgroup label="Commercial Banks" id="opt_commercial">
                                    <option value="Meezan Bank" {{ old('bank_name', $bankDetail->bank_name ?? '') == 'Meezan Bank' ? 'selected' : '' }}>Meezan Bank</option>
                                    <option value="Habib Bank Limited (HBL)" {{ old('bank_name', $bankDetail->bank_name ?? '') == 'Habib Bank Limited (HBL)' ? 'selected' : '' }}>Habib Bank Limited (HBL)</option>
                                    <option value="United Bank Limited (UBL)" {{ old('bank_name', $bankDetail->bank_name ?? '') == 'United Bank Limited (UBL)' ? 'selected' : '' }}>United Bank Limited (UBL)</option>
                                    <option value="Bank Alfalah" {{ old('bank_name', $bankDetail->bank_name ?? '') == 'Bank Alfalah' ? 'selected' : '' }}>Bank Alfalah</option>
                                    <option value="Allied Bank Limited (ABL)" {{ old('bank_name', $bankDetail->bank_name ?? '') == 'Allied Bank Limited (ABL)' ? 'selected' : '' }}>Allied Bank Limited (ABL)</option>
                                    <option value="Faysal Bank" {{ old('bank_name', $bankDetail->bank_name ?? '') == 'Faysal Bank' ? 'selected' : '' }}>Faysal Bank</option>
                                </optgroup>
                                <optgroup label="Wallets & Microfinance" id="opt_wallet">
                                    <option value="Easypaisa" {{ old('bank_name', $bankDetail->bank_name ?? '') == 'Easypaisa' ? 'selected' : '' }}>Easypaisa</option>
                                    <option value="JazzCash" {{ old('bank_name', $bankDetail->bank_name ?? '') == 'JazzCash' ? 'selected' : '' }}>JazzCash</option>
                                    <option value="SadaPay" {{ old('bank_name', $bankDetail->bank_name ?? '') == 'SadaPay' ? 'selected' : '' }}>SadaPay</option>
                                    <option value="NayaPay" {{ old('bank_name', $bankDetail->bank_name ?? '') == 'NayaPay' ? 'selected' : '' }}>NayaPay</option>
                                </optgroup>
                            </select>
                            @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Extra Fields Wrapper for Toggle --}}
                        <div class="col-md-4 commercial-field">
                            <label for="iban" class="form-label">IBAN</label>
                            <input type="text" name="iban" id="iban" class="form-control" value="{{ old('iban') }}" placeholder="PKXXMEZN00000XXXXXXXXX">
                        </div>
                        <div class="col-md-4 commercial-field">
                            <label for="branch_name" class="form-label">Branch Name</label>
                            <input type="text" name="branch_name" id="branch_name" class="form-control" value="{{ old('branch_name') }}">
                        </div>
                        <div class="col-md-4 commercial-field">
                            <label for="branch_code" class="form-label">Branch Code</label>
                            <input type="text" name="branch_code" id="branch_code" class="form-control" value="{{ old('branch_code') }}">
                        </div>
                    </div>

                    {{-- SOCIAL MEDIA SECTION --}}
                    <div class="row mb-2">
                        <div class="col-12">
                            <h5 class="text-primary border-bottom pb-2">Social Media</h5>
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6"><input type="text" name="fb_url" class="form-control" placeholder="Facebook URL" value="{{ old('fb_url') }}"></div>
                        <div class="col-md-6"><input type="text" name="twitter_url" class="form-control" placeholder="Twitter URL" value="{{ old('twitter_url') }}"></div>
                        <div class="col-md-6"><input type="text" name="linkedin_url" class="form-control" placeholder="LinkedIn URL" value="{{ old('linkedin_url') }}"></div>
                        <div class="col-md-6"><input type="text" name="insta_url" class="form-control" placeholder="Instagram URL" value="{{ old('insta_url') }}"></div>
                    </div>

                    {{-- DOCUMENTS SECTION --}}
                    <div class="row mb-2">
                        <div class="col-12">
                            <h5 class="text-primary border-bottom pb-2">Documents</h5>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6"><label for="doc_resume" class="form-label">Resume</label><input type="file" name="doc_resume" id="doc_resume" class="form-control"></div>
                        <div class="col-md-6"><label for="doc_joining" class="form-label">Joining Letter</label><input type="file" name="doc_joining" id="doc_joining" class="form-control"></div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-success px-4">Update Accountant Profile</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // --- 1. Accountant ID Auto-generation ---
        $('#last_name').on('blur', function() {
            const fName = $('#first_name').val();
            const lName = $(this).val();
            const idField = $('#accountant_id_field');

            if (fName && lName) {
                idField.val("Generating...");

                $.ajax({
                    url: "{{ route('accountant.get-accountant-id') }}",
                    type: 'GET',
                    data: {
                        first_name: fName,
                        last_name: lName
                    },
                    success: function(res) {
                        if (res.accountant_id) {
                            idField.val(res.accountant_id);
                        }
                    },
                    error: function() {
                        idField.val("");
                        toastr.error('Could not generate ID');
                    }
                });
            }
        });
        // --- AJAX Form Submission ke shuru mein ---
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "preventDuplicates": true, // Ye ek jaise multiple notifications ko rokega
            "positionClass": "toast-top-right"
        };
        // --- 2. AJAX Form Submission ---
        $('.btn-success').on('click', function(e) {
            e.preventDefault();

            let btn = $(this); // Button reference
            let form = btn.closest('form');
            let formData = new FormData(form[0]);

            // Button ko disable karein taake double submit na ho
            btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    toastr.success('Accountant added successfully!');
                    form[0].reset();

                    // Image preview aur ID field ko manually clear karein
                    $('#imagePreview').attr('src', '{{ asset("default-avatar.png") }}'); // Apni default image path dein
                    $('#accountant_id_field').val("");

                    window.location.href = "{{ route('accountant.index') }}";
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Save Accountant Profile'); // Button enable karein

                    $('.form-control').removeClass('is-invalid');
                    $('.invalid-feedback').remove();

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        toastr.error("Please check the highlighted fields.");

                        $.each(errors, function(key, value) {
                            let field = $('[name="' + key + '"]');
                            field.addClass('is-invalid');
                            field.after('<div class="invalid-feedback" style="display:block;">' + value[0] + '</div>');
                        });
                    } else {
                        toastr.error('Server Error: ' + xhr.status);
                    }
                }
            });
        });

        // --- 3. UI Helpers ---
        $('#accountant_id_field').css({
            'color': '#00bc2f',
            'font-weight': 'bold',
            'font-size': '15px'
        });

        function handleBankTypeChange() {
            var selectedType = $('input[name="bank_type"]:checked').val();
            if (selectedType === 'microfinance_wallet') {
                $('.commercial-field').slideUp();
                $('#acc_no_label').text('Mobile / Wallet Number *');
            } else {
                $('.commercial-field').slideDown();
                $('#acc_no_label').text('Bank Account Number *');
            }
        }

        handleBankTypeChange();
        $('input[name="bank_type"]').change(handleBankTypeChange);
    });

    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush