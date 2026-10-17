<div class="staff-detail container-fluid p-2">
    {{-- ROW 1: Header (Photo + Basic Info) --}}
    <div class="row g-2 mb-2">
        <div class="col-md-3">
            <div class="border rounded p-1 bg-light text-center">
                <img src="{{ $receptionist->photo_url }}"
                    class="staff-detail-photo rounded" alt="Profile">
            </div>
        </div>
        <div class="col-md-9">
            <h5 class="fw-bold text-primary mb-2">{{ ucfirst($receptionist->first_name) }} {{ ucfirst($receptionist->last_name) }}</h5>
            <div class="staff-detail-grid">
                <div><small class="text-muted">ID:</small>
                    <div class="fw-bold">{{ $receptionist->receptionist_id ?? 'N/A' }}</div>
                </div>
                <div><small class="text-muted">Username:</small>
                    <div class="fw-bold">{{ $receptionist->user->username ?? 'N/A' }}</div>
                </div>
                <div><small class="text-muted">Email:</small>
                    <div class="fw-bold">{{ $receptionist->user->email ?? 'N/A' }}</div>
                </div>
                <div><small class="text-muted">Phone:</small>
                    <div class="fw-bold">{{ $receptionist->phone ?? 'N/A' }}</div>
                </div>
                <div><small class="text-muted">CNIC:</small>
                    <div class="fw-bold">{{ $receptionist->cnic ?: 'N/A' }}</div>
                </div>
                <div><small class="text-muted">Father Name:</small>
                    <div class="fw-bold">{{ $receptionist->father_name ?: 'N/A' }}</div>
                </div>
                <div><small class="text-muted">DOB:</small>
                    <div class="fw-bold">{{ $receptionist->dob ? \Carbon\Carbon::parse($receptionist->dob)->format('d M, Y') : 'N/A' }}</div>
                </div>
                <div><small class="text-muted">Joined:</small>
                    <div class="fw-bold">{{ $receptionist->joining_date ? \Carbon\Carbon::parse($receptionist->joining_date)->format('d M, Y') : 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    {{-- ROW 2: Salary & Bank --}}
    <div class="row g-2 mb-2">
        <div class="col-md-6">
            @php
            $salary = $receptionist->user->salaries->first();
            $net = ($salary->basic_salary ?? 0) + ($salary->allowance ?? 0) - ($salary->deduction ?? 0);
            @endphp
            <div class="alert alert-info p-2 m-0 border-0 shadow-sm h-100">
                <small class="staff-detail-heading text-primary">Salary Information</small>
                <div class="staff-detail-row">
                    <span>Basic:</span>
                    <strong class="text-primary">PKR {{ number_format($salary->basic_salary ?? 0, 0) }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>Allowance:</span>
                    <strong class="text-success">PKR {{ number_format($salary->allowance ?? 0, 0) }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>Deduction:</span>
                    <strong class="text-danger">PKR {{ number_format($salary->deduction ?? 0, 0) }}</strong>
                </div>
                <div class="staff-detail-row staff-detail-total">
                    <span class="fw-bold">Net Salary:</span>
                    <strong>PKR {{ number_format($net, 0) }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            @php
            $bank = $receptionist->user->bankDetail;
            @endphp
            <div class="alert alert-warning p-2 m-0 border-0 shadow-sm h-100">
                <small class="staff-detail-heading text-warning">Bank Details</small>
                <div class="staff-detail-row">
                    <span>Bank:</span>
                    <strong>{{ $bank->bank_name ?? 'N/A' }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>A/C Title:</span>
                    <strong>{{ $bank->account_title ?? 'N/A' }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>A/C No:</span>
                    <strong>{{ $bank->account_number ?? 'N/A' }}</strong>
                </div>
                @if($bank && $bank->iban)
                <div class="staff-detail-row">
                    <span>IBAN:</span>
                    <strong>{{ $bank->iban }}</strong>
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
                    <strong>{{ $receptionist->emergency_name ? ucfirst($receptionist->emergency_name) : 'N/A' }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>Relation:</span>
                    <strong>{{ $receptionist->emergency_relation ? ucfirst($receptionist->emergency_relation) : 'N/A' }}</strong>
                </div>
                <div class="staff-detail-row">
                    <span>Contact:</span>
                    <strong>{{ $receptionist->emergency_phone ?? 'N/A' }}</strong>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="alert alert-primary p-2 m-0 border-0 shadow-sm h-100">
                <small class="staff-detail-heading text-primary">Leave Records</small>
                @if(isset($leaveSettings) && $leaveSettings->count() > 0)
                    @foreach($leaveSettings as $leave)
                    <div class="staff-detail-row">
                        <span>
                            <i class="bx bx-check-circle text-info me-1"></i>
                            {{ ucfirst($leave->leave_type) }}:
                        </span>
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
                    {{-- Current Address --}}
                    <div class="col-md-6"><small class="text-muted">Current Address:</small> <div class="small fw-bold staff-detail-value">{{ $receptionist->address ?? 'N/A' }}</div></div>
                    {{-- Permanent Address --}}
                    <div class="col-md-6"><small class="text-muted">Permanent Address:</small> <div class="small fw-bold staff-detail-value">{{ $receptionist->permanent_address ?? 'N/A' }}</div></div>
                </div>
            </div>
        </div>
    </div>
</div>
