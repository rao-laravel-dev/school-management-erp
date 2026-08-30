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
                <li class="breadcrumb-item active" aria-current="page">Edit Class Subjects</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl-10 mx-auto">
        <div class="card radius-10 border-top border-0 border-4 border-primary">

            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <div>
                    <h5 class="mb-0">Edit Assigned Subjects</h5>
                    <p class="mb-0 small text-muted">Update subject mapping configuration for this class</p>
                </div>
                <a href="{{ route('school-class-subjects.index') }}" class="btn btn-dark btn-sm">
                    <i class="bx bx-list-ul"></i> View Assignments
                </a>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('school-class-subjects.update', $class->id) }}" method="POST" class="row g-3">
                    @csrf

                    <input type="hidden" name="class_id" value="{{ $class->id }}">

                    <div class="mb-3">
                        <label for="class_dropdown">Selected Class <span class="text-danger">*</span></label>
                        <select id="class_dropdown" class="form-control bg-light" disabled>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $c->id == $class->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>
                            <input type="checkbox" id="checkSubjectAll"> Select All Subjects / Options
                        </label>
                    </div>

                    <hr />

                    <div class="mb-3">
                        <label class="d-block mb-3">Modify Subjects <span class="text-danger">*</span></label>

                        @error('subject_ids')
                        <div class="text-danger small mb-2"><i class="bx bx-error-circle"></i> {{ $message }}</div>
                        @enderror

                        <div class="row">
                            @foreach($subjects as $subject)
                            <div class="col-xl-2 col-md-3 col-sm-6 mb-3">
                                <label class="fw-normal text-dark">
                                    <input type="checkbox"
                                        class="subject-checkbox @error('subject_ids') is-invalid @enderror"
                                        name="subject_ids[]"
                                        value="{{ $subject->id }}"
                                        id="subject_{{ $subject->id }}"
                                        {{ in_array($subject->id, $assignedSubjects) ? 'checked' : '' }}>

                                    {{ $subject->name }}
                                    <span class="badge bg-secondary font-12 ms-1">{{ $subject->type }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                            <a href="{{ route('school-class-subjects.index') }}" class="btn btn-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">Update Mapping</button>
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
        // Master Select All logic for subjects mapping
        $('#checkSubjectAll').click(function() {
            $('.subject-checkbox').prop('checked', $(this).prop('checked'));
        });

        // Individual Sync Check logic
        function updateMasterCheckboxState() {
            let totalSubjects = $('.subject-checkbox').length;
            let totalCheckedSubjects = $('.subject-checkbox:checked').length;

            if (totalSubjects === totalCheckedSubjects && totalSubjects > 0) {
                $('#checkSubjectAll').prop('checked', true);
            } else {
                $('#checkSubjectAll').prop('checked', false);
            }
        }

        $('.subject-checkbox').click(function() {
            updateMasterCheckboxState();
        });

        // On Page Load: Sync the state immediately
        updateMasterCheckboxState();

        // VS Code Extension formatter safe alert logic
        if ("{{ $errors->any() ? '1' : '0' }}" === "1") {
            toastr.error("Please select at least one required subject.");

            <?php if ($errors->any()): ?>
                <?php foreach ($errors->all() as $error): ?>
                    console.error("Validation Error: " + "{{ $error }}");
                <?php endforeach; ?>
            <?php endif; ?>
        }
    });
</script>
@endpush