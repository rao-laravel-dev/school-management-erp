@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => $dashboardUrl],
    ['label' => 'Academic', 'url' => '#'],
    ['label' => 'Class Schedule', 'url' => ''],
]" />

@isset($activeChild)
    @include('parent.partials.child_switcher')
@endisset

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

    // Prev / This Week / Next buttons (week = any date of that week, empty = today)
    $(document).on('click', '.tt-week-nav', function() {
        loadTimetable($(this).data('week') || '');
    });

    function esc(text) {
        return $('<div>').text(text || '').html();
    }

    function loadTimetable(week) {
        // parent page passes ':id' placeholder + child id; student page passes a plain url
        $.get(@json($scheduleDataUrl).replace(':id', @json($scheduleStudentId)), {
                week: week || '{{ now()->toDateString() }}'
            })
            .done(function(data) {
                let res = data.timetable || {};
                let dayStatus = data.day_status || {};
                let wk = data.week || {};
                let hasAny = Object.keys(res).length > 0;
                let cardHtml = `<div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h6 class="mb-0">${esc(@json($scheduleTitle))}</h6>
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
                                let subject = item.subject ? item.subject.name : '-';
                                let teacher = item.teacher ? (item.teacher.first_name + ' ' + item.teacher.last_name) : '-';
                                cardHtml += `
                                <div class="tt-card">
                                    <div class="tt-subject">
                                        <i class='bx bx-book-content'></i>
                                        <span>${esc(subject)}</span>
                                    </div>
                                    <div class="tt-meta">
                                        <i class='bx bx-user'></i>
                                        <span>${esc(teacher)}</span>
                                    </div>
                                    <div class="tt-meta">
                                        <i class='bx bx-time-five'></i>
                                        <span>${item.time_from_formatted || item.time_from} - ${item.time_to_formatted || item.time_to}</span>
                                    </div>
                                    <div class="tt-meta">
                                        <i class='bx bx-door-open'></i>
                                        <span>Room No.: ${esc(room)}</span>
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
                                ${st.label ? '<div class="small">' + esc(st.label) + '</div>' : ''}
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
        loadTimetable();
    });
</script>
@endpush
