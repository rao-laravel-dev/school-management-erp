@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Generate Salary Slips</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('salary_slips.index') }}">Salary Slips</a></li>
                <li class="breadcrumb-item active" aria-current="page">Generate</li>
            </ol>
        </nav>
    </div>
    
</div>

<div class="card radius-10">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Generate Salary Slips</h5>
        <span class="badge bg-light text-dark border">
            <i class="bx bx-calendar"></i> {{ now()->format('d-m-Y') }}
        </span>
    </div>
    <div class="card-body">

        <form action="{{ route('salary_slips.generate') }}" method="POST" id="generateForm">
            @csrf

            <div class="row mb-3">
                <div class="col-12 col-md-4 mb-3 mb-md-0">
                    <label class="form-label">Month <span class="text-danger">*</span></label>
                    <input type="month"
                        name="month"
                        class="form-control form-control-sm @error('month') is-invalid @enderror"
                        value="{{ old('month', $prefillMonth ?? now()->format('Y-m')) }}" required>
                    @error('month')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4 mb-3 mb-md-0">
                    <label class="form-label">Generate For <span class="text-danger">*</span></label>
                    <select name="mode" id="mode" class="form-select form-select-sm @error('mode') is-invalid @enderror" required>
                        <option value="all" {{ old('mode', $prefillRole ? 'role' : 'all') == 'all' ? 'selected' : '' }}>All Staff</option>
                        <option value="role" {{ old('mode', $prefillRole ? 'role' : 'all') == 'role' ? 'selected' : '' }}>Specific Role</option>
                        <option value="selected" {{ old('mode') == 'selected' ? 'selected' : '' }}>Selected Staff</option>
                    </select>
                    @error('mode')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4" id="roleField" style="display:none;">
                    <label class="form-label">Select Role</label>
                    <select name="role" class="form-select form-select-sm @error('role') is-invalid @enderror">
                        <option value="">-- Select Role --</option>
                        @foreach($roles as $r)
                        <option value="{{ $r }}" {{ old('role', $prefillRole) == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                    @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3" id="staffField" style="display:none;">
                <label class="form-label">Select Staff <span class="text-danger">*</span></label>
                <div class="border rounded p-2 @error('staff_ids') is-invalid @enderror" style="max-height: 300px; overflow-y: auto;">
                    <div class="form-check mb-2 border-bottom pb-2">
                        <input type="checkbox" id="selectAll" class="form-check-input">
                        <label class="form-check-label fw-semibold" for="selectAll">Select All</label>
                    </div>
                    @forelse($staff as $s)
                    <div class="form-check">
                        <input type="checkbox"
                            name="staff_ids[]"
                            value="{{ $s->id }}"
                            class="form-check-input staff-checkbox"
                            id="staff_{{ $s->id }}"
                            {{ !$s->salaries ? 'disabled' : '' }}
                            {{ in_array($s->id, old('staff_ids', [])) ? 'checked' : '' }}>
                        <label class="form-check-label {{ !$s->salaries ? 'text-muted' : '' }}" for="staff_{{ $s->id }}">
                            {{ $s->name }} <span class="text-secondary">({{ $s->roles->first()->name ?? '-' }})</span>
                            @if(!$s->salaries)
                            <span class="badge bg-danger-subtle text-danger ms-1">No Salary Structure</span>
                            @endif
                        </label>
                    </div>
                    @empty
                    <p class="text-muted mb-0">No staff found.</p>
                    @endforelse
                </div>
                @error('staff_ids')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="alert alert-info text-danger py-2" role="alert">
    <i class="bx bx-info-circle"></i>
    <strong>Note:</strong> Staff with no salary structure are auto-excluded. Duplicate slips for the same month are blocked.
</div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="bx bx-check"></i> Generate
                </button>
                <a href="{{ route('salary_slips.index') }}" class="btn btn-secondary btn-sm px-4">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {

        function toggleMode() {
            const mode = $('#mode').val();
            $('#roleField').toggle(mode === 'role');
            $('#staffField').toggle(mode === 'selected');
        }
        toggleMode();
        $('#mode').on('change', toggleMode);

        $('#selectAll').on('change', function() {
            $('.staff-checkbox:not(:disabled)').prop('checked', $(this).is(':checked'));
        });

        @if(session('success'))
        toastr.success("{{ session('success') }}");
        @endif
        @if(session('error'))
        toastr.error("{{ session('error') }}");
        @endif

        @if($errors->any())
    toastr.error("{{ $errors->first() }}", 'Validation Error');
@endif

        $('#generateForm').on('submit', function(e) {
            e.preventDefault();
            const form = this;

            const mode = $('#mode').val();
            if (mode === 'selected' && $('.staff-checkbox:checked').length === 0) {
                toastr.error('Please select at least one staff member.');
                return;
            }

            Swal.fire({
                title: 'Generate Salary Slips?',
                text: 'This will generate salary slips for the selected staff for this month.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Generate',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#aaa',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush