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
            <button id="btnCopyDay" type="button" class="btn btn-outline-primary btn-sm text-nowrap"><i class='bx bx-copy'></i> Copy Day</button>
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
    <div class="input-group input-group-sm">
        <select class="form-select form-select-sm teacher_id">
            <option value="">Select</option>
            @foreach($teachers as $teacher)
            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
            @endforeach
        </select>
        <button type="button" class="btn btn-outline-primary btnTeacherSchedule" title="View teacher availability">
            <i class='bx bx-calendar-event'></i>
        </button>
    </div>
    <div class="invalid-feedback error-teacher_id"></div>
</td>
        <td><button type="button" class="btn btn-outline-danger btn-sm btnRemoveRow"><i class='bx bx-trash'></i></button></td>
    </tr>
</template>

{{-- Start-Modal Window: Copy Day --}}
<div class="modal fade" id="copyDayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title mb-0"><i class='bx bx-copy text-primary'></i> Copy <span id="copy_src_day"></span> to</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-check mb-2 border-bottom pb-2">
                    <input class="form-check-input" type="checkbox" id="copy_day_all">
                    <label class="form-check-label small fw-bold" for="copy_day_all">Select all</label>
                </div>
                <div id="copyDayList"></div>
                <div class="small text-muted mt-2">Copied days are only loaded on screen. Save each day separately.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnCopyConfirm">Copy</button>
            </div>
        </div>
    </div>
</div>
{{-- End-Modal Window: Copy Day --}}

{{-- Start-Modal Window --}}
<div class="modal fade" id="teacherAvailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0"><i class='bx bx-user-circle text-primary'></i> <span id="ta_name"></span></h5>
                    <small class="text-muted">Teacher availability</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="ta_alert"></div>
                <div class="ta-days mb-3" id="ta_days"></div>

                <div id="ta_timeline" class="mb-2"></div>
                <div class="ta-legend mb-3">
                    <span><i class="ta-dot busy"></i> Busy</span>
                    <span><i class="ta-dot free"></i> Free</span>
                    <span><i class="ta-dot current"></i> This period</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="fw-semi-bold text-danger mb-2"><i class='bx bx-time'></i> Busy Periods</h6>
                        <div id="ta_busy_list"></div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-semi-bold text-success mb-2"><i class='bx bx-check-circle'></i> Free Slots</h6>
                        <div id="ta_free_list"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
{{-- End-Modal Window --}}
@endsection

