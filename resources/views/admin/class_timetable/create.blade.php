@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Academic', 'url' => '#'],
    ['label' => 'Class Timetable', 'url' => route('class_timetable.index')],
    ['label' => 'Create', 'url' => '#'],
]" />

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 fw-semi-bold text-primary">Select Criteria</h5>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label>Class <span class="text-danger">*</span></label>
                <select id="class_id" class="form-select form-select-sm">
                    <option value="">Select</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback" id="err_class_id"></div>
            </div>
            <div class="col-md-4">
                <label>Section <span class="text-danger">*</span></label>
                <select id="section_id" class="form-select form-select-sm">
                    <option value="">Select Class First</option>
                </select>
                <div class="invalid-feedback" id="err_section_id"></div>
            </div>
            <div class="col-md-4" id="groupWrapper" style="display:none;">
                <label>Subject Group <span class="text-danger">*</span></label>
                <select id="group_id" class="form-select form-select-sm">
                    <option value="">Select</option>
                </select>
                <div class="invalid-feedback" id="err_group_id"></div>
            </div>
        </div>
    </div>

    <div class="card-footer text-end">
        <button id="btnSearch" class="btn btn-primary btn-sm">
            <i class="bx bx-search me-1"></i> Search
        </button>
    </div>
</div>

{{-- Ye poora block sirf Search click hone ke baad reveal hoga --}}
<div class="card mt-3" id="formSection" style="display:none;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fs-5 fw-semi-bold text-primary">Set Timetable</h6>
        <a href="{{ route('class_timetable.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class='bx bx-arrow-back'></i> Back to Index
        </a>
    </div>
    <div class="card-body">
        <h6 class="fw-semi-bold text-danger">Select parameter to generate time table quickly</h6>
        <div class="row mb-3 align-items-end gy-2">
            <div class="col-6 col-md-3 col-lg-2">
                <label>Period Start Time <span class="text-danger">*</span></label>
                <input type="time" id="period_start" class="form-control form-control-sm">
                <div class="invalid-feedback" id="err_period_start"></div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label>Duration (minutes) <span class="text-danger">*</span></label>
                <input type="number" id="duration" class="form-control form-control-sm" value="45">
                <div class="invalid-feedback" id="err_duration"></div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label>Interval (minutes)</label>
                <input type="number" id="interval" class="form-control form-control-sm" value="0">
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label>No. of Periods <span class="text-danger">*</span></label>
                <input type="number" id="period_count" class="form-control form-control-sm" value="6">
                <div class="invalid-feedback" id="err_period_count"></div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label>Break After Period #</label>
                <input type="number" id="break_after" class="form-control form-control-sm" placeholder="e.g. 5" min="0">
            </div>
            <div class="col-6 col-md-3 col-lg-1">
                <label>Break (min)</label>
                <input type="number" id="break_duration" class="form-control form-control-sm" placeholder="15" min="0">
            </div>
            <div class="col-12 col-lg-1">
                <button id="btnApply" class="btn btn-success btn-sm w-100">Apply</button>
            </div>
        </div>

        <ul class="nav nav-tabs" id="dayTabs">
            @foreach($days as $i => $day)
            <li class="nav-item">
                <a class="nav-link {{ $i == 0 ? 'active' : '' }}" data-day="{{ $day }}" href="javascript:;">{{ $day }}</a>
            </li>
            @endforeach
        </ul>

        <div class="d-flex justify-content-end align-items-center gap-2 mt-3 mb-2">
            <input type="text" id="periodSearchInput" class="form-control form-control-sm" style="max-width:220px;" placeholder="Search periods...">
            <button id="btnAddRow" class="btn btn-outline-warning btn-sm text-nowrap"><i class='bx bx-plus'></i> Add New</button>
        </div>

        <table class="table table-bordered table-striped" id="periodTable">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Subject</th>
                    <th>Time From</th>
                    <th>Time To</th>
                    <th>Teacher</th>
                    <th width="60">Action</th>
                </tr>
            </thead>
            <tbody id="periodRows"></tbody>
        </table>

        <button id="btnSave" class="btn btn-success btn-sm float-end"><i class="bx bx-save me-1"></i>Save</button>
    </div>
</div>

