@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Teacher Managment', 'url' => '#'],
    ['label' => 'Assign Class', 'url' => route('teacher.assign.index')],
]" />

<div class="row">
    {{-- LEFT COLUMN: INPUT ASSIGNMENT FORM --}}
    <div class="col-12 col-lg-4">
        <div class="card border shadow-none radius-10">
            <div class="card-body p-4">
                <h5 class="card-title text-primary mb-3 fw-bold">Assign Class Teacher</h5>
                <hr />
                <form action="{{ isset($editData) ? route('teacher.assign.update', $editData->id) : route('teacher.assign.store') }}" method="POST">
                    @csrf
                    @if(isset($editData)) @method('PUT') @endif

                    <div class="mb-3">
                        <label for="academic_year_id" class="form-label text-secondary small fw-bold">Academic Year *</label>
                        <select name="academic_year_id" id="academic_year_id" class="form-select form-select-sm @error('academic_year_id') is-invalid @enderror">
                            <option value="">Select Session</option>
                            @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ (old('academic_year_id', isset($editData) ? $editData->academic_year_id : '') == $year->id) ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('academic_year_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="class_id" class="form-label text-secondary small fw-bold">Class *</label>
                        <select name="class_id" id="class_id" class="form-select form-select-sm @error('class_id') is-invalid @enderror">
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ (old('class_id', isset($editData) ? $editData->class_id : '') == $class->id) ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('class_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="section_id" class="form-label text-secondary small fw-bold">Section *</label>
                        <select name="section_id" id="section_id" class="form-select form-select-sm">
                            <option value="">Select Section</option>
                            @if(isset($sections) && $sections->count() > 0)
                            @foreach($sections as $section)
                            <option value="{{ $section->id }}"
                                {{ (isset($editData) && $editData->section_id == $section->id) ? 'selected' : '' }}>
                                {{ $section->name }}
                            </option>
                            @endforeach
                            @endif
                        </select>
                        @error('section_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="class_subject_id" class="form-label text-secondary small fw-bold">Subject (optional)</label>
                        <select name="class_subject_id" id="class_subject_id" class="form-select form-select-sm">
                            <option value="">Whole Class (No specific subject)</option>
                            @if(isset($subjects) && $subjects->count() > 0)
                            @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}"
                                {{ (isset($editData) && $editData->class_subject_id == $subject->id) ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                            @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold d-block mb-2">Class Teacher *</label>
                        <div class="p-1" style="max-height: 200px; overflow-y: auto; border: 1px solid #eee;">
                            @foreach($teachers as $teacher)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="teacher_id[]" value="{{ $teacher->id }}" id="teacher_{{ $teacher->id }}"
                                    {{ (isset($editData) && $editData->teacher_id == $teacher->id) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="teacher_{{ $teacher->id }}">{{ $teacher->first_name }} {{ $teacher->last_name }}</label>
                            </div>
                            @endforeach
                        </div>
                        @error('teacher_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bx bx-save"></i> {{ isset($editData) ? 'Update' : 'Save' }} Assignment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- RIGHT COLUMN: TABLE --}}
    <div class="col-12 col-lg-8">
        <div class="card border shadow-none radius-10">
            <div class="card-body p-4">
                <h5 class="card-title text-primary mb-0 fw-bold">Class Teacher List</h5>
                <hr />
                <div class="table-responsive">
                    <table id="assignmentTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Teacher</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $row->schoolClass->name ?? 'N/A' }}</td>
                                <td>
                                    @php
                                    $sectionName = strtoupper($row->section->name ?? '');
                                    $badgeClass = 'bg-light-secondary text-secondary border-secondary';

                                    switch ($sectionName) {
                                    case 'A': $badgeClass = 'bg-light-success text-success border-success'; break;
                                    case 'B': $badgeClass = 'bg-light-info text-info border-info'; break;
                                    case 'C': $badgeClass = 'bg-light-warning text-warning border-warning'; break;
                                    case 'D': $badgeClass = 'bg-light-danger text-danger border-danger'; break;
                                    }
                                    @endphp
                                    <span class="badge {{ $badgeClass }} border border-opacity-25 px-2 py-1 small fw-bold">
                                        {{ $row->section->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-success"><i class="bx bx-star me-1"></i></span>
                                    <span class="text-secondary fw-bold">
                                        {{ $row->teacher->first_name ?? 'N/A' }} {{ $row->teacher->last_name ?? '' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('teacher.assign.edit', $row->id) }}" class="btn btn-sm btn-outline-primary"><i class="bx bxs-edit"></i></a>
                                    @can('manage-teacher-assignment')
                                    <form action="{{ route('teacher.assign.delete', $row->id) }}" method="POST" class="delete-form" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-btn">
                                            <i class="bx bxs-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // --- 1. TOASTR CONFIGURATION ---
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000"
        };

        @if($errors->any())
            let errorHtml = "";
            @foreach($errors->all() as $error)
                errorHtml += "• {{ $error }}<br>";
            @endforeach
            toastr.error(errorHtml, 'Validation Error!');
        @endif

        @if(session('success'))
            toastr.success("{{ session('success') }}", 'Success!');
        @endif

        @if(session('error'))
            toastr.error("{{ session('error') }}", 'Error!');
        @endif

        // --- 2. DATATABLE INITIALIZATION ---
        $('#assignmentTable').DataTable({
            "pageLength": 10,
            "ordering": true
        });

        // --- 3. FUNCTION: LOAD SECTIONS ---
        function loadSections(classId, selectedSectionId = null) {
            var sectionDropdown = $('#section_id');
            sectionDropdown.html('<option value="">Loading...</option>');

            if (classId) {
                $.ajax({
                    url: "{{ route('teacher.assign.get-sections-by-class') }}",
                    type: "GET",
                    data: {
                        class_id: classId
                    },
                    dataType: "json",
                    success: function(data) {
                        sectionDropdown.html('<option value="">Select Section</option>');
                        $.each(data, function(key, value) {
                            var selected = (selectedSectionId == value.id) ? 'selected' : '';
                            sectionDropdown.append('<option value="' + value.id + '" ' + selected + '>' + value.name + '</option>');
                        });
                    },
                    error: function() {
                        toastr.error("Could not load sections.");
                        sectionDropdown.html('<option value="">Select Section</option>');
                    }
                });
            } else {
                sectionDropdown.html('<option value="">Select Section</option>');
            }
        }

        // --- 4. FUNCTION: LOAD SUBJECTS ---
        function loadSubjects(classId, selectedSubjectId = null) {
    var subjectDropdown = $('#class_subject_id');
    var subjectWrapper = subjectDropdown.closest('.mb-3'); // poora field block

    if (classId) {
        $.ajax({
            url: "{{ route('teacher.assign.get-subjects-by-class') }}",
            type: "GET",
            data: { class_id: classId },
            dataType: "json",
            success: function(response) {
                if (!response.has_subjects) {
                    // Whole-class: dropdown hide + force "no subject"
                    subjectDropdown.html('<option value="">Whole Class (No specific subject)</option>');
                    subjectDropdown.val('').prop('required', false);
                    subjectWrapper.hide();
                } else {
                    // Subject-wise: dropdown show + required
                    subjectWrapper.show();
                    subjectDropdown.html('<option value="">Select Subject</option>');
                    $.each(response.subjects, function(key, value) {
                        var selected = (selectedSubjectId == value.id) ? 'selected' : '';
                        subjectDropdown.append('<option value="' + value.id + '" ' + selected + '>' + value.name + '</option>');
                    });
                    subjectDropdown.prop('required', true);
                }
            },
            error: function() {
                subjectDropdown.html('<option value="">Whole Class (No specific subject)</option>');
            }
        });
    } else {
        subjectWrapper.hide();
        subjectDropdown.html('<option value="">Whole Class (No specific subject)</option>');
    }
}

        // --- 5. EVENT: ON CLASS CHANGE ---
        $('#class_id').on('change', function() {
            var classId = $(this).val();
            loadSections(classId);
            loadSubjects(classId);
        });

        // --- 6. EDIT MODE: AUTO LOAD ---
        @if(isset($editData))
            loadSections("{{ $editData->class_id }}", "{{ $editData->section_id }}");
            loadSubjects("{{ $editData->class_id }}", "{{ $editData->class_subject_id }}");
        @endif

        // --- 7. SWEETALERT DELETE CONFIRMATION ---
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');

            Swal.fire({
                title: 'Are you sure?',
                text: "This record will be deleted permanently!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush