@extends($current_layout)

@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academic</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Student Management</a></li>
                <li class="breadcrumb-item active" aria-current="page">Trash Bin</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <h5 class="mb-0 text-danger"><i class="bx bx-trash"></i> Trashed Students</h5>
                <a href="{{ route('students.index') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-arrow-back"></i> Back to List
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Profile</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Deleted At</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trashedStudents as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td class="text-center align-middle">
                                    {{-- Make sure path matches your student uploads folder --}}
                                    <img src="{{ $item->photo_url }}" class="rounded shadow-sm p-1 border" width="45" height="45" alt="Photo">
                                </td>
                                <td>
                                    <div class="text-primary fw-bold">{{ ucfirst($item->first_name) }} {{ ucfirst($item->last_name) }}</div>
                                </td>
                                <td>{{ $item->email }}</td>
                                <td>
                                    <span class="text-muted small">
                                        {{ $item->deleted_at ? $item->deleted_at->format('d M, Y') : 'N/A' }}
                                        <br>
                                        <small>{{ $item->deleted_at ? $item->deleted_at->format('h:i A') : '' }}</small>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('students.restore', $item->id) }}" class="btn btn-sm btn-outline-success restore-btn" title="Restore">
                                            <i class="bx bx-refresh my-0"></i>
                                        </a>
                                        {{-- Class updated to 'force-delete-btn' to match JS --}}
                                        <a href="{{ route('students.force-delete', $item->id) }}" class="btn btn-sm btn-outline-danger force-delete-btn" title="Permanent Delete">
                                            <i class="bx bx-trash my-0"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No trashed students found.</td>
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

        // --- TOASTR CONFIGURATION ---
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "6000",
            "showDuration": "300",
            "hideDuration": "1000"
        };

        @if(session('toastr-success'))
        toastr.success("{{ session('toastr-success') }}", "Success");
        @endif

        @if(session('toastr-error'))
        toastr.error("{{ session('toastr-error') }}", "Error");
        @endif

        // --- FORCE DELETE CONFIRMATION (already present, just moved inside ready) ---
        $(document).on('click', '.force-delete-btn', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");

            Swal.fire({
                title: 'Permanent Delete?',
                text: "This record will be deleted permanently and cannot be recovered!",
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link;
                }
            });
        });

        // --- RESTORE CONFIRMATION (naya — halka confirm, sweetalert nahi, sirf safety) ---
        $(document).on('click', '.restore-btn', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");

            Swal.fire({
                title: 'Restore Student?',
                text: "Is student ka record wapas active list mein aa jayega.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, restore it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link;
                }
            });
        });
    });
</script>
@endpush