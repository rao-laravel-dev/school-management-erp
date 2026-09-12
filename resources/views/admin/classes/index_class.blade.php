@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Academic', 'url' => '#'],
    ['label' => 'Class Management', 'url' => route('classes.index')],
]" />

<!-- Status Cards -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-3 align-items-center">
    <div class="col">
        <div class="card border shadow-none radius-10 mb-0">
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

    <div class="col">
        <div class="card border shadow-none radius-10 mb-0">
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

    <div class="col">
        <div class="card border shadow-none radius-10 mb-0">
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

    <div class="col text-end ms-auto">
        <button type="button" class="btn btn-success btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addClassModal">
            <i class='bx bx-plus-circle'></i> Add Class
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xl">
        <div class="card border shadow-none radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <h5 class="mb-0 fw-bold text-primary">School Classes List</h5>
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
                                <td><div class="fw-bold text-primary">{{ $item->name }}</div></td>
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
                                <td><span class="badge bg-info text-dark">{{ $item->enrollments_count }}</span></td>
                                <td>
                                    <button type="button"
                                        class="btn btn-sm toggle-status {{ $item->status == 1 ? 'btn-outline-success' : 'btn-outline-danger' }}"
                                        data-url="{{ route('classes.status', $item->id) }}">
                                        {{ $item->status == 1 ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('school_class_section.index', ['class_id' => $item->id]) }}"
                                            class="btn btn-sm btn-outline-info" title="Assign Sections">
                                            <i class="bx bx-list-check"></i>
                                        </a>
                                        @can('manage-academics')
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-btn"
                                            data-url="{{ route('classes.edit', $item->id) }}" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <a href="{{ route('classes.delete', $item->id) }}" class="btn btn-sm btn-outline-danger delete-btn" title="Delete">
                                            <i class="bx bx-trash"></i>
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-muted">No Classes Found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.classes.modals')

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable({ "pageLength": 10, "ordering": true });
        }

        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000"
        };

        @if(session('message'))
            toastr.{{ session('alert-type', 'success') }}("{{ session('message') }}");
        @endif

        // --- ADD CLASS ---
        $('#addClassForm').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(res) {
                    toastr.success(res.message);
                    $('#addClassModal').modal('hide');
                    setTimeout(() => location.reload(), 800);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let msg = "";
                        $.each(errors, function(key, val) { msg += "• " + val[0] + "<br>"; });
                        toastr.error(msg, 'Validation Error!');
                    } else {
                        toastr.error('Something went wrong!');
                    }
                }
            });
        });

        // --- EDIT: load data into modal ---
        $(document).on('click', '.edit-btn', function() {
    let url = $(this).data('url');

    $.ajax({
        url: url,
        type: 'GET',
        success: function(data) {
            $('#edit_id').val(data.id);
            $('#edit_name').val(data.name);
            $('#edit_numeric_name').val(data.numeric_name);
            $('#edit_has_subjects').prop('checked', data.has_subjects == 1);
            $('#edit_description').val(data.description);
            $('#edit_status').prop('checked', data.status == 1);

            // FIX: route pattern ke mutabiq sahi URL banayein
            $('#editClassForm').attr('action', "{{ url('classes/update') }}/" + data.id);
            $('#editClassModal').modal('show');
        },
        error: function() {
            toastr.error('Unable to load class data.');
        }
    });
});

        // --- UPDATE CLASS ---
        $('#editClassForm').on('submit', function(e) {
    e.preventDefault();
    let form = $(this);

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),   // <-- '&_method=PUT' hata diya
        success: function(res) {
            toastr.success(res.message);
            $('#editClassModal').modal('hide');
            setTimeout(() => location.reload(), 800);
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                let msg = "";
                $.each(errors, function(key, val) { msg += "• " + val[0] + "<br>"; });
                toastr.error(msg, 'Validation Error!');
            } else {
                toastr.error('Something went wrong!');
            }
        }
    });
});

        // --- STATUS TOGGLE ---
        $(document).on('click', '.toggle-status', function(e) {
            e.preventDefault();
            let btn = $(this);

            $.ajax({
                url: btn.data('url'),
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
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Internal Server Error!');
                }
            });
        });

        // --- DELETE ---
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");

            Swal.fire({
                title: 'Are you sure?',
                text: "Removing this class might affect associated students and sections!",
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
</script>
@endpush