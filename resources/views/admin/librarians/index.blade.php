@extends($current_layout)
@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Librarian Management', 'url' => route('librarians.index')],
    ['label' => 'All Librarians', 'url' => '#'],
]" />

{{-- Widgets Section --}}
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-3 align-items-center">
    {{-- Total Librarians --}}
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-primary shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">Total Librarians</p>
                        <h5 class="my-0 text-primary fw-bold" id="total-count">{{ $librarians->count() }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bx-book-reader'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Librarians --}}
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">Active</p>
                        <h5 class="my-0 text-success fw-bold" id="active-count">{{ $librarians->filter(fn($l) => optional($l->user)->status == 1)->count() }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-check-shield'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Inactive & Pending Librarians --}}
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-danger shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">Inactive/Pending</p>
                        <h5 class="my-0 text-danger fw-bold" id="inactive-count">
                            {{ $librarians->filter(fn($l) => in_array(optional($l->user)->status, [0, 2]))->count() }}
                        </h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-x-circle'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col text-end ms-auto">
        @can('manage-librarian')
        <a href="{{ route('librarians.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
            <i class='bx bx-plus'></i> Add New Librarian
        </a>
        @endcan
    </div>
</div>

{{-- Librarian Directory --}}
<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <h5 class="mb-0 text-primary">Librarians Directory</h5>

                <div class="d-flex gap-2 align-items-center">
                    {{-- Trash Bin Button --}}
                    @can('manage-librarian')
                    <a href="{{ route('librarians.trash') }}" class="btn btn-danger btn-sm">
                        <i class="bx bx-trash"></i> Trash Bin
                    </a>
                    @endcan

                    <span class="badge bg-primary m-0 align-self-center" id="directory-badge">
                        Records: {{ $librarians->count() }}
                    </span>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="librarianTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Librarian ID</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Status</th>
                                @can('manage-librarian')
                                <th>Action</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @include('admin.librarians.partials.table_rows', ['librarians' => $librarians])
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Details Modal (AJAX) --}}
<div class="modal fade" id="librarianDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Librarian Full Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="librarianModalBody">
                <p class="text-center">Loading...</p>
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

        @if(session('toastr-success'))
        toastr.success(@json(session('toastr-success')), 'Success!');
        @endif

        @if(session('toastr-error'))
        toastr.error(@json(session('toastr-error')), 'Error!');
        @endif

        // DataTable
        $('#librarianTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "language": { "emptyTable": "No librarians found." }
        });

        // Details Modal (AJAX)
        $(document).on('click', '.view-librarian-btn', function() {
            let librarianId = $(this).data('id');
            $('#librarianDetailsModal').modal('show');
            $('#librarianModalBody').html('<p class="text-center">Loading...</p>');

            $.ajax({
                url: "{{ route('librarians.get-details', ':id') }}".replace(':id', librarianId),
                type: 'GET',
                success: function(res) {
                    $('#librarianModalBody').html(res);
                },
                error: function() {
                    $('#librarianModalBody').html('<p class="text-danger text-center">Failed to load data.</p>');
                }
            });
        });

        // Status Toggle (AJAX), Pending par Approve/Reject
        $(document).on('click', '.toggle-librarian-status', function(e) {
            e.preventDefault();
            let btn = $(this);
            let librarianId = btn.data('id');

            if (btn.text().trim() === 'Pending') {
                Swal.fire({
                    title: 'Action Required',
                    text: "Do you want to Approve or Reject this librarian?",
                    icon: 'question',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonColor: '#28a745',
                    denyButtonColor: '#dc3545',
                    confirmButtonText: 'Approve',
                    denyButtonText: 'Reject'
                }).then((result) => {
                    if (result.isConfirmed) {
                        sendStatusRequest(btn, librarianId, 'approve');
                    } else if (result.isDenied) {
                        sendStatusRequest(btn, librarianId, 'reject');
                    }
                });
            } else {
                sendStatusRequest(btn, librarianId, 'toggle');
            }
        });

        function sendStatusRequest(btn, id, action) {
            btn.prop('disabled', true);
            $.ajax({
                url: "{{ route('librarians.status', ':id') }}".replace(':id', id),
                type: 'POST',
                data: { action: action },
                success: function(res) {
                    btn.removeClass('btn-outline-warning btn-outline-success btn-outline-danger');
                    if (res.status == 1) {
                        btn.addClass('btn-outline-success').text('Active');
                    } else if (res.status == 0) {
                        btn.addClass('btn-outline-danger').text('Inactive');
                    } else {
                        btn.addClass('btn-outline-warning').text('Pending');
                    }
                    $('#active-count').text(res.activeCount);
                    $('#inactive-count').text(res.inactiveCount);
                    $('#total-count').text(res.totalCount);
                    toastr.success(res.message);
                    btn.prop('disabled', false);
                },
                error: function() {
                    toastr.error("Update failed.");
                    btn.prop('disabled', false);
                }
            });
        }

        // Move to trash: DELETE form + SweetAlert
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            let form = $(this).closest('form');

            Swal.fire({
                title: 'Are you sure?',
                text: "Librarian record will be moved to the Trash bin!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, move to trash!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.trigger('submit');
                }
            });
        });

    });
</script>
@endpush
