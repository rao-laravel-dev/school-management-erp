@extends('parent.layout.app')

@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
    <div class="breadcrumb-title pe-3">Dashboard</div>
</div>

<div class="card radius-10">
    <div class="card-body text-center py-5">
        <i class="bx bx-user-x fs-1 text-secondary"></i>
        <h5 class="mt-3 mb-1">No student is linked to your account yet.</h5>
        <p class="text-secondary mb-0">Please contact the school office.</p>
    </div>
</div>

@endsection
