@extends($current_layout)

@section('content')
<x-breadcrumb :items="[
    ['label' => 'Site Setting', 'url' => '#'],
    ['label' => 'Setting', 'url' => route('site_setting.edit')],
]" />

<div class="card">
    <div class="card-header">
        <h5 class="fw-semi-bold text-primary mb-0">Site Settings</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('site_setting.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="school_name" class="form-label">School Name <span class="text-danger">*</span></label>
                    <input type="text" name="school_name" id="school_name"
                        placeholder="e.g. Mount Carmel School"
                        class="form-control form-control-sm @error('school_name') is-invalid @enderror"
                        value="{{ old('school_name', $setting->school_name) }}">
                    @error('school_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="registration_no" class="form-label">Registration / Affiliation No</label>
                    <input type="text" name="registration_no" id="registration_no"
                        placeholder="e.g. REG-2024-001"
                        class="form-control form-control-sm @error('registration_no') is-invalid @enderror"
                        value="{{ old('registration_no', $setting->registration_no) }}">
                    @error('registration_no')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 👇 NAYA FIELD YAHAN ADD KAREIN --}}
                <div class="col-md-6">
                    <label for="admission_prefix" class="form-label">Admission No. Prefix <span class="text-danger">*</span></label>
                    <input type="text" name="admission_prefix" id="admission_prefix"
                        placeholder="e.g. APS, SSS"
                        maxlength="10"
                        class="form-control form-control-sm @error('admission_prefix') is-invalid @enderror"
                        value="{{ old('admission_prefix', $setting->admission_prefix) }}">
                    <small class="text-muted">Used in student Admission Numbers, e.g. APS-2026-0001</small>
                    @error('admission_prefix')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" name="phone" id="phone"
                        placeholder="e.g. 0321-1234567"
                        class="form-control form-control-sm @error('phone') is-invalid @enderror"
                        value="{{ old('phone', $setting->phone) }}">
                    @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email"
                        placeholder="e.g. info@school.edu.pk"
                        class="form-control form-control-sm @error('email') is-invalid @enderror"
                        value="{{ old('email', $setting->email) }}">
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="website" class="form-label">Website</label>
                    <input type="text" name="website" id="website"
                        placeholder="e.g. https://www.school.edu.pk"
                        class="form-control form-control-sm @error('website') is-invalid @enderror"
                        value="{{ old('website', $setting->website) }}">
                    @error('website')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="established_year" class="form-label">Established Year</label>
                    <input type="text" name="established_year" id="established_year"
                        placeholder="e.g. 2010"
                        class="form-control form-control-sm @error('established_year') is-invalid @enderror"
                        value="{{ old('established_year', $setting->established_year) }}">
                    @error('established_year')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" name="address" id="address"
                        placeholder="e.g. 110 Kings Street, Lahore"
                        class="form-control form-control-sm @error('address') is-invalid @enderror"
                        value="{{ old('address', $setting->address) }}">
                    @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="header_note" class="form-label">Header Note</label>
                    <input type="text" name="header_note" id="header_note"
                        placeholder="Text shown at top of printed documents"
                        class="form-control form-control-sm @error('header_note') is-invalid @enderror"
                        value="{{ old('header_note', $setting->header_note) }}">
                    @error('header_note')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="footer_note" class="form-label">Footer Note</label>
                    <input type="text" name="footer_note" id="footer_note"
                        placeholder="Text shown at bottom of printed documents"
                        class="form-control form-control-sm @error('footer_note') is-invalid @enderror"
                        value="{{ old('footer_note', $setting->footer_note) }}">
                    @error('footer_note')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="logo" class="form-label">School Logo</label>
                    <input type="file" name="logo" id="logo" accept="image/*"
                        class="form-control form-control-sm @error('logo') is-invalid @enderror">
                    <small class="text-muted">JPG, PNG or WebP — max 2MB</small>
                    @error('logo')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if($setting->logo)
                    <div><img src="{{ asset('storage/uploads/site_setting/' . $setting->logo) }}" class="mt-2" style="max-height:60px;" alt="Current Logo"></div>
                    @endif
                </div>

                <div class="col-md-6">
                    <label for="principal_signature" class="form-label">Principal Signature</label>
                    <input type="file" name="principal_signature" id="principal_signature" accept="image/*"
                        class="form-control form-control-sm @error('principal_signature') is-invalid @enderror">
                    <small class="text-muted">JPG, PNG or WebP — max 2MB</small>
                    @error('principal_signature')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if($setting->principal_signature)
                    <div><img src="{{ asset('storage/uploads/site_setting/' . $setting->principal_signature) }}" class="mt-2" style="max-height:60px;" alt="Current Signature"></div>
                    @endif
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-sm btn-success px-4">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    @if($errors->any())
    toastr.error("{{ $errors->first() }}");
    @endif
    @if(session('toastr-success'))
    toastr.success("{{ session('toastr-success') }}");
    @endif
</script>
@endpush