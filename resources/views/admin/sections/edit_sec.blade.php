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
                <li class="breadcrumb-item active" aria-current="page">Edit Section</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 mx-auto">
        <div class="card radius-10 border-top border-0 border-4 border-primary">

            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Edit Section: {{ $section->name }}</h5>
                    <p class="mb-0 small text-muted">Update section details and assignments</p>
                </div>
                <a href="{{ route('sections.index') }}" class="btn btn-dark btn-sm">
                    <i class="bx bx-list-ul"></i> All Sections
                </a>
            </div>

            <div class="card-body p-4">

                <form action="{{ route('sections.update', $section->id) }}" method="POST" class="row g-3">
                    @csrf

                    <!-- Select Class (Foreign Key) -->
                    <div class="col-md-7">
                        <label for="class_id" class="form-label text-secondary">Class <span class="text-danger">*</span></label>
                        <select name="class_id" class="form-select @error('class_id') is-invalid @enderror">
                            <option value="" selected disabled>Select Class</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}"
                                {{-- Ye line check karegi ke agar ID match hoti hai to 'selected' likh de --}}
                                {{ (old('class_id', $section->class_id) == $class->id) ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('class_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Section Code (Auto Generated - Read Only) -->
                    <div class="col-md-5">
                        <label for="section_code" class="form-label text-secondary">Section Code</label>
                        <input type="text" class="form-control bg-light"
                            id="section_code" value="{{ $section->section_code }}" readonly>
                        <small class="text-muted">Auto-refreshes on update</small>
                    </div>

                    <!-- Section Name -->
                    <div class="col-md-12">
                        <label for="name" class="form-label text-secondary">Section Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $section->name) }}">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Capacity -->
                    <div class="col-md-12">
                        <label for="capacity" class="form-label text-secondary">Capacity</label>
                        <input type="number" name="capacity" id="capacity"
                            class="form-control @error('capacity') is-invalid @enderror"
                            value="{{ old('capacity', $section->capacity) }}">
                        @error('capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="col-md-12">
                        <label for="description" class="form-label text-secondary">Description</label>
                        <textarea name="description" class="form-control" id="description" rows="3">{{ old('description', $section->description) }}</textarea>
                    </div>

                    <!-- Status -->
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="status" name="status" value="1"
                                {{ $section->status == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="status">Active Status</label>
                        </div>
                    </div>

                    <div class="col-md-12 mt-4">
                        <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                            <a href="{{ route('sections.index') }}" class="btn btn-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">Update Section</button>
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
    $(document).ready(function() {
        @if($errors->any())
        @foreach($errors->all() as $error)
        toastr.error("{{ $error }}");
        @endforeach
        @endif
    });
</script>
@endpush