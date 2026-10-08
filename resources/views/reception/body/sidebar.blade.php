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

        {{-- Receptionist ke pass apni detail edit karne ki permission hoti hai, admin. ki nahi --}}
        @can('access-receptionist')
        <li>
            <a href="{{ route('receptionist.index') }}">
                <div class="parent-icon"><i class="bx bx-user-circle"></i></div>
                <div class="menu-title">My Profile / Receptionist</div>
            </a>
        </li>
        @endcan

        @can('access-students')
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-user-plus"></i></div>
                <div class="menu-title">Student Information</div>
            </a>
            <ul>
                <li> <a href="{{ route('students.index') }}"><i class='bx bx-radio-circle'></i>All Students</a></li>
                <li> <a href="{{ route('students.create') }}"><i class='bx bx-radio-circle'></i>Student's Admission</a></li>
            </ul>
        </li>
        @endcan

        @canany(['access-teacher', 'manage-teacher', 'access-teacher-assignment', 'manage-teacher-assignment'])
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-chalkboard"></i></div>
                <div class="menu-title">Teacher Management</div>
            </a>
            <ul>
                {{-- Teachers Section --}}
                @can('access-teacher')
                <li><a href="{{ route('teacher.index') }}"><i class='bx bx-radio-circle'></i>All Teachers</a></li>
                @endcan
                @can('access-teacher')
                <li><a href="{{ route('teacher.create') }}"><i class='bx bx-radio-circle'></i>Add Teacher</a></li>
                @endcan

                {{-- Assignments Section --}}
                @can('access-teacher-assignment')
                <li><a href="{{ route('teacher.assign.index') }}"><i class='bx bx-radio-circle'></i>Assign Class List</a></li>
                @endcan
                @can('access-teacher-assignment')
                <li><a href="{{ route('teacher.assign.create') }}"><i class='bx bx-radio-circle'></i>Assign Class Teacher</a></li>
                @endcan
            </ul>
        </li>
        @endcanany

        @can('access-leave-application')
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-calendar-x'></i></div>
                <div class="menu-title">Leave Applications</div>
            </a>
            <ul>
                <li>
                    <a href="{{ route('staffleaveapp.index') }}">
                        <i class='bx bx-radio-circle'></i>Leave List
                    </a>
                </li>
            </ul>
        </li>
        @endcan

        {{-- Attendance dropdown hata diya: receptionist sirf reports dekhta hai, links ab "Reports" menu mein --}}

        @can('access-academics')
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-category"></i></div>
                <div class="menu-title">Academics</div>
            </a>
            <ul>
                {{-- Classes --}}
                <li>
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Classes</a>
                    <ul>
                        <li><a href="{{ route('classes.index') }}"><i class='bx bx-right-arrow-alt'></i>Classes List</a></li>
                    </ul>
                </li>

                {{-- Sections --}}
                <li>
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Sections</a>
                    <ul>
                        <li><a href="{{ route('sections.index') }}"><i class='bx bx-right-arrow-alt'></i>Sections List</a></li>
                        <li><a href="{{ route('sections.create') }}"><i class='bx bx-right-arrow-alt'></i>Add Section</a></li>
                    </ul>
                </li>

                {{-- Groups --}}
                <li>
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Groups</a>
                    <ul>
                        <li><a href="{{ route('groups.index') }}"><i class='bx bx-right-arrow-alt'></i>Groups List</a></li>
                        <li><a href="{{ route('groups.create') }}"><i class='bx bx-right-arrow-alt'></i>Add Group</a></li>
                    </ul>
                </li>

                {{-- Subjects --}}
                <li>
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Subjects</a>
                    <ul>
                        <li><a href="{{ route('subjects.index') }}"><i class='bx bx-right-arrow-alt'></i>Subjects List</a></li>
                        <li><a href="{{ route('subjects.create') }}"><i class='bx bx-right-arrow-alt'></i>Add Subject</a></li>
                    </ul>
                </li>

                {{-- Class Subjects --}}
                <li>
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Class Subjects</a>
                    <ul>
                        <li><a href="{{ route('school-class-subjects.index') }}"><i class='bx bx-right-arrow-alt'></i>List</a></li>
                        <li><a href="{{ route('school-class-subjects.assign') }}"><i class='bx bx-right-arrow-alt'></i>Assign New</a></li>
                    </ul>
                </li>

                {{-- Academic Year --}}
                <li><a href="{{ route('academic_years.index') }}"><i class='bx bx-radio-circle'></i>Academic Year</a></li>

                {{-- TimeTable --}}
                @canany(['access-class-timetable', 'access-teacher-timetable'])
                <li>
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>TimeTable</a>
                    <ul>
                        @can('access-class-timetable')
                        <li><a href="{{ route('class_timetable.index') }}"><i class='bx bx-right-arrow-alt'></i>Class Timetable</a></li>
                        @endcan
                        @can('access-teacher-timetable')
                        <li><a href="{{ route('teacher_timetable.index') }}"><i class='bx bx-right-arrow-alt'></i>Teacher Timetable</a></li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Rooms --}}
                @can('access-rooms')
                <li><a href="{{ route('rooms.index') }}"><i class='bx bx-radio-circle'></i>Manage Room</a></li>
                @endcan

                {{-- Academic Calendar --}}
                <li>
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Academic Calendar</a>
                    <ul>
                        <li><a href="{{ route('academic_calendar.index') }}"><i class='bx bx-right-arrow-alt'></i>Academic Calendar</a></li>
                        <li><a href="{{ route('event_type.index') }}"><i class='bx bx-right-arrow-alt'></i>Holiday</a></li>
                    </ul>
                </li>
            </ul>
        </li>
        @endcan

        {{-- Examination --}}
        @canany(['access-exam-schedules', 'access-results', 'access-marksheets'])
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-notepad'></i></div>
                <div class="menu-title">Examination</div>
            </a>
            <ul>
                @can('access-exam-schedules')
                <li><a href="{{ route('exam_schedule.index') }}"><i class='bx bx-radio-circle'></i>Exam Schedule</a></li>
                @endcan
                @can('access-results')
                <li><a href="{{ route('result.index') }}"><i class='bx bx-radio-circle'></i>Result</a></li>
                @endcan
                @can('access-marksheets')
                <li><a href="{{ route('print_marksheet.index') }}"><i class='bx bx-radio-circle'></i>Print Marksheet</a></li>
                @endcan
            </ul>
        </li>
        @endcanany

        {{-- ID Cards (sirf print) --}}
        @canany(['access-id-card-templates', 'access-staff-id-card-templates'])
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-id-card'></i></div>
                <div class="menu-title">ID Cards</div>
            </a>
            <ul>
                @can('access-id-card-templates')
                <li><a href="{{ route('print_id_card.index') }}"><i class='bx bx-radio-circle'></i>Print Student ID Card</a></li>
                @endcan
                @can('access-staff-id-card-templates')
                <li><a href="{{ route('print_staff_id_card.index') }}"><i class='bx bx-radio-circle'></i>Print Staff ID Card</a></li>
                @endcan
            </ul>
        </li>
        @endcanany

        {{-- Reports: logged-in user ki apni reports + attendance reports (aage aur links yahan aayenge) --}}
        @canany(['access-my-attendance', 'access-my-salary-slips', 'access-attendance', 'access-staff-attendance'])
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-file'></i></div>
                <div class="menu-title">Reports</div>
            </a>
            <ul>
                @can('access-my-attendance')
                <li><a href="{{ route('my.attendance.index') }}"><i class='bx bx-radio-circle'></i>My Attendance</a></li>
                @endcan
                @can('access-my-salary-slips')
                <li><a href="{{ route('my.salary_slips.index') }}"><i class='bx bx-radio-circle'></i>My Salary Slips</a></li>
                @endcan
                @can('access-attendance')
                <li><a href="{{ route('attendance.index') }}"><i class='bx bx-radio-circle'></i>Class Attendance Report</a></li>
                @endcan
                @can('access-staff-attendance')
                <li><a href="{{ route('staff_attendance.report') }}"><i class='bx bx-radio-circle'></i>Staff Attendance Report</a></li>
                @endcan
            </ul>
        </li>
        @endcanany
    </ul>
</div>