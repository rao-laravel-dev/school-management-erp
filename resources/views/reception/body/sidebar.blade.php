<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div><img src="{{ asset('backend/assets/images/logo-icon.png') }}" class="logo-icon" alt="logo icon"></div>
        <div>
            <h4 class="logo-text">Receptionist</h4>
        </div>
        <div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i></div>
    </div>

    <ul class="metismenu" id="menu">
        <li>
            <a href="{{ route('reception.dashboard') }}">
                <div class="parent-icon"><i class='bx bx-home-alt'></i></div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>

        {{-- Front Office --}}
        @can('access-front-office')
        @php $open = request()->routeIs('admission-enquiry.*', 'complain.*', 'visitor-book.*', 'visitor.*', 'settings.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-building"></i></div>
                <div class="menu-title">Front Office</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                <li class="{{ request()->routeIs('admission-enquiry.*') ? 'mm-active' : '' }}"><a href="{{ route('admission-enquiry.index') }}"><i class='bx bx-radio-circle'></i>Admission Enquiry</a></li>
                <li class="{{ request()->routeIs('complain.*') ? 'mm-active' : '' }}"><a href="{{ route('complain.index') }}"><i class='bx bx-radio-circle'></i>Complaints</a></li>
                <li class="{{ request()->routeIs('visitor-book.*', 'visitor.*') ? 'mm-active' : '' }}"><a href="{{ route('visitor-book.index') }}"><i class='bx bx-radio-circle'></i>Visitor Book</a></li>
                <li class="{{ request()->routeIs('settings.complaint-type.*') ? 'mm-active' : '' }}"><a href="{{ route('settings.complaint-type.index') }}"><i class='bx bx-radio-circle'></i>Complaint Type</a></li>
                <li class="{{ request()->routeIs('settings.purpose.*') ? 'mm-active' : '' }}"><a href="{{ route('settings.purpose.index') }}"><i class='bx bx-radio-circle'></i>Purpose</a></li>
                <li class="{{ request()->routeIs('settings.source.*') ? 'mm-active' : '' }}"><a href="{{ route('settings.source.index') }}"><i class='bx bx-radio-circle'></i>Source</a></li>
            </ul>
        </li>
        @endcan

        @can('access-students')
        @php $open = request()->routeIs('students.*', 'student-category.*', 'student-house.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-user-plus"></i></div>
                <div class="menu-title">Student Information</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                <li class="{{ request()->routeIs('students.*') && ! request()->routeIs('students.create') ? 'mm-active' : '' }}"> <a href="{{ route('students.index') }}"><i class='bx bx-radio-circle'></i>All Students</a></li>
                <li class="{{ request()->routeIs('students.create') ? 'mm-active' : '' }}"> <a href="{{ route('students.create') }}"><i class='bx bx-radio-circle'></i>Student's Admission</a></li>
                @can('access-student-categories')
                <li class="{{ request()->routeIs('student-category.*') ? 'mm-active' : '' }}"> <a href="{{ route('student-category.index') }}"><i class='bx bx-radio-circle'></i>Student Categories</a></li>
                @endcan
                @can('access-student-houses')
                <li class="{{ request()->routeIs('student-house.*') ? 'mm-active' : '' }}"> <a href="{{ route('student-house.index') }}"><i class='bx bx-radio-circle'></i>Student Houses</a></li>
                @endcan
            </ul>
        </li>
        @endcan

        @canany(['access-teacher', 'manage-teacher', 'access-teacher-assignment', 'manage-teacher-assignment'])
        @php $open = request()->routeIs('teacher.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-chalkboard"></i></div>
                <div class="menu-title">Teacher Management</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                {{-- Teachers Section --}}
                @can('access-teacher')
                <li class="{{ request()->routeIs('teacher.index', 'teacher.edit') ? 'mm-active' : '' }}"><a href="{{ route('teacher.index') }}"><i class='bx bx-radio-circle'></i>All Teachers</a></li>
                @endcan
                @can('manage-teacher')
                <li class="{{ request()->routeIs('teacher.create') ? 'mm-active' : '' }}"><a href="{{ route('teacher.create') }}"><i class='bx bx-radio-circle'></i>Add Teacher</a></li>
                @endcan

                {{-- Assignments Section --}}
                @can('access-teacher-assignment')
                <li class="{{ request()->routeIs('teacher.assign.index', 'teacher.assign.edit') ? 'mm-active' : '' }}"><a href="{{ route('teacher.assign.index') }}"><i class='bx bx-radio-circle'></i>Assign Class List</a></li>
                @endcan
                @can('access-teacher-assignment')
                <li class="{{ request()->routeIs('teacher.assign.create') ? 'mm-active' : '' }}"><a href="{{ route('teacher.assign.create') }}"><i class='bx bx-radio-circle'></i>Assign Class Teacher</a></li>
                @endcan
            </ul>
        </li>
        @endcanany

        @can('access-leave-application')
        @php $open = request()->routeIs('staffleaveapp.create'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-calendar-x'></i></div>
                <div class="menu-title">Leave Applications</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                {{-- create route index page par redirect karta hai (apply modal wahin hai) --}}
                <li class="{{ request()->routeIs('staffleaveapp.create') ? 'mm-active' : '' }}">
                    <a href="{{ route('staffleaveapp.create') }}">
                        <i class='bx bx-radio-circle'></i>Apply Leave
                    </a>
                </li>
            </ul>
        </li>
        @endcan

        {{-- Attendance: Scan + Mark Staff Attendance (gate); reports ke links "Reports" menu mein hain --}}
        @canany(['access-scan-attendance', 'mark-staff-attendance'])
        @php $open = request()->routeIs('attendance.scan*', 'staff_attendance.create', 'staff_attendance.edit'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-calendar-check"></i></div>
                <div class="menu-title">Attendance</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-scan-attendance')
                <li class="{{ request()->routeIs('attendance.scan', 'attendance.scan.*') ? 'mm-active' : '' }}"><a href="{{ route('attendance.scan') }}"><i class='bx bx-radio-circle'></i>Scan Attendance</a></li>
                @endcan
                @can('mark-staff-attendance')
                <li class="{{ request()->routeIs('staff_attendance.create', 'staff_attendance.edit') ? 'mm-active' : '' }}"><a href="{{ route('staff_attendance.create') }}"><i class='bx bx-radio-circle'></i>Mark Staff Attendance</a></li>
                @endcan
            </ul>
        </li>
        @endcanany

        @can('access-academics')
        @php $open = request()->routeIs('classes.*', 'sections.*', 'groups.*', 'subjects.*', 'school-class-subjects.*', 'academic_years.*', 'class_timetable.*', 'teacher_timetable.*', 'rooms.*', 'school_timing.*', 'academic_calendar.*', 'event_type.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-category"></i></div>
                <div class="menu-title">Academics</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                {{-- Classes --}}
                @php $subOpen = request()->routeIs('classes.*'); @endphp
                <li class="{{ $subOpen ? 'mm-active' : '' }}">
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Classes</a>
                    <ul class="{{ $subOpen ? 'mm-show' : '' }}">
                        <li class="{{ request()->routeIs('classes.*') ? 'mm-active' : '' }}"><a href="{{ route('classes.index') }}"><i class='bx bx-right-arrow-alt'></i>Classes List</a></li>
                    </ul>
                </li>

                {{-- Sections --}}
                @php $subOpen = request()->routeIs('sections.*'); @endphp
                <li class="{{ $subOpen ? 'mm-active' : '' }}">
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Sections</a>
                    <ul class="{{ $subOpen ? 'mm-show' : '' }}">
                        <li class="{{ request()->routeIs('sections.index', 'sections.edit') ? 'mm-active' : '' }}"><a href="{{ route('sections.index') }}"><i class='bx bx-right-arrow-alt'></i>Sections List</a></li>
                        <li class="{{ request()->routeIs('sections.create') ? 'mm-active' : '' }}"><a href="{{ route('sections.create') }}"><i class='bx bx-right-arrow-alt'></i>Add Section</a></li>
                    </ul>
                </li>

                {{-- Groups --}}
                @php $subOpen = request()->routeIs('groups.*'); @endphp
                <li class="{{ $subOpen ? 'mm-active' : '' }}">
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Groups</a>
                    <ul class="{{ $subOpen ? 'mm-show' : '' }}">
                        <li class="{{ request()->routeIs('groups.index', 'groups.edit') ? 'mm-active' : '' }}"><a href="{{ route('groups.index') }}"><i class='bx bx-right-arrow-alt'></i>Groups List</a></li>
                        <li class="{{ request()->routeIs('groups.create') ? 'mm-active' : '' }}"><a href="{{ route('groups.create') }}"><i class='bx bx-right-arrow-alt'></i>Add Group</a></li>
                    </ul>
                </li>

                {{-- Subjects --}}
                @php $subOpen = request()->routeIs('subjects.*'); @endphp
                <li class="{{ $subOpen ? 'mm-active' : '' }}">
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Subjects</a>
                    <ul class="{{ $subOpen ? 'mm-show' : '' }}">
                        <li class="{{ request()->routeIs('subjects.index', 'subjects.edit') ? 'mm-active' : '' }}"><a href="{{ route('subjects.index') }}"><i class='bx bx-right-arrow-alt'></i>Subjects List</a></li>
                        <li class="{{ request()->routeIs('subjects.create') ? 'mm-active' : '' }}"><a href="{{ route('subjects.create') }}"><i class='bx bx-right-arrow-alt'></i>Add Subject</a></li>
                    </ul>
                </li>

                {{-- Class Subjects --}}
                @php $subOpen = request()->routeIs('school-class-subjects.*'); @endphp
                <li class="{{ $subOpen ? 'mm-active' : '' }}">
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Class Subjects</a>
                    <ul class="{{ $subOpen ? 'mm-show' : '' }}">
                        <li class="{{ request()->routeIs('school-class-subjects.index', 'school-class-subjects.edit') ? 'mm-active' : '' }}"><a href="{{ route('school-class-subjects.index') }}"><i class='bx bx-right-arrow-alt'></i>List</a></li>
                        <li class="{{ request()->routeIs('school-class-subjects.assign') ? 'mm-active' : '' }}"><a href="{{ route('school-class-subjects.assign') }}"><i class='bx bx-right-arrow-alt'></i>Assign New</a></li>
                    </ul>
                </li>

                {{-- Academic Year --}}
                <li class="{{ request()->routeIs('academic_years.*') ? 'mm-active' : '' }}"><a href="{{ route('academic_years.index') }}"><i class='bx bx-radio-circle'></i>Academic Year</a></li>

                {{-- TimeTable --}}
                @canany(['access-class-timetable', 'access-teacher-timetable'])
                @php $subOpen = request()->routeIs('class_timetable.*', 'teacher_timetable.*'); @endphp
                <li class="{{ $subOpen ? 'mm-active' : '' }}">
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>TimeTable</a>
                    <ul class="{{ $subOpen ? 'mm-show' : '' }}">
                        @can('access-class-timetable')
                        <li class="{{ request()->routeIs('class_timetable.*') ? 'mm-active' : '' }}"><a href="{{ route('class_timetable.index') }}"><i class='bx bx-right-arrow-alt'></i>Class Timetable</a></li>
                        @endcan
                        @can('access-teacher-timetable')
                        <li class="{{ request()->routeIs('teacher_timetable.*') ? 'mm-active' : '' }}"><a href="{{ route('teacher_timetable.index') }}"><i class='bx bx-right-arrow-alt'></i>Teacher Timetable</a></li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Rooms --}}
                @can('access-rooms')
                <li class="{{ request()->routeIs('rooms.*') ? 'mm-active' : '' }}"><a href="{{ route('rooms.index') }}"><i class='bx bx-radio-circle'></i>Manage Room</a></li>
                @endcan

                {{-- School Timing --}}
                @can('access-school-timing')
                <li class="{{ request()->routeIs('school_timing.*') ? 'mm-active' : '' }}"><a href="{{ route('school_timing.index') }}"><i class='bx bx-radio-circle'></i>School Timing</a></li>
                @endcan

                {{-- Academic Calendar --}}
                @php $subOpen = request()->routeIs('academic_calendar.*', 'event_type.*'); @endphp
                <li class="{{ $subOpen ? 'mm-active' : '' }}">
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Academic Calendar</a>
                    <ul class="{{ $subOpen ? 'mm-show' : '' }}">
                        <li class="{{ request()->routeIs('academic_calendar.*') ? 'mm-active' : '' }}"><a href="{{ route('academic_calendar.index') }}"><i class='bx bx-right-arrow-alt'></i>Academic Calendar</a></li>
                        <li class="{{ request()->routeIs('event_type.*') ? 'mm-active' : '' }}"><a href="{{ route('event_type.index') }}"><i class='bx bx-right-arrow-alt'></i>Holiday</a></li>
                    </ul>
                </li>
            </ul>
        </li>
        @endcan

        {{-- Examination --}}
        @canany(['access-exam-schedules', 'access-results', 'access-marksheets', 'access-marksheet-templates', 'access-print-report-card'])
        @php $open = request()->routeIs('exam_schedule.*', 'result.*', 'print_marksheet.*', 'marksheet_template.*', 'print_report_card.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-notepad'></i></div>
                <div class="menu-title">Examination</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-exam-schedules')
                <li class="{{ request()->routeIs('exam_schedule.*') ? 'mm-active' : '' }}"><a href="{{ route('exam_schedule.index') }}"><i class='bx bx-radio-circle'></i>Exam Schedule</a></li>
                @endcan
                @can('access-results')
                <li class="{{ request()->routeIs('result.*') ? 'mm-active' : '' }}"><a href="{{ route('result.index') }}"><i class='bx bx-radio-circle'></i>Result</a></li>
                @endcan
                @can('access-marksheets')
                <li class="{{ request()->routeIs('print_marksheet.*') ? 'mm-active' : '' }}"><a href="{{ route('print_marksheet.index') }}"><i class='bx bx-radio-circle'></i>Print Marksheet</a></li>
                @endcan
                @can('access-marksheet-templates')
                <li class="{{ request()->routeIs('marksheet_template.*') ? 'mm-active' : '' }}"><a href="{{ route('marksheet_template.index') }}"><i class='bx bx-radio-circle'></i>Marksheet Template</a></li>
                @endcan
                @can('access-print-report-card')
                <li class="{{ request()->routeIs('print_report_card.*') ? 'mm-active' : '' }}"><a href="{{ route('print_report_card.index') }}"><i class='bx bx-radio-circle'></i>Print Report Card</a></li>
                @endcan
            </ul>
        </li>
        @endcanany

        {{-- ID Cards (sirf print) --}}
        @canany(['access-id-card-templates', 'access-staff-id-card-templates'])
        @php $open = request()->routeIs('print_id_card.*', 'print_staff_id_card.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-id-card'></i></div>
                <div class="menu-title">ID Cards</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-id-card-templates')
                <li class="{{ request()->routeIs('print_id_card.*') ? 'mm-active' : '' }}"><a href="{{ route('print_id_card.index') }}"><i class='bx bx-radio-circle'></i>Print Student ID Card</a></li>
                @endcan
                @can('access-staff-id-card-templates')
                <li class="{{ request()->routeIs('print_staff_id_card.*') ? 'mm-active' : '' }}"><a href="{{ route('print_staff_id_card.index') }}"><i class='bx bx-radio-circle'></i>Print Staff ID Card</a></li>
                @endcan
            </ul>
        </li>
        @endcanany

        {{-- Reports: logged-in user ki apni reports + attendance reports (aage aur links yahan aayenge) --}}
        @canany(['access-my-attendance', 'access-leave-application', 'access-my-salary-slips', 'access-attendance', 'access-staff-attendance'])
        @php $open = request()->routeIs('my.*', 'staffleaveapp.*', 'attendance.index', 'attendance.class_report', 'attendance.student_report', 'staff_attendance.report') && ! request()->routeIs('staffleaveapp.create'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-file'></i></div>
                <div class="menu-title">Reports</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-my-attendance')
                <li class="{{ request()->routeIs('my.attendance.*') ? 'mm-active' : '' }}"><a href="{{ route('my.attendance.index') }}"><i class='bx bx-radio-circle'></i>My Attendance Report</a></li>
                @endcan
                @can('access-leave-application')
                <li class="{{ request()->routeIs('staffleaveapp.*') && ! request()->routeIs('staffleaveapp.create') ? 'mm-active' : '' }}"><a href="{{ route('staffleaveapp.index') }}"><i class='bx bx-radio-circle'></i>My Leave Report</a></li>
                @endcan
                @can('access-my-salary-slips')
                <li class="{{ request()->routeIs('my.salary_slips.*') ? 'mm-active' : '' }}"><a href="{{ route('my.salary_slips.index') }}"><i class='bx bx-radio-circle'></i>My Salary Slip</a></li>
                @endcan
                @can('access-attendance')
                <li class="{{ request()->routeIs('attendance.index', 'attendance.class_report', 'attendance.student_report') ? 'mm-active' : '' }}"><a href="{{ route('attendance.index') }}"><i class='bx bx-radio-circle'></i>Class Attendance Report</a></li>
                @endcan
                @can('access-staff-attendance')
                <li class="{{ request()->routeIs('staff_attendance.report') ? 'mm-active' : '' }}"><a href="{{ route('staff_attendance.report') }}"><i class='bx bx-radio-circle'></i>Staff Attendance Report</a></li>
                @endcan
            </ul>
        </li>
        @endcanany

        {{-- Logout (hamesha last) --}}
        <li>
            <a href="{{ route('reception.logout') }}">
                <div class="parent-icon"><i class="bx bx-log-out-circle"></i></div>
                <div class="menu-title">Logout</div>
            </a>
        </li>
    </ul>
</div>