@push('styles')
<style>
    .error-teacher_id:not(:empty) { display: block; }

    .ta-days { display: flex; flex-wrap: wrap; gap: 6px; }
    .ta-day-pill { border: 1px solid #dee2e6; background: #fff; border-radius: 20px; padding: 3px 12px; font-size: 12px; cursor: pointer; }
    .ta-day-pill.active { background: #0d6efd; border-color: #0d6efd; color: #fff; }

    .ta-track { position: relative; height: 40px; background: #f1f3f5; border-radius: 8px; overflow: hidden; }
    .ta-seg { position: absolute; top: 0; bottom: 0; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600; color: #fff; overflow: hidden; white-space: nowrap; border-right: 1px solid rgba(255,255,255,.6); }
    .ta-seg.assembly { background: #adb5bd; }
    .ta-seg.break { background: #ffc107; color: #664d03; }
    .ta-seg.busy { background: #dc3545; }
    .ta-seg.free { background: #3fae7a; }
    .ta-seg.free.clickable { cursor: pointer; transition: filter .15s; }
    .ta-seg.free.clickable:hover { filter: brightness(.9); }
    .ta-seg.current { background: transparent; border: 3px solid #0d6efd; box-shadow: 0 0 0 2px rgba(13,110,253,.25); z-index: 3; pointer-events: none; }

    .ta-ticks { position: relative; height: 18px; margin-top: 2px; }
    .ta-tick { position: absolute; transform: translateX(-50%); font-size: 10px; color: #6c757d; white-space: nowrap; }
    .ta-tick:first-child { transform: none; }
    .ta-tick:last-child { transform: translateX(-100%); }

    .ta-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 4px; }
    .ta-dot.busy { background: #dc3545; }
    .ta-dot.free { background: #3fae7a; }
    .ta-item.neutral { border-left: 4px solid #adb5bd; background: #f8f9fa; font-size: 12px; padding: 6px 10px; }
    .ta-item.breakrow { border-left: 4px solid #ffc107; background: #fffbea; font-size: 12px; padding: 6px 10px; }
    .ta-pbadge { display: inline-block; background: #fff; border: 1px solid #f1aeb5; color: #842029; border-radius: 10px; font-size: 11px; padding: 0 7px; margin-left: 4px; }
    .ta-dot.current { background: transparent; border: 2px solid #0d6efd; }

    .ta-item { border: 1px solid #e5e7eb; border-radius: 8px; padding: 8px 10px; margin-bottom: 8px; font-size: 13px; }
    .ta-item.busy { border-left: 4px solid #dc3545; background: #fff5f5; }
    .ta-item.free { border-left: 4px solid #198754; background: #f3faf6; display: flex; justify-content: space-between; align-items: center; gap: 8px; }
    .ta-item small { color: #6c757d; display: block; }

    .ta-sum { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
    .ta-chip { font-size: 12px; font-weight: 600; padding: 3px 12px; border-radius: 20px; }
    .ta-chip.busy { background: #f8d7da; color: #842029; }
    .ta-chip.free { background: #d1e7dd; color: #0f5132; }

    .ta-seg.free.leftover { background: #a9d8c0; color: #0f5132; }

    .ta-gap-head { font-size: 12px; font-weight: 600; color: #198754; margin: 10px 0 6px; }
    .ta-gap-head:first-child { margin-top: 0; }
    .ta-item.free.leftover { background: #f8f9fa; border-left-color: #adb5bd; }
</style>
@endpush


@push('scripts')
<script>
    let currentDay = "{{ $days[0] }}";
    let subjectsCache = [];
    let allData = {};
    let periodDataTable = null;
    let subjectsLoading = false;
    let savedDays = new Set();   // wo din jo DB mein pehle se saved hain (label: Update / Save)
    let dirtyDays = new Set();   // wo din jin mein unsaved changes hain (tab par *)

    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: 3000
    };

    // ---------- Save label + unsaved marks ----------
    function updateSaveLabel() {
        $('#btnSave').html('<i class="bx bx-save me-1"></i>' + (savedDays.has(currentDay) ? 'Update ' : 'Save ') + currentDay);
    }

    function refreshTabMarks() {
        $('#dayTabs .nav-link').each(function() {
            const d = $(this).data('day');
            $(this).text(d + (dirtyDays.has(d) ? ' *' : ''));
        });
    }

    function markDirty() {
        dirtyDays.add(currentDay);
        refreshTabMarks();
    }

    $(document).on('input change', '#periodRows select, #periodRows input', markDirty);

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

    // Loads sections + groups for a class, returns a jQuery Deferred
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
            if (!$('#groupWrapper').is(':visible')) {
                loadSubjects(classId, null);
            } else {
                $('#btnSearch').prop('disabled', false);
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
                savedDays = new Set(Object.keys(res));
                dirtyDays = new Set();
                Object.keys(res).forEach(day => {
                    allData[day] = res[day].map(r => ({
                        subject_id: r.subject_id,
                        subject_name: r.subject ? r.subject.name : '',
                        time_from: r.time_from,
                        time_to: r.time_to,
                        teacher_id: r.teacher_id,
                    }));
                });
                $('#formSection').show();
                renderRowsForDay(currentDay);
                refreshTabMarks();
                updateSaveLabel();
            })
            .fail(function() {
                toastr.error('Failed to load timetable');
            });
    }

    $('#btnSearch').on('click', runSearch);

    // Edit mode — arriving from Index's "Edit Timetable" button with ?class_id=&section_id=
    $(document).ready(function() {
        const params = new URLSearchParams(window.location.search);
        const preClassId = params.get('class_id');
        const preSectionId = params.get('section_id');

        if (!preClassId) return;

        $('#class_id').val(preClassId);
        $('#btnSearch').prop('disabled', true);

        loadClassDependents(preClassId).always(function() {
            if (preSectionId) $('#section_id').val(preSectionId);

            // Index ke period card ke Edit button se ?day= aaye to usi din ka tab kholo (galat/unknown day ignore).
            // Sirf tab/currentDay badalta hai, groups/sections ka flow bina day jaisa hi rehta hai
            const preDay = params.get('day');
            const $preTab = $('#dayTabs .nav-link').filter(function() {
                return $(this).data('day') === preDay;
            });
            if ($preTab.length) {
                $('#dayTabs .nav-link').removeClass('active');
                $preTab.addClass('active');
                currentDay = preDay;
            }

            if ($('#groupWrapper').is(':visible')) {
                $('#btnSearch').prop('disabled', false);
                toastr.info('Please select Subject Group, then click Search to load the existing timetable.');
            } else {
                loadSubjects(preClassId, null).always(function() {
                    runSearch();
                });
            }
        });
    });

    // Day tab switch
    $(document).on('click', '#dayTabs .nav-link', function() {
        if (dirtyDays.has(currentDay)) {
            toastr.warning(`${currentDay} has unsaved changes. Come back and click Save ${currentDay} to keep them.`, '', { timeOut: 5000 });
        }
        allData[currentDay] = collectCurrentDayRows();
        $('#dayTabs .nav-link').removeClass('active');
        $(this).addClass('active');
        currentDay = $(this).data('day');
        renderRowsForDay(currentDay);
        updateSaveLabel();
    });

    function subjectOptionsHtml(selectedId = '', selectedName = '') {
        let opts = '<option value="">Select</option>';
        let found = false;
        subjectsCache.forEach(s => {
            if (s.id == selectedId) found = true;
            opts += `<option value="${s.id}" ${s.id == selectedId ? 'selected' : ''}>${s.name}</option>`;
        });
        // saved subject current group ki list mein na ho to bhi option dikhao
        if (selectedId && !found && selectedName) {
            opts += `<option value="${selectedId}" selected>${selectedName}</option>`;
        }
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
            // input-group (select + calendar button) ko poora replace karte hain
            $row.find('.input-group').replaceWith(`<input type="text" class="form-control form-control-sm teacher_id" value="-" readonly>`);
        } else {
            $row.find('.subject_id').html(subjectOptionsHtml(data.subject_id, data.subject_name));
            $row.find('.teacher_id').val(data.teacher_id || '');
            // Copy Day ke baad jis row ka teacher us din busy ho
            if (data.busy_flag) {
                $row.find('.teacher_id').addClass('is-invalid');
                $row.find('.error-teacher_id').text('Busy in another class on this day');
            }
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
        markDirty();
    });

    $(document).on('click', '.btnRemoveRow', function() {
        if (periodDataTable) periodDataTable.destroy();
        $(this).closest('tr').remove();
        reindexRows();
        initPeriodDataTable();
        markDirty();
    });

    // Rows render: periods ke beech ka gap = Break row (break DB mein save nahi hota)
    function renderRowsForDay(day) {
        if (periodDataTable) periodDataTable.destroy();
        $('#periodRows').empty();

        const rows = (allData[day] || []).slice().sort((a, b) =>
            normalizeTimeToHi(a.time_from).localeCompare(normalizeTimeToHi(b.time_from)));

        rows.forEach((row, i) => {
            if (i > 0) {
                const prevTo = normalizeTimeToHi(rows[i - 1].time_to);
                const curFrom = normalizeTimeToHi(row.time_from);
                if (prevTo && curFrom && curFrom > prevTo) {
                    addRow({ is_break: true, time_from: prevTo, time_to: curFrom });
                }
            }
            addRow(row);
        });

        initPeriodDataTable();
        refreshBusyTeachers();
    }

    // Time badalne par rows ko time ke hisab se sort karo
    function sortRowsByTime() {
        if (periodDataTable) periodDataTable.destroy();

        const $rows = $('#periodRows tr').get().sort((a, b) => {
            const ta = normalizeTimeToHi($(a).find('.time_from').val()) || '99:99';
            const tb = normalizeTimeToHi($(b).find('.time_from').val()) || '99:99';
            return ta.localeCompare(tb);
        });

        $('#periodRows').append($rows);
        reindexRows();
        initPeriodDataTable();
    }

    function collectCurrentDayRows() {
        // Search filter hidden rows ko DOM se hata deta hai; collect se pehle filter saaf karo
        // (warna tab switch/Save mein sirf visible rows jati hain aur baaqi mit sakti hain)
        if ($.fn.DataTable.isDataTable('#periodTable') && $('#periodSearchInput').val()) {
            $('#periodSearchInput').val('');
            periodDataTable.search('').draw();
        }
        let rows = [];
        $('#periodRows tr').each(function() {
            let $row = $(this);
            if ($row.hasClass('table-warning')) return;
            if ($row.find('.dataTables_empty').length) return; // "No periods added yet" placeholder row
            rows.push({
                subject_id: $row.find('.subject_id').val(),
                subject_name: $row.find('.subject_id option:selected').text(),
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
            if ($row.find('.dataTables_empty').length) return; // "No periods added yet" placeholder row
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

    // Apply: sirf current (khule hue) din ke liye
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
        markDirty();
        refreshBusyTeachers();
        toastr.success('Periods generated');
    });

    // =====================================================================
    // TEACHER AVAILABILITY (clash check + modal)
    // =====================================================================
    const AVAIL_URL = "{{ route('class_timetable.teacher_availability') }}";
    const ALL_DAYS  = @json($days);
    const SCHOOL    = { start: '07:30', periodStart: '07:50' };   // 07:30 assembly, 07:50 se periods
    let availCtx    = { $row: null, teacherId: null, day: null, token: 0, slots: [] };
    let availModal  = null;

    const esc   = s => $('<div>').text(s ?? '').html();
    const toMin = t => { const [h, m] = t.split(':').map(Number); return h * 60 + m; };
    const toHi  = m => String(Math.floor(m / 60)).padStart(2, '0') + ':' + String(m % 60).padStart(2, '0');
    const to12  = t => {
        let [h, m] = t.split(':').map(Number);
        const ap = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')} ${ap}`;
    };

    function getRowTimes($row) {
        return {
            from: normalizeTimeToHi($row.find('.time_from').val()),
            to: normalizeTimeToHi($row.find('.time_to').val())
        };
    }

    function fetchAvailability(teacherId, day, $row) {
        const params = {
            teacher_id: teacherId,
            day: day,
            school_class_id: $('#class_id').val(),
            section_id: $('#section_id').val()
        };
        if (day === currentDay) {
            const t = getRowTimes($row);
            if (t.from && t.to && t.to > t.from) { params.time_from = t.from; params.time_to = t.to; }
        }
        return $.get(AVAIL_URL, params);
    }

    // Fallback: table khali ho to form ke fields se grid banao
    function buildGridFromFields(dur) {
        const items = [];
        const startStr = normalizeTimeToHi($('#period_start').val()) || SCHOOL.periodStart;

        if (toMin(SCHOOL.start) < toMin(startStr)) {
            items.push({ type: 'assembly', from: SCHOOL.start, to: startStr });
        }

        const count    = parseInt($('#period_count').val()) || 6;
        const interval = parseInt($('#interval').val()) || 0;
        const breakAfter = parseInt($('#break_after').val()) || 0;
        const breakDur   = parseInt($('#break_duration').val()) || 0;

        let cur = toMin(startStr);
        for (let i = 1; i <= count; i++) {
            items.push({ type: 'period', no: i, from: toHi(cur), to: toHi(cur + dur) });
            cur += dur;
            if (i < count) {
                if (breakAfter && breakDur && i === breakAfter) {
                    items.push({ type: 'break', from: toHi(cur), to: toHi(cur + breakDur) });
                    cur += breakDur;
                } else {
                    cur += interval;
                }
            }
        }
        return items;
    }

    // Din ka grid: table ki asli rows + breaks se (gap ko bhi break maante hain)
    function buildDayGrid(dur) {
        const rows = [];
        $('#periodRows tr').each(function () {
            const $r = $(this), t = getRowTimes($r);
            if (t.from && t.to && t.to > t.from) {
                rows.push({ $row: $r, from: t.from, to: t.to, isBreak: $r.hasClass('table-warning') });
            }
        });
        if (!rows.length) return buildGridFromFields(dur);

        rows.sort((a, b) => a.from.localeCompare(b.from));
        const items = [];
        if (toMin(SCHOOL.start) < toMin(rows[0].from)) {
            items.push({ type: 'assembly', from: SCHOOL.start, to: rows[0].from });
        }

        let no = 0, prev = null;
        rows.forEach(r => {
            if (prev && !prev.isBreak && !r.isBreak && toMin(r.from) > toMin(prev.to)) {
                items.push({ type: 'break', from: prev.to, to: r.from });
            }
            if (r.isBreak) items.push({ type: 'break', from: r.from, to: r.to });
            else items.push({ type: 'period', no: ++no, from: r.from, to: r.to, $row: r.$row });
            prev = r;
        });
        return items;
    }

    function renderAvailability(res, $row) {
        availCtx.day = res.day;
        $('#ta_name').text(res.teacher.name);

        $('#ta_days').html(ALL_DAYS.map(d =>
            `<button type="button" class="ta-day-pill ${d === res.day ? 'active' : ''}" data-day="${d}">${d}</button>`
        ).join(''));

        const t0 = getRowTimes($row);
        const hasTimes = t0.from && t0.to && t0.to > t0.from;
        const dur = hasTimes ? toMin(t0.to) - toMin(t0.from) : (parseInt($('#duration').val()) || 45);
        const canAssign = res.day === currentDay;
        const tid = String(res.teacher.id);
        const len = o => toMin(o.to) - toMin(o.from);
        const overlap = (a, b) => a.from < b.to && a.to > b.from;

        // ---- Alert ----
        if (res.conflict) {
            const c = res.conflict;
            $('#ta_alert').html(`<div class="alert alert-danger py-2 mb-3">
                <i class='bx bx-error-circle'></i>
                <b>${esc(res.teacher.name)}</b> is already teaching <b>${esc(c.class)} - ${esc(c.section)}</b> (${esc(c.subject)})
                between ${to12(c.from)} and ${to12(c.to)}, which overlaps with ${to12(t0.from)} - ${to12(t0.to)}.
                Please pick one of the available periods below.</div>`);
        } else if (canAssign) {
            $('#ta_alert').html(`<div class="alert alert-success py-2 mb-3"><i class='bx bx-check-circle'></i> The selected time slot is free.</div>`);
        } else {
            $('#ta_alert').html(`<div class="alert alert-info py-2 mb-3"><i class='bx bx-info-circle'></i> View only. Switch to the "${esc(currentDay)}" tab to assign a period.</div>`);
        }

        // ---- Grid + status of every period ----
        const items = buildDayGrid(dur);
        items.forEach(it => {
            if (it.type !== 'period') return;
            const $sel = it.$row ? it.$row.find('select.teacher_id') : null;
            const $sub = it.$row ? it.$row.find('select.subject_id') : null;
            it.busy        = res.busy.find(b => overlap(b, it)) || null;
            it.teacherId   = $sel ? String($sel.val() || '') : '';
            it.teacherName = (it.teacherId && $sel) ? $sel.find('option:selected').text() : '';
            it.subject     = ($sub && $sub.val()) ? $sub.find('option:selected').text() : '';
            it.state       = it.busy ? 'busy' : (it.teacherId === tid ? 'here' : 'free');
        });
        const slots = items.filter(i => i.type === 'period');
        availCtx.slots = slots;
        const busySlots = slots.filter(s => s.state === 'busy');
        const hereSlots = slots.filter(s => s.state === 'here');
        const freeSlots = slots.filter(s => s.state === 'free');
        const sumMin = arr => arr.reduce((s, o) => s + len(o), 0);

        // ---- Window ----
        let ws = toMin(items[0].from);
        let we = toMin(items[items.length - 1].to);
        res.busy.forEach(b => { ws = Math.min(ws, toMin(b.from)); we = Math.max(we, toMin(b.to)); });
        if (canAssign && hasTimes) { ws = Math.min(ws, toMin(t0.from)); we = Math.max(we, toMin(t0.to)); }
        const total = we - ws;
        const pct = m => ((m - ws) / total * 100);
        const seg = (cls, f, t, label, attrs = '', title = '') => {
            const l = pct(toMin(f)), w = pct(toMin(t)) - l;
            return `<div class="ta-seg ${cls}" style="left:${l}%;width:${w}%" title="${esc(title || label)}" ${attrs}>${w > 7 ? esc(label) : ''}</div>`;
        };

        // ---- Timeline ----
        let track = '';
        items.forEach(it => {
            if (it.type === 'assembly') {
                track += seg('assembly', it.from, it.to, 'Assembly', '', `Assembly ${to12(it.from)} - ${to12(it.to)}`);
            } else if (it.type === 'break') {
                track += seg('break', it.from, it.to, 'Break', '', `Break ${to12(it.from)} - ${to12(it.to)}`);
            } else if (it.state === 'busy') {
                track += seg('blocked', it.from, it.to, '', '', `Period ${it.no} ${to12(it.from)} - ${to12(it.to)}: teacher busy`);
            } else if (it.state === 'here') {
                track += seg('own', it.from, it.to, `P${it.no} · here`, '', `Period ${it.no}: already teaching in this class`);
            } else if (canAssign && it.$row) {
                track += seg('free clickable btnAssignPeriod', it.from, it.to, `P${it.no} · ${len(it)}m`,
                    `data-idx="${slots.indexOf(it)}"`, `Period ${it.no}: ${to12(it.from)} - ${to12(it.to)} (free). Click to assign`);
            } else {
                track += seg('free', it.from, it.to, `P${it.no} · ${len(it)}m`, '', `Period ${it.no} (free)`);
            }
        });
        res.busy.forEach(b => track += seg('busy', b.from, b.to, `${b.class}-${b.section}`, '',
            `Busy ${to12(b.from)} - ${to12(b.to)}: ${b.class} - ${b.section} (${b.subject})`));
        if (canAssign && hasTimes) track += seg('current', t0.from, t0.to, '', '', `This period ${to12(t0.from)} - ${to12(t0.to)}`);

        const tickMins = [ws];
        for (let m = Math.ceil((ws + 20) / 60) * 60; m <= we; m += 60) tickMins.push(m);
        const ticks = tickMins.map(m =>
            `<span class="ta-tick" style="left:${pct(m)}%">${to12(toHi(m)).replace(':00', '')}</span>`).join('');

        const pl = n => `${n} period${n === 1 ? '' : 's'}`;
        $('#ta_timeline').html(`
            <div class="ta-sum">
                <span class="ta-chip busy"><i class='bx bx-time'></i> Busy: ${pl(busySlots.length)} &middot; ${sumMin(busySlots)} min</span>
                <span class="ta-chip own"><i class='bx bx-chalkboard'></i> In this class: ${pl(hereSlots.length)}</span>
                <span class="ta-chip free"><i class='bx bx-check-circle'></i> Free: ${pl(freeSlots.length)} &middot; ${sumMin(freeSlots)} min</span>
            </div>
            <div class="ta-track">${track}</div><div class="ta-ticks">${ticks}</div>`);

        // ---- Busy list ----
        $('#ta_busy_list').html(res.busy.length ? res.busy.map(b => {
            const ps = slots.filter(s => overlap(b, s)).map(s => `<span class="ta-pbadge">P${s.no}</span>`).join('');
            return `<div class="ta-item busy">
                <b>${to12(b.from)} - ${to12(b.to)}</b> <span class="text-muted">(${len(b)} min)</span>${ps}
                <small>${esc(b.class)} - ${esc(b.section)} &middot; ${esc(b.subject)}</small>
            </div>`;
        }).join('') : `<div class="text-muted small">No busy periods on this day.</div>`);

        // ---- Available periods list ----
        let html = '';
        items.forEach(it => {
            if (it.type === 'assembly') {
                html += `<div class="ta-item neutral"><i class='bx bx-flag'></i> Assembly &middot; ${to12(it.from)} - ${to12(it.to)}</div>`;
            } else if (it.type === 'break') {
                html += `<div class="ta-item breakrow"><i class='bx bx-coffee'></i> Break &middot; ${to12(it.from)} - ${to12(it.to)} (${len(it)} min)</div>`;
            } else if (it.state === 'here') {
                html += `<div class="ta-item own"><div><b>Period ${it.no} &middot; ${to12(it.from)} - ${to12(it.to)}</b>
                    <small>Already teaching here${it.subject ? ' &middot; ' + esc(it.subject) : ''}</small></div></div>`;
            } else if (it.state === 'free') {
                const replace = !!it.teacherName;
                const btn = (canAssign && it.$row)
                    ? `<button type="button" class="btn btn-sm ${replace ? 'btn-warning' : 'btn-success'} btnAssignPeriod text-nowrap" data-idx="${slots.indexOf(it)}">
                           <i class='bx bx-calendar-check'></i> ${replace ? 'Replace' : 'Assign'}</button>`
                    : '';
                html += `<div class="ta-item free"><div><b>Period ${it.no} &middot; ${to12(it.from)} - ${to12(it.to)}</b>
                    <small>${esc(it.subject || 'No subject selected')} &middot; ${replace ? 'Now: ' + esc(it.teacherName) : 'No teacher yet'}</small></div>${btn}</div>`;
            }
        });
        $('#ta_free_list').html((freeSlots.length || hereSlots.length) ? html : `<div class="text-muted small">No available periods on this day.</div>`);
    }

    function showAvailability(res, $row) {
        renderAvailability(res, $row);
        if (!availModal) availModal = new bootstrap.Modal(document.getElementById('teacherAvailModal'));
        availModal.show();
    }

    function loadAvailability(teacherId, $row, day) {
        availCtx.$row = $row;
        availCtx.teacherId = teacherId;
        const token = ++availCtx.token;
        return fetchAvailability(teacherId, day, $row)
            .done(res => { if (token === availCtx.token) showAvailability(res, $row); })
            .fail(() => toastr.error('Failed to load teacher availability'));
    }

    function checkTeacherClash($row) {
        if ($row.hasClass('table-warning')) return;
        const $sel = $row.find('select.teacher_id');
        const teacherId = $sel.val();
        const t = getRowTimes($row);

        $sel.removeClass('is-invalid');
        $row.find('.error-teacher_id').text('');
        if (!teacherId || !t.from || !t.to || t.to <= t.from) return;

        fetchAvailability(teacherId, currentDay, $row).done(function (res) {
            if (!res.conflict) return;

            const c = res.conflict;
            const msg = `${res.teacher.name} is already assigned to ${c.class} - ${c.section} (${c.subject}) from ${to12(c.from)} to ${to12(c.to)}. This period cannot be assigned at this time.`;

            availCtx.$row = $row;
            availCtx.teacherId = teacherId;

            $sel.val('').addClass('is-invalid');
            $row.find('.error-teacher_id').text('Busy in ' + c.class + ' - ' + c.section);

            toastr.error(msg, 'Teacher Already Assigned', { timeOut: 6000 });
            showAvailability(res, $row);
        });
    }

    $(document).on('change', '#periodRows .teacher_id, #periodRows .time_from, #periodRows .time_to', function () {
        const $row = $(this).closest('tr');
        checkTeacherClash($row);
        if ($(this).hasClass('time_from') || $(this).hasClass('time_to')) sortRowsByTime();
    });

    $(document).on('click', '.btnTeacherSchedule', function () {
        const $row = $(this).closest('tr');
        const teacherId = $row.find('select.teacher_id').val();
        if (!teacherId) { toastr.warning('Please select a teacher first'); return; }
        loadAvailability(teacherId, $row, currentDay);
    });

    $(document).on('click', '.ta-day-pill', function () {
        loadAvailability(availCtx.teacherId, availCtx.$row, $(this).data('day'));
    });

    // Assign: us period ki row mein teacher lagao (time nahi badalta)
    $(document).on('click', '.btnAssignPeriod', function () {
        const slot = availCtx.slots[$(this).data('idx')];
        if (!slot || !slot.$row) return;
        const $t = slot.$row;

        $t.find('select.teacher_id').val(availCtx.teacherId).removeClass('is-invalid');
        $t.find('.error-teacher_id').text('');

        availModal.hide();
        $t[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        $t.addClass('table-info');
        setTimeout(() => $t.removeClass('table-info'), 2000);

        markDirty();
        toastr.success(`${$('#ta_name').text()} assigned to Period ${slot.no} (${to12(slot.from)} - ${to12(slot.to)})`);
    });
    // ===================== END TEACHER AVAILABILITY ======================

    // =====================================================================
    // BUSY TEACHERS (dropdown mein pehle se disable, clash modal fallback rehta hai)
    // =====================================================================
    const BUSY_URL = "{{ route('class_timetable.busy_teachers') }}";
    let busyToken = 0;

    // Row ke teacher options ko enable karo aur "(Busy)" suffix hatao
    function resetTeacherOptions($sel) {
        $sel.find('option').each(function () {
            const $o = $(this);
            if ($o.data('name') === undefined) $o.data('name', $o.text());
            $o.prop('disabled', false).text($o.data('name'));
        });
    }

    function refreshBusyTeachers() {
        const slotSet = {};
        $('#periodRows tr').each(function () {
            const $r = $(this);
            if ($r.hasClass('table-warning') || $r.find('.dataTables_empty').length) return;
            resetTeacherOptions($r.find('select.teacher_id'));
            const t = getRowTimes($r);
            if (t.from && t.to && t.to > t.from) slotSet[t.from + '-' + t.to] = true;
        });

        const slots = Object.keys(slotSet);
        const classId = $('#class_id').val();
        const sectionId = $('#section_id').val();
        if (!slots.length || !classId || !sectionId) return;

        const token = ++busyToken;
        $.get(BUSY_URL, { school_class_id: classId, section_id: sectionId, day: currentDay, slots: slots })
            .done(function (res) {
                if (token !== busyToken || !res.success) return;
                $('#periodRows tr').each(function () {
                    const $r = $(this);
                    if ($r.hasClass('table-warning') || $r.find('.dataTables_empty').length) return;
                    const t = getRowTimes($r);
                    const ids = (t.from && t.to) ? res.busy[t.from + '-' + t.to] : null;
                    if (!ids || !ids.length) return;
                    const $sel = $r.find('select.teacher_id');
                    const selected = String($sel.val() || '');
                    $sel.find('option').each(function () {
                        const $o = $(this);
                        const v = String($o.val());
                        // is row ka selected teacher kabhi disable nahi hota
                        if (!v || v === selected) return;
                        if (ids.map(String).indexOf(v) !== -1) {
                            $o.prop('disabled', true).text($o.data('name') + ' (Busy)');
                        }
                    });
                });
            });
        // fail par chup: save/teacherAvailability ka clash check fallback hai
    }

    $(document).on('change', '#periodRows .time_from, #periodRows .time_to', refreshBusyTeachers);
    // ===================== END BUSY TEACHERS ======================

    // =====================================================================
    // COPY DAY (frontend-only: sirf screen par load + dirty, save har din ka alag)
    // =====================================================================
    const WEEKLY_OFF = @json($weeklyOffDays);   // active year ke weekly off din (khali ho sakta hai)
    let copyModal = null;
    let copyToken = 0;
    let copySrc = [];

    $('#btnCopyDay').on('click', function () {
        if ($('#periodSearchInput').val()) {
            toastr.warning('Clear the search box first, otherwise only the visible periods will be copied');
            return;
        }
        copySrc = collectCurrentDayRows();
        if (!copySrc.length) {
            toastr.warning('' + currentDay + ' has no periods to copy');
            return;
        }

        let html = '';
        ALL_DAYS.forEach(function (d) {
            if (d === currentDay) return;
            const off = WEEKLY_OFF.indexOf(d) !== -1;
            html += `<div class="form-check mb-1">
                <input class="form-check-input copy-day-cb" type="checkbox" id="copy_day_${d}" value="${d}" data-off="${off ? 1 : 0}">
                <label class="form-check-label small fw-bold" for="copy_day_${d}">${d}${off ? ' <span class="text-muted fw-normal">(Weekly Off)</span>' : ''}</label>
            </div>`;
        });
        $('#copyDayList').html(html);
        $('#copy_day_all').prop('checked', false);
        $('#copy_src_day').text(currentDay);

        if (!copyModal) copyModal = new bootstrap.Modal(document.getElementById('copyDayModal'));
        copyModal.show();
    });

    // Select all: weekly off din select nahi hote
    $('#copy_day_all').on('change', function () {
        $('.copy-day-cb').filter(function () { return $(this).data('off') != 1; }).prop('checked', this.checked);
    });

    $('#btnCopyConfirm').on('click', function () {
        const days = $('.copy-day-cb:checked').map(function () { return this.value; }).get();
        if (!days.length) {
            toastr.error('Please select at least one day');
            return;
        }
        // Jin dinon mein pehle se rows hain (saved ya unsaved) unka confirm
        const existing = days.filter(d => savedDays.has(d) || (allData[d] && allData[d].length));
        if (!existing.length) {
            doCopyDays(days);
            return;
        }
        Swal.fire({
            title: 'Overwrite existing periods?',
            html: 'These days already have periods and will be replaced:<br><b>' + existing.join(', ') + '</b>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, overwrite',
        }).then(function (result) {
            if (result.isConfirmed) doCopyDays(days);
        });
    });

    function doCopyDays(days) {
        copyModal.hide();

        const src = copySrc.map(r => ({
            subject_id: r.subject_id,
            subject_name: r.subject_name,
            time_from: normalizeTimeToHi(r.time_from),
            time_to: normalizeTimeToHi(r.time_to),
            teacher_id: r.teacher_id,
        })).sort((a, b) => a.time_from.localeCompare(b.time_from));

        allData[currentDay] = copySrc;
        days.forEach(function (d) {
            allData[d] = src.map(r => $.extend({}, r));   // har din ki apni copy (shared reference nahi)
            dirtyDays.add(d);
        });
        refreshTabMarks();
        toastr.success('Copied to ' + days.join(', ') + '. Not saved yet: review and click Save for each day.', '', { timeOut: 5000 });

        // Har target din ke liye busy-teachers re-check (ek request per din)
        const token = ++copyToken;
        const warns = {};
        let pending = days.length;
        const finish = function () {
            pending--;
            if (pending > 0 || token !== copyToken) return;
            const lines = days.filter(d => warns[d] && warns[d].length).map(d => d + ': ' + warns[d].join(', '));
            if (lines.length) {
                toastr.warning('These copied periods need a different teacher (busy in another class):<br>' + lines.join('<br>'),
                    'Busy teachers', { timeOut: 10000, extendedTimeOut: 5000, escapeHtml: false });
            }
        };

        days.forEach(function (d) {
            const rows = allData[d];
            const slotSet = {};
            rows.forEach(function (r) {
                if (r.teacher_id && r.time_from && r.time_to && r.time_to > r.time_from) slotSet[r.time_from + '-' + r.time_to] = true;
            });
            const slots = Object.keys(slotSet);
            if (!slots.length) { finish(); return; }

            $.get(BUSY_URL, { school_class_id: $('#class_id').val(), section_id: $('#section_id').val(), day: d, slots: slots })
                .done(function (res) {
                    if (!res.success) return;
                    warns[d] = [];
                    rows.forEach(function (r, i) {
                        const ids = res.busy[r.time_from + '-' + r.time_to];
                        if (r.teacher_id && ids && ids.map(String).indexOf(String(r.teacher_id)) !== -1) {
                            r.busy_flag = true;
                            warns[d].push('P' + (i + 1));
                        }
                    });
                })
                .always(finish);   // fail par chup: save() ka clash check fallback hai
        });
    }
    // ===================== END COPY DAY ======================

    // Save: sirf current (khule hue) din ka
    $('#btnSave').on('click', function() {
        clearErrors();
        allData[currentDay] = collectCurrentDayRows();
        let classId = $('#class_id').val();
        let sectionId = $('#section_id').val();

        if (!validateRows()) {
            toastr.error('Please fix highlighted fields before saving');
            return;
        }

        const savingDay = currentDay;
        const periods = allData[savingDay].map(r => ({
            day: savingDay,
            subject_id: r.subject_id,
            teacher_id: r.teacher_id,
            time_from: normalizeTimeToHi(r.time_from),
            time_to: normalizeTimeToHi(r.time_to),
        }));

        // Khali din = Weekly Off. Pehle confirm, phir day_off flag ke saath save
        if (periods.length === 0) {
            Swal.fire({
                title: `Mark ${savingDay} as Weekly Off?`,
                text: 'Is din ke saved periods (agar hain) delete ho jayenge.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, mark off',
            }).then(function(result) {
                if (result.isConfirmed) {
                    sendSave(savingDay, classId, sectionId, [], true);
                }
            });
            return;
        }

        sendSave(savingDay, classId, sectionId, periods, false);
    });

    function sendSave(savingDay, classId, sectionId, periods, isDayOff) {
        let payload = {
            _token: '{{ csrf_token() }}',
            school_class_id: classId,
            section_id: sectionId,
            day: savingDay
        };
        if (isDayOff) {
            payload.day_off = 1;
        } else {
            payload.periods = periods;
        }

        $.ajax({
            url: '/class-timetable/save',
            method: 'POST',
            data: payload,
            success: function(res) {
                toastr.success(res.message || `${savingDay} saved successfully`);
                dirtyDays.delete(savingDay);
                if (isDayOff) {
                    savedDays.delete(savingDay);
                } else {
                    savedDays.add(savingDay);
                }
                refreshTabMarks();
                updateSaveLabel();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let res = xhr.responseJSON;
                    if (res.errors) {
                        const fieldLabels = {
                            subject_id: 'Subject',
                            time_from: 'Time From',
                            time_to: 'Time To',
                            teacher_id: 'Teacher',
                            day: 'Day'
                        };
                        let messages = [];
                        const $dataRows = $('#periodRows tr').not('.table-warning'); // break rows ke baghair

                        $.each(res.errors, function(key, msgs) {
                            let msg = msgs[0];
                            let match = key.match(/^periods\.(\d+)\.(\w+)$/);

                            if (match) {
                                let idx = parseInt(match[1]);
                                let field = match[2];
                                messages.push(`${savingDay} - Period ${idx + 1}: ${fieldLabels[field] || field} ${msg}`);
                                let $row = $dataRows.eq(idx);
                                $row.find(`.${field}`).addClass('is-invalid');
                                $row.find(`.error-${field}`).text(msg);
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
    }
</script>
@endpush