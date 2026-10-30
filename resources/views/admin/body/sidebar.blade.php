<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div><img src="{{ asset('backend/assets/images/logo-icon.png') }}" class="logo-icon" alt="logo icon"></div>
        <div>
            <h4 class="logo-text">{{ Auth::user()->isSuperAdmin() ? 'Super Admin' : 'Admin' }}</h4>
        </div>
        <div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i></div>
    </div>

    <ul class="metismenu" id="menu">
        <li>
            <a href="{{ route('admin.dashboard') }}">
                <div class="parent-icon"><i class='bx bx-home-alt'></i></div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>

        {{-- Access Control: sirf superadmin (user + role block/unblock, audit log) --}}
        @role('superadmin')
        <li>
            <a href="{{ route('superadmin.access.index') }}">
                <div class="parent-icon"><i class='bx bx-shield-quarter'></i></div>
                <div class="menu-title">Access Control</div>
            </a>
        </li>
        @endrole

        @can('access-receptionist')
        @php $open = request()->routeIs('receptionist.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-category"></i></div>
                <div class="menu-title">Receptionist Details</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('manage-receptionist')
                <li class="{{ request()->routeIs('receptionist.create') ? 'mm-active' : '' }}"><a href="{{ route('receptionist.create') }}"><i class='bx bx-radio-circle'></i>Add Receptionist</a></li>
                @endcan
                <li class="{{ request()->routeIs('receptionist.index', 'receptionist.edit', 'receptionist.trash') ? 'mm-active' : '' }}"><a href="{{ route('receptionist.index') }}"><i class='bx bx-radio-circle'></i>Receptionist Details</a></li>
            </ul>
        </li>
        @endcan

        @if(auth()->user()->can('access-accountant'))
        @php $open = request()->routeIs('accountant.index', 'accountant.create', 'accountant.edit', 'accountant.trash'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-wallet'></i></div>
                <div class="menu-title">Accountant Management</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                <li class="{{ request()->routeIs('accountant.index', 'accountant.edit', 'accountant.trash') ? 'mm-active' : '' }}"><a href="{{ route('accountant.index') }}"><i class='bx bx-radio-circle'></i>All Accountants</a></li>
                @can('manage-accountant')
                <li class="{{ request()->routeIs('accountant.create') ? 'mm-active' : '' }}"><a href="{{ route('accountant.create') }}"><i class='bx bx-radio-circle'></i>Add Accountant</a></li>
                @endcan
            </ul>
        </li>
        @endif

        {{-- Librarian Management (admin CRUD) --}}
        @can('access-librarian')
        @php $open = request()->routeIs('librarians.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-book-reader'></i></div>
                <div class="menu-title">Librarian Management</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                <li class="{{ request()->routeIs('librarians.index', 'librarians.edit', 'librarians.trash') ? 'mm-active' : '' }}"><a href="{{ route('librarians.index') }}"><i class='bx bx-radio-circle'></i>All Librarians</a></li>
                @can('manage-librarian')
                <li class="{{ request()->routeIs('librarians.create') ? 'mm-active' : '' }}"><a href="{{ route('librarians.create') }}"><i class='bx bx-radio-circle'></i>Add Librarian</a></li>
                @endcan
            </ul>
        </li>
        @endcan

        {{-- Student Information Section --}}
        @if(auth()->user()->can('access-students') || auth()->user()->can('access-student-categories') || auth()->user()->can('access-student-houses') || auth()->user()->can('access-student-import'))
        @php $open = request()->routeIs('students.*', 'student_import.*', 'student_promotion.*', 'student-category.*', 'student-house.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-user-pin"></i></div>
                <div class="menu-title">Student Information</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-students')
                <li class="{{ request()->routeIs('students.index', 'students.edit', 'students.trash') ? 'mm-active' : '' }}"><a href="{{ route('students.index') }}"><i class="bx bx-radio-circle"></i>All Students</a></li>
                <li class="{{ request()->routeIs('students.create') ? 'mm-active' : '' }}"><a href="{{ route('students.create') }}"><i class="bx bx-radio-circle"></i>Student Admission</a></li>
                @endcan

                @can('access-student-import')
                <li class="{{ request()->routeIs('student_import.*') ? 'mm-active' : '' }}"><a href="{{ route('student_import.index') }}"><i class="bx bx-radio-circle"></i>Bulk Import</a></li>
                @endcan

                @can('access-student-promotion')
                <li class="{{ request()->routeIs('student_promotion.*') ? 'mm-active' : '' }}"><a href="{{ route('student_promotion.index') }}"><i class="bx bx-radio-circle"></i>Student Promotion</a></li>
                @endcan

                @can('access-student-categories')
                <li class="{{ request()->routeIs('student-category.*') ? 'mm-active' : '' }}"><a href="{{ route('student-category.index') }}"><i class="bx bx-radio-circle"></i>Student Categories</a></li>
                @endcan

                @can('access-student-houses')
                <li class="{{ request()->routeIs('student-house.*') ? 'mm-active' : '' }}"><a href="{{ route('student-house.index') }}"><i class="bx bx-radio-circle"></i>Student Houses</a></li>
                @endcan
            </ul>
        </li>
        @endif

        {{-- Teacher Management Section --}}
        @if(auth()->user()->can('access-teacher') || auth()->user()->can('access-teacher-assignment'))
        @php $open = request()->routeIs('teacher.index', 'teacher.create', 'teacher.edit', 'teacher.trash', 'teacher.assign.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-chalkboard"></i></div>
                <div class="menu-title">Teacher Management</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-teacher')
                <li class="{{ request()->routeIs('teacher.index', 'teacher.edit', 'teacher.trash') ? 'mm-active' : '' }}"><a href="{{ route('teacher.index') }}"><i class='bx bx-radio-circle'></i>All Teachers</a></li>
                @can('manage-teacher')
                <li class="{{ request()->routeIs('teacher.create') ? 'mm-active' : '' }}"><a href="{{ route('teacher.create') }}"><i class='bx bx-radio-circle'></i>Add Teacher</a></li>
                @endcan
                @endcan

                @can('access-teacher-assignment')
                <li class="{{ request()->routeIs('teacher.assign.index', 'teacher.assign.edit') ? 'mm-active' : '' }}"><a href="{{ route('teacher.assign.index') }}"><i class='bx bx-radio-circle'></i>Assigned Classes List</a></li>
                <li class="{{ request()->routeIs('teacher.assign.create') ? 'mm-active' : '' }}"><a href="{{ route('teacher.assign.create') }}"><i class='bx bx-radio-circle'></i>Assign Class</a></li>
                @endcan
            </ul>
        </li>
        @endif

        {{-- Leave Management Section --}}
        @if(auth()->user()->can('access-leave-application') || auth()->user()->can('access-leave-setting'))
        @php $open = request()->routeIs('staffleavesett.*', 'staffleaveapp.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-calendar-x'></i></div>
                <div class="menu-title">Leave Management</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-leave-setting')
                <li class="{{ request()->routeIs('staffleavesett.*') ? 'mm-active' : '' }}"><a href="{{ route('staffleavesett.index') }}"><i class='bx bx-radio-circle'></i>Leave Settings</a></li>
                @endcan
                @can('access-leave-application')
                <li class="{{ request()->routeIs('staffleaveapp.*') ? 'mm-active' : '' }}"><a href="{{ route('staffleaveapp.index') }}"><i class='bx bx-radio-circle'></i>Leave Applications</a></li>
                @endcan
            </ul>
        </li>
        @endif

        {{-- Academic Management Section --}}
        @can('access-academics')
        @php $open = request()->routeIs('classes.*', 'sections.*', 'groups.*', 'subjects.*', 'school-class-subjects.*', 'academic_years.*', 'class_timetable.*', 'teacher_timetable.*', 'rooms.*', 'academic_calendar.*', 'event_type.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-book-reader"></i></div>
                <div class="menu-title">Academics</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                <li class="{{ request()->routeIs('classes.*') ? 'mm-active' : '' }}"><a href="{{ route('classes.index') }}"><i class='bx bx-radio-circle'></i>Manage Class</a></li>
                <li class="{{ request()->routeIs('sections.*') ? 'mm-active' : '' }}"><a href="{{ route('sections.create') }}"><i class='bx bx-radio-circle'></i>Manage Section</a></li>
                <li class="{{ request()->routeIs('groups.*') ? 'mm-active' : '' }}"><a href="{{ route('groups.create') }}"><i class='bx bx-radio-circle'></i>Manage Group</a></li>
                <li class="{{ request()->routeIs('subjects.*') ? 'mm-active' : '' }}"><a href="{{ route('subjects.create') }}"><i class='bx bx-radio-circle'></i>Manage Subject</a></li>
                <li class="{{ request()->routeIs('school-class-subjects.*') ? 'mm-active' : '' }}"><a href="{{ route('school-class-subjects.assign') }}"><i class='bx bx-radio-circle'></i>Manage Class-Subject</a></li>
                <li class="{{ request()->routeIs('academic_years.*') ? 'mm-active' : '' }}"><a href="{{ route('academic_years.index') }}"><i class='bx bx-radio-circle'></i>Manage Academic Year</a></li>

                @if(auth()->user()->can('access-class-timetable') || auth()->user()->can('access-teacher-timetable'))
                <li class="{{ request()->routeIs('class_timetable.*', 'teacher_timetable.*') ? 'mm-active' : '' }}">
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>TimeTable</a>
                    <ul class="{{ request()->routeIs('class_timetable.*', 'teacher_timetable.*') ? 'mm-show' : '' }}">
                        @can('access-class-timetable')
                        <li class="{{ request()->routeIs('class_timetable.*') ? 'mm-active' : '' }}"><a href="{{ route('class_timetable.index') }}"><i class='bx bx-right-arrow-alt'></i>Class Timetable</a></li>
                        @endcan
                        @can('access-teacher-timetable')
                        <li class="{{ request()->routeIs('teacher_timetable.*') ? 'mm-active' : '' }}"><a href="{{ route('teacher_timetable.index') }}"><i class='bx bx-right-arrow-alt'></i>Teacher Timetable</a></li>
                        @endcan
                    </ul>
                </li>
                @endif

                @can('access-rooms')
                <li class="{{ request()->routeIs('rooms.*') ? 'mm-active' : '' }}"><a href="{{ route('rooms.index') }}"><i class='bx bx-radio-circle'></i>Manage Room</a></li>
                @endcan

                <li class="{{ request()->routeIs('academic_calendar.*', 'event_type.*') ? 'mm-active' : '' }}">
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Academic Calendar</a>
                    <ul class="{{ request()->routeIs('academic_calendar.*', 'event_type.*') ? 'mm-show' : '' }}">
                        <li class="{{ request()->routeIs('academic_calendar.*') ? 'mm-active' : '' }}"><a href="{{ route('academic_calendar.index') }}"><i class='bx bx-right-arrow-alt'></i>Academic Calendar</a></li>
                        <li class="{{ request()->routeIs('event_type.*') ? 'mm-active' : '' }}"><a href="{{ route('event_type.index') }}"><i class='bx bx-right-arrow-alt'></i>Holiday</a></li>
                    </ul>
                </li>
            </ul>
        </li>
        @endcan

        {{-- Home-Work Management Section --}}
        @if(auth()->user()->can('access-home-works'))
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

        {{-- Lesson Plan Section --}}
        @if(auth()->user()->can('access-lessons') || auth()->user()->can('access-topics') || auth()->user()->can('access-lesson-plans') || auth()->user()->can('access-copy-old-lessons') || auth()->user()->can('access-syllabus-status'))
        @php $open = request()->routeIs('copy_old_lesson.*', 'lesson_plan.*', 'syllabus_status.*', 'lesson.*', 'topic.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-book-content'></i></div>
                <div class="menu-title">Lesson Plan</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @if(auth()->user()->can('access-copy-old-lessons'))
                <li class="{{ request()->routeIs('copy_old_lesson.*') ? 'mm-active' : '' }}"><a href="{{ route('copy_old_lesson.index') }}"><i class='bx bx-radio-circle'></i>Copy Old Lessons</a></li>
                @endif
                @if(auth()->user()->can('access-lesson-plans'))
                <li class="{{ request()->routeIs('lesson_plan.*') ? 'mm-active' : '' }}"><a href="{{ route('lesson_plan.index') }}"><i class='bx bx-radio-circle'></i>Manage Lesson Plan</a></li>
                @endif
                @if(auth()->user()->can('access-syllabus-status'))
                <li class="{{ request()->routeIs('syllabus_status.*') ? 'mm-active' : '' }}"><a href="{{ route('syllabus_status.index') }}"><i class='bx bx-radio-circle'></i>Manage Syllabus Status</a></li>
                @endif
                @if(auth()->user()->can('access-lessons'))
                <li class="{{ request()->routeIs('lesson.*') ? 'mm-active' : '' }}"><a href="{{ route('lesson.index') }}"><i class='bx bx-radio-circle'></i>Manage Lesson</a></li>
                @endif
                @if(auth()->user()->can('access-topics'))
                <li class="{{ request()->routeIs('topic.*') ? 'mm-active' : '' }}"><a href="{{ route('topic.index') }}"><i class='bx bx-radio-circle'></i>Manage Topic</a></li>
                @endif
            </ul>
        </li>
        @endif

        {{-- Examination Menu --}}
        @if(auth()->user()->can('access-exam-types') || auth()->user()->can('access-exams') || auth()->user()->can('access-exam-schedules') || auth()->user()->can('access-marksheets') || auth()->user()->can('access-results') || auth()->user()->can('access-marks-grades') || auth()->user()->can('access-skill_categories') || auth()->user()->can('access-skill_assessment_areas') || auth()->user()->can('access-report-card-template') || auth()->user()->can('access-print-report-card'))
        @php $open = request()->routeIs('exam_type.*', 'exam.*', 'exam_schedule.*', 'marksheet.*', 'result.*', 'marks_grade.*', 'marksheet_template.*', 'print_marksheet.*', 'skill_categories.*', 'skill_assessment_areas.*', 'skill_assessment_entry.*', 'report_card_template.*', 'print_report_card.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-notepad'></i></div>
                <div class="menu-title">Examination</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-exam-types')
                <li class="{{ request()->routeIs('exam_type.*') ? 'mm-active' : '' }}"><a href="{{ route('exam_type.index') }}"><i class='bx bx-radio-circle'></i>Exam Type</a></li>
                @endcan
                @can('access-exams')
                <li class="{{ request()->routeIs('exam.*', 'marks_grade.*') ? 'mm-active' : '' }}"><a href="{{ route('exam.index') }}"><i class='bx bx-radio-circle'></i>Exam</a></li>
                @endcan
                @can('access-exam-schedules')
                <li class="{{ request()->routeIs('exam_schedule.*') ? 'mm-active' : '' }}"><a href="{{ route('exam_schedule.index') }}"><i class='bx bx-radio-circle'></i>Exam Schedule</a></li>
                @endcan
                @can('access-marksheets')
                <li class="{{ request()->routeIs('marksheet.*') ? 'mm-active' : '' }}"><a href="{{ route('marksheet.index') }}"><i class='bx bx-radio-circle'></i>MarkSheet</a></li>
                @endcan
                @can('access-results')
                <li class="{{ request()->routeIs('result.*') ? 'mm-active' : '' }}"><a href="{{ route('result.index') }}"><i class='bx bx-radio-circle'></i>Result</a></li>
                @endcan
                <li class="menu-item {{ request()->routeIs('marksheet_template.*') ? 'active mm-active' : '' }}">
                    <a href="{{ route('marksheet_template.index') }}" class="menu-link">
                        <i class='bx bx-radio-circle'></i>
                        <div>Design Marksheet</div>
                    </a>
                </li>
                @can('access-marksheets')
                <li class="menu-item {{ request()->routeIs('print_marksheet.*') ? 'active mm-active' : '' }}">
                    <a href="{{ route('print_marksheet.index') }}" class="menu-link">
                        <i class='bx bx-radio-circle'></i>
                        <div>Print Marksheet</div>
                    </a>
                </li>
                @endcan
                @can('access-skill-categories')
                <li class="menu-item {{ request()->routeIs('skill_categories.*') ? 'active mm-active' : '' }}">
                    <a href="{{ route('skill_categories.index') }}" class="menu-link">
                        <i class='bx bx-radio-circle'></i>
                        <div>Skill Categories</div>
                    </a>
                </li>
                @endcan
                @can('access-skill-assessment-areas')
                <li class="menu-item {{ request()->routeIs('skill_assessment_areas.*') ? 'active mm-active' : '' }}">
                    <a href="{{ route('skill_assessment_areas.index') }}" class="menu-link">
                        <i class='bx bx-radio-circle'></i>
                        <div>Skill Assessment Areas</div>
                    </a>
                </li>
                @endcan
                @can('access-skill-assessment-entry')
                <li class="nav-item {{ request()->routeIs('skill_assessment_entry.*') ? 'mm-active' : '' }}">
                    <a href="{{ route('skill_assessment_entry.index') }}" class="nav-link {{ request()->routeIs('skill_assessment_entry.*') ? 'active' : '' }}">
                        <i class='bx bx-radio-circle'></i>
                        <p>Skill Assessment Entry</p>
                    </a>
                </li>
                @endcan
                @can('access-report-card-template')
                <li class="menu-item {{ request()->routeIs('report_card_template.*') ? 'active mm-active' : '' }}">
                    <a href="{{ route('report_card_template.index') }}" class="menu-link">
                        <i class='bx bx-radio-circle'></i>
                        <div>Design Report Card</div>
                    </a>
                </li>
                @endcan
                @can('access-print-report-card')
                <li class="menu-item {{ request()->routeIs('print_report_card.*') ? 'active mm-active' : '' }}">
                    <a href="{{ route('print_report_card.index') }}" class="menu-link">
                        <i class='bx bx-radio-circle'></i>
                        <div>Print Report Card</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endif

        {{-- Attendance Management Section --}}
        @if(auth()->user()->can('access-attendance') || auth()->user()->can('access-staff-attendance'))
        @php $open = request()->routeIs('attendance.*', 'staff_attendance.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-check-shield'></i></div>
                <div class="menu-title">Attendance</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-attendance')
                @php $subOpen = request()->routeIs('attendance.*'); @endphp
                <li class="{{ $subOpen ? 'mm-active' : '' }}">
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Student Attendance</a>
                    <ul class="{{ $subOpen ? 'mm-show' : '' }}">
                        @can('mark-attendance')
                        <li class="{{ request()->routeIs('attendance.create', 'attendance.mark') ? 'mm-active' : '' }}"><a href="{{ route('attendance.create') }}"><i class='bx bx-right-arrow-alt'></i>Mark Attendance</a></li>
                        @endcan
                        @can('access-scan-attendance')
                        <li class="{{ request()->routeIs('attendance.scan*') ? 'mm-active' : '' }}"><a href="{{ route('attendance.scan') }}"><i class='bx bx-right-arrow-alt'></i>Scan Attendance</a></li>
                        @endcan
                        <li class="{{ request()->routeIs('attendance.index', 'attendance.details', 'attendance.report', 'attendance.edit', 'attendance.class_report', 'attendance.student_report', 'attendance.daily') ? 'mm-active' : '' }}"><a href="{{ route('attendance.index') }}"><i class='bx bx-right-arrow-alt'></i>Attendance Report</a></li>
                    </ul>
                </li>
                @endcan

                @can('access-staff-attendance')
                @php $subOpen = request()->routeIs('staff_attendance.*'); @endphp
                <li class="{{ $subOpen ? 'mm-active' : '' }}">
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Staff Attendance</a>
                    <ul class="{{ $subOpen ? 'mm-show' : '' }}">
                        @can('mark-staff-attendance')
                        <li class="{{ request()->routeIs('staff_attendance.create', 'staff_attendance.edit') ? 'mm-active' : '' }}"><a href="{{ route('staff_attendance.create') }}"><i class='bx bx-right-arrow-alt'></i>Mark Attendance</a></li>
                        @endcan
                        <li class="{{ request()->routeIs('staff_attendance.report', 'staff_attendance.show', 'staff_attendance.index') ? 'mm-active' : '' }}"><a href="{{ route('staff_attendance.report') }}"><i class='bx bx-right-arrow-alt'></i>Attendance Report</a></li>
                    </ul>
                </li>
                @endcan
            </ul>
        </li>
        @endif

        @if(auth()->user()->can('access-staff-salaries') || auth()->user()->can('access-salary-slips'))
        @php $open = request()->routeIs('staff_salaries.*', 'salary_slips.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-wallet-alt'></i></div>
                <div class="menu-title">Payroll</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-staff-salaries')
                @if (Route::has('staff_salaries.index'))
                <li class="{{ request()->routeIs('staff_salaries.*') ? 'mm-active' : '' }}"><a href="{{ route('staff_salaries.index') }}"><i class='bx bx-radio-circle'></i>Salary Structure</a></li>
                @endif
                @endcan
                @can('access-salary-slips')
                <li class="{{ request()->routeIs('salary_slips.*') ? 'mm-active' : '' }}"><a href="{{ route('salary_slips.index') }}"><i class='bx bx-radio-circle'></i>Salary Slips</a></li>
                @endcan
            </ul>
        </li>
        @endif

        @can('access-finance-report')
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
        @endcan

        @if(auth()->user()->can('access-front-office'))
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

        @if(auth()->user()->can('access-fee-types') || auth()->user()->can('access-fee-structures') || auth()->user()->can('access-discount-policies') || auth()->user()->can('access-fine-policies') || auth()->user()->can('access-billing-settings') || auth()->user()->can('access-fee-assignments') || auth()->user()->can('access-expense-categories') || auth()->user()->can('access-expenses'))
        @php $open = request()->routeIs('fee_types.*', 'fee_structure.*', 'fee_assignments.*', 'fees.*', 'bank_accounts.*', 'discount_policies.*', 'fine_policies.*', 'billing_settings.*', 'expense_categories.*', 'expenses.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-money"></i></div>
                <div class="menu-title">Fees Collection</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-fee-types')
                <li class="{{ request()->routeIs('fee_types.*') ? 'mm-active' : '' }}"><a href="{{ route('fee_types.index') }}"><i class='bx bx-radio-circle'></i>Fee Type</a></li>
                @endcan

                @can('access-fee-structures')
                <li class="{{ request()->routeIs('fee_structure.*') ? 'mm-active' : '' }}"><a href="{{ route('fee_structure.index') }}"><i class='bx bx-radio-circle'></i>Fee Structure</a></li>
                <li class="{{ request()->routeIs('fees.collect.index', 'fees.collect.show', 'fees.collect.receipt') ? 'mm-active' : '' }}"><a href="{{ route('fees.collect.index') }}"><i class='bx bx-radio-circle'></i>Collect Fees</a></li>
                @can('access-fee-assignments')
                <li class="{{ request()->routeIs('fee_assignments.*') ? 'mm-active' : '' }}"><a href="{{ route('fee_assignments.index') }}"><i class='bx bx-radio-circle'></i>Assign Fee</a></li>
                @endcan
                <li class="{{ request()->routeIs('fees.collect.report') ? 'mm-active' : '' }}"><a href="{{ route('fees.collect.report') }}"><i class='bx bx-radio-circle'></i>Fee Reports</a></li>
                <li class="{{ request()->routeIs('bank_accounts.*') ? 'mm-active' : '' }}"><a href="{{ route('bank_accounts.index') }}"><i class='bx bx-radio-circle'></i>Bank Accounts</a></li>
                @endcan

                @if(auth()->user()->can('access-discount-policies') || auth()->user()->can('access-fine-policies') || auth()->user()->can('access-billing-settings'))
                @php $subOpen = request()->routeIs('discount_policies.*', 'fine_policies.*', 'billing_settings.*'); @endphp
                <li class="{{ $subOpen ? 'mm-active' : '' }}">
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Fee Settings</a>
                    <ul class="{{ $subOpen ? 'mm-show' : '' }}">
                        @can('access-discount-policies')
                        <li class="{{ request()->routeIs('discount_policies.*') ? 'mm-active' : '' }}"><a href="{{ route('discount_policies.index') }}"><i class='bx bx-radio-circle'></i>Discount Policies</a></li>
                        @endcan
                        @can('access-billing-settings')
                        <li class="{{ request()->routeIs('billing_settings.*') ? 'mm-active' : '' }}"><a href="{{ route('billing_settings.index') }}"><i class='bx bx-radio-circle'></i>Billing Settings</a></li>
                        @endcan
                        {{-- Fine Policies CRUD is not built yet (FinePolicyController is empty) - link hidden
                        @can('access-fine-policies')
                        <li class="{{ request()->routeIs('fine_policies.*') ? 'mm-active' : '' }}"><a href="{{ route('fine_policies.index') }}"><i class='bx bx-radio-circle'></i>Fine Policies</a></li>
                        @endcan
                        --}}
                    </ul>
                </li>
                @endif

                @if(auth()->user()->can('access-expense-categories') || auth()->user()->can('access-expenses'))
                @php $subOpen = request()->routeIs('expense_categories.*', 'expenses.*'); @endphp
                <li class="{{ $subOpen ? 'mm-active' : '' }}">
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Expenses</a>
                    <ul class="{{ $subOpen ? 'mm-show' : '' }}">
                        @can('access-expense-categories')
                        <li class="{{ request()->routeIs('expense_categories.*') ? 'mm-active' : '' }}"><a href="{{ route('expense_categories.index') }}"><i class='bx bx-radio-circle'></i>Expense Categories</a></li>
                        @endcan
                        @can('access-expenses')
                        <li class="{{ request()->routeIs('expenses.*') ? 'mm-active' : '' }}"><a href="{{ route('expenses.index') }}"><i class='bx bx-radio-circle'></i>Expense</a></li>
                        @endcan
                    </ul>
                </li>
                @endif
            </ul>
        </li>
        @endif

        {{-- Library Menu --}}
        @canany(['access-book-categories', 'access-books', 'access-book-issues', 'access-library-settings', 'access-library-members'])
        @php $open = request()->routeIs('book_category.*', 'books.*', 'book_issue.*', 'library_settings.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-book-alt"></i></div>
                <div class="menu-title">Library</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-book-categories')
                <li class="{{ request()->routeIs('book_category.*') ? 'mm-active' : '' }}"><a href="{{ route('book_category.index') }}"><i class='bx bx-radio-circle'></i>Book Category</a></li>
                @endcan
                @can('access-books')
                <li class="{{ request()->routeIs('books.*') ? 'mm-active' : '' }}"><a href="{{ route('books.index') }}"><i class='bx bx-radio-circle'></i>Books</a></li>
                @endcan
                @can('access-book-issues')
                <li class="{{ request()->routeIs('book_issue.*') ? 'mm-active' : '' }}"><a href="{{ route('book_issue.index') }}"><i class='bx bx-radio-circle'></i>Book Issues</a></li>
                @endcan

                @can('access-library-settings')
                @php $subOpen = request()->routeIs('library_settings.*'); @endphp
                <li class="{{ $subOpen ? 'mm-active' : '' }}">
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Library Settings</a>
                    <ul class="{{ $subOpen ? 'mm-show' : '' }}">
                        <li class="{{ request()->routeIs('library_settings.index') ? 'mm-active' : '' }}"><a href="{{ route('library_settings.index') }}"><i class='bx bx-radio-circle'></i>General Settings</a></li>
                        @can('access-library-members')
                        <li class="{{ request()->routeIs('library_settings.members.student*') ? 'mm-active' : '' }}"><a href="{{ route('library_settings.members.student') }}"><i class='bx bx-radio-circle'></i>Library Students</a></li>
                        <li class="{{ request()->routeIs('library_settings.members.staff*') ? 'mm-active' : '' }}"><a href="{{ route('library_settings.members.staff') }}"><i class='bx bx-radio-circle'></i>Library Staff</a></li>
                        @endcan
                    </ul>
                </li>
                @endcan
            </ul>
        </li>
        @endcanany

        {{-- Setup / Settings Parent Menu --}}
@php $open = request()->routeIs('site_setting.*', 'school_timing.*'); @endphp
<li class="{{ $open ? 'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-layer'></i></div>
        <div class="menu-title">Site Setup</div>
    </a>
    <ul class="{{ $open ? 'mm-show' : '' }}">
        @can('access-site-settings')
        <li class="{{ request()->routeIs('site_setting.*') ? 'mm-active' : '' }}"><a href="{{ route('site_setting.edit') }}"><i class='bx bx-radio-circle'></i>Site Settings</a></li>
        @endcan
        @can('access-school-timing')
        <li class="{{ request()->routeIs('school_timing.*') ? 'mm-active' : '' }}"><a href="{{ route('school_timing.index') }}"><i class='bx bx-radio-circle'></i>School Timing</a></li>
        @endcan
    </ul>
</li>

        {{-- ID Cards Menu --}}
        @canany(['access-id-card-templates', 'access-staff-id-card-templates'])
        @php $open = request()->routeIs('id_card_template.*', 'print_id_card.*', 'staff_id_card_template.*', 'print_staff_id_card.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-id-card'></i></div>
                <div class="menu-title">ID Cards</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-id-card-templates')
                <li class="{{ request()->routeIs('id_card_template.*') ? 'mm-active' : '' }}"><a href="{{ route('id_card_template.index') }}"><i class='bx bx-radio-circle'></i>Design Student ID Card</a></li>
                <li class="{{ request()->routeIs('print_id_card.*') ? 'mm-active' : '' }}"><a href="{{ route('print_id_card.index') }}"><i class='bx bx-radio-circle'></i>Print Student ID Card</a></li>
                @endcan

                @can('access-staff-id-card-templates')
                <li class="{{ request()->routeIs('staff_id_card_template.*') ? 'mm-active' : '' }}"><a href="{{ route('staff_id_card_template.index') }}"><i class='bx bx-radio-circle'></i>Design Staff ID Card</a></li>
                <li class="{{ request()->routeIs('print_staff_id_card.*') ? 'mm-active' : '' }}"><a href="{{ route('print_staff_id_card.index') }}"><i class='bx bx-radio-circle'></i>Print Staff ID Card</a></li>
                @endcan
            </ul>
        </li>
        @endcanany

        {{-- Logout (hamesha last) --}}
        <li>
            <a href="{{ route('admin.logout') }}">
                <div class="parent-icon"><i class="bx bx-log-out-circle"></i></div>
                <div class="menu-title">Logout</div>
            </a>
        </li>
    </ul>
</div>