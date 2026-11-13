@extends($current_layout)

@section('content')

    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Roles</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item">
                        <a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active">{{ $isSuperPage ? 'Admin Permissions' : 'Role Permissions' }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h6 class="mb-0 text-uppercase">{{ $isSuperPage ? 'Admin Role Permissions' : 'Role Permissions' }}</h6>

            @if (!$isSuperPage)
                <div class="d-flex align-items-center gap-2">
                    <label for="rolePermissionRole" class="form-label mb-0">Role</label>
                    <select id="rolePermissionRole" class="form-select form-select-sm w-auto">
                        @foreach ($roleOptions as $option)
                            <option value="{{ $option }}" {{ $option === $role ? 'selected' : '' }}>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="card-body">
            <p class="text-muted small mb-3">Only existing permissions can be granted or revoked here; none are created or deleted.</p>

            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="rolePermissionAll">
                <label class="form-check-label fw-bold" for="rolePermissionAll">All</label>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <tbody>
                        @forelse ($groups as $group)
                            <tr class="role-permission-row">
                                <td style="width: 28%;">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input role-permission-module"
                                            id="rolePermissionModule{{ $loop->index }}">
                                        <label class="form-check-label fw-semibold" for="rolePermissionModule{{ $loop->index }}">{{ $group['label'] }}</label>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach ($group['perms'] as $perm)
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input role-permission-box"
                                                    id="rolePermission{{ $loop->parent->index }}_{{ $loop->index }}"
                                                    value="{{ $perm['name'] }}" title="{{ $perm['name'] }}"
                                                    {{ $perm['checked'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="rolePermission{{ $loop->parent->index }}_{{ $loop->index }}"
                                                    title="{{ $perm['name'] }}">{{ $perm['verb'] }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center text-muted">No permissions available for this role.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <button type="button" id="rolePermissionSave" class="btn btn-primary btn-sm px-4">Save Permissions</button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(function() {
            const rolePermissionRole = @json($role);
            const rolePermissionIsSuper = @json($isSuperPage);
            const rolePermissionEditUrl = @json($editUrl);
            const rolePermissionUpdateUrl = @json($updateUrl);

            // Module + All checkbox state from the permission boxes
            function rolePermissionSync() {
                $('.role-permission-row').each(function() {
                    const boxes = $(this).find('.role-permission-box');
                    const checked = boxes.filter(':checked').length;
                    const module = $(this).find('.role-permission-module');
                    module.prop('checked', checked > 0 && checked === boxes.length);
                    module.prop('indeterminate', checked > 0 && checked < boxes.length);
                });
                const all = $('.role-permission-box');
                const allChecked = all.filter(':checked').length;
                $('#rolePermissionAll').prop('checked', all.length > 0 && allChecked === all.length);
                $('#rolePermissionAll').prop('indeterminate', allChecked > 0 && allChecked < all.length);
            }

            $('#rolePermissionAll').on('change', function() {
                $('.role-permission-box').prop('checked', $(this).is(':checked'));
                rolePermissionSync();
            });

            $('.role-permission-module').on('change', function() {
                $(this).closest('.role-permission-row').find('.role-permission-box').prop('checked', $(this).is(':checked'));
                rolePermissionSync();
            });

            $('.role-permission-box').on('change', rolePermissionSync);

            // Admin page: switch role = open that role's page
            $('#rolePermissionRole').on('change', function() {
                window.location.href = rolePermissionEditUrl.replace(':id', encodeURIComponent($(this).val()));
            });

            $('#rolePermissionSave').on('click', function() {
                const permissions = $('.role-permission-box:checked').map(function() {
                    return $(this).val();
                }).get();

                const warning = rolePermissionIsSuper
                    ? 'Permissions you remove here are also removed from receptionist, accountant, librarian, teacher, parent and student.'
                    : 'The permissions of this role will be updated.';

                Swal.fire({
                    title: 'Are you sure?',
                    text: warning,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Save',
                    cancelButtonText: 'Cancel',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-success btn-sm me-2',
                        cancelButton: 'btn btn-danger btn-sm'
                    }
                }).then(function(result) {
                    if (!result.isConfirmed) {
                        return;
                    }
                    $.ajax({
                        url: rolePermissionUpdateUrl.replace(':id', encodeURIComponent(rolePermissionRole)),
                        type: 'POST',
                        data: { permissions: permissions },
                        success: function(res) {
                            toastr.success(res.message);
                        },
                        error: function(xhr) {
                            const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Something went wrong.';
                            toastr.error(message);
                        }
                    });
                });
            });

            rolePermissionSync();
        });
    </script>
@endpush
