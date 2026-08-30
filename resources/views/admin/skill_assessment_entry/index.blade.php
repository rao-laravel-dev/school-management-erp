@extends($current_layout)

@section('title', 'Skill Assessment Entry')

@section('content')
<div class="content-wrapper">
    <x-breadcrumb :items="[
        ['label' => 'Examination', 'url' => '#'],
        ['label' => 'Skill Assessment Entry', 'url' => route('skill_assessment_entry.index')],
    ]" />

    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">Select Criteria</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 mb-2">
                            <label for="exam_id">Exam</label>
                            <select id="exam_id" class="form-control form-control-sm">
                                <option value="">-- Exam --</option>
                                @foreach($exams as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="exam_id_error"></div>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="class_id">Class</label>
                            <select id="class_id" class="form-control form-control-sm">
                                <option value="">-- Class --</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="class_id_error"></div>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="section_id">Section</label>
                            <select id="section_id" class="form-control form-control-sm">
                                <option value="">-- Section --</option>
                            </select>
                            <div class="invalid-feedback" id="section_id_error"></div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="area_id">Skill Area</label>
                            <select id="area_id" class="form-control form-control-sm">
                                <option value="">-- Area --</option>
                                @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->category->name }} - {{ $area->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="area_id_error"></div>
                        </div>
                        <div class="col-md-2 mb-2 d-flex align-items-end">
                            <button type="button" class="btn btn-success btn-sm w-100" id="searchBtn">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" id="entryCard" style="display:none;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Student List</span>
                    <button type="button" class="btn btn-success btn-sm" id="saveBtn">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="entryTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th style="width:200px;">Grade</th>
                                <th>Remark</th>
                            </tr>
                        </thead>
                        <tbody id="entryTbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentGrades = [];

    $(function() {
        @if($errors->any())
        toastr.error("{{ $errors->first() }}");
        @endif
    });

    function initEntryTable() {
        if ($.fn.DataTable.isDataTable('#entryTable')) {
            $('#entryTable').DataTable().destroy();
        }

        $('#entryTable').DataTable({
            paging: true,
            lengthChange: true,
            searching: true,
            ordering: false,
            info: true,
            columnDefs: [{
                orderable: false,
                targets: [3, 4]
            }],
            language: {
    emptyTable: '<div class="alert alert-danger text-center mb-0"><i class="bx bxs-error-circle me-1"></i>No data available in the table</div>'
}
        });
    }

    // Class change par section load karna
    $('#class_id').on('change', function() {
        let classId = $(this).val();
        $('#section_id').html('<option value="">-- Section --</option>');
        if (!classId) return;

        $.get("{{ route('skill_assessment_entry.get_sections') }}", {
            class_id: classId
        }, function(res) {
            res.forEach(function(s) {
                $('#section_id').append(`<option value="${s.id}">${s.name}</option>`);
            });
        }).fail(function(xhr) {
            console.error('get_sections failed:', xhr);
            toastr.error('Unable to load sections.');
        });
    });

    // Dropdown change hone par error remove karna
    $('#exam_id, #class_id, #section_id, #area_id').on('change', function() {
        $(this).removeClass('is-invalid');
        $('#' + $(this).attr('id') + '_error').text('');
    });

    // Search button click handler
    $('#searchBtn').on('click', function() {
        let examId = $('#exam_id').val();
        let classId = $('#class_id').val();
        let sectionId = $('#section_id').val();
        let areaId = $('#area_id').val();

        // Reset previous validation state
        $('#exam_id, #class_id, #section_id, #area_id').removeClass('is-invalid');
        $('#exam_id_error, #class_id_error, #section_id_error, #area_id_error').text('');

        let hasError = false;

        if (!examId) {
            $('#exam_id').addClass('is-invalid');
            $('#exam_id_error').text('Please select an exam.');
            hasError = true;
        }
        if (!classId) {
            $('#class_id').addClass('is-invalid');
            $('#class_id_error').text('Please select a class.');
            hasError = true;
        }
        if (!sectionId) {
            $('#section_id').addClass('is-invalid');
            $('#section_id_error').text('Please select a section.');
            hasError = true;
        }
        if (!areaId) {
            $('#area_id').addClass('is-invalid');
            $('#area_id_error').text('Please select a skill area.');
            hasError = true;
        }

        if (hasError) {
            toastr.error('Please fill all required fields.');
            return;
        }

        let gradesRequest = $.get("{{ route('skill_assessment_entry.get_grades') }}", {
            exam_id: examId
        }).fail(function(xhr) {
            console.error('get_grades failed:', xhr);
        });

        let studentsRequest = $.get("{{ route('skill_assessment_entry.get_students') }}", {
            exam_id: examId,
            class_id: classId,
            section_id: sectionId,
            area_id: areaId
        }).fail(function(xhr) {
            console.error('get_students failed:', xhr);
            toastr.error('Unable to load students. Check console for details.');
        });

        $.when(gradesRequest, studentsRequest).done(function(gradesRes, studentsRes) {
            currentGrades = gradesRes[0] || [];
            let students = studentsRes[0] || [];

            let gradeOptions = '<option value="">-- Grade --</option>';
            currentGrades.forEach(function(g) {
                gradeOptions += `<option value="${g.id}">${g.grade_name}</option>`;
            });

            let rows = '';
            students.forEach(function(s, index) {
                let rowGrades = gradeOptions;
                if (s.grade_id) {
                    rowGrades = gradeOptions.replace(
                        `value="${s.grade_id}"`,
                        `value="${s.grade_id}" selected`
                    );
                }
                rows += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${s.roll_no}</td>
                    <td>${s.student_name}
                        <input type="hidden" class="enrollment_id" value="${s.enrollment_id}">
                    </td>
                    <td><select class="form-control form-control-sm grade_id">${rowGrades}</select></td>
                    <td><input type="text" class="form-control form-control-sm remark" value="${s.remark ?? ''}"></td>
                </tr>`;
            });

            $('#entryTbody').html(rows);
            $('#entryCard').show();

            initEntryTable();
        }).fail(function() {
            toastr.error('Something went wrong loading the entry screen. Check browser console (F12) for details.');
        });
    });

    // Save button click handler
    $('#saveBtn').on('click', function() {
        let examId = $('#exam_id').val();
        let areaId = $('#area_id').val();
        let entries = [];

        $('#entryTbody tr').each(function() {
            entries.push({
                enrollment_id: $(this).find('.enrollment_id').val(),
                grade_id: $(this).find('.grade_id').val() || null,
                remark: $(this).find('.remark').val() || null,
            });
        });

        $('#saveBtn').prop('disabled', true);

        $.ajax({
            url: "{{ route('skill_assessment_entry.save') }}",
            method: 'POST',
            dataType: 'json',
            data: {
                _token: '{{ csrf_token() }}',
                exam_id: examId,
                area_id: areaId,
                entries: entries
            },
            success: function(res) {
                toastr.success(res.message);
            },
            error: function(xhr) {
                toastr.error('Something went wrong while saving.');
                console.error(xhr);
            },
            complete: function() {
                $('#saveBtn').prop('disabled', false);
            }
        });
    });
</script>
@endpush