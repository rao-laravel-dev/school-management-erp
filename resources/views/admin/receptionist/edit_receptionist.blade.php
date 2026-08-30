@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academic</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('receptionist.index') }}">Receptionist List</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Receptionist Profile</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card radius-10 border-top border-0 border-3 border-primary">
    <div class="card-body p-4">
        <div class="card-title d-flex align-items-center mb-4">
            <h5 class="mb-0 text-primary">Modify Receptionist Information (<span class="text-success fw-bold">{{ $receptionist->receptionist_id }}</span>)</h5>
        </div>
        <hr/>

        <form action="{{ route('receptionist.update', $receptionist->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            
            {{-- Role hidden field --}}
            <input type="hidden" name="role" value="receptionist">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 text-primary">Edit Receptionist Information</h5>
                <a href="{{ route('receptionist.index') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-list-ul"></i> Receptionist List</a>
            </div>
            <hr />

            {{-- START-PART 1: BASIC INFORMATION --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="joining_date" class="form-label">Date of Joining</label>
                    <input type="date" name="joining_date" id="joining_date" class="form-control" value="{{ old('joining_date', $receptionist->joining_date) }}">
                </div>

                {{-- Receptionist ID (Read-only during edit) --}}
                <div class="col-md-3">
                    <label for="receptionist_id" class="form-label">Receptionist ID *</label>
                    <input type="text" name="receptionist_id" id="receptionist_id_field" class="form-control bg-light fw-bold text-success" value="{{ $receptionist->receptionist_id }}" readonly>
                </div>

                <div class="col-md-3">
                    <label for="first_name" class="form-label">First Name *</label>
                    <input type="text" name="first_name" id="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $receptionist->first_name) }}">
                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label for="last_name" class="form-label">Last Name *</label>
                    <input type="text" name="last_name" id="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $receptionist->last_name) }}">
                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label for="father_name" class="form-label">Father Name *</label>
                    <input type="text" name="father_name" id="father_name" class="form-control @error('father_name') is-invalid @enderror" value="{{ old('father_name', $receptionist->father_name) }}">
                    @error('father_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label for="mother_name" class="form-label">Mother Name</label>
                    <input type="text" name="mother_name" id="mother_name" class="form-control" value="{{ old('mother_name', $receptionist->mother_name) }}">
                </div>

                <div class="col-md-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $receptionist->email) }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label for="cnic" class="form-label">CNIC *</label>
                    <input type="text" name="cnic" id="cnic" class="form-control @error('cnic') is-invalid @enderror" value="{{ old('cnic', $receptionist->cnic) }}" placeholder="xxxxx-xxxxxxx-x">
                    @error('cnic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label for="gender" class="form-label">Gender *</label>
                    <select name="gender" id="gender" class="form-select">
                        <option value="male" {{ old('gender', $receptionist->gender) == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $receptionist->gender) == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="dob" class="form-label">Date of Birth *</label>
                    <input type="date" name="dob" id="dob" class="form-control @error('dob') is-invalid @enderror" value="{{ old('dob', $receptionist->dob) }}">
                    @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label for="phone" class="form-label">Primary Phone *</label>
                    <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $receptionist->phone) }}" placeholder="03xx-xxxxxxx">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label for="alt_phone" class="form-label">WhatsApp / Alt Phone</label>
                    <input type="text" name="alt_phone" id="alt_phone" class="form-control @error('alt_phone') is-invalid @enderror" value="{{ old('alt_phone', $receptionist->alt_phone) }}" placeholder="03xx-xxxxxxx">
                    @error('alt_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label for="marital_status" class="form-label">Marital Status</label>
                    <select name="marital_status" id="marital_status" class="form-select">
                        <option value="single" {{ old('marital_status', $receptionist->marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                        <option value="married" {{ old('marital_status', $receptionist->marital_status) == 'married' ? 'selected' : '' }}>Married</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="desk_number" class="form-label">Assigned Desk / Counter</label>
                    <select name="desk_number" id="desk_number" class="form-select">
                        <option value="Main Reception" {{ old('desk_number', $receptionist->desk_number) == 'Main Reception' ? 'selected' : '' }}>Main Reception Desk</option>
                        <option value="Counter 1" {{ old('desk_number', $receptionist->desk_number) == 'Counter 1' ? 'selected' : '' }}>Front Counter 1</option>
                        <option value="Counter 2" {{ old('desk_number', $receptionist->desk_number) == 'Counter 2' ? 'selected' : '' }}>Front Counter 2</option>
                        <option value="Academic Block Desk" {{ old('desk_number', $receptionist->desk_number) == 'Academic Block Desk' ? 'selected' : '' }}>Academic Block Desk</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="work_experience" class="form-label">Work Experience</label>
                    <input type="text" name="work_experience" id="work_experience" class="form-control" value="{{ old('work_experience', $receptionist->work_experience) }}">
                </div>

                <div class="col-md-3">
                    <label for="qualification" class="form-label">Qualification</label>
                    <input type="text" name="qualification" id="qualification" class="form-control" value="{{ old('qualification', $receptionist->qualification) }}">
                </div>

                {{-- Photo Block with Live Saved State Rendering --}}
                <div class="col-md-4">
                    <label class="form-label text-secondary small fw-semibold">Photo</label>
                    <div class="d-flex align-items-center gap-2">
                        <div class="flex-grow-1">
                            <input type="file" name="photo" id="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*" onchange="previewImage(this)">
                            @error('photo')
                            <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Max size 2MB (WebP preferred)</small>
                        </div>

                        <div class="avatar-preview-wrapper border rounded bg-white d-flex align-items-center justify-content-center shadow-sm" style="width: 100px; height: 100px; min-width: 100px; overflow: hidden; padding: 3px;">
                            <img id="imagePreview" 
                                 src="{{ (!empty($receptionist->photo) && file_exists(public_path('uploads/receptionists/'.$receptionist->photo))) ? asset('uploads/receptionists/'.$receptionist->photo) : asset('uploads/no_image.jpg') }}" 
                                 alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" name="address" id="address" class="form-control" value="{{ old('address', $receptionist->address) }}">
                </div>

                <div class="col-md-4">
                    <label for="permanent_address" class="form-label">Permanent Address</label>
                    <input type="text" name="permanent_address" id="permanent_address" class="form-control" value="{{ old('permanent_address', $receptionist->permanent_address) }}">
                </div>
            </div>
            {{-- END-PART 1: BASIC INFORMATION --}}

            <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-4 d-flex justify-content-between align-items-center px-3" data-bs-toggle="collapse" data-bs-target="#moreDetails">
                <span>View More Details</span> <i class="bx bx-plus"></i>
            </button>

            <div class="collapse show" id="moreDetails">
                <div class="card p-4 mb-4 shadow-sm">

                    {{-- EMERGENCY CONTACT INFORMATION --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <h6 class="text-primary border-bottom pb-2 mt-3">Emergency Contact Information</h6>
                        </div>

                        <div class="col-md-4">
                            <label for="emergency_name" class="form-label">Person Name</label>
                            <input type="text" name="emergency_name" id="emergency_name" class="form-control @error('emergency_name') is-invalid @enderror" value="{{ old('emergency_name', $receptionist->emergency_name) }}" placeholder="Enter name">
                            @error('emergency_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="emergency_relation" class="form-label">Relation</label>
                            <select name="emergency_relation" id="emergency_relation" class="form-select">
                                <option value="">Select Relation</option>
                                <option value="father" {{ old('emergency_relation', $receptionist->emergency_relation) == 'father' ? 'selected' : '' }}>Father</option>
                                <option value="mother" {{ old('emergency_relation', $receptionist->emergency_relation) == 'mother' ? 'selected' : '' }}>Mother</option>
                                <option value="spouse" {{ old('emergency_relation', $receptionist->emergency_relation) == 'spouse' ? 'selected' : '' }}>Spouse</option>
                                <option value="brother" {{ old('emergency_relation', $receptionist->emergency_relation) == 'brother' ? 'selected' : '' }}>Brother</option>
                                <option value="friend" {{ old('emergency_relation', $receptionist->emergency_relation) == 'friend' ? 'selected' : '' }}>Friend</option>
                                <option value="other" {{ old('emergency_relation', $receptionist->emergency_relation) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="emergency_phone" class="form-label">Phone Number</label>
                            <input type="tel" name="emergency_phone" id="emergency_phone" class="form-control @error('emergency_phone') is-invalid @enderror" value="{{ old('emergency_phone', $receptionist->emergency_phone) }}" placeholder="03xx-xxxxxxx">
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
                            <input type="number" name="basic_salary" id="basic_salary" class="form-control @error('basic_salary') is-invalid @enderror" value="{{ old('basic_salary', $receptionist->salary?->basic_salary ?? $receptionist->basic_salary) }}">
                            @error('basic_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="contract_type" class="form-label">Contract Type</label>
                            <select name="contract_type" id="contract_type" class="form-select">
                                <option value="permanent" {{ old('contract_type', $receptionist->contract_type) == 'permanent' ? 'selected' : '' }}>Permanent</option>
                                <option value="contract" {{ old('contract_type', $receptionist->contract_type) == 'contract' ? 'selected' : '' }}>Contract</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="work_shift" class="form-label">Work Shift</label>
                            <input type="text" name="work_shift" id="work_shift" class="form-control" value="{{ old('work_shift', $receptionist->work_shift) }}">
                        </div>
                    </div>

                    {{-- BANK DETAILS SECTION --}}
                    <div class="row mb-2">
                        <div class="col-12">
                            <h5 class="text-primary border-bottom pb-2">Bank Details</h5>
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-12 mb-2">
                            <label class="form-check-label d-block fw-bold mb-2">Bank Account Type *</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bank_type" id="bank_type_commercial" value="commercial" {{ old('bank_type', $receptionist->bankDetail?->bank_type) == 'commercial' ? 'checked' : '' }}>
                                <label class="form-check-label" for="bank_type_commercial">Commercial Bank (HBL, Meezan, etc.)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="bank_type" id="bank_type_wallet" value="microfinance_wallet" {{ old('bank_type', $receptionist->bankDetail?->bank_type) == 'microfinance_wallet' ? 'checked' : '' }}>
                                <label class="form-check-label" for="bank_type_wallet">Digital Wallet / Microfinance (Easypaisa, JazzCash, etc.)</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="account_title" class="form-label">Account Title</label>
                            <input type="text" name="account_title" id="account_title" class="form-control @error('account_title') is-invalid @enderror" value="{{ old('account_title', $receptionist->bankDetail?->account_title) }}" placeholder="e.g., John Doe">
                            @error('account_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="account_number" id="acc_no_label" class="form-label">Account Number</label>
                            <input type="text" name="account_number" id="account_number" class="form-control @error('account_number') is-invalid @enderror" value="{{ old('account_number', $receptionist->bankDetail?->account_number) }}" placeholder="Account or Mobile No">
                            @error('account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="bank_name" class="form-label">Bank Name</label>
                            @php $savedBank = old('bank_name', $receptionist->bankDetail?->bank_name); @endphp
                            <select name="bank_name" id="bank_name" class="form-select @error('bank_name') is-invalid @enderror">
                                <option value="">Select Bank / Wallet</option>
                                <optgroup label="Commercial Banks" id="opt_commercial">
                                    <option value="Meezan Bank" {{ $savedBank == 'Meezan Bank' ? 'selected' : '' }}>Meezan Bank</option>
                                    <option value="Habib Bank Limited (HBL)" {{ $savedBank == 'Habib Bank Limited (HBL)' ? 'selected' : '' }}>Habib Bank Limited (HBL)</option>
                                    <option value="United Bank Limited (UBL)" {{ $savedBank == 'United Bank Limited (UBL)' ? 'selected' : '' }}>United Bank Limited (UBL)</option>
                                    <option value="Bank Alfalah" {{ $savedBank == 'Bank Alfalah' ? 'selected' : '' }}>Bank Alfalah</option>
                                    <option value="Allied Bank Limited (ABL)" {{ $savedBank == 'Allied Bank Limited (ABL)' ? 'selected' : '' }}>Allied Bank Limited (ABL)</option>
                                    <option value="Faysal Bank" {{ $savedBank == 'Faysal Bank' ? 'selected' : '' }}>Faysal Bank</option>
                                </optgroup>
                                <optgroup label="Wallets & Microfinance" id="opt_wallet">
                                    <option value="Easypaisa" {{ $savedBank == 'Easypaisa' ? 'selected' : '' }}>Easypaisa</option>
                                    <option value="JazzCash" {{ $savedBank == 'JazzCash' ? 'selected' : '' }}>JazzCash</option>
                                    <option value="SadaPay" {{ $savedBank == 'SadaPay' ? 'selected' : '' }}>SadaPay</option>
                                    <option value="NayaPay" {{ $savedBank == 'NayaPay' ? 'selected' : '' }}>NayaPay</option>
                                </optgroup>
                            </select>
                            @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 commercial-field">
                            <label for="iban" class="form-label">IBAN</label>
                            <input type="text" name="iban" id="iban" class="form-control" value="{{ old('iban', $receptionist->bankDetail?->iban) }}" placeholder="PKXXMEZN00000XXXXXXXXX">
                        </div>
                        <div class="col-md-4 commercial-field">
                            <label for="branch_name" class="form-label">Branch Name</label>
                            <input type="text" name="branch_name" id="branch_name" class="form-control" value="{{ old('branch_name', $receptionist->bankDetail?->branch_name) }}">
                        </div>
                        <div class="col-md-4 commercial-field">
                            <label for="branch_code" class="form-label">Branch Code</label>
                            <input type="text" name="branch_code" id="branch_code" class="form-control" value="{{ old('branch_code', $receptionist->bankDetail?->branch_code) }}">
                        </div>
                    </div>

                    {{-- SOCIAL MEDIA SECTION --}}
                    <div class="row mb-2">
                        <div class="col-12">
                            <h5 class="text-primary border-bottom pb-2">Social Media</h5>
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6"><input type="text" name="fb_url" class="form-control" placeholder="Facebook URL" value="{{ old('fb_url', $receptionist->fb_url) }}"></div>
                        <div class="col-md-6"><input type="text" name="twitter_url" class="form-control" placeholder="Twitter URL" value="{{ old('twitter_url', $receptionist->twitter_url) }}"></div>
                        <div class="col-md-6"><input type="text" name="linkedin_url" class="form-control" placeholder="LinkedIn URL" value="{{ old('linkedin_url', $receptionist->linkedin_url) }}"></div>
                        <div class="col-md-6"><input type="text" name="insta_url" class="form-control" placeholder="Instagram URL" value="{{ old('insta_url', $receptionist->insta_url) }}"></div>
                    </div>

                    {{-- DOCUMENTS SECTION --}}
                    <div class="row mb-2">
                        <div class="col-12">
                            <h5 class="text-primary border-bottom pb-2">Documents <small class="text-muted text-capitalize fs-6">(Leave blank to keep existing)</small></h5>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="doc_resume" class="form-label">Resume</label>
                            <input type="file" name="doc_resume" id="doc_resume" class="form-control">
                            @if(!empty($receptionist->doc_resume))
                                <div class="mt-1"><a href="{{ asset('uploads/documents/'.$receptionist->receptionist->doc_resume) }}" target="_blank" class="small text-primary fw-bold"><i class="bx bx-file"></i> View Current Resume</a></div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="doc_joining" class="form-label">Joining Letter</label>
                            <input type="file" name="doc_joining" id="doc_joining" class="form-control">
                            @if(!empty($receptionist->doc_joining))
                                <div class="mt-1"><a href="{{ asset('uploads/documents/'.$receptionist->receptionist->doc_joining) }}" target="_blank" class="small text-primary fw-bold"><i class="bx bx-file"></i> View Current Joining Letter</a></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('receptionist.index') }}" class="btn btn-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-success px-4">Update Receptionist Profile</button>
            </div>
        </form>
    </div>
</div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // --- 1. Realtime Image Preview handler (Synced with Form IDs) ---
        $('#photo').change(function(e) {
            if (e.target.files && e.target.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').attr('src', e.target.result);
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // --- 2. Toastr Notifications ---
        @if(session('error'))
        toastr.error("{{ session('error') }}", "Validation Error", {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right"
        });
        @endif

        @if(session('success'))
        toastr.success("{{ session('success') }}");
        @endif

        // --- 3. Styling Receptionist ID ---
        $('#receptionist_id_field').css({
            'color': '#00bc2f',
            'font-weight': 'bold',
            'font-size': '15px'
        });

        // --- 4. Bank Type Toggle ---
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
        
        // Initial state load on page refresh/edit
        handleBankTypeChange();
        $('input[name="bank_type"]').change(handleBankTypeChange);
    });

    // Inline safe call helper function for dynamic changes
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            // Edit fallback standard if cleared
            $('#imagePreview').attr('src', "{{ asset('uploads/no_image.jpg') }}");
        }
    }
</script>
@endpush