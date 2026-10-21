@extends($current_layout)

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Staff Attendance</div>
    <div class="ps-3">
        <nav>
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('staff_attendance.index') }}">Attendance</a></li>
                <li class="breadcrumb-item active">{{ $isEditMode ? 'Edit' : 'Mark' }} Staff Attendance</li>
            </ol>
        </nav>
    </div>
</div>

<div class="att-stats">
    @include('admin.attendance.partials.stat_card', ['label' => 'Total', 'value' => $summary['total'], 'color' => 'primary'])
    @include('admin.attendance.partials.stat_card', ['label' => 'Present', 'value' => $summary['present'], 'color' => 'success', 'id' => 'card-present'])
    @include('admin.attendance.partials.stat_card', ['label' => 'Absent', 'value' => $summary['absent'], 'color' => 'danger', 'id' => 'card-absent'])
    @include('admin.attendance.partials.stat_card', ['label' => 'Half Day', 'value' => $summary['half_day'], 'color' => 'info', 'id' => 'card-half-day'])
    @include('admin.attendance.partials.stat_card', ['label' => 'Leave', 'value' => $summary['leave'], 'color' => 'warning', 'id' => 'card-leave'])
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center att-card-header">
        <h5 class="mb-0">{{ $isEditMode ? 'Edit' : 'Mark' }} Attendance</h5>
        <div class="att-header-actions">
            <a href="{{ route('staff_attendance.index', ['date' => $date]) }}" class="btn btn-sm btn-outline-secondary">
                <i class='bx bx-list-ul'></i> View List
            </a>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" id="holidayToggle" {{ $isHoliday ? 'checked' : '' }}>
                <label class="form-check-label" for="holidayToggle">Mark Today as Holiday</label>
            </div>
        </div>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('staff_attendance.create') }}" class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label" for="date">Date</label>
                <input type="date" name="date" id="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="role">Select Staff</label>
                <select name="role" id="role" class="form-select form-select-sm @error('role') is-invalid @enderror" onchange="this.form.submit()">
                    <option value="">Select Staff Role</option>
                    @foreach($roles as $roleName)
                    <option value="{{ $roleName }}" {{ $roleFilter == $roleName ? 'selected' : '' }}>{{ ucfirst($roleName) }}</option>
                    @endforeach
                </select>
                @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </form>

        <form id="attendanceForm" method="POST" action="{{ $isEditMode ? route('staff_attendance.update') : route('staff_attendance.store') }}">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="role" value="{{ $roleFilter }}">

            @if($isEditMode)
            <div class="alert alert-info text-danger" role="alert">
                <i class="bx bx-info-circle"></i>
                <strong>Note:</strong> Attendance for this date has already been marked. You are currently in <b>Edit Mode</b>.
            </div>
            @endif


            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" id="attendanceTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Staff ID</th>
                            <th>Staff Name</th>
                            <th>Role</th>
                            <th>Attendance Status</th>
                            <th>Marked_At</th>
                            <th>Time Out</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!$roleFilter)
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="alert alert-info mb-0" role="alert">
                                    <i class='bx bx-info-circle'></i> Please select a role first to view and mark attendance.
                                </div>
                            </td>
                        </tr>
                        @else
                        @forelse($staffMembers as $i => $staff)
                        @php $att = $existing[$staff->id] ?? null; @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $staff->staff_code }}</td>
                            <td>{{ $staff->name }}</td>
                            <td>{{ $staff->roles->pluck('name')->implode(', ') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <input type="radio" class="btn-check" name="attendance[{{ $staff->id }}]"
                                        id="p_{{ $staff->id }}" value="present"
                                        {{ ($att->status ?? 'absent') == 'present' ? 'checked' : '' }}>
                                    <label class="btn btn-sm btn-outline-success" for="p_{{ $staff->id }}">P</label>

                                    <input type="radio" class="btn-check" name="attendance[{{ $staff->id }}]"
                                        id="a_{{ $staff->id }}" value="absent"
                                        {{ ($att->status ?? 'absent') == 'absent' ? 'checked' : '' }}>
                                    <label class="btn btn-sm btn-outline-danger" for="a_{{ $staff->id }}">A</label>

                                    <input type="radio" class="btn-check" name="attendance[{{ $staff->id }}]"
                                        id="l_{{ $staff->id }}" value="leave"
                                        {{ ($att->status ?? 'absent') == 'leave' ? 'checked' : '' }}>
                                    <label class="btn btn-sm btn-outline-warning" for="l_{{ $staff->id }}">L</label>

                                    <input type="radio" class="btn-check" name="attendance[{{ $staff->id }}]"
                                        id="hd_{{ $staff->id }}" value="half_day"
                                        {{ ($att->status ?? 'absent') == 'half_day' ? 'checked' : '' }}>
                                    <label class="btn btn-sm btn-outline-info" for="hd_{{ $staff->id }}">HD</label>
                                </div>
                            </td>
                            <td>
                                {{-- NEW: read-only, koi input nahi --}}
                                <small class="text-muted">{{ $att?->marked_at ? $att->marked_at->format('d-m-Y h:i A') : '—' }}</small>
                            </td>
                            <td>
                                <input type="time" name="time_out[{{ $staff->id }}]"
                                    class="form-control form-control-sm time-out-field"
                                    value="{{ $att && $att->time_out ? $att->time_out->format('H:i') : '' }}">
                            </td>
                            <td>
                                <input type="text" name="remarks[{{ $staff->id }}]"
                                    class="form-control form-control-sm"
                                    value="{{ $att->remarks ?? '' }}" placeholder="Optional">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No staff members found for this role.</td>
                        </tr>
                        @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
            {{-- Mass Time Out aur Submit button ek hi row mein, table ke neeche --}}
            <div class="row gy-3 mt-4 pt-3 border-top align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center att-mass-row">
                        <label class="form-label fw-bold mb-0 me-2" style="white-space: nowrap;">Mass Time Out:</label>
                        <div class="input-group input-group-sm" style="max-width: 250px;">
                            <input type="time" id="massTimeOut" class="form-control form-control-sm">
                            <button type="button" class="btn btn-sm btn-warning" id="applyMassTime">Apply to All</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    @can('mark-staff-attendance')
                    <button type="submit" class="btn btn-sm btn-primary px-4" id="saveAttendanceBtn">
                        <i class='bx bx-save'></i> {{ $isEditMode ? 'Update Attendance' : 'Save Attendance' }}
                    </button>
                    @endcan
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {

        // Session flash messages ko toastr se dikhayen
        @if(session('success'))
        toastr.success("{{ session('success') }}");
        @endif
        @if(session('error'))
        toastr.error("{{ session('error') }}");
        @endif

        // Validation error
        @if($errors->any())
        toastr.error("{{ $errors->first() }}", 'Validation Error');
        @endif

        function updateCounters() {
            let present = $('#attendanceTable input[type="radio"][value="present"]:checked').length;
            let absent = $('#attendanceTable input[type="radio"][value="absent"]:checked').length;
            let halfDay = $('#attendanceTable input[type="radio"][value="half_day"]:checked').length;
            let leave = $('#attendanceTable input[type="radio"][value="leave"]:checked').length;

            $('#card-present').text(present);
            $('#card-absent').text(absent);
            $('#card-half-day').text(halfDay);
            $('#card-leave').text(leave);
        }

        updateCounters();
        $(document).on('change', '.btn-check', updateCounters);

        $('#applyMassTime').on('click', function() {
            let val = $('#massTimeOut').val();
            if (!val) {
                toastr.warning('Please select a time first.');
                return;
            }
            $('.time-out-field').val(val);
            toastr.success('Time out applied to all staff.');
        });

        $('#holidayToggle').on('change', function() {
            let toggle = $(this);

            if (toggle.is(':checked')) {
                Swal.fire({
                    title: 'Confirm Holiday?',
                    text: "Do you want to mark today's attendance as a 'Holiday'?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Mark Holiday!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post("{{ route('staff_attendance.mark_holiday') }}", {
                            _token: "{{ csrf_token() }}",
                            date: $('input[name=date]').val()
                        }).done(function(res) {
                            $('.btn-check').prop('disabled', true);
                            $('.btn-group label').addClass('disabled-style');
                            Swal.fire('Done!', res.message, 'success');
                        }).fail(function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'Could not mark holiday.');
                            toggle.prop('checked', false);
                        });
                    } else {
                        toggle.prop('checked', false);
                    }
                });
            } else {
                $('.btn-check').prop('disabled', false);
                $('.btn-group label').removeClass('disabled-style');
            }
        });

    });
</script>
@endpush