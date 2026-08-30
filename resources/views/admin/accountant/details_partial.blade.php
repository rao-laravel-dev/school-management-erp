<div class="container-fluid p-2">
    {{-- ROW 1: Header (Photo + Basic Info) --}}
    <div class="row g-2 mb-2">
        <div class="col-md-3">
            <div class="border rounded p-1 bg-light text-center">
                <img src="{{ $accountant->photo && file_exists(public_path('uploads/accountants/'.$accountant->photo)) ? asset('uploads/accountants/'.$accountant->photo) : asset('uploads/no_image.jpg') }}"
                    class="img-fluid rounded" style="width: 100%; height: 160px; object-fit: cover;" alt="Profile">
            </div>
        </div>
        <div class="col-md-9">
            <h5 class="fw-bold text-primary mb-2">{{ ucfirst($accountant->first_name) }} {{ ucfirst($accountant->last_name) }}</h5>
            <div class="row row-cols-2 g-2">
                <div class="col"><small class="text-muted">ID:</small>
                    <div class="fw-bold">{{ $accountant->accountant_id ?? 'N/A' }}</div>
                </div>
                <div class="col"><small class="text-muted">Username:</small>
                    <div class="fw-bold">{{ $accountant->user->username ?? 'N/A' }}</div>
                </div>
                <div class="col"><small class="text-muted">Email:</small>
                    <div class="fw-bold">{{ $accountant->email ?? 'N/A' }}</div>
                </div>
                <div class="col"><small class="text-muted">Phone:</small>
                    <div class="fw-bold">{{ $accountant->phone ?? 'N/A' }}</div>
                </div>
                <div class="col"><small class="text-muted">DOB:</small>
                    <div class="fw-bold">{{ $accountant->dob ? \Carbon\Carbon::parse($accountant->dob)->format('d M, Y') : 'N/A' }}</div>
                </div>
                <div class="col"><small class="text-muted">Joined:</small>
                    <div class="fw-bold">{{ $accountant->joining_date ? \Carbon\Carbon::parse($accountant->joining_date)->format('d M, Y') : 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>
    <hr>
    {{-- ROW 2: Salary & Bank --}}
<div class="row g-2 mb-2">
    <div class="col-md-6">
        <div class="alert alert-info p-2 m-0 border-0 shadow-sm">
            <small class="text-uppercase fw-bold text-primary d-block mb-1 border-bottom border-primary-subtle">Salary Information</small>
            <div class="row row-cols-2 mt-1 small">
                <div class="col">Basic: <strong class="text-primary">{{ number_format($accountant->salary->basic_salary ?? 0, 0) }}</strong></div>
                <div class="col">Allow: <strong class="text-success">{{ number_format($accountant->salary->allowance ?? 0, 0) }}</strong></div>
                <div class="col">Dedu: <strong class="text-danger">{{ number_format($accountant->salary->deduction ?? 0, 0) }}</strong></div>
                <div class="col">Net: <strong class="text-dark">{{ number_format(($accountant->salary->basic_salary ?? 0) + ($accountant->salary->allowance ?? 0) - ($accountant->salary->deduction ?? 0), 0) }}</strong></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="alert alert-warning p-2 m-0 border-0 shadow-sm">
            <small class="text-uppercase fw-bold text-warning d-block mb-1 border-bottom border-warning-subtle">Bank Details</small>
            <div class="row mt-1 small">
                <div class="col-6">Bank: <strong class="text-dark">{{ $accountant->bankDetail->bank_name ?? 'N/A' }}</strong></div>
                <div class="col-6">Title: <strong class="text-dark">{{ $accountant->bankDetail->account_title ?? 'N/A' }}</strong></div>
                <div class="col-12">Account No: <strong class="text-dark">{{ $accountant->bankDetail->account_number ?? 'N/A' }}</strong></div>
            </div>
        </div>
    </div>
</div>

{{-- ROW 3: Emergency & Leaves --}}
<div class="row g-2 mb-2">
    <div class="col-md-6">
        <div class="alert alert-danger p-2 m-0 border-0 shadow-sm" style="min-height: 95px;">
            <small class="fw-bold text-uppercase d-block mb-1 border-bottom border-danger-subtle">Emergency Contact</small>
            <div class="small d-flex justify-content-between">
                <span>Name:</span> 
                <strong class="text-truncate ps-2" style="max-width: 70%;">{{ ucfirst($accountant->emergency_name ?? 'N/A') }}</strong>
            </div>
            <div class="small d-flex justify-content-between">
                <span>Relation:</span> <strong>{{ ucfirst($accountant->emergency_relation ?? 'N/A') }}</strong>
            </div>
            <div class="small d-flex justify-content-between">
                <span>Contact:</span> <strong>{{ $accountant->emergency_phone ?? 'N/A' }}</strong>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="alert alert-primary p-2 m-0 border-0 shadow-sm" style="min-height: 95px;">
            <small class="fw-bold text-uppercase d-block mb-1 border-bottom border-primary-subtle">Leave Records</small>
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
            <small class="fw-bold text-uppercase d-block mb-1 border-bottom border-success-subtle">Address Information</small>
            <div class="row">
                <div class="col-md-6"><small class="text-muted">Current:</small> <span class="small d-block fw-bold">{{ $accountant->address ?? 'N/A' }}</span></div>
                <div class="col-md-6"><small class="text-muted">Permanent:</small> <span class="small d-block fw-bold">{{ $accountant->permanent_address ?? 'N/A' }}</span></div>
            </div>
        </div>
    </div>
</div>
</div>