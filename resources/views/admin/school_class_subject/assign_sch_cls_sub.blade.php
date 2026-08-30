@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academic</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Assign Class Subjects</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl-10 mx-auto">
        <div class="card radius-10 border-top border-0 border-4 border-primary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Assign Subjects to Class</h5>
                    <p class="mb-0 small text-muted">Map multiple academic subjects to a single class</p>
                </div>
                <a href="{{ route('school-class-subjects.index') }}" class="btn btn-dark btn-sm">
                    <i class="bx bx-list-ul"></i> View Assignments
                </a>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('school-class-subjects.save') }}" method="POST" class="row g-3">
                    @csrf

                    <div class="col-md-6">
                        <label class="form-label text-secondary fw-bold">Select Class(es) <span class="text-danger">*</span></label>
<select name="class_id[]" id="class_id" class="form-select" multiple>
    @foreach($classes as $class)
    <option value="{{ $class->id }}">{{ $class->name }}</option>
    @endforeach
</select>
<small class="text-muted">Hold Ctrl (or Cmd) to select multiple classes.</small>
                        @error('class_id') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary fw-bold">Select Academic Group (Optional)</label>
                        <select name="group_id" id="group_id" class="form-select @error('group_id') is-invalid @enderror">
                            <option value="">General / Compulsory</option>
                            @foreach($groups as $group)
                            {{-- Old logic ke sath selected state maintain rakha hai --}}
                            <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                {{ $group->name }} ({{ $group->group_code }})
                            </option>
                            @endforeach
                        </select>
                        @error('group_id') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-12 mt-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="checkSubjectAll">
                            <label class="form-check-label fw-bold text-primary" for="checkSubjectAll">Select All Subjects / Options</label>
                        </div>
                    </div>

                    <hr />

                    <div class="col-md-12">
                        <label class="d-block mb-3 text-secondary fw-bold">Select Subjects <span class="text-danger">*</span></label>

                        @error('subject_ids')
                        <div class="text-danger small fw-bold mb-2"><i class="bx bx-error-circle"></i> {{ $message }}</div>
                        @enderror

                        <div class="row">
                            @foreach($subjects as $subject)
                            <div class="col-xl-2 col-md-3 col-sm-6 mb-3">
                                <div class="form-check card p-2 shadow-none border-dashed mb-0 bg-light">
                                    <input type="checkbox"
                                        class="form-check-input subject-checkbox"
                                        name="subject_ids[]"
                                        value="{{ $subject->id }}"
                                        id="subject_{{ $subject->id }}"
                                        {{ (is_array(old('subject_ids')) && in_array($subject->id, old('subject_ids'))) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark sorted-label ms-1" for="subject_{{ $subject->id }}">
                                        {{ $subject->name }}
                                        <span class="badge bg-secondary font-12 ms-1 text-uppercase">{{ $subject->type }}</span>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-md-12 mt-4 pt-3 border-top">
                        <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                            <a href="{{ route('school-class-subjects.index') }}" class="btn btn-secondary px-4">Cancel</a>
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
        // --- GROUP REQUIREMENT LOGIC ---
        function handleGroupVisibility() {
            // .val() ab array return karega, hum check karenge kya usmein 9 ya 10 hai
            let selectedClasses = $('#class_id').val() || [];
            
            if (selectedClasses.includes('9') || selectedClasses.includes('10')) {
                $('#group_id').prop('required', true);
            } else {
                $('#group_id').prop('required', false);
            }
        }

        $('#class_id').on('change', handleGroupVisibility);
        handleGroupVisibility();

        // --- BULK SELECT LOGIC ---
        $('#bulk_group').on('change', function() {
            let group = $(this).val();
            let groups = {
                'primary': ['1', '2', '3', '4', '5'],
                'middle': ['6', '7', '8'],
                'science': ['9', '10']
            };
            
            if(groups[group]) {
                $('#class_id').val(groups[group]).trigger('change'); 
            }
        });

        // --- MASTER SELECT ALL ---
        $(document).on('change', '#checkSubjectAll', function() {
            $('.subject-checkbox').prop('checked', this.checked);
        });

        function updateMasterCheckboxState() {
            let total = $('.subject-checkbox').length;
            let checked = $('.subject-checkbox:checked').length;
            $('#checkSubjectAll').prop('checked', (total === checked && total > 0));
        }
        $(document).on('change', '.subject-checkbox', updateMasterCheckboxState);
        updateMasterCheckboxState();
    });
</script>
@endpush