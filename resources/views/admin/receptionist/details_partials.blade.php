<div class="container-fluid p-3">
    <div class="row">
        <div class="col-md-4">
            <div class="p-2 border rounded bg-light text-center">
                <img src="{{ file_exists(public_path('uploads/receptionists/'.$receptionist->photo)) ? asset('uploads/receptionists/'.$receptionist->photo) : asset('uploads/no_image.jpg') }}"
                    class="img-fluid rounded" style="width: 100%; height: 180px; object-fit: cover;" alt="Profile">
            </div>
        </div>

        <div class="col-md-8">
            <h4 class="fw-bold text-primary mb-2">{{ ucfirst($receptionist->first_name) }} {{ ucfirst($receptionist->last_name) }}</h4>
            <table class="table table-sm table-borderless">
                <tr>
                    <td class="text-muted w-25">Receptionist ID:</td>
                    <td class="fw-bold">{{ $receptionist->receptionist_id ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="text-muted w-25">Username:</td>
                    <td class="fw-bold">{{ $receptionist->user->username ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="text-muted w-25">Email:</td>
                    <td class="fw-bold">{{ $receptionist->user->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="text-muted w-25">Phone:</td>
                    <td class="fw-bold">{{ $receptionist->phone ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="text-muted w-25">DOB:</td>
                    <td class="fw-bold">{{ $receptionist->dob ? \Carbon\Carbon::parse($receptionist->dob)->format('d M, Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="text-muted w-25">Joined:</td>
                    <td class="fw-bold">{{ $receptionist->joining_date ? \Carbon\Carbon::parse($receptionist->joining_date)->format('d M, Y') : 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <hr>

    <div class="row mt-3">
        <div class="col-md-6">
            <div class="alert alert-info p-2 border-0 shadow-sm" style="background-color: #e1f5fe;">
                <small class="text-uppercase fw-bold text-primary" style="opacity: 0.8;">Salary Information</small>
                @php
                $salary = $receptionist->user->salaries->first();
                @endphp

                <div class="d-flex flex-column mt-1">
                    <div class="d-flex justify-content-between">
                        <span class="text-dark">Basic:</span>
                        <strong class="text-primary">PKR {{ number_format($salary->basic_salary ?? 0, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-dark">Allowance:</span>
                        <strong class="text-success">PKR {{ number_format($salary->allowance ?? 0, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-dark">Deduction:</span>
                        <strong class="text-danger">PKR {{ number_format($salary->deduction ?? 0, 2) }}</strong>
                    </div>
                    <hr class="my-1 border-info">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold text-dark">Net Salary:</span>
                        @php
                        $net = ($salary->basic_salary ?? 0) + ($salary->allowance ?? 0) - ($salary->deduction ?? 0);
                        @endphp
                        <strong class="text-dark">PKR {{ number_format($net, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-warning p-2 border-0 shadow-sm" style="background-color: #fff3cd;">
                <small class="text-uppercase fw-bold text-warning" style="opacity: 0.9;">Bank Details</small>

                @php
                $bank = $receptionist->user->bankDetail;
                @endphp

                <div class="d-flex flex-column mt-1">
                    <div class="d-flex justify-content-between">
                        <span class="text-dark">Bank:</span>
                        <strong class="text-dark">{{ $bank->bank_name ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-dark">A/C Title:</span>
                        <strong class="text-dark">{{ $bank->account_title ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-dark">A/C No:</span>
                        <strong class="text-dark">{{ $bank->account_number ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

   <div class="row mt-2">
    <div class="col-md-6">
        <div class="alert alert-danger p-2 mb-0 border-0 shadow-sm" style="background-color: #f8d7da;">
            <small class="d-block text-uppercase fw-bold text-danger mb-1">Emergency Contact:</small>
            
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="ps-0 py-0 text-dark">Name:</td>
                    <td class="text-end py-0 fw-bold text-dark">{{ $receptionist->emergency_name ? ucfirst($receptionist->emergency_name) : 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="ps-0 py-0 text-dark">Relation:</td>
                    <td class="text-end py-0 fw-bold text-dark">{{ $receptionist->emergency_relation ? ucfirst($receptionist->emergency_relation) : 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="ps-0 py-0 text-dark">Contact:</td>
                    <td class="text-end py-0 fw-bold text-dark">{{ $receptionist->emergency_phone ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="alert alert-info p-2 mb-0 border-0 shadow-sm" style="background-color: #e0f7fa;">
            <small class="d-block text-uppercase fw-bold text-info mb-1">Leave Records:</small>
            
            @if(isset($leaveSettings) && $leaveSettings->count() > 0)
                <table class="table table-sm table-borderless mb-0">
                    @foreach($leaveSettings as $leave)
                    <tr>
                        <td class="ps-0 py-0 text-dark">
                            <i class="bx bx-check-circle text-info me-1"></i>
                            {{ ucfirst($leave->leave_type) }}
                        </td>
                        <td class="text-end py-0 fw-bold text-dark">
                            {{ $leave->total_days }} days
                        </td>
                    </tr>
                    @endforeach
                </table>
            @else
                <p class="mb-0 text-muted small">No record found.</p>
            @endif
        </div>
    </div>
</div>
    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-success p-3 border-0 shadow-sm" style="background-color: #d1e7dd;">
                <h6 class="text-uppercase fw-bold mb-3" style="color: #0f5132;">Address Information</h6>
                <div class="row">
                    {{-- Current Address --}}
                    <div class="col-md-6 border-end border-success">
                        <small class="d-block" style="color: #1c7430;">Current Address:</small>
                        <strong class="text-dark">{{ $receptionist->address ?? 'N/A' }}</strong>
                    </div>
                    {{-- Permanent Address --}}
                    <div class="col-md-6">
                        <small class="d-block" style="color: #1c7430;">Permanent Address:</small>
                        <strong class="text-dark">{{ $receptionist->permanent_address ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>