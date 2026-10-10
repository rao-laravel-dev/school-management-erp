@extends($current_layout)

@section('content')

<x-breadcrumb :items="[
        ['label' => 'Examination', 'url' => '#'],
        ['label' => 'Print Report Card', 'url' => route('print_report_card.index')],
    ]" />

<div class="card">
    <div class="card-header">
        <h5 class="fw-semi-bold text-primary mb-0">Print Report Card</h5>
    </div>
    <div class="card-body">

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label" for="exam_id">Exam <span class="text-danger">*</span></label>
                <select id="exam_id" class="form-select form-select-sm">
                    <option value="">Select Exam</option>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label" for="class_id">Class <span class="text-danger">*</span></label>
                <select id="class_id" class="form-select form-select-sm">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label" for="section_id">Section <span class="text-danger">*</span></label>
                <select id="section_id" class="form-select form-select-sm" disabled>
                    <option value="">Select Class First</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label" for="template_id">Report Card Template <span class="text-danger">*</span></label>
                <select id="template_id" class="form-select form-select-sm">
                    <option value="">Select Template</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->template_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="studentsWrapper" class="d-none">
            <hr>
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAll">
                    <label class="form-check-label" for="selectAll">Select All</label>
                </div>
                <button type="button" class="btn btn-primary btn-sm px-3" id="printBtn" disabled>
                    <i class="bx bx-printer"></i> Generate &amp; Print
                </button>
            </div>

            <table id="studentsTable" class="table table-sm table-bordered w-100">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="headCheck" disabled></th>
                        <th>Roll No</th>
                        <th>Name</th>
                        <th>Father Name</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div id="emptyState" class="text-center text-muted py-4 d-none">
            <i class="bx bx-info-circle"></i> No students found with a result for this exam.
        </div>

    </div>
</div>

{{-- hidden form used to submit selected enrollment_ids + open print view in a new tab --}}
<form id="printForm" action="{{ route('print_report_card.generate') }}" method="POST" target="_blank">
    @csrf
    <input type="hidden" name="exam_id" id="form_exam_id">
    <input type="hidden" name="class_id" id="form_class_id">
    <input type="hidden" name="template_id" id="form_template_id">
    <div id="enrollmentIdsWrapper"></div>
</form>

@endsection

@push('scripts')
<script>
    $(function () {

        function resetStudents() {
            $('#studentsWrapper').addClass('d-none');
            $('#emptyState').addClass('d-none');
            $('#studentsTable tbody').empty();
            $('#printBtn').prop('disabled', true);
        }

        // ---------- Class -> Sections ----------
        $('#class_id').on('change', function () {
            const classId = $(this).val();
            resetStudents();

            const $section = $('#section_id');
            $section.prop('disabled', true).html('<option value="">Loading...</option>');

            if (!classId) {
                $section.html('<option value="">Select Class First</option>');
                return;
            }

            $.get(`{{ url('print-report-card/get-sections') }}/${classId}`, function (sections) {
                let options = '<option value="">Select Section</option>';
                sections.forEach(s => options += `<option value="${s.id}">${s.name}</option>`);
                $section.html(options).prop('disabled', false);
            }).fail(function () {
                toastr.error('Unable to load sections.');
            });
        });

        // ---------- Exam/Class/Section -> Students ----------
        function loadStudents() {
            const examId = $('#exam_id').val();
            const classId = $('#class_id').val();
            const sectionId = $('#section_id').val();

            resetStudents();
            if (!examId || !classId || !sectionId) return;

            $.post("{{ route('print_report_card.get_students') }}", {
                _token: "{{ csrf_token() }}",
                exam_id: examId,
                class_id: classId,
                section_id: sectionId,
            }, function (students) {
                if (!students.length) {
                    $('#emptyState').removeClass('d-none');
                    return;
                }

                let rows = '';
                students.forEach(s => {
                    rows += `<tr>
                        <td><input type="checkbox" class="student-check" value="${s.id}"></td>
                        <td>${s.roll_no || '-'}</td>
                        <td>${s.name || '-'}</td>
                        <td>${s.father_name || '-'}</td>
                    </tr>`;
                });

                $('#studentsTable tbody').html(rows);
                $('#studentsWrapper').removeClass('d-none');
                $('#headCheck').prop('disabled', false).prop('checked', false);
            }).fail(function () {
                toastr.error('Unable to load students.');
            });
        }

        $('#exam_id, #section_id').on('change', loadStudents);

        // ---------- Select all ----------
        $(document).on('change', '#selectAll, #headCheck', function () {
            const checked = $(this).is(':checked');
            $('.student-check').prop('checked', checked);
            $('#selectAll, #headCheck').prop('checked', checked);
            togglePrintBtn();
        });

        $(document).on('change', '.student-check', togglePrintBtn);

        function togglePrintBtn() {
            $('#printBtn').prop('disabled', $('.student-check:checked').length === 0);
        }

        // ---------- Generate & Print ----------
        $('#printBtn').on('click', function () {
            const templateId = $('#template_id').val();
            if (!templateId) {
                toastr.error('Please select a Report Card Template.');
                return;
            }

            $('#form_exam_id').val($('#exam_id').val());
            $('#form_class_id').val($('#class_id').val());
            $('#form_template_id').val(templateId);

            let inputs = '';
            $('.student-check:checked').each(function () {
                inputs += `<input type="hidden" name="enrollment_ids[]" value="${$(this).val()}">`;
            });
            $('#enrollmentIdsWrapper').html(inputs);

            $('#printForm').submit();
        });

    });
</script>
@endpush