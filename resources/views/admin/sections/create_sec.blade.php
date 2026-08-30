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
                <li class="breadcrumb-item active" aria-current="page">Add New Section</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 mx-auto">
        <div class="card radius-10 border-top border-0 border-4 border-success">

            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Add New Section</h5>
                    <p class="mb-0 small text-muted">Create a new section for existing classes</p>
                </div>
                <a href="{{ route('sections.index') }}" class="btn btn-dark btn-sm">
                    <i class="bx bx-list-ul"></i> All Sections
                </a>
            </div>

            <div class="card-body p-4">

                <form action="{{ route('sections.store') }}" method="POST" class="row g-3">
                    @csrf

                    <div class="col-md-12">
                        <label for="name" class="form-label text-secondary">Section Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="e.g. Section A, Blue, or Morning" value="{{ old('name') }}">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label text-secondary">System Generated Code Preview</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary">ID</span>
                            <input type="text" id="code_preview" class="form-control bg-light text-dark" placeholder="SEC-..." readonly>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label for="capacity" class="form-label text-secondary">Student Capacity (Optional)</label>
                        <input type="number" name="capacity" id="capacity"
                            class="form-control @error('capacity') is-invalid @enderror"
                            placeholder="e.g. 40" value="{{ old('capacity') }}">
                        @error('capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label for="description" class="form-label text-secondary">Description (Optional)</label>
                        <textarea name="description" class="form-control" id="description" rows="3"
                            placeholder="Briefly describe the section...">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="status" name="status" value="1" {{ old('status', '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="status">Active Status</label>
                        </div>
                    </div>

                    <div class="col-md-12 mt-4">
                        <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                            <a href="{{ route('sections.index') }}" class="btn btn-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-success px-4 shadow-sm">Create Section</button>
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
        // Toastr Error Handler
        @if($errors->any())
        @foreach($errors->all() as $error)
        toastr.error("{{ $error }}");
        @endforeach
        @endif

        // Real-time Preview Script for SEC- Code
        $('#name').on('input', function() {
            let nameVal = $(this).val().trim().toUpperCase();
            if(nameVal) {
                let firstWord = nameVal.split(' ')[0]; // Targets main keyword
                $('#code_preview').val('SEC-' + firstWord);
            } else {
                $('#code_preview').val('');
            }
        });

        // Keeps value sustained if returned with internal updates
        if($('#name').val()) {
            $('#name').trigger('input');
        }
    });
</script>
@endpush