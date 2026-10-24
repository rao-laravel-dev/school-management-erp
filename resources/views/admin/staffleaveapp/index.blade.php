@extends($current_layout)

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Staff Leave</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active">Leave Applications</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card shadow-none border">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary">Leave History</h5>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#leaveModal">
            <i class="bx bx-plus"></i> Apply New Leave
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
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
                    @forelse($applications as $key => $app)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ optional($app->user)->name ?? 'User not found' }}</td>
                        <td>{{ ucfirst($app->leave_type) }}</td>
                        <td>{{ \Carbon\Carbon::parse($app->from_date)->format('d-m-Y') }} <br> to <br> {{ \Carbon\Carbon::parse($app->to_date)->format('d-m-Y') }}</td>
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
                                data-status="{{ $app->status }}"
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
                            {{-- Edit sirf pending par (sab roles, non-admin ko list mein sirf apni rows milti hain) --}}
                            @if($app->status == 'pending')
                            <a href="javascript:void(0)"
                                data-url="{{ route('staffleaveapp.edit', $app->id) }}"
                                class="btn btn-sm btn-outline-primary edit-leave-btn">
                                <i class="bx bx-edit"></i>
                            </a>
                            @endif
                            {{-- Delete: admin har row, baqi sirf apni pending --}}
                            @if(auth()->user()->can('manage-leave-application') || $app->status == 'pending')
                            <button type="button" class="btn btn-sm btn-outline-danger delete-leave-btn" data-id="{{ $app->id }}">
                                <i class="bx bx-trash"></i>
                            </button>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No leave requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="leaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('staffleaveapp.store') }}" method="POST" id="leaveForm" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="modal-header bg-success text-white p-2" id="leaveModalHeader">
                    <h5 class="modal-title text-white mb-0" id="leaveModalTitle">New Leave Application</h5>
                    <button type="button" class="btn-close btn-close-white" id="leaveModalClose" data-bs-dismiss="modal" style="font-size: 0.7rem;" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="apply_date" class="small fw-bold">Apply Date *</label>
                            <input type="date" name="apply_date" id="apply_date" class="form-control form-control-sm @error('apply_date') is-invalid @enderror" value="{{ old('apply_date', date('Y-m-d')) }}">
                            @error('apply_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="staff_user_id" class="small fw-bold">Staff *</label>
                            @if(auth()->user()->hasAnyRole(['admin', 'superadmin']))
                            {{-- Admin/Superadmin ke liye Select Dropdown --}}
                            <select name="user_id" id="staff_user_id" class="form-select form-select-sm @error('user_id') is-invalid @enderror">
                                <option value="">Select Staff</option>
                                @foreach($staffs as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ ucfirst(optional($s->roles->first())->name ?: '-') }} - {{ $s->staff_code }})</option>
                                @endforeach
                            </select>
                            @else
                            {{-- Staff ke liye Read-only Input --}}
                            <input type="text" class="form-control form-control-sm" value="{{ auth()->user()->name }}" readonly>
                            {{-- Hidden input jisme wahi ID hai --}}
                            <input type="hidden" name="user_id" id="staff_user_id" value="{{ auth()->id() }}">
                            @endif
                            @error('user_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="leave_type" class="small fw-bold">Leave Type *</label>
                            <select name="leave_type" id="leave_type"
                                class="form-select form-select-sm @error('leave_type') is-invalid @enderror">
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
                            <label for="start_date" class="small fw-bold">Start *</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}">
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="small fw-bold">End *</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}">
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small id="total_days_display" class="text-success fw-bold"></small>
                        </div>

                        <div class="col-12">
                            <label class="small fw-bold">Duration:</label><br>
                            <input type="radio" name="leave_duration" id="dur1" value="full" {{ old('leave_duration', 'full') == 'full' ? 'checked' : '' }}>
                            <label for="dur1" class="me-3">Full Day</label>

                            <input type="radio" name="leave_duration" id="dur2" value="first_half" {{ old('leave_duration') == 'first_half' ? 'checked' : '' }}>
                            <label for="dur2" class="me-3">First Half</label>

                            <input type="radio" name="leave_duration" id="dur3" value="second_half" {{ old('leave_duration') == 'second_half' ? 'checked' : '' }}>
                            <label for="dur3">Second Half</label>
                        </div>

                        <div class="col-12">
                            <label for="reason" class="small fw-bold">Reason (optional)</label>
                            <textarea name="reason" id="reason" class="form-control form-control-sm @error('reason') is-invalid @enderror">{{ old('reason') }}</textarea>
                            @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label for="document" class="small fw-bold">Attachment (optional)</label>
                            <input type="file" name="document" id="document" class="form-control form-control-sm @error('document') is-invalid @enderror">
                            @error('document') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm" id="leaveSubmitBtn"><i class='bx bx-save'></i> Save</button>
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

        // Apply aur Edit dono isi modal se; editData null = apply mode
        const STORE_URL = "{{ route('staffleaveapp.store') }}";
        const UPDATE_URL_TPL = "{{ route('staffleaveapp.update', ':id') }}";
        const DESTROY_URL_TPL = "{{ route('staffleaveapp.destroy', ':id') }}";
        let editData = null;

        // 2. Helper: Load Leave Types (selectedType: edit mode mein pehle se select)
        function loadLeaveTypes(userId, selectedType) {
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

                    // Edit mode: purana type select + balance load
                    if (selectedType) {
                        dropdown.val(selectedType).trigger('change');
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

        // 5. EVENT: Date + Duration Calculation
        // Half day sirf ek din (start = end) par, warna Full Day force
        function calcLeaveDays() {
            let startVal = $('#start_date').val();
            let endVal = $('#end_date').val();
            let display = $('#total_days_display');
            let sameDay = !startVal || !endVal || startVal === endVal;

            $('#dur2, #dur3').prop('disabled', !sameDay);
            if (!sameDay && !$('#dur1').is(':checked')) {
                $('#dur1').prop('checked', true);
            }

            if (!startVal || !endVal) {
                display.text('');
                return;
            }

            let start = new Date(startVal);
            let end = new Date(endVal);
            if (end >= start) {
                let isHalf = $('input[name="leave_duration"]:checked').val() !== 'full';
                let diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                display.text(isHalf ? "Total: 0.5 day" : "Total: " + diff + " days").removeClass('text-danger').addClass('text-success');
            } else {
                display.text("Invalid Date Range").removeClass('text-success').addClass('text-danger');
            }
        }

        $(document).on('change', '#start_date, #end_date, input[name="leave_duration"]', calcLeaveDays);

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
                    calcLeaveDays();
                    setTimeout(() => location.reload(), 1000);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(editData ? "<i class='bx bx-save'></i> Update" : "<i class='bx bx-save'></i> Save");
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let firstMsg = '';
                        let firstEl = null;
                        $.each(errors, function(key, value) {
                            if (!firstMsg) firstMsg = value[0];
                            // Duration radios 3 hain, error sirf ek dafa (Second Half label ke baad)
                            if (key === 'leave_duration') {
                                $('label[for="dur3"]').after('<div class="invalid-feedback d-block">' + value[0] + '</div>');
                                return;
                            }
                            let input = form.find('[name="' + key + '"]');
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback d-block">' + value[0] + '</div>');
                            if (!firstEl) firstEl = input;
                        });
                        // Sirf pehla error toastr mein, pehli invalid field par focus
                        toastr.clear();
                        toastr.error($('<div>').text(firstMsg).html());
                        if (firstEl) firstEl.trigger('focus');
                    } else {
                        // 403 (processed / not owner) ya 500 ka server message
                        toastr.error((xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || 'Something went wrong');
                    }
                }
            });
        });

        // 7. EVENT: Change Status (popup: current status chhor kar baqi option + Cancel)
        $(document).on('click', '.toggle-leave-status', function() {
            let btn = $(this);
            let url = btn.data('url');
            let current = btn.data('status');
            let options = [
                { value: 'approved', label: 'Approved', cls: 'btn btn-success btn-sm' },
                { value: 'rejected', label: 'Rejected', cls: 'btn btn-danger btn-sm' }
            ].filter(function(o) { return o.value !== current; });

            let swalConfig = {
                title: 'Change Status',
                text: 'Select the new status for this leave application.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: options[0].label,
                // fee-assignment-swal = app.css dark mode popup + buttons gap
                buttonsStyling: false,
                customClass: {
                    popup: 'fee-assignment-swal',
                    confirmButton: options[0].cls,
                    cancelButton: 'btn btn-secondary btn-sm'
                }
            };
            if (options.length > 1) {
                swalConfig.showDenyButton = true;
                swalConfig.denyButtonText = options[1].label;
                swalConfig.customClass.denyButton = options[1].cls;
            }

            Swal.fire(swalConfig).then(function(result) {
                if (result.isConfirmed) sendLeaveStatus(btn, url, options[0].value);
                else if (result.isDenied) sendLeaveStatus(btn, url, options[1].value);
            });
        });

        function sendLeaveStatus(btn, url, newStatus) {
            // Double click se bachao: reply aane tak button disabled
            btn.prop('disabled', true);
            $.ajax({
                url: url,
                type: 'POST',
                data: { status: newStatus, _token: $('meta[name="csrf-token"]').attr('content') || $('#leaveForm input[name="_token"]').val() },
                success: function(res) {
                    toastr.success(res.message);
                    btn.removeClass('btn-success btn-danger btn-warning');
                    btn.addClass(res.status === 'approved' ? 'btn-success' : 'btn-danger').text(res.status === 'approved' ? 'Approved' : 'Rejected');
                    btn.data('status', res.status);
                    // Edit/Delete buttons status par depend karte hain, is liye reload
                    setTimeout(function() { location.reload(); }, 1500);
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    toastr.error((xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || 'Could not update the status.');
                }
            });
        }

        // 8. Modal Trigger (Yeh ab .ready() ke andar hai)
        $('#leaveModal').on('shown.bs.modal', function() {
            // Edit mode: us leave ke staff ki types, purana type selected
            if (editData) {
                loadLeaveTypes(editData.user_id, editData.leave_type);
                return;
            }
            @if(auth()->user()->hasAnyRole(['admin', 'superadmin']))
                let adminSelectedId = $('#staff_user_id').val();
                if (adminSelectedId) loadLeaveTypes(adminSelectedId);
            @else
                loadLeaveTypes("{{ auth()->id() }}");
            @endif
        });

        // 9. EVENT: Edit (same modal edit mode mein, AJAX prefill)
        $(document).on('click', '.edit-leave-btn', function() {
            $.get($(this).data('url'))
                .done(function(res) {
                    editData = res;
                    let form = $('#leaveForm');

                    $('#leaveModalTitle').text('Edit Leave Application').removeClass('text-white');
                    $('#leaveModalHeader').removeClass('bg-success text-white').addClass('bg-warning');
                    $('#leaveModalClose').removeClass('btn-close-white');
                    $('#leaveSubmitBtn').html("<i class='bx bx-save'></i> Update");
                    form.attr('action', UPDATE_URL_TPL.replace(':id', res.id));
                    // File upload ke liye POST + _method=PUT
                    form.find('input[name="_method"]').remove();
                    form.append('<input type="hidden" name="_method" value="PUT">');

                    // Apply date aur staff update mein change nahi hote
                    $('#apply_date').val(res.apply_date || '').prop('readonly', true);
                    $('select#staff_user_id').val(res.user_id).prop('disabled', true);

                    $('#start_date').val(res.from_date || '');
                    $('#end_date').val(res.to_date || '');
                    $('input[name="leave_duration"][value="' + (res.leave_duration || 'full') + '"]').prop('checked', true);
                    $('#reason').val(res.reason || '');
                    calcLeaveDays();

                    $('#leaveModal').modal('show');
                })
                .fail(function(xhr) {
                    toastr.error((xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || 'Could not load the leave application.');
                });
        });

        // 10. Modal band: edit mode se wapas apply mode, form hamesha poora reset
        $('#leaveModal').on('hidden.bs.modal', function() {
            let form = $('#leaveForm');

            if (editData) {
                editData = null;
                $('#leaveModalTitle').text('New Leave Application').addClass('text-white');
                $('#leaveModalHeader').removeClass('bg-warning').addClass('bg-success text-white');
                $('#leaveModalClose').addClass('btn-close-white');
                form.attr('action', STORE_URL);
                form.find('input[name="_method"]').remove();
                $('#apply_date').prop('readonly', false);
                $('select#staff_user_id').prop('disabled', false);
            }
            $('#leaveSubmitBtn').prop('disabled', false).html("<i class='bx bx-save'></i> Save");

            form[0].reset();
            form.find('.invalid-feedback').remove();
            form.find('.is-invalid').removeClass('is-invalid');
            $('#balance_container').hide();
            // Admin: staff reset ke baad leave types dobara khali
            if ($('select#staff_user_id').length) {
                $('#leave_type').html('<option value="">Select Staff First</option>');
            }
            calcLeaveDays();
        });

        // Typing/change par us field ka error saaf
        $(document).on('input change', '#leaveForm .is-invalid', function() {
            $(this).removeClass('is-invalid').next('.invalid-feedback').remove();
        });

        // 11. EVENT: Delete (confirm ke baad, AJAX DELETE)
        $(document).on('click', '.delete-leave-btn', function() {
            let deleteBtn = $(this);
            let id = deleteBtn.data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                // fee-assignment-swal = app.css dark mode popup + buttons gap
                buttonsStyling: false,
                customClass: {
                    popup: 'fee-assignment-swal',
                    confirmButton: 'btn btn-danger btn-sm',
                    cancelButton: 'btn btn-secondary btn-sm'
                },
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (!result.isConfirmed) return;
                // Double click se bachao: reply aane tak button disabled
                deleteBtn.prop('disabled', true);
                $.ajax({
                    url: DESTROY_URL_TPL.replace(':id', id),
                    type: 'DELETE',
                    // Teacher/reception/accountant layouts mein global $.ajaxSetup nahi, token khud bhejo
                    data: { _token: $('meta[name="csrf-token"]').attr('content') || $('#leaveForm input[name="_token"]').val() }
                }).done(function(res) {
                    toastr.success(res.message || 'Deleted');
                    setTimeout(() => location.reload(), 3000);
                }).fail(function(xhr) {
                    deleteBtn.prop('disabled', false);
                    toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Could not delete the leave record.');
                });
            });
        });
    }); // End of $(document).ready
</script>
@endpush