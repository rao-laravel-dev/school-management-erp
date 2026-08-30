@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Examination', 'url' => '#'],
    ['label' => 'Print Marksheet', 'url' => route('print_marksheet.index')],
]" />

{{-- Criteria Card --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
        <h5 class="fw-semi-bold text-primary mb-0"><i class='bx bx-printer me-1'></i> Print Marksheet</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Exam <span class="text-danger">*</span></label>
                <select id="exam_id" class="form-select form-select-sm">
                    <option value="">Select Exam</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Class <span class="text-danger">*</span></label>
                <select id="class_id" class="form-select form-select-sm">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Section <span class="text-danger">*</span></label>
                <select id="section_id" class="form-select form-select-sm" disabled>
                    <option value="">Select Class First</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Marksheet Template <span class="text-danger">*</span></label>
                <select id="template_id" class="form-select form-select-sm">
                    <option value="">Select Template</option>
                    @foreach($templates as $template)
                    <option value="{{ $template->id }}">{{ $template->template_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="card-footer bg-transparent text-end">
        <button type="button" class="btn btn-sm btn-primary px-4" id="searchBtn">
            <i class='bx bx-search'></i> Search
        </button>
    </div>
</div>

{{-- Results Card — appears only after Search is clicked --}}
<div class="card mt-3 d-none" id="studentsCard">
    <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
        <div class="form-check mb-0">
            <input type="checkbox" id="checkAll" class="form-check-input">
            <label class="form-check-label" for="checkAll">Select All</label>
        </div>
        <button type="button" class="btn btn-sm btn-success px-3" id="generateBtn">
            <i class='bx bx-printer'></i> Generate &amp; Print
        </button>
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="studentsTable">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>Admission No</th>
                    <th>Student Name</th>
                    <th>Roll Number</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>



@endsection

@push('scripts')
<script>
    $(function() {

        function studentRowsEmptyState() {
            return `<tr><td colspan="4">
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-0">
                    <i class='bx bx-error-circle'></i> No published results found for this selection.
                </div>
            </td></tr>`;
        }

        // ===== Class -> Sections =====
        $('#class_id').on('change', function() {
            let classId = $(this).val();
            let $section = $('#section_id');

            $section.html('<option value="">Loading...</option>').prop('disabled', true);

            if (!classId) {
                $section.html('<option value="">Select Class First</option>');
                return;
            }

            $.get("{{ url('print-marksheet/get-sections') }}/" + classId, function(sections) {
                let options = '<option value="">Select Section</option>';
                sections.forEach(s => options += `<option value="${s.id}">${s.name}</option>`);
                $section.html(options).prop('disabled', false);
            }).fail(function() {
                toastr.error('Could not load sections.');
                $section.html('<option value="">Select Section</option>').prop('disabled', false);
            });
        });

        // ===== Search: load students who already have a result for this exam =====
        $('#searchBtn').on('click', function() {
            let examId = $('#exam_id').val();
            let classId = $('#class_id').val();
            let sectionId = $('#section_id').val();
            let templateId = $('#template_id').val();

            if (!examId || !classId || !sectionId || !templateId) {
                toastr.error('Please select Exam, Class, Section and Template.');
                return;
            }

            let $btn = $(this).prop('disabled', true);
            let originalHtml = $btn.html();
            $btn.html(`<span class="spinner-border spinner-border-sm"></span> Searching...`);

            $.get(`{{ url('print-marksheet/get-students') }}/${examId}/${classId}/${sectionId}`)
                .done(function(students) {
                    let rows = '';
                    if (students.length === 0) {
                        rows = studentRowsEmptyState();
                    } else {
                        students.forEach(s => {
                            rows += `<tr>
                                <td><input type="checkbox" class="form-check-input student-check" value="${s.enrollment_id}"></td>
                                <td>${s.admission_no}</td>
                                <td>${s.name}</td>
                                <td>${s.roll_number}</td>
                            </tr>`;
                        });
                    }
                    $('#checkAll').prop('checked', false);
                    $('#studentsTable tbody').html(rows);
                    $('#studentsCard').removeClass('d-none');
                })
                .fail(function() {
                    toastr.error('Could not load students.');
                })
                .always(function() {
                    $btn.prop('disabled', false).html(originalHtml);
                });
        });

        // ===== Select All header checkbox -> all rows =====
        $(document).on('change', '#checkAll', function() {
            $('.student-check').prop('checked', $(this).is(':checked'));
        });

        // ===== Individual checkbox -> keep header checkbox in sync =====
        $(document).on('change', '.student-check', function() {
            let total = $('.student-check').length;
            let checked = $('.student-check:checked').length;
            $('#checkAll').prop('checked', total > 0 && total === checked);
        });

        // ===== Generate: submit selected enrollment_ids and open print view in a new tab =====
        $('#generateBtn').on('click', function() {
            let enrollmentIds = $('.student-check:checked').map(function() {
                return $(this).val();
            }).get();

            if (enrollmentIds.length === 0) {
                toastr.error('Please select at least one student.');
                return;
            }

            // Build a temporary form so the print view opens in a new tab (POST, not AJAX)
            let $form = $('<form>', {
                action: "{{ route('print_marksheet.generate') }}",
                method: 'POST',
                target: '_blank'
            });

            $form.append($('<input>', { type: 'hidden', name: '_token', value: "{{ csrf_token() }}" }));
            $form.append($('<input>', { type: 'hidden', name: 'exam_id', value: $('#exam_id').val() }));
            $form.append($('<input>', { type: 'hidden', name: 'class_id', value: $('#class_id').val() }));
            $form.append($('<input>', { type: 'hidden', name: 'section_id', value: $('#section_id').val() }));
            $form.append($('<input>', { type: 'hidden', name: 'template_id', value: $('#template_id').val() }));

            enrollmentIds.forEach(id => {
                $form.append($('<input>', { type: 'hidden', name: 'enrollment_ids[]', value: id }));
            });

            $('body').append($form);
            $form.submit();
            $form.remove();
        });

    });
</script>
@endpush