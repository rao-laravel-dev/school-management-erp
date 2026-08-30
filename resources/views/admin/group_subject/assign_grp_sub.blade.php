@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academic</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('admindashboard') }}"><i class="bx bx-home-alt"></i></a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Assign Group Subjects</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl-11 mx-auto">
        <div class="card radius-10 border-top border-0 border-4 border-primary">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <div>
                    <h5 class="mb-0">Assign Subjects to Group</h5>
                    <p class="mb-0 small text-muted">Map multiple academic subjects to a single group</p>
                </div>
                <a href="{{ route('admingroup-subjects.index') }}" class="btn btn-dark btn-sm">
                    <i class="bx bx-list-ul"></i> View Group Assignments
                </a>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('admingroup-subjects.save') }}" method="POST" class="row g-3">
                    @csrf

                    <div class="mb-3 col-md-12">
                        <label for="group_id" class="form-label text-secondary fw-bold">Select Group <span class="text-danger">*</span></label>
                        <select name="group_id" id="group_id" class="form-select @error('group_id') is-invalid @enderror">
                            <option value="" selected disabled>Choose a group...</option>
                            @foreach($groups as $g)
                            <option value="{{ $g->id }}" @selected(old('group_id')==$g->id)>
                                {{ $g->name }} ({{ $g->group_code }})
                            </option>
                            @endforeach
                        </select>
                        @error('group_id')
                        <div class="invalid-feedback fw-bold d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2 col-md-12">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="checkSubjectAll">
                            <label class="form-check-label fw-bold text-primary" for="checkSubjectAll" style="cursor: pointer;">
                                Select All Subjects / Options
                            </label>
                        </div>
                    </div>

                    <hr />

                    <div class="mb-3 col-md-12">
                        <label class="d-block mb-3 text-secondary fw-bold">Select Subjects <span class="text-danger">*</span></label>

                        @error('subject_ids')
                        <div class="text-danger small fw-bold mb-2"><i class="bx bx-error-circle"></i> {{ $message }}</div>
                        @enderror

                        <div class="row">
                            @foreach($subjects as $subject)
                            <div class="col-xl-2 col-md-3 col-sm-6 mb-3">
                                <div class="form-check card p-2 shadow-none border-dashed mb-0 bg-light">
                                    <input type="checkbox"
                                        class="form-check-input subject-checkbox @error('subject_ids') is-invalid @enderror"
                                        name="subject_ids[]"
                                        value="{{ $subject->id }}"
                                        id="subject_{{ $subject->id }}"
                                        @checked(is_array(old('subject_ids')) && in_array($subject->id, old('subject_ids')))>
                                    <label class="form-check-label fw-bold text-dark ms-1" for="subject_{{ $subject->id }}" style="cursor: pointer;">
                                        {{ $subject->name }}
                                        <span class="badge bg-secondary font-12 ms-1 text-uppercase">{{ $subject->type }}</span>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top col-md-12">
                        <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                            <a href="{{ route('admingroup-subjects.index') }}" class="btn btn-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">Save Mapping</button>
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

        // 1. Master Select All logic (Event Delegation)
        $(document).on('change', '#checkSubjectAll', function() {
            $('.subject-checkbox').prop('checked', this.checked);
        });

        // 2. Individual Sync Check logic
        function updateGroupMasterCheckboxState() {
            let totalSubjects = $('.subject-checkbox').length;
            let totalCheckedSubjects = $('.subject-checkbox:checked').length;

            if (totalSubjects === totalCheckedSubjects && totalSubjects > 0) {
                $('#checkSubjectAll').prop('checked', true);
            } else {
                $('#checkSubjectAll').prop('checked', false);
            }
        }

        $(document).on('change', '.subject-checkbox', function() {
            updateGroupMasterCheckboxState();
        });

        // Memory state handler on load
        updateGroupMasterCheckboxState();

        // 3. Sir Ke Standard Ke Mutabiq Error Logging & Toastr
        if ("{{ $errors->any() ? '1' : '0' }}" === "1") {
            toastr.error("Please select all required fields correctly.");

            <?php if ($errors->any()): ?>
                <?php foreach ($errors->all() as $error): ?>
                    console.error("Validation Error: " + "{{ $error }}");
                <?php endforeach; ?>
            <?php endif; ?>
        }
    });
</script>
@endpush