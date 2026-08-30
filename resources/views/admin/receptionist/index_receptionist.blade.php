@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academic</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Receptionist Management</li>
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
                        <p class="mb-0 text-secondary small">Total Receptionists</p>
                        <h5 class="my-0 text-primary fw-bold" id="total-count">{{ $totalCount }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto"><i class='bx bxs-group'></i></div>
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
                        <h5 class="my-0 text-success fw-bold" id="active-count">{{ $activeCount }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto"><i class='bx bxs-check-shield'></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-danger shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        {{-- Yahan naam change kar dein taake clear rahe --}}
                        <p class="mb-0 text-secondary small">Inactive / Pending</p>
                        <h5 class="my-0 text-danger fw-bold" id="inactive-count">{{ $inactiveCount }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto"><i class='bx bxs-x-circle'></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col text-end ms-auto">
        <a href="{{ route('receptionist.create') }}" class="btn btn-primary px-4 shadow-sm">
            <i class='bx bx-plus'></i> Add Receptionist
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap bg-transparent">
                <h5 class="mb-0 text-primary">Receptionist Directory</h5>

                <div class="d-flex align-items-center gap-2">
                    {{-- Trash button pe permission check --}}
                    @can('manage-receptionist')
                    <a href="{{ route('receptionist.trash') }}" class="btn btn-warning btn-sm">
                        <i class="bx bx-trash"></i> View Trash
                    </a>
                    @endcan

                    <span class="badge bg-primary" id="directory-badge">
                        Records: {{ $receptionists->count() }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Profile</th>
                                <th>Full Name</th>
                                <th>Staff ID</th>
                                <th>Phone No</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="receptionist-table-body">
                            @include('admin.receptionist.partials.table_rows', ['receptionists' => $receptionists])
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- MODEL CONTENT HERE -->
<div class="modal fade" id="receptionistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Receptionist Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-content">
                <div class="text-center">Loading...</div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "6000"
        };

        @if(session('toastr-success')) toastr.success("{{ session('toastr-success') }}", "Success");
        @endif
        @if(session('toastr-error')) toastr.error("{{ session('toastr-error') }}", "Error");
        @endif

        $('#example').DataTable({
            "pageLength": 10,
            "ordering": true,
            "destroy": true // Ye line error solve kar degi
        });

        // --- STATUS TOGGLE (AJAX) ---
        $(document).on('click', '.toggle-status-btn', function(e) {
            e.preventDefault();
            let btn = $(this);
            let id = btn.data('id');
            btn.prop('disabled', true);

            $.ajax({
                url: "{{ route('receptionist.status', ':id') }}".replace(':id', id),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    // Update counts
                    $('#active-count').text(res.activeCount);
                    $('#inactive-count').text(res.inactiveCount);

                    // Button update logic
                    btn.removeClass('btn-outline-success btn-outline-danger btn-outline-warning');

                    if (res.status == 1) {
                        btn.addClass('btn-outline-success').text('Active');
                    } else if (res.status == 2) {
                        btn.addClass('btn-outline-warning').text('Pending');
                    } else {
                        btn.addClass('btn-outline-danger').text('Inactive');
                    }
                    toastr.success(res.message);
                },
                error: function(xhr) {
                    toastr.error("Something went wrong!");
                },
                complete: function() {
                    // Ye line hamesha chalegi success ya error ke baad
                    btn.prop('disabled', false);
                }
            });
        });

        // --- HARD DELETION ---
        $(document).on('click', '#delete', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");

            Swal.fire({
                title: 'Are you sure?',
                text: "Receptionist record will be moved to the Trash bin!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, move to trash!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link;
                }
            });
        });
    });

    // --- MODAL DETAILS LOAD ---
    function showReceptionistDetails(id) {
        $('#receptionistModal').modal('show');
        $('#modal-content').html('<div class="text-center p-3">Loading details...</div>');

        $.ajax({
            url: "{{ route('receptionist.get-details', ':id') }}".replace(':id', id),
            type: 'GET',
            success: function(response) {
                $('#modal-content').html(response);
            },
            error: function(xhr) {
                console.log(xhr.responseText); // Console mein error dekhein
                $('#modal-content').html('<p class="text-danger p-3">Failed to load details.</p>');
            }
        });
    }
</script>
@endpush