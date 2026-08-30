@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Site Setup', 'url' => '#'],
    ['label' => 'School Timing', 'url' => route('school_timing.index')],
]" />

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semi-bold text-primary">School Timings</h5>
        @can('manage-school-timing')
        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addTimingModal">
            <i class='bx bx-plus'></i> Add
        </button>
        @endcan
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="timingsTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Season</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Late Grace (min)</th>
                        <th>Status</th>
                        @can('manage-school-timing')
                        <th>Action</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @foreach($timings as $index => $timing)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $timing->season_name }}</td>
                        <td>{{ \Carbon\Carbon::parse($timing->start_time)->format('g:i A') }}</td>
                        <td>{{ \Carbon\Carbon::parse($timing->end_time)->format('g:i A') }}</td>
                        <td>{{ $timing->late_grace_minutes }}</td>
                        <td>
                            @if($timing->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        @can('manage-school-timing')
                        <td>
                            @if(!$timing->is_active)
                            <button class="btn btn-sm btn-outline-success px-3 set-active-btn" data-id="{{ $timing->id }}">
                                <i class='bx bx-check-circle'></i> Set Active
                            </button>
                            @endif
                            <button class="btn btn-sm btn-outline-primary edit-btn px-3"
                                data-id="{{ $timing->id }}"
                                data-season_name="{{ $timing->season_name }}"
                                data-start_time="{{ \Carbon\Carbon::parse($timing->start_time)->format('H:i') }}"
                                data-end_time="{{ \Carbon\Carbon::parse($timing->end_time)->format('H:i') }}"
                                data-late_grace_minutes="{{ $timing->late_grace_minutes }}">
                                <i class='bx bx-edit'></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger status-btn px-3" data-id="{{ $timing->id }}">
                                <i class='bx bx-{{ $timing->status ? 'x-circle' : 'check' }}'></i>
                            </button>
                        </td>
                        @endcan
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('admin.school_timing.modals')
@endsection

@push('scripts')
<script>
    $(function() {

        let table = $('#timingsTable').DataTable({
            @can('manage-school-timing')
            columnDefs: [{
                orderable: false,
                targets: 6
            }],
            @endcan
            language: {
                emptyTable: `<div class="alert alert-danger d-flex align-items-center justify-content-center text-center mb-0" role="alert"><i class='bx bx-error-circle me-2'></i><span>No school timings added yet</span></div>`
            }
        });

        $('#addTimingModal').on('hidden.bs.modal', function() {
            $('#addTimingForm')[0].reset();
            $('#addTimingForm .is-invalid').removeClass('is-invalid');
            $('#addTimingForm .invalid-feedback').remove();
        });

        $('#addTimingForm').on('submit', function(e) {
            e.preventDefault();
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').remove();

            $.ajax({
                url: "{{ route('school_timing.store') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#addTimingModal').modal('hide');
                    toastr.success(res.message);
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let firstMessage = null;
                        $.each(errors, function(key, value) {
                            let $field = $(`#addTimingForm [name="${key}"]`);
                            $field.addClass('is-invalid');
                            $field.siblings('.invalid-feedback').remove();
                            $field.after(`<div class="invalid-feedback d-block">${value[0]}</div>`);
                            if (firstMessage === null) {
                                firstMessage = value[0];
                            }
                        });
                        toastr.error(firstMessage);
                    } else {
                        toastr.error('Something went wrong!');
                    }
                }
            });
        });

        // Clear invalid state as soon as the user starts fixing a field
        $(document).on('input change', '#addTimingForm .is-invalid, #editTimingForm .is-invalid', function() {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        });

        // Edit Modal: open + prefill (from data attributes, no extra AJAX call)
        $(document).on('click', '.edit-btn', function() {
            $('#editTimingForm').find('.is-invalid').removeClass('is-invalid');
            $('#editTimingForm').find('.invalid-feedback').remove();
            $('#edit_id').val($(this).data('id'));
            $('#editSeasonName').val($(this).data('season_name'));
            $('#editStartTime').val($(this).data('start_time'));
            $('#editEndTime').val($(this).data('end_time'));
            $('#editGraceMinutes').val($(this).data('late_grace_minutes'));
            $('#editTimingModal').modal('show');
        });

        // Edit Modal: submit
        $('#editTimingForm').on('submit', function(e) {
            e.preventDefault();
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').remove();

            let id = $('#edit_id').val();
            let url = "{{ route('school_timing.update', ':id') }}".replace(':id', id);

            $.ajax({
                url: url,
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#editTimingModal').modal('hide');
                    toastr.success(res.message);
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let firstMessage = null;
                        $.each(errors, function(key, value) {
                            let $field = $(`#editTimingForm [name="${key}"]`);
                            $field.addClass('is-invalid');
                            $field.siblings('.invalid-feedback').remove();
                            $field.after(`<div class="invalid-feedback d-block">${value[0]}</div>`);
                            if (firstMessage === null) {
                                firstMessage = value[0];
                            }
                        });
                        toastr.error(firstMessage);
                    } else {
                        toastr.error('Something went wrong!');
                    }
                }
            });
        });

        // Set Active
        $(document).on('click', '.set-active-btn', function() {
            let id = $(this).data('id');
            let url = "{{ route('school_timing.set_active', ':id') }}".replace(':id', id);

            $.ajax({
                url: url,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    toastr.success(res.message);
                    location.reload();
                },
                error: function() {
                    toastr.error('Failed to set active timing');
                }
            });
        });

        // Status toggle (activate/deactivate) with confirmation
        $(document).on('click', '.status-btn', function() {
            let id = $(this).data('id');
            let url = "{{ route('school_timing.status', ':id') }}".replace(':id', id);

            Swal.fire({
                title: 'Are you sure?',
                text: "Ye School Timing ka status badal dega.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, do it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            toastr.success(res.message);
                            location.reload();
                        },
                        error: function() {
                            toastr.error('Failed to update status');
                        }
                    });
                }
            });
        });

    });
</script>
@endpush