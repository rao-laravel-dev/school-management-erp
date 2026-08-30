@extends($current_layout)

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/assets/plugins/fullcalendar/css/main.min.css') }}">
<style>
    #calendar{
        width: 100%;
        background: #fff;
        padding: 15px;
    }
    #eventModal .invalid-feedback {
        font-size: 0.75rem;
        margin-top: 0.15rem;
    }
    #eventTypeWrapper.is-invalid-group .form-check {
        border-color: #dc3545 !important;
    }
</style>
@endpush

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academic</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Calendar</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto d-flex gap-2">
        @can('manage-academics')
        <button type="button" class="btn btn-primary btn-sm" id="addEventBtn" data-bs-toggle="modal" data-bs-target="#eventModal">
            <i class="bx bx-plus"></i> Add Event
        </button>
        @endcan
        <a href="{{ route('academic_calendar.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back"></i> Back to List
        </a>
    </div>
</div>

<div class="card radius-10">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

{{-- Same modal jo index page pe use ho raha hai --}}
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="eventForm" method="POST" action="{{ route('academic_calendar.store') }}" novalidate>
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title" id="modalTitle">Add Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label mb-1">Academic Year</label>
                            <select name="academic_year_id" id="academic_year_id" class="form-select form-select-sm">
                                <option value="">Select Academic Year</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->title ?? $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label mb-1">Title</label>
                            <input type="text" name="title" id="title" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label mb-1">Type</label>
                        <div class="d-flex flex-wrap gap-3" id="eventTypeWrapper">
                            @foreach($eventTypes as $type)
                                <div class="form-check form-check-inline border rounded px-2 py-1 m-0">
                                    <input class="form-check-input" type="radio" name="event_type_id"
                                        id="type_{{ $type->id }}" value="{{ $type->id }}">
                                    <label class="form-check-label small" for="type_{{ $type->id }}">
                                        {{ $type->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div id="event_type_id_error"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label mb-1">From Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label mb-1">To Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label mb-1">Description</label>
                        <textarea name="description" id="description" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="submit" class="btn btn-success btn-sm" id="saveBtn">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/plugins/fullcalendar/js/main.min.js') }}"></script>
<script>
$(document).ready(function() {
    var canManage         = @json($canManage);
    var storeUrl           = "{{ route('academic_calendar.store') }}";
    var updateUrlTemplate  = "{{ route('academic_calendar.update', ':id') }}";

    var $eventForm  = $('#eventForm');
    var eventModal  = new bootstrap.Modal(document.getElementById('eventModal'));

    // friendly labels for field names shown in toastr
    const fieldLabels = {
        academic_year_id: 'Academic Year',
        title: 'Title',
        event_type_id: 'Type',
        start_date: 'From Date',
        end_date: 'To Date',
        description: 'Description'
    };

    function resetValidation() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#eventTypeWrapper').removeClass('is-invalid-group');
        $('#event_type_id_error').empty();
    }

    function resetForm() {
        $eventForm[0].reset();
        resetValidation();
        $eventForm.attr('action', storeUrl);
        $('#modalTitle').text('Add Event');
        $('#saveBtn').text('Save');
    }

    // clear a field's own error as user edits it
    $(document).on('input change', '.form-control, .form-select, input[name="event_type_id"]', function() {
        $(this).removeClass('is-invalid');
        $(this).closest('.mb-2, .col-md-6').find('.invalid-feedback').remove();
        $('#eventTypeWrapper').removeClass('is-invalid-group');
        $('#event_type_id_error').empty();
    });

    // "+ Add Event" button — plain add mode
    $('#addEventBtn').on('click', function() {
        resetForm();
    });

    // Modal band hone pe form reset (taake purana data next "Add" pe na dikhe)
    $('#eventModal').on('hidden.bs.modal', resetForm);

    // Save / Update — AJAX submit with validation
    $eventForm.on('submit', function(e) {
        e.preventDefault();
        resetValidation();

        let originalBtnText = $('#saveBtn').text();
        $('#saveBtn').prop('disabled', true);

        $.ajax({
            url: $eventForm.attr('action'),
            type: 'POST',
            data: $eventForm.serialize(),
            success: function(response) {
                if (response.status) {
                    toastr.success(response.message);
                    eventModal.hide();
                    setTimeout(() => location.reload(), 800);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function(key, value) {
                        if (key === 'event_type_id') {
                            $('#eventTypeWrapper').addClass('is-invalid-group');
                            $('#event_type_id_error').html(
                                '<div class="text-danger small mt-1">' + value[0] + '</div>'
                            );
                        } else {
                            let input = $('#' + key);
                            if (input.length) {
                                input.addClass('is-invalid');
                                input.after('<div class="invalid-feedback text-danger d-block">' + value[0] + '</div>');
                            }
                        }
                    });

                    let firstKey   = Object.keys(errors)[0];
                    let firstLabel = fieldLabels[firstKey] || firstKey;
                    toastr.error(errors[firstKey][0], firstLabel);
                } else {
                    toastr.error('Something went wrong, please try again!');
                }
            },
            complete: function() {
                $('#saveBtn').prop('disabled', false).text(originalBtnText);
            }
        });
    });

    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        initialView: 'dayGridMonth',
        navLinks: true,
        selectable: canManage,
        nowIndicator: true,
        editable: false,
        dayMaxEvents: true,
        events: @json($events),

        // Kisi bhi date pe click -> Add Event modal (sirf permission wale user ke liye)
        dateClick: function(info) {
            if (!canManage) return;
            resetForm();
            $('#start_date').val(info.dateStr);
            $('#end_date').val(info.dateStr);
            eventModal.show();
        },

        // Kisi bhi event pe click -> Edit modal (sirf permission wale user ke liye)
        eventClick: function(info) {
            if (!canManage) {
                toastr.info(info.event.title + ' (' + info.event.startStr + ')');
                return;
            }

            resetValidation();

            var props = info.event.extendedProps;

            $('#modalTitle').text('Edit Event');
            $('#saveBtn').text('Update');
            $('#title').val(info.event.title);
            $('#academic_year_id').val(props.academic_year_id);
            $('#start_date').val(props.start_date);
            $('#end_date').val(props.end_date);
            $('#description').val(props.description ?? '');

            $('input[name="event_type_id"]').each(function () {
                $(this).prop('checked', $(this).val() == props.event_type_id);
            });

            $eventForm.attr('action', updateUrlTemplate.replace(':id', info.event.id));
            eventModal.show();
        }
    });

    calendar.render();
});
</script>
@endpush