@extends($current_layout)
@section('content')

{{-- Breadcrumb and Stats (Same as before) --}}
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Attendance</div>
    <div class="ps-3">
        <nav>
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active">Mark Daily Attendance</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-5 g-3 mb-4">
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-primary shadow-sm bg-white">
            <div class="card-body p-3">
                <p class="mb-0 text-secondary small">Total</p>
                <h4 class="my-0 text-primary fw-bold" id="total-count">0</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm bg-white">
            <div class="card-body p-3">
                <p class="mb-0 text-secondary small">Present</p>
                <h4 class="my-0 text-success fw-bold" id="present-count">0</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-danger shadow-sm bg-white">
            <div class="card-body p-3">
                <p class="mb-0 text-secondary small">Absent</p>
                <h4 class="my-0 text-danger fw-bold" id="absent-count">0</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-warning shadow-sm bg-white">
            <div class="card-body p-3">
                <p class="mb-0 text-secondary small">Late</p>
                <h4 class="my-0 text-warning fw-bold" id="late-count">0</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-info shadow-sm bg-white">
            <div class="card-body p-3">
                <p class="mb-0 text-secondary small">Half-Day</p>
                <h4 class="my-0 text-info fw-bold" id="hd-count">0</h4>
            </div>
        </div>
    </div>
</div>

