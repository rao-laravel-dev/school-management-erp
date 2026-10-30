@extends($current_layout)
@section('content')

{{-- Breadcrumb and Stats (Same as before) --}}
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Attendance</div>
    <div class="ps-3">
        <nav>
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active">Mark Daily Attendance</li>
            </ol>
        </nav>
    </div>
</div>

<div class="att-stats">
    @include('admin.attendance.partials.stat_card', ['label' => 'Total', 'value' => '0', 'color' => 'primary', 'id' => 'total-count'])
    @include('admin.attendance.partials.stat_card', ['label' => 'Present', 'value' => '0', 'color' => 'success', 'id' => 'present-count'])
    @include('admin.attendance.partials.stat_card', ['label' => 'Absent', 'value' => '0', 'color' => 'danger', 'id' => 'absent-count'])
    @include('admin.attendance.partials.stat_card', ['label' => 'Late', 'value' => '0', 'color' => 'warning', 'id' => 'late-count'])
    @include('admin.attendance.partials.stat_card', ['label' => 'Half-Day', 'value' => '0', 'color' => 'info', 'id' => 'hd-count'])
    @include('admin.attendance.partials.stat_card', ['label' => 'Leave', 'value' => '0', 'color' => 'secondary', 'id' => 'leave-count'])
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center att-card-header">
        <h5 class="mb-0">Mark Attendance</h5>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="holidayToggle" name="is_holiday" value="on" form="attendanceForm">
            <label class="form-check-label text-danger fw-bold" for="holidayToggle">Mark Today as Holiday</label>
        </div>
    </div>

    <div class="card-body p-4">
        <form id="attendanceForm" action="{{ route('attendance.store') }}" method="POST">
            @csrf
            <input type="hidden" name="class_id" id="hidden_class_id">
            <input type="hidden" name="section_id" id="hidden_section_id">

            <div class="row g-3 mb-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small">Select Class <span class="text-danger">*</span></label>
                        <select name="class_id" id="class_id" class="form-select form-select-sm @error('class_id') is-invalid @enderror">
                            <option value="">-- Choose Class --</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small">Select Section <span class="text-danger">*</span></label>
                        <select name="section_id" id="section_id" class="form-select form-select-sm @error('section_id') is-invalid @enderror">
                            <option value="">-- Choose Section --</option>
                            {{-- Section options --}}
                        </select>
                        @error('section_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small">Attendance Date</label>
                        <input type="date" name="attendance_date" class="form-control form-control-sm @error('attendance_date') is-invalid @enderror" value="{{ old('attendance_date', date('Y-m-d')) }}" required>
                        @error('attendance_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- START-ATTENDANCE HO CHUKI HA -->
            <div id="attendance-message" class="alert alert-info d-none text-danger" role="alert">
                <i class="bx bx-info-circle"></i>
                <strong>Note:</strong> Attendance for this date and section has already been marked. You are currently in <b>Edit Mode</b>.
            </div>
            <!-- END-ATTENDANCE HO CHUKI HA -->


            <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Roll No</th>
                        <th>Student Name</th>
                        <th>Attendance Status</th>
                        <th>Time Out</th> {{-- naya column --}}
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody id="studentTableBody">
                    <tr>
                        <td colspan="5" class="text-center text-muted">Select class and section to load students.</td>
                    </tr>
                </tbody>
            </table>
            </div>

            {{-- Mass Departure aur Submit button ek hi line mein --}}
            <div class="row gy-3 mt-4 pt-3 border-top align-items-center">
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
                    <button type="submit" class="btn btn-sm btn-success px-4"><i class='bx bx-save'></i> Save Attendance</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // 1. Blade se PHP variables uthayein (jo aapne method mein pass kiye hain)
        let editClass = "{{ $selected_class ?? '' }}";
        let editSection = "{{ $section_id ?? ($selected_section ?? '') }}";
        let editDate = "{{ $selected_date ?? '' }}";

        // 2. Sirf tab chalein jab edit mode (yani data) majood ho
        if (editClass !== '' && editSection !== '') {

            // Class select karke trigger change karein taake sections load hon
            $('#class_id').val(editClass).trigger('change');

            // Section aur Date ko set karke data load karein
            let interval = setInterval(() => {
                if ($('#section_id option[value="' + editSection + '"]').length > 0) {

                    $('#section_id').val(editSection).trigger('change');

                    // Date input ko ensure karein
                    if (editDate) {
                        $('#attendance_date').val(editDate);
                    }

                    // Data load karein
                    loadStudents();

                    clearInterval(interval);
                }
            }, 200);
        }
    });

    // ATTENDANCE-FORM K LIYE TOASTR
    $('#attendanceForm').on('submit', function(e) {
        e.preventDefault();

        let formData = $(this).serialize();

        $.ajax({
            url: $(this).attr('action'),
            type: "POST",
            data: formData,
            success: function(res) {
                // Sirf JSON success maane; HTML/redirect page aaye to khali green toastr nahi, error dikhao
                if (!res || typeof res !== 'object' || !(res.status === 'success' || res.success === true)) {
                    toastr.error((res && res.message) || 'Attendance could not be saved. Please try again.');
                    return;
                }

                // 1. Toastr message show karein
                toastr.success(res.message || 'Attendance saved successfully.');

                // 2. Redirect handle karein
                // Agar res.redirect majood hai, toh kuch dair baad redirect karein
                if (res.redirect) {
                    setTimeout(function() {
                        window.location.href = res.redirect;
                    }, 1500); // 1.5 seconds ka wait taake user msg parh le
                } else {
                    // Agar redirect nahi hai (sirf holiday mark kiya ho), toh table update kar dein
                    loadStudents();
                }
            },
            error: function(err) {
                toastr.error((err.responseJSON && err.responseJSON.message) || "Error saving attendance!");
            }
        });
    });

    // --- NEW FUNCTION: APPLY MASS TIME ---
    function applyMassTime() {
        let time = $('#mass_departure_time').val();

        // Check karein ke time select kiya gaya hai ya nahi
        if (time) {
            // Table mein jitne bhi time_out inputs hain, sab mein ye time set kar dein
            $('.student-time-out').val(time);

            // Toastr success notification
            toastr.success("Time applied to all students successfully!", "Success");
        } else {
            // Toastr error notification agar time empty hai
            toastr.error("Please select a valid time first!", "Required");
        }
    }

    $('#class_id').on('change', function() {
        let class_id = $(this).val();

        // PHP se aaj ki date lein, ye browser ke timezone ka masla khatam kar degi
        let today = "{{ date('Y-m-d') }}";
        let editDate = "{{ $selected_date ?? '' }}";

        // Sirf tab date set karein agar hum edit mode mein nahi hain
        if (editDate === '') {
            $('#attendance_date').val(today);
        }

        $.get("{{ url('/students/get-sections') }}/" + class_id, function(res) {
            let options = '<option value="">-- Choose Section --</option>';
            // Teacher ko sirf apne allowed sections (server par bhi check hota hai); admin = null = sab
            let allowedSections = @json($allowedSections ?? null);
            let allowedForClass = allowedSections ? (allowedSections[class_id] || []) : null;
            if (res.status == 'success') res.data.forEach(function(s) {
                if (allowedForClass && allowedForClass.indexOf(Number(s.id)) === -1) return;
                options += `<option value="${s.id}">${s.name}</option>`;
            });
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



    $(document).on('click', '.btn-check', function() {
        $(this).closest('.btn-group').find('label').removeClass('active');
        $(this).next('label').addClass('active');
        $(this).prop('checked', true);
        updateCounters();
    });

    // Is function ko script ke andar kahi bhi rakh dein
    function loadStudents() {
        let class_id = $('#class_id').val();
        let section_id = $('#section_id').val();
        let attendance_date = $('input[name="attendance_date"]').val();

        if (class_id && section_id && attendance_date) {
            $.ajax({
                url: "{{ route('attendance.get.students') }}",
                type: "GET",
                data: {
                    class_id: class_id,
                    section_id: section_id,
                    attendance_date: attendance_date
                },
                success: function(res) {
                    // Yahan res.is_marked use karein (Backend ke mutabiq)
                    if (res.is_marked) {
                        $('#attendance-message').removeClass('d-none');
                        $('#attendanceForm button[type="submit"]').html("<i class='bx bx-save'></i> Update Attendance");
                    } else {
                        $('#attendance-message').addClass('d-none');
                        $('#attendanceForm button[type="submit"]').html("<i class='bx bx-save'></i> Save Attendance");
                    }

                    // Poori class Holiday (4 + remarks "Holiday") ho to toggle checked + buttons lock, bina Swal ke
                    let allHoliday = res.students.length > 0 && res.students.every(function(item) {
                        let a = item.attendance && item.attendance.length > 0 ? item.attendance[0] : null;
                        return a && a.status == 4 && String(a.remarks || '').trim().toLowerCase() === 'holiday';
                    });
                    $('#holidayToggle').prop('checked', allHoliday);

                    // 2. Previous Pending Warning (agar aapne backend se ye key bheji hai)
                    if (res.is_previous_pending) {
                        toastr.warning('Warning: You have pending attendance for previous dates!');
                    }

                    // 3. Render Table (res.students pass karein)
                    renderStudentTable(res.students);
                    if (allHoliday) {
                        $('.btn-check').prop('disabled', true);
                        $('.btn-group label').addClass('disabled-style');
                    }
                    updateCounters();
                },
                error: function(err) {
                    console.error("Error loading students:", err);
                    $('#studentTableBody').html('<tr><td colspan="4" class="text-center text-danger">Error loading students.</td></tr>');
                }
            });
        }
    }

    // Table generate karne wala function
    function renderStudentTable(students) {
        let html = '';
        if (students.length === 0) {
            html = '<tr><td colspan="4" class="text-center">No students found.</td></tr>';
        } else {
            students.forEach(function(item) {
                // item = enrollment data, item.student = student profile data
                let s = item.student || item;

                // 1. Name fix: Sahi tarike se first_name aur last_name combine karein
                let fName = s.first_name ? s.first_name : '';
                let lName = s.last_name ? s.last_name : '';
                let fullName = fName + ' ' + lName;

                // 2. Roll No fix: roll_no enrollments table (item) mein hai, student table mein nahi
                let rollNo = item.roll_no ? item.roll_no : 'N/A';

                let att = item.attendance && item.attendance.length > 0 ? item.attendance[0] : null;
                let status = att ? att.status : '1';

                html += `<tr>
                <td>${rollNo}</td>
                <td>${fullName.trim()}</td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="status[${item.id}]" id="p${item.id}" value="1" ${status == '1' ? 'checked' : ''}>
                        <label class="btn btn-outline-success btn-sm ${status == '1' ? 'active' : ''}" for="p${item.id}" title="Present">P</label>

                        <input type="radio" class="btn-check" name="status[${item.id}]" id="a${item.id}" value="0" ${status == '0' ? 'checked' : ''}>
                        <label class="btn btn-outline-danger btn-sm ${status == '0' ? 'active' : ''}" for="a${item.id}" title="Absent">A</label>

                        <input type="radio" class="btn-check" name="status[${item.id}]" id="l${item.id}" value="2" ${status == '2' ? 'checked' : ''}>
                        <label class="btn btn-outline-warning btn-sm ${status == '2' ? 'active' : ''}" for="l${item.id}" title="Late">LT</label>

                        <input type="radio" class="btn-check" name="status[${item.id}]" id="h${item.id}" value="3" ${status == '3' ? 'checked' : ''}>
                        <label class="btn btn-outline-info btn-sm ${status == '3' ? 'active' : ''}" for="h${item.id}" title="Half Day">HD</label>

                        <input type="radio" class="btn-check" name="status[${item.id}]" id="lv${item.id}" value="4" ${status == '4' ? 'checked' : ''}>
                        <label class="btn btn-outline-secondary btn-sm ${status == '4' ? 'active' : ''}" for="lv${item.id}" title="Leave">LV</label>
                    </div>
                </td>
                <td>
                    <input type="time" name="time_out[${item.id}]" class="form-control form-control-sm student-time-out" value="${att && att.time_out ? att.time_out : ''}">
                </td>
                <td>
                    <input type="text" name="remarks[${item.id}]" class="form-control form-control-sm" value="${att ? (att.remarks || '') : ''}">
                </td>
            </tr>`;
            });
        }
        $('#studentTableBody').html(html);
    }

    function updateCounters() {
        let total = $('#studentTableBody tr').length;
        $('#total-count').text(total);

        // Count currently checked radios
        let pCount = $('input[name^="status"][value="1"]:checked').length;
        let aCount = $('input[name^="status"][value="0"]:checked').length;
        let lCount = $('input[name^="status"][value="2"]:checked').length;
        let hdCount = $('input[name^="status"][value="3"]:checked').length;
        // Holiday rows (4 + remarks "Holiday") Leave mein nahi ginti jati
        let leaveCount = $('input[name^="status"][value="4"]:checked').filter(function() {
            let rem = $(this).closest('tr').find('input[name^="remarks"]').val();
            return $.trim(rem || '').toLowerCase() !== 'holiday';
        }).length;

        // Logic: Present = sirf status 1 (reports jaisa); Late aur Half-Day ke apne cards hain
        let totalPresent = pCount;

        // Update UI
        $('#present-count').text(totalPresent);
        $('#absent-count').text(aCount);
        $('#late-count').text(lCount);
        $('#hd-count').text(hdCount);
        $('#leave-count').text(leaveCount);
    }

    // Single Event Listener to rule them all
    $(document).on('change', '.btn-check', function() {
        // Visual update
        $(this).closest('.btn-group').find('label').removeClass('active');
        $(this).next('label').addClass('active');

        // Recalculate
        updateCounters();
    });

    $(document).on('change', '.btn-check', updateCounters);
    $('#holidayToggle').on('change', function() {
        let toggle = $(this); // Toggle button ka reference store kiya

        if (toggle.is(':checked')) {
            // Selected date d-m-Y mein (aaj ki nahi, jo date select hai wahi)
            let holidayDate = String($('input[name="attendance_date"]').val() || '').split('-').reverse().join('-');
            // SweetAlert dikhayein
            Swal.fire({
                title: 'Confirm Holiday?',
                text: "Do you want to mark the attendance of " + holidayDate + " as a 'Holiday'?",
                icon: 'warning',
                showCancelButton: true,
                // fee-assignment-swal = app.css dark mode popup + buttons gap
                buttonsStyling: false,
                customClass: {
                    popup: 'fee-assignment-swal',
                    confirmButton: 'btn btn-danger btn-sm',
                    cancelButton: 'btn btn-secondary btn-sm'
                },
                confirmButtonText: 'Yes, Mark Holiday!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Agar user ne OK kiya
                    $('.btn-check').prop('disabled', true);
                    $('.btn-group label').addClass('disabled-style');
                    Swal.fire({
                        title: 'Done!',
                        text: 'Holiday selected for ' + holidayDate + '. Click Save Attendance to apply.',
                        icon: 'success',
                        buttonsStyling: false,
                        customClass: {
                            popup: 'fee-assignment-swal',
                            confirmButton: 'btn btn-success btn-sm'
                        }
                    });
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