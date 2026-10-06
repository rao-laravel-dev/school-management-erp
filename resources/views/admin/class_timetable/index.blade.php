@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Academic', 'url' => '#'],
    ['label' => 'Class Timetable', 'url' => route('class_timetable.index')],
]" />

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Select Criteria</h5>
        @can('manage-class-timetable')
        <a href="{{ route('class_timetable.create') }}" class="btn btn-primary btn-sm">
            <i class='bx bx-plus'></i> Add
        </a>
        @endcan
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
                <label>Subject Group</label>
                <select id="group_id" class="form-select form-select-sm">
                    <option value="">Select</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card-footer text-end">
        <button id="btnSearch" class="btn btn-primary btn-sm">Search</button>
    </div>
</div>

<div id="resultWrapper" class="mt-3"></div>

@endsection

@push('styles')
<style>
    .tt-week-wrapper {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 12px;
    }

    .tt-day-col {
        min-width: 0;
    }

    .tt-card {
        border: 1px solid #e5e7eb;
        border-left: 4px solid var(--tt-day-color, #6c757d);
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 10px;
        background: #fff;
    }

    .tt-card .tt-subject {
        font-weight: 600;
        font-size: 13px;
        color: #212529;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .tt-card .tt-meta {
        font-size: 12px;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 4px;
    }

    .tt-card .tt-meta i {
        font-size: 14px;
        color: var(--tt-day-color, #6c757d);
    }

    .tt-card .tt-subject i {
        color: var(--tt-day-color, #6c757d);
        font-size: 15px;
    }

    .tt-not-scheduled {
        border: 1px dashed #dc3545;
        border-left: 4px solid #dc3545;
        border-radius: 8px;
        padding: 14px 12px;
        text-align: center;
        color: #dc3545;
        font-size: 12px;
        font-weight: 500;
        background: #fff5f5;
    }

    .tt-not-scheduled i {
        font-size: 16px;
        display: block;
        margin-bottom: 4px;
    }

    .tt-actions {
        display: flex;
        justify-content: flex-end;
        gap: 6px;
        margin-top: 8px;
    }

    .tt-day-header {
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 10px;
        padding-bottom: 6px;
        border-bottom: 2px solid var(--tt-day-color, #6c757d);
        color: var(--tt-day-color, #6c757d);
    }
</style>
@endpush

@push('scripts')
<script>
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: 3000
    };

    const dayColors = {
        'Monday': '#0d6efd',
        'Tuesday': '#198754',
        'Wednesday': '#fd7e14',
        'Thursday': '#6610f2',
        'Friday': '#dc3545',
        'Saturday': '#0dcaf0',
        'Sunday': '#6c757d'
    };

    // Manage permission (button sirf UI ke liye; asli rok route middleware `can:manage-class-timetable` hai)
    @can('manage-class-timetable')
    const canManageTT = true;
    @else
    const canManageTT = false;
    @endcan
    const editBaseUrl = "{{ route('class_timetable.create') }}";
    const deleteUrlTpl = "{{ route('class_timetable.delete', ':id') }}";
    let currentWeek = ''; // abhi dikhaya ja raha hafta (delete ke baad usi par refresh)

    function clearErrors() {
        $('.form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    function showFieldError(id, msg) {
        $(`#${id}`).addClass('is-invalid');
        $(`#err_${id}`).text(msg);
    }

    $('#class_id').on('change', function() {
        let classId = $(this).val();
        $('#section_id').html('<option value="">Loading...</option>');
        $('#groupWrapper').hide();

        if (!classId) {
            $('#section_id').html('<option value="">Select Class First</option>');
            return;
        }

        $.get(`/class-timetable/get-sections/${classId}`)
            .done(function(res) {
                let opts = '<option value="">Select</option>';
                if (res.length === 0) opts = '<option value="">No sections found</option>';
                res.forEach(s => opts += `<option value="${s.id}">${s.name}</option>`);
                $('#section_id').html(opts);
            })
            .fail(function() {
                toastr.error('Failed to load sections');
            });

        $.get(`/class-timetable/get-groups/${classId}`)
            .done(function(res) {
                if (res.length > 0) {
                    let opts = '<option value="">Select</option>';
                    res.forEach(g => opts += `<option value="${g.id}">${g.name}</option>`);
                    $('#group_id').html(opts);
                    $('#groupWrapper').show();
                }
            });
    });

    $('#btnSearch').on('click', function() {
        clearErrors();
        let classId = $('#class_id').val();
        let sectionId = $('#section_id').val();
        let hasError = false;

        if (!classId) {
            showFieldError('class_id', 'Class is required');
            hasError = true;
        }
        if (!sectionId) {
            showFieldError('section_id', 'Section is required');
            hasError = true;
        }
        if (hasError) {
            toastr.error('Please fill required fields');
            return;
        }

        loadClassWeek(classId, sectionId, '');
    });

    // Prev / This Week / Next buttons (week = us hafte ki koi bhi date, khali = aaj)
    $(document).on('click', '.tt-week-nav', function() {
        loadClassWeek($('#class_id').val(), $('#section_id').val(), $(this).data('week') || '');
    });

    // Period delete: SweetAlert confirm, phir usi hafte ka card refresh
    $(document).on('click', '.tt-delete', function() {
        let id = $(this).data('id');
        let day = $(this).data('day');
        let classId = $('#class_id').val();
        let sectionId = $('#section_id').val();

        Swal.fire({
            title: 'Delete this period?',
            text: `Delete this period from the ${day} timetable? It will be removed from EVERY ${day}, not only this date. Linked lesson plans will lose their timetable link.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: deleteUrlTpl.replace(':id', id),
                type: 'DELETE'
            }).done(function(res) {
                toastr.success(res.message || 'Period deleted');
                loadClassWeek(classId, sectionId, currentWeek);
            }).fail(function(xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to delete period');
                // 404 (kisi aur ne pehle hi delete kar diya) par bhi card taza karo
                loadClassWeek(classId, sectionId, currentWeek);
            });
        });
    });

    function loadClassWeek(classId, sectionId, week) {
        currentWeek = week || '';
        $.get(`/class-timetable/get-data`, {
                school_class_id: classId,
                section_id: sectionId,
                week: week || '{{ now()->toDateString() }}'
            })
            .done(function(data) {
                let res = data.timetable || {};
                let dayStatus = data.day_status || {};
                let wk = data.week || {};
                let hasAny = Object.keys(res).length > 0;
                // Class/Section ka naam selected dropdown se (escaped)
                let className = $('<div>').text($('#class_id option:selected').text()).html();
                let sectionName = $('<div>').text($('#section_id option:selected').text()).html();

                let cardHtml = `<div class="card"><div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Timetable Result</h6>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm tt-week-nav" data-week="${wk.prev}"><i class='bx bx-chevron-left'></i></button>
                        <span class="small fw-bold">${wk.label}</span>
                        <button type="button" class="btn btn-outline-secondary btn-sm tt-week-nav" data-week="">This Week</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm tt-week-nav" data-week="${wk.next}"><i class='bx bx-chevron-right'></i></button>
                    </div>
                    ${canManageTT ? `<a href="/class-timetable/create?class_id=${classId}&section_id=${sectionId}" class="btn btn-warning btn-sm">
                        <i class='bx bx-edit'></i> Edit Timetable
                    </a>` : ''}
                </div><div class="card-body">`;

                if (!hasAny) {
                    cardHtml += `
        <div class="alert alert-warning text-center mb-0" role="alert">
            <i class='bx bx-info-circle'></i> No timetable found for this class-section.
        </div>`;
                } else {
                    cardHtml += `<div class="tt-week-wrapper">`;
                    ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'].forEach(day => {
                        let color = dayColors[day];
                        let st = dayStatus[day] || {};
                        cardHtml += `<div class="tt-day-col" style="--tt-day-color:${color}">
                            <div class="tt-day-header">${day} <small class="fw-normal">${st.date_label || ''}</small></div>
                            <div>`;

                        if (st.status === 'periods' && res[day] && res[day].length > 0) {
                            res[day].forEach(item => {
                                let room = item.assigned_room_no || '-';
                                cardHtml += `
                            <div class="tt-card">
                                <div class="tt-subject">
                                    <i class='bx bx-book-content'></i>
                                    <span>${item.subject.name}</span>
                                </div>
                                <div class="tt-meta">
                                    <i class='bx bx-chalkboard'></i>
                                    <span>Class: ${className}</span>
                                </div>
                                <div class="tt-meta">
                                    <i class='bx bx-collection'></i>
                                    <span>Section: ${sectionName}</span>
                                </div>
                                <div class="tt-meta">
                                    <i class='bx bx-time-five'></i>
                                    <span>${item.time_from_formatted} - ${item.time_to_formatted}</span>
                                </div>
                                <div class="tt-meta">
                                    <i class='bx bx-user'></i>
                                    <span>${item.teacher.name}</span>
                                </div>
                                <div class="tt-meta">
                                    <i class='bx bx-door-open'></i>
                                    <span>Room No.: ${room}</span>
                                </div>
                                ${canManageTT ? `<div class="tt-actions">
                                    <a href="${editBaseUrl}?class_id=${encodeURIComponent(classId)}&section_id=${encodeURIComponent(sectionId)}&day=${encodeURIComponent(day)}" class="btn btn-outline-warning btn-sm" title="Edit ${day} timetable"><i class='bx bx-edit'></i></a>
                                    <button type="button" class="btn btn-outline-danger btn-sm tt-delete" data-id="${parseInt(item.id, 10)}" data-day="${day}" title="Delete period"><i class='bx bx-trash'></i></button>
                                </div>` : ''}
                            </div>`;
                            });
                        } else if (st.status === 'weekly_off') {
                            cardHtml += `
                            <div class="tt-not-scheduled" style="border-color:#6c757d;color:#6c757d;background:#f8f9fa;">
                                <i class='bx bx-moon'></i>
                                Weekly Off
                            </div>`;
                        } else {
                            cardHtml += `
                            <div class="tt-not-scheduled">
                                <i class='bx bx-x-circle'></i>
                                Not Scheduled
                                ${st.label ? '<div class="small">' + $('<div>').text(st.label).html() + '</div>' : ''}
                            </div>`;
                        }

                        cardHtml += `</div></div>`;
                    });
                    cardHtml += `</div>`;
                }

                cardHtml += `</div></div>`;
                $('#resultWrapper').html(cardHtml);
            })
            .fail(function() {
                toastr.error('Failed to load timetable');
            });
    }
</script>
@endpush