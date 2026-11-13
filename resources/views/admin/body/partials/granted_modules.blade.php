{{--
    Granted modules: admin sidebar ke module sections, staff portals (receptionist / accountant / librarian / teacher) ke liye.
    Har item woh permission use karta hai jo uska route asal mein maangta hai; har parent apne bachon ka @canany hai.
    Staff sidebar mein pehle se maujood item/section skip karne ke liye: @include('admin.body.partials.granted_modules', ['skip' => ['key', ...]])
    Naya module admin sidebar mein add ho to yahan bhi add karein (docs/ai/roles_permissions_summary.md).
--}}
@php
    $skip = isset($skip) ? $skip : [];
    $grantUser = auth()->user();

    // Item: key (skip list) + permission(s) jo uska route maangta hai (sab honi chahiye)
    $show = function ($key, $perms) use ($skip, $grantUser) {
        if (in_array($key, $skip, true)) {
            return false;
        }
        foreach ((array) $perms as $p) {
            if (! $grantUser->can($p)) {
                return false;
            }
        }
        return true;
    };

    // Parent: koi bhi child dikhna ho to parent dikhe. $checks = [[key, perms], ...]
    $any = function (array $checks) use ($show) {
        foreach ($checks as $c) {
            if ($show($c[0], $c[1])) {
                return true;
            }
        }
        return false;
    };

    // Section skip (poora block pehle se sidebar mein ho)
    $sec = function ($key) use ($skip) {
        return ! in_array($key, $skip, true);
    };

    // Agar is section ka koi item sidebar mein pehle se hai to parent ka naam "(More)" ke saath
    $title = function ($t, array $keys) use ($skip) {
        return count(array_intersect($keys, $skip)) ? $t . ' (More)' : $t;
    };
@endphp

