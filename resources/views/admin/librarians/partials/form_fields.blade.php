{{-- Shared fields for Add/Edit Librarian (teacher form markup). $librarian = model ya null (create), $librarianId = ID text --}}
@php
    $isEdit = isset($librarian) && $librarian;
    $bank = $isEdit ? $librarian->bankDetail : null;
    $salary = $isEdit ? $librarian->salary : null;
    // old() pehle, phir saved value (edit), warna default
    $val = function ($field, $default = '') use ($isEdit, $librarian) {
        return old($field, $isEdit ? $librarian->$field : $default);
    };
    $dateVal = function ($field, $default = '') use ($isEdit, $librarian) {
        return old($field, ($isEdit && $librarian->$field) ? $librarian->$field->format('Y-m-d') : $default);
    };
    $bankVal = function ($field, $default = '') use ($bank) {
        return old($field, $bank ? $bank->$field : $default);
    };
@endphp

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <label for="joining_date" class="form-label">Date of Joining</label>
        <input type="date" name="joining_date" id="joining_date" class="form-control form-control-sm @error('joining_date') is-invalid @enderror" value="{{ $dateVal('joining_date', $currentDate ?? '') }}">
        @error('joining_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        {{-- ID server banata hai; yeh field sirf dikhane ke liye (name nahi, request mein jati hi nahi) --}}
        <label for="librarian_id_field" class="form-label">Librarian ID *</label>
        <input type="text" id="librarian_id_field" class="form-control form-control-sm bg-light" value="{{ $librarianId }}" placeholder="Enter First Name to generate ID..." readonly>
    </div>

    <div class="col-md-3">
        <label for="first_name" class="form-label">First Name *</label>
        <input type="text" name="first_name" id="first_name" class="form-control form-control-sm @error('first_name') is-invalid @enderror" value="{{ $val('first_name') }}">
        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="last_name" class="form-label">Last Name *</label>
        <input type="text" name="last_name" id="last_name" class="form-control form-control-sm @error('last_name') is-invalid @enderror" value="{{ $val('last_name') }}">
        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="father_name" class="form-label">Father Name *</label>
        <input type="text" name="father_name" id="father_name" class="form-control form-control-sm @error('father_name') is-invalid @enderror" value="{{ $val('father_name') }}">
        @error('father_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="mother_name" class="form-label">Mother Name</label>
        <input type="text" name="mother_name" id="mother_name" class="form-control form-control-sm @error('mother_name') is-invalid @enderror" value="{{ $val('mother_name') }}">
        @error('mother_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="email" class="form-label">Email *</label>
        <input type="email" name="email" id="email" class="form-control form-control-sm @error('email') is-invalid @enderror" value="{{ $val('email') }}" autocomplete="username">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="cnic" class="form-label">CNIC *</label>
        <input type="text" name="cnic" id="cnic" class="form-control form-control-sm @error('cnic') is-invalid @enderror" value="{{ $val('cnic') }}" placeholder="xxxxx-xxxxxxx-x">
        @error('cnic')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="gender" class="form-label">Gender *</label>
        <select name="gender" id="gender" class="form-select form-select-sm @error('gender') is-invalid @enderror">
            <option value="male" {{ $val('gender') == 'male' ? 'selected' : '' }}>Male</option>
            <option value="female" {{ $val('gender') == 'female' ? 'selected' : '' }}>Female</option>
        </select>
        @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="dob" class="form-label">Date of Birth *</label>
        <input type="date" name="dob" id="dob" class="form-control form-control-sm @error('dob') is-invalid @enderror" value="{{ $dateVal('dob') }}">
        @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label for="phone" class="form-label">Phone *</label>
        <input type="text" name="phone" id="phone" class="form-control form-control-sm @error('phone') is-invalid @enderror" value="{{ $val('phone') }}" placeholder="03xx-xxxxxxx">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label for="marital_status" class="form-label">Marital Status</label>
        <select name="marital_status" id="marital_status" class="form-select form-select-sm @error('marital_status') is-invalid @enderror">
            <option value="single" {{ $val('marital_status') == 'single' ? 'selected' : '' }}>Single</option>
            <option value="married" {{ $val('marital_status') == 'married' ? 'selected' : '' }}>Married</option>
        </select>
        @error('marital_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label for="photo" class="form-label text-secondary small fw-semibold">Photo</label>
        <div class="d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <input type="file" name="photo" id="photo" class="form-control form-control-sm @error('photo') is-invalid @enderror" accept="image/*" onchange="previewImage(this)">
                @error('photo')<div class="invalid-feedback fw-semibold d-block">{{ $message }}</div>@enderror
                <small class="text-muted d-block mt-1" style="font-size: 11px;">Max size 2MB (WebP preferred)</small>
            </div>
            <div class="avatar-preview-wrapper border rounded bg-white d-flex align-items-center justify-content-center shadow-sm"
                style="width: 100px; height: 100px; min-width: 100px; overflow: hidden; padding: 3px;">
                <img id="imagePreview" src="{{ $isEdit ? $librarian->photo_url : asset('uploads/no_image.jpg') }}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <label for="work_experience" class="form-label">Work Experience</label>
        <input type="text" name="work_experience" id="work_experience" class="form-control form-control-sm @error('work_experience') is-invalid @enderror" value="{{ $val('work_experience') }}">
        @error('work_experience')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="qualification" class="form-label">Qualification</label>
        <input type="text" name="qualification" id="qualification" class="form-control form-control-sm @error('qualification') is-invalid @enderror" value="{{ $val('qualification') }}">
        @error('qualification')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="address" class="form-label">Address</label>
        <input type="text" name="address" id="address" class="form-control form-control-sm @error('address') is-invalid @enderror" value="{{ $val('address') }}">
        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="permanent_address" class="form-label">Permanent Address</label>
        <input type="text" name="permanent_address" id="permanent_address" class="form-control form-control-sm @error('permanent_address') is-invalid @enderror" value="{{ $val('permanent_address') }}">
        @error('permanent_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                <label for="emergency_name" class="form-label">Person Name *</label>
                <input type="text" name="emergency_name" id="emergency_name" class="form-control form-control-sm @error('emergency_name') is-invalid @enderror" value="{{ $val('emergency_name') }}" placeholder="Enter name">
                @error('emergency_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label for="emergency_relation" class="form-label">Relation</label>
                <select name="emergency_relation" id="emergency_relation" class="form-select form-select-sm @error('emergency_relation') is-invalid @enderror">
                    <option value="">Select Relation</option>
                    @foreach(['father' => 'Father', 'mother' => 'Mother', 'spouse' => 'Spouse', 'brother' => 'Brother', 'friend' => 'Friend', 'other' => 'Other'] as $key => $label)
                    <option value="{{ $key }}" {{ $val('emergency_relation') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('emergency_relation')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label for="emergency_phone" class="form-label">Phone Number *</label>
                <input type="tel" name="emergency_phone" id="emergency_phone" class="form-control form-control-sm @error('emergency_phone') is-invalid @enderror" value="{{ $val('emergency_phone') }}" placeholder="03xx-xxxxxxx">
                @error('emergency_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-12">
                <h5 class="text-primary border-bottom pb-2">Payroll</h5>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="basic_salary" class="form-label">Basic Salary *</label>
                <input type="number" name="basic_salary" id="basic_salary" class="form-control form-control-sm @error('basic_salary') is-invalid @enderror" value="{{ old('basic_salary', $salary ? $salary->basic_salary : '') }}">
                @error('basic_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label for="contract_type" class="form-label">Contract Type</label>
                <select name="contract_type" id="contract_type" class="form-select form-select-sm @error('contract_type') is-invalid @enderror">
                    <option value="permanent" {{ $val('contract_type') == 'permanent' ? 'selected' : '' }}>Permanent</option>
                    <option value="contract" {{ $val('contract_type') == 'contract' ? 'selected' : '' }}>Contract</option>
                </select>
                @error('contract_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label for="work_shift" class="form-label">Work Shift</label>
                <input type="text" name="work_shift" id="work_shift" class="form-control form-control-sm @error('work_shift') is-invalid @enderror" value="{{ $val('work_shift') }}">
                @error('work_shift')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-12">
                <h5 class="text-primary border-bottom pb-2">Bank Details</h5>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-12 mb-2" id="bank_type_group">
                <label class="form-check-label d-block fw-bold mb-2">Bank Account Type *</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="bank_type" id="bank_type_commercial" value="commercial" {{ $bankVal('bank_type', 'commercial') == 'commercial' ? 'checked' : '' }}>
                    <label class="form-check-label" for="bank_type_commercial">Commercial Bank (HBL, Meezan, etc.)</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="bank_type" id="bank_type_wallet" value="microfinance_wallet" {{ $bankVal('bank_type') == 'microfinance_wallet' ? 'checked' : '' }}>
                    <label class="form-check-label" for="bank_type_wallet">Digital Wallet / Microfinance (Easypaisa, JazzCash, etc.)</label>
                </div>
                @error('bank_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label for="account_title" class="form-label">Account Title *</label>
                <input type="text" name="account_title" id="account_title" class="form-control form-control-sm @error('account_title') is-invalid @enderror" value="{{ $bankVal('account_title') }}" placeholder="e.g., John Doe">
                @error('account_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label for="account_number" id="acc_no_label" class="form-label">Account Number</label>
                <input type="text" name="account_number" id="account_number" class="form-control form-control-sm @error('account_number') is-invalid @enderror" value="{{ $bankVal('account_number') }}" placeholder="Account or Mobile No">
                @error('account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label for="bank_name" class="form-label">Bank Name</label>
                <select name="bank_name" id="bank_name" class="form-select form-select-sm @error('bank_name') is-invalid @enderror">
                    <option value="">Select Bank / Wallet</option>
                    <optgroup label="Commercial Banks">
                        @foreach(['Meezan Bank', 'Habib Bank Limited (HBL)', 'United Bank Limited (UBL)', 'Bank Alfalah', 'Allied Bank Limited (ABL)', 'Faysal Bank'] as $bankName)
                        <option value="{{ $bankName }}" {{ $bankVal('bank_name') == $bankName ? 'selected' : '' }}>{{ $bankName }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Wallets & Microfinance">
                        @foreach(['Easypaisa', 'JazzCash', 'SadaPay', 'NayaPay'] as $wallet)
                        <option value="{{ $wallet }}" {{ $bankVal('bank_name') == $wallet ? 'selected' : '' }}>{{ $wallet }}</option>
                        @endforeach
                    </optgroup>
                </select>
                @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4 commercial-field">
                <label for="iban" class="form-label">IBAN</label>
                <input type="text" name="iban" id="iban" class="form-control form-control-sm @error('iban') is-invalid @enderror" value="{{ $bankVal('iban') }}" placeholder="PKXXMEZN00000XXXXXXXXX">
                @error('iban')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 commercial-field">
                <label for="branch_name" class="form-label">Branch Name</label>
                <input type="text" name="branch_name" id="branch_name" class="form-control form-control-sm @error('branch_name') is-invalid @enderror" value="{{ $bankVal('branch_name') }}">
                @error('branch_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 commercial-field">
                <label for="branch_code" class="form-label">Branch Code</label>
                <input type="text" name="branch_code" id="branch_code" class="form-control form-control-sm @error('branch_code') is-invalid @enderror" value="{{ $bankVal('branch_code') }}">
                @error('branch_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Social Media + Documents yahan nahi: librarians table mein columns nahi (rule 19, silent NULL save nahi) --}}

        {{-- Login Security Setup --}}
        <div class="row mb-2">
            <div class="col-12">
                <h5 class="text-primary border-bottom pb-2"><i class="bx bx-shield-quarter me-1"></i> Login Security Setup</h5>
                @if($isEdit)
                <small class="text-muted">Leave blank to keep the current password.</small>
                @endif
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="password">Librarian System Password @if(!$isEdit)<span class="text-danger">*</span>@endif</label>
                <div class="input-group input-group-sm">
                    <input type="password" name="password" id="password" class="form-control form-control-sm @error('password') is-invalid @enderror" placeholder="••••••••" autocomplete="new-password">
                    <button class="btn btn-outline-secondary btn-sm toggle-password" type="button" data-target="password">
                        <i class="bx bx-hide" id="password_icon"></i>
                    </button>
                </div>
                @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="password_confirmation">Confirm Password @if(!$isEdit)<span class="text-danger">*</span>@endif</label>
                <div class="input-group input-group-sm">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-sm @error('password_confirmation') is-invalid @enderror" placeholder="••••••••" autocomplete="new-password">
                    <button class="btn btn-outline-secondary btn-sm toggle-password" type="button" data-target="password_confirmation">
                        <i class="bx bx-hide" id="password_confirmation_icon"></i>
                    </button>
                </div>
                @error('password_confirmation') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
        </div>

    </div>
</div>
