@extends($current_layout)

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Staff Leave</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active">Leave Applications</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card shadow-none border">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary">Leave History</h5>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#leaveModal">
            <i class="bx bx-plus"></i> Apply New Leave
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Name Staff</th>
                        <th>Leave Type</th>
                        <th>Dates</th>
                        <th>Total Days</th>
                        <th>Duration</th>
                        <th>Attachment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $key => $app)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ optional($app->user)->name ?? 'User not found' }}</td>
                        <td>{{ ucfirst($app->leave_type) }}</td>
                        <td>{{ $app->from_date }} <br> to <br> {{ $app->to_date }}</td>
                        <td>{{ $app->total_days }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $app->leave_duration)) }}</td>

                        <td class="text-center">
                            @if($app->document)
                            <a href="{{ asset('storage/leave_documents/' . $app->document) }}" target="_blank" class="btn btn-sm btn-info text-white">
                                <i class="bx bx-file"></i> View
                            </a>
                            @else
                            <span class="text-muted">No File</span>
                            @endif
                        </td>

                        {{-- Status Column --}}
                        <td>
                            @can('manage-leave-application')
                            {{-- Admin ke liye: Button jo status change kar sake --}}
                            <button type="button"
                                class="btn btn-sm toggle-leave-status {{ $app->status == 'approved' ? 'btn-success' : ($app->status == 'rejected' ? 'btn-danger' : 'btn-warning') }}"
                                data-url="{{ route('staffleaveapp.updateStatus', $app->id) }}">
                                {{ ucfirst($app->status) }}
                            </button>
                            @else
                            {{-- Receptionist ke liye: Sirf badge (no button behavior) --}}
                            <span class="badge {{ $app->status == 'approved' ? 'bg-success' : ($app->status == 'rejected' ? 'bg-danger' : 'bg-warning') }}">
                                {{ ucfirst($app->status) }}
                            </span>
                            @endcan
                        </td>

                        {{-- Actions Column --}}
                        <td class="text-center">
                            @can('manage-leave-application')
                            {{-- Admin ke liye: Sirf Delete --}}
                            <button type="button" class="btn btn-sm btn-outline-danger delete-leave-btn" data-id="{{ $app->id }}">
                                <i class="bx bx-trash"></i>
                            </button>
                            @else
                            {{-- Receptionist ke liye: Edit aur Delete dono --}}
                            <a href="javascript:void(0)"
                                data-url="{{ route('staffleaveapp.edit', $app->id) }}"
                                class="btn btn-sm btn-outline-primary edit-leave-btn">
                                <i class="bx bx-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger delete-leave-btn" data-id="{{ $app->id }}">
                                <i class="bx bx-trash"></i>
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="leaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('staffleaveapp.store') }}" method="POST" id="leaveForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white p-2">
                    <h5 class="modal-title text-white mb-0">New Leave Application</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="font-size: 0.7rem;" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="apply_date" class="form-label">Apply Date *</label>
                            <input type="date" name="apply_date" id="apply_date" class="form-control @error('apply_date') is-invalid @enderror" value="{{ old('apply_date', date('Y-m-d')) }}">
                            @error('apply_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Staff *</label>
                            @if(auth()->user()->hasRole('admin'))
                            {{-- Admin ke liye Select Dropdown --}}
                            <select name="user_id" id="staff_user_id" class="form-select @error('user_id') is-invalid @enderror">
                                <option value="">Select Staff</option>
                                @foreach($staffs as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                            @else
                            {{-- Staff ke liye Read-only Input --}}
                            <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                            {{-- Hidden input jisme wahi ID hai --}}
                            <input type="hidden" name="user_id" id="staff_user_id" value="{{ auth()->id() }}">
                            @endif
                            @error('user_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="leave_type" class="form-label">Leave Type *</label>
                            <select name="leave_type" id="leave_type"
                                class="form-select @error('leave_type') is-invalid @enderror" required>
                                <option value="">Select Staff First</option>
                            </select>
                            @error('leave_type') <div class="invalid-feedback">{{ $message }}</div> @enderror

                            <div id="balance_container" class="mt-2" style="display: none;">
                                <div class="badge bg-warning-subtle text-danger border border-warning w-100 p-2 text-start fs-7">
                                    <i class="bx bx-info-circle me-1"></i>
                                    <span id="lbl_balance_details"></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start *</label>
                            <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}">
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End *</label>
                            <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}">
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small id="total_days_display" class="text-success fw-bold"></small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Duration:</label><br>
                            <input type="radio" name="leave_duration" id="dur1" value="full" {{ old('leave_duration', 'full') == 'full' ? 'checked' : '' }}>
                            <label for="dur1" class="me-3">Full Day</label>

                            <input type="radio" name="leave_duration" id="dur2" value="first_half" {{ old('leave_duration') == 'first_half' ? 'checked' : '' }}>
                            <label for="dur2" class="me-3">First Half</label>

                            <input type="radio" name="leave_duration" id="dur3" value="second_half" {{ old('leave_duration') == 'second_half' ? 'checked' : '' }}>
                            <label for="dur3">Second Half</label>
                        </div>

                        <div class="col-12">
                            <label for="reason" class="form-label">Reason</label>
                            <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror">{{ old('reason') }}</textarea>
                            @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label for="document" class="form-label">Attachment</label>
                            <input type="file" name="document" id="document" class="form-control @error('document') is-invalid @enderror">
                            @error('document') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Application</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // 1. Toastr & Global Config
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "preventDuplicates": true
        };

        // 2. Helper: Load Leave Types
        function loadLeaveTypes(userId) {
            if (!userId) return;

            $.ajax({
                url: "{{ route('staffleaveapp.getLeaveTypes', ':id') }}".replace(':id', userId),
                type: "GET",
                cache: false,
                success: function(data) {
                    let dropdown = $('#leave_type');
                    dropdown.prop('disabled', false).html('<option value="">Select Type</option>');

                    if (data && data.length > 0) {
                        $.each(data, function(index, item) {
                            let typeName = item.leave_type;
                            dropdown.append('<option value="' + typeName + '">' + typeName.toUpperCase() + '</option>');
                        });
                    } else {
                        dropdown.append('<option value="">No types found</option>');
                    }
                },
                error: function(xhr) {
                    console.error("Error:", xhr);
                    $('#leave_type').html('<option value="">Error loading</option>');
                }
            });
        }

        // 3. EVENT: Trigger on Staff/User change
        $(document).on('change', '#staff_user_id', function() {
            let userId = $(this).val();
            if (userId) {
                loadLeaveTypes(userId);
            }
        });

        // 4. EVENT: Fetch Balance
        $('#leave_type').on('change', function() {
            let userId = $('#staff_user_id').val();
            let leaveType = $(this).val();
            let container = $('#balance_container');
            let lblDetails = $('#lbl_balance_details');

            if (userId && leaveType) {
                $.ajax({
                    url: "{{ route('staffleaveapp.getBalance', ['user_id' => ':id', 'leave_type' => ':type']) }}"
                        .replace(':id', userId)
                        .replace(':type', leaveType),
                    type: "GET",
                    cache: false,
                    success: function(res) {
                        container.show();
                        lblDetails.html(`Allowed: <b>${res.total_allowed}</b> | Used: <b>${res.used}</b> | Balance: <b>${res.balance}</b> days`);
                    }
                });
            } else {
                container.hide();
            }
        });

        // 5. EVENT: Date Calculation
        $('#start_date, #end_date').on('change', function() {
            let start = new Date($('#start_date').val());
            let end = new Date($('#end_date').val());
            let display = $('#total_days_display');

            if ($('#start_date').val() && $('#end_date').val()) {
                if (end >= start) {
                    let diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                    display.text("Total: " + diff + " days").removeClass('text-danger').addClass('text-success');
                } else {
                    display.text("Invalid Date Range").removeClass('text-success').addClass('text-danger');
                }
            }
        });

        // 6. FORM: Leave Application Submit
        $(document).on('submit', '#leaveForm', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = form.find('button[type="submit"]');

            btn.prop('disabled', true).text('Saving...');
            $('.invalid-feedback').remove();
            $('.is-invalid').removeClass('is-invalid');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: new FormData(this),
                contentType: false,
                processData: false,
                success: function(res) {
                    toastr.success(res.success || "Saved successfully!");
                    $('#leaveModal').modal('hide');
                    form[0].reset();
                    setTimeout(() => location.reload(), 1000);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Save Application');
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            let input = form.find('[name="' + key + '"]');
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback d-block">' + value[0] + '</div>');
                        });
                    }
                }
            });
        });

        // 7. EVENT: Toggle Status
        $(document).on('click', '.toggle-leave-status', function() {
            let btn = $(this);
            let url = btn.data('url');
            $.ajax({
                url: url,
                type: 'POST',
                data: { _token: "{{ csrf_token() }}" },
                success: function(res) {
                    toastr.success(res.message);
                    btn.removeClass('btn-success btn-danger btn-warning');
                    if (res.status === 'approved') {
                        btn.addClass('btn-success').text('Approved');
                    } else if (res.status === 'rejected') {
                        btn.addClass('btn-danger').text('Rejected');
                    } else {
                        btn.addClass('btn-warning').text('Pending');
                    }
                },
                error: function(xhr) { toastr.error("Error updating status!"); }
            });
        });

        // 8. Modal Trigger (Yeh ab .ready() ke andar hai)
        $('#leaveModal').on('shown.bs.modal', function() {
            @if(auth()->user()->hasRole('admin'))
                let adminSelectedId = $('#staff_user_id').val();
                if (adminSelectedId) loadLeaveTypes(adminSelectedId);
            @else
                loadLeaveTypes("{{ auth()->id() }}");
            @endif
        });

    }); // End of $(document).ready
</script>
@endpush