@extends($current_layout)
@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Lesson Plan', 'url' => '#'],
    ['label' => 'Manage Syllabus Status', 'url' => route('syllabus_status.index')],
]" />

{{-- Criteria Card --}}
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 fw-semi-bold text-primary">Select Criteria</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('syllabus_status.index') }}">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label" for="filter_class_id">Class <span class="text-danger">*</span></label>
                    <select name="class_id" id="filter_class_id" class="form-select form-select-sm @error('class_id') is-invalid @enderror">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">Please select Class.</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="filter_section_id">Section <span class="text-danger">*</span></label>
                    <select name="section_id" id="filter_section_id" class="form-select form-select-sm @error('section_id') is-invalid @enderror">
                        <option value="">Select Section</option>
                    </select>
                    <div class="invalid-feedback">Please select Section.</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="filter_subject_id">Subject <span class="text-danger">*</span></label>
                    <select name="subject_id" id="filter_subject_id" class="form-select form-select-sm @error('subject_id') is-invalid @enderror">
                        <option value="">Select Subject</option>
                    </select>
                    <div class="invalid-feedback">Please select Subject.</div>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bx bx-search"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Results --}}
@if($topics->isNotEmpty())
@php
$grouped = $topics->groupBy('lesson_id');
$subjectName = $topics->first()->subject->name ?? '';
@endphp

<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0 fw-semi-bold text-primary fs-5">Syllabus Status For: {{ $subjectName }}</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Lesson / Topic</th>
                        <th style="width: 180px;">Topic Completion Date</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grouped as $lessonId => $lessonTopics)
                    @php $lessonNo = $loop->iteration; @endphp
                    <tr class="table-light">
                        <td>{{ $lessonNo }}</td>
                        <td colspan="4" class="fw-semibold">{{ $lessonTopics->first()->lesson->name ?? '-' }}</td>
                    </tr>
                    @foreach($lessonTopics as $ti => $topic)
                    <tr>
                        <td></td>
                        <td class="ps-4 text-muted">{{ $lessonNo }}.{{ $ti + 1 }} {{ $topic->name }}</td>
                        <td>
                            <input type="date"
                                class="form-control form-control-sm completion-date-input"
                                data-id="{{ $topic->id }}"
                                value="{{ $topic->completion_date ? \Carbon\Carbon::parse($topic->completion_date)->format('Y-m-d') : '' }}"
                                style="{{ $topic->is_completed ? '' : 'display:none;' }}">
                        </td>
                        <td>
                            <span class="badge {{ $topic->is_completed ? 'bg-success' : 'bg-warning' }} status-badge" data-id="{{ $topic->id }}">
                                {{ $topic->is_completed ? 'Completed' : 'Incomplete' }}
                            </span>
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input type="checkbox"
                                    class="form-check-input status-toggle"
                                    data-id="{{ $topic->id }}"
                                    {{ $topic->is_completed ? 'checked' : '' }}>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@elseif(request()->filled('subject_id'))
<div class="alert alert-info mt-3">No lessons/topics found for the selected criteria.</div>
@endif

@endsection

@push('scripts')
<script>
    $(function() {

        // ===== Show toastr for server-side validation errors =====
        @if($errors->any())
        toastr.error("{{ $errors->first() }}");
        @endif

        function loadSections(classId, targetSelectId, selectedVal = null) {
            const $select = $('#' + targetSelectId);
            $select.html('<option value="">Loading...</option>');
            if (!classId) {
                $select.html('<option value="">Select Section</option>');
                return;
            }
            $.get(`{{ url('syllabus_status/get-sections') }}/${classId}`, function(data) {
                let opts = '<option value="">Select Section</option>';
                data.forEach(s => opts += `<option value="${s.id}" ${selectedVal == s.id ? 'selected' : ''}>${s.name}</option>`);
                $select.html(opts);
            });
        }

        function loadSubjects(classId, targetSelectId, selectedVal = null) {
            const $select = $('#' + targetSelectId);
            $select.html('<option value="">Loading...</option>');
            if (!classId) {
                $select.html('<option value="">Select Subject</option>');
                return;
            }
            $.get(`{{ url('syllabus_status/get-subjects') }}/${classId}`, function(data) {
                let opts = '<option value="">Select Subject</option>';
                data.forEach(s => opts += `<option value="${s.id}" ${selectedVal == s.id ? 'selected' : ''}>${s.name}</option>`);
                $select.html(opts);
            });
        }

        // page load pe agar filters already select the huay hein (validation fail ya search ke baad), unhe restore karo
        const initialClass = "{{ request('class_id') }}";
        const initialSection = "{{ request('section_id') }}";
        const initialSubject = "{{ request('subject_id') }}";

        if (initialClass) {
            loadSections(initialClass, 'filter_section_id', initialSection);
            loadSubjects(initialClass, 'filter_subject_id', initialSubject);
        }

        $('#filter_class_id').on('change', function() {
            loadSections($(this).val(), 'filter_section_id');
            loadSubjects($(this).val(), 'filter_subject_id');
        });

        // ===== Toggle Status (with SweetAlert confirmation) =====
        $(document).on('change', '.status-toggle', function() {
            const $toggle = $(this);
            const id = $toggle.data('id');
            const row = $toggle.closest('tr');
            const willComplete = $toggle.is(':checked');

            Swal.fire({
                title: willComplete ? 'Mark this topic as Completed?' : 'Mark this topic as Incomplete?',
                text: willComplete ?
                    "Completion date will be set to today's date." : "Completion date will be cleared.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (!result.isConfirmed) {
                    // user ne cancel kiya — toggle wapis purani state pe le jao
                    $toggle.prop('checked', !willComplete);
                    return;
                }

                $.ajax({
                    url: `{{ url('syllabus_status/toggle') }}/${id}`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        const $badge = row.find('.status-badge');
                        const $dateInput = row.find('.completion-date-input');

                        if (res.is_completed) {
                            $badge.removeClass('bg-secondary').addClass('bg-dark').text('Completed');
                            $dateInput.val(res.completion_date_raw || '').show();
                        } else {
                            $badge.removeClass('bg-dark').addClass('bg-secondary').text('Incomplete');
                            $dateInput.val('').hide();
                        }
                        toastr.success('Status updated.');
                    },
                    error: function() {
                        toastr.error('Failed to update status.');
                        $toggle.prop('checked', !willComplete);
                    }
                });
            });
        });

        // ===== Inline Completion Date update =====
        $(document).on('change', '.completion-date-input', function() {
            const $input = $(this);
            const id = $input.data('id');
            const val = $input.val();
            const row = $input.closest('tr');

            if (!val) return;

            $.ajax({
                url: `{{ url('syllabus_status/update-date') }}/${id}`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    completion_date: val
                },
                success: function(res) {
                    row.find('.status-badge').removeClass('bg-secondary').addClass('bg-dark').text('Completed');
                    row.find('.status-toggle').prop('checked', true);
                    row.find('.completion-date-input').show();
                    toastr.success('Completion date updated.');
                },
                error: function() {
                    toastr.error('Failed to update date.');
                }
            });
        });

    });
</script>
@endpush