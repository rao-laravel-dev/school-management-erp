@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academic</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Academic Year / Sessions</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-3 g-3 mb-4">

    {{-- Total Academic Sessions --}}
    <div class="col">
        <div class="card radius-10 mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary small">
                            📅 Total Academic Sessions
                        </p>

                        <h5 class="my-0 text-primary fw-bold">
                            {{ $academic_years->count() }}
                        </h5>
                    </div>

                    <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto"
                        style="width:45px;height:45px;line-height:45px;">
                        <i class='bx bx-calendar'></i>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Current Session --}}
    <div class="col">
        <div class="card radius-10 mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div>

                        <p class="mb-0 text-secondary small">
                            ⭐ Current Academic Session
                        </p>

                        <h5 class="my-0 text-success fw-bold">
                            {{ $academic_years->where('is_current',1)->count() }}
                        </h5>

                    </div>

                    <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto"
                        style="width:45px;height:45px;line-height:45px;">
                        <i class='bx bxs-star'></i>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Archived Sessions --}}
    <div class="col">
        <div class="card radius-10 mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">

                    <div>

                        <p class="mb-0 text-secondary small">
                            📁 Archived Sessions
                        </p>

                        <h5 class="my-0 text-danger fw-bold">
                            {{ $academic_years->where('is_current',0)->count() }}
                        </h5>

                    </div>

                    <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto"
                        style="width:45px;height:45px;line-height:45px;">
                        <i class='bx bx-archive'></i>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<div class="row">
    @can('manage-academics')
    <div class="col-xl-4 col-lg-5">
        <div class="card radius-10" id="form-card-wrapper">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0 card-title-text fw-bold text-dark dark-readable">Add New Session</h5>
                <p class="mb-0 small text-muted card-subtitle-text">Configure a new active school cycle</p>
            </div>

            <div class="card-body">
                <form action="{{ route('academic_years.store') }}" method="POST" id="academic-year-form" class="row g-3" novalidate>
                    @csrf

                    <div class="col-md-12">
                        <label for="name" class="small fw-bold">Academic Year / Session *</label>
                        <input type="text" name="name" id="name" class="form-control form-control-sm"
                            placeholder="e.g. 2026-2027 or 2026" value="{{ old('name') }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-12">
                        <label for="start_date" class="small fw-bold">Start Date *</label>
                        <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ old('start_date') }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-12">
                        <label for="end_date" class="small fw-bold">End Date *</label>
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ old('end_date') }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-12">
                        <label class="small fw-bold">Weekly Off Days</label>
                        <input type="hidden" name="weekly_off_days_present" value="1">
                        <div class="d-flex flex-wrap gap-3">
                            @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input weekly-off-check" name="weekly_off_days[]" id="weekly_off_{{ $d }}" value="{{ $d }}"
                                    {{ in_array($d, old('weekly_off_days', $d === 'Sunday' ? ['Sunday'] : [])) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="weekly_off_{{ $d }}">{{ substr($d, 0, 3) }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-md-12 mt-4 pt-2 border-top">
                        <div class="d-flex align-items-center gap-2 justify-content-end">
                            <button type="submit" class="btn btn-success btn-sm" id="submit-form-btn"><i class="bx bx-save"></i> Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    <div class="{{ auth()->user()->can('manage-academics') ? 'col-xl-8 col-lg-7' : 'col-12' }}">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0 fw-bold text-dark dark-readable">Configured Cycles</h5>
                <span class="badge bg-light-primary text-primary px-3 py-2 radius-30 font-13 fw-semibold">Records: {{ $academic_years->count() }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-sm table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Session / Year</th>
                                <th>Duration / Limits</th>
                                <th>Current Status</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($academic_years as $key => $item)
                            <tr id="row-{{ $item->id }}">
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark dark-readable font-14">{{ $item->name }}</div>
                                </td>
                                <td data-order="{{ $item->start_date ? $item->start_date->format('Y-m-d') : '' }}">
                                    @if($item->start_date && $item->end_date)
                                    <small class="text-secondary fw-semibold">
                                        <i class="bx bx-calendar-event"></i> {{ $item->start_date->format('d-m-Y') }} - {{ $item->end_date->format('d-m-Y') }}
                                    </small>
                                    @else
                                    <span class="badge bg-light text-secondary">Not Configured</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_current == 1)
                                    <span class="badge bg-success px-3 py-2 radius-30 text-uppercase shadow-sm font-12 current-indicator-badge">
                                        <i class="bx bxs-star"></i> Current Active
                                    </span>
                                    @else
                                    @can('manage-academics')
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-1 radius-30 font-12 make-current-btn"
                                        data-url="{{ route('academic_years.current', $item->id) }}">
                                        Mark Current
                                    </button>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endcan
                                    @endif
                                </td>
                                <td>
                                    @can('manage-academics')
                                    <button type="button" class="btn btn-sm toggle-status {{ $item->status == 1 ? 'btn-success' : 'btn-danger' }}"
                                        data-url="{{ route('academic_years.status', $item->id) }}">
                                        {{ $item->status == 1 ? 'Active' : 'Inactive' }}
                                    </button>
                                    @else
                                    <span class="badge {{ $item->status == 1 ? 'bg-success' : 'bg-danger' }}">{{ $item->status == 1 ? 'Active' : 'Inactive' }}</span>
                                    @endcan
                                </td>
                                <td>
                                    @can('manage-academics')
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#academicYearEdit-{{ $item->id }}">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <a href="{{ route('academic_years.delete', $item->id) }}" class="btn btn-sm btn-outline-danger delete-btn" id="delete">
                                            <i class="bx bx-trash"></i>
                                        </a>
                                    </div>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Academic Year Modals (one per row) --}}
@can('manage-academics')
@foreach($academic_years as $item)
<div class="modal fade academic-year-edit-modal" id="academicYearEdit-{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('academic_years.update', $item->id) }}" method="POST" class="academic-year-edit-form" novalidate>
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="edit_name_{{ $item->id }}" class="small fw-bold">Academic Year / Session *</label>
                            <input type="text" name="name" id="edit_name_{{ $item->id }}" class="form-control form-control-sm"
                                placeholder="e.g. 2026-2027 or 2026" value="{{ $item->name }}">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_start_date_{{ $item->id }}" class="small fw-bold">Start Date *</label>
                            <input type="date" name="start_date" id="edit_start_date_{{ $item->id }}" class="form-control form-control-sm"
                                value="{{ $item->start_date ? $item->start_date->format('Y-m-d') : '' }}">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_end_date_{{ $item->id }}" class="small fw-bold">End Date *</label>
                            <input type="date" name="end_date" id="edit_end_date_{{ $item->id }}" class="form-control form-control-sm"
                                value="{{ $item->end_date ? $item->end_date->format('Y-m-d') : '' }}">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-12">
                            <label class="small fw-bold">Weekly Off Days</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d)
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="weekly_off_days[]" id="edit_weekly_off_{{ $item->id }}_{{ $d }}" value="{{ $d }}"
                                        {{ in_array($d, $item->weekly_off_days ?: []) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="edit_weekly_off_{{ $item->id }}_{{ $d }}">{{ substr($d, 0, 3) }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bx bx-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endcan

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Data Table safely
        if (!$.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable({
                "order": [
                    [0, "asc"]
                ],
                "language": {
                    "emptyTable": "No academic sessions found."
                }
            });
        }

        // 1. Sir Ke Standard Ke Mutabiq Validation Error Logging & Global Toastr
        @if($errors->any())
        @foreach($errors->all() as $error)
        toastr.error("{{ $error }}");
        console.error("Validation Runtime Catch: " + "{{ $error }}");
        @endforeach
        @endif

        // 2. AJAX Dynamic Toggle Status (Active / Inactive)
        $(document).on('click', '.toggle-status', function(e) {
            e.preventDefault();
            let btn = $(this);

            $.ajax({
                url: btn.data('url'),
                type: 'GET',
                success: function(res) {
                    let activeCount = parseInt($('#active-count').text());
                    let inactiveCount = parseInt($('#inactive-count').text());

                    if (res.status == 1) {
                        btn.removeClass('btn-danger').addClass('btn-success').text('Active');
                        $('#active-count').text(activeCount + 1);
                        $('#inactive-count').text(Math.max(0, inactiveCount - 1));
                        toastr.success(res.message);
                    } else {
                        btn.removeClass('btn-success').addClass('btn-danger').text('Inactive');
                        $('#active-count').text(Math.max(0, activeCount - 1));
                        $('#inactive-count').text(inactiveCount + 1);
                        toastr.error(res.message);
                    }
                },
                error: function(xhr, status, error) { // Standard arguments mapping used here
                    console.error("Status Toggle Failed:", error);
                    toastr.error("Failed to alter cycle runtime parameters.");
                }
            });
        });

        // 3. AJAX Toggle Running State (Mark Current Session Rule)
        $(document).on('click', '.make-current-btn', function(e) {
            e.preventDefault();
            let btn = $(this);

            $.ajax({
                url: btn.data('url'),
                type: 'GET',
                success: function(res) {
                    toastr.success(res.message);
                    // Fast reload to sync changes cleanly across system nodes immediately
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                },
                error: function(xhr, status, error) { // Standard 3 parameters rule
                    console.error("Session State Swap Error:", error);
                    toastr.error("Something went wrong while updating running session context.");
                }
            });
        });

        // 4. Add + Edit form: AJAX submit, server 422 errors field ke neeche, toastr mein sirf pehla error
        function clearYearErrors($form) {
            $form.find('.is-invalid').removeClass('is-invalid').next('.invalid-feedback').text('');
        }

        function submitYearForm($form) {
            let $btn = $form.find('button[type="submit"]');
            clearYearErrors($form);
            $btn.prop('disabled', true);

            $.ajax({
                url: $form.attr('action'),
                type: 'POST', // Edit form mein hidden _method=PUT hai
                data: $form.serialize()
            }).done(function(res) {
                toastr.success(res.message);
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            }).fail(function(xhr) {
                $btn.prop('disabled', false);
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    let first = null;
                    $.each(xhr.responseJSON.errors, function(key, msgs) {
                        if (!first) first = msgs[0];
                        $form.find('[name="' + key + '"]').addClass('is-invalid').next('.invalid-feedback').text(msgs[0]);
                    });
                    toastr.clear();
                    toastr.error(first);
                    $form.find('.is-invalid').first().trigger('focus');
                } else {
                    toastr.clear();
                    toastr.error('Something went wrong. Please try again.');
                }
            });
        }

        $(document).on('submit', '#academic-year-form, .academic-year-edit-form', function(e) {
            e.preventDefault();
            submitYearForm($(this));
        });

        // Field badalne par sirf usi field ka error saaf
        $(document).on('input change', '#academic-year-form .is-invalid, .academic-year-edit-form .is-invalid', function() {
            $(this).removeClass('is-invalid').next('.invalid-feedback').text('');
        });

        // Edit modal band ya khulne par poora reset (reset() server wali values par wapas le jata hai)
        $(document).on('show.bs.modal hidden.bs.modal', '.academic-year-edit-modal', function() {
            let $form = $(this).find('form');
            $form[0].reset();
            clearYearErrors($form);
            $form.find('button[type="submit"]').prop('disabled', false);
        });

        // 5. SweetAlert Delete Context Rules
        $(document).on('click', '#delete', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                // fee-assignment-swal = app.css dark mode popup + buttons gap
                buttonsStyling: false,
                customClass: {
                    popup: 'fee-assignment-swal',
                    confirmButton: 'btn btn-danger btn-sm',
                    cancelButton: 'btn btn-secondary btn-sm'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link;
                }
            });
        });
    });
</script>
@endpush