<div class="card radius-10 border-top border-0 border-4 border-primary">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Mark Attendance</h5>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="holidayToggle" name="is_holiday" value="on">
            <label class="form-check-label text-danger fw-bold" for="holidayToggle">Mark Today as Holiday</label>
        </div>
    </div>

    <div class="card-body p-4">
        <form id="attendanceForm" action="{{ route('attendance.store') }}" method="POST">
            @csrf
            <input type="hidden" name="class_id" id="hidden_class_id">
            <input type="hidden" name="section_id" id="hidden_section_id">
            <input type="hidden" name="attendance_date" value="{{ $selected_date }}">

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Select Class <span class="text-danger">*</span></label>
                    <select name="class_id" id="class_id" class="form-control" required>
                        <option value="">-- Choose Class --</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ (isset($selected_class) && $selected_class == $class->id) ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Select Section <span class="text-danger">*</span></label>
                    <select name="section_id" id="section_id" class="form-control" required>
                        <option value="">-- Choose Section --</option>

                        @if(isset($section_id) && $section_id)
                        {{-- Edit mode: Sirf woh section dikhayein ya load karein jo select hai --}}
                        @foreach(\App\Models\Section::all() as $s)
                        <option value="{{ $s->id }}" {{ $s->id == $section_id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                        @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Attendance Date</label>
                    <input type="date" name="attendance_date" id="attendance_date"
                        class="form-control"
                        value="{{ $selected_date ?? date('Y-m-d') }}" required>
                </div>
            </div>

            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Roll No</th>
                        <th>Student Name</th>
                        <th>Attendance Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody id="studentTableBody">
                    <tr>
                        <td colspan="4" class="text-center text-muted">Select class and section to load students.</td>
                    </tr>
                </tbody>
            </table>

            {{-- Mass Departure aur Submit button ek hi line mein --}}
            <div class="row mt-4 pt-3 border-top align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <label class="form-label fw-bold mb-0 me-2" style="white-space: nowrap;">Mass Time:</label>
                        <div class="input-group" style="max-width: 250px;">
                            <input type="time" id="mass_departure_time" class="form-control form-control-sm">
                            <button type="button" class="btn btn-sm btn-warning" onclick="applyMassTime()">Apply</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <button type="submit" class="btn btn-primary px-4">Submit Attendance</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // 1. Blade se values uthayein
        let editClass = "{{ $selected_class ?? '' }}";
        let editSection = "{{ $section_id ?? '' }}";
        let editDate = "{{ $selected_date ?? '' }}"; // Yeh variable miss tha

        // 2. Agar Edit mode hai
        if (editClass !== '') {
            // Hidden fields mein values set karein
            $('#hidden_class_id').val(editClass);
            $('#hidden_section_id').val(editSection);

            // Date field mein value set karein (agar maujood ho)
            if (editDate !== '') {
                $('#attendance_date').val(editDate);
            }

            // Sections fetch karein, jo automatically loadStudents() call karega
            fetchSections(editClass, editSection);
        }
    });

    // Function taake code repeat na ho
    function fetchSections(class_id, selectedSectionId = null) {
        $.get("{{ url('/students/get-sections') }}/" + class_id, function(res) {
            let options = '<option value="">-- Choose Section --</option>';
            if (res.status == 'success') {
                res.data.forEach(s => {
                    let isSelected = (s.id == selectedSectionId) ? 'selected' : '';
                    options += `<option value="${s.id}" ${isSelected}>${s.name}</option>`;
                });
            }
            $('#section_id').html(options);

            // Agar edit mode hai, to students load karein
            if (selectedSectionId) {
                loadStudents();
            }
        });
    }

    // --- NEW FUNCTION: APPLY MASS TIME ---
    function applyMassTime() {
        let time = $('#mass_departure_time').val();
        if (time) {
            // Table mein jitne bhi time_out inputs hain, sab mein ye time daal de
            $('.student-time-out').val(time);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Please select a time first!'
            });
        }
    }

    $('#class_id').on('change', function() {
        let today = new Date().toISOString().split('T')[0];
        $('#attendance_date').val(today);

        let class_id = $(this).val();
        $.get("{{ url('/students/get-sections') }}/" + class_id, function(res) {
            let options = '<option value="">-- Choose Section --</option>';
            if (res.status == 'success') res.data.forEach(s => options += `<option value="${s.id}">${s.name}</option>`);
            $('#section_id').html(options);
        });
    });

    $('#section_id').on('change', function() {
        loadStudents();
    });

    $('input[name="attendance_date"]').on('change', function() {
        let selectedDate = $(this).val();
        $('#hidden_attendance_date').val(selectedDate);
        loadStudents();
    });

    function loadStudents() {
        let class_id = $('#class_id').val();
        let section_id = $('#section_id').val();
        let attendance_date = $('#attendance_date').val();

        // 1. Hidden inputs update (Important for form submission)
        $('#hidden_class_id').val(class_id);
        $('#hidden_section_id').val(section_id);
        $('#hidden_attendance_date').val(attendance_date);

        if (!class_id || !section_id) return;

        // Loading state
        $('#studentTableBody').html('<tr><td colspan="4" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');

        $.get("{{ route('attendance.get.students') }}", {
            class_id: class_id,
            section_id: section_id,
            attendance_date: attendance_date
        }, function(res) {

            let studentsData = res.data ? res.data : (Array.isArray(res) ? res : []);
            let isAlreadyMarked = res.already_marked !== undefined ? res.already_marked : false;

            // 2. Button Logic (Alert removed)
            let btn = $('button[type="submit"]');
            if (isAlreadyMarked) {
                btn.html('<i class="bx bx-save"></i> Update Attendance').removeClass('btn-primary').addClass('btn-success');
            } else {
                btn.html('Submit Attendance').removeClass('btn-success').addClass('btn-primary');
            }

            // 3. Render Table
            let html = '';
            if (Array.isArray(studentsData) && studentsData.length > 0) {
                studentsData.forEach(function(s) {
                    let att = s.attendance && s.attendance.length > 0 ? s.attendance[0] : null;
                    let status = att ? parseInt(att.status) : 1;
                    let remarksValue = att && att.remarks ? att.remarks : '';

                    html += `<tr>
                    <td>${s.roll_no}</td>
                    <td>${s.student ? (s.student.first_name + ' ' + s.student.last_name) : 'N/A'}</td>
                    <td>
                        <input type="hidden" name="enrollment_id[]" value="${s.id}">
                        <div class="btn-group btn-group-sm">
                            <input type="radio" class="btn-check status-radio" name="status[${s.id}]" value="1" id="p${s.id}" ${(status == 1 ? 'checked' : '')}>
                            <label class="btn btn-outline-success ${(status == 1 ? 'active' : '')}" for="p${s.id}">P</label>
                            
                            <input type="radio" class="btn-check status-radio" name="status[${s.id}]" value="0" id="a${s.id}" ${(status == 0 ? 'checked' : '')}>
                            <label class="btn btn-outline-danger ${(status == 0 ? 'active' : '')}" for="a${s.id}">A</label>
                            
                            <input type="radio" class="btn-check status-radio" name="status[${s.id}]" value="2" id="l${s.id}" ${(status == 2 ? 'checked' : '')}>
                            <label class="btn btn-outline-warning ${(status == 2 ? 'active' : '')}" for="l${s.id}">L</label>
                            
                            <input type="radio" class="btn-check status-radio" name="status[${s.id}]" value="3" id="hd${s.id}" ${(status == 3 ? 'checked' : '')}>
                            <label class="btn btn-outline-info ${(status == 3 ? 'active' : '')}" for="hd${s.id}">HD</label>
                        </div>
                    </td>
                    <td>
                        <input type="hidden" name="time_out[${s.id}]" class="student-time-out">
                        <input type="text" name="remarks[${s.id}]" class="form-control form-control-sm" value="${remarksValue}">
                    </td>
                </tr>`;
                });
            } else {
                html = '<tr><td colspan="4" class="text-center text-danger">No students found.</td></tr>';
            }

            $('#studentTableBody').html(html);

            if ($('#holidayToggle').is(':checked')) {
                $('.btn-check, input[name^="remarks"]').prop('disabled', true);
            }

            if (typeof updateCounters === "function") {
                updateCounters();
            }

        }).fail(function() {
            $('#studentTableBody').html('<tr><td colspan="4" class="text-center text-danger">Error loading students.</td></tr>');
        });
    }

    // Auto-load students on Edit Page load
    $(document).ready(function() {
        if ($('#class_id').val() && $('#section_id').val()) {
            loadStudents();
        }

        // Trigger on change
        $('#class_id, #section_id, #attendance_date').on('change', function() {
            loadStudents();
        });

        // Helper to visually update active classes on labels when radio changes
        $(document).on('change', '.status-radio', function() {
            let parentGroup = $(this).closest('.btn-group');
            parentGroup.find('label').removeClass('active');
            $(this).next('label').addClass('active');
            if (typeof updateCounters === "function") {
                updateCounters();
            }
        });
    });

    $(document).on('click', '.btn-check', function() {
        $(this).closest('.btn-group').find('label').removeClass('active');
        $(this).next('label').addClass('active');
        $(this).prop('checked', true);
        updateCounters();
    });

    // --- Updated Counter Logic for Edit Page ---
    function updateCounters() {
        let total = $('#studentTableBody tr').length;
        $('#total-count').text(total);

        // Get all checked radio buttons status
        let pCount = $('input[name^="status"][value="1"]:checked').length;
        let aCount = $('input[name^="status"][value="0"]:checked').length;
        let lCount = $('input[name^="status"][value="2"]:checked').length;
        let hdCount = $('input[name^="status"][value="3"]:checked').length;

        // Logic: Present = Present (1) + Late (2) + Half-Day (3)
        let totalPresent = parseInt(pCount) + parseInt(lCount) + parseInt(hdCount);

        // Update UI
        $('#present-count').text(totalPresent);
        $('#absent-count').text(aCount);
        $('#late-count').text(lCount);
        $('#hd-count').text(hdCount);
    }

    $(document).on('change', '.btn-check', updateCounters);
    $('#holidayToggle').on('change', function() {
        let toggle = $(this); // Toggle button ka reference store kiya

        if (toggle.is(':checked')) {
            // SweetAlert dikhayein
            Swal.fire({
                title: 'Confirm Holiday?',
                text: "Do you want to mark today's attendance as a 'Holiday'?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Mark Holiday!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Agar user ne OK kiya
                    $('.btn-check').prop('disabled', true);
                    $('.btn-group label').addClass('disabled-style');
                    Swal.fire('Done!', 'Holiday has been marked.', 'success');
                } else {
                    // Agar user ne Cancel kiya, toh toggle wapas off kar dein
                    toggle.prop('checked', false);
                }
            });
        } else {
            // Normal mode: Unlock kar do
            $('.btn-check').prop('disabled', false);
            $('.btn-group label').removeClass('disabled-style');
        }
    });
</script>
@endpush