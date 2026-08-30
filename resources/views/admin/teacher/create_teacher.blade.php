@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Teacher Management', 'url' => '#'],
    ['label' => 'Add New Teacher', 'url' => route('teacher.create')],
]" />

<div class="card shadow-none border">
    <div class="card-body">
        <form action="{{ route('teacher.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="role" value="teacher">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 text-primary">Basic Information</h5>
                <a href="{{ route('teacher.index') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-list-ul"></i> Teacher List</a>
            </div>
            <hr />

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="joining_date" class="form-label">Date of Joining</label>
                    <input type="date" name="joining_date" id="joining_date" class="form-control form-control-sm" value="{{ old('joining_date', $currentDate) }}">
                </div>

                <div class="col-md-3">
                    <label for="teacher_id" class="form-label">Teacher ID *</label>
                    <input type="text" name="teacher_id" id="teacher_id_field" class="form-control form-control-sm @error('teacher_id') is-invalid @enderror" value="{{ old('teacher_id', $teacherId) }}" placeholder="Enter First Name to generate ID..." readonly>
                    @error('teacher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label for="first_name" class="form-label">First Name *</label>
                    <input type="text" name="first_name" id="first_name" class="form-control form-control-sm @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}">
                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="last_name" class="form-label">Last Name *</label>
                    <input type="text" name="last_name" id="last_name" class="form-control form-control-sm @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}">
                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="father_name" class="form-label">Father Name *</label>
                    <input type="text" name="father_name" id="father_name" class="form-control form-control-sm @error('father_name') is-invalid @enderror" value="{{ old('father_name') }}">
                    @error('father_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="mother_name" class="form-label">Mother Name</label>
                    <input type="text" name="mother_name" id="mother_name" class="form-control form-control-sm" value="{{ old('mother_name') }}">
                </div>
                <div class="col-md-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" name="email" id="email" class="form-control form-control-sm @error('email') is-invalid @enderror" value="{{ old('email') }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="cnic" class="form-label">CNIC *</label>
                    <input type="text" name="cnic" id="cnic" class="form-control form-control-sm @error('cnic') is-invalid @enderror" value="{{ old('cnic') }}" placeholder="xxxxx-xxxxxxx-x">
                    @error('cnic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="gender" class="form-label">Gender *</label>
                    <select name="gender" id="gender" class="form-select form-select-sm">
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="dob" class="form-label">Date of Birth *</label>
                    <input type="date" name="dob" id="dob" class="form-control form-control-sm @error('dob') is-invalid @enderror" value="{{ old('dob') }}">
                    @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label for="phone" class="form-label">Phone *</label>
                    <input type="text" name="phone" id="phone" class="form-control form-control-sm @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="03xx-xxxxxxx">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label for="marital_status" class="form-label">Marital Status</label>
                    <select name="marital_status" id="marital_status" class="form-select form-select-sm">
                        <option value="single" {{ old('marital_status') == 'single' ? 'selected' : '' }}>Single</option>
                        <option value="married" {{ old('marital_status') == 'married' ? 'selected' : '' }}>Married</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-secondary small fw-semibold">Photo</label>
                    <div class="d-flex align-items-center gap-2">
                        <div class="flex-grow-1">
                            <input type="file" name="photo" id="photo" class="form-control form-control-sm @error('photo') is-invalid @enderror" accept="image/*" onchange="previewImage(this)">
                            @error('photo')<div class="invalid-feedback fw-semibold d-block">{{ $message }}</div>@enderror
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Max size 2MB (WebP preferred)</small>
                        </div>
                        <div class="avatar-preview-wrapper border rounded bg-white d-flex align-items-center justify-content-center shadow-sm"
                            style="width: 100px; height: 100px; min-width: 100px; overflow: hidden; padding: 3px;">
                            <img id="imagePreview" src="{{ asset('uploads/no_image.jpg') }}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="work_experience" class="form-label">Work Experience</label>
                    <input type="text" name="work_experience" id="work_experience" class="form-control form-control-sm" value="{{ old('work_experience') }}">
                </div>
                <div class="col-md-4">
                    <label for="qualification" class="form-label">Qualification</label>
                    <input type="text" name="qualification" id="qualification" class="form-control form-control-sm" value="{{ old('qualification') }}">
                </div>

                <div class="col-md-6">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" name="address" id="address" class="form-control form-control-sm" value="{{ old('address') }}">
                </div>
                <div class="col-md-6">
                    <label for="permanent_address" class="form-label">Permanent Address</label>
                    <input type="text" name="permanent_address" id="permanent_address" class="form-control form-control-sm" value="{{ old('permanent_address') }}">
                </div>
            </div>

            <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-4 d-flex justify-content-between align-items-center px-3" data-bs-toggle="collapse" data-bs-target="#moreDetails">
                <span>Add More Details</span> <i class="bx bx-plus"></i>
            </button>

            <div class="collapse" id="moreDetails">
                <div class="card p-4 mb-4 shadow-sm">

                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <h6 class="text-primary border-bottom pb-2 mt-3">Emergency Contact Information</h6>
                        </div>

                        <div class="col-md-4">
                            <label for="emergency_name" class="form-label">Person Name</label>
                            <input type="text" name="emergency_name" id="emergency_name" class="form-control form-control-sm @error('emergency_name') is-invalid @enderror" value="{{ old('emergency_name') }}" placeholder="Enter name">
                            @error('emergency_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="emergency_relation" class="form-label">Relation</label>
                            <select name="emergency_relation" id="emergency_relation" class="form-select form-select-sm">
                                <option value="">Select Relation</option>
                                <option value="father" {{ old('emergency_relation') == 'father' ? 'selected' : '' }}>Father</option>
                                <option value="mother" {{ old('emergency_relation') == 'mother' ? 'selected' : '' }}>Mother</option>
                                <option value="spouse" {{ old('emergency_relation') == 'spouse' ? 'selected' : '' }}>Spouse</option>
                                <option value="brother" {{ old('emergency_relation') == 'brother' ? 'selected' : '' }}>Brother</option>
                                <option value="friend" {{ old('emergency_relation') == 'friend' ? 'selected' : '' }}>Friend</option>
                                <option value="other" {{ old('emergency_relation') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="emergency_phone" class="form-label">Phone Number</label>
                            <input type="tel" name="emergency_phone" id="emergency_phone" class="form-control form-control-sm @error('emergency_phone') is-invalid @enderror" value="{{ old('emergency_phone') }}" placeholder="03xx-xxxxxxx">
                            @error('emergency_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12">
                            <h5 class="text-primary border-bottom pb-2">Payroll</h5>
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4"><label for="basic_salary" class="form-label">Basic Salary *</label><input type="number" name="basic_salary" id="basic_salary" class="form-control form-control-sm @error('basic_salary') is-invalid @enderror" value="{{ old('basic_salary') }}">@error('basic_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-4"><label for="contract_type" class="form-label">Contract Type</label><select name="contract_type" id="contract_type" class="form-select form-select-sm">
                                <option value="permanent">Permanent</option>
                                <option value="contract">Contract</option>
                            </select></div>
                        <div class="col-md-4"><label for="work_shift" class="form-label">Work Shift</label><input type="text" name="work_shift" id="work_shift" class="form-control form-control-sm" value="{{ old('work_shift') }}"></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12">
                            <h5 class="text-primary border-bottom pb-2">Bank Details</h5>
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-12 mb-2">
                            <label class="form-check-label d-block fw-bold mb-2">Bank Account Type *</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bank_type" id="bank_type_commercial" value="commercial" {{ old('bank_type', 'commercial') == 'commercial' ? 'checked' : '' }}>
                                <label class="form-check-label" for="bank_type_commercial">Commercial Bank (HBL, Meezan, etc.)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bank_type" id="bank_type_wallet" value="microfinance_wallet" {{ old('bank_type') == 'microfinance_wallet' ? 'checked' : '' }}>
                                <label class="form-check-label" for="bank_type_wallet">Digital Wallet / Microfinance (Easypaisa, JazzCash, etc.)</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="account_title" class="form-label">Account Title</label>
                            <input type="text" name="account_title" id="account_title" class="form-control form-control-sm @error('account_title') is-invalid @enderror" value="{{ old('account_title') }}" placeholder="e.g., John Doe">
                            @error('account_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="account_number" id="acc_no_label" class="form-label">Account Number</label>
                            <input type="text" name="account_number" id="account_number" class="form-control form-control-sm @error('account_number') is-invalid @enderror" value="{{ old('account_number') }}" placeholder="Account or Mobile No">
                            @error('account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="bank_name" class="form-label">Bank Name</label>
                            <select name="bank_name" id="bank_name" class="form-select form-select-sm @error('bank_name') is-invalid @enderror">
                                <option value="">Select Bank / Wallet</option>
                                <optgroup label="Commercial Banks" id="opt_commercial">
                                    <option value="Meezan Bank" {{ old('bank_name') == 'Meezan Bank' ? 'selected' : '' }}>Meezan Bank</option>
                                    <option value="Habib Bank Limited (HBL)" {{ old('bank_name') == 'Habib Bank Limited (HBL)' ? 'selected' : '' }}>Habib Bank Limited (HBL)</option>
                                    <option value="United Bank Limited (UBL)" {{ old('bank_name') == 'United Bank Limited (UBL)' ? 'selected' : '' }}>United Bank Limited (UBL)</option>
                                    <option value="Bank Alfalah" {{ old('bank_name') == 'Bank Alfalah' ? 'selected' : '' }}>Bank Alfalah</option>
                                    <option value="Allied Bank Limited (ABL)" {{ old('bank_name') == 'Allied Bank Limited (ABL)' ? 'selected' : '' }}>Allied Bank Limited (ABL)</option>
                                    <option value="Faysal Bank" {{ old('bank_name') == 'Faysal Bank' ? 'selected' : '' }}>Faysal Bank</option>
                                </optgroup>
                                <optgroup label="Wallets & Microfinance" id="opt_wallet">
                                    <option value="Easypaisa" {{ old('bank_name') == 'Easypaisa' ? 'selected' : '' }}>Easypaisa</option>
                                    <option value="JazzCash" {{ old('bank_name') == 'JazzCash' ? 'selected' : '' }}>JazzCash</option>
                                    <option value="SadaPay" {{ old('bank_name') == 'SadaPay' ? 'selected' : '' }}>SadaPay</option>
                                    <option value="NayaPay" {{ old('bank_name') == 'NayaPay' ? 'selected' : '' }}>NayaPay</option>
                                </optgroup>
                            </select>
                            @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 commercial-field">
                            <label for="iban" class="form-label">IBAN</label>
                            <input type="text" name="iban" id="iban" class="form-control form-control-sm" value="{{ old('iban') }}" placeholder="PKXXMEZN00000XXXXXXXXX">
                        </div>
                        <div class="col-md-4 commercial-field">
                            <label for="branch_name" class="form-label">Branch Name</label>
                            <input type="text" name="branch_name" id="branch_name" class="form-control form-control-sm" value="{{ old('branch_name') }}">
                        </div>
                        <div class="col-md-4 commercial-field">
                            <label for="branch_code" class="form-label">Branch Code</label>
                            <input type="text" name="branch_code" id="branch_code" class="form-control form-control-sm" value="{{ old('branch_code') }}">
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12">
                            <h5 class="text-primary border-bottom pb-2">Social Media</h5>
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6"><input type="text" name="fb_url" class="form-control form-control-sm" placeholder="Facebook URL" value="{{ old('fb_url') }}"></div>
                        <div class="col-md-6"><input type="text" name="twitter_url" class="form-control form-control-sm" placeholder="Twitter URL" value="{{ old('twitter_url') }}"></div>
                        <div class="col-md-6"><input type="text" name="linkedin_url" class="form-control form-control-sm" placeholder="LinkedIn URL" value="{{ old('linkedin_url') }}"></div>
                        <div class="col-md-6"><input type="text" name="insta_url" class="form-control form-control-sm" placeholder="Instagram URL" value="{{ old('insta_url') }}"></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12">
                            <h5 class="text-primary border-bottom pb-2">Documents</h5>
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6"><label for="doc_resume" class="form-label">Resume</label><input type="file" name="doc_resume" id="doc_resume" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label for="doc_joining" class="form-label">Joining Letter</label><input type="file" name="doc_joining" id="doc_joining" class="form-control form-control-sm"></div>
                    </div>

                    {{-- Login Security Setup --}}
                    <div class="row mb-2">
                        <div class="col-12">
                            <h5 class="text-primary border-bottom pb-2"><i class="bx bx-shield-quarter me-1"></i> Login Security Setup</h5>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="password">Teacher System Password <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="password" name="password" id="password" class="form-control form-control-sm @error('password') is-invalid @enderror" placeholder="••••••••">
                                <button class="btn btn-outline-secondary btn-sm toggle-password" type="button" data-target="password">
                                    <i class="bx bx-hide" id="password_icon"></i>
                                </button>
                            </div>
                            @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-sm @error('password_confirmation') is-invalid @enderror" placeholder="••••••••">
                                <button class="btn btn-outline-secondary btn-sm toggle-password" type="button" data-target="password_confirmation">
                                    <i class="bx bx-hide" id="password_confirmation_icon"></i>
                                </button>
                            </div>
                            @error('password_confirmation') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-sm btn-success px-4">Save Teacher Profile</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Teacher ID Auto-generation on First Name blur
        $('#first_name').on('blur', function() {
            const name = $(this).val();
            const idField = $('#teacher_id_field');

            if (name && name.trim().length > 0) {
                idField.val("Generating...");
                $.ajax({
                    url: "{{ route('teacher.get-teacher-id') }}",
                    type: 'GET',
                    data: {
                        first_name: name
                    },
                    success: function(res) {
                        if (res.teacher_id) {
                            idField.val(res.teacher_id);
                        } else {
                            idField.val("");
                            toastr.error("ID generation failed. Please try again.");
                        }
                    },
                    error: function(xhr) {
                        idField.val("");
                        toastr.error("Server error, could not generate ID.");
                    }
                });
            }
        });

        // Toastr Flash Success Message Only
        @if(session('toastr-success'))
            toastr.success("{{ session('toastr-success') }}");
        @endif

        // Show ONLY the first validation error in a single clean toast
        @if($errors->any())
            toastr.error("{{ $errors->first() }}", "Validation Error", {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "5000"
            });
        @endif

        // Real-time error removal on input / change
        $('input, select, textarea').on('input change', function() {
            if ($(this).hasClass('is-invalid')) {
                $(this).removeClass('is-invalid');
                $(this).siblings('.invalid-feedback').hide();
            }
        });

        $('#teacher_id_field').css({
            'color': '#00bc2f',
            'font-weight': 'bold',
            'font-size': '15px'
        });

        // Bank Type & Wallet Toggle Logic
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

        // Password Toggle Inside Document Ready
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
    });

    // Image Preview Function
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            $('#imagePreview').attr('src', "{{ asset('uploads/no_image.jpg') }}");
        }
    }
</script>
@endpush