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
                <li class="breadcrumb-item active" aria-current="page">Section Management</li>
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
                        <p class="mb-0 text-secondary small">Total Sections</p>
                        <h5 class="my-0 text-primary fw-bold">{{ $sections->count() }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-layer'></i>
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
                        <p class="mb-0 text-secondary small">Active</p>
                        <h5 class="my-0 text-success fw-bold" id="active-count">{{ $sections->where('status', 1)->count() }}</h5>
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
                        <p class="mb-0 text-secondary small">Inactive</p>
                        <h5 class="my-0 text-danger fw-bold" id="inactive-count">{{ $sections->where('status', 0)->count() }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-error'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col text-end ms-auto">
        <a href="{{ route('sections.create') }}" class="btn btn-primary px-4 shadow-sm">
            <i class='bx bx-plus'></i> Add New Section
        </a>
    </div>

</div>

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <h5 class="mb-0">Sections List</h5>
                <span class="badge bg-primary">Records: {{ $sections->count() }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Section</th>
                                <th>Capacity</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sections as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td><small class="text-muted fw-bold">{{ $item->section_code }}</small></td>
                                <td>
                                    <div class="fw-bold text-primary">{{ $item->name }}</div>
                                </td>
                                <td>{{ $item->capacity ?? 'N/A' }}</td>
                                <td>
                                    <button type="button"
                                        class="btn btn-sm toggle-status {{ $item->status == 1 ? 'btn-outline-success' : 'btn-outline-danger' }}"
                                        data-url="{{ route('sections.status', $item->id) }}">
                                        {{ $item->status == 1 ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('sections.edit', $item->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        @can('manage-academics')
                                        <a href="{{ route('sections.delete', $item->id) }}" class="btn btn-sm btn-outline-danger" id="delete">
                                            <i class="bx bx-trash"></i>
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
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
    // Script ke bilkul shuru mein ye add karein
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable();
        }

        // AJAX Status Toggle
        $(document).on('click', '.toggle-status', function(e) {
            e.preventDefault();
            let btn = $(this);
            $.ajax({
                url: btn.data('url'),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}' // Ye token security ke liye lazmi hai
                },
                success: function(res) {
                    let activeCount = parseInt($('#active-count').text());
                    let inactiveCount = parseInt($('#inactive-count').text());

                    if (res.status == 1) {
                        btn.removeClass('btn-danger').addClass('btn-success').text('Active');
                        $('#active-count').text(activeCount + 1);
                        $('#inactive-count').text(Math.max(0, inactiveCount - 1));
                        toastr.success(res.message);
                    } else {
                        btn.removeClass('btn-success').addClass('btn-danger').text('Inactive');
                        $('#active-count').text(Math.max(0, activeCount - 1));
                        $('#inactive-count').text(inactiveCount + 1);
                        toastr.error(res.message);
                    }
                }
            });
        });

        // SweetAlert Delete
        $(document).on('click', '#delete', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");
            Swal.fire({
                title: 'Are you sure?',
                text: "Delete this section?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link;
                }
            });
        });
    });
</script>
@endpush