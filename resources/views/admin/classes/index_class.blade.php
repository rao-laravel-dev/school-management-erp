@extends($current_layout)

@section('content')

<!-- Breadcrumb -->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academic</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Class Management</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Status Cards (Classes Summary) -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-3 align-items-center">
    <!-- Total Classes Card -->
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-primary shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small fw-normal">Total Classes</p>
                        <h4 class="my-0 text-primary fw-bold">{{ $classes->count() }}</h4>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-graduation'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Classes Card -->
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small fw-normal">Active Classes</p>
                        <h4 class="my-0 text-success fw-bold" id="active-count">{{ $classes->where('status', 1)->count() }}</h4>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-check-shield'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inactive Classes Card -->
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-danger shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small fw-normal">Inactive Classes</p>
                        <h4 class="my-0 text-danger fw-bold" id="inactive-count">{{ $classes->where('status', 0)->count() }}</h4>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-error'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Class Button -->
    <div class="col text-end ms-auto">
        <a href="{{ route('classes.create') }}" class="btn btn-primary px-4 shadow-sm">
            <i class='bx bx-plus-circle'></i> Add Class
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <h5 class="mb-0">
                    School Classes List
                </h5>

                <span class="badge bg-primary">Records: {{ $classes->count() }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Class Name</th>
                                <th width="20%">Assigned Sections</th>
                                <th width="10%">Code</th>
                                <th width="10%">Numeric</th>
                                <th width="10%">Students</th>
                                <th width="15%">Status</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classes as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-primary">{{ $item->name }}</div>
                                </td>
                                <td>
                                    @if($item->mappedSections && $item->mappedSections->isNotEmpty())
                                    @foreach($item->mappedSections as $section)
                                    <span class="badge bg-success me-1 mb-1">{{ $section->name }}</span>
                                    @endforeach
                                    @else
                                    <span class="text-muted small">No sections</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-light-info text-info border border-info px-3">{{ $item->class_code }}</span></td>
                                <td>{{ $item->numeric_name }}</td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $item->enrollments_count }} </span>
                                </td>
                                <td>
                                    <button type="button"
                                        class="btn btn-sm toggle-status {{ $item->status == 1 ? 'btn-success' : 'btn-danger' }}"
                                        data-url="{{ route('classes.status', $item->id) }}">
                                        {{ $item->status == 1 ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('school_class_section.index', ['class_id' => $item->id]) }}"
                                            class="btn btn-sm btn-info text-white" title="Assign Sections">
                                            <i class="bx bx-list-check"></i>
                                        </a>
                                        @can('manage-academics')
                                        {{-- Edit Button --}}
                                        <a href="{{ route('classes.edit', $item->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </a>

                                        {{-- Delete Button --}}
                                        <a href="{{ route('classes.delete', $item->id) }}" class="btn btn-sm btn-danger delete-btn" id="delete" title="Delete">
                                            <i class="bx bx-trash"></i>
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No Classes Found</td>
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
        // DataTable Initialization
        if (!$.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable({
                "pageLength": 10,
                "ordering": true
            });
        }

        // AJAX Status Toggle logic
        $(document).on('click', '.toggle-status', function(e) {
            e.preventDefault();
            let btn = $(this);
            let url = btn.data('url');

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    let activeCount = parseInt($('#active-count').text());
                    let inactiveCount = parseInt($('#inactive-count').text());

                    if (response.status == 1) {
                        btn.removeClass('btn-danger').addClass('btn-success').text('Active');
                        $('#active-count').text(activeCount + 1);
                        $('#inactive-count').text(Math.max(0, inactiveCount - 1));
                        toastr.success(response.message);
                    } else {
                        btn.removeClass('btn-success').addClass('btn-danger').text('Inactive');
                        $('#active-count').text(Math.max(0, activeCount - 1));
                        $('#inactive-count').text(inactiveCount + 1);
                        // Using toastr.error for Inactive as per your requirement
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    toastr.error('Internal Server Error!');
                }
            });
        });

        // SweetAlert2 for Delete Confirmation
        $(document).on('click', '#delete', function(e) {
            e.preventDefault();
            var link = $(this).attr("href");

            Swal.fire({
                title: 'Are you sure?',
                text: "Removing this class might affect associated students and sections!",
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