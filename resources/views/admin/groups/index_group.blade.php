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
                <li class="breadcrumb-item active" aria-current="page">Global Group Management</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-3 align-items-center">
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-primary shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small fw-normal">Total Groups</p>
                        <h4 class="my-0 text-primary fw-bold">{{ $groups->count() }}</h4>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-group'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small fw-normal">Active Groups</p>
                        <h4 class="my-0 text-success fw-bold" id="active-count">{{ $groups->where('status', 1)->count() }}</h4>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-check-shield'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-danger shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small fw-normal">Inactive Groups</p>
                        <h4 class="my-0 text-danger fw-bold" id="inactive-count">{{ $groups->where('status', 0)->count() }}</h4>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-error-alt'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col text-end ms-auto">
        <a href="{{ route('groups.create') }}" class="btn btn-primary px-4 shadow-sm">
            <i class='bx bx-plus-circle'></i> Add Global Group
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0">Global Academic Groups List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="20%">Group Code</th>
                                <th width="25%">Group Name</th>
                                <th width="25%">Description</th>
                                <th width="10%">Status</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($groups as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark text-capitalize">{{ $item->name }}</div>
                                </td>
                                
                                <td>
                                    <span class="badge bg-light-info text-info border border-info px-3 fw-bold">
                                        {{ $item->group_code }}
                                    </span>
                                </td>
                                
                                <td>{{ Str::limit($item->description, 50) ?? 'No description added.' }}</td>
                                <td>
                                    <button type="button"
                                        class="btn btn-sm toggle-status {{ $item->status == 1 ? 'btn-outline-success' : 'btn-outline-danger' }}"
                                        data-url="{{ route('groups.status', $item->id) }}">
                                        {{ $item->status == 1 ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('groups.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        @can('manage-academics')
                                        <a href="{{ route('groups.delete', $item->id) }}" class="btn btn-sm btn-outline-danger" id="delete" title="Delete">
                                            <i class="bx bx-trash"></i>
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No Global Groups Configured.</td>
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
        // 1. DataTable Initialization
        if (!$.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable({
                "pageLength": 10,
                "ordering": true,
                "language": {
                    "search": "Search Groups:"
                }
            });
        }

        // 2. AJAX Status Toggle Logic for Global Groups
        $(document).on('click', '.toggle-status', function(e) {
            e.preventDefault();
            let btn = $(this);
            let url = btn.data('url');

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    let activeElem = $('#active-count');
                    let inactiveElem = $('#inactive-count');

                    let activeCount = parseInt(activeElem.text().trim()) || 0;
                    let inactiveCount = parseInt(inactiveElem.text().trim()) || 0;

                    if (response.status == 1) {
                        btn.removeClass('btn-danger').addClass('btn-success').text('Active');
                        activeElem.text(activeCount + 1);
                        inactiveElem.text(Math.max(0, inactiveCount - 1));
                        toastr.success(response.message);
                    } else {
                        btn.removeClass('btn-success').addClass('btn-danger').text('Inactive');
                        activeElem.text(Math.max(0, activeCount - 1));
                        inactiveElem.text(inactiveCount + 1);
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Server Error';
                    toastr.error('Error: ' + errorMsg);
                }
            });
        });

        // 3. SweetAlert2 for Group Delete Confirmation
        $(document).on('click', '#delete', function(e) {
            e.preventDefault();
            var link = $(this).attr("href");

            Swal.fire({
                title: 'Are you sure?',
                text: "Deleting this global group will completely break the academic mappings if already assigned to classes!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link;
                }
            });
        });
    });
</script>
@endpush