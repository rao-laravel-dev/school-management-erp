@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Student Information', 'url' => '#'],
    ['label' => 'Student Promotion', 'url' => route('student_promotion.index')],
]" />

<div class="card radius-10 mb-3">
    <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
        <h5 class="mb-0 text-primary">Student Promotion</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info small mb-3">
            Create the new session first (not current yet). Promote only after final results and the last attendance of the old session are done,
            then mark the new session as current.
        </div>

        <div class="row g-2 mb-2">
            <div class="col-12"><span class="small fw-bold text-uppercase text-muted">From</span></div>
            <div class="col-md-3">
                <label for="studentPromotionFromYear" class="small fw-bold">Session *</label>
                <select id="studentPromotionFromYear" class="form-select form-select-sm">
                    @foreach($years as $year)
                    <option value="{{ $year->id }}" {{ $currentYearId == $year->id ? 'selected' : '' }}>{{ $year->name }}{{ $year->is_current ? ' (Current)' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="studentPromotionFromClass" class="small fw-bold">Class *</label>
                <select id="studentPromotionFromClass" class="form-select form-select-sm">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="studentPromotionFromSection" class="small fw-bold">Section *</label>
                <select id="studentPromotionFromSection" class="form-select form-select-sm">
                    <option value="">Select Section</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="studentPromotionExam" class="small fw-bold">Exam (optional)</label>
                <select id="studentPromotionExam" class="form-select form-select-sm">
                    <option value="">No exam (everyone Promote)</option>
                </select>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-12"><span class="small fw-bold text-uppercase text-muted">To</span></div>
            <div class="col-md-3">
                <label for="studentPromotionToYear" class="small fw-bold">Session *</label>
                <select id="studentPromotionToYear" class="form-select form-select-sm"></select>
            </div>
            <div class="col-md-3">
                <label for="studentPromotionToClass" class="small fw-bold">Class *</label>
                <select id="studentPromotionToClass" class="form-select form-select-sm">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="studentPromotionToSection" class="small fw-bold">Section *</label>
                <select id="studentPromotionToSection" class="form-select form-select-sm">
                    <option value="">Select Section</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" id="studentPromotionLoadBtn" class="btn btn-primary btn-sm w-100">Load Students</button>
            </div>
        </div>
    </div>
</div>

<div class="card radius-10" id="studentPromotionListCard" style="display:none;">
    <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
        <h5 class="mb-0 text-primary">Students</h5>
        @can('manage-student-promotion')
        <button type="button" id="studentPromotionSaveBtn" class="btn btn-success btn-sm shadow-sm px-5">Save</button>
        @endcan
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle" id="studentPromotionTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Admission No</th>
                        <th>Name</th>
                        <th>Roll No</th>
                        <th>Result</th>
                        <th style="min-width:150px;">Action</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="studentPromotionBody"></tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {
        const studentPromotionYears = @json($years);
        const studentPromotionClasses = @json($classes);
        const studentPromotionHighestClassId = @json($highestClassId);
        const studentPromotionSectionsUrl = "{{ route('student_promotion.get_sections', ':id') }}";
        const studentPromotionExamsUrl = "{{ route('student_promotion.get_exams', ':id') }}";
        const studentPromotionStudentsUrl = "{{ route('student_promotion.students') }}";
        const studentPromotionStoreUrl = "{{ route('student_promotion.store') }}";
        const studentPromotionUndoUrl = "{{ route('student_promotion.undo', ':id') }}";
        @can('manage-student-promotion')
        const studentPromotionCanManage = true;
        @else
        const studentPromotionCanManage = false;
        @endcan

        let studentPromotionToken = 0; // purane AJAX reply ignore karne ke liye
        let studentPromotionLoaded = null; // last loaded {from_year_id, ..., is_highest}

        // Server error key -> select id
        const studentPromotionFieldMap = {
            from_year_id: '#studentPromotionFromYear',
            from_class_id: '#studentPromotionFromClass',
            from_section_id: '#studentPromotionFromSection',
            exam_id: '#studentPromotionExam',
            to_year_id: '#studentPromotionToYear',
            to_class_id: '#studentPromotionToClass',
            to_section_id: '#studentPromotionToSection'
        };

        function studentPromotionSetError(selector, msg) {
            const $el = $(selector);
            $el.addClass('is-invalid');
            $el.next('.invalid-feedback').remove();
            $el.after('<div class="invalid-feedback">' + $('<div>').text(msg).html() + '</div>');
        }

        function studentPromotionClearErrors() {
            $('#studentPromotionFromYear, #studentPromotionFromClass, #studentPromotionFromSection, #studentPromotionExam, #studentPromotionToYear, #studentPromotionToClass, #studentPromotionToSection')
                .removeClass('is-invalid').next('.invalid-feedback').remove();
        }

        // Missing = [{selector, msg}]: har select par message, toastr sirf pehla
        function studentPromotionShowMissing(missing) {
            $.each(missing, function(i, m) {
                studentPromotionSetError(m.selector, m.msg);
            });
            toastr.clear();
            toastr.error(missing[0].msg);
            $(missing[0].selector).trigger('focus');
        }

        // 422 reply: errors keyed ho to har select par, warna sirf message ka toastr
        function studentPromotionShowServerErrors(xhr, fallback) {
            const json = xhr.responseJSON || {};
            const missing = [];
            if (json.errors) {
                $.each(json.errors, function(key, msgs) {
                    if (studentPromotionFieldMap[key]) {
                        missing.push({ selector: studentPromotionFieldMap[key], msg: msgs[0] });
                    }
                });
            }
            if (missing.length) {
                studentPromotionShowMissing(missing);
                return;
            }
            toastr.clear();
            toastr.error(json.message ? json.message : fallback);
        }

        $(document).on('change input', '.is-invalid', function() {
            $(this).removeClass('is-invalid').next('.invalid-feedback').remove();
        });

        function studentPromotionEsc(text) {
            return $('<div>').text(text === null || text === undefined ? '' : text).html();
        }

        function studentPromotionYearById(id) {
            for (let i = 0; i < studentPromotionYears.length; i++) {
                if (String(studentPromotionYears[i].id) === String(id)) {
                    return studentPromotionYears[i];
                }
            }
            return null;
        }

        // To session list: sirf wo sessions jo From se baad shuru hotay hain (dates DB se)
        function studentPromotionFillToYears() {
            const from = studentPromotionYearById($('#studentPromotionFromYear').val());
            const $to = $('#studentPromotionToYear').empty();
            let count = 0;
            for (let i = 0; i < studentPromotionYears.length; i++) {
                const y = studentPromotionYears[i];
                if (from && y.start_date > from.start_date && Number(y.status) === 1) {
                    $to.append($('<option>').val(y.id).text(y.name));
                    count++;
                }
            }
            if (!count) {
                $to.append($('<option>').val('').text('No later session. Create one first'));
            }
        }

        function studentPromotionFillSections($select, classId, selectedId, done) {
            $select.html('<option value="">Loading...</option>');
            if (!classId) {
                $select.html('<option value="">Select Section</option>');
                if (done) { done(); }
                return;
            }
            $.get(studentPromotionSectionsUrl.replace(':id', classId)).done(function(res) {
                let html = '<option value="">Select Section</option>';
                $.each(res, function(i, s) {
                    html += '<option value="' + s.id + '">' + studentPromotionEsc(s.name) + '</option>';
                });
                $select.html(html);
                if (selectedId) {
                    $select.val(String(selectedId));
                    if ($select.val() !== String(selectedId)) { $select.val(''); }
                }
                if (done) { done(); }
            }).fail(function() {
                $select.html('<option value="">Select Section</option>');
                toastr.error('Could not load sections');
            });
        }

        function studentPromotionFillExams() {
            const yearId = $('#studentPromotionFromYear').val();
            const $exam = $('#studentPromotionExam');
            $exam.html('<option value="">No exam (everyone Promote)</option>');
            if (!yearId) { return; }
            $.get(studentPromotionExamsUrl.replace(':id', yearId)).done(function(res) {
                $.each(res, function(i, e) {
                    $exam.append($('<option>').val(e.id).text(e.name));
                });
            }).fail(function() {
                toastr.error('Could not load exams');
            });
        }

        // Default To class = agli class (numeric_name bara), admin badal sakta hai
        function studentPromotionSetNextClass() {
            const fromClassId = $('#studentPromotionFromClass').val();
            let fromNumeric = null;
            for (let i = 0; i < studentPromotionClasses.length; i++) {
                if (String(studentPromotionClasses[i].id) === String(fromClassId)) {
                    fromNumeric = Number(studentPromotionClasses[i].numeric_name);
                }
            }
            let nextId = '';
            if (fromNumeric !== null) {
                for (let i = 0; i < studentPromotionClasses.length; i++) {
                    if (Number(studentPromotionClasses[i].numeric_name) > fromNumeric) {
                        nextId = studentPromotionClasses[i].id; // list numeric_name se sorted hai
                        break;
                    }
                }
            }
            $('#studentPromotionToClass').val(String(nextId));
            return nextId;
        }

        function studentPromotionActionSelect(row, isHighest) {
            if (!row.selectable || !studentPromotionCanManage) {
                return '<span class="text-muted">-</span>';
            }
            const opts = [];
            if (isHighest) {
                opts.push(['passed_out', 'Passed Out']);
            } else {
                opts.push(['promote', 'Promote']);
            }
            opts.push(['detain', 'Detain']);
            opts.push(['skip', 'Skip']);
            let html = '<select class="form-select form-select-sm studentPromotionAction" data-enrollment="' + row.enrollment_id + '">';
            $.each(opts, function(i, o) {
                html += '<option value="' + o[0] + '"' + (row.default_action === o[0] ? ' selected' : '') + '>' + o[1] + '</option>';
            });
            return html + '</select>';
        }

        function studentPromotionRender(rows, isHighest) {
            const $body = $('#studentPromotionBody').empty();
            if (!rows.length) {
                $body.append('<tr><td colspan="7" class="text-center text-muted">No students in this class and section</td></tr>');
                return;
            }
            $.each(rows, function(i, r) {
                let result = '-';
                if (r.result_status) {
                    const badge = r.result_status === 'Pass' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                    result = studentPromotionEsc(r.percentage) + '% ' + studentPromotionEsc(r.grade || '') +
                        ' <span class="badge ' + badge + '">' + studentPromotionEsc(r.result_status) + '</span>';
                }
                let status = '<span class="text-muted">Ready</span>';
                if (r.note) {
                    status = '<span class="badge bg-secondary">' + studentPromotionEsc(r.note) + '</span>';
                    if (r.can_undo && studentPromotionCanManage) {
                        status += ' <button type="button" class="btn btn-outline-danger btn-sm ms-1 studentPromotionUndoBtn" data-id="' + r.enrollment_id + '">Undo</button>';
                    }
                }
                $body.append('<tr>' +
                    '<td>' + (i + 1) + '</td>' +
                    '<td>' + studentPromotionEsc(r.admission_no) + '</td>' +
                    '<td>' + studentPromotionEsc(r.name) + '</td>' +
                    '<td>' + studentPromotionEsc(r.roll_no) + '</td>' +
                    '<td>' + result + '</td>' +
                    '<td>' + studentPromotionActionSelect(r, isHighest) + '</td>' +
                    '<td>' + status + '</td>' +
                    '</tr>');
            });
        }

        function studentPromotionLoad() {
            const params = {
                from_year_id: $('#studentPromotionFromYear').val(),
                to_year_id: $('#studentPromotionToYear').val(),
                from_class_id: $('#studentPromotionFromClass').val(),
                from_section_id: $('#studentPromotionFromSection').val(),
                exam_id: $('#studentPromotionExam').val()
            };
            studentPromotionClearErrors();
            const missing = [];
            if (!params.from_year_id) { missing.push({ selector: '#studentPromotionFromYear', msg: 'Please select the From session.' }); }
            if (!params.from_class_id) { missing.push({ selector: '#studentPromotionFromClass', msg: 'Please select the From class.' }); }
            if (!params.from_section_id) { missing.push({ selector: '#studentPromotionFromSection', msg: 'Please select the From section.' }); }
            if (!params.to_year_id) { missing.push({ selector: '#studentPromotionToYear', msg: 'Please select the To session (create the new session first).' }); }
            if (missing.length) {
                studentPromotionShowMissing(missing);
                return;
            }
            const token = ++studentPromotionToken;
            // To class/section: admin ka intikhab (ya default) Load ke baad bhi barqarar rakhna
            const keepToClass = $('#studentPromotionToClass').val();
            const keepToSection = $('#studentPromotionToSection').val();
            $('#studentPromotionLoadBtn').prop('disabled', true);
            $.get(studentPromotionStudentsUrl, params).done(function(res) {
                if (token !== studentPromotionToken) { return; }
                const toClassId = keepToClass || studentPromotionSetNextClass();
                $('#studentPromotionToClass').val(String(toClassId || ''));
                if (toClassId && $('#studentPromotionToSection').val() !== (keepToSection || params.from_section_id)) {
                    studentPromotionFillSections($('#studentPromotionToSection'), toClassId, keepToSection || params.from_section_id);
                }
                studentPromotionLoaded = $.extend({}, params, { is_highest: res.is_highest });
                $('#studentPromotionListCard').show();
                studentPromotionRender(res.rows, res.is_highest);
            }).fail(function(xhr) {
                if (token !== studentPromotionToken) { return; }
                studentPromotionShowServerErrors(xhr, 'Could not load students');
            }).always(function() {
                $('#studentPromotionLoadBtn').prop('disabled', false);
            });
        }

        // Filters badlen to purani list chhupa do (galat params se save na ho)
        function studentPromotionHideList() {
            studentPromotionLoaded = null;
            $('#studentPromotionListCard').hide();
        }

        $('#studentPromotionFromYear').on('change', function() {
            studentPromotionFillToYears();
            studentPromotionFillExams();
            studentPromotionHideList();
        });

        $('#studentPromotionFromClass').on('change', function() {
            studentPromotionHideList();
            studentPromotionFillSections($('#studentPromotionFromSection'), $(this).val(), null);
            const nextId = studentPromotionSetNextClass();
            // To section ki list; From section chunne par wohi section default ho jata hai (agar mapped ho)
            studentPromotionFillSections($('#studentPromotionToSection'), nextId, null);
        });

        $('#studentPromotionFromSection').on('change', function() {
            studentPromotionHideList();
            const sectionId = $(this).val();
            if (sectionId && $('#studentPromotionToClass').val()) {
                $('#studentPromotionToSection').val(sectionId);
                if ($('#studentPromotionToSection').val() !== sectionId) {
                    $('#studentPromotionToSection').val('');
                }
            }
        });

        $('#studentPromotionToClass').on('change', function() {
            studentPromotionHideList();
            studentPromotionFillSections($('#studentPromotionToSection'), $(this).val(), $('#studentPromotionFromSection').val());
        });

        $('#studentPromotionToYear, #studentPromotionToSection, #studentPromotionExam').on('change', studentPromotionHideList);

        $('#studentPromotionLoadBtn').on('click', studentPromotionLoad);

        $('#studentPromotionSaveBtn').on('click', function() {
            if (!studentPromotionLoaded) { return; }
            const rows = [];
            const counts = { promote: 0, detain: 0, passed_out: 0 };
            $('.studentPromotionAction').each(function() {
                const action = $(this).val();
                rows.push({ enrollment_id: $(this).data('enrollment'), action: action });
                if (counts[action] !== undefined) { counts[action]++; }
            });
            const total = counts.promote + counts.detain + counts.passed_out;
            if (!total) {
                toastr.clear();
                toastr.error('Select at least one student (Promote, Detain or Passed Out)');
                return;
            }
            studentPromotionClearErrors();
            const missing = [];
            if (!$('#studentPromotionToYear').val()) { missing.push({ selector: '#studentPromotionToYear', msg: 'Please select the To session.' }); }
            if (counts.promote && !$('#studentPromotionToClass').val()) { missing.push({ selector: '#studentPromotionToClass', msg: 'Please select the To class.' }); }
            if (counts.promote && !$('#studentPromotionToSection').val()) { missing.push({ selector: '#studentPromotionToSection', msg: 'Please select the To section.' }); }
            if (missing.length) {
                studentPromotionShowMissing(missing);
                return;
            }

            const toYearName = $('#studentPromotionToYear option:selected').text();
            Swal.fire({
                title: 'Save promotion?',
                html: studentPromotionEsc(counts.promote) + ' promote, ' + studentPromotionEsc(counts.detain) + ' detain, ' +
                    studentPromotionEsc(counts.passed_out) + ' passed out<br>Session: ' + studentPromotionEsc(toYearName),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="bx bx-save"></i> Yes, save',
                buttonsStyling: false,
                customClass: { popup: 'fee-assignment-swal', confirmButton: 'btn btn-success btn-sm me-2', cancelButton: 'btn btn-danger btn-sm' }
            }).then(function(result) {
                if (!result.isConfirmed) { return; }
                const $btn = $('#studentPromotionSaveBtn').prop('disabled', true);
                $.ajax({
                    url: studentPromotionStoreUrl,
                    type: 'POST',
                    data: {
                        from_year_id: studentPromotionLoaded.from_year_id,
                        to_year_id: studentPromotionLoaded.to_year_id,
                        from_class_id: studentPromotionLoaded.from_class_id,
                        from_section_id: studentPromotionLoaded.from_section_id,
                        to_class_id: $('#studentPromotionToClass').val(),
                        to_section_id: $('#studentPromotionToSection').val(),
                        rows: rows
                    }
                }).done(function(res) {
                    toastr.clear();
                    toastr.success(res.message);
                    studentPromotionLoad();
                }).fail(function(xhr) {
                    studentPromotionShowServerErrors(xhr, 'Could not save');
                }).always(function() {
                    $btn.prop('disabled', false);
                });
            });
        });

        $(document).on('click', '.studentPromotionUndoBtn', function() {
            if (!studentPromotionLoaded) { return; }
            const id = $(this).data('id');
            const $btn = $(this);
            Swal.fire({
                title: 'Undo this promotion?',
                text: 'The new enrollment is removed and the student returns to the old class.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, undo',
                buttonsStyling: false,
                customClass: { popup: 'fee-assignment-swal', confirmButton: 'btn btn-danger btn-sm me-2', cancelButton: 'btn btn-secondary btn-sm' }
            }).then(function(result) {
                if (!result.isConfirmed) { return; }
                $btn.prop('disabled', true);
                $.ajax({
                    url: studentPromotionUndoUrl.replace(':id', id),
                    type: 'POST',
                    data: { to_year_id: studentPromotionLoaded.to_year_id }
                }).done(function(res) {
                    toastr.clear();
                    toastr.success(res.message);
                    studentPromotionLoad();
                }).fail(function(xhr) {
                    $btn.prop('disabled', false);
                    toastr.clear();
                    toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Could not undo');
                });
            });
        });

        studentPromotionFillToYears();
        studentPromotionFillExams();
    });
</script>
@endpush
