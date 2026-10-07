@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Academic', 'url' => '#'],
    ['label' => 'My Timetable', 'url' => '#'],
]" />

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

    // Prev / This Week / Next buttons (week = us hafte ki koi bhi date, khali = aaj)
    $(document).on('click', '.tt-week-nav', function() {
        loadTimetable("{{ $ownTeacherId }}", $(this).data('week') || '');
    });

    function loadTimetable(teacherId, week) {
        let url = "{{ route('teacher.timetable.get_data', ':id') }}";
        url = url.replace(':id', teacherId);

        $.get(url, {
                week: week || '{{ now()->toDateString() }}'
            })
            .done(function(data) {
                let res = data.timetable || {};
                let dayStatus = data.day_status || {};
                let wk = data.week || {};
                let hasAny = Object.keys(res).length > 0;
                let cardHtml = `<div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h6 class="mb-0">Timetable Result</h6>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm tt-week-nav" data-week="${wk.prev}"><i class='bx bx-chevron-left'></i></button>
                        <span class="small fw-bold">${wk.label}</span>
                        <button type="button" class="btn btn-outline-secondary btn-sm tt-week-nav" data-week="">This Week</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm tt-week-nav" data-week="${wk.next}"><i class='bx bx-chevron-right'></i></button>
                    </div></div><div class="card-body">`;

                if (!hasAny) {
                    cardHtml += `
                    <div class="alert alert-warning text-center mb-0" role="alert">
                        <i class='bx bx-info-circle'></i> No timetable found.
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

    $(document).ready(function() {
        loadTimetable("{{ $ownTeacherId }}");
    });
</script>
@endpush