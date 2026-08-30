@extends($current_layout)

@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Staff Leave</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Apply for Leave</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card shadow-none border">
    <div class="card-body">
        <form action="{{ route('staffleaveapp.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 text-primary">New Leave Application</h5>
                <a href="{{ route('staffleaveapp.index') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-list-ul"></i> My Applications</a>
            </div>
            <hr />

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Apply Date *</label>
                    <input type="date" name="apply_date" class="form-control" value="{{ date('Y-m-d') }}" readonly>
                </div>

                @if(auth()->user()->hasRole('admin'))
                <div class="col-md-3">
                    <label class="form-label">Select Staff *</label>
                    <select name="user_id" id="user_select" class="form-select" required>
                        <option value="">-- Select Staff Member --</option>
                        @foreach($staffs as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <input type="hidden" name="user_id" id="user_select" value="{{ auth()->user()->id }}">
                @endif

                <div class="col-md-4">
                    <label class="form-label">Select Leave Type *</label>
                    <select name="leave_type" id="leave_type" class="form-select" required>
                        <option value="">-- Select Type --</option>
                    </select>
                    <small id="balance_info" class="text-primary fw-bold mt-1 d-block" style="font-size: 0.85rem;"></small>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Start Date *</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">End Date *</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                    <small id="total_days" class="text-success fw-bold"></small>
                </div>

                <div class="col-12">
                    <label class="form-label">Reason / Description</label>
                    <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason for leave..."></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Attachment (Optional)</label>
                    <input type="file" name="document" class="form-control">
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4">Submit Application</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        @if(!auth()->user()->hasRole('admin'))
        $('#user_select').trigger('change');
        @endif
    });

    // Balance AJAX Logic (Updated to show formatting)
    $('#leave_type').on('change', function() {
        var user_id = $('#user_select').val();
        var leave_type = $(this).val();
        var info = $('#balance_info');

        if (user_id && leave_type) {
            $.ajax({
                url: "{{ route('staffleaveapp.getBalance', ['user_id' => ':id', 'leave_type' => ':type']) }}"
                    .replace(':id', user_id)
                    .replace(':type', leave_type),
                type: "GET",
                success: function(response) {
                    // Controller ke variables ke mutabiq keys use ki hain
                    // total_allowed, used, balance
                    info.html(`
                    <span class="text-primary">
                        Allowed: ${response.total_allowed} | Used: ${response.used} | 
                        <strong>Balance: ${response.balance} days</strong>
                    </span>
                `);
                }
            });
        } else {
            info.html("");
        }
    });

    // Date Calculation (Remaining as is)
    $('#start_date, #end_date').on('change', function() {
        var start = new Date($('#start_date').val());
        var end = new Date($('#end_date').val());
        if (start && end && end >= start) {
            var diffDays = Math.ceil(Math.abs(end - start) / (1000 * 60 * 60 * 24)) + 1;
            // Text primary color mein set kiya
            $('#total_days').removeClass('text-success').addClass('text-success').text("Total: " + diffDays + " days");
        } else {
            $('#total_days').text("");
        }
    });

    // Load Leave Types (Remaining as is)
    $('#user_select').on('change', function() {
        var user_id = $(this).val();
        var leaveSelect = $('#leave_type');
        if (user_id) {
            $.ajax({
                url: "{{ route('staffleaveapp.getLeaveTypes', ':id') }}".replace(':id', user_id),
                type: "GET",
                success: function(data) {
                    leaveSelect.empty().append('<option value="">-- Select Type --</option>');
                    $.each(data, function(key, value) {
                        leaveSelect.append('<option value="' + value.leave_type + '">' + value.leave_type + '</option>');
                    });
                }
            });
        }
    });
</script>
@endpush