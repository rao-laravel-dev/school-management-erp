@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Academics', 'url' => '#'],
    ['label' => 'Manage Class-Section', 'url' => route('school_class_section.index')],
]" />

<div class="row">
    <div class="col-xl">
        <div class="card border shadow-none radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <h5 class="mb-0 fw-bold text-primary">Class-Section Mapping List</h5>
                <div class="d-flex gap-2">
                    <span class="badge bg-primary align-self-center">Records: {{ $mapped_classes->count() }}</span>
                    <a href="{{ route('classes.index') }}" class="btn btn-dark btn-sm">
                        <i class="bx bx-arrow-back"></i> Back to Classes
                    </a>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addMappingModal">
                        <i class="bx bx-plus-circle"></i> Assign Sections
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th>Class Name</th>
                                <th>Section</th>
                                <th>Status</th>
                                <th width="12%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mapped_classes as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-bold text-primary">{{ $row->name }}</td>
                                <td>
                                    @if($row->mappedSections && $row->mappedSections->count() > 0)
                                        @foreach($row->mappedSections as $s)
                                        @php
                                            $secBadge = match(strtoupper($s->name)) {
                                                'A' => 'bg-success',
                                                'B' => 'bg-warning text-dark',
                                                'C' => 'bg-danger',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $secBadge }} me-1 mb-1">{{ $s->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted small">No sections</span>
                                    @endif
                                </td>
                                <td>
                                    @if($row->mappedSections && $row->mappedSections->count() > 0)
                                        @foreach($row->mappedSections as $s)
                                        <button type="button" onclick="toggleStatus({{ $s->pivot->id }})"
                                            class="btn btn-sm {{ $s->pivot->status == 1 ? 'btn-outline-success' : 'btn-outline-danger' }} mb-1">
                                            {{ $s->name }}: {{ $s->pivot->status == 1 ? 'Active' : 'Inactive' }}
                                        </button>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#editModal{{ $row->id }}" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        @can('manage-academics')
                                        <a href="{{ route('school_class_section.destroy', $row->id) }}" class="btn btn-sm btn-outline-danger delete-btn" title="Delete">
                                            <i class="bx bx-trash"></i>
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted">No records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.school_class_section.modals')

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#example').DataTable({
            "destroy": true,
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "pageLength": 10
        });

        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000"
        };

        @if(session('message'))
            toastr.{{ session('alert-type', 'success') }}("{{ session('message') }}");
        @endif

        @if($errors->any())
            let errorHtml = "";
            @foreach($errors->all() as $error)
                errorHtml += "• {{ $error }}<br>";
            @endforeach
            toastr.error(errorHtml, 'Validation Error!');
        @endif

        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");

            Swal.fire({
                title: 'Are you sure?',
                text: "This mapping will be removed permanently!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = link;
            });
        });
    });

    window.toggleStatus = function(id) {
        $.ajax({
            url: "{{ route('school_class_section.toggle_status', ['id' => ':id']) }}".replace(':id', id),
            type: "GET",
            success: function(response) {
                toastr.success(response.message);
                setTimeout(function() { location.reload(); }, 1000);
            },
            error: function() {
                toastr.error("Error updating status!");
            }
        });
    }
</script>
@endpush