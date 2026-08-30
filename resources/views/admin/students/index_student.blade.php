@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Student Information', 'url' => '#'],
    ['label' => 'All Students', 'url' => route('students.index')],
]" />

<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-3 align-items-center">

    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-primary shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">Total Students</p>
                        <h5 class="my-0 text-primary fw-bold" id="total-count">{{ $totalCount }}</h5>
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
                        <p class="mb-0 text-secondary small">Active</p>
                        <h5 class="my-0 text-success fw-bold" id="active-count">{{ $activeCount }}</h5>
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
                        <h5 class="my-0 text-danger fw-bold" id="inactive-count">{{ $inactiveCount }}</h5>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto" style="width: 45px; height: 45px; line-height: 45px;">
                        <i class='bx bxs-x-circle'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col text-end ms-auto">
        <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm px-4 shadow-sm">
            <i class='bx bx-plus'></i> New Admission
        </a>
    </div>

</div>

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                {{-- Title --}}
                <h5 class="mb-0 text-primary">Student Directory</h5>

                {{-- Right Side Actions --}}
                <div class="d-flex gap-2 align-items-center">

                    {{-- Trash Bin Button (Protected by permission) --}}
                    @can('manage-students')
                    <a href="{{ route('students.trash') }}" class="btn btn-danger btn-sm">
                        <i class="bx bx-trash"></i> Trash Bin
                    </a>
                    @endcan

                    {{-- Record Badge --}}
                    <span class="badge bg-primary m-0 align-self-center" id="directory-badge">
                        Records: {{ $enrollments->count() }}
                    </span>
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-4 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Select Class</label>
                        <select id="filter-class" class="form-select form-select-sm">
                            <option value="">-- Choose Class --</option>
                            @foreach($classes as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Select Section</label>
                        <select id="filter-section" class="form-select form-select-sm">
                            <option value="">-- Choose Section --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Select Group</label>
                        <select id="filter-group" name="group_id" class="form-select form-select-sm">
                            <option value="">-- Choose Group --</option>
                            @if(isset($groups))
                            @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" id="filter-keyword" class="form-control form-control-sm"
                            placeholder="Name, Admission No...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Profile</th>
                                <th>Full Name</th>
                                <th>Father Name</th>
                                <th>Phone No</th>
                                <th>Admission No</th>
                                <th>Roll No</th>
                                <th>Class</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="student-table-body">
                            @include('admin.students.partials.table_rows', ['enrollments' => $enrollments])
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL WINDOW OPEN KRNE K LIYE -->
<div class="modal fade" id="studentDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Student Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-body-content">
                <div class="text-center">Loading...</div>
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
            "timeOut": "6000",
            "showDuration": "300",
            "hideDuration": "1000"
        };

        @if(session('toastr-success'))
        toastr.success("{{ session('toastr-success') }}", "Success");
        @endif

        @if(session('toastr-error'))
        toastr.error("{{ session('toastr-error') }}", "Error");
        @endif

        // --- 1. STUDENT MODAL CLICK EVENT (FIXED: Moved inside ready) ---
        $(document).on('click', '.view-student-btn', function(e) {
            e.preventDefault();

            let studentId = $(this).data('id');
            let modal = $('#studentDetailModal');
            let modalBody = $('#modal-body-content');

            modal.modal('show');
            modalBody.html('<div class="text-center p-3"><div class="spinner-border text-primary" role="status"></div><p>Loading...</p></div>');

            $.ajax({
                url: "{{ url('/students/get-details') }}/" + studentId,
                type: 'GET',
                success: function(response) {
                    modalBody.html(response);
                },
                error: function(xhr) {
                    console.error("AJAX Error:", xhr);
                    modalBody.html('<p class="text-danger text-center">Error: ' + xhr.status + ' - Please check console.</p>');
                }
            });
        });

        // --- CENTRAL FUNCTION: FETCH STUDENTS (UPDATED: keyword added) ---
        function fetchStudents() {
            let data = {
                class_id: $('#filter-class').val(),
                section_id: $('#filter-section').val(),
                group_id: $('#filter-group').val(),
                keyword: $('#filter-keyword').val() // 👈 naya field
            };

            $.ajax({
                url: "{{ route('students.filter') }}",
                method: "GET",
                data: data,
                cache: false,
                success: function(res) {
                    if (res.status === 'success') {
                        if ($.fn.DataTable.isDataTable('#example')) {
                            $('#example').DataTable().clear().destroy();
                        }

                        $('#student-table-body').html(res.html);

                        $('#example').DataTable({
                            "paging": true,
                            "ordering": true,
                            "info": true,
                            "columnDefs": [{
                                "orderable": false,
                                "targets": [0, 1, 8, 9]
                            }]
                        });

                        $('#directory-badge').text('Records: ' + res.count);
                    }
                },
                error: function(err) {
                    console.error("AJAX Error:", err);
                }
            });
        }

        // --- NAYA: KEYWORD SEARCH INPUT PAR DEBOUNCE (500ms) ---
        let searchTimer;
        $('#filter-keyword').on('keyup', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                fetchStudents();
            }, 500);
        });

        // --- CENTRAL FUNCTION: LOAD DEPENDENT DROPDOWNS ---
        function loadDependentDropdowns(class_id) {
            $('#filter-section').html('<option value="">Select Section</option>');
            $('#filter-group').html('<option value="">-- Choose Group --</option>');

            if (!class_id) {
                fetchStudents();
                return;
            }

            $.when(
                $.get("/students/get-sections/" + class_id),
                $.get("/students/get-groups/" + class_id)
            ).done(function(sectionRes, groupRes) {
                if (sectionRes[0].status === 'success') {
                    sectionRes[0].data.forEach(s => $('#filter-section').append(`<option value="${s.id}">${s.name}</option>`));
                }
                if (groupRes[0].status === 'success') {
                    groupRes[0].data.forEach(g => $('#filter-group').append(`<option value="${g.id}">${g.name}</option>`));
                }

                // Zaroori: Dropdowns update hone ke baad hi table load karein
                fetchStudents();
            });
        }

        // --- 2. EVENTS: TEENO DROPDOWNS PAR LISTENER ---
        $('#filter-class, #filter-section, #filter-group').change(function() {
            let id = $(this).attr('id');

            if (id === 'filter-class') {
                // Class change hone par pehle dropdowns update karein
                loadDependentDropdowns($(this).val());

                // YAHAN GHALTI THI: Class change hone par table ko bhi update hona chahiye!
                // Lekin dropdowns ke update hone ka wait karna padega, isliye 
                // ise loadDependentDropdowns ke 'success' function ke andar call karein.
            } else {
                // Section ya Group change hone par direct fetchStudents
                fetchStudents();
            }
        });
        // end new code for dropdown

        // --- 2. STATE 1: PENDING ADMISSION ACTIONS ---
        $(document).on('click', '.toggle-status-pending', function(e) {
            e.preventDefault();
            let btn = $(this);
            let actionUrl = btn.data('url');

            Swal.fire({
                title: 'Process Admission Request',
                text: "Do you want to Approve or Reject this pending student profile?",
                icon: 'question',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonColor: '#28a745',
                denyButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check me-1"></i> Approve',
                denyButtonText: '<i class="fas fa-times me-1"></i> Reject',
                cancelButtonText: 'Cancel',
                customClass: {
                    container: 'swal2-override-absolute-display'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    executeAdmissionStatus(btn, actionUrl, 'approve');
                } else if (result.isDenied) {
                    executeAdmissionStatus(btn, actionUrl, 'reject');
                }
            });
        });

        // Ajax Engine for Pending State Changes
        function executeAdmissionStatus(btn, url, action) {
            btn.prop('disabled', true);
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    action: action
                },
                dataType: 'json',
                success: function(res) {
                    // Update Dashboard Counters (Server se aayi hui value)
                    if (res.activeCount !== undefined) $('#active-count').text(res.activeCount);
                    if (res.inactiveCount !== undefined) $('#inactive-count').text(res.inactiveCount);
                    if (res.pendingCount !== undefined) $('#pending-count').text(res.pendingCount);

                    let tdContainer = btn.parent();
                    let toggleRoute = "{{ route('students.status', ':id') }}".replace(':id', btn.data('id'));

                    if (action === 'approve') {
                        toastr.success(res.message);
                        tdContainer.html(`<button type="button" class="btn btn-sm toggle-status-approved btn-outline-success" data-id="${btn.data('id')}" data-url="${toggleRoute}">Active</button>`);
                    } else {
                        toastr.warning(res.message);
                        tdContainer.html(`<button type="button" class="btn btn-sm toggle-status-approved btn-outline-danger" data-id="${btn.data('id')}" data-url="${toggleRoute}">Inactive</button>`);
                    }
                    btn.prop('disabled', false);
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    toastr.error("Update failed.");
                }
            });
        }

        // --- 3. STATE 2 & 3: STANDARD APPROVED TOGGLE INTERACTION ---
        $(document).on('click', '.toggle-status-approved', function(e) {
            e.preventDefault();
            let btn = $(this);
            btn.prop('disabled', true);

            $.ajax({
                url: btn.data('url'),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function(res) {
                    // Update Dashboard Counters (Server se aayi hui value)
                    if (res.activeCount !== undefined) $('#active-count').text(res.activeCount);
                    if (res.inactiveCount !== undefined) $('#inactive-count').text(res.inactiveCount);
                    if (res.pendingCount !== undefined) $('#pending-count').text(res.pendingCount);

                    // Button UI Update
                    if (res.status == 1) {
                        btn.removeClass('btn-outline-danger').addClass('btn-outline-success').text('Active');
                    } else {
                        btn.removeClass('btn-outline-success').addClass('btn-outline-danger').text('Inactive');
                    }
                    toastr.success(res.message);
                    btn.prop('disabled', false);
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    toastr.error("Sync failed.");
                }
            });
        });

        // --- 4. HARD DELETION CONFIRMATION MATRIX ---
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");

            Swal.fire({
                title: 'Are you sure?',
                text: "Student records and user system accounts will be permanently wiped out!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link;
                }
            });
        });
    });
</script>
@endpush