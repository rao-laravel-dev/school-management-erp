@extends('admin..layout.app')
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academic</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Academic Year / Sessions</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-3 g-3 mb-4">

    {{-- Total Academic Sessions --}}
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-primary shadow-sm mb-0">
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
        <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm mb-0">
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
        <div class="card radius-10 border-start border-0 border-3 border-secondary shadow-sm mb-0">
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
    <div class="col-xl-4 col-lg-5">
        <div class="card radius-10 border-top border-0 border-4 border-success" id="form-card-wrapper">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0 card-title-text fw-bold text-dark">Add New Session</h5>
                <p class="mb-0 small text-muted card-subtitle-text">Configure a new active school cycle</p>
            </div>

            <div class="card-body">
                <form action="{{ route('academic_years.store') }}" method="POST" id="academic-year-form" class="row g-3">
                    @csrf
                    <input type="hidden" name="_method" id="form-method-field" value="POST">

                    <div class="col-md-12">
                        <label for="name" class="form-label text-secondary fw-semibold">Academic Year / Session <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                            placeholder="e.g. 2026-2027 or 2026" value="{{ old('name') }}" required>
                        @error('name')
                        <div class="invalid-feedback fw-bold d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label for="start_date" class="form-label text-secondary fw-semibold">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}">
                        @error('start_date')
                        <div class="invalid-feedback fw-bold d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label for="end_date" class="form-label text-secondary fw-semibold">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}">
                        @error('end_date')
                        <div class="invalid-feedback fw-bold d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label text-secondary fw-semibold">Weekly Off Days</label>
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
                        @error('weekly_off_days.*')
                        <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mt-4 pt-2 border-top">
                        <div class="d-flex align-items-center gap-2 justify-content-end">
                            <button type="button" class="btn btn-secondary px-3 d-none" id="reset-form-btn">Cancel Edit</button>
                            <button type="submit" class="btn btn-success px-4 shadow-sm" id="submit-form-btn">Save Session</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-lg-7">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0 fw-bold text-dark">Configured Cycles</h5>
                <span class="badge bg-light-primary text-primary px-3 py-2 radius-30 font-13 fw-semibold">Records: {{ $academic_years->count() }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
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
                                    <div class="fw-bold text-dark font-14">{{ $item->name }}</div>
                                </td>
                                <td>
                                    @if($item->start_date && $item->end_date)
                                    <small class="text-secondary fw-semibold">
                                        <i class="bx bx-calendar-event"></i> {{ $item->start_date->format('d M, Y') }} - {{ $item->end_date->format('d M, Y') }}
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
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-1 radius-30 font-12 make-current-btn"
                                        data-url="{{ route('academic_years.current', $item->id) }}">
                                        Mark Current
                                    </button>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm toggle-status {{ $item->status == 1 ? 'btn-success' : 'btn-danger' }}"
                                        data-url="{{ route('academic_years.status', $item->id) }}">
                                        {{ $item->status == 1 ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-ajax-trigger"
                                            data-id="{{ $item->id }}"
                                            data-name="{{ $item->name }}"
                                            data-start="{{ $item->start_date ? \Carbon\Carbon::parse($item->start_date)->format('Y-m-d') : '' }}"
                                            data-end="{{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('Y-m-d') : '' }}"
                                            data-weekly-off='@json($item->weekly_off_days ?: [])'
                                            data-update-url="{{ route('academic_years.update', $item->id) }}">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <a href="{{ route('academic_years.delete', $item->id) }}" class="btn btn-sm btn-outline-danger delete-btn" id="delete">
                                            <i class="bx bx-trash"></i>
                                        </a>
                                    </div>
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

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Initialize Data Table safely
        if (!$.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable({
                "order": [
                    [0, "asc"]
                ]
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

        // 4. Client Side Dynamic UI State Manager (Zero Latency Edit Mapping Mode)
        $(document).on('click', '.edit-ajax-trigger', function() {
            let trigger = $(this);

            // Alter Left column presentation wrapper layer
            $('#form-card-wrapper').removeClass('border-success').addClass('border-primary');
            $('.card-title-text').text('Update Session Setting');
            $('.card-subtitle-text').text('Modifying existing structural data fields');

            // Map dynamic payloads into element input references
            $('#name').val(trigger.data('name'));

            // Format fallback configuration matching HTML date inputs safely
            if (trigger.data('start')) $('#start_date').val(trigger.data('start'));
            if (trigger.data('end')) $('#end_date').val(trigger.data('end'));

            // Weekly off checkboxes (jQuery .data JSON ko khud array bana deta hai)
            let weeklyOff = trigger.data('weekly-off') || [];
            $('.weekly-off-check').each(function() {
                $(this).prop('checked', weeklyOff.indexOf($(this).val()) !== -1);
            });

            // Swap Form Strategy from Create to Update Context
            $('#academic-year-form').attr('action', trigger.data('update-url'));
            $('#form-method-field').val('PUT'); // standard Laravel tracking configuration update pointer

            $('#submit-form-btn').removeClass('btn-success').addClass('btn-primary').text('Update Configuration');
            $('#reset-form-btn').removeClass('d-none');
        });

        // Reset Form View State Handler
        $(document).on('click', '#reset-form-btn', function() {
            $('#form-card-wrapper').removeClass('border-primary').addClass('border-success');
            $('.card-title-text').text('Add New Session');
            $('.card-subtitle-text').text('Configure a new active school cycle');

            $('#academic-year-form').attr('action', "{{ route('academic_years.store') }}");
            $('#form-method-field').val('POST');
            $('#academic-year-form')[0].reset();

            $('#submit-form-btn').removeClass('btn-primary').addClass('btn-success').text('Save Session');
            $(this).addClass('d-none');
        });

        // 5. SweetAlert Delete Context Rules
        $(document).on('click', '#delete', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");

            Swal.fire({
                title: 'Are you sure?',
                text: "Delete this configuration completely?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link;
                }
            });
        });
    });
</script>
@endpush