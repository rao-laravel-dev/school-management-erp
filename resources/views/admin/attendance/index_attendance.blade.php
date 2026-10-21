@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Attendance Report</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Daily Attendance Report</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Cards Section Wapas Add Kar Diya -->
<div class="att-stats">
    @include('admin.attendance.partials.stat_card', ['label' => 'Total Class Sections', 'value' => $totalClasses, 'color' => 'primary'])
    @include('admin.attendance.partials.stat_card', ['label' => "Today's Attendance Done", 'value' => $attendanceDone . ' / ' . $totalClasses, 'color' => 'success'])
</div>

<div class="card radius-10">
    <div class="card-header d-flex justify-content-between align-items-center bg-transparent att-card-header">
        <h5 class="mb-0">Full Attendance Report - <span id="report-header-date">{{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</span></h5>

        <form method="GET" action="{{ route('attendance.index') }}" style="width: 100%; max-width: 250px;">
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
                        <th>Stats (P/A/L/H/Lv/Hol)</th>
                        <th>Time In/Out</th>
                        <th>Marked By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
    @foreach($classes as $class)
        {{-- Yahan humne loop update kiya hai taake har mapped section ke liye row bane --}}
        @forelse($class->mappedSections as $section)
        @php
            $c = $sectionCounts->get($class->id . '-' . $section->id, ['P' => 0, 'A' => 0, 'L' => 0, 'H' => 0, 'Lv' => 0, 'HOL' => 0]);
            $marked = array_sum($c) > 0;
        @endphp
        <tr>
            <td>{{ $class->name }} - {{ $section->name }}</td>
            <td>
                {{-- Check: Kya is specific class/section ki attendance mark hui hai? --}}
                @if($marked)
                    <span class="badge bg-success">Marked</span>
                @else
                    <span class="badge bg-danger">Pending</span>
                @endif
            </td>
            <td>
                @if($marked)
                    <span class="text-success" title="Present">P:{{ $c['P'] }}</span> |
                    <span class="text-danger" title="Absent">A:{{ $c['A'] }}</span> |
                    <span class="text-warning" title="Late">L:{{ $c['L'] }}</span> |
                    <span class="text-info" title="Half Day">H:{{ $c['H'] }}</span> |
                    <span class="text-secondary" title="Leave">Lv:{{ $c['Lv'] }}</span> |
                    <span class="text-primary" title="Holiday">Hol:{{ $c['HOL'] }}</span>
                @else
                    <span class="text-muted">N/A</span>
                @endif
            </td>
            <td>
                @if($marked)
                    <small>In: {{ $class->first_time_in ?? 'N/A' }}<br>Out: {{ $class->last_time_out ?? 'N/A' }}</small>
                @else
                    <span class="text-muted">---</span>
                @endif
            </td>
            <td>
                @php $markedBy = isset($markers) ? $markers->get($class->id . '-' . $section->id) : null; @endphp
                @if($markedBy)
                    <small>{{ $markedBy }}</small>
                @else
                    <span class="text-muted">---</span>
                @endif
            </td>
            <td>
                {{-- Ab yahan $section->id sahi tarah se pass hoga --}}
                <a href="{{ route('attendance.class_report', [$class->id, $section->id]) . '?date=' . $date }}" 
   class="btn btn-sm btn-outline-primary">
    <i class="bx bx-show"></i> View Details
</a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center">No sections mapped for {{ $class->name }}</td>
        </tr>
        @endforelse
    @endforeach
</tbody>
            </table>
        </div>
    </div>
</div>
@endsection