<template id="rowTemplate">
    <tr>
        <td class="row-index"></td>
        <td>
            <select class="form-select form-select-sm subject_id"></select>
            <div class="invalid-feedback error-subject_id"></div>
        </td>
        <td><input type="time" class="form-control form-control-sm time_from">
            <div class="invalid-feedback error-time_from"></div>
        </td>
        <td><input type="time" class="form-control form-control-sm time_to">
            <div class="invalid-feedback error-time_to"></div>
        </td>
        <td>
            <select class="form-select form-select-sm teacher_id">
                <option value="">Select</option>
                @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback error-teacher_id"></div>
        </td>
        <td><button type="button" class="btn btn-outline-danger btn-sm btnRemoveRow"><i class='bx bx-trash'></i></button></td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
    let currentDay = "{{ $days[0] }}";
    let subjectsCache = [];
    let allData = {};
    let periodDataTable = null;
    let subjectsLoading = false;

    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: 3000
    };

    function clearErrors() {
        $('.form-select, .form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    function normalizeTimeToHi(raw) {
        if (!raw) return '';
        raw = raw.trim();

        let ampm = raw.match(/^(\d{1,2}):(\d{2})(?::\d{2})?\s*([AaPp][Mm])$/);
        if (ampm) {
            let hour = parseInt(ampm[1], 10);
            let minute = ampm[2];
            let period = ampm[3].toLowerCase();
            if (period === 'pm' && hour !== 12) hour += 12;
            if (period === 'am' && hour === 12) hour = 0;
            return String(hour).padStart(2, '0') + ':' + minute;
        }

        let hhmm = raw.match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
        if (hhmm) {
            return String(parseInt(hhmm[1], 10)).padStart(2, '0') + ':' + hhmm[2];
        }

        return raw;
    }

    function showFieldError(id, msg) {
        $(`#${id}`).addClass('is-invalid');
        $(`#err_${id}`).text(msg);
    }

    // NEW: reusable — loads sections + groups for a class, returns a jQuery Deferred
    // so callers (user click vs auto-init from query params) can chain on it reliably,
    // instead of relying on a fixed timeout.
    function loadClassDependents(classId) {
        $('#section_id').html('<option value="">Loading...</option>');
        $('#groupWrapper').hide();
        $('#group_id').html('<option value="">Select</option>');
        subjectsCache = [];

        let sectionsRequest = $.get(`/class-timetable/get-sections/${classId}`)
            .done(function(res) {
                let opts = '<option value="">Select</option>';
                if (res.length === 0) opts = '<option value="">No sections found</option>';
                res.forEach(s => opts += `<option value="${s.id}">${s.name}</option>`);
                $('#section_id').html(opts);
            })
            .fail(function() {
                toastr.error('Failed to load sections');
            });

        let groupsRequest = $.get(`/class-timetable/get-groups/${classId}`)
            .done(function(res) {
                if (res.length > 0) {
                    let opts = '<option value="">Select</option>';
                    res.forEach(g => opts += `<option value="${g.id}">${g.name}</option>`);
                    $('#group_id').html(opts);
                    $('#groupWrapper').show();
                }
            })
            .fail(function() {
                toastr.error('Failed to load subject groups');
            });

        // caller can do .done() on the returned promise once BOTH calls finish
        return $.when(sectionsRequest, groupsRequest);
    }

    $('#class_id').on('change', function() {
        let classId = $(this).val();
        $('#formSection').hide();
        $('#btnSearch').prop('disabled', true);

        if (!classId) {
            $('#section_id').html('<option value="">Select Class First</option>');
            $('#groupWrapper').hide();
            $('#btnSearch').prop('disabled', false);
            return;
        }

        loadClassDependents(classId).always(function() {
            // agar group wrapper visible nahi hui (no groups for this class), subjects load kar do
            if (!$('#groupWrapper').is(':visible')) {
                loadSubjects(classId, null);
            } else {
                $('#btnSearch').prop('disabled', false); // group case: subjects load after group pick
            }
        });
    });

    $('#group_id').on('change', function() {
        let classId = $('#class_id').val();
        let groupId = $(this).val();
        if (classId && groupId) {
            loadSubjects(classId, groupId);
        }
    });

    function loadSubjects(classId, groupId) {
        let url = `/class-timetable/get-subjects/${classId}`;
        if (groupId) url += `?group_id=${groupId}`;
        subjectsLoading = true;
        $('#btnSearch').prop('disabled', true);

        return $.get(url)
            .done(function(res) {
                subjectsCache = res;
            })
            .fail(function() {
                toastr.error('Failed to load subjects');
            })
            .always(function() {
                subjectsLoading = false;
                $('#btnSearch').prop('disabled', false);
            });
    }

    function runSearch() {
        clearErrors();
        let classId = $('#class_id').val();
        let sectionId = $('#section_id').val();
        let groupRequired = $('#groupWrapper').is(':visible');
        let groupId = $('#group_id').val();
        let hasError = false;

        if (!classId) {
            showFieldError('class_id', 'Class is required');
            hasError = true;
        }
        if (!sectionId) {
            showFieldError('section_id', 'Section is required');
            hasError = true;
        }
        if (groupRequired && !groupId) {
            showFieldError('group_id', 'Subject Group is required');
            hasError = true;
        }
        if (hasError) {
            toastr.error('Please fill required fields');
            return;
        }

        if (subjectsLoading) {
            toastr.warning('Please wait, loading subjects...');
            return;
        }

        $.get(`/class-timetable/get-data`, {
                school_class_id: classId,
                section_id: sectionId
            })
            .done(function(res) {
                allData = {};
                Object.keys(res).forEach(day => {
                    allData[day] = res[day].map(r => ({
                        subject_id: r.subject_id,
                        time_from: r.time_from,
                        time_to: r.time_to,
                        teacher_id: r.teacher_id,
                    }));
                });
                $('#formSection').show();
                renderRowsForDay(currentDay);
            })
            .fail(function() {
                toastr.error('Failed to load timetable');
            });
    }

    $('#btnSearch').on('click', runSearch);

    // NEW: Edit mode — arriving here from Index's "Edit Timetable" button with
    // ?class_id=&section_id= in the URL. Chains class->sections/groups->subjects
    // properly instead of guessing with a timeout.
    $(document).ready(function() {
        const params = new URLSearchParams(window.location.search);
        const preClassId = params.get('class_id');
        const preSectionId = params.get('section_id');

        if (!preClassId) return;

        // Edit mode UI cue — button label change + top note
        $('#btnSave').html('<i class="bx bx-save me-1"></i>Update');

        $('#class_id').val(preClassId);
        $('#btnSearch').prop('disabled', true);

        loadClassDependents(preClassId).always(function() {
            if (preSectionId) $('#section_id').val(preSectionId);

            if ($('#groupWrapper').is(':visible')) {
                // Multiple subject groups exist for this class — we don't know which
                // one was originally used, so we stop here and let the user pick the
                // group manually before hitting Search (class/section are pre-filled).
                $('#btnSearch').prop('disabled', false);
                toastr.info('Please select Subject Group, then click Search to load the existing timetable.');
            } else {
                // No group ambiguity — safe to auto-load subjects and auto-search.
                loadSubjects(preClassId, null).always(function() {
                    runSearch();
                });
            }
        });
    });

    $(document).on('click', '#dayTabs .nav-link', function() {
        allData[currentDay] = collectCurrentDayRows();
        $('#dayTabs .nav-link').removeClass('active');
        $(this).addClass('active');
        currentDay = $(this).data('day');
        renderRowsForDay(currentDay);
    });

    function subjectOptionsHtml(selectedId = '') {
        let opts = '<option value="">Select</option>';
        subjectsCache.forEach(s => opts += `<option value="${s.id}" ${s.id == selectedId ? 'selected' : ''}>${s.name}</option>`);
        return opts;
    }

    function reindexRows() {
        $('#periodRows tr').each(function(i) {
            $(this).find('.row-index').text(i + 1);
        });
    }

    function initPeriodDataTable() {
        if ($.fn.DataTable.isDataTable('#periodTable')) periodDataTable.destroy();
        periodDataTable = $('#periodTable').DataTable({
            paging: false,
            searching: true,
            info: false,
            ordering: false,
            dom: 'rt',
            language: {
                emptyTable: '<div class="alert alert-warning text-center mb-0 py-2"><i class="bx bx-error-circle"></i> No periods added yet. Use "Apply" above or "+ Add New" to add one.</div>',
                zeroRecords: '<div class="alert alert-warning text-center mb-0 py-2"><i class="bx bx-search-alt"></i> No periods match your search.</div>'
            }
        });
        let currentSearch = $('#periodSearchInput').val();
        if (currentSearch) periodDataTable.search(currentSearch).draw();
    }

    $(document).on('keyup', '#periodSearchInput', function() {
        if (periodDataTable) periodDataTable.search(this.value).draw();
    });

    function addRow(data = {}) {
        let $row = $($('#rowTemplate').html());
        if (data.is_break) {
            $row.addClass('table-warning');
            $row.find('.subject_id').replaceWith(`<input type="text" class="form-control form-control-sm subject_id" value="Break" readonly>`);
            $row.find('.teacher_id').replaceWith(`<input type="text" class="form-control form-control-sm teacher_id" value="-" readonly>`);
        } else {
            $row.find('.subject_id').html(subjectOptionsHtml(data.subject_id));
            $row.find('.teacher_id').val(data.teacher_id || '');
        }
        $row.find('.time_from').val(data.time_from || '');
        $row.find('.time_to').val(data.time_to || '');
        $('#periodRows').append($row);
        reindexRows();
    }

    $('#btnAddRow').on('click', function() {
        if (periodDataTable) periodDataTable.destroy();
        addRow();
        initPeriodDataTable();
    });

    $(document).on('click', '.btnRemoveRow', function() {
        if (periodDataTable) periodDataTable.destroy();
        $(this).closest('tr').remove();
        reindexRows();
        initPeriodDataTable();
    });

    function renderRowsForDay(day) {
        if (periodDataTable) periodDataTable.destroy();
        $('#periodRows').empty();
        (allData[day] || []).forEach(row => addRow(row));
        initPeriodDataTable();
    }

    function collectCurrentDayRows() {
        let rows = [];
        $('#periodRows tr').each(function() {
            let $row = $(this);
            if ($row.hasClass('table-warning')) return;
            rows.push({
                subject_id: $row.find('.subject_id').val(),
                time_from: $row.find('.time_from').val(),
                time_to: $row.find('.time_to').val(),
                teacher_id: $row.find('.teacher_id').val(),
            });
        });
        return rows;
    }

    function validateRows() {
        let valid = true;
        $('#periodRows tr').each(function() {
            let $row = $(this);
            if ($row.hasClass('table-warning')) return;
            $row.find('.form-select, .form-control').removeClass('is-invalid');
            $row.find('.invalid-feedback').text('');

            let subjectId = $row.find('.subject_id').val();
            let timeFrom = $row.find('.time_from').val();
            let timeTo = $row.find('.time_to').val();
            let teacherId = $row.find('.teacher_id').val();

            if (!subjectId) {
                $row.find('.subject_id').addClass('is-invalid');
                $row.find('.error-subject_id').text('Required');
                valid = false;
            }
            if (!timeFrom) {
                $row.find('.time_from').addClass('is-invalid');
                $row.find('.error-time_from').text('Required');
                valid = false;
            }
            if (!timeTo) {
                $row.find('.time_to').addClass('is-invalid');
                $row.find('.error-time_to').text('Required');
                valid = false;
            }
            let normFrom = normalizeTimeToHi(timeFrom);
            let normTo = normalizeTimeToHi(timeTo);
            if (normFrom && normTo && normTo <= normFrom) {
                $row.find('.time_to').addClass('is-invalid');
                $row.find('.error-time_to').text('Must be after Time From');
                valid = false;
            }
            if (!teacherId) {
                $row.find('.teacher_id').addClass('is-invalid');
                $row.find('.error-teacher_id').text('Required');
                valid = false;
            }
        });
        return valid;
    }

    $('#btnApply').on('click', function() {
        clearErrors();
        let start = $('#period_start').val();
        let duration = parseInt($('#duration').val());
        let count = parseInt($('#period_count').val());
        let interval = parseInt($('#interval').val()) || 0;
        let breakAfter = parseInt($('#break_after').val()) || 0;
        let breakDuration = parseInt($('#break_duration').val()) || 0;
        let hasError = false;
        if (!start) {
            showFieldError('period_start', 'Start time required');
            hasError = true;
        }
        if (!duration) {
            showFieldError('duration', 'Duration required');
            hasError = true;
        }
        if (!count) {
            showFieldError('period_count', 'Period count required');
            hasError = true;
        }
        if (hasError) {
            toastr.error('Please fill quick-generate fields');
            return;
        }

        if (periodDataTable) periodDataTable.destroy();
        $('#periodRows').empty();
        let [h, m] = start.split(':').map(Number);
        let current = h * 60 + m;
        for (let i = 0; i < count; i++) {
            let from = current,
                to = current + duration;
            let fromStr = String(Math.floor(from / 60)).padStart(2, '0') + ':' + String(from % 60).padStart(2, '0');
            let toStr = String(Math.floor(to / 60)).padStart(2, '0') + ':' + String(to % 60).padStart(2, '0');
            addRow({
                time_from: fromStr,
                time_to: toStr
            });
            current = to + interval;

            if (breakAfter && breakDuration && (i + 1) === breakAfter) {
                let breakFrom = current,
                    breakTo = current + breakDuration;
                let bFromStr = String(Math.floor(breakFrom / 60)).padStart(2, '0') + ':' + String(breakFrom % 60).padStart(2, '0');
                let bToStr = String(Math.floor(breakTo / 60)).padStart(2, '0') + ':' + String(breakTo % 60).padStart(2, '0');
                addRow({
                    time_from: bFromStr,
                    time_to: bToStr,
                    is_break: true
                });
                current = breakTo;
            }
        }
        initPeriodDataTable();
        toastr.success('Periods generated');
    });

    $('#btnSave').on('click', function() {
        clearErrors();
        allData[currentDay] = collectCurrentDayRows();
        let classId = $('#class_id').val();
        let sectionId = $('#section_id').val();

        if (!validateRows()) {
            toastr.error('Please fix highlighted fields before saving');
            return;
        }

        let periods = [];
        let periodsMeta = [];
        Object.keys(allData).forEach(day => {
            allData[day].forEach((r, idx) => {
                periods.push({
                    day,
                    subject_id: r.subject_id,
                    teacher_id: r.teacher_id,
                    time_from: normalizeTimeToHi(r.time_from),
                    time_to: normalizeTimeToHi(r.time_to),
                });
                periodsMeta.push({
                    day,
                    rowIndex: idx
                });
            });
        });

        if (periods.length === 0) {
            toastr.error('Add at least one period before saving');
            return;
        }

        $.ajax({
            url: '/class-timetable/save',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                school_class_id: classId,
                section_id: sectionId,
                periods: periods
            },
            success: function(res) {
                toastr.success(res.message || 'Timetable saved successfully');
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let res = xhr.responseJSON;
                    if (res.errors) {
                        let fieldLabels = {
                            subject_id: 'Subject',
                            time_from: 'Time From',
                            time_to: 'Time To',
                            teacher_id: 'Teacher',
                            day: 'Day'
                        };
                        let messages = [];

                        $.each(res.errors, function(key, msgs) {
                            let msg = msgs[0];
                            let match = key.match(/^periods\.(\d+)\.(\w+)$/);

                            if (match) {
                                let idx = parseInt(match[1]);
                                let field = match[2];
                                let meta = periodsMeta[idx];

                                if (meta) {
                                    messages.push(`${meta.day} - Period ${meta.rowIndex + 1}: ${fieldLabels[field] || field} ${msg}`);
                                    if (meta.day === currentDay) {
                                        let $row = $('#periodRows tr').eq(meta.rowIndex);
                                        $row.find(`.${field}`).addClass('is-invalid');
                                        $row.find(`.error-${field}`).text(msg);
                                    }
                                } else {
                                    messages.push(msg);
                                }
                            } else {
                                messages.push(msg);
                            }
                        });

                        let uniqueMessages = [...new Set(messages)];
                        let listHtml = '<ul class="mb-0 ps-3 text-start">' + uniqueMessages.map(m => `<li>${m}</li>`).join('') + '</ul>';

                        toastr.error(listHtml, `${uniqueMessages.length} field(s) need attention`, {
                            timeOut: 8000,
                            extendedTimeOut: 4000,
                            escapeHtml: false
                        });
                    } else {
                        toastr.error(res.message || 'Validation failed');
                    }
                } else {
                    toastr.error('Something went wrong');
                }
            }
        });
    });
</script>
@endpush