@extends($current_layout)

@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academic</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('event_type.index') }}">Event Types</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card radius-10">
    <div class="card-body">
        <form action="{{ route('event_type.store') }}" method="POST">
            @csrf
            @include('admin.event_types._form')
            <button class="btn btn-primary mt-3">Save</button>
        </form>
    </div>
</div>

@endsection