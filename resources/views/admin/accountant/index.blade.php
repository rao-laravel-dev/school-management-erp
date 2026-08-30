@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Finance</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Accountant Management</li>
            </ol>
        </nav>
    </div>
</div>

{{-- Widgets Section --}}
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-3 align-items-center">
    {{-- Total Accountants --}}
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-primary shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">Total Accountants</p>
                        <h5 class="my-0 text-primary fw-bold" id="total-count">{{ $accountants->count() }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-calculator'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Accountants --}}
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">Active</p>
                        <h5 class="my-0 text-success fw-bold" id="active-count">{{ $accountants->filter(fn($a) => optional($a->user)->status == 1)->count() }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-check-shield'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Inactive & Pending Accountants --}}
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-danger shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">Inactive/Pending</p>
                        <h5 class="my-0 text-danger fw-bold" id="inactive-count">
                            {{ $accountants->filter(fn($a) => in_array(optional($a->user)->status, [0, 2]))->count() }}
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
        <a href="{{ route('accountant.create') }}" class="btn btn-primary px-4 shadow-sm">
            <i class='bx bx-plus'></i> Add New Accountant
        </a>
    </div>
</div>

{{-- Accountant Directory --}}
<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <h5 class="mb-0 text-primary">Accountant Directory</h5>

                <div class="d-flex gap-2 align-items-center">
                    @can('manage-accountant')
                    <a href="{{ route('accountant.trash') }}" class="btn btn-danger btn-sm">
                        <i class="bx bx-trash"></i> Trash Bin
                    </a>
                    @endcan

                    <span class="badge bg-primary m-0 align-self-center" id="directory-badge">
                        Records: {{ $accountants->count() }}
                    </span>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="accountantTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Accountant ID</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="accountant-table-body">
                            @include('admin.accountant.partials.table_rows', ['accountants' => $accountants])
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

        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "6000"
        };

        @if(session('toastr-success')) toastr.success("{{ session('toastr-success') }}", "Success"); @endif
        @if(session('toastr-error')) toastr.error("{{ session('toastr-error') }}", "Error"); @endif

        $('#accountantTable').DataTable({
            "pageLength": 10,
            "ordering": true
        });

        // --- ACCOUNTANT DETAILS MODAL (AJAX) ---
        $(document).on('click', '.view-accountant-btn', function() {
            let accountantId = $(this).data('id');
            $('#accountantDetailsModal').modal('show');
            $('#accountantModalBody').html('<p class="text-center">Loading...</p>');

            $.ajax({
                url: "{{ route('accountant.get-details', ':id') }}".replace(':id', accountantId),
                type: 'GET',
                success: function(res) {
                    $('#accountantModalBody').html(res);
                },
                error: function() {
                    $('#accountantModalBody').html('<p class="text-danger text-center">Failed to load data.</p>');
                }
            });
        });

        // --- TOGGLE ACCOUNTANT STATUS (AJAX) ---
        $(document).on('click', '.toggle-accountant-status', function(e) {
            e.preventDefault();
            let btn = $(this);
            let accountantId = btn.data('id');

            if (btn.text().trim() === 'Pending') {
                Swal.fire({
                    title: 'Action Required',
                    text: "Do you want to Approve or Reject this accountant?",
                    icon: 'question',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonColor: '#28a745',
                    denyButtonColor: '#dc3545',
                    confirmButtonText: 'Approve',
                    denyButtonText: 'Reject'
                }).then((result) => {
                    if (result.isConfirmed) {
                        sendAjaxRequest(btn, accountantId, 'approve');
                    } else if (result.isDenied) {
                        sendAjaxRequest(btn, accountantId, 'reject');
                    }
                });
            } else {
                sendAjaxRequest(btn, accountantId, 'toggle');
            }
        });

        function sendAjaxRequest(btn, id, action) {
            btn.prop('disabled', true);
            $.ajax({
                url: "{{ route('accountant.status', ':id') }}".replace(':id', id),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    action: action
                },
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

        // --- HARD DELETION CONFIRMATION ---
        $(document).on('click', '#delete-btn', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");

            Swal.fire({
                title: 'Are you sure?',
                text: "Accountant record will be moved to the Trash bin!",
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
</script>
@endpush