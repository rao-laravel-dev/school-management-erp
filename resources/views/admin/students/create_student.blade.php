@extends($current_layout)
@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Students Information', 'url' => '#'],
    ['label' => 'Add New Student', 'url' => route('students.create')],
]" />

<div class="row">
    <div class="col-xl-12 mx-auto">

        <form id="studentAdmissionForm" action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="accordion" id="admissionAccordion">

                {{-- 1. ACADEMIC & PERSONAL INFO --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAcademic" aria-expanded="true" aria-controls="collapseAcademic">
                            <i class="bx bx-user-pin me-2"></i> Academic &amp; Personal Info
                        </button>
                    </h2>
                    <div id="collapseAcademic" class="accordion-collapse collapse show" data-bs-parent="#admissionAccordion">
                        <div class="accordion-body">
                            <div class="row g-3">

                                <div class="col-md-3">
                                    <label class="form-label" for="admission_no">Admission No <span class="text-danger">*</span></label>
                                    <input type="text" name="admission_no" id="admission_no" class="form-control form-control-sm bg-light fw-bold text-success" value="{{ old('admission_no') }}" placeholder="Select Class & Section to Generate..." readonly>
                                    @error('admission_no') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="roll_number">Roll No <span class="text-danger">*</span></label>
                                    <input type="text" name="roll_number" id="roll_number" class="form-control form-control-sm bg-light fw-bold text-success" value="{{ old('roll_number') }}" placeholder="Automatic Allocation..." readonly>
                                    @error('roll_number') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="class_id">Class <span class="text-danger">*</span></label>
                                    <select name="class_id" id="class_id" class="form-select form-select-sm @error('class_id') is-invalid @enderror">
                                        <option value="">-- Select Class --</option>
                                        @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('class_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="section_id">Section <span class="text-danger">*</span></label>
                                    <select name="section_id" id="section_id" class="form-select form-select-sm  @error('section_id') is-invalid @enderror">
                                        <option value="">-- Select Section --</option>
                                        @foreach($sections as $section)
                                        <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('section_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="first_name">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" id="first_name" class="form-control form-control-sm @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" placeholder="Student First Name">
                                    @error('first_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="last_name">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" id="last_name" class="form-control form-control-sm @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" placeholder="Student Last Name">
                                    @error('last_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="date_of_birth" id="date_of_birth" class="form-control form-control-sm @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth') }}">
                                    @error('date_of_birth') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="gender">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" id="gender" class="form-select form-select-sm @error('gender') is-invalid @enderror">
                                        <option value="">-- Select Gender --</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="admission_date">Admission Date <span class="text-danger">*</span></label>
                                    <input type="date" name="admission_date" id="admission_date" class="form-control form-control-sm @error('admission_date') is-invalid @enderror" value="{{ old('admission_date', date('Y-m-d')) }}">
                                    @error('admission_date') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="category_id">Category</label>
                                    <select name="category_id" id="category_id" class="form-select  form-select-sm @error('category_id') is-invalid @enderror">
                                        <option value="">-- Select Category --</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="religion">Religion</label>
                                    <input type="text" name="religion" id="religion" class="form-control form-control-sm @error('religion') is-invalid @enderror" value="{{ old('religion') }}" placeholder="e.g. Islam, Christian">
                                    @error('religion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="caste">Caste</label>
                                    <input type="text" name="caste" id="caste" class="form-control form-control-sm @error('caste') is-invalid @enderror" value="{{ old('caste') }}" placeholder="e.g. Rajput">
                                    @error('caste') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="blood_group">Blood Group</label>
                                    <select name="blood_group" id="blood_group" class="form-select form-select-sm @error('blood_group') is-invalid @enderror">
                                        <option value="">-- Select Blood Group --</option>
                                        @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg)
                                        <option value="{{ $bg }}" {{ old('blood_group') == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                        @endforeach
                                    </select>
                                    @error('blood_group') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="house_id">House</label>
                                    <select name="house_id" id="house_id" class="form-select  form-select-sm @error('house_id') is-invalid @enderror">
                                        <option value="">-- Select House --</option>
                                        @foreach($houses as $h)
                                        <option value="{{ $h->id }}" {{ old('house_id') == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('house_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="height">Height (ft)</label>
                                    <input type="text" name="height" id="height" class="form-control form-control-sm @error('height') is-invalid @enderror" value="{{ old('height') }}" placeholder="e.g. 4.5 ft">
                                    @error('height') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="weight">Weight (kg)</label>
                                    <input type="text" name="weight" id="weight" class="form-control form-control-sm @error('weight') is-invalid @enderror" value="{{ old('weight') }}" placeholder="e.g. 35 kg">
                                    @error('weight') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="measurement_date">Measurement Date</label>
                                    <input type="date" name="measurement_date" id="measurement_date" class="form-control form-control-sm @error('measurement_date') is-invalid @enderror" value="{{ old('measurement_date') }}">
                                    @error('measurement_date') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="group_id">Group / Stream <span id="groupAsterisk" class="text-danger d-none">*</span></label>
                                    <select name="group_id" id="group_id" class="form-select form-select-sm @error('group_id') is-invalid @enderror">
                                        <option value="">-- Select Group --</option>
                                        @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('group_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="student_photo">Student Photo</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="flex-grow-1">
                                            <input type="file" name="student_photo" id="student_photo" class="form-control form-control-sm @error('student_photo') is-invalid @enderror" accept="image/*">
                                            @error('student_photo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                            <small class="text-muted d-block mt-1">Max size 2MB (WebP preferred)</small>
                                        </div>
                                        <div class="border rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 38px; min-width: 40px; overflow: hidden;">
                                            <img id="student_preview" src="{{ asset('uploads/no_image.jpg') }}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label" for="medical_history">Medical History / Internal Remarks</label>
                                    <textarea name="medical_history" id="medical_history" class="form-control form-control-sm @error('medical_history') is-invalid @enderror" rows="3" placeholder="Write any medical concerns or academic internal notes here...">{{ old('medical_history') }}</textarea>
                                    @error('medical_history') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. PARENT / GUARDIAN DETAILS --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseParent" aria-expanded="false" aria-controls="collapseParent">
                            <i class="bx bx-group me-2"></i> Parent / Guardian Details
                        </button>
                    </h2>
                    <div id="collapseParent" class="accordion-collapse collapse" data-bs-parent="#admissionAccordion">
                        <div class="accordion-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="father_name">Father Name <span class="text-danger">*</span></label>
                                    <input type="text" name="father_name" id="father_name" class="form-control form-control-sm @error('father_name') is-invalid @enderror" value="{{ old('father_name') }}" placeholder="Father Name">
                                    @error('father_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="father_phone">Father Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="father_phone" id="father_phone" class="form-control form-control-sm @error('father_phone') is-invalid @enderror" value="{{ old('father_phone') }}" placeholder="Father Phone">
                                    @error('father_phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="father_photo">Father Photo</label>
                                    <input type="file" name="father_photo" id="father_photo" class="form-control form-control-sm @error('father_photo') is-invalid @enderror" accept="image/*">
                                    @error('father_photo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label" for="mathor_name">Mother Name</label>
                                    <input type="text" name="mother_name" id="mother_name" class="form-control form-control-sm @error('mother_name') is-invalid @enderror" value="{{ old('mother_name') }}" placeholder="Mother Name">
                                    @error('mother_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="mother_phone">Mother Phone</label>
                                    <input type="text" name="mother_phone" id="mother_phone" class="form-control form-control-sm @error('mother_phone') is-invalid @enderror" value="{{ old('mother_phone') }}" placeholder="Mother Phone">
                                    @error('mother_phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="mother_photo">Mother Photo</label>
                                    <input type="file" name="mother_photo" id="mother_photo" class="form-control form-control-sm @error('mother_photo') is-invalid @enderror" accept="image/*">
                                    @error('mother_photo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 my-2 py-2 bg-light rounded px-3">
                                    <span class="fw-bold text-secondary me-3 small">If Guardian is :</span>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input g-check" type="radio" name="is_guardian" id="g_father" value="father" {{ old('is_guardian', 'father') == 'father' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="g_father">Father</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input g-check" type="radio" name="is_guardian" id="g_mother" value="mother" {{ old('is_guardian') == 'mother' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="g_mother">Mother</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input g-check" type="radio" name="is_guardian" id="g_other" value="other" {{ old('is_guardian') == 'other' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="g_other">Other</label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label" for="guardian_name">Guardian Name <span class="text-danger">*</span></label>
                                    <input type="text" name="guardian_name" id="guardian_name" class="form-control form-control-sm @error('guardian_name') is-invalid @enderror" value="{{ old('guardian_name') }}" readonly>
                                    @error('guardian_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="guardian_relation">Guardian Relation <span class="text-danger">*</span></label>
                                    <input type="text" name="guardian_relation" id="guardian_relation" class="form-control form-control-sm @error('guardian_relation') is-invalid @enderror" value="{{ old('guardian_relation', 'Father') }}" readonly>
                                    @error('guardian_relation') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="guardian_email">Guardian Email</label>
                                    <input type="email" name="guardian_email" id="guardian_email" class="form-control form-control-sm @error('guardian_email') is-invalid @enderror" value="{{ old('guardian_email') }}" placeholder="guardian@example.com">
                                    @error('guardian_email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label" for="guardian_phone">Guardian Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="guardian_phone" id="guardian_phone" class="form-control form-control-sm @error('guardian_phone') is-invalid @enderror" value="{{ old('guardian_phone') }}" readonly>
                                    @error('guardian_phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="guardian_address">Guardian Address</label>
                                    <input type="text" name="guardian_address" id="guardian_address" class="form-control form-control-sm @error('guardian_address') is-invalid @enderror" value="{{ old('guardian_address') }}" placeholder="Complete Residential Address">
                                    @error('guardian_address') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="guardian_photo">Guardian Photo</label>
                                    <input type="file" name="guardian_photo" id="guardian_photo" class="form-control form-control-sm @error('guardian_photo') is-invalid @enderror" accept="image/*" disabled>
                                    @error('guardian_photo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                {{-- 👇 NAYA — Guardian Login Password --}}
                                <div class="col-md-6">
                                    <label class="form-label" for="guardian_password">Guardian System Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="guardian_password" id="guardian_password" class="form-control form-control-sm @error('guardian_password') is-invalid @enderror" placeholder="••••••••">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="guardian_password">
                                            <i class="bx bx-hide" id="guardian_password_icon"></i>
                                        </button>
                                    </div>
                                    @error('guardian_password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="guardian_password_confirmation">Confirm Guardian Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="guardian_password_confirmation" id="guardian_password_confirmation" class="form-control form-control-sm @error('guardian_password_confirmation') is-invalid @enderror" placeholder="••••••••">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="guardian_password_confirmation">
                                            <i class="bx bx-hide" id="guardian_password_confirmation_icon"></i>
                                        </button>
                                    </div>
                                    @error('guardian_password_confirmation') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. IDENTIFICATION DOCUMENTS --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDocs" aria-expanded="false" aria-controls="collapseDocs">
                            <i class="bx bx-id-card me-2"></i> Identification Documents
                        </button>
                    </h2>
                    <div id="collapseDocs" class="accordion-collapse collapse" data-bs-parent="#admissionAccordion">
                        <div class="accordion-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="father_cnic">Father CNIC No.</label>
                                    <input type="text" name="father_cnic" id="father_cnic" class="form-control form-control-sm @error('father_cnic') is-invalid @enderror" value="{{ old('father_cnic') }}" placeholder="eg: 42101-1234567-8">
                                    @error('father_cnic') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="father_cnic_front">Father CNIC Front</label>
                                    <input type="file" name="father_cnic_front" id="father_cnic_front" class="form-control form-control-sm @error('father_cnic_front') is-invalid @enderror" accept="image/*">
                                    @error('father_cnic_front') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="father_cnic_back">Father CNIC Back</label>
                                    <input type="file" name="father_cnic_back" id="father_cnic_back" class="form-control form-control-sm @error('father_cnic_back') is-invalid @enderror" accept="image/*">
                                    @error('father_cnic_back') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label" for="mother_cnic">Mother CNIC No.</label>
                                    <input type="text" name="mother_cnic" id="mother_cnic" class="form-control form-control-sm @error('mother_cnic') is-invalid @enderror" value="{{ old('mother_cnic') }}" placeholder="eg: 42101-1234567-9">
                                    @error('mother_cnic') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="mother_cnic_front">Mother CNIC Front</label>
                                    <input type="file" name="mother_cnic_front" id="mother_cnic_front" class="form-control form-control-sm @error('mother_cnic_front') is-invalid @enderror" accept="image/*">
                                    @error('mother_cnic_front') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="mother_cnic_back">Mother CNIC Back</label>
                                    <input type="file" name="mother_cnic_back" id="mother_cnic_back" class="form-control form-control-sm @error('mother_cnic_back') is-invalid @enderror" accept="image/*">
                                    @error('mother_cnic_back') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. LOGIN SECURITY SETUP --}}
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSecurity" aria-expanded="false" aria-controls="collapseSecurity">
                            <i class="bx bx-shield-quarter me-2"></i> Login Security Setup
                        </button>
                    </h2>
                    <div id="collapseSecurity" class="accordion-collapse collapse" data-bs-parent="#admissionAccordion">
                        <div class="accordion-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="password">Student System Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control form-control-sm @error('password') is-invalid @enderror" placeholder="••••••••">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                            <i class="bx bx-hide" id="password_icon"></i>
                                        </button>
                                    </div>
                                    @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-sm @error('password_confirmation') is-invalid @enderror" placeholder="••••••••">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                                            <i class="bx bx-hide" id="password_confirmation_icon"></i>
                                        </button>
                                    </div>
                                    @error('password_confirmation') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> {{-- /accordion --}}

            {{-- FEE CONCESSION / DISCOUNT (dynamic — class select hone par khulta hai) --}}
            <div class="card mt-3 d-none" id="discount_card">
                <div class="card-header bg-white">
                    <i class="bx bx-discount me-2"></i> Fee Concession / Discount
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Ek se zyada discount select kiye ja sakte hain (agar alag fee types pe hon).</p>
                    <div class="row g-3" id="discountPolicyList">
                        @foreach($discountPolicies as $policy)
                        <div class="col-md-4">
                            <div class="form-check border rounded-3 p-3">
                                <input class="form-check-input discount-policy-check" type="checkbox"
                                    name="discount_policy_ids[]" value="{{ $policy->id }}"
                                    id="policy_{{ $policy->id }}"
                                    data-policy-type="{{ $policy->policy_type }}"
                                    data-category-id="{{ $policy->category_id }}"
                                    data-trigger="{{ $policy->trigger_value }}">
                                <label class="form-check-label small ms-1" for="policy_{{ $policy->id }}">
                                    <strong>{{ $policy->name }}</strong><br>
                                    <span class="badge bg-secondary text-uppercase">
                                        {{ $policy->policy_type === 'category' ? ($policy->category->name ?? 'Category') : $policy->policy_type }}
                                    </span>
                                    <span class="badge bg-light text-dark border">{{ $policy->feeType->name ?? 'All Fees' }}</span>
                                    <span class="text-success">
                                        {{ $policy->discount_type === 'percentage' ? $policy->discount_value.'%' : 'Rs '.number_format($policy->discount_value) }}
                                    </span>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <label class="form-label small text-secondary" for="discount_remarks">Remarks (optional)</label>
                        <input type="text" name="discount_remarks" id="discount_remarks" class="form-control form-control-sm" placeholder="e.g. Scholarship approved by Principal">
                    </div>
                </div>
            </div>

            {{-- FEE STRUCTURE ALLOCATION (dynamic) --}}
            <div class="card mt-3 d-none" id="fee_structure_card">
                <div class="card-header bg-white">
                    <i class="bx bx-money me-2"></i> Fee Structure Allocation
                </div>
                <div class="card-body">
                    <div id="fee_loader" class="text-center py-3 d-none">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 mb-0 small text-muted">Compiling financial matrix...</p>
                    </div>
                    <div id="fee_structure_container"></div>
                </div>
            </div>

            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bx bx-save me-1"></i> Complete Enrollment
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@php
$validationErrorsJson = json_encode($errors->all());
@endphp

@push('scripts')
<script>
    $(document).ready(function() {

        // --- TOASTR GLOBAL CONFIGURATION ---
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "6000",
            "showDuration": "300",
            "hideDuration": "1000"
        };

        // --- DIRECT SESSION CHECK ---
        @if(Session::has('toastr-success') || session('toastr-success'))
        toastr.success("{!! session('toastr-success') !!}", "Success Simulation");
        @endif

        @if(Session::has('toastr-error') || session('toastr-error'))
        toastr.error("{!! session('toastr-error') !!}", "Execution Failure");
        @endif

        // --- 1. INITIAL SYSTEM ACTIONS ---
        if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_RELOAD) {
            if ($('#studentAdmissionForm').length > 0) {
                $('#studentAdmissionForm')[0].reset();
                toastr.info("Form has been reset due to page refresh.");
            }
        }

        // --- 2. DYNAMIC CLASS AND SECTIONS PIPELINE ---
        $('#class_id').on('change', function() {
            var classId = $(this).val();
            var sectionDropdown = $('#section_id');
            sectionDropdown.empty().append('<option value="">-- Select Section --</option>');

            $('#admission_no').val('');
            $('#roll_number').val('Auto Generating...');
            hideFeeCard();

            var classText = $(this).find('option:selected').text().trim().toLowerCase();
            if (classText.includes('9') || classText.includes('10') || classText.includes('matric') || classText.includes('eleven') || classText.includes('twelve')) {
                $('#groupAsterisk').removeClass('d-none');
            } else {
                $('#groupAsterisk').addClass('d-none');
            }

            if (!classId) {
                $('#roll_number').val('');
                return;
            }

            $.ajax({
                url: "/students/get-sections/" + classId,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.status === 'success' && response.data.length > 0) {
                        $.each(response.data, function(key, value) {
                            sectionDropdown.append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    } else {
                        $('#roll_number').val('');
                        toastr.warning("No active sections configuration detected.", "Data Check");
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error("Critical architecture breakdown loading sections.", "Pipeline Error");
                }
            });
        });

        // --- 3. LIVE ACADEMIC IDENTIFIERS & FEE ENGINE ---
        $(document).on('change', "select[name='class_id'], select[name='section_id'], select[name='group_id']", function() {
            let classId = $("select[name='class_id']").val();
            let sectionId = $("select[name='section_id']").val();
            let groupId = $("select[name='group_id']").val();

            if (classId && sectionId) {
                $.ajax({
                    url: "{{ route('students.get_identifiers') }}",
                    type: "GET",
                    data: {
                        class_id: classId,
                        section_id: sectionId,
                        group_id: groupId
                    },
                    success: function(res) {
                        if (res.success) {
                            $("#admission_no").val(res.admission_no);
                            $("#roll_number").val(res.roll_no);
                        }
                    },
                    error: function() {
                        toastr.error("Failed to compile real-time academic identifiers.", "Engine Failure");
                    }
                });

                fetchFeeStructure(classId);
            } else {
                hideFeeCard();
            }
        });

        function fetchFeeStructure(classId) {
            $('#fee_structure_card').removeClass('d-none');
            $('#discount_card').removeClass('d-none');
            $('#fee_loader').removeClass('d-none');
            $('#fee_structure_container').empty();

            let selectedPolicyIds = $('.discount-policy-check:checked').map(function() {
                return $(this).val();
            }).get();

            $.ajax({
                url: "{{ route('students.get_fee_structure') }}",
                type: "GET",
                data: {
                    class_id: classId,
                    discount_policy_ids: selectedPolicyIds
                },
                success: function(res) {
                    $('#fee_loader').addClass('d-none');
                    $('#fee_structure_container').html(res.success ? res.html : '<div class="alert alert-danger mb-0 small">Fee structure load nahi ho saki.</div>');
                },
                error: function() {
                    $('#fee_loader').addClass('d-none');
                    $('#fee_structure_container').html('<div class="alert alert-danger mb-0 small">Fetch fail ho gaya.</div>');
                }
            });
        }

        function hideFeeCard() {
            $('#fee_structure_card').addClass('d-none');
            $('#fee_structure_container').empty();
        }

        // --- 4. GUARDIAN SYNCHRONIZATION ---
        function handleGuardianSync() {
            if ($('#sibling-alert-banner').length > 0) {
                return;
            }

            var selection = $('.g-check:checked').val();
            var fatherName = $('#father_name').val();
            var fatherPhone = $('#father_phone').val();
            var motherName = $('#mother_name').val();
            var motherPhone = $('#mother_phone').val();

            var gName = $('#guardian_name');
            var gPhone = $('#guardian_phone');
            var gRelation = $('#guardian_relation');
            var gPhoto = $('#guardian_photo');

            if (selection === 'father') {
                gName.val(fatherName).attr('readonly', true);
                gPhone.val(fatherPhone).attr('readonly', true);
                gRelation.val('Father').attr('readonly', true);
                gPhoto.attr('disabled', true).val('');
                gPhoto.closest('.col-md-4').css('opacity', '0.5');
            } else if (selection === 'mother') {
                gName.val(motherName).attr('readonly', true);
                gPhone.val(motherPhone).attr('readonly', true);
                gRelation.val('Mother').attr('readonly', true);
                gPhoto.attr('disabled', true).val('');
                gPhoto.closest('.col-md-4').css('opacity', '0.5');
            } else {
                if (gName.attr('readonly')) gName.val('').attr('readonly', false);
                if (gPhone.attr('readonly')) gPhone.val('').attr('readonly', false);
                if (gRelation.attr('readonly')) gRelation.val('').attr('readonly', false);
                gPhoto.attr('disabled', false);
                gPhoto.closest('.col-md-4').css('opacity', '1');
            }
        }

        $(document).on('input', '#father_name, #father_phone, #mother_name, #mother_phone', handleGuardianSync);
        $('.g-check').on('change', handleGuardianSync);
        handleGuardianSync();

        // --- 5. IMAGE PREVIEW ---
        $('#student_photo').on('change', function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#student_preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // --- 6. PASSWORD TOGGLE ---
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

        // --- 7. BACKEND VALIDATION ERRORS TRIGGER ---
        let validationErrors = {!! $validationErrorsJson !!}; // 👈 extra { } hata diye
        if (validationErrors && validationErrors.length > 0) {
            toastr.error("Please fill all required fields correctly.", "Validation Error");

            validationErrors.forEach(function(error) {
                console.error("SmartSchool Validation Error: " + error);
            });
        }

        // --- 8. SIBLING AUTO-FETCH & FIELD LOCK ENGINE ---
        $('#father_cnic').on('blur keyup', function() {
            let cnicValue = $(this).val().trim();

            if (cnicValue.length >= 13) {
                $.ajax({
                    url: '/students/check-parent/' + cnicValue,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.sibling_detected) {
                            toastr.success('Sibling Sync Matrix Triggered! Parent profiles successfully loaded.');

                            if ($('#sibling-alert-banner').length === 0) {
                                $('.parent-details-heading').after(`
                                    <div id="sibling-alert-banner" class="alert alert-info d-flex align-items-center shadow-sm p-3 mb-4 border-left-info" role="alert">
                                        <i class="bx bx-group fa-lg mr-3 me-2" style="font-size:24px;"></i>
                                        <div>
                                            <strong>Sibling Connection Matrix:</strong> This parent profile is already mapped with 
                                            <span class="badge bg-primary font-weight-bold mx-1">${response.children_count}</span> registered student(s). 
                                            <br><small class="text-muted">Dynamic Sibling Matrix Applied: <strong>${response.discount_percent}% Concession Template recommended.</strong></small>
                                        </div>
                                    </div>
                                `);
                            }

                            $('#father_name').val(response.parent_data.father_name).prop('readonly', true);
                            $('#father_phone').val(response.parent_data.father_phone).prop('readonly', true);
                            $('#mother_name').val(response.parent_data.mother_name).prop('readonly', true);
                            $('#mother_phone').val(response.parent_data.mother_phone).prop('readonly', true);
                            $('#mother_cnic').val(response.parent_data.mother_cnic).prop('readonly', true);
                            $('#guardian_name').val(response.parent_data.guardian_name).prop('readonly', true);
                            $('#guardian_relation').val(response.parent_data.guardian_relation).prop('readonly', true);
                            $('#guardian_phone').val(response.parent_data.guardian_phone).prop('readonly', true);

                            if (response.parent_data.guardian_email) {
                                $('#guardian_email').val(response.parent_data.guardian_email);
                            }
                            $('#guardian_email').prop('readonly', true);

                            if (response.parent_data.guardian_address) {
                                $('#guardian_address').val(response.parent_data.guardian_address);
                            }
                            $('#guardian_address').prop('readonly', true);

                            if (response.parent_data.is_guardian) {
                                $('.g-check').prop('disabled', true);
                                $(`.g-check[value="${response.parent_data.is_guardian}"]`).prop('checked', true).prop('disabled', false);
                            }

                            if ($('.locked-lbl').length === 0) {
                                $('#father_photo, #mother_photo, #guardian_photo, #father_cnic_front, #father_cnic_back, #mother_cnic_front, #mother_cnic_back')
                                    .prop('disabled', true)
                                    .val('')
                                    .closest('.col-md-4')
                                    .css('opacity', '0.55')
                                    .find('label').append(' <span class="badge bg-secondary text-white locked-lbl" style="font-size:9px;">Linked</span>');
                            }

                            // 👇 NAYA — sibling discount policy auto-select karo
                            let nextChildNumber = response.children_count + 1;
                            let siblingCheck = $('.discount-policy-check[data-policy-type="sibling"][data-trigger="' + nextChildNumber + '"]');

                            if (siblingCheck.length > 0) {
                                siblingCheck.prop('checked', true);
                                toastr.info('Sibling discount (Child #' + nextChildNumber + ') auto-selected.');
                                if ($("select[name='class_id']").val()) {
                                    fetchFeeStructure($("select[name='class_id']").val());
                                }
                            }

                        } else {
                            resetSiblingStructuralSync();
                        }
                    }
                }).fail(function(xhr, status, error) {
                    toastr.error('Verification Framework Latency Warning: Parent data stream check failed.');
                    console.error("AJAX Interface Stack Failure Logged: ", error);
                });
            } else {
                resetSiblingStructuralSync();
            }
        });

        function resetSiblingStructuralSync() {
            $('#sibling-alert-banner').remove();
            $('.locked-lbl').remove();

            $('#father_name, #father_phone, #mother_name, #mother_phone, #mother_cnic, #guardian_name, #guardian_relation, #guardian_phone, #guardian_email, #guardian_address')
                .prop('readonly', false);

            $('.g-check').prop('disabled', false);

            $('#father_photo, #mother_photo, #guardian_photo, #father_cnic_front, #father_cnic_back, #mother_cnic_front, #mother_cnic_back')
                .prop('disabled', false)
                .closest('.col-md-4')
                .css('opacity', '1');

            // 👇 NAYA — sibling discounts bhi uncheck karo agar reset ho raha hai
            $('.discount-policy-check[data-policy-type="sibling"]').prop('checked', false);

            handleGuardianSync();
        }

        // --- 9. SAFE NATIVE FORM SUBMISSION HANDLER ---
        $('#studentAdmissionForm').on('submit', function(e) {
            e.preventDefault();

            $('#father_photo, #mother_photo, #guardian_photo, #father_cnic_front, #father_cnic_back, #mother_cnic_front, #mother_cnic_back, .g-check, #admission_no, #roll_number, #guardian_name, #guardian_phone, #guardian_relation')
                .prop('disabled', false)
                .prop('readonly', false);

            toastr.info("Processing enrollment sequence, please wait...", "System Action");

            this.submit();
        });

        // --- 10. AUTO SELECT DISCOUNT (CATEGORY BASED) ---
        $('#category_id').on('change', function() {
            let selectedCategoryId = $(this).val();

            $('.discount-policy-check[data-policy-type="category"]').prop('checked', false);

            if (selectedCategoryId) {
                let matchingChecks = $('.discount-policy-check[data-policy-type="category"][data-category-id="' + selectedCategoryId + '"]');
                matchingChecks.prop('checked', true);
                if (matchingChecks.length > 0) {
                    toastr.info(matchingChecks.length + ' matching discount(s) auto-selected — deselect any you don\'t want.');
                }
            }

            if ($("select[name='class_id']").val()) {
                fetchFeeStructure($("select[name='class_id']").val());
            }
        });

        // --- 11. MANUAL CHECKBOX TOGGLE → RE-FETCH FEE STRUCTURE ---
        $(document).on('change', '.discount-policy-check', function() {
            if ($("select[name='class_id']").val()) {
                fetchFeeStructure($("select[name='class_id']").val());
            }
        });
    });
</script>
@endpush