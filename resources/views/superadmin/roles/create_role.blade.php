@extends('superadmin.dashboard')

@section('content')

    <!--start page wrapper -->
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Roles</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item">
                        <a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active">Create Role</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="card">
        <div class="card-header">
            <h6 class="mb-0 text-uppercase">Create Role</h6>
        </div>

        <div class="card-body">

            <form method="POST" action="{{ route('adminsuperadmin.roles.store') }}">
                @csrf

                <div class="row">

                    <!-- Role Name -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Role Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter Role Name">
                    </div>

                </div>

                <!-- Permissions -->
                <div class="row">
                    <div class="mb-3">
                        <div class="form-group">
                            <label for="name">Permissions</label>
                            <hr>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="checkPermissionAll" value="1">
                                <label class="form-check-label" for="checkPermissionAll">All</label>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="Management" value="Roles">
                                        <label for="checkPermission" class="form-check-label">Roles</label>
                                    </div>
                                </div>
                                <div class="col-9 role-0-management-checkbox">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="permission"
                                            id="checkPermission0" value="Permission">
                                        <label for="checkPermission0" class="form-check-label">Permission</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="permission"
                                            id="checkPermission1" value="Permission">
                                        <label for="checkPermission1" class="form-check-label">List</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="permission"
                                            id="checkPermission1" value="Permission">
                                        <label for="checkPermission1" class="form-check-label">Create</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="permission"
                                            id="checkPermission1" value="Permission">
                                        <label for="checkPermission1" class="form-check-label">Edit</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="permission"
                                            id="checkPermission1" value="Permission">
                                        <label for="checkPermission1" class="form-check-label">Delete</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary px-4">
                        Save Role
                    </button>
                </div>

            </form>

        </div>
    </div>
    <!--end page wrapper -->

@endsection