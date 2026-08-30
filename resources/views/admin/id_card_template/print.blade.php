@extends($current_layout)
@section('content')
<x-breadcrumb :items="[
    ['label' => 'ID Card', 'url' => '#'],
    ['label' => 'Print ID Card', 'url' => route('print_id_card.index')],
]" />

<div class="card">
    <div class="card-header">
        <h5 class="fw-semi-bold text-primary mb-0">Select Criteria</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                <select id="class_id" class="form-select form-select-sm">
                    <option value="">Select</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="section_id" class="form-label">Section <span class="text-danger">*</span></label>
                <select id="section_id" class="form-select form-select-sm">
                    <option value="">Select Class First</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="template_id" class="form-label">ID Card Template <span class="text-danger">*</span></label>
                <select id="template_id" class="form-select form-select-sm">
                    <option value="">Select</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" id="loadStudentsBtn" class="btn btn-sm btn-primary w-100">
                    <i class="bx bx-search"></i> Show Students
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3" id="studentListWrapper" style="display:none;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" id="selectAllStudents">
            <label class="form-check-label" for="selectAllStudents">Select All</label>
        </div>
        <button type="button" id="printSelectedBtn" class="btn btn-sm btn-success">
            <i class="bx bx-printer"></i> Print Selected Cards
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead>
                    <tr>
                        <th width="40"></th>
                        <th width="50">Photo</th>
                        <th>Admission No</th>
                        <th>Name</th>
                        <th>Roll No</th>
                    </tr>
                </thead>
                <tbody id="studentListBody"></tbody>
            </table>
        </div>
        <div id="noStudentsMsg" class="alert alert-warning d-none mb-0">
            <i class="bx bx-error-circle"></i> No students found in this class/section.
        </div>
    </div>
</div>

{{-- Hidden form -> submits selected enrollment IDs -> card.blade.php opens in new tab --}}
<form id="generateCardsForm" action="{{ route('print_id_card.generate') }}" method="POST" target="_blank" style="display:none;">
    @csrf
    <input type="hidden" name="template_id" id="hiddenTemplateId">
    <div id="enrollmentIdsContainer"></div>
</form>
@endsection

@push('scripts')
<script>
$(function() {

    $('#class_id').on('change', function() {
        const classId = $(this).val();
        $('#section_id').html('<option value="">Loading...</option>');
        $('#studentListWrapper').hide();

        if (!classId) {
            $('#section_id').html('<option value="">Select Class First</option>');
            return;
        }
        $.get('/print-id-card/get-sections/' + classId, function(data) {
            let options = '<option value="">Select</option>';
            data.forEach(function(s) {
                options += `<option value="${s.id}">${s.name}</option>`;
            });
            $('#section_id').html(options);
        });
    });

    $('#section_id, #template_id').on('change', function() {
        $('#studentListWrapper').hide();
    });

    $('#loadStudentsBtn').on('click', function() {
        const classId    = $('#class_id').val();
        const sectionId  = $('#section_id').val();
        const templateId = $('#template_id').val();

        if (!classId || !sectionId || !templateId) {
            toastr.error('Class, Section aur Template teeno select karein.');
            return;
        }

        $.get('/print-id-card/get-students/' + classId + '/' + sectionId, function(data) {
            $('#studentListBody').empty();
            $('#selectAllStudents').prop('checked', false);

            if (data.length === 0) {
                $('#noStudentsMsg').removeClass('d-none');
                $('#studentListWrapper').show();
                return;
            }
            $('#noStudentsMsg').addClass('d-none');

            let rows = '';
            data.forEach(function(s) {
                rows += `
                    <tr>
                        <td><input type="checkbox" class="form-check-input studentCheckbox" value="${s.enrollment_id}"></td>
                        <td><img src="${s.photo}" width="35" height="35" style="object-fit:cover;border-radius:4px;"></td>
                        <td>${s.admission_no}</td>
                        <td>${s.name}</td>
                        <td>${s.roll_no}</td>
                    </tr>`;
            });
            $('#studentListBody').html(rows);
            $('#studentListWrapper').show();
        });
    });

    $('#selectAllStudents').on('change', function() {
        $('.studentCheckbox').prop('checked', $(this).is(':checked'));
    });

    $('#printSelectedBtn').on('click', function() {
        const selected = $('.studentCheckbox:checked').map(function() { return this.value; }).get();

        if (selected.length === 0) {
            toastr.error('Kam az kam ek student select karein.');
            return;
        }

        $('#hiddenTemplateId').val($('#template_id').val());

        const container = $('#enrollmentIdsContainer');
        container.empty();
        selected.forEach(function(id) {
            container.append(`<input type="hidden" name="enrollment_ids[]" value="${id}">`);
        });

        $('#generateCardsForm').submit();
    });

});
</script>
@endpush