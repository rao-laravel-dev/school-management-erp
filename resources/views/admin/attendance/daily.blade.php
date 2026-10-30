@extends($current_layout)

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Attendance Report</div>
    <div class="ps-3">
        <nav>
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('attendance.index', ['date' => $date]) }}">Daily Attendance Report</a></li>
                <li class="breadcrumb-item active">Daily Student Attendance</li>
            </ol>
        </nav>
    </div>
</div>

<div class="att-stats">
    @foreach([
        ['Total', $total, 'primary'],
        ['Present', $summary['P'], 'success'],
        ['Absent', $summary['A'], 'danger'],
        ['Late', $summary['L'], 'warning'],
        ['Half Day', $summary['H'], 'info'],
        ['Leave', $summary['F'], 'secondary'],
    ] as $card)
        @include('admin.attendance.partials.stat_card', ['label' => $card[0], 'value' => $card[1], 'color' => $card[2]])
    @endforeach
    @if($summary['HOL'] > 0)
        @include('admin.attendance.partials.stat_card', ['label' => 'Holiday', 'value' => $summary['HOL'], 'color' => 'dark'])
    @endif
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center att-card-header">
        <h5 class="mb-0">{{ $class->name }} - {{ $section->name }} Attendance — {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</h5>
        <div class="d-flex align-items-center gap-2">
            {{-- Class departure: sirf aaj ki date aur jab present/late students hon --}}
            @can('manage-attendance')
            @if($date === now()->toDateString() && ($summary['P'] + $summary['L']) > 0)
            <input type="time" id="departureTime" class="form-control form-control-sm w-auto" value="{{ now()->format('H:i') }}">
            <button type="button" id="markDepartureBtn" class="btn btn-sm btn-warning">
                <i class='bx bx-log-out'></i> Mark Departure
            </button>
            @endif
            @endcan
            @can('mark-attendance')
            <a href="{{ route('attendance.mark', [$class->id, $section->id, $date]) }}" class="btn btn-sm btn-primary">
                <i class='bx bx-edit'></i> Mark Attendance
            </a>
            @endcan
        </div>
    </div>

    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label" for="date">Date</label>
                <input type="date" name="date" id="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped table-hover" id="dailyAttendanceTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Roll No</th>
                        <th>Student</th>
                        <th>Status</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Remarks</th>
                        <th>Marked By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $i => $r)
                    @php
                        $student = optional($r->enrollment)->student;
                        $isHoliday = (int) $r->status === 4 && strtolower(trim((string) $r->remarks)) === 'holiday';
                        $badges = [1 => ['success', 'Present'], 0 => ['danger', 'Absent'], 2 => ['warning', 'Late'], 3 => ['info', 'Half Day'], 4 => ['secondary', 'Leave']];
                        $badge = $isHoliday ? ['dark', 'Holiday'] : (isset($badges[(int) $r->status]) ? $badges[(int) $r->status] : ['secondary', '-']);
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ optional($r->enrollment)->roll_no ?: '-' }}</td>
                        <td>
                            <a href="{{ route('attendance.student_report', $r->enrollment_id) }}" class="text-success fw-bold att-name-link">
                                {{ $student ? trim($student->first_name . ' ' . $student->last_name) : '-' }}
                            </a>
                        </td>
                        <td><span class="badge bg-{{ $badge[0] }}">{{ $badge[1] }}</span></td>
                        <td>{{ $r->scanned_at ? \Carbon\Carbon::parse($r->scanned_at)->format('h:i A') : '-' }}</td>
                        <td>{{ $r->time_out ? \Carbon\Carbon::parse($r->time_out)->format('h:i A') : '-' }}</td>
                        <td>{{ $r->remarks ?: '-' }}</td>
                        <td>{{ optional($r->markedBy)->name_with_role ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No attendance marked for this date yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#dailyAttendanceTable').DataTable();

        // Class departure: present/late students (jin ka time_out nahi) ko time_out lagta hai aur parents ko alert jata hai
        $('#markDepartureBtn').on('click', function() {
            let time = $('#departureTime').val();
            if (!time) {
                toastr.error('Please select a departure time first!');
                return;
            }
            Swal.fire({
                title: 'Mark class departure?',
                text: 'Present/late students without a time out will be set to ' + time + ' and their parents will be notified.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, mark departure'
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }
                $.post(@json(route('attendance.markDeparture')), {
                    class_id: @json($class->id),
                    section_id: @json($section->id),
                    attendance_date: @json($date),
                    departure_time: time
                }).done(function(res) {
                    toastr.success(res.success);
                    setTimeout(function() { location.reload(); }, 800);
                }).fail(function(xhr) {
                    let msg = 'Something went wrong!';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors)[0][0];
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    toastr.error(msg);
                });
            });
        });
    });
</script>
@endpush
