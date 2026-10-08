<div class="container-fluid p-2">
    {{-- ROW 1: Header (Photo + Basic Info) --}}
    <div class="row g-2 mb-2">
        <div class="col-md-3">
    <div class="border rounded p-1 bg-light text-center">
        <img src="{{ $teacher->photo_url }}"
            class="img-fluid rounded" style="width: 100%; height: 160px; object-fit: cover;" alt="Profile">
    </div>
</div>
        <div class="col-md-9">
            <h5 class="fw-bold text-primary mb-2">{{ ucfirst($teacher->first_name) }} {{ ucfirst($teacher->last_name) }}</h5>
            <div class="row row-cols-2 g-2">
                <div class="col"><small class="text-muted">ID:</small>
                    <div class="fw-bold">{{ $teacher->teacher_id ?? 'N/A' }}</div>
                </div>
                <div class="col"><small class="text-muted">Username:</small>
                    <div class="fw-bold">{{ $teacher->user->username ?? 'N/A' }}</div>
                </div>
                <div class="col"><small class="text-muted">Email:</small>
                    <div class="fw-bold">{{ $teacher->email ?? 'N/A' }}</div>
                </div>
                <div class="col"><small class="text-muted">Phone:</small>
                    <div class="fw-bold">{{ $teacher->phone ?? 'N/A' }}</div>
                </div>
                <div class="col"><small class="text-muted">DOB:</small>
                    <div class="fw-bold">{{ $teacher->dob ? \Carbon\Carbon::parse($teacher->dob)->format('d M, Y') : 'N/A' }}</div>
                </div>
                <div class="col"><small class="text-muted">Joined:</small>
                    <div class="fw-bold">{{ $teacher->joining_date ? \Carbon\Carbon::parse($teacher->joining_date)->format('d M, Y') : 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>
<hr>
    {{-- ROW 2: Salary & Bank --}}
    <div class="row g-2 mb-2">
        <div class="col-md-6">
            <div class="alert alert-info p-2 m-0 border-0 shadow-sm">
                <small class="text-uppercase fw-bold text-primary">Salary Information</small>
                <div class="row row-cols-2 mt-1 small">
                    <div class="col">Basic: <strong class="text-primary">{{ number_format($teacher->salary->basic_salary ?? 0, 0) }}</strong></div>
                    <div class="col">Allow: <strong class="text-success">{{ number_format($teacher->salary->allowance ?? 0, 0) }}</strong></div>
                    <div class="col">Dedu: <strong class="text-danger">{{ number_format($teacher->salary->deduction ?? 0, 0) }}</strong></div>
                    <div class="col">Net: <strong class="text-dark">{{ number_format(($teacher->salary->basic_salary ?? 0) + ($teacher->salary->allowance ?? 0) - ($teacher->salary->deduction ?? 0), 0) }}</strong></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-warning p-2 m-0 border-0 shadow-sm">
                <small class="text-uppercase fw-bold text-warning">Bank Details</small>
                <div class="row mt-1 small">
                    <div class="col-6">Bank: <strong class="text-dark">{{ $teacher->bankDetail->bank_name ?? 'N/A' }}</strong></div>
                    <div class="col-6">Title: <strong class="text-dark">{{ $teacher->bankDetail->account_title ?? 'N/A' }}</strong></div>
                    <div class="col-12">Account No: <strong class="text-dark">{{ $teacher->bankDetail->account_number ?? 'N/A' }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 3: Emergency, Classes & Leaves --}}
    <div class="row g-2 mb-2">
        <div class="col-md-4">
           <div class="alert alert-danger p-2 m-0 border-0 shadow-sm mb-3">
    <small class="fw-bold text-uppercase d-block mb-1">Emergency Contact</small>
    
    <div class="small d-flex justify-content-between">
        <span>Name:</span> 
        <strong>{{ $teacher->emergency_name ? ucfirst($teacher->emergency_name) : 'N/A' }}</strong>
    </div>
    <div class="small d-flex justify-content-between">
        <span>Relation:</span> 
        <strong>{{ $teacher->emergency_relation ? ucfirst($teacher->emergency_relation) : 'N/A' }}</strong>
    </div>
    <div class="small d-flex justify-content-between">
        <span>Contact:</span> 
        <strong>{{ $teacher->emergency_phone ?? 'N/A' }}</strong>
    </div>
</div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-info p-2 m-0 border-0 shadow-sm">
                <small class="fw-bold text-uppercase d-block mb-1">Assigned Classes</small>
                <div class="small fw-bold">{{ $teacher->assignments->pluck('schoolClass.name')->implode(', ') ?: 'Not Assigned' }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-warning p-2 m-0 border-0 shadow-sm">
                <small class="fw-bold text-uppercase d-block mb-1">Leave Records</small>
                @if(isset($leaveSettings) && $leaveSettings->count() > 0)
                @foreach($leaveSettings as $leave)
                <div class="small d-flex justify-content-between">
                    <span>{{ ucfirst($leave->leave_type) }}:</span>
                    <strong>{{ $leave->total_days }}</strong>
                </div>
                @endforeach
                @else
                <div class="small text-muted">No leaves assigned.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ROW 4: Address --}}
    <div class="row g-2">
        <div class="col-12">
            <div class="alert alert-success p-2 m-0 border-0 shadow-sm">
                <div class="row">
                    <div class="col-md-6"><small class="fw-bold text-uppercase">Current Address:</small> <span class="small d-block fw-bold">{{ $teacher->address ?? 'N/A' }}</span></div>
                    <div class="col-md-6"><small class="fw-bold text-uppercase">Permanent Address:</small> <span class="small d-block fw-bold">{{ $teacher->permanent_address ?? 'N/A' }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>