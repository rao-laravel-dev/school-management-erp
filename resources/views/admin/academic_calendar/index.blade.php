@extends($current_layout)

@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academic</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Academic Calendar</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card radius-10">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Academic Calendar</h5>
          <div class="d-flex gap-2">
            <a href="{{ route('academic_calendar.calendar') }}" class="btn btn-outline-warning btn-sm">
                <i class="bx bx-calendar"></i> View Calendar
            </a>
            <button class="btn btn-primary btn-sm" id="addBtn" data-bs-toggle="modal" data-bs-target="#eventModal">
                + Add Event
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle" id="calendarTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>Description</th>
                        <th>Created By</th>
                        <th style="width: 120px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row->title }}</td>
                        <td>
                            <span class="badge rounded-pill px-3" style="background: {{ $row->eventType->color ?? '#ccc' }};">
                                {{ $row->eventType->name ?? '-' }}
                            </span>
                        </td>
                        <td>{{ $row->start_date->format('d M Y') }}</td>
                        <td>{{ $row->end_date?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $row->description ?? '-' }}</td>
                        <td>{{ $row->creator->name ?? '-' }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-warning editBtn px-3"
                                data-id="{{ $row->id }}"
                                data-academic_year_id="{{ $row->academic_year_id }}"
                                data-title="{{ $row->title }}"
                                data-event_type_id="{{ $row->event_type_id }}"
                                data-start_date="{{ $row->start_date->format('Y-m-d') }}"
                                data-end_date="{{ $row->end_date?->format('Y-m-d') }}"
                                data-description="{{ $row->description }}"
                                data-url="{{ route('academic_calendar.update', $row->id) }}">
                                <i class="bx bx-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger px-3 delete-btn"
                                data-url="{{ route('academic_calendar.delete', $row->id) }}">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="eventForm" method="POST" action="{{ route('academic_calendar.store') }}" novalidate>
            @csrf
            <div class="modal-content">
                <div class="modal-header  bg-warning">
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
                        <div class="d-flex flex-wrap gap-2" id="event_type_wrapper">
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
                        {{-- server error for the radio group goes here --}}
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
<script>
$(document).ready(function() {
    $('#calendarTable').DataTable();

    // friendly labels for field names shown in toastr
    const fieldLabels = {
        academic_year_id: 'Academic Year',
        title: 'Title',
        event_type_id: 'Type',
        start_date: 'From Date',
        end_date: 'To Date',
        description: 'Description'
    };

    // clear a single field's error state as user types/changes it
    $(document).on('input change', '.form-control, .form-select, input[name="event_type_id"]', function() {
        $(this).removeClass('is-invalid');
        $(this).closest('.mb-2, .col-md-6').find('.invalid-feedback').remove();
        $('#event_type_wrapper').removeClass('is-invalid-group');
        $('#event_type_id_error').empty();
    });

    function resetValidation() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#event_type_wrapper').removeClass('is-invalid-group');
        $('#event_type_id_error').empty();
    }

    // Add button — reset form, set to "Save" mode
    $('#addBtn').click(function() {
        $('#eventForm')[0].reset();
        resetValidation();
        $('#modalTitle').text('Add Event');
        $('#saveBtn').text('Save');
        $('#eventForm').attr('action', "{{ route('academic_calendar.store') }}");
    });

    // Edit button — fill form, set to "Update" mode
    $('.editBtn').click(function() {
        resetValidation();
        $('#modalTitle').text('Edit Event');
        $('#saveBtn').text('Update');
        $('#academic_year_id').val($(this).data('academic_year_id'));
        $('#title').val($(this).data('title'));
        $('input[name="event_type_id"][value="' + $(this).data('event_type_id') + '"]').prop('checked', true);
        $('#start_date').val($(this).data('start_date'));
        $('#end_date').val($(this).data('end_date'));
        $('#description').val($(this).data('description'));
        $('#eventForm').attr('action', $(this).data('url'));

        var modal = new bootstrap.Modal(document.getElementById('eventModal'));
        modal.show();
    });

    // reset validation state whenever modal is closed
    $('#eventModal').on('hidden.bs.modal', function() {
        resetValidation();
    });

    // Submit (Add or Edit — dono POST hain)
    $('#eventForm').submit(function(e) {
        e.preventDefault();
        let form = $(this);
        resetValidation();

        let originalBtnText = $('#saveBtn').text();
        $('#saveBtn').prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.status) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function(key, value) {
                        if (key === 'event_type_id') {
                            $('#event_type_wrapper').addClass('is-invalid-group');
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

                    // first error field name shown as toastr title
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

    // Delete
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        let url = $(this).data('url');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1000);
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Error deleting record!';
                        toastr.error(errorMsg);
                    }
                });
            }
        });
    });
});
</script>

<style>
    #event_type_wrapper.is-invalid-group .form-check {
        border-color: #dc3545 !important;
    }
    #eventModal .modal-body {
        padding: 1rem 1.25rem;
    }
    #eventModal .form-label {
        font-size: 0.85rem;
        font-weight: 500;
    }
    #eventModal .form-control-sm,
    #eventModal .form-select-sm {
        font-size: 0.85rem;
        padding: 0.35rem 0.6rem;
    }
    #eventModal .invalid-feedback {
        font-size: 0.75rem;
        margin-top: 0.15rem;
    }
    #eventModal .form-check-inline {
        min-height: auto;
    }
</style>
@endpush