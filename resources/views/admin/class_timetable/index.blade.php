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

        $.get(`/class-timetable/get-data`, {
                school_class_id: classId,
                section_id: sectionId
            })
            .done(function(res) {
                let hasAny = Object.keys(res).length > 0;

                let cardHtml = `<div class="card"><div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Timetable Result</h6>
                    <a href="/class-timetable/create?class_id=${classId}&section_id=${sectionId}" class="btn btn-warning btn-sm">
                        <i class='bx bx-edit'></i> Edit Timetable
                    </a>
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
                        cardHtml += `<div class="tt-day-col" style="--tt-day-color:${color}">
                            <div class="tt-day-header">${day}</div>
                            <div>`;

                        if (res[day] && res[day].length > 0) {
                            res[day].forEach(item => {
                                let room = item.room_no ?? item.room_number ?? item.room ?? '-';
                                cardHtml += `
                            <div class="tt-card">
                                <div class="tt-subject">
                                    <i class='bx bx-book-content'></i>
                                    <span>${item.subject.name}</span>
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
                            </div>`;
                            });
                        } else if (day === 'Sunday') {
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
    });
</script>
@endpush