{{-- Receptionist Details --}}
@php $checks = [['receptionist_list', 'access-receptionist'], ['receptionist_add', 'manage-receptionist']]; @endphp
@if($any($checks))
@php $open = request()->routeIs('receptionist.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="bx bx-category"></i></div>
        <div class="menu-title">{{ $title('Receptionist Details', ['receptionist_list']) }}</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        @if($show('receptionist_add', 'manage-receptionist'))
        <li class="{{ request()->routeIs('receptionist.create') ? 'mm-active' : '' }}"><a href="{{ route('receptionist.create') }}"><i class='bx bx-radio-circle'></i>Add Receptionist</a></li>
        @endif
        @if($show('receptionist_list', 'access-receptionist'))
        <li class="{{ request()->routeIs('receptionist.index', 'receptionist.edit', 'receptionist.trash') ? 'mm-active' : '' }}"><a href="{{ route('receptionist.index') }}"><i class='bx bx-radio-circle'></i>Receptionist Details</a></li>
        @endif
    </ul>
</li>
@endif

{{-- Accountant Management --}}
@php $checks = [['accountant_list', 'access-accountant'], ['accountant_add', 'manage-accountant']]; @endphp
@if($any($checks))
@php $open = request()->routeIs('accountant.index', 'accountant.create', 'accountant.edit', 'accountant.trash'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-wallet'></i></div>
        <div class="menu-title">{{ $title('Accountant Management', ['accountant_list']) }}</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        @if($show('accountant_list', 'access-accountant'))
        <li class="{{ request()->routeIs('accountant.index', 'accountant.edit', 'accountant.trash') ? 'mm-active' : '' }}"><a href="{{ route('accountant.index') }}"><i class='bx bx-radio-circle'></i>All Accountants</a></li>
        @endif
        @if($show('accountant_add', 'manage-accountant'))
        <li class="{{ request()->routeIs('accountant.create') ? 'mm-active' : '' }}"><a href="{{ route('accountant.create') }}"><i class='bx bx-radio-circle'></i>Add Accountant</a></li>
        @endif
    </ul>
</li>
@endif

{{-- Librarian Management --}}
@php $checks = [['librarian_list', 'access-librarian'], ['librarian_add', 'manage-librarian']]; @endphp
@if($any($checks))
@php $open = request()->routeIs('librarians.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-book-reader'></i></div>
        <div class="menu-title">{{ $title('Librarian Management', ['librarian_list']) }}</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        @if($show('librarian_list', 'access-librarian'))
        <li class="{{ request()->routeIs('librarians.index', 'librarians.edit', 'librarians.trash') ? 'mm-active' : '' }}"><a href="{{ route('librarians.index') }}"><i class='bx bx-radio-circle'></i>All Librarians</a></li>
        @endif
        @if($show('librarian_add', 'manage-librarian'))
        <li class="{{ request()->routeIs('librarians.create') ? 'mm-active' : '' }}"><a href="{{ route('librarians.create') }}"><i class='bx bx-radio-circle'></i>Add Librarian</a></li>
        @endif
    </ul>
</li>
@endif

{{-- Student Information --}}
@php $checks = [['students_list', 'access-students'], ['student_admit', ['access-students', 'admit-students']], ['student_import', 'access-student-import'], ['student_promotion', 'access-student-promotion'], ['student_category', 'access-student-categories'], ['student_house', 'access-student-houses']]; @endphp
@if($any($checks))
@php $open = request()->routeIs('students.*', 'student_import.*', 'student_promotion.*', 'student-category.*', 'student-house.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="bx bx-user-pin"></i></div>
        <div class="menu-title">{{ $title('Student Information', ['students_list', 'student_admit', 'student_category', 'student_house']) }}</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        @if($show('students_list', 'access-students'))
        <li class="{{ request()->routeIs('students.index', 'students.edit', 'students.trash') ? 'mm-active' : '' }}"><a href="{{ route('students.index') }}"><i class="bx bx-radio-circle"></i>All Students</a></li>
        @endif
        @if($show('student_admit', ['access-students', 'admit-students']))
        <li class="{{ request()->routeIs('students.create') ? 'mm-active' : '' }}"><a href="{{ route('students.create') }}"><i class="bx bx-radio-circle"></i>Student Admission</a></li>
        @endif
        @if($show('student_import', 'access-student-import'))
        <li class="{{ request()->routeIs('student_import.*') ? 'mm-active' : '' }}"><a href="{{ route('student_import.index') }}"><i class="bx bx-radio-circle"></i>Bulk Import</a></li>
        @endif
        @if($show('student_promotion', 'access-student-promotion'))
        <li class="{{ request()->routeIs('student_promotion.*') ? 'mm-active' : '' }}"><a href="{{ route('student_promotion.index') }}"><i class="bx bx-radio-circle"></i>Student Promotion</a></li>
        @endif
        @if($show('student_category', 'access-student-categories'))
        <li class="{{ request()->routeIs('student-category.*') ? 'mm-active' : '' }}"><a href="{{ route('student-category.index') }}"><i class="bx bx-radio-circle"></i>Student Categories</a></li>
        @endif
        @if($show('student_house', 'access-student-houses'))
        <li class="{{ request()->routeIs('student-house.*') ? 'mm-active' : '' }}"><a href="{{ route('student-house.index') }}"><i class="bx bx-radio-circle"></i>Student Houses</a></li>
        @endif
    </ul>
</li>
@endif

{{-- Teacher Management --}}
@php $checks = [['all_teachers', 'access-teacher'], ['teacher_add', 'manage-teacher'], ['assign_list', 'access-teacher-assignment'], ['assign_create', 'access-teacher-assignment']]; @endphp
@if($any($checks))
@php $open = request()->routeIs('teacher.index', 'teacher.create', 'teacher.edit', 'teacher.trash', 'teacher.assign.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="bx bx-chalkboard"></i></div>
        <div class="menu-title">{{ $title('Teacher Management', ['all_teachers', 'teacher_add', 'assign_list', 'assign_create']) }}</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        @if($show('all_teachers', 'access-teacher'))
        <li class="{{ request()->routeIs('teacher.index', 'teacher.edit', 'teacher.trash') ? 'mm-active' : '' }}"><a href="{{ route('teacher.index') }}"><i class='bx bx-radio-circle'></i>All Teachers</a></li>
        @endif
        @if($show('teacher_add', 'manage-teacher'))
        <li class="{{ request()->routeIs('teacher.create') ? 'mm-active' : '' }}"><a href="{{ route('teacher.create') }}"><i class='bx bx-radio-circle'></i>Add Teacher</a></li>
        @endif
        @if($show('assign_list', 'access-teacher-assignment'))
        <li class="{{ request()->routeIs('teacher.assign.index', 'teacher.assign.edit') ? 'mm-active' : '' }}"><a href="{{ route('teacher.assign.index') }}"><i class='bx bx-radio-circle'></i>Assigned Classes List</a></li>
        @endif
        @if($show('assign_create', 'access-teacher-assignment'))
        <li class="{{ request()->routeIs('teacher.assign.create') ? 'mm-active' : '' }}"><a href="{{ route('teacher.assign.create') }}"><i class='bx bx-radio-circle'></i>Assign Class</a></li>
        @endif
    </ul>
</li>
@endif

{{-- Leave Management (Apply / My Leave Report links har staff sidebar mein pehle se hain; yahan sirf Leave Settings) --}}
@if($show('leave_settings', 'access-leave-setting'))
@php $open = request()->routeIs('staffleavesett.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-calendar-x'></i></div>
        <div class="menu-title">Leave Management</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        <li class="{{ request()->routeIs('staffleavesett.*') ? 'mm-active' : '' }}"><a href="{{ route('staffleavesett.index') }}"><i class='bx bx-radio-circle'></i>Leave Settings</a></li>
    </ul>
</li>
@endif

{{-- Academics (poora block reception sidebar mein pehle se hai: skip 'sec_academics') --}}
@php $checks = [['academics', 'access-academics'], ['class_timetable', 'access-class-timetable'], ['teacher_timetable', 'access-teacher-timetable'], ['rooms', 'access-rooms']]; @endphp
@if($sec('sec_academics') && $any($checks))
@php $open = request()->routeIs('classes.*', 'sections.*', 'groups.*', 'subjects.*', 'school-class-subjects.*', 'academic_years.*', 'class_timetable.*', 'teacher_timetable.*', 'rooms.*', 'academic_calendar.*', 'event_type.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="bx bx-book-reader"></i></div>
        <div class="menu-title">{{ $title('Academics', ['class_timetable']) }}</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        @if($show('academics', 'access-academics'))
        <li class="{{ request()->routeIs('classes.*') ? 'mm-active' : '' }}"><a href="{{ route('classes.index') }}"><i class='bx bx-radio-circle'></i>Manage Class</a></li>
        <li class="{{ request()->routeIs('sections.*') ? 'mm-active' : '' }}"><a href="{{ route('sections.create') }}"><i class='bx bx-radio-circle'></i>Manage Section</a></li>
        <li class="{{ request()->routeIs('groups.*') ? 'mm-active' : '' }}"><a href="{{ route('groups.create') }}"><i class='bx bx-radio-circle'></i>Manage Group</a></li>
        <li class="{{ request()->routeIs('subjects.*') ? 'mm-active' : '' }}"><a href="{{ route('subjects.create') }}"><i class='bx bx-radio-circle'></i>Manage Subject</a></li>
        <li class="{{ request()->routeIs('school-class-subjects.*') ? 'mm-active' : '' }}"><a href="{{ route('school-class-subjects.assign') }}"><i class='bx bx-radio-circle'></i>Manage Class-Subject</a></li>
        <li class="{{ request()->routeIs('academic_years.*') ? 'mm-active' : '' }}"><a href="{{ route('academic_years.index') }}"><i class='bx bx-radio-circle'></i>Manage Academic Year</a></li>
        @endif

        @if($any([['class_timetable', 'access-class-timetable'], ['teacher_timetable', 'access-teacher-timetable']]))
        <li class="{{ request()->routeIs('class_timetable.*', 'teacher_timetable.*') ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>TimeTable</a>
            <ul class="{{ request()->routeIs('class_timetable.*', 'teacher_timetable.*') ? 'mm-show' : '' }}">
                @if($show('class_timetable', 'access-class-timetable'))
                <li class="{{ request()->routeIs('class_timetable.*') ? 'mm-active' : '' }}"><a href="{{ route('class_timetable.index') }}"><i class='bx bx-right-arrow-alt'></i>Class Timetable</a></li>
                @endif
                @if($show('teacher_timetable', 'access-teacher-timetable'))
                <li class="{{ request()->routeIs('teacher_timetable.*') ? 'mm-active' : '' }}"><a href="{{ route('teacher_timetable.index') }}"><i class='bx bx-right-arrow-alt'></i>Teacher Timetable</a></li>
                @endif
            </ul>
        </li>
        @endif

        @if($show('rooms', 'access-rooms'))
        <li class="{{ request()->routeIs('rooms.*') ? 'mm-active' : '' }}"><a href="{{ route('rooms.index') }}"><i class='bx bx-radio-circle'></i>Manage Room</a></li>
        @endif

        @if($show('academics', 'access-academics'))
        <li class="{{ request()->routeIs('academic_calendar.*', 'event_type.*') ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Academic Calendar</a>
            <ul class="{{ request()->routeIs('academic_calendar.*', 'event_type.*') ? 'mm-show' : '' }}">
                <li class="{{ request()->routeIs('academic_calendar.*') ? 'mm-active' : '' }}"><a href="{{ route('academic_calendar.index') }}"><i class='bx bx-right-arrow-alt'></i>Academic Calendar</a></li>
                <li class="{{ request()->routeIs('event_type.*') ? 'mm-active' : '' }}"><a href="{{ route('event_type.index') }}"><i class='bx bx-right-arrow-alt'></i>Holiday</a></li>
            </ul>
        </li>
        @endif
    </ul>
</li>
@endif

{{-- Home Work --}}
@if($show('home_work', 'access-home-works'))
@php $open = request()->routeIs('home_work.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-task'></i></div>
        <div class="menu-title">Home Work</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        <li class="{{ request()->routeIs('home_work.*') ? 'mm-active' : '' }}"><a href="{{ route('home_work.index') }}"><i class='bx bx-radio-circle'></i>Manage Home Work</a></li>
    </ul>
</li>
@endif

{{-- Lesson Plan --}}
@php $checks = [['copy_old_lesson', 'access-copy-old-lessons'], ['lesson_plan', 'access-lesson-plans'], ['syllabus_status', 'access-syllabus-status'], ['lesson', 'access-lessons'], ['topic', 'access-topics']]; @endphp
@if($any($checks))
@php $open = request()->routeIs('copy_old_lesson.*', 'lesson_plan.*', 'syllabus_status.*', 'lesson.*', 'topic.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-book-content'></i></div>
        <div class="menu-title">Lesson Plan</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        @if($show('copy_old_lesson', 'access-copy-old-lessons'))
        <li class="{{ request()->routeIs('copy_old_lesson.*') ? 'mm-active' : '' }}"><a href="{{ route('copy_old_lesson.index') }}"><i class='bx bx-radio-circle'></i>Copy Old Lessons</a></li>
        @endif
        @if($show('lesson_plan', 'access-lesson-plans'))
        <li class="{{ request()->routeIs('lesson_plan.*') ? 'mm-active' : '' }}"><a href="{{ route('lesson_plan.index') }}"><i class='bx bx-radio-circle'></i>Manage Lesson Plan</a></li>
        @endif
        @if($show('syllabus_status', 'access-syllabus-status'))
        <li class="{{ request()->routeIs('syllabus_status.*') ? 'mm-active' : '' }}"><a href="{{ route('syllabus_status.index') }}"><i class='bx bx-radio-circle'></i>Manage Syllabus Status</a></li>
        @endif
        @if($show('lesson', 'access-lessons'))
        <li class="{{ request()->routeIs('lesson.*') ? 'mm-active' : '' }}"><a href="{{ route('lesson.index') }}"><i class='bx bx-radio-circle'></i>Manage Lesson</a></li>
        @endif
        @if($show('topic', 'access-topics'))
        <li class="{{ request()->routeIs('topic.*') ? 'mm-active' : '' }}"><a href="{{ route('topic.index') }}"><i class='bx bx-radio-circle'></i>Manage Topic</a></li>
        @endif
    </ul>
</li>
@endif

{{-- Examination --}}
@php $checks = [['exam_type', 'access-exam-types'], ['exam', 'access-exams'], ['exam_schedule', 'access-exam-schedules'], ['marksheet', 'access-marksheets'], ['result', 'access-results'], ['design_marksheet', 'access-marksheet-templates'], ['print_marksheet', 'access-marksheets'], ['skill_categories', 'access-skill-categories'], ['skill_areas', 'access-skill-assessment-areas'], ['skill_entry', 'access-skill-assessment-entry'], ['design_report_card', 'access-report-card-template'], ['print_report_card', 'access-print-report-card']]; @endphp
@if($any($checks))
@php $open = request()->routeIs('exam_type.*', 'exam.*', 'exam_schedule.*', 'marksheet.*', 'result.*', 'marks_grade.*', 'marksheet_template.*', 'print_marksheet.*', 'skill_categories.*', 'skill_assessment_areas.*', 'skill_assessment_entry.*', 'report_card_template.*', 'print_report_card.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-notepad'></i></div>
        <div class="menu-title">{{ $title('Examination', ['exam_schedule', 'marksheet', 'result', 'design_marksheet', 'print_marksheet', 'print_report_card']) }}</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        @if($show('exam_type', 'access-exam-types'))
        <li class="{{ request()->routeIs('exam_type.*') ? 'mm-active' : '' }}"><a href="{{ route('exam_type.index') }}"><i class='bx bx-radio-circle'></i>Exam Type</a></li>
        @endif
        @if($show('exam', 'access-exams'))
        <li class="{{ request()->routeIs('exam.*', 'marks_grade.*') ? 'mm-active' : '' }}"><a href="{{ route('exam.index') }}"><i class='bx bx-radio-circle'></i>Exam</a></li>
        @endif
        @if($show('exam_schedule', 'access-exam-schedules'))
        <li class="{{ request()->routeIs('exam_schedule.*') ? 'mm-active' : '' }}"><a href="{{ route('exam_schedule.index') }}"><i class='bx bx-radio-circle'></i>Exam Schedule</a></li>
        @endif
        @if($show('marksheet', 'access-marksheets'))
        <li class="{{ request()->routeIs('marksheet.*') ? 'mm-active' : '' }}"><a href="{{ route('marksheet.index') }}"><i class='bx bx-radio-circle'></i>MarkSheet</a></li>
        @endif
        @if($show('result', 'access-results'))
        <li class="{{ request()->routeIs('result.*') ? 'mm-active' : '' }}"><a href="{{ route('result.index') }}"><i class='bx bx-radio-circle'></i>Result</a></li>
        @endif
        @if($show('design_marksheet', 'access-marksheet-templates'))
        <li class="{{ request()->routeIs('marksheet_template.*') ? 'mm-active' : '' }}"><a href="{{ route('marksheet_template.index') }}"><i class='bx bx-radio-circle'></i>Design Marksheet</a></li>
        @endif
        @if($show('print_marksheet', 'access-marksheets'))
        <li class="{{ request()->routeIs('print_marksheet.*') ? 'mm-active' : '' }}"><a href="{{ route('print_marksheet.index') }}"><i class='bx bx-radio-circle'></i>Print Marksheet</a></li>
        @endif
        @if($show('skill_categories', 'access-skill-categories'))
        <li class="{{ request()->routeIs('skill_categories.*') ? 'mm-active' : '' }}"><a href="{{ route('skill_categories.index') }}"><i class='bx bx-radio-circle'></i>Skill Categories</a></li>
        @endif
        @if($show('skill_areas', 'access-skill-assessment-areas'))
        <li class="{{ request()->routeIs('skill_assessment_areas.*') ? 'mm-active' : '' }}"><a href="{{ route('skill_assessment_areas.index') }}"><i class='bx bx-radio-circle'></i>Skill Assessment Areas</a></li>
        @endif
        @if($show('skill_entry', 'access-skill-assessment-entry'))
        <li class="{{ request()->routeIs('skill_assessment_entry.*') ? 'mm-active' : '' }}"><a href="{{ route('skill_assessment_entry.index') }}"><i class='bx bx-radio-circle'></i>Skill Assessment Entry</a></li>
        @endif
        @if($show('design_report_card', 'access-report-card-template'))
        <li class="{{ request()->routeIs('report_card_template.*') ? 'mm-active' : '' }}"><a href="{{ route('report_card_template.index') }}"><i class='bx bx-radio-circle'></i>Design Report Card</a></li>
        @endif
        @if($show('print_report_card', 'access-print-report-card'))
        <li class="{{ request()->routeIs('print_report_card.*') ? 'mm-active' : '' }}"><a href="{{ route('print_report_card.index') }}"><i class='bx bx-radio-circle'></i>Print Report Card</a></li>
        @endif
    </ul>
</li>
@endif

{{-- Attendance --}}
@php $checks = [['att_mark', ['access-attendance', 'mark-attendance']], ['att_scan', 'access-scan-attendance'], ['att_report', 'access-attendance'], ['staff_att_mark', ['access-staff-attendance', 'mark-staff-attendance']], ['staff_att_report', 'access-staff-attendance']]; @endphp
@if($any($checks))
@php $open = request()->routeIs('attendance.*', 'staff_attendance.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-check-shield'></i></div>
        <div class="menu-title">{{ $title('Attendance', ['att_mark', 'att_scan', 'att_report', 'staff_att_mark', 'staff_att_report']) }}</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        @if($any([['att_mark', ['access-attendance', 'mark-attendance']], ['att_scan', 'access-scan-attendance'], ['att_report', 'access-attendance']]))
        @php $subOpen = request()->routeIs('attendance.*'); @endphp
        <li class="{{ $subOpen ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Student Attendance</a>
            <ul class="{{ $subOpen ? 'mm-show' : '' }}">
                @if($show('att_mark', ['access-attendance', 'mark-attendance']))
                <li class="{{ request()->routeIs('attendance.create', 'attendance.mark') ? 'mm-active' : '' }}"><a href="{{ route('attendance.create') }}"><i class='bx bx-right-arrow-alt'></i>Mark Attendance</a></li>
                @endif
                @if($show('att_scan', 'access-scan-attendance'))
                <li class="{{ request()->routeIs('attendance.scan*') ? 'mm-active' : '' }}"><a href="{{ route('attendance.scan') }}"><i class='bx bx-right-arrow-alt'></i>Scan Attendance</a></li>
                @endif
                @if($show('att_report', 'access-attendance'))
                <li class="{{ request()->routeIs('attendance.index', 'attendance.details', 'attendance.report', 'attendance.edit', 'attendance.class_report', 'attendance.student_report', 'attendance.daily') ? 'mm-active' : '' }}"><a href="{{ route('attendance.index') }}"><i class='bx bx-right-arrow-alt'></i>Attendance Report</a></li>
                @endif
            </ul>
        </li>
        @endif

        @if($any([['staff_att_mark', ['access-staff-attendance', 'mark-staff-attendance']], ['staff_att_report', 'access-staff-attendance']]))
        @php $subOpen = request()->routeIs('staff_attendance.*'); @endphp
        <li class="{{ $subOpen ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Staff Attendance</a>
            <ul class="{{ $subOpen ? 'mm-show' : '' }}">
                @if($show('staff_att_mark', ['access-staff-attendance', 'mark-staff-attendance']))
                <li class="{{ request()->routeIs('staff_attendance.create', 'staff_attendance.edit') ? 'mm-active' : '' }}"><a href="{{ route('staff_attendance.create') }}"><i class='bx bx-right-arrow-alt'></i>Mark Attendance</a></li>
                @endif
                @if($show('staff_att_report', 'access-staff-attendance'))
                <li class="{{ request()->routeIs('staff_attendance.report', 'staff_attendance.show', 'staff_attendance.index') ? 'mm-active' : '' }}"><a href="{{ route('staff_attendance.report') }}"><i class='bx bx-right-arrow-alt'></i>Attendance Report</a></li>
                @endif
            </ul>
        </li>
        @endif
    </ul>
</li>
@endif

{{-- Payroll (Salary Structure route abhi nahi bana, isliye link nahi) --}}
@if($show('salary_slips', 'access-salary-slips'))
@php $open = request()->routeIs('salary_slips.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-wallet-alt'></i></div>
        <div class="menu-title">Payroll</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        <li class="{{ request()->routeIs('salary_slips.*') ? 'mm-active' : '' }}"><a href="{{ route('salary_slips.index') }}"><i class='bx bx-radio-circle'></i>Salary Slips</a></li>
    </ul>
</li>
@endif

{{-- Finance Overview --}}
@if($show('finance_report', 'access-finance-report'))
@php $open = request()->routeIs('finance_report.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-line-chart'></i></div>
        <div class="menu-title">Finance Overview</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        <li class="{{ request()->routeIs('finance_report.*') ? 'mm-active' : '' }}"><a href="{{ route('finance_report.index') }}"><i class='bx bx-radio-circle'></i>Finance Report</a></li>
    </ul>
</li>
@endif

{{-- Front Office (poora block reception sidebar mein pehle se hai: skip 'sec_front_office') --}}
@if($sec('sec_front_office') && $show('front_office', 'access-front-office'))
@php $open = request()->routeIs('settings.*', 'admission-enquiry.*', 'visitor-book.*', 'visitor.*', 'complain.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-store-alt'></i></div>
        <div class="menu-title">Front Office</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        @php $subOpen = request()->routeIs('settings.*'); @endphp
        <li class="{{ $subOpen ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Front Office Setup</a>
            <ul class="{{ $subOpen ? 'mm-show' : '' }}">
                <li class="{{ request()->routeIs('settings.purpose.*') ? 'mm-active' : '' }}"><a href="{{ route('settings.purpose.index') }}"><i class='bx bx-chevron-right'></i>Purpose</a></li>
                <li class="{{ request()->routeIs('settings.complaint-type.*') ? 'mm-active' : '' }}"><a href="{{ route('settings.complaint-type.index') }}"><i class='bx bx-chevron-right'></i>Complaint-Type</a></li>
                <li class="{{ request()->routeIs('settings.source.*') ? 'mm-active' : '' }}"><a href="{{ route('settings.source.index') }}"><i class='bx bx-chevron-right'></i>Source</a></li>
            </ul>
        </li>
        <li class="{{ request()->routeIs('admission-enquiry.*') ? 'mm-active' : '' }}"><a href="{{ route('admission-enquiry.index') }}"><i class='bx bx-radio-circle'></i>Admission Enquiry</a></li>
        <li class="{{ request()->routeIs('visitor-book.*', 'visitor.*') ? 'mm-active' : '' }}"><a href="{{ route('visitor-book.index') }}"><i class='bx bx-radio-circle'></i>Visitor Book</a></li>
        <li class="{{ request()->routeIs('complain.*') ? 'mm-active' : '' }}"><a href="{{ route('complain.index') }}"><i class='bx bx-radio-circle'></i>Complaint</a></li>
    </ul>
</li>
@endif

{{-- Fees Collection (har item wahi permission jo uska route maangta hai; Fee Reports route = manage-fee-collections) --}}
@php $checks = [['fee_types', 'access-fee-types'], ['fee_structure', 'access-fee-structures'], ['fee_collect', 'access-fee-collections'], ['fee_assign', 'access-fee-assignments'], ['fee_report', 'manage-fee-collections'], ['bank_accounts', 'access-bank-accounts'], ['discount_policies', 'access-discount-policies'], ['billing_settings', 'access-billing-settings'], ['expense_categories', 'access-expense-categories'], ['expenses', 'access-expenses']]; @endphp
@if($any($checks))
@php $open = request()->routeIs('fee_types.*', 'fee_structure.*', 'fee_assignments.*', 'fees.*', 'bank_accounts.*', 'discount_policies.*', 'fine_policies.*', 'billing_settings.*', 'expense_categories.*', 'expenses.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="bx bx-money"></i></div>
        <div class="menu-title">{{ $title('Fees Collection', ['fee_types', 'fee_structure', 'fee_collect', 'fee_report', 'bank_accounts', 'discount_policies', 'expense_categories', 'expenses']) }}</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        @if($show('fee_types', 'access-fee-types'))
        <li class="{{ request()->routeIs('fee_types.*') ? 'mm-active' : '' }}"><a href="{{ route('fee_types.index') }}"><i class='bx bx-radio-circle'></i>Fee Type</a></li>
        @endif
        @if($show('fee_structure', 'access-fee-structures'))
        <li class="{{ request()->routeIs('fee_structure.*') ? 'mm-active' : '' }}"><a href="{{ route('fee_structure.index') }}"><i class='bx bx-radio-circle'></i>Fee Structure</a></li>
        @endif
        @if($show('fee_collect', 'access-fee-collections'))
        <li class="{{ request()->routeIs('fees.collect.index', 'fees.collect.show', 'fees.collect.receipt') ? 'mm-active' : '' }}"><a href="{{ route('fees.collect.index') }}"><i class='bx bx-radio-circle'></i>Collect Fees</a></li>
        @endif
        @if($show('fee_assign', 'access-fee-assignments'))
        <li class="{{ request()->routeIs('fee_assignments.*') ? 'mm-active' : '' }}"><a href="{{ route('fee_assignments.index') }}"><i class='bx bx-radio-circle'></i>Assign Fee</a></li>
        @endif
        @if($show('fee_report', 'manage-fee-collections'))
        <li class="{{ request()->routeIs('fees.collect.report') ? 'mm-active' : '' }}"><a href="{{ route('fees.collect.report') }}"><i class='bx bx-radio-circle'></i>Fee Reports</a></li>
        @endif
        @if($show('bank_accounts', 'access-bank-accounts'))
        <li class="{{ request()->routeIs('bank_accounts.*') ? 'mm-active' : '' }}"><a href="{{ route('bank_accounts.index') }}"><i class='bx bx-radio-circle'></i>Bank Accounts</a></li>
        @endif

        @if($any([['discount_policies', 'access-discount-policies'], ['billing_settings', 'access-billing-settings']]))
        @php $subOpen = request()->routeIs('discount_policies.*', 'fine_policies.*', 'billing_settings.*'); @endphp
        <li class="{{ $subOpen ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Fee Settings</a>
            <ul class="{{ $subOpen ? 'mm-show' : '' }}">
                @if($show('discount_policies', 'access-discount-policies'))
                <li class="{{ request()->routeIs('discount_policies.*') ? 'mm-active' : '' }}"><a href="{{ route('discount_policies.index') }}"><i class='bx bx-radio-circle'></i>Discount Policies</a></li>
                @endif
                @if($show('billing_settings', 'access-billing-settings'))
                <li class="{{ request()->routeIs('billing_settings.*') ? 'mm-active' : '' }}"><a href="{{ route('billing_settings.index') }}"><i class='bx bx-radio-circle'></i>Billing Settings</a></li>
                @endif
                {{-- Fine Policies CRUD is not built yet (FinePolicyController is empty) - link hidden
                @can('access-fine-policies')
                <li class="{{ request()->routeIs('fine_policies.*') ? 'mm-active' : '' }}"><a href="{{ route('fine_policies.index') }}"><i class='bx bx-radio-circle'></i>Fine Policies</a></li>
                @endcan
                --}}
            </ul>
        </li>
        @endif

        @if($any([['expense_categories', 'access-expense-categories'], ['expenses', 'access-expenses']]))
        @php $subOpen = request()->routeIs('expense_categories.*', 'expenses.*'); @endphp
        <li class="{{ $subOpen ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Expenses</a>
            <ul class="{{ $subOpen ? 'mm-show' : '' }}">
                @if($show('expense_categories', 'access-expense-categories'))
                <li class="{{ request()->routeIs('expense_categories.*') ? 'mm-active' : '' }}"><a href="{{ route('expense_categories.index') }}"><i class='bx bx-radio-circle'></i>Expense Categories</a></li>
                @endif
                @if($show('expenses', 'access-expenses'))
                <li class="{{ request()->routeIs('expenses.*') ? 'mm-active' : '' }}"><a href="{{ route('expenses.index') }}"><i class='bx bx-radio-circle'></i>Expense</a></li>
                @endif
            </ul>
        </li>
        @endif
    </ul>
</li>
@endif

{{-- Library (librarian sidebar mein pehle se hai: uske keys skip) --}}
@php $checks = [['lib_categories', 'access-book-categories'], ['lib_books', 'access-books'], ['lib_issue', 'access-book-issues'], ['lib_settings', 'access-library-settings'], ['lib_members', 'access-library-members']]; @endphp
@if($any($checks))
@php $open = request()->routeIs('book_category.*', 'books.*', 'book_issue.*', 'library_settings.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="bx bx-book-alt"></i></div>
        <div class="menu-title">{{ $title('Library', ['lib_categories', 'lib_books', 'lib_issue', 'lib_settings', 'lib_members']) }}</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        @if($show('lib_categories', 'access-book-categories'))
        <li class="{{ request()->routeIs('book_category.*') ? 'mm-active' : '' }}"><a href="{{ route('book_category.index') }}"><i class='bx bx-radio-circle'></i>Book Category</a></li>
        @endif
        @if($show('lib_books', 'access-books'))
        <li class="{{ request()->routeIs('books.*') ? 'mm-active' : '' }}"><a href="{{ route('books.index') }}"><i class='bx bx-radio-circle'></i>Books</a></li>
        @endif
        @if($show('lib_issue', 'access-book-issues'))
        <li class="{{ request()->routeIs('book_issue.*') ? 'mm-active' : '' }}"><a href="{{ route('book_issue.index') }}"><i class='bx bx-radio-circle'></i>Book Issues</a></li>
        @endif
        @if($show('lib_settings', 'access-library-settings'))
        <li class="{{ request()->routeIs('library_settings.index', 'library_settings.update') ? 'mm-active' : '' }}"><a href="{{ route('library_settings.index') }}"><i class='bx bx-radio-circle'></i>Library Settings</a></li>
        @endif
        @if($show('lib_members', 'access-library-members'))
        <li class="{{ request()->routeIs('library_settings.members.student*') ? 'mm-active' : '' }}"><a href="{{ route('library_settings.members.student') }}"><i class='bx bx-radio-circle'></i>Library Students</a></li>
        <li class="{{ request()->routeIs('library_settings.members.staff*') ? 'mm-active' : '' }}"><a href="{{ route('library_settings.members.staff') }}"><i class='bx bx-radio-circle'></i>Library Staff</a></li>
        @endif
    </ul>
</li>
@endif

{{-- Site Setup --}}
@php $checks = [['site_settings', 'access-site-settings'], ['school_timing', 'access-school-timing']]; @endphp
@if($any($checks))
@php $open = request()->routeIs('site_setting.*', 'school_timing.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-layer'></i></div>
        <div class="menu-title">Site Setup</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        @if($show('site_settings', 'access-site-settings'))
        <li class="{{ request()->routeIs('site_setting.*') ? 'mm-active' : '' }}"><a href="{{ route('site_setting.edit') }}"><i class='bx bx-radio-circle'></i>Site Settings</a></li>
        @endif
        @if($show('school_timing', 'access-school-timing'))
        <li class="{{ request()->routeIs('school_timing.*') ? 'mm-active' : '' }}"><a href="{{ route('school_timing.index') }}"><i class='bx bx-radio-circle'></i>School Timing</a></li>
        @endif
    </ul>
</li>
@endif

{{-- ID Cards --}}
@php $checks = [['id_design', 'access-id-card-templates'], ['id_print', 'access-id-card-templates'], ['staff_id_design', 'access-staff-id-card-templates'], ['staff_id_print', 'access-staff-id-card-templates']]; @endphp
@if($any($checks))
@php $open = request()->routeIs('id_card_template.*', 'print_id_card.*', 'staff_id_card_template.*', 'print_staff_id_card.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-id-card'></i></div>
        <div class="menu-title">{{ $title('ID Cards', ['id_print', 'staff_id_print']) }}</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        @if($show('id_design', 'access-id-card-templates'))
        <li class="{{ request()->routeIs('id_card_template.*') ? 'mm-active' : '' }}"><a href="{{ route('id_card_template.index') }}"><i class='bx bx-radio-circle'></i>Design Student ID Card</a></li>
        @endif
        @if($show('id_print', 'access-id-card-templates'))
        <li class="{{ request()->routeIs('print_id_card.*') ? 'mm-active' : '' }}"><a href="{{ route('print_id_card.index') }}"><i class='bx bx-radio-circle'></i>Print Student ID Card</a></li>
        @endif
        @if($show('staff_id_design', 'access-staff-id-card-templates'))
        <li class="{{ request()->routeIs('staff_id_card_template.*') ? 'mm-active' : '' }}"><a href="{{ route('staff_id_card_template.index') }}"><i class='bx bx-radio-circle'></i>Design Staff ID Card</a></li>
        @endif
        @if($show('staff_id_print', 'access-staff-id-card-templates'))
        <li class="{{ request()->routeIs('print_staff_id_card.*') ? 'mm-active' : '' }}"><a href="{{ route('print_staff_id_card.index') }}"><i class='bx bx-radio-circle'></i>Print Staff ID Card</a></li>
        @endif
    </ul>
</li>
@endif
