<div class="staff-detail container-fluid p-2">
    {{-- ROW 1: Header (Photo + Basic Info) --}}
    <div class="row g-2 mb-2">
        <div class="col-md-3">
            <div class="border rounded p-1 bg-light text-center">
                <img src="{{ $accountant->photo_url }}"
                    class="staff-detail-photo rounded" alt="Profile">
            </div>
        </div>
        <div class="col-md-9">
            <h5 class="fw-bold text-primary mb-2">{{ ucfirst($accountant->first_name) }} {{ ucfirst($accountant->last_name) }}</h5>
            <div class="staff-detail-grid">
                <div><small class="text-muted">ID:</small>
                    <div class="fw-bold">{{ $accountant->accountant_id ?? 'N/A' }}</div>
                </div>
                <div><small class="text-muted">Username:</small>
                    <div class="fw-bold">{{ $accountant->user->username ?? 'N/A' }}</div>
                </div>
                <div><small class="text-muted">Email:</small>
                    <div class="fw-bold">{{ $accountant->email ?? 'N/A' }}</div>
                </div>
                <div><small class="text-muted">Phone:</small>
                    <div class="fw-bold">{{ $accountant->phone ?? 'N/A' }}</div>
                </div>
                <div><small class="text-muted">CNIC:</small>
                    <div class="fw-bold">{{ $accountant->cnic ?: 'N/A' }}</div>
                </div>
                <div><small class="text-muted">Father Name:</small>
                    <div class="fw-bold">{{ $accountant->father_name ?: 'N/A' }}</div>
                </div>
                <div><small class="text-muted">DOB:</small>
                    <div class="fw-bold">{{ $accountant->dob ? \Carbon\Carbon::parse($accountant->dob)->format('d M, Y') : 'N/A' }}</div>
                </div>
                <div><small class="text-muted">Joined:</small>
                    <div class="fw-bold">{{ $accountant->joining_date ? \Carbon\Carbon::parse($accountant->joining_date)->format('d M, Y') : 'N/A' }}</div>
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
                    <strong class="text-primary">PKR {{ number_format($accountant->salary->basic_salary ?? 0, 0) }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>Allowance:</span>
                    <strong class="text-success">PKR {{ number_format($accountant->salary->allowance ?? 0, 0) }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>Deduction:</span>
                    <strong class="text-danger">PKR {{ number_format($accountant->salary->deduction ?? 0, 0) }}</strong>
                </div>
                <div class="staff-detail-row staff-detail-total">
                    <span class="fw-bold">Net Salary:</span>
                    <strong>PKR {{ number_format(($accountant->salary->basic_salary ?? 0) + ($accountant->salary->allowance ?? 0) - ($accountant->salary->deduction ?? 0), 0) }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-warning p-2 m-0 border-0 shadow-sm h-100">
                <small class="staff-detail-heading text-warning">Bank Details</small>
                <div class="staff-detail-row">
                    <span>Bank:</span>
                    <strong>{{ $accountant->bankDetail->bank_name ?? 'N/A' }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>A/C Title:</span>
                    <strong>{{ $accountant->bankDetail->account_title ?? 'N/A' }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>A/C No:</span>
                    <strong>{{ $accountant->bankDetail->account_number ?? 'N/A' }}</strong>
                </div>
                @if($accountant->bankDetail && $accountant->bankDetail->iban)
                <div class="staff-detail-row">
                    <span>IBAN:</span>
                    <strong>{{ $accountant->bankDetail->iban }}</strong>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ROW 3: Emergency & Leaves --}}
    <div class="row g-2 mb-2">
        <div class="col-md-6">
            <div class="alert alert-danger p-2 m-0 border-0 shadow-sm h-100">
                <small class="staff-detail-heading text-danger">Emergency Contact</small>
                <div class="staff-detail-row">
                    <span>Name:</span>
                    <strong>{{ ucfirst($accountant->emergency_name ?? 'N/A') }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>Relation:</span>
                    <strong>{{ ucfirst($accountant->emergency_relation ?? 'N/A') }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>Contact:</span>
                    <strong>{{ $accountant->emergency_phone ?? 'N/A' }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-primary p-2 m-0 border-0 shadow-sm h-100">
                <small class="staff-detail-heading text-primary">Leave Records</small>
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
                    <div class="col-md-6"><small class="text-muted">Current Address:</small> <div class="small fw-bold staff-detail-value">{{ $accountant->address ?? 'N/A' }}</div></div>
                    <div class="col-md-6"><small class="text-muted">Permanent Address:</small> <div class="small fw-bold staff-detail-value">{{ $accountant->permanent_address ?? 'N/A' }}</div></div>
                </div>
            </div>
        </div>
    </div>
</div>
