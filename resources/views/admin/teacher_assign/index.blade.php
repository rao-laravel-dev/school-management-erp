@extends($current_layout)

@section('content')

{{-- Header --}}
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academic</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Class Teacher List</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="{{ route('teacher.assign.create') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus"></i> Assign New</a>
    </div>
</div>

<div class="card radius-10">
    <div class="card-body">
        <div class="table-responsive">
    {{-- 'table-hover' add kiya hai taake mouse le jane par highlight ho --}}
    <table id="assignmentTable" class="table table-bordered table-hover align-middle" style="width:100%">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Academic Year</th>
                <th>Class</th>
                <th>Section</th>
                <th>Teacher</th>
                <th>Assigned Date</th>
            </tr>
        </thead>
        <tbody>
            @php
                // 6 dynamic colors ka array
                $rowColors = ['table-danger', 'table-warning', 'table-info'];
            @endphp

            @foreach($assignments as $index => $row)
                @php
                    // Har loop par colors rotate honge (0 se 5 tak)
                    $colorClass = $rowColors[$index % 3];
                @endphp

                <tr class="{{ $colorClass }}">
                    <td>{{ $loop->iteration }}</td>
                    <td><span class="badge bg-light-secondary text-secondary">{{ $row->academicYear->name ?? 'N/A' }}</span></td>
                    <td>{{ $row->schoolClass->name ?? 'N/A' }}</td>

                    {{-- Section Badge --}}
                    <td>
                        @php
                            $sName = strtoupper($row->section->name ?? '');
                            $badgeColor = ($sName == 'A') ? 'success' :(($sName == 'B') ? 'info' :(($sName == 'C') ? 'warning' :(($sName == 'D') ? 'danger' : 'primary')));
                        @endphp
                        <span class="badge bg-light-{{$badgeColor}} text-{{$badgeColor}} border border-{{$badgeColor}} border-opacity-25 px-2 py-1">
                            {{ $row->section->name ?? 'N/A' }}
                        </span>
                    </td>

                    {{-- Teacher with Success Star --}}
                    <td>
                        <span class="text-dark fw-bold">
                            <i class="bx bxs-star me-1 text-success"></i>
                            {{ $row->teacher->first_name ?? '' }} {{ $row->teacher->last_name ?? '' }}
                        </span>
                    </td>

                    {{-- Assigned Date --}}
                    <td>{{ $row->created_at ? $row->created_at->format('d M, Y') : 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#assignmentTable').DataTable({
            "pageLength": 10,
            "ordering": true
        });
    });
</script>
@endpush