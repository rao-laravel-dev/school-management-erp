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
                <li class="breadcrumb-item active" aria-current="page">Edit Group Subjects</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl-11 mx-auto">
        <div class="card radius-10 border-top border-0 border-4 border-primary">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <div>
                    <h5 class="mb-0">Edit Assigned Group Subjects</h5>
                    <p class="mb-0 small text-muted">Modify subject configuration for the selected group</p>
                </div>
                <a href="{{ route('admingroup-subjects.index') }}" class="btn btn-dark btn-sm">
                    <i class="bx bx-list-ul"></i> View Group Assignments
                </a>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('admingroup-subjects.save') }}" method="POST" class="row g-3">
                    @csrf

                    <div class="mb-3">
                        <label for="group_id">Select Group <span class="text-danger">*</span></label>
                        <select name="group_id" id="group_id" class="form-control @error('group_id') is-invalid @enderror">
                            @foreach($groups as $g)
                            <option value="{{ $g->id }}" @selected($g->id == $group->id)>
                                {{ $g->name }} ({{ $g->school_class->name ?? 'No Class' }})
                            </option>
                            @endforeach
                        </select>
                        @error('group_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label style="cursor: pointer;">
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
                                <label class="fw-normal text-dark" style="cursor: pointer;">
                                    <input type="checkbox" 
                                           class="subject-checkbox @error('subject_ids') is-invalid @enderror"
                                           name="subject_ids[]"
                                           value="{{ $subject->id }}"
                                           id="subject_{{ $subject->id }}"
                                           @checked(in_array($subject->id, $assignedSubjects))>
                                    
                                    {{ $subject->name }}
                                    <span class="badge bg-secondary font-12 ms-1">{{ $subject->type }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                            <a href="{{ route('admingroup-subjects.index') }}" class="btn btn-secondary px-4">Cancel</a>
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
        $('#checkSubjectAll').click(function() {
            $('.subject-checkbox').prop('checked', $(this).prop('checked'));
        });

        function updateMasterCheckboxState() {
            let total = $('.subject-checkbox').length;
            let checked = $('.subject-checkbox:checked').length;
            $('#checkSubjectAll').prop('checked', total === checked && total > 0);
        }

        $('.subject-checkbox').click(function() {
            updateMasterCheckboxState();
        });

        updateMasterCheckboxState();

        if ("{{ $errors->any() ? '1' : '0' }}" === "1") {
            toastr.error("Please fill all configuration details correctly.");
        }
    });
</script>
@endpush