@extends($current_layout)
@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Academic', 'url' => '#'],
    ['label' => 'Teacher Timetable', 'url' => route('teacher_timetable.index')],
]" />

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Select Criteria</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label>Teachers <span class="text-danger">*</span></label>
                <select id="teacher_id" class="form-select form-select-sm">
                    <option value="">Select</option>
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback" id="err_teacher_id"></div>
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <button id="btnSearch" class="btn btn-primary btn-sm">Search</button>
    </div>
</div>

{{-- Teacher Info --}}
<div class="card mt-3" id="teacherInfoCard" style="display:none;">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                <img id="ti_photo" src="" class="rounded-circle" width="70" height="70" style="object-fit:cover;">
            </div>
            <div class="col">
                <div class="row">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Name</small>
                        <span id="ti_name" class="fw-semi-bold"></span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Father Name</small>
                        <span id="ti_father_name" class="fw-semi-bold"></span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Assigned Class</small>
                        <span id="ti_class" class="fw-semi-bold"></span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Phone</small>
                        <span id="ti_phone" class="fw-semi-bold"></span>
                    </div>
                </div>
            </div>
        </div>
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

    function loadTimetable(teacherId) {
        let url = "{{ route('teacher_timetable.get_data', ':id') }}";
        url = url.replace(':id', teacherId);

        $.get(url)
            .done(function(res) {
                let hasAny = Object.keys(res).length > 0;
                let cardHtml = `<div class="card"><div class="card-header"><h6 class="mb-0">Timetable Result</h6></div><div class="card-body">`;

                if (!hasAny) {
                    cardHtml += `
                    <div class="alert alert-warning text-center mb-0" role="alert">
                        <i class='bx bx-info-circle'></i> No timetable found.
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
                                        <i class='bx bx-chalkboard'></i>
                                        <span>Class: ${item.school_class.name}</span>
                                    </div>
                                    <div class="tt-meta">
                                        <i class='bx bx-collection'></i>
                                        <span>Section: ${item.section.name}</span>
                                    </div>
                                    <div class="tt-meta">
                                        <i class='bx bx-time-five'></i>
                                        <span>${item.time_from_formatted ?? item.time_from} - ${item.time_to_formatted ?? item.time_to}</span>
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
    }

    $('#teacher_id').on('change', function() {
        let teacherId = $(this).val();
        if (!teacherId) {
            $('#teacherInfoCard').hide();
            return;
        }

        $.get(`/teacher-timetable/get-teacher-info/${teacherId}`)
            .done(function(info) {
                $('#ti_photo').attr('src', info.photo);
                $('#ti_name').text(info.name || '-');
                $('#ti_father_name').text(info.father_name || '-');
                $('#ti_phone').text(info.phone || '-');
                $('#ti_class').text(info.classes && info.classes.length ? info.classes.join(', ') : '-');
                $('#teacherInfoCard').show();
            })
            .fail(function() {
                toastr.error('Failed to load teacher info');
                $('#teacherInfoCard').hide();
            });
    });

    $('#btnSearch').on('click', function() {
        clearErrors();
        let teacherId = $('#teacher_id').val();
        if (!teacherId) {
            showFieldError('teacher_id', 'Teacher is required');
            toastr.error('Please select a teacher');
            return;
        }
        loadTimetable(teacherId);
    });
</script>
@endpush