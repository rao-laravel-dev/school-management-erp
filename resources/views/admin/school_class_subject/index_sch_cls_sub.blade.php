@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academic</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Class Subjects Assignment</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Status Cards & Add Button Row -->
<div class="row g-3 mb-4 align-items-center">
    <div class="col-12 col-md-4 col-xl-3">
        <div class="card radius-10 border-start border-0 border-3 border-primary shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">Total Configurations</p>
                        <h5 class="my-0 text-primary fw-bold">{{ $classes->count() }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-book-open'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4 col-xl-3">
        <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">Active Status</p>
                        <h5 class="my-0 text-success fw-bold" id="active-count">{{ $classes->where('status', 1)->count() }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-check-shield'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4 col-xl-3">
        <div class="card radius-10 border-start border-0 border-3 border-danger shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">Inactive Status</p>
                        <h5 class="my-0 text-danger fw-bold" id="inactive-count">{{ $classes->where('status', 0)->count() }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-x-circle'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-3 text-xl-end text-start">
        <a href="{{ route('school-class-subjects.assign') }}" class="btn btn-primary px-4 shadow-sm w-100-sm">
            <i class='bx bx-plus'></i> Assign Subjects
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <h5 class="mb-0">Class-Wise Subjects List</h5>
                <span class="badge bg-primary">Configured: {{ $classes->count() }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>S.No</th>
                                <th>Class Name</th>
                                <th>Assigned Subjects</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classes as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->name }}</div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($item->subjects->isNotEmpty())
                                            @foreach($item->subjects as $subject)
                                            <span class="badge bg-light-info text-info border border-info px-2 py-1">
                                                {{ $subject->name }} 
                                                <small class="text-secondary">({{ $subject->type }})</small>
                                            </span>
                                            @endforeach
                                        @else
                                            <span class="text-muted small fst-italic">No Subjects Linked</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <a href="javascript:void(0);"
                                        class="btn btn-sm toggle-status {{ $item->status == 1 ? 'btn-outline-success' : 'btn-outline-danger' }}"
                                        data-url="{{ route('school-class-subjects.status', $item->id) }}">
                                        {{ $item->status == 1 ? 'Active' : 'Inactive' }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('school-class-subjects.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Assignment">
                                            <i class="bx bx-edit"></i>
                                        </a>

                                        @can('manage-academics')
                                        <a href="{{ route('school-class-subjects.delete', $item->id) }}" class="btn btn-sm btn-outline-danger" id="delete" title="Remove Assignment">
                                            <i class="bx bx-trash"></i>
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No mappings or configuration found.</td>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable({
                "pageLength": 10,
                "ordering": true
            });
        }

        // AJAX Status Toggle
        $(document).on('click', '.toggle-status', function(e) {
            e.preventDefault();
            let btn = $(this);
            
            $.ajax({
                url: btn.data('url'),
                type: 'GET',
                success: function(res) {
                    let activeElem = $('#active-count');
                    let inactiveElem = $('#inactive-count');
                    
                    let activeCount = parseInt(activeElem.text()) || 0;
                    let inactiveCount = parseInt(inactiveElem.text()) || 0;

                    if (res.status == 1) {
                        btn.removeClass('btn-outline-danger btn-danger').addClass('btn-outline-success').text('Active');
                        activeElem.text(activeCount + 1);
                        inactiveElem.text(Math.max(0, inactiveCount - 1));
                        toastr.success(res.message);
                    } else {
                        btn.removeClass('btn-outline-success btn-success').addClass('btn-outline-danger').text('Inactive');
                        activeElem.text(Math.max(0, activeCount - 1));
                        inactiveElem.text(inactiveCount + 1);
                        toastr.error(res.message);
                    }
                },
                error: function() {
                    toastr.error('Something went wrong!');
                }
            });
        });

        // SweetAlert Delete Confirmation
        $(document).on('click', '#delete', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");
            Swal.fire({
                title: 'Are you sure?',
                text: "Removing this mapping will detach all assigned subjects from this class!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link;
                }
            });
        });
    });
</script>
@endpush