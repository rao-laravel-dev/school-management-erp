@extends('student.layout.app')

@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academics</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active">My Subjects</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-uppercase">My Subject List</h6>
                <span class="badge bg-primary">Total: {{ $subjects->count() }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="subjectsTable" class="table table-bordered table-striped" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th>Subject Name</th>
                                <th>Code</th>
                                <th>Full Marks</th>
                                <th>Pass Marks</th>
                            </tr>
                        </thead>
                        <tbody>
    @forelse($subjects as $index => $subject)
    <tr>
        <td>{{ $index + 1 }}</td>
        
        <td>
            <span class="text-success fw-bold">{{ $subject->name }}</span>
        </td>
        
        <td>
            <span class="badge bg-light-warning text-danger border border-info px-2 py-1">
                {{ $subject->subject_code }}
            </span>
        </td>
        
        <td class="text-center">{{ $subject->school_class->first()->pivot->full_marks ?? '0' }}</td>
        <td class="text-center">{{ $subject->school_class->first()->pivot->pass_marks ?? '0' }}</td>
    </tr>
    @empty
    <tr>
        <td colspan="5" class="text-center">No subjects found for your class.</td>
    </tr>
    @endforelse
</tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#subjectsTable').DataTable({
            "destroy": true,
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "pageLength": 10
        });
    });
</script>
@endpush