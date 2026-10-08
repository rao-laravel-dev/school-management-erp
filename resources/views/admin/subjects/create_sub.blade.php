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
                <li class="breadcrumb-item active" aria-current="page">Add New Subject</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 mx-auto">
        <!-- Border color change to Primary for Subjects -->
        <div class="card radius-10 border-top border-0 border-4 border-primary">

            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <div>
                    <h5 class="mb-0">Add New Subject</h5>
                    <p class="mb-0 small text-muted">Assign a new subject to an academic class</p>
                </div>
                <a href="{{ route('subjects.index') }}" class="btn btn-dark btn-sm">
                    <i class="bx bx-list-ul"></i> All Subjects
                </a>
            </div>

            <div class="card-body p-4">
    <form action="{{ route('subjects.store') }}" method="POST" class="row g-3">
        @csrf

        <div class="col-md-6">
            <label for="name" class="form-label text-secondary fw-bold">Subject Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name"
                class="form-control @error('name') is-invalid @enderror"
                placeholder="e.g. Mathematics, English, Physics" value="{{ old('name') }}">
            @error('name')
                <div class="invalid-feedback fw-bold">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="type" class="form-label text-secondary fw-bold">Subject Type <span class="text-danger">*</span></label>
            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror">
                <option value="" selected disabled>Choose type...</option>
                <option value="Theory" {{ old('type') == 'Theory' ? 'selected' : '' }}>Theory</option>
                <option value="Practical" {{ old('type') == 'Practical' ? 'selected' : '' }}>Practical</option>
                <option value="Both" {{ old('type') == 'Both' ? 'selected' : '' }}>Both</option>
            </select>
            @error('type')
                <div class="invalid-feedback fw-bold">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-12">
            <label for="description" class="form-label text-secondary fw-bold">Description (Optional)</label>
            <textarea name="description" class="form-control @error('description') is-invalid @enderror" id="description" rows="3"
                placeholder="Briefly describe the course/subject scope...">{{ old('description') }}</textarea>
            @error('description')
                <div class="invalid-feedback fw-bold">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="status" name="status" value="1" {{ old('status', '1') == '1' ? 'checked' : '' }}>
                <label class="form-check-label fw-bold text-secondary" for="status">Active Status</label>
            </div>
        </div>

        <div class="col-md-12 mt-4">
            <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                <a href="{{ route('subjects.index') }}" class="btn btn-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4 shadow-sm">Save Subject</button>
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
        // Agar koi bhi validation error ho
        @if($errors->any())
        // Sirf aik generic message dikhayega
        toastr.error("Please fill all required fields correctly.");

        // Debugging ke liye aap console mein errors dekh sakte hain (User ko nazar nahi aayega)
        @foreach($errors->all() as $error)
        console.error("Validation Error: {{ $error }}");
        @endforeach
        @endif
    });
</script>
@endpush