@extends($current_layout)

@section('title', 'Student Library Members')

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Library', 'url' => '#'],
    ['label' => 'Settings', 'url' => route('library_settings.index')],
    ['label' => 'Student Members', 'url' => route('library_settings.members.student')],
]" />

<div class="container-fluid">

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Select Criteria</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-4">
                    <label for="class_id" class="form-label form-label-sm">Class <span class="text-danger">*</span></label>
                    <select name="class_id" id="class_id" class="form-select form-select-sm">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    <span class="text-danger error-text" data-error="class_id"></span>
                </div>
                <div class="col-md-4">
                    <label for="section_id" class="form-label form-label-sm">Section</label>
                    <select name="section_id" id="section_id" class="form-select form-select-sm">
                        <option value="">All Sections</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bx bx-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Student Members List</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped w-100" id="studentTable">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Library Card No.</th>
                        <th>Admission No</th>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Father Name</th>
                        <th>DOB</th>
                        <th>Gender</th>
                        <th>Mobile</th>
                        <th style="width:70px">Action</th>
                    </tr>
                </thead>
                <tbody id="studentTableBody"></tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    $(function() {
        const searchUrl = "{{ route('library_settings.members.student.search') }}";
        const addUrlBase = "{{ url('library-settings/members/student/add') }}";
        const removeUrlBase = "{{ url('library-settings/members/student/remove') }}";

        // ---------- Initialize empty DataTable on load ----------
        let table = $('#studentTable').DataTable({
            pageLength: 10,
            ordering: true,
            language: {
                emptyTable: "Select a class and search to view students."
            },
            columnDefs: [{
                orderable: false,
                targets: [0, 1, 9]
            }]
        });

        function clearErrors() {
            $('.error-text').text('');
            $('.form-select, .form-control').removeClass('is-invalid');
        }

        // Class -> Section dependent dropdown
        $('#class_id').on('change', function() {
            const classId = $(this).val();
            $('#section_id').html('<option value="">All Sections</option>');
            if (!classId) return;

            $.get("{{ url('fees/collect/get-sections') }}/" + classId, function(res) {
                res.forEach(function(sec) {
                    $('#section_id').append('<option value="' + sec.id + '">' + sec.name + '</option>');
                });
            });
        });

        function renderRow(s, index) {
            const rowClass = s.is_member ? 'table-success' : '';
            const actionBtn = s.is_member ?
                `<button class="btn btn-sm btn-outline-danger btn-remove-student" data-id="${s.id}"><i class="bx bx-undo fw-bold"></i></button>` :
                `<button class="btn btn-sm btn-outline-success btn-add-student" data-id="${s.id}"><i class="bx bx-plus fw-bold"></i></button>`;

            const cardNoHtml = s.library_card_no ?
                `<span class="fw-bold text-danger">${s.library_card_no}</span>` :
                '-';

            const nameHtml = `<i class="bx bx-star text-success me-1"></i><span class="fw-semibold text-primary">${s.name || '-'}</span>`;

            return [
                index + 1,
                cardNoHtml,
                s.admission_no || '-',
                nameHtml,
                s.class_section,
                s.father_name || '-',
                s.dob || '-',
                s.gender || '-',
                s.mobile || '-',
                actionBtn,
            ];
        }

        function markRowClass(rowNode, isMember) {
            $(rowNode).toggleClass('table-success', isMember);
        }

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors();

            const classId = $('#class_id').val();
            if (!classId) {
                $('[data-error="class_id"]').text('The class field is required.');
                $('#class_id').addClass('is-invalid');
                toastr.error('Please select a class.');
                return;
            }

            $.get(searchUrl, $(this).serialize(), function(res) {
                table.clear();

                res.students.forEach((s, index) => {
                    const rowNode = table.row.add(renderRow(s, index)).draw(false).node();
                    markRowClass(rowNode, s.is_member);
                    $(rowNode).attr('id', `student-row-${s.id}`);
                });

                if (!res.students.length) {
                    toastr.info('No students found for this selection.');
                }
            }).fail(function() {
                toastr.error('Unable to load students.');
            });
        });

        // ---------- Add ----------
        $('#studentTable tbody').on('click', '.btn-add-student', function() {
            const btn = $(this);
            const id = btn.data('id');

            $.post(`${addUrlBase}/${id}`, {
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(res) {
                toastr.success(res.message);
                const row = table.row(btn.closest('tr'));
                const rowData = row.data();
                rowData[1] = `<span class="fw-bold text-danger">${res.library_card_no}</span>`; // col 1 = Library Card No.
                rowData[9] = `<button class="btn btn-sm btn-outline-danger btn-remove-student" data-id="${id}"><i class="bx bx-undo fw-bold"></i></button>`; // col 9 = Action
                row.data(rowData).draw(false);
                $(row.node()).addClass('table-success');
            }).fail(function(xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Unable to add member.');
            });
        });

        // ---------- Remove ----------
        $('#studentTable tbody').on('click', '.btn-remove-student', function() {
            const btn = $(this);
            const id = btn.data('id');

            Swal.fire({
                title: 'Remove this member?',
                text: 'Student will lose library membership.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove',
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: `${removeUrlBase}/${id}`,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        toastr.success(res.message);
                        const row = table.row(btn.closest('tr'));
                        const rowData = row.data();
                        rowData[1] = '-'; // col 1 = Library Card No.
                        rowData[9] = `<button class="btn btn-sm btn-outline-success btn-add-student" data-id="${id}"><i class="bx bx-plus fw-bold"></i></button>`; // col 9 = Action
                        row.data(rowData).draw(false);
                        $(row.node()).removeClass('table-success');
                    },
                    error: function(xhr) {
                        toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Unable to remove member.');
                    }
                });
            });
        });
    });
</script>
@endpush