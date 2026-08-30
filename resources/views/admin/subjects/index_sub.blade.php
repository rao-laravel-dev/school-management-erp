@extends('admin..layout.app')
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academic</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Subject Management</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Status Cards & Add Button Row -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-3 align-items-center">

    <!-- Total Subjects Card -->
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-primary shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">Total Subjects</p>
                        <h5 class="my-0 text-primary fw-bold">{{ $subjects->count() }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-book-open'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Subjects Card -->
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">Active</p>
                        <h5 class="my-0 text-success fw-bold" id="active-count">{{ $subjects->where('status', 1)->count() }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-check-shield'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inactive Subjects Card -->
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-danger shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">Inactive</p>
                        <h5 class="my-0 text-danger fw-bold" id="inactive-count">{{ $subjects->where('status', 0)->count() }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-x-circle'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Button -->
    <div class="col text-end ms-auto">
        <a href="{{ route('subjects.create') }}" class="btn btn-primary px-4 shadow-sm">
            <i class='bx bx-plus'></i> Add New Subject
        </a>
    </div>

</div>

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <h5 class="mb-0">Subjects List</h5>
                <span class="badge bg-primary">Records: {{ $subjects->count() }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Subject Name</th>
                                <th>Class</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subjects as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>

                                <!-- Subject Name: Model Attribute getter ki wajah se Ucwords hoga -->
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->name }}</div>
                                </td>

                                <!-- Subject Code: Model Attribute getter ki wajah se auto-uppercase hoga -->
                                <td><span class="badge bg-light-secondary text-secondary border">{{ $item->subject_code }}</span></td>

                                <!-- Class Column: Many-to-Many logic yahan aayega -->
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($item->school_class->isNotEmpty())
                                        @foreach($item->school_class as $class)
                                        <span class="badge bg-light-info text-info border border-info px-2">
                                            {{ $class->name }}
                                        </span>
                                        @endforeach
                                        @else
                                        <span class="text-muted small italic">Not Assigned</span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <button type="button"
                                        class="btn btn-sm toggle-status {{ $item->status == 1 ? 'btn-outline-success' : 'btn-outline-danger' }}"
                                        data-url="{{ route('subjects.status', $item->id) }}">
                                        {{ $item->status == 1 ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('subjects.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        @can('manage-academics')
                                        <a href="{{ route('subjects.delete', $item->id) }}" class="btn btn-sm btn-outline-danger" id="delete" title="Delete">
                                            <i class="bx bx-trash"></i>
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No subjects found.</td>
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

        // AJAX Status Toggle for Subjects
        $(document).on('click', '.toggle-status', function(e) {
            e.preventDefault();
            let btn = $(this);
            $.ajax({
                url: btn.data('url'),
                type: 'GET',
                success: function(res) {
                    let activeElem = $('#active-count');
                    let inactiveElem = $('#inactive-count');
                    let activeCount = parseInt(activeElem.text());
                    let inactiveCount = parseInt(inactiveElem.text());

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
                    toastr.error('Something went wrong!');
                }
            });
        });

        // SweetAlert Delete
        $(document).on('click', '#delete', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");
            Swal.fire({
                title: 'Are you sure?',
                text: "Deleting this subject might affect related marks/exams!",
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