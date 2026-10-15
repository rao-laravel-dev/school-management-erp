@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'System', 'url' => '#'],
    ['label' => 'Access Control', 'url' => route('superadmin.access.index')],
]" />

{{-- Red banner jab school locked ho --}}
@if($schoolLocked)
<div class="alert alert-danger fw-bold" role="alert">
    School is LOCKED. No admin is active, so only the Super Admin can log in. Set an admin Active to unlock.
</div>
@endif

<div class="card radius-10">
    <div class="card-header bg-transparent">
        <ul class="nav nav-tabs card-header-tabs" id="accessTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#schoolTab" type="button">School Access</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#adminTab" type="button">Admin Accounts</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#logTab" type="button">Audit Log</button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">

            {{-- a) SCHOOL ACCESS (status only, no button) --}}
            <div class="tab-pane fade show active" id="schoolTab">
                <div class="fw-bold mb-2">School:
                    @if($schoolLocked)
                    <span class="badge bg-danger">Locked</span>
                    @else
                    <span class="badge bg-success">Unlocked</span>
                    @endif
                </div>
                <div class="text-muted mb-1">Active admins: {{ $activeAdminCount }} of {{ $adminCount }}</div>
                <small class="text-muted">The school locks automatically when the last active admin is set Inactive. Every user except the Super Admin is then logged out and blocked, and nobody else's own status changes. Setting any admin Active unlocks it.</small>
            </div>

            {{-- b) ADMIN ACCOUNTS --}}
            <div class="tab-pane fade" id="adminTab">
                <div class="table-responsive">
                    <table id="usersTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Roles</th>
                                <th>Status</th>
                                <th width="140">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- c) AUDIT LOG --}}
            <div class="tab-pane fade" id="logTab">
                <div class="table-responsive">
                    <table id="logsTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>When</th>
                                <th>By</th>
                                <th>Type</th>
                                <th>Target</th>
                                <th>Action</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
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
        var usersTable = $('#usersTable').DataTable({
            pageLength: 10,
            ajax: {
                url: @json(route('superadmin.access.users')),
                error: function(xhr) {
                    toastr.error('Could not load admin accounts.');
                }
            }
        });

        var logsTable = $('#logsTable').DataTable({
            pageLength: 10,
            order: [],
            ajax: {
                url: @json(route('superadmin.access.logs')),
                error: function(xhr) {
                    toastr.error('Could not load the audit log.');
                }
            }
        });

        // Hidden tab mein init hui DataTables ke columns tab show par adjust
        $('#accessTabs button').on('shown.bs.tab', function() {
            $.fn.dataTable.tables({
                visible: true,
                api: true
            }).columns.adjust();
        });

        // Common POST helper (data: action + optional confirm)
        function postAction(url, data, onDone) {
            $.post(url, data)
                .done(function(res) {
                    toastr.success(res.message);
                    onDone(res);
                })
                .fail(function(xhr) {
                    var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Something went wrong.';
                    toastr.error(msg);
                });
        }

        // Admin user: Set Active / Set Inactive (last active admin inactive = school lock, LOCK type karna zaroori)
        $(document).on('click', '.set-user-status', function() {
            var id = $(this).data('id');
            var action = $(this).data('action');
            var isLast = parseInt($(this).data('last'), 10) === 1;
            var url = @json(url('superadmin/access-control/users')) + '/' + id + '/status';

            var options = {
                showCancelButton: true,
                cancelButtonColor: '#3085d6'
            };

            if (action === 'deactivate' && isLast) {
                options.icon = 'error';
                options.title = 'Lock the entire school?';
                options.text = 'This is the LAST active admin. All users will be logged out (admins, staff, teachers, parents, students) and blocked, and API tokens revoked. Only you can log in. Type LOCK to confirm.';
                options.input = 'text';
                options.confirmButtonColor = '#d33';
                options.confirmButtonText = 'Lock school';
                options.inputValidator = function(value) {
                    if (value !== 'LOCK') {
                        return 'Type LOCK exactly to continue.';
                    }
                };
            } else {
                options.icon = 'warning';
                options.title = action === 'activate' ? 'Set this admin Active?' : 'Set this admin Inactive?';
                options.text = action === 'activate' ? 'The admin will be able to log in. If the school is locked, it will be unlocked.' : 'The admin will be logged out and cannot log in. Another admin is still active, so the school stays unlocked.';
                options.confirmButtonColor = action === 'activate' ? '#198754' : '#d33';
                options.confirmButtonText = 'Yes';
            }

            Swal.fire(options).then(function(result) {
                if (result.isConfirmed) {
                    var data = {
                        action: action
                    };
                    if (options.input) {
                        data.confirm = result.value;
                    }
                    postAction(url, data, function(res) {
                        if (res.locked_changed) {
                            window.location.reload();
                            return;
                        }
                        usersTable.ajax.reload(null, false);
                        logsTable.ajax.reload(null, false);
                    });
                }
            });
        });
    });
</script>
@endpush
