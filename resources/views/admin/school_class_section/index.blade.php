@extends($current_layout)

@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academics</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active">Manage Class-Section</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="{{ route('classes.index') }}" class="btn btn-secondary btn-sm">
            <i class="bx bx-arrow-back"></i> Back to Classes
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card" style="border-top: 5px solid #0d6efd; border-radius: 10px;">
            <div class="card-header bg-transparent">
                <h5 class="mb-0"></i> Assign New Sections</h5>
                <p class="mb-0 small text-muted">Create a new section for existing classes</p>
            </div>
            <div class="card-body">
                <form action="{{ route('school_class_section.store') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="form-label">Select Class</label>
                        <select name="school_class_id" class="form-control" required>
                            <option value="">-- Choose Class --</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ (isset($selectedClassId) && $selectedClassId == $class->id) ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Select Sections</label>
                        <div class="border p-2" style="height: 200px; overflow-y: scroll; background: #fff;">
                            @foreach($sections as $sec)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="section_ids[]" value="{{ $sec->id }}" id="sec{{ $sec->id }}">
                                <label class="form-check-label" for="sec{{ $sec->id }}">{{ $sec->name }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-save"></i> Save Mapping</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"> Mapping List</h6>
                <span class="badge bg-primary">Records: {{ $mapped_classes->count() }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">#</th> {{-- Naya Index Column --}}
                                <th>Class Name</th>
                                <th>Section</th>
                                <th>Status</th>
                                <th width="12%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mapped_classes as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td> {{-- Loop ka number --}}
                                <td class="fw-bold">{{ $row->name }}</td>
                                <td>
                                    @if($row->mappedSections && $row->mappedSections->count() > 0)
                                    @foreach($row->mappedSections as $s)
                                    <span class="badge bg-success me-1">{{ $s->name }}</span>
                                    @endforeach
                                    @else
                                    <span class="text-muted">No sections</span>
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
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $row->id }}">
                                            <i class="bx bx-edit"></i>
                                        </button>

                                        @can('manage-academics')
                                        <form action="{{ route('school_class_section.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            {{-- Modal code yahan rahega... --}}
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No records found.</td> {{-- colspan 4 se 5 kar diya --}}
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
        // Initialize DataTable to handle Search, Entries, Info, and Pagination
        $('#example').DataTable({
            "destroy": true, // Yeh error ko fix karega
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "pageLength": 10
        });

    });

    // AJAX Status Toggle Function
    window.toggleStatus = function(id) {
        $.ajax({
            url: "{{ route('school_class_section.toggle_status', ['id' => ':id']) }}".replace(':id', id),
            type: "GET",
            success: function(response) {
                // Toastr show karein
                toastr.success(response.message);

                // 1 second baad reload karein taake user message dekh sake
                setTimeout(function() {
                    location.reload();
                }, 1000);
            },
            error: function(xhr) {
                toastr.error("Error updating status!");
            }
        });
    }
</script>
@endpush