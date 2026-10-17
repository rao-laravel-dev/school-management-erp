<div class="staff-detail container-fluid p-2">
    {{-- ROW 1: Header (Photo + Basic Info) --}}
    <div class="row g-2 mb-2">
        <div class="col-md-3">
            <div class="border rounded p-1 bg-light text-center">
                <img src="{{ $teacher->photo_url }}"
                    class="staff-detail-photo rounded" alt="Profile">
            </div>
        </div>
        <div class="col-md-9">
            <h5 class="fw-bold text-primary mb-2">{{ ucfirst($teacher->first_name) }} {{ ucfirst($teacher->last_name) }}</h5>
            <div class="staff-detail-grid">
                <div><small class="text-muted">ID:</small>
                    <div class="fw-bold">{{ $teacher->teacher_id ?? 'N/A' }}</div>
                </div>
                <div><small class="text-muted">Username:</small>
                    <div class="fw-bold">{{ $teacher->user->username ?? 'N/A' }}</div>
                </div>
                <div><small class="text-muted">Email:</small>
                    <div class="fw-bold">{{ $teacher->email ?? 'N/A' }}</div>
                </div>
                <div><small class="text-muted">Phone:</small>
                    <div class="fw-bold">{{ $teacher->phone ?? 'N/A' }}</div>
                </div>
                <div><small class="text-muted">CNIC:</small>
                    <div class="fw-bold">{{ $teacher->cnic ?: 'N/A' }}</div>
                </div>
                <div><small class="text-muted">Father Name:</small>
                    <div class="fw-bold">{{ $teacher->father_name ?: 'N/A' }}</div>
                </div>
                <div><small class="text-muted">DOB:</small>
                    <div class="fw-bold">{{ $teacher->dob ? \Carbon\Carbon::parse($teacher->dob)->format('d M, Y') : 'N/A' }}</div>
                </div>
                <div><small class="text-muted">Joined:</small>
                    <div class="fw-bold">{{ $teacher->joining_date ? \Carbon\Carbon::parse($teacher->joining_date)->format('d M, Y') : 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>
<hr>
    {{-- ROW 2: Salary & Bank --}}
    <div class="row g-2 mb-2">
        <div class="col-md-6">
            <div class="alert alert-info p-2 m-0 border-0 shadow-sm h-100">
                <small class="staff-detail-heading text-primary">Salary Information</small>
                <div class="staff-detail-row">
                    <span>Basic:</span>
                    <strong class="text-primary">PKR {{ number_format($teacher->salary->basic_salary ?? 0, 0) }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>Allowance:</span>
                    <strong class="text-success">PKR {{ number_format($teacher->salary->allowance ?? 0, 0) }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>Deduction:</span>
                    <strong class="text-danger">PKR {{ number_format($teacher->salary->deduction ?? 0, 0) }}</strong>
                </div>
                <div class="staff-detail-row staff-detail-total">
                    <span class="fw-bold">Net Salary:</span>
                    <strong>PKR {{ number_format(($teacher->salary->basic_salary ?? 0) + ($teacher->salary->allowance ?? 0) - ($teacher->salary->deduction ?? 0), 0) }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-warning p-2 m-0 border-0 shadow-sm h-100">
                <small class="staff-detail-heading text-warning">Bank Details</small>
                <div class="staff-detail-row">
                    <span>Bank:</span>
                    <strong>{{ $teacher->bankDetail->bank_name ?? 'N/A' }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>A/C Title:</span>
                    <strong>{{ $teacher->bankDetail->account_title ?? 'N/A' }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>A/C No:</span>
                    <strong>{{ $teacher->bankDetail->account_number ?? 'N/A' }}</strong>
                </div>
                @if($teacher->bankDetail && $teacher->bankDetail->iban)
                <div class="staff-detail-row">
                    <span>IBAN:</span>
                    <strong>{{ $teacher->bankDetail->iban }}</strong>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ROW 3: Emergency, Classes & Leaves --}}
    <div class="row g-2 mb-2">
        <div class="col-md-4">
            <div class="alert alert-danger p-2 m-0 border-0 shadow-sm h-100">
                <small class="staff-detail-heading text-danger">Emergency Contact</small>
                <div class="staff-detail-row">
                    <span>Name:</span>
                    <strong>{{ $teacher->emergency_name ? ucfirst($teacher->emergency_name) : 'N/A' }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>Relation:</span>
                    <strong>{{ $teacher->emergency_relation ? ucfirst($teacher->emergency_relation) : 'N/A' }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>Contact:</span>
                    <strong>{{ $teacher->emergency_phone ?? 'N/A' }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-info p-2 m-0 border-0 shadow-sm h-100">
                <small class="staff-detail-heading text-primary">Assigned Classes</small>
                <div class="small fw-bold staff-detail-value">{{ $teacher->assignments->pluck('schoolClass.name')->implode(', ') ?: 'Not Assigned' }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-warning p-2 m-0 border-0 shadow-sm h-100">
                <small class="staff-detail-heading text-warning">Leave Records</small>
                @if(isset($leaveSettings) && $leaveSettings->count() > 0)
                @foreach($leaveSettings as $leave)
                <div class="staff-detail-row">
                    <span>{{ ucfirst($leave->leave_type) }}:</span>
                    <strong>{{ $leave->total_days }} days</strong>
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
                <small class="staff-detail-heading text-success">Address Information</small>
                <div class="row g-2">
                    <div class="col-md-6"><small class="text-muted">Current Address:</small> <div class="small fw-bold staff-detail-value">{{ $teacher->address ?? 'N/A' }}</div></div>
                    <div class="col-md-6"><small class="text-muted">Permanent Address:</small> <div class="small fw-bold staff-detail-value">{{ $teacher->permanent_address ?? 'N/A' }}</div></div>
                </div>
            </div>
        </div>
    </div>
</div>
