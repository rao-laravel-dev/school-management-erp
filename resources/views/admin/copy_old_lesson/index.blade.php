@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Lesson Plan', 'url' => '#'],
    ['label' => 'Copy Old Lessons', 'url' => route('copy_old_lesson.index')],
    ['label' => 'Old Lesson', 'url' => '#'],
]" />

<div class="container-fluid">

    {{-- Select Criteria Card (Source only) --}}
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0 fw-semi-bold text-primary">Select Old Session Details</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                    <select id="source_academic_year_id" class="form-select form-select-sm">
                        <option value="">-- Select --</option>
                        @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">Please select Academic Year.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Class <span class="text-danger">*</span></label>
                    <select id="source_class_id" class="form-select form-select-sm">
                        <option value="">-- Select --</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">Please select Class.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Section <span class="text-danger">*</span></label>
                    <select id="source_section_id" class="form-select form-select-sm" disabled>
                        <option value="">-- Select Class First --</option>
                    </select>
                    <div class="invalid-feedback">Please select Section.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                    <select id="source_subject_id" class="form-select form-select-sm" disabled>
                        <option value="">-- Select Class First --</option>
                    </select>
                    <div class="invalid-feedback">Please select Subject.</div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button id="searchBtn" class="btn btn-primary btn-sm">
                <i class='bx bx-search'></i> Search
            </button>
        </div>
    </div>

    {{-- Results Wrapper --}}
    <div id="resultWrapper"></div>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {

    // ===== Show toastr + is-invalid from server-side (422) validation errors =====
    function showServerErrors(xhr, fieldMap) {
        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            const errors = xhr.responseJSON.errors;
            Object.keys(errors).forEach(field => {
                toastr.error(errors[field][0]);
                const domId = fieldMap[field];
                if (domId) {
                    $('#' + domId).addClass('is-invalid');
                }
            });
        } else {
            toastr.error('Something went wrong.');
        }
    }

    function loadSections(classId, targetSelectId) {
        const $select = $('#' + targetSelectId);
        $select.prop('disabled', true).html('<option value="">Loading...</option>');

        if (!classId) {
            $select.html('<option value="">-- Select Class First --</option>');
            return;
        }

        $.get(`{{ url('copy_old_lesson/get-sections') }}/${classId}`, function(res) {
            let opts = '<option value="">-- Select --</option>';
            res.data.forEach(s => opts += `<option value="${s.id}">${s.name}</option>`);
            $select.html(opts).prop('disabled', false);
        });
    }

    function loadSubjects(classId, targetSelectId) {
        const $select = $('#' + targetSelectId);
        $select.prop('disabled', true).html('<option value="">Loading...</option>');

        if (!classId) {
            $select.html('<option value="">-- Select Class First --</option>');
            return;
        }

        $.get(`{{ url('copy_old_lesson/get-subjects') }}/${classId}`, function(res) {
            let opts = '<option value="">-- Select --</option>';
            res.data.forEach(s => opts += `<option value="${s.id}">${s.name}</option>`);
            $select.html(opts).prop('disabled', false);
        });
    }

    $('#source_class_id').on('change', function() {
        loadSections($(this).val(), 'source_section_id');
        loadSubjects($(this).val(), 'source_subject_id');
    });

    $(document).on('change', '#target_class_id', function() {
        loadSections($(this).val(), 'target_section_id');
        loadSubjects($(this).val(), 'target_subject_id');
    });

    function validateFields(fields) {
        for (const f of fields) {
            const $el = $('#' + f.id);
            if (!$el.val()) {
                $el.addClass('is-invalid');
                toastr.error(`Please select ${f.label}.`);
                return false;
            } else {
                $el.removeClass('is-invalid');
            }
        }
        return true;
    }

    $('#searchBtn').on('click', function() {
        const fields = [
            { id: 'source_academic_year_id', label: 'Academic Year' },
            { id: 'source_class_id', label: 'Class' },
            { id: 'source_section_id', label: 'Section' },
            { id: 'source_subject_id', label: 'Subject' },
        ];

        if (!validateFields(fields)) {
            return;
        }

        const payload = {
            academic_year_id: $('#source_academic_year_id').val(),
            class_id: $('#source_class_id').val(),
            section_id: $('#source_section_id').val(),
            subject_id: $('#source_subject_id').val(),
        };

        const searchFieldMap = {
            academic_year_id: 'source_academic_year_id',
            class_id: 'source_class_id',
            section_id: 'source_section_id',
            subject_id: 'source_subject_id',
        };

        $.get(`{{ route('copy_old_lesson.get_topics') }}`, payload)
            .done(function(res) {
                if (res.empty) {
                    $('#resultWrapper').html('<div class="alert alert-info">No lessons found for the selected criteria.</div>');
                    return;
                }
                $('#resultWrapper').html(res.html);
            })
            .fail(function(xhr) {
                showServerErrors(xhr, searchFieldMap);
            });
    });

    $(document).on('change', '#selectAll', function() {
        $('.lesson-checkbox').prop('checked', $(this).is(':checked'));
    });

    $(document).on('click', '#copyBtn', function() {
        const lessonIds = $('.lesson-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (!lessonIds.length) {
            toastr.warning('Please select at least one lesson.');
            return;
        }

        const targetFields = [
            { id: 'target_academic_year_id', label: 'Target Academic Year' },
            { id: 'target_class_id', label: 'Target Class' },
            { id: 'target_section_id', label: 'Target Section' },
            { id: 'target_subject_id', label: 'Target Subject' },
        ];

        if (!validateFields(targetFields)) {
            return;
        }

        const payload = {
            source_academic_year_id: $('#source_academic_year_id').val(),
            source_class_id: $('#source_class_id').val(),
            source_section_id: $('#source_section_id').val(),
            source_subject_id: $('#source_subject_id').val(),
            target_academic_year_id: $('#target_academic_year_id').val(),
            target_class_id: $('#target_class_id').val(),
            target_section_id: $('#target_section_id').val(),
            target_subject_id: $('#target_subject_id').val(),
            lesson_ids: lessonIds,
            _token: '{{ csrf_token() }}'
        };

        const copyFieldMap = {
            source_academic_year_id: 'source_academic_year_id',
            source_class_id: 'source_class_id',
            source_section_id: 'source_section_id',
            source_subject_id: 'source_subject_id',
            target_academic_year_id: 'target_academic_year_id',
            target_class_id: 'target_class_id',
            target_section_id: 'target_section_id',
            target_subject_id: 'target_subject_id',
        };

        $.ajax({
            url: '{{ route("copy_old_lesson.copy") }}',
            method: 'POST',
            data: payload,
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    $('#resultWrapper').html('');
                } else {
                    toastr.error(res.message);
                }
            },
            error: function(xhr) {
                showServerErrors(xhr, copyFieldMap);
            }
        });
    });

});
</script>
@endpush