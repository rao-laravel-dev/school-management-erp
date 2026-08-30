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
                <li class="breadcrumb-item active" aria-current="page">Edit Class</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 mx-auto">
        <div class="card radius-10 border-top border-0 border-4 border-primary">

            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Edit Class: {{ $class->name }}</h5>
                    <p class="mb-0 small text-muted">Modify existing class details</p>
                </div>
                <a href="{{ route('classes.index') }}" class="btn btn-dark btn-sm">
                    <i class="bx bx-list-ul"></i> All Classes
                </a>
            </div>

            <div class="card-body p-4">
                
                <form action="{{ route('classes.update', $class->id) }}" method="POST" class="row g-3">
                    @csrf
                    
                    {{-- Hidden ID field for update --}}
                    <input type="hidden" name="id" value="{{ $class->id }}">
                    
                    <!-- Class Name -->
                    <div class="col-md-7">
                        <label for="name" class="form-label text-secondary">Class Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               id="name" value="{{ old('name', $class->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Class Code (Read Only) -->
                    <div class="col-md-5">
                        <label for="class_code" class="form-label text-secondary">Class Code</label>
                        <input type="text" class="form-control bg-light" 
                               id="class_code" value="{{ $class->class_code }}" readonly>
                    </div>

                    <!-- Numeric Name -->
                    <div class="col-md-12">
                        <label for="numeric_name" class="form-label text-secondary">Numeric Name <span class="text-danger">*</span></label>
                        <input type="number" name="numeric_name" class="form-control @error('numeric_name') is-invalid @enderror" 
                               id="numeric_name" value="{{ old('numeric_name', $class->numeric_name) }}">
                        @error('numeric_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="col-md-12">
                        <label for="description" class="form-label text-secondary">Description (Optional)</label>
                        <textarea name="description" class="form-control" id="description" rows="3">{{ old('description', $class->description) }}</textarea>
                    </div>

                    <!-- Status -->
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="status" name="status" value="1" 
                                   {{ $class->status == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="status">Active Status</label>
                        </div>
                    </div>

                    <div class="col-md-12 mt-4">
                        <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                            <a href="{{ route('classes.index') }}" class="btn btn-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">Update Changes</button>
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
    @if($errors->any())
        @foreach($errors->all() as $error)
            toastr.error("{{ $error }}");
        @endforeach
    @endif
</script>
@endpush