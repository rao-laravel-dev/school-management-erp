@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Attendance Report</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Daily Attendance Report</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Cards Section Wapas Add Kar Diya -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-3">
        <div class="card radius-10 border-start border-0 border-3 border-primary shadow-sm">
            <div class="card-body">
                <p class="text-secondary mb-0">Total Classes</p>
                <h4 class="text-primary">{{ $totalClasses }}</h4>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm">
            <div class="card-body">
                <p class="text-secondary mb-0">Today's Attendance Done</p>
                <h4 class="text-success">{{ $attendanceDone }} / {{ $totalClasses }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card radius-10">
    <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
        <h5>Full Attendance Report - <span id="report-header-date">{{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</span></h5>

        <form method="GET" action="{{ route('attendance.index') }}" style="width: 250px;">
            <div class="input-group">
                <span class="input-group-text bg-light">Date:</span>
                <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="attendanceTable">
                <thead class="table-light">
                    <tr>
                        <th>Class/Section</th>
                        <th>Status</th>
                        <th>Stats (P/A/L/H/Lv)</th>
                        <th>Time In/Out</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
    @foreach($classes as $class)
        {{-- Yahan humne loop update kiya hai taake har mapped section ke liye row bane --}}
        @forelse($class->mappedSections as $section)
        <tr>
            <td>{{ $class->name }} - {{ $section->name }}</td>
            <td>
                {{-- Check: Kya is specific class/section ki attendance mark hui hai? --}}
                @if($class->present_count + $class->absent_count + $class->late_count + $class->halfday_count + $class->leave_count > 0)
                    <span class="badge bg-success">Marked</span>
                @else
                    <span class="badge bg-danger">Pending</span>
                @endif
            </td>
            <td>
                @if($class->present_count + $class->absent_count + $class->late_count + $class->halfday_count + $class->leave_count > 0)
                    <span class="text-success" title="Present">P:{{ $class->present_count }}</span> |
                    <span class="text-danger" title="Absent">A:{{ $class->absent_count }}</span> |
                    <span class="text-warning" title="Late">L:{{ $class->late_count }}</span> |
                    <span class="text-info" title="Half Day">H:{{ $class->halfday_count }}</span> |
                    <span class="text-secondary" title="Leave">Lv:{{ $class->leave_count }}</span>
                @else
                    <span class="text-muted">N/A</span>
                @endif
            </td>
            <td>
                @if($class->present_count + $class->absent_count + $class->late_count + $class->halfday_count + $class->leave_count > 0)
                    <small>In: {{ $class->first_time_in ?? 'N/A' }}<br>Out: {{ $class->last_time_out ?? 'N/A' }}</small>
                @else
                    <span class="text-muted">---</span>
                @endif
            </td>
            <td>
                {{-- Ab yahan $section->id sahi tarah se pass hoga --}}
                <a href="{{ route('attendance.edit', $class->id) . '?section_id=' . $section->id . '&date=' . $date }}" 
   class="btn btn-sm btn-outline-primary">
    <i class="bx bx-edit"></i> View Details
</a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">No sections mapped for {{ $class->name }}</td>
        </tr>
        @endforelse
    @endforeach
</tbody>
            </table>
        </div>
    </div>
</div>
@endsection