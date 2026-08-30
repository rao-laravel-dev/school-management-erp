@extends($current_layout)

@section('title', 'Staff Library Members')

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Library', 'url' => '#'],
    ['label' => 'Settings', 'url' => route('library_settings.index')],
    ['label' => 'Staff Members', 'url' => route('library_settings.members.staff')],
]" />

<div class="container-fluid">

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Select Criteria</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-4">
                    <label for="role_id" class="form-label form-label-sm">Role <span class="text-danger">*</span></label>
                    <select name="role_id" id="role_id" class="form-select form-select-sm">
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ ucwords($role->name) }}</option>
                        @endforeach
                    </select>
                    <span class="text-danger error-text" data-error="role_id"></span>
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
            <h5 class="mb-0">Staff Members List</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped w-100" id="staffTable">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Library Card No.</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th style="width:70px">Action</th>
                    </tr>
                </thead>
                <tbody id="staffTableBody"></tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    $(function() {
        const searchUrl = "{{ route('library_settings.members.staff.search') }}";
        const addUrlBase = "{{ url('library-settings/members/staff/add') }}";
        const removeUrlBase = "{{ url('library-settings/members/staff/remove') }}";

        // ---------- Initialize empty DataTable on load ----------
        let table = $('#staffTable').DataTable({
            pageLength: 10,
            ordering: true,
            language: {
                emptyTable: "Select a role and search to view staff."
            },
            columnDefs: [{
                orderable: false,
                targets: [0, 1, 5]
            }]
        });

        function clearErrors() {
            $('.error-text').text('');
            $('.form-select, .form-control').removeClass('is-invalid');
        }

        function renderRow(s, index) {
            const actionBtn = s.is_member ?
                `<button class="btn btn-sm btn-outline-danger btn-remove-staff" data-id="${s.user_id}"><i class="bx bx-undo fw-bold"></i></button>` :
                `<button class="btn btn-sm btn-outline-success btn-add-staff" data-id="${s.user_id}"><i class="bx bx-plus fw-bold"></i></button>`;

            const cardNoHtml = s.library_card_no ?
                `<span class="fw-bold text-danger">${s.library_card_no}</span>` :
                '-';

            const nameHtml = `<i class="bx bx-star text-success me-1"></i><span class="fw-semibold text-primary">${s.name ?? '-'}</span>`;

            return [
                index + 1,
                cardNoHtml,
                nameHtml,
                s.email ?? '-',
                s.phone ?? '-',
                actionBtn,
            ];
        }

        function markRowClass(rowNode, isMember) {
            $(rowNode).toggleClass('table-success', isMember);
        }

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors();

            const roleId = $('#role_id').val();
            if (!roleId) {
                $('[data-error="role_id"]').text('The role field is required.');
                $('#role_id').addClass('is-invalid');
                toastr.error('Please select a role.');
                return;
            }

            $.get(searchUrl, $(this).serialize(), function(res) {
                table.clear();

                res.staff.forEach((s, index) => {
                    const rowNode = table.row.add(renderRow(s, index)).draw(false).node();
                    markRowClass(rowNode, s.is_member);
                    $(rowNode).attr('id', `staff-row-${s.user_id}`);
                });

                if (!res.staff.length) {
                    toastr.info('No staff found for this selection.');
                }
            }).fail(function() {
                toastr.error('Unable to load staff.');
            });
        });

        // ---------- Add ----------
        $('#staffTable tbody').on('click', '.btn-add-staff', function() {
            const btn = $(this);
            const id = btn.data('id');

            $.post(`${addUrlBase}/${id}`, {
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(res) {
                toastr.success(res.message);
                const row = table.row(btn.closest('tr'));
                const rowData = row.data();
                rowData[0] = `<span class="fw-bold text-success">${res.library_card_no}</span>`;
                rowData[4] = `<button class="btn btn-sm btn-outline-danger btn-remove-staff" data-id="${id}"><i class="bx bx-undo"></i></button>`;
                row.data(rowData).draw(false);
                $(row.node()).addClass('table-success');
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Unable to add member.');
            });
        });

        // ---------- Remove ----------
        $('#staffTable tbody').on('click', '.btn-remove-staff', function() {
            const btn = $(this);
            const id = btn.data('id');

            Swal.fire({
                title: 'Remove this member?',
                text: 'Staff will lose library membership.',
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
                        rowData[0] = '-';
                        rowData[4] = `<button class="btn btn-sm btn-outline-success btn-add-staff" data-id="${id}"><i class="bx bx-plus"></i></button>`;
                        row.data(rowData).draw(false);
                        $(row.node()).removeClass('table-success');
                    },
                    error: function() {
                        toastr.error('Unable to remove member.');
                    }
                });
            });
        });
    });
</script>
@endpush