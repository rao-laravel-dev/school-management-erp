@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Examination', 'url' => '#'],
    ['label' => 'MarkSheet', 'url' => route('marksheet.index')],
]" />

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Exam</label>
                <select id="filter_exam_id" class="form-select form-select-sm">
                    <option value="">Select Exam</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ $selectedExamId == $exam->id ? 'selected' : '' }}>
                        {{ $exam->name }} ({{ $exam->examType->name ?? '-' }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Class</label>
                <select id="filter_class_id" class="form-select form-select-sm">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Section</label>
                <select id="filter_section_id" class="form-select form-select-sm">
                    <option value="">Select Section</option>
                    @foreach($sections as $section)
                    <option value="{{ $section->id }}" {{ $selectedSectionId == $section->id ? 'selected' : '' }}>
                        {{ $section->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Subject</label>
                <select id="filter_subject_id" class="form-select form-select-sm">
                    <option value="">Select Subject</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject['id'] }}" {{ $selectedSubjectId == $subject['id'] ? 'selected' : '' }}>
                        {{ $subject['name'] }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semi-bold text-primary">MarkSheet Entry</h5>
        @if($schedule)
        <span class="badge bg-info-subtle text-info">Max Marks: {{ $schedule->max_marks }} | Passing: {{ $schedule->passing_marks }}</span>
        @endif
    </div>
    <div class="card-body">

        @if(!$selectedExamId || !$selectedClassId || !$selectedSectionId || !$selectedSubjectId)
        <div class="alert alert-info mb-0">Please select Exam, Class, Section and Subject above to enter marks.</div>

        @elseif(!$hasAccess)
        <div class="alert alert-danger d-flex align-items-center mb-0">
            <i class='bx bx-error-circle me-2'></i>
            <span>You are not assigned to teach this Class/Section/Subject.</span>
        </div>

        @elseif(!$schedule)
        <div class="alert alert-warning d-flex align-items-center mb-0">
            <i class='bx bx-error me-2'></i>
            <span>No Exam Schedule found for this Exam+Class+Subject. Please add it first from Exam Schedule.</span>
        </div>

        @else

        @if($isLocked)
        <div class="alert alert-warning d-flex align-items-center mb-3">
            <i class='bx bx-lock-alt me-2'></i>
            <span>This exam's result is published. Marks are locked for editing.</span>
        </div>
        @endif

        <form id="markSheetForm">
            @csrf
            <input type="hidden" name="exam_id" value="{{ $selectedExamId }}">
            <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
            <input type="hidden" name="section_id" value="{{ $selectedSectionId }}">
            <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">

            <div class="table-responsive">
                <table class="table table-bordered table-sm" id="markSheetTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Marks Obtained (out of {{ $schedule->max_marks }})</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $index => $student)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $student['roll_no'] }}</span></td>
                            <td>{{ $student['name'] }}</td>
                            <td>
                                <input type="number" step="0.01" min="0" max="{{ $schedule->max_marks }}"
                                    class="form-control form-control-sm marks-input"
                                    name="marks[{{ $student['enrollment_id'] }}]"
                                    value="{{ $student['marks_obtained'] }}"
                                    {{ $isLocked ? 'disabled' : '' }}>
                            </td>
                            <td>
                                <input type="text" maxlength="255"
                                    class="form-control form-control-sm"
                                    name="remarks[{{ $student['enrollment_id'] }}]"
                                    value="{{ $student['remark'] }}"
                                    placeholder="e.g. Distinction"
                                    {{ $isLocked ? 'disabled' : '' }}>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">
                                <div class="alert alert-warning d-flex align-items-center justify-content-center text-center mb-0" role="alert">
                                    <i class='bx bx-error me-2'></i>
                                    <span>No active students found in this Class/Section.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(!$isLocked && $students->count() > 0)
            <button type="submit" class="btn btn-success btn-sm">
                <i class='bx bx-save'></i> Save Marks
            </button>
            @endif
        </form>

        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {

        @if($errors->any())
        toastr.error("{{ $errors->first() }}");
        @endif

        function reloadWithFilters() {
            let examId = $('#filter_exam_id').val();
            let classId = $('#filter_class_id').val();
            let sectionId = $('#filter_section_id').val();
            let subjectId = $('#filter_subject_id').val();

            if (examId && classId && sectionId && subjectId) {
                window.location = "{{ route('marksheet.index') }}?exam_id=" + examId +
                    "&class_id=" + classId + "&section_id=" + sectionId + "&subject_id=" + subjectId;
            }
        }

        // ===== Class change: load Sections + Subjects =====
        $('#filter_class_id').on('change', function() {
            let classId = $(this).val();
            let $section = $('#filter_section_id');
            let $subject = $('#filter_subject_id');

            $section.html('<option value="">Loading...</option>');
            $subject.html('<option value="">Loading...</option>');

            if (!classId) {
                $section.html('<option value="">Select Section</option>');
                $subject.html('<option value="">Select Subject</option>');
                return;
            }

            let sectionsUrl = "{{ route('marksheet.get_sections', ['classId' => ':classId']) }}".replace(':classId', classId);
            let subjectsUrl = "{{ route('marksheet.get_subjects', ['classId' => ':classId']) }}".replace(':classId', classId);

            $.get(sectionsUrl, function(data) {
                let options = '<option value="">Select Section</option>';
                $.each(data, function(i, s) {
                    options += `<option value="${s.id}">${s.name}</option>`;
                });
                $section.html(options);
            });

            $.get(subjectsUrl, function(data) {
                let options = '<option value="">Select Subject</option>';
                $.each(data, function(i, s) {
                    options += `<option value="${s.id}">${s.name}</option>`;
                });
                $subject.html(options);
            });
        });

        $('#filter_section_id, #filter_subject_id').on('change', reloadWithFilters);
        $('#filter_exam_id').on('change', reloadWithFilters);

        // ===== Save marks =====
        $('#markSheetForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('marksheet.save') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    toastr.success(res.message);
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || 'Something went wrong!';
                    toastr.error(message);
                }
            });
        });

    });
</script>
@endpush