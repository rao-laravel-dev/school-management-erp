@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Examination', 'url' => '#'],
    ['label' => 'Result', 'url' => route('result.index')],
]" />

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
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
            <div class="col-md-4">
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
            <div class="col-md-4">
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
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semi-bold text-primary">Result</h5>
        @if($selectedExamId && $selectedClassId && $selectedSectionId)
        <button type="button" id="calculateBtn" class="btn btn-sm btn-success">
            <i class='bx bx-calculator'></i> Calculate Result
        </button>
        @endif
    </div>
    <div class="card-body">

        @if(!$selectedExamId || !$selectedClassId || !$selectedSectionId)
        <div class="alert alert-info mb-0">Please select Exam, Class and Section above to view/calculate results.</div>

        @else
        <div class="table-responsive">
            <table id="resultTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Position</th>
                        <th>Roll No</th>
                        <th>Student Name</th>
                        <th>Total Marks</th>
                        <th>Obtained Marks</th>
                        <th>Percentage</th>
                        <th>Grade</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="resultTableBody">
                    @forelse($results as $result)
                    <tr>
                        <td><span class="badge bg-warning-subtle text-warning">{{ $result->position }}</span></td>
                        <td>{{ $result->enrollment->roll_no ?? '-' }}</td>
                        <td>{{ trim(($result->enrollment->student->first_name ?? '') . ' ' . ($result->enrollment->student->last_name ?? '')) }}</td>
                        <td>{{ $result->total_marks }}</td>
                        <td>{{ $result->obtained_marks }}</td>
                        <td>{{ $result->percentage }}%</td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary">{{ $result->grade->grade_name ?? '-' }}</span>
                        </td>
                        <td>
                            @if($result->status == 'Pass')
                            <span class="badge bg-success-subtle text-success">Pass</span>
                            @else
                            <span class="badge bg-danger-subtle text-danger">Fail</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="alert alert-warning d-flex align-items-center justify-content-center text-center mb-0" role="alert">
                                <i class='bx bx-error me-2'></i>
                                <span>No results calculated yet. Click "Calculate Result" above.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {

        @if($selectedExamId && $selectedClassId && $selectedSectionId && count($results) > 0)
        $('#resultTable').DataTable({
            language: {
                emptyTable: `<div class="alert alert-danger d-flex align-items-center justify-content-center text-center mb-0" role="alert"><i class='bx bx-error-circle me-2'></i><span>No data available in the table</span></div>`
            }
        });
        @endif

        function reloadWithFilters() {
            let examId = $('#filter_exam_id').val();
            let classId = $('#filter_class_id').val();
            let sectionId = $('#filter_section_id').val();

            if (examId && classId && sectionId) {
                window.location = "{{ route('result.index') }}?exam_id=" + examId +
                    "&class_id=" + classId + "&section_id=" + sectionId;
            }
        }

        $('#filter_class_id').on('change', function() {
            let classId = $(this).val();
            let $section = $('#filter_section_id');
            $section.html('<option value="">Loading...</option>');

            if (!classId) {
                $section.html('<option value="">Select Section</option>');
                return;
            }

            let url = "{{ route('result.get_sections', ['classId' => ':classId']) }}".replace(':classId', classId);
            $.get(url, function(data) {
                let options = '<option value="">Select Section</option>';
                $.each(data, function(i, s) {
                    options += `<option value="${s.id}">${s.name}</option>`;
                });
                $section.html(options);
            });
        });

        $('#filter_exam_id, #filter_section_id').on('change', reloadWithFilters);

        // ===== Calculate Result =====
        $('#calculateBtn').on('click', function() {
            let $btn = $(this);
            $btn.prop('disabled', true).html("<i class='bx bx-loader bx-spin'></i> Calculating...");

            $.ajax({
                url: "{{ route('result.calculate') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    exam_id: "{{ $selectedExamId }}",
                    class_id: "{{ $selectedClassId }}",
                    section_id: "{{ $selectedSectionId }}",
                },
                success: function(res) {
                    toastr.success(res.message);
                    location.reload();
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || 'Something went wrong!';
                    toastr.error(message);
                    $btn.prop('disabled', false).html("<i class='bx bx-calculator'></i> Calculate Result");
                }
            });
        });

    });
</script>
@endpush