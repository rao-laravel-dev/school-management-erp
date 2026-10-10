@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Student Information', 'url' => '#'],
    ['label' => 'Edit Student Profile', 'url' => route('students.edit', $student->id)],
]" />

<div class="row">
    <div class="col-xl-12 mx-auto">

        <form id="studentAdmissionForm" action="{{ route('students.update', $student->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card radius-10 border-top border-0 border-4 border-primary mb-4 shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 text-primary fw-bold"><i class="bx bx-user-pin me-2"></i>Edit Academic & Personal Info</h5>
                        <p class="mb-0 small text-muted">Modify student credentials and academic mapping configuration vectors</p>
                    </div>
                    <a href="{{ route('students.index') }}" class="btn btn-dark btn-sm radius-30 px-3"><i class="bx bx-list-ul me-1"></i>All Students</a>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">Admission No <span class="text-danger">*</span></label>
                            <input type="text" name="admission_no" id="admission_no" class="form-control bg-light fw-bold text-success" value="{{ old('admission_no', $student->admission_no) }}" readonly>
                            @error('admission_no') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">Roll No <span class="text-danger">*</span></label>
                            <input type="text" name="roll_number" id="roll_number" class="form-control bg-light fw-bold text-success" value="{{ old('roll_number', $student->currentEnrollment->roll_no ?? '') }}" readonly>
                            @error('roll_number') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">Class <span class="text-danger">*</span></label>
                            <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror" autocomplete="off">
                                <option value="">-- Select Class --</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id', $student->currentEnrollment->class_id ?? '') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('class_id') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">Section <span class="text-danger">*</span></label>
                            <select name="section_id" id="section_id" class="form-select @error('section_id') is-invalid @enderror" autocomplete="off">
                                <option value="">-- Select Section --</option>
                                @foreach($sections as $section)
                                <option value="{{ $section->id }}" {{ old('section_id', $student->currentEnrollment->section_id ?? '') == $section->id ? 'selected' : '' }}>
                                    {{ $section->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('section_id') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" id="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $student->first_name) }}" placeholder="Student First Name">
                            @error('first_name') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" id="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $student->last_name) }}" placeholder="Student Last Name">
                            @error('last_name') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', $student->date_of_birth) }}">
                            @error('date_of_birth') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">Gender <span class="text-danger">*</span></label>
                            <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror">
                                <option value="">-- Select Gender --</option>
                                <option value="male" {{ old('gender', $student->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">Admission Date <span class="text-danger">*</span></label>
                            <input type="date" name="admission_date" id="admission_date" class="form-control @error('admission_date') is-invalid @enderror" value="{{ old('admission_date', $student->admission_date) }}">
                            @error('admission_date') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">Category</label>
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $c)
                                <option value="{{ $c->id }}" {{ old('category_id', $student->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">Religion</label>
                            <input type="text" name="religion" id="religion" class="form-control @error('religion') is-invalid @enderror" value="{{ old('religion', $student->religion) }}" placeholder="e.g. Islam">
                            @error('religion') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">Caste</label>
                            <input type="text" name="caste" id="caste" class="form-control @error('caste') is-invalid @enderror" value="{{ old('caste', $student->caste) }}" placeholder="e.g. Rajput">
                            @error('caste') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">Blood Group</label>
                            <select name="blood_group" id="blood_group" class="form-select @error('blood_group') is-invalid @enderror">
                                <option value="">-- Select Blood Group --</option>
                                @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg)
                                <option value="{{ $bg }}" {{ old('blood_group', $student->blood_group) == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                @endforeach
                            </select>
                            @error('blood_group') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">House</label>
                            <select name="house_id" id="house_id" class="form-select @error('house_id') is-invalid @enderror">
                                <option value="">-- Select House --</option>
                                @foreach($houses as $h)
                                <option value="{{ $h->id }}" {{ old('house_id', $student->house_id) == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
                                @endforeach
                            </select>
                            @error('house_id') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">Height (ft)</label>
                            <input type="text" name="height" id="height" class="form-control @error('height') is-invalid @enderror" value="{{ old('height', $student->height) }}" placeholder="e.g. 4.5 ft">
                            @error('height') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">Weight (kg)</label>
                            <input type="text" name="weight" id="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ old('weight', $student->weight) }}" placeholder="e.g. 35 kg">
                            @error('weight') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">Measurement Date</label>
                            <input type="date" name="measurement_date" id="measurement_date" class="form-control @error('measurement_date') is-invalid @enderror" value="{{ old('measurement_date', $student->measurement_date) }}">
                            @error('measurement_date') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary small fw-semibold">Group / Stream</label>
                            <select name="group_id" id="group_id" class="form-select @error('group_id') is-invalid @enderror">
                                <option value="">-- Select Group --</option>
                                @foreach($groups as $group)
                                <option value="{{ $group->id }}" {{ old('group_id', $student->currentEnrollment->group_id ?? '') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                            @error('group_id') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-semibold">Student Photo</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="flex-grow-1">
                                    <input type="file" name="student_photo" id="student_photo" class="form-control @error('student_photo') is-invalid @enderror" accept="image/*">
                                    @error('student_photo') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="avatar-preview-wrapper border rounded bg-light d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 38px; min-width: 40px; overflow: hidden;">
                                    <img id="student_preview" src="{{ $student->photo_url }}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-secondary small fw-semibold">Medical History / Internal Remarks</label>
                            <textarea name="medical_history" id="medical_history" class="form-control @error('medical_history') is-invalid @enderror" rows="3" placeholder="Write any medical concerns...">{{ old('medical_history', $student->medical_history) }}</textarea>
                            @error('medical_history') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                    </div>
                </div>
            </div>

            <div class="card radius-10 border-top border-0 border-4 border-primary mb-4 shadow-sm">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 text-primary fw-bold"><i class="bx bx-group me-2"></i>Parent / Guardian Details</h5>
                    <p class="mb-0 small text-muted">Configure structural contact channels and link configuration matrices</p>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Father Name <span class="text-danger">*</span></label>
                            <input type="text" name="father_name" id="father_name" class="form-control @error('father_name') is-invalid @enderror" value="{{ old('father_name', $student->parent->father_name ?? '') }}" placeholder="Father Name">
                            @error('father_name') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Father Phone <span class="text-danger">*</span></label>
                            <input type="text" name="father_phone" id="father_phone" class="form-control @error('father_phone') is-invalid @enderror" value="{{ old('father_phone', $student->parent->father_phone ?? '') }}" placeholder="Father Phone">
                            @error('father_phone') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Father Photo</label>
                            <div class="d-flex gap-2">
                                <input type="file" name="father_photo" id="father_photo" class="form-control @error('father_photo') is-invalid @enderror" accept="image/*">
                                <img id="father_preview" src="{{ $student->parent ? $student->parent->father_photo_url : asset('uploads/no_image.jpg') }}" style="width:38px; height:38px; object-fit:cover" class="border rounded">
                            </div>
                            @error('father_photo') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Mother Name</label>
                            <input type="text" name="mother_name" id="mother_name" class="form-control @error('mother_name') is-invalid @enderror" value="{{ old('mother_name', $student->parent->mother_name ?? '') }}" placeholder="Mother Name">
                            @error('mother_name') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Mother Phone</label>
                            <input type="text" name="mother_phone" id="mother_phone" class="form-control @error('mother_phone') is-invalid @enderror" value="{{ old('mother_phone', $student->parent->mother_phone ?? '') }}" placeholder="Mother Phone">
                            @error('mother_phone') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Mother Photo</label>
                            <div class="d-flex gap-2">
                                <input type="file" name="mother_photo" id="mother_photo" class="form-control @error('mother_photo') is-invalid @enderror" accept="image/*">
                                <img id="mother_preview" src="{{ $student->parent ? $student->parent->mother_photo_url : asset('uploads/no_image.jpg') }}" style="width:38px; height:38px; object-fit:cover" class="border rounded">
                            </div>
                            @error('mother_photo') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 my-2 py-2 bg-light rounded px-3 border-start border-3 border-secondary">
                            <span class="fw-bold text-secondary me-3 small">If Guardian is :</span>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input g-check" type="radio" name="is_guardian" id="g_father" value="father" {{ old('is_guardian', $student->parent->is_guardian ?? 'father') == 'father' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="g_father">Father</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input g-check" type="radio" name="is_guardian" id="g_mother" value="mother" {{ old('is_guardian', $student->parent->is_guardian ?? '') == 'mother' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="g_mother">Mother</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input g-check" type="radio" name="is_guardian" id="g_other" value="other" {{ old('is_guardian', $student->parent->is_guardian ?? '') == 'other' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="g_other">Other</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Guardian Name <span class="text-danger">*</span></label>
                            <input type="text" name="guardian_name" id="guardian_name" class="form-control @error('guardian_name') is-invalid @enderror" value="{{ old('guardian_name', $student->parent->guardian_name ?? '') }}" readonly>
                            @error('guardian_name') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Guardian Relation <span class="text-danger">*</span></label>
                            <input type="text" name="guardian_relation" id="guardian_relation" class="form-control @error('guardian_relation') is-invalid @enderror" value="{{ old('guardian_relation', $student->parent->guardian_relation ?? 'Father') }}" readonly>
                            @error('guardian_relation') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Guardian Email</label>
                            <input type="email" name="guardian_email" id="guardian_email" class="form-control @error('guardian_email') is-invalid @enderror" value="{{ old('guardian_email', $student->parent->guardian_email ?? '') }}" placeholder="guardian@example.com">
                            @error('guardian_email') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Guardian Phone <span class="text-danger">*</span></label>
                            <input type="text" name="guardian_phone" id="guardian_phone" class="form-control @error('guardian_phone') is-invalid @enderror" value="{{ old('guardian_phone', $student->parent->guardian_phone ?? '') }}" readonly>
                            @error('guardian_phone') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Guardian Address</label>
                            <input type="text" name="guardian_address" id="guardian_address" class="form-control @error('guardian_address') is-invalid @enderror" value="{{ old('guardian_address', $student->parent->guardian_address ?? '') }}" placeholder="Complete Residential Address">
                            @error('guardian_address') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Guardian Photo</label>
                            <div class="d-flex gap-2">
                                <input type="file" name="guardian_photo" id="guardian_photo" class="form-control @error('guardian_photo') is-invalid @enderror" accept="image/*" disabled>
                                <img id="guardian_preview" src="{{ $student->parent ? $student->parent->guardian_photo_url : asset('uploads/no_image.jpg') }}" style="width:38px; height:38px; object-fit:cover" class="border rounded">
                            </div>
                            @error('guardian_photo') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card radius-10 border-top border-0 border-4 border-primary mb-4 shadow-sm">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 text-primary fw-bold"><i class="bx bx-id-card me-2"></i>Identification Documents</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Father CNIC No.</label>
                            <input type="text" name="father_cnic" id="father_cnic" class="form-control @error('father_cnic') is-invalid @enderror" value="{{ old('father_cnic', $student->parent->father_cnic ?? '') }}">
                            @error('father_cnic') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Father CNIC Front</label>
                            <div class="d-flex gap-2">
                                <input type="file" name="father_cnic_front" id="father_cnic_front" class="form-control @error('father_cnic_front') is-invalid @enderror" accept="image/*">
                                <img id="father_cnic_front_preview" src="{{ !empty($student->parent->father_cnic_front) ? asset('storage/uploads/documents/'.$student->parent->father_cnic_front) : asset('uploads/no_image.jpg') }}" style="width:38px; height:38px; object-fit:cover" class="border rounded">
                            </div>
                            @error('father_cnic_front') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Father CNIC Back</label>
                            <div class="d-flex gap-2">
                                <input type="file" name="father_cnic_back" id="father_cnic_back" class="form-control @error('father_cnic_back') is-invalid @enderror" accept="image/*">
                                <img id="father_cnic_back_preview" src="{{ !empty($student->parent->father_cnic_back) ? asset('storage/uploads/documents/'.$student->parent->father_cnic_back) : asset('uploads/no_image.jpg') }}" style="width:38px; height:38px; object-fit:cover" class="border rounded">
                            </div>
                            @error('father_cnic_back') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Mother CNIC No.</label>
                            <input type="text" name="mother_cnic" id="mother_cnic" class="form-control @error('mother_cnic') is-invalid @enderror" value="{{ old('mother_cnic', $student->parent->mother_cnic ?? '') }}">
                            @error('mother_cnic') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Mother CNIC Front</label>
                            <div class="d-flex gap-2">
                                <input type="file" name="mother_cnic_front" id="mother_cnic_front" class="form-control @error('mother_cnic_front') is-invalid @enderror" accept="image/*">
                                <img id="mother_cnic_front_preview" src="{{ !empty($student->parent->mother_cnic_front) ? asset('storage/uploads/documents/'.$student->parent->mother_cnic_front) : asset('uploads/no_image.jpg') }}" style="width:38px; height:38px; object-fit:cover" class="border rounded">
                            </div>
                            @error('mother_cnic_front') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Mother CNIC Back</label>
                            <div class="d-flex gap-2">
                                <input type="file" name="mother_cnic_back" id="mother_cnic_back" class="form-control @error('mother_cnic_back') is-invalid @enderror" accept="image/*">
                                <img id="mother_cnic_back_preview" src="{{ !empty($student->parent->mother_cnic_back) ? asset('storage/uploads/documents/'.$student->parent->mother_cnic_back) : asset('uploads/no_image.jpg') }}" style="width:38px; height:38px; object-fit:cover" class="border rounded">
                            </div>
                            @error('mother_cnic_back') <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card radius-10 border-top border-0 border-4 border-primary mb-4 shadow-sm">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 text-primary fw-bold"><i class="bx bx-shield-quarter me-2"></i>Login Security Setup (Leave blank to keep current)</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-semibold">New System Password</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-semibold">Confirm New Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="••••••••">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4 text-end">
                <button type="submit" class="btn btn-primary radius-30 px-4 fw-semibold"><i class="bx bx-save me-1"></i>Update Student Profile</button>
            </div>
        </form>

    </div>
</div>

@endsection
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
            toastr.success("{!! session('toastr-success') !!}", "Success");
        @endif

        @if(Session::has('toastr-error') || session('toastr-error'))
            toastr.error("{!! session('toastr-error') !!}", "Execution Failure");
        @endif

        // --- 1. LIVE ACADEMIC IDENTIFIERS ENGINE ---
        // DB wali original values — admission_no edit par kabhi change nahi hota, roll_no sirf placement change par naya banta hai
        const origAdmissionNo = @json($student->admission_no);
        const origRollNo = @json($student->currentEnrollment->roll_no ?? '');
        const origClass = @json((string) ($student->currentEnrollment->class_id ?? ''));
        const origSection = @json((string) ($student->currentEnrollment->section_id ?? ''));
        const origGroup = @json((string) ($student->currentEnrollment->group_id ?? ''));

        function triggerIdentifierEngine() {
            let classId = $("select[name='class_id']").val();
            let sectionId = $("select[name='section_id']").val();
            let groupId = $("select[name='group_id']").val();

            // Placement original jaisi hai to originals wapas, AJAX nahi
            if ((classId || '') === origClass && (sectionId || '') === origSection && (groupId || '') === origGroup) {
                $("#admission_no").val(origAdmissionNo);
                $("#roll_number").val(origRollNo);
                if (classId && sectionId) {
                    showTemporaryFeeStructure();
                }
                return;
            }

            if (classId) {
                $.ajax({
                    url: "{{ route('students.get_identifiers') }}",
                    type: "GET",
                    data: {
                        class_id: classId,
                        section_id: sectionId,
                        group_id: groupId,
                        student_id: "{{ $student->id ?? '' }}"
                    },
                    success: function(res) {
                        if (res.success) {
                            // admission_no edit par nahi badalta (sirf roll_no preview)
                            $("#roll_number").val(res.roll_no);
                        }
                    },
                    error: function() {
                        console.error("SmartSchool Live Identifier Pipeline Latency detected.");
                    }
                });

                if (classId && sectionId) {
                    showTemporaryFeeStructure();
                } else {
                    hideFeeCard();
                }
            } else {
                hideFeeCard();
            }
        }

        // --- 2. DYNAMIC CLASS AND SECTIONS PIPELINE ---
        $('#class_id').on('change', function() {
            var classId = $(this).val();
            var sectionDropdown = $('#section_id');
            
            sectionDropdown.empty().append('<option value="">-- Select Section --</option>');
            
            // Only overwrite inputs on MANUAL change action
            $('#roll_number').val('Generating...').attr('placeholder', 'Automatic Allocation...'); 
            hideFeeCard();

            var classText = $(this).find('option:selected').text().trim().toLowerCase();
            toggleGroupAsterisk(classText);

            if (!classId) {
                $('#roll_number').val('');
                return;
            }

            triggerIdentifierEngine();

            $.ajax({
                url: "/students/get-sections/" + classId,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.status === 'success' && response.data.length > 0) {
                        $.each(response.data, function(key, value) {
                            // [.toUpperCase() Applied here for section names display fix]
                            sectionDropdown.append('<option value="' + value.id + '">' + value.name.toUpperCase() + '</option>');
                        });
                    } else {
                        toastr.warning("No active sections configuration detected for this class.", "Data Check");
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error("Critical architecture breakdown loading sections.", "Pipeline Error");
                }
            });
        });

        function toggleGroupAsterisk(classText) {
            if (classText.includes('9') || classText.includes('10') || classText.includes('matric') || classText.includes('eleven') || classText.includes('twelve')) {
                $('#groupAsterisk').removeClass('d-none');
            } else {
                $('#groupAsterisk').addClass('d-none');
            }
        }

        // Listeners for manual changes across structural selects
        $(document).on('change', "select[name='section_id'], select[name='group_id']", function() {
            $('#roll_number').val('Updating Engine...');
            triggerIdentifierEngine();
        });

        function showTemporaryFeeStructure() {
            $('#fee_structure_card').removeClass('d-none');
            $('#fee_loader').addClass('d-none');

            let temporaryHtml = `
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fee Head</th>
                            <th>Type</th>
                            <th class="text-end">Amount (PKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="fw-bold text-dark">Tuition Fee</span></td>
                            <td><span class="badge bg-success">Monthly</span></td>
                            <td class="text-end fw-bold">--- (Pending Fee CRUD)</td>
                        </tr>
                    </tbody>
                </table>
            </div>`;
            $('#fee_structure_container').html(temporaryHtml);
        }

        function hideFeeCard() {
            $('#fee_structure_card').addClass('d-none');
            $('#fee_structure_container').empty();
        }

        // --- 3. GUARDIAN SYNCHRONIZATION ---
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
                gPhoto.attr('disabled', true);
                gPhoto.closest('.col-md-4').css('opacity', '0.5');
            } else if (selection === 'mother') {
                gName.val(motherName).attr('readonly', true);
                gPhone.val(motherPhone).attr('readonly', true);
                gRelation.val('Mother').attr('readonly', true);
                gPhoto.attr('disabled', true);
                gPhoto.closest('.col-md-4').css('opacity', '0.5');
            } else {
                gName.attr('readonly', false);
                gPhone.attr('readonly', false);
                gRelation.attr('readonly', false);
                gPhoto.attr('disabled', false);
                gPhoto.closest('.col-md-4').css('opacity', '1');
            }
        }

        $(document).on('input', '#father_name, #father_phone, #mother_name, #mother_phone', handleGuardianSync);
        $('.g-check').on('change', handleGuardianSync);

        // --- 4. IMAGE PREVIEW ---
        $('#student_photo').on('change', function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#student_preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Parent photos + CNIC images: file select hote hi preview update
        var previewMap = {
            '#father_photo': '#father_preview',
            '#mother_photo': '#mother_preview',
            '#guardian_photo': '#guardian_preview',
            '#father_cnic_front': '#father_cnic_front_preview',
            '#father_cnic_back': '#father_cnic_back_preview',
            '#mother_cnic_front': '#mother_cnic_front_preview',
            '#mother_cnic_back': '#mother_cnic_back_preview'
        };
        $.each(previewMap, function(inputSel, previewSel) {
            $(inputSel).on('change', function() {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $(previewSel).attr('src', e.target.result);
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });

        // --- 5. SIBLING AUTO-FETCH ENGINE ---
        function checkParentSibling(cnicValue) {
            if (cnicValue.length >= 13) {
                $.ajax({
                    url: 'students/check-parent/' + cnicValue,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.sibling_detected) {
                            if ($('#sibling-alert-banner').length === 0) {
                                $('.parent-details-heading').after(`
                                    <div id="sibling-alert-banner" class="alert alert-info d-flex align-items-center shadow-sm p-3 mb-4" role="alert">
                                        <i class="bx bx-group me-2" style="font-size:24px;"></i>
                                        <div>
                                            <strong>Sibling Connection Matrix:</strong> Parent profile mapped with secondary database clusters.
                                        </div>
                                    </div>
                                `);
                            }
                            $('#father_name, #father_phone, #mother_name, #mother_phone, #mother_cnic, #guardian_name, #guardian_relation, #guardian_phone, #guardian_email, #guardian_address').prop('readonly', true);
                        } else {
                            resetSiblingStructuralSync();
                        }
                    }
                });
            }
        }

        $('#father_cnic').on('blur keyup', function() {
            checkParentSibling($(this).val().trim());
        });

        function resetSiblingStructuralSync() {
            $('#sibling-alert-banner').remove();
            $('.locked-lbl').remove();
            $('#father_name, #father_phone, #mother_name, #mother_phone, #mother_cnic, #guardian_name, #guardian_relation, #guardian_phone, #guardian_email, #guardian_address').prop('readonly', false);
            $('.g-check').prop('disabled', false);
            handleGuardianSync(); 
        }

        // --- 6. SAFE FORM SUBMISSION ---
        $('#studentAdmissionForm').on('submit', function(e) {
            e.preventDefault();
            $(':disabled', this).prop('disabled', false);
            $('[readonly]', this).prop('readonly', false);
            toastr.info("Updating academic record blueprints...", "System Action");
            this.submit();
        });

        // --- 7. INITIAL HYDRATION ON PAGE LOAD (SAFE CHECK) ---
        // Agar load par fields khali hain (jaise create form), tab engine chalega. 
        // Agar Roll No pehle se mojood hai database wala, to use safe rakha jayega.
        if($('#class_id').val()) {
            toggleGroupAsterisk($('#class_id').find('option:selected').text().trim().toLowerCase());
        }
        
        // Edit page par load par engine kabhi nahi chalta — DB ke admission_no/roll_no hi rehte hain
        if ($("select[name='class_id']").val() && $("select[name='section_id']").val()) {
            showTemporaryFeeStructure();
        }

        if($('#father_cnic').val()) {
            checkParentSibling($('#father_cnic').val().trim());
        }
        handleGuardianSync();

        // --- 8. BACKEND VALIDATION ERRORS (create page jaisa toastr + pehle error tak scroll) ---
        let validationErrors = @json($errors->all());
        if (validationErrors && validationErrors.length > 0) {
            toastr.error("Please fill all required fields correctly.", "Validation Error");

            validationErrors.forEach(function(error) {
                console.error("SmartSchool Validation Error: " + error);
            });

            // Edit page par accordion nahi, sirf pehli error wali field tak scroll
            let firstError = $('.is-invalid').first();
            if (firstError.length) {
                $('html, body').animate({ scrollTop: Math.max(firstError.offset().top - 120, 0) }, 300);
                firstError.trigger('focus');
            }
        }

        // Field change/input par us ka is-invalid aur error text hata do (input, select, file, input-group sab)
        $(document).on('input change', '.is-invalid', function() {
            let field = $(this);
            field.removeClass('is-invalid');
            field.nextAll('.invalid-feedback').first().remove();
            field.closest('.input-group').nextAll('.invalid-feedback').first().remove();
        });
    });
</script>
@endpush