@extends($current_layout)

@section('content')
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('dashboard') }}"><i class='bx bx-home-alt'></i></a>
    <i class='bx bx-chevron-right mx-1'></i>
    <span>Attendance</span>
    <i class='bx bx-chevron-right mx-1'></i>
    <span class="fw-semibold">Class Report</span>
</div>

@if($noClass)
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            No class assigned to you for the current session.
        </div>
    </div>
@elseif(!$class)
    {{-- Teacher ki ek se zyada class-sections: pehle select karein --}}
    <div class="card">
        <div class="card-body">
            <label class="form-label" for="classSectionSelect">Select Class - Section</label>
            <select id="classSectionSelect" class="form-select form-select-sm" style="max-width: 320px;">
                <option value="">-- Select --</option>
                @foreach($scope as $a)
                    <option value="{{ route('attendance.class_report', [$a->class_id, $a->section_id]) }}">
                        {{ optional($a->schoolClass)->name }} - {{ optional($a->section)->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
@else
    <div class="card mb-3">
        <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0">{{ $class->name }} - {{ $section->name }} Attendance</h5>
            @if($scope !== null && $scope->count() > 1)
                <select id="classSectionSelect" class="form-select form-select-sm" style="max-width: 260px;">
                    @foreach($scope as $a)
                        <option value="{{ route('attendance.class_report', [$a->class_id, $a->section_id]) }}"
                            {{ $a->class_id == $class->id && $a->section_id == $section->id ? 'selected' : '' }}>
                            {{ optional($a->schoolClass)->name }} - {{ optional($a->section)->name }}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('attendance.class_report', [$class->id, $section->id]) }}" class="row g-3">
                <div class="col-6 col-md-2">
                    <label class="form-label" for="from_date">From</label>
                    <input type="date" name="from_date" id="from_date" class="form-control form-control-sm" value="{{ $filters['from'] }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label" for="to_date">To</label>
                    <input type="date" name="to_date" id="to_date" class="form-control form-control-sm" value="{{ $filters['to'] }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label" for="status">Status</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'half_day' => 'Half Day', 'leave' => 'Leave'] as $key => $label)
                            <option value="{{ $key }}" {{ $filters['status'] === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label" for="q">Student</label>
                    <input type="text" name="q" id="q" class="form-control form-control-sm" maxlength="50" placeholder="Name, admission no or roll no" value="{{ $filters['q'] }}">
                </div>
                <div class="col-12 col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary me-2">Filter</button>
                    <a href="{{ route('attendance.class_report', [$class->id, $section->id]) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary cards (grouped count, poori filtered range; page ke rows se nahi) --}}
    <div class="row g-3 mb-3">
        @foreach([
            ['Students', $studentCount, 'primary'],
            ['Present', $summary['P'], 'success'],
            ['Absent', $summary['A'], 'danger'],
            ['Late', $summary['L'], 'warning'],
            ['Half Day', $summary['H'], 'info'],
            ['Leave', $summary['F'], 'secondary'],
        ] as $card)
            <div class="col-6 col-md-2">
                <div class="card radius-10 border-start border-3 border-0 border-{{ $card[2] }} mb-0">
                    <div class="card-body py-2">
                        <small class="text-muted">{{ $card[0] }}</small>
                        <h4 class="mb-0 text-{{ $card[2] }}">{{ $card[1] }}</h4>
                    </div>
                </div>
            </div>
        @endforeach
        @if($summary['HOL'] > 0)
            <div class="col-6 col-md-2">
                <div class="card radius-10 border-start border-3 border-0 border-dark mb-0">
                    <div class="card-body py-2">
                        <small class="text-muted">Holiday</small>
                        <h4 class="mb-0">{{ $summary['HOL'] }}</h4>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-hover" id="classReportTable">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
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
                        @forelse($rows as $r)
                            @php
                                $student = optional($r->enrollment)->student;
                                $isHoliday = (int) $r->status === 4 && strtolower(trim((string) $r->remarks)) === 'holiday';
                                $badges = [1 => ['success', 'Present'], 0 => ['danger', 'Absent'], 2 => ['warning', 'Late'], 3 => ['info', 'Half Day'], 4 => ['secondary', 'Leave']];
                                $badge = $isHoliday ? ['dark', 'Holiday'] : (isset($badges[(int) $r->status]) ? $badges[(int) $r->status] : ['secondary', '-']);
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($r->attendance_date)->format('d-m-Y') }}</td>
                                <td>{{ optional($r->enrollment)->roll_no ?: '-' }}</td>
                                <td>
                                    <a href="{{ route('attendance.student_report', $r->enrollment_id) }}">
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
                                <td colspan="8" class="text-center text-muted">No attendance records found for this range.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $rows->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    $('#classSectionSelect').on('change', function () {
        if ($(this).val()) {
            window.location.href = $(this).val();
        }
    });
</script>
@endpush
