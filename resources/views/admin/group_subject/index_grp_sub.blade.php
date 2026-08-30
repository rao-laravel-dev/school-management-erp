@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academic</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('admindashboard') }}"><i class="bx bx-home-alt"></i></a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Group Subjects Assignment</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row g-3 mb-4 align-items-center">

    <div class="col-12 col-md-4 col-xl-3">
        <div class="card radius-10 border-start border-0 border-3 border-primary shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">Total Assignments</p>
                        <h5 class="my-0 text-primary fw-bold">{{ $groups->count() }}</h5>
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
                        <p class="mb-0 text-secondary small">Active Groups</p>
                        <h5 class="my-0 text-success fw-bold" id="active-count">{{ $groups->where('status', 1)->count() }}</h5>
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
                        <p class="mb-0 text-secondary small">Inactive Groups</p>
                        <h5 class="my-0 text-danger fw-bold" id="inactive-count">{{ $groups->where('status', 0)->count() }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-x-circle'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-3 text-xl-end text-start">
        <a href="{{ route('admingroup-subjects.assign') }}" class="btn btn-primary px-4 shadow-sm w-100-sm">
            <i class='bx bx-plus'></i> Assign Group Subjects
        </a>
    </div>

</div>

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <h5 class="mb-0">Group-Wise Subjects List</h5>
                <span class="badge bg-primary">Groups Configured: {{ $groups->count() }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">S.No</th>
                                <th width="15%">Group Name</th>
                                <th width="10%">Linked Class</th>
                                <th width="45%">Assigned Subjects</th>
                                <th width="10%">Status</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($groups as $key => $item)
    <tr>
        <td>{{ $key + 1 }}</td>

        <td>
            <div><span class="fw-bold text-dark">{{ $item->name }}</span></div>
            @if($item->group_code)
                <small class="text-muted d-block">Code: {{ $item->group_code }}</small>
            @endif
        </td>

        <td>
            <div class="d-flex flex-wrap gap-1">
                @forelse($item->school_class->unique('id') as $class)
                    <span class="badge bg-light-primary text-primary fw-semibold px-3 py-2 radius-30 text-uppercase">
                        {{ $class->name }}
                    </span>
                @empty
                    <span class="badge bg-light-danger text-danger fw-semibold px-3 py-2 radius-30">
                        <i class="bx bx-error-circle"></i> Not Assigned
                    </span>
                @endforelse
            </div>
        </td>

        <td>
            <div class="d-flex flex-wrap gap-1">
                @forelse($item->subjects as $subject)
                    <span class="badge bg-light-info text-info border border-info px-2 text-capitalize">
                        {{ $subject->name }}
                        <small class="text-secondary">({{ $subject->type }})</small>
                    </span>
                @empty
                    <span class="text-danger small fw-semibold">
                        <i class="bx bx-error-circle"></i> No Subjects Linked
                    </span>
                @endforelse
            </div>
        </td>

        <td>
            <a href="javascript:void(0);"
               class="btn btn-sm toggle-status {{ $item->status == 1 ? 'btn-success' : 'btn-danger' }}"
               data-url="{{ route('admingroup-subjects.status', $item->id) }}">
                {{ $item->status == 1 ? 'Active' : 'Inactive' }}
            </a>
        </td>

        <td>
            <div class="d-flex gap-2">
                <a href="{{ route('admingroup-subjects.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Assignment">
                    <i class="bx bx-edit"></i>
                </a>
                <a href="{{ route('admingroup-subjects.delete', $item->id) }}" class="btn btn-sm btn-outline-danger" id="delete" title="Remove Assignment">
                    <i class="bx bx-trash"></i>
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center text-muted py-4">No mappings or configuration found for groups.</td>
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

        // AJAX Status Toggle for Group Mapping
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
                        btn.removeClass('btn-danger').addClass('btn-success').text('Active');
                        activeElem.text(activeCount + 1);
                        inactiveElem.text(Math.max(0, inactiveCount - 1));
                        toastr.success(res.message);
                    } else {
                        btn.removeClass('btn-success').addClass('btn-danger').text('Inactive');
                        activeElem.text(Math.max(0, activeCount - 1));
                        inactiveElem.text(inactiveCount + 1);
                        toastr.error(res.message);
                    }
                },
                error: function() {
                    toastr.error('Something went wrong while updating status!');
                }
            });
        });

        // SweetAlert Delete Mapping Context Confirmation
        $(document).on('click', '#delete', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");

            Swal.fire({
                title: 'Are you sure?',
                text: "Removing this mapping will detach all assigned subjects from this group!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Yeh line execute hogi to browser direct controller method par hit karega
                    window.location.href = link;
                }
            });
        });
    });
</script>
@endpush