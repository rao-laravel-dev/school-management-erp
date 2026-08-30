@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academic</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Add New Class</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 mx-auto">
        <div class="card radius-10 border-top border-0 border-4 border-primary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Create Class</h5>
                    <p class="mb-0 small text-muted">Setup new academic class in the system</p>
                </div>
                <a href="{{ route('classes.index') }}" class="btn btn-dark btn-sm">
                    <i class="bx bx-list-ul"></i> All Classes
                </a>
            </div>
            <div class="card-body p-4">

                <form action="{{ route('classes.store') }}" method="POST" class="row g-3" novalidate>
                    @csrf

                    <!-- Class Name -->
                    <div class="col-md-12">
                        <label for="class_name" class="form-label text-secondary">Class Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="class_name"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="e.g. Grade 10" value="{{ old('name') }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Numeric Name -->
                    <div class="col-md-12">
                        <label for="numeric_name" class="form-label text-secondary">Numeric Name <span class="text-danger">*</span></label>
                        <input type="number" name="numeric_name" id="numeric_name"
                            class="form-control @error('numeric_name') is-invalid @enderror"
                            placeholder="e.g. 10" value="{{ old('numeric_name') }}">
                        @error('numeric_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="col-md-12">
                        <label for="description" class="form-label text-secondary">Description (Optional)</label>
                        <textarea name="description" class="form-control" id="description" rows="3"
                            placeholder="Enter class details...">{{ old('description') }}</textarea>
                    </div>

                    <!-- Status -->
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="status" name="status" value="1" checked>
                            <label class="form-check-label" for="status">Active Status</label>
                        </div>
                    </div>

                    <div class="col-md-12 mt-4">
                        <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">Save Class</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Validation Errors Toastr
    @if($errors->any())
        @foreach($errors->all() as $error)
            toastr.error("{{ $error }}", 'Validation Error!', {
                "closeButton": true,
                "progressBar": true
            });
        @endforeach
    @endif
</script>
@endpush