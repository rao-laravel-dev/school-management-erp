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
                <li class="breadcrumb-item">
                    <a href="{{ route('groups.index') }}">Groups</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Edit Global Group</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 mx-auto">
        <div class="card radius-10 border-top border-0 border-4 border-primary">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <div>
                    <h5 class="mb-0">Edit Global Academic Group</h5>
                    <p class="mb-0 small text-muted">Modify custom group configuration and code globally</p>
                </div>
                <a href="{{ route('groups.index') }}" class="btn btn-dark btn-sm">
                    <i class="bx bx-list-ul"></i> All Groups
                </a>
            </div>
            <div class="card-body p-4">

                <form action="{{ route('groups.update', $group->id) }}" method="POST" class="row g-3" novalidate>
                    @csrf

                    <div class="col-md-6">
                        <label for="name" class="form-label text-secondary">Group Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g., Science, Arts, Pre-Medical" value="{{ old('name', $group->name) }}">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="group_code" class="form-label text-secondary">Group Code <span class="text-danger">*</span></label>
                        <input type="text" name="group_code" id="group_code" class="form-control text-uppercase @error('group_code') is-invalid @enderror" placeholder="e.g., SCI, ART, MED" value="{{ old('group_code', $group->group_code) }}">
                        @error('group_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-12">
                        <label for="description" class="form-label text-secondary">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Enter details...">{{ old('description', $group->description) }}</textarea>
                    </div>

                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="statusSwitch" {{ $group->status == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="statusSwitch">Active Status</label>
                        </div>
                    </div>

                    <div class="col-md-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">Update Global Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Agar edit karte waqt admin. naam poora badalta hai, to code auto-suggest hoga
    $(document).on('input', '#name', function() {
        let name = $(this).val().trim();
        let codeInput = $('#group_code');
        
        if (name.length >= 3) {
            let suggestedCode = name.substring(0, 3).toUpperCase();
            codeInput.val(suggestedCode);
        } else {
            codeInput.val('');
        }
    });

    // Validation Errors Toastr Trigger
    @if($errors->any())
        @foreach($errors->all() as $error)
            toastr.error("{{ $error }}", 'Validation Error!', {
                closeButton: true,
                progressBar: true
            });
        @endforeach
    @endif
</script>
@endpush