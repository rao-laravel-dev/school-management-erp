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
                <li class="breadcrumb-item active" aria-current="page">Add New Global Group</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 mx-auto">
        <div class="card radius-10 border-top border-0 border-4 border-primary">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <div>
                    <h5 class="mb-0">Create Global Group</h5>
                    <p class="mb-0 small text-muted">Setup independent school group (Admin can type anything custom)</p>
                </div>
                <a href="{{ route('groups.index') }}" class="btn btn-dark btn-sm">
                    <i class="bx bx-list-ul"></i> All Groups
                </a>
            </div>
            <div class="card-body p-4">

                <form action="{{ route('groups.store') }}" method="POST" class="row g-3" novalidate>
                    @csrf

                    <div class="col-md-6">
                        <label for="name" class="form-label text-secondary">Group Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g., Pre-Medical, Pre-Engineering, Arts" value="{{ old('name') }}">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="group_code" class="form-label text-secondary">Group Code <span class="text-danger">*</span></label>
                        <input type="text" name="group_code" id="group_code" class="form-control text-uppercase @error('group_code') is-invalid @enderror" placeholder="e.g., MED, ENG, ART" value="{{ old('group_code') }}">
                        <small class="text-muted">Enter a short code for mapping (uppercase recommended)</small>
                        @error('group_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-12">
                        <label for="description" class="form-label text-secondary">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Enter academic scope details...">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="statusSwitch" checked>
                            <label class="form-check-label" for="statusSwitch">Active Status</label>
                        </div>
                    </div>

                    <div class="col-md-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">Save Global Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Flag to check if user has manually changed the group code
    let isManualCode = false;

    // Jab admin. khud group_code wale box mein type karega
    $(document).on('input', '#group_code', function() {
        isManualCode = $(this).val().trim().length > 0;
    });

    // Smart Shorthand Auto-Generation Engine
    $(document).on('input', '#name', function() {
        let name = $(this).val().trim();
        let codeInput = $('#group_code');
        
        if (!isManualCode) {
            if (name.length >= 3) {
                // Regex se extra spaces saaf karke words array banayein
                let words = name.replace(/\s+/g, ' ').split(' ');
                let suggestedCode = '';
                
                if (words.length > 1 && words[1].length > 0) {
                    // 🔥 RULE 2: Agar 2 words hain (e.g., "Higher Secondary")
                    // First word se 2 chars + Second word se 1 char = HIS
                    let firstPart = words[0].substring(0, 2);
                    let secondPart = words[1].substring(0, 1);
                    suggestedCode = firstPart + secondPart;
                } else {
                    // 🔥 RULE 1: Agar single word hai (e.g., "Primary")
                    // Direct pehle 3 characters uthayega = PRI
                    suggestedCode = name.substring(0, 3);
                }
                
                // Hamesha uppercase mein set karein aur input box ko value dein
                codeInput.val(suggestedCode.toUpperCase());
            } else {
                codeInput.val('');
            }
        }
    });

    // Name poora khali hone par reset karein
    $(document).on('blur', '#name', function() {
        if ($(this).val().trim() === '') {
            isManualCode = false;
            $('#group_code').val('');
        }
    });

    // Validation Errors Notification
    @if($errors->any())
        @foreach($errors->all() as $error)
            toastr.error("{{ $error }}", 'Validation Error!');
        @endforeach
    @endif
</script>
@endpush