@extends($current_layout)
@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Teacher Management', 'url' => route('teacher.index')],
    ['label' => 'All Teachers', 'url' => '#'],
]" />

{{-- Widgets Section --}}
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-3 align-items-center">
    {{-- Total Teachers --}}
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-primary shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">Total Teachers</p>
                        <h5 class="my-0 text-primary fw-bold" id="total-count">{{ $teachers->count() }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-user-badge'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Teachers --}}
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">Active</p>
                        <h5 class="my-0 text-success fw-bold" id="active-count">{{ $teachers->filter(fn($t) => optional($t->user)->status == 1)->count() }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-check-shield'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Inactive & Pending Teachers --}}
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-danger shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">Inactive/Pending</p>
                        <h5 class="my-0 text-danger fw-bold" id="inactive-count">
                            {{ $teachers->filter(fn($t) => in_array(optional($t->user)->status, [0, 2]))->count() }}
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
        <a href="{{ route('teacher.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
            <i class='bx bx-plus'></i> Add New Teacher
        </a>
    </div>
</div>

{{-- Teacher Directory --}}
<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            {{-- Updated Header with Buttons --}}
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                {{-- Title --}}
                <h5 class="mb-0 text-primary">Teachers Directory</h5>

                {{-- Right Side Actions --}}
                <div class="d-flex gap-2 align-items-center">
                    {{-- Trash Bin Button --}}
                    @can('manage-teacher')
                    <a href="{{ route('teacher.trash') }}" class="btn btn-danger btn-sm">
                        <i class="bx bx-trash"></i> Trash Bin
                    </a>
                    @endcan

                    {{-- Record Badge --}}
                    <span class="badge bg-primary m-0 align-self-center" id="directory-badge">
                        Records: {{ $teachers->count() }}
                    </span>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="teacherTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Teacher ID</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="teacher-table-body">
                            @include('admin.teacher.partials.table_rows', ['teachers' => $teachers])
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

        // --- 0. TOASTR CONFIGURATION ---
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "6000"
        };

        @if(session('success'))
toastr.success(@json(session('success')), 'Success!');
@endif

@if(session('error'))
toastr.error(@json(session('error')), 'Error!');
@endif

@if(session('toastr-success'))
toastr.success(@json(session('toastr-success')), 'Success!');
@endif

@if(session('toastr-error'))
toastr.error(@json(session('toastr-error')), 'Error!');
@endif

        // --- 1. DATATABLE INITIALIZATION ---
        $('#teacherTable').DataTable({
            "pageLength": 10,
            "ordering": true
        });

        // --- NEW: TEACHER DETAILS MODAL (AJAX) ---
        $(document).on('click', '.view-teacher-btn', function() {
            let teacherId = $(this).data('id');
            $('#teacherDetailsModal').modal('show');
            $('#teacherModalBody').html('<p class="text-center">Loading...</p>');

            $.ajax({
                url: "{{ route('teacher.get-details', ':id') }}".replace(':id', teacherId),
                type: 'GET',
                success: function(res) {
                    $('#teacherModalBody').html(res);
                },
                error: function() {
                    $('#teacherModalBody').html('<p class="text-danger text-center">Failed to load data.</p>');
                }
            });
        });

        // --- TOGGLE TEACHER STATUS (AJAX) ---
        $(document).on('click', '.toggle-teacher-status', function(e) {
            e.preventDefault();
            let btn = $(this);
            let teacherId = btn.data('id');

            if (btn.text().trim() === 'Pending') {
                Swal.fire({
                    title: 'Action Required',
                    text: "Do you want to Approve or Reject this teacher?",
                    icon: 'question',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonColor: '#28a745',
                    denyButtonColor: '#dc3545',
                    confirmButtonText: 'Approve',
                    denyButtonText: 'Reject'
                }).then((result) => {
                    if (result.isConfirmed) {
                        sendAjaxRequest(btn, teacherId, 'approve');
                    } else if (result.isDenied) {
                        sendAjaxRequest(btn, teacherId, 'reject');
                    }
                });
            } else {
                sendAjaxRequest(btn, teacherId, 'toggle');
            }
        });

        function sendAjaxRequest(btn, id, action) {
            btn.prop('disabled', true);
            $.ajax({
                url: "{{ route('teacher.status', ':id') }}".replace(':id', id),
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

        // --- 3. HARD DELETION CONFIRMATION ---
        $(document).on('click', '#delete-btn', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");

            Swal.fire({
                title: 'Are you sure?',
                text: "Teacher record will be moved to the Trash bin!",
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