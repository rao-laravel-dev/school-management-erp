@extends($current_layout)

@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Staff Management</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Assign Leave Quota</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card shadow-none border">
    <div class="card-body">
        <form action="{{ route('staffleavesett.store') }}" method="POST" id="leaveForm">
            @csrf

            <input type="hidden" name="role_id" id="role_id_field" value="">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 text-primary">Assign Leave Quota to Staff</h5>
                <a href="{{ route('staffleavesett.index') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-list-ul"></i> View All Quotas</a>
            </div>
            <hr />

            <div class="row g-3">
                <div class="col-md-4">
    <label class="form-label">Select Role *</label>
    <select name="role_id" id="role_select" class="form-select" required>
        <option value="">-- Select Role --</option>
        @foreach($roles as $role)
            <option value="{{ $role->id }}">{{ $role->name }}</option>
        @endforeach
    </select>
</div>

                <div class="col-md-4">
                    <label class="form-label">Leave Type *</label>
                    <select name="leave_type" class="form-select" required>
                        <option value="medical">Medical Leave</option>
                        <option value="casual">Casual Leave</option>
                        <option value="maternity">Maternity Leave</option>
                        <option value="sick">Sick Leave</option>
                        <option value="mandatory">Mandatory Leave</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Number of Days *</label>
                    <input type="number" name="total_days" class="form-control" placeholder="Enter number of days" required>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <button type="submit" class="btn btn-success px-4">Assign Quota</button>
            </div>
        </form>
    </div>
</div>



@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        
        // 1. Jab Staff Select change ho, tab hidden role_id field update karein
        $('#user_select').on('change', function() {
            var selectedRole = $(this).find(':selected').data('role');
            $('#role_id_field').val(selectedRole);
        });

        // 2. Form Submission
        $('#leaveForm').on('submit', function(e) {
            e.preventDefault();

            // Submit button ko disable karein taake double click na ho
            var submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).text('Processing...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        toastr.success(response.message);
                        
                        // Success ke baad thoda wait karke redirect karein
                        setTimeout(function() {
                            window.location.href = "{{ route('staffleavesett.index') }}";
                        }, 1500);
                    } else {
                        toastr.error(response.message);
                        submitBtn.prop('disabled', false).text('Assign Quota');
                    }
                },
                error: function(xhr) {
                    // Agar validation error ho toh server se message dikhayein
                    var errorMessage = xhr.responseJSON && xhr.responseJSON.message 
                                       ? xhr.responseJSON.message 
                                       : "Something went wrong!";
                    toastr.error(errorMessage);
                    submitBtn.prop('disabled', false).text('Assign Quota');
                }
            });
        });
    });
</script>
@endpush