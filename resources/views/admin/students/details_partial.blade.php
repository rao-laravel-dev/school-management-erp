<div class="container-fluid p-2">
    {{-- ROW 1: Header (Photo + Basic Info) --}}
    <div class="row g-2 mb-2">
        <div class="col-md-3">
            <div class="border rounded p-1 bg-light text-center">
                <img src="{{ $student->photo && file_exists(public_path('uploads/students/'.$student->photo)) ? asset('uploads/students/'.$student->photo) : asset('uploads/no_image.jpg') }}"
                    class="img-fluid rounded" style="width: 100%; height: 160px; object-fit: cover;" alt="Profile">
            </div>
        </div>
        <div class="col-md-9">
            <h5 class="fw-bold text-primary mb-2">{{ ucfirst($student->first_name) }} {{ ucfirst($student->last_name) }}</h5>
            <div class="row row-cols-2 g-2">
                <div class="col"><small class="text-muted">Reg No:</small>
                    <div class="fw-bold">{{ $student->admission_no }}</div>
                </div>
                <div class="col"><small class="text-muted">Username:</small>
                    <div class="fw-bold">{{ $student->user->username ?? 'N/A' }}</div>
                </div>
                <div class="col"><small class="text-muted">Class/Section:</small>
                    <div class="fw-bold">{{ $enrollment->schoolClass->name ?? 'N/A' }} ({{ $enrollment->section->name ?? 'N/A' }})</div>
                </div>
                <div class="col"><small class="text-muted">Phone:</small>
                    <div class="fw-bold">{{ $student->parent->father_phone }}</div>
                </div>
                <div class="col"><small class="text-muted">DOB:</small>
                    <div class="fw-bold">{{ \Carbon\Carbon::parse($student->date_of_birth)->format('d M, Y') }}</div>
                </div>
                <div class="col"><small class="text-muted">Admission Date:</small>
                    <div class="fw-bold">{{ \Carbon\Carbon::parse($student->admission_date)->format('d M, Y') }}</div>
                </div>
            </div>
        </div>
    </div>
    <hr>

    {{-- ROW 2: Fee & Parent --}}
    <div class="row g-2 mb-2">
        <div class="col-md-6">
            <div class="alert alert-info p-2 m-0 border-0 shadow-sm">
                <small class="text-uppercase fw-bold text-primary">Fee Information</small>
                <hr class="my-1">
                <div class="row mt-1 small">
                    <div class="col-6">Monthly Fee: <strong class="text-primary">{{ number_format($student->fee_structure ?? 0, 0) }}</strong></div>
                    <div class="col-6">Status: <strong class="text-success">{{ ucfirst($student->status ?? 'Active') }}</strong></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-warning p-2 m-0 border-0 shadow-sm">
                <small class="text-uppercase fw-bold text-warning">Parent Details</small>
                <hr class="my-1">
                <div class="row mt-1 small">
                    <div class="col-6">Father: <strong class="text-dark">{{ $student->parent->father_name ?? 'N/A' }}</strong></div>
                    <div class="col-6">CNIC: <strong class="text-dark">{{ $student->parent->father_cnic ?? 'N/A' }}</strong></div>
                    <div class="col-12">Contact: <strong class="text-dark">{{ $student->parent->father_phone ?? 'N/A' }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 3: Emergency & Medical --}}
    <div class="row g-2 mb-2">
        <div class="col-md-6">
            <div class="alert alert-danger p-2 m-0 border-0 shadow-sm">
                <small class="fw-bold text-uppercase d-block mb-1">Emergency Contact</small>
                <hr class="my-1">
                <div class="small d-flex justify-content-between">
                    <span>Name:</span>
                    {{-- Student ke model mein agar emergency_name field hai to ye use karein --}}
                    <strong>{{ $student->guardian_name ?? ($student->parent->guardian_name ?? 'N/A') }}</strong>
                </div>
                <div class="small d-flex justify-content-between">
                    <span>Relation:</span>
                    {{-- Yahan $student->parent use karein --}}
                    <strong>{{ $student->parent->guardian_relation ? ucfirst($student->parent->guardian_relation) : 'N/A' }}</strong>
                </div>
                <div class="small d-flex justify-content-between">
                    <span>Address:</span>
                    {{-- Yahan $student->parent use karein --}}
                    <strong>{{ $student->parent->guardian_address ? ucfirst($student->parent->guardian_address) : 'N/A' }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-primary p-2 m-0 border-0 shadow-sm">
                <small class="fw-bold text-uppercase d-block mb-1">Medical/Other</small>
                <hr class="my-1">
                <div class="small">Blood Group: <strong>{{ $student->blood_group ?? 'N/A' }}</strong></div>
                <div class="small">Health Issues: <strong>{{ $student->medical_history ?? 'None' }}</strong></div>
            </div>
        </div>
    </div>

    {{-- ROW 4: Address --}}
    <div class="row g-2">
        <div class="col-12">
            <div class="alert alert-success p-2 m-0 border-0 shadow-sm">
                <small class="fw-bold text-uppercase">Residential Address:</small>
                <hr class="my-1">
                <span class="small d-block fw-bold">{{ $student->parent->guardian_address }}</span>
            </div>
        </div>
    </div>

    {{-- ROW 5: Attendance Summary --}}
    <div class="row g-2 mt-1">
        <div class="col-12">
            <div class="alert alert-dark p-2 m-0 border-0 shadow-sm">
                <small class="fw-bold text-uppercase">Attendance Summary</small>
                <hr class="my-1">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="small">Present</div>
                        <strong class="text-success">{{ $enrollment->attendance->where('status', \App\Models\Attendance::STATUS_PRESENT)->count() }}</strong>
                    </div>
                    <div class="col-4">
                        <div class="small">Absent</div>
                        <strong class="text-danger">{{ $enrollment->attendance->where('status', \App\Models\Attendance::STATUS_ABSENT)->count() }}</strong>
                    </div>
                    <div class="col-4">
                        <div class="small">Other</div>
                        <strong class="text-warning">{{ $enrollment->attendance->whereIn('status', [\App\Models\Attendance::STATUS_LATE, \App\Models\Attendance::STATUS_LEAVE])->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>