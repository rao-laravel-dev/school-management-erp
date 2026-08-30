<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div><img src="{{ asset('backend/assets/images/logo-icon.png') }}" class="logo-icon" alt="logo icon"></div>
        <div>
            <h4 class="logo-text">Admin</h4>
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

        @can('access-receptionist')
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-category"></i></div>
                <div class="menu-title">Receptionist Details</div>
            </a>
            <ul>
                <li><a href="{{ route('receptionist.create') }}"><i class='bx bx-radio-circle'></i>Add Receptionist</a></li>
                <li><a href="{{ route('receptionist.index') }}"><i class='bx bx-radio-circle'></i>Receptionist Details</a></li>
            </ul>
        </li>
        @endcan

        @if(auth()->user()->can('access-accountant'))
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-wallet'></i></div>
                <div class="menu-title">Accountant Management</div>
            </a>
            <ul>
                <li><a href="{{ route('accountant.index') }}"><i class='bx bx-radio-circle'></i>All Accountants</a></li>
                <li><a href="{{ route('accountant.create') }}"><i class='bx bx-radio-circle'></i>Add Accountant</a></li>
            </ul>
        </li>
        @endif

        @if(auth()->user()->can('access-students') || auth()->user()->can('access-student-categories') || auth()->user()->can('access-student-houses'))
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-user-pin"></i></div>
                <div class="menu-title">Student Information</div>
            </a>
            <ul>
                @can('access-students')
                <li><a href="{{ route('students.index') }}"><i class="bx bx-radio-circle"></i>All Students</a></li>
                <li><a href="{{ route('students.create') }}"><i class="bx bx-radio-circle"></i>Student Admission</a></li>
                @endcan

                @can('access-student-categories')
                <li><a href="{{ route('student-category.index') }}"><i class="bx bx-radio-circle"></i>Student Categories</a></li>
                @endcan

                @can('access-student-houses')
                <li><a href="{{ route('student-house.index') }}"><i class="bx bx-radio-circle"></i>Student Houses</a></li>
                @endcan
            </ul>
        </li>
        @endif

        @if(auth()->user()->can('access-teacher') || auth()->user()->can('access-teacher-assignment'))
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-chalkboard"></i></div>
                <div class="menu-title">Teacher Management</div>
            </a>
            <ul>
                @can('access-teacher')
                <li><a href="{{ route('teacher.index') }}"><i class='bx bx-radio-circle'></i>All Teachers</a></li>
                <li><a href="{{ route('teacher.create') }}"><i class='bx bx-radio-circle'></i>Add Teacher</a></li>
                @endcan

                @can('access-teacher-assignment')
                <li><a href="{{ route('teacher.assign.index') }}"><i class='bx bx-radio-circle'></i>Assigned Classes List</a></li>
                <li><a href="{{ route('teacher.assign.create') }}"><i class='bx bx-radio-circle'></i>Assign Class</a></li>
                @endcan
            </ul>
        </li>
        @endif

        {{-- Leave Management Section --}}
        @if(auth()->user()->can('access-leave-application') || auth()->user()->can('access-leave-setting'))
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-calendar-x'></i></div>
                <div class="menu-title">Leave Management</div>
            </a>
            <ul>
                @can('access-leave-setting')
                <li><a href="{{ route('staffleavesett.index') }}"><i class='bx bx-radio-circle'></i>Leave Settings</a></li>
                @endcan
                @can('access-leave-application')
                <li><a href="{{ route('staffleaveapp.index') }}"><i class='bx bx-radio-circle'></i>Leave Applications</a></li>
                @endcan
            </ul>
        </li>
        @endif

        @can('access-academics')
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-book-reader"></i></div>
                <div class="menu-title">Academics</div>
            </a>
            <ul>
                <li><a href="{{ route('classes.create') }}"><i class='bx bx-radio-circle'></i>Manage Class</a></li>
                <li><a href="{{ route('sections.create') }}"><i class='bx bx-radio-circle'></i>Manage Section</a></li>
                <li><a href="{{ route('groups.create') }}"><i class='bx bx-radio-circle'></i>Manage Group</a></li>
                <li><a href="{{ route('subjects.create') }}"><i class='bx bx-radio-circle'></i>Manage Subject</a></li>
                <li><a href="{{ route('school-class-subjects.assign') }}"><i class='bx bx-radio-circle'></i>Manage Class-Subject</a></li>
                <li><a href="{{ route('academic_years.index') }}"><i class='bx bx-radio-circle'></i>Manage Academic Year</a></li>

                @if(auth()->user()->can('access-class-timetable') || auth()->user()->can('access-teacher-timetable'))
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
                @endif

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

        @if(auth()->user()->can('access-home-works'))
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-task'></i></div>
                <div class="menu-title">Home Work</div>
            </a>
            <ul>
                <li><a href="{{ route('home_work.index') }}"><i class='bx bx-radio-circle'></i>Manage Home Work</a></li>
            </ul>
        </li>
        @endif

        @if(auth()->user()->can('access-lessons') || auth()->user()->can('access-topics') || auth()->user()->can('access-lesson-plans') || auth()->user()->can('access-copy-old-lessons') || auth()->user()->can('access-syllabus-status'))
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-book-content'></i></div>
                <div class="menu-title">Lesson Plan</div>
            </a>
            <ul>
                @if(auth()->user()->can('access-copy-old-lessons'))
                <li><a href="{{ route('copy_old_lesson.index') }}"><i class='bx bx-radio-circle'></i>Copy Old Lessons</a></li>
                @endif
                @if(auth()->user()->can('access-lesson-plans'))
                <li><a href="{{ route('lesson_plan.index') }}"><i class='bx bx-radio-circle'></i>Manage Lesson Plan</a></li>
                @endif
                @if(auth()->user()->can('access-syllabus-status'))
                <li><a href="{{ route('syllabus_status.index') }}"><i class='bx bx-radio-circle'></i>Manage Syllabus Status</a></li>
                @endif
                @if(auth()->user()->can('access-lessons'))
                <li><a href="{{ route('lesson.index') }}"><i class='bx bx-radio-circle'></i>Manage Lesson</a></li>
                @endif
                @if(auth()->user()->can('access-topics'))
                <li><a href="{{ route('topic.index') }}"><i class='bx bx-radio-circle'></i>Manage Topic</a></li>
                @endif
            </ul>
        </li>
        @endif

        {{-- Examination Menu --}}
        @if(auth()->user()->can('access-exam-types') || auth()->user()->can('access-exams') || auth()->user()->can('access-exam-schedules') || auth()->user()->can('access-marksheets') || auth()->user()->can('access-results') || auth()->user()->can('access-marks-grades') || auth()->user()->can('access-skill_categories') || auth()->user()->can('access-skill_assessment_areas') || auth()->user()->can('access-report-card-template') || auth()->user()->can('access-print-report-card'))
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-notepad'></i></div>
                <div class="menu-title">Examination</div>
            </a>
            <ul>
                @can('access-exam-types')
                <li><a href="{{ route('exam_type.index') }}"><i class='bx bx-radio-circle'></i>Exam Type</a></li>
                @endcan
                @can('access-exams')
                <li><a href="{{ route('exam.index') }}"><i class='bx bx-radio-circle'></i>Exam</a></li>
                @endcan
                @can('access-exam-schedules')
                <li><a href="{{ route('exam_schedule.index') }}"><i class='bx bx-radio-circle'></i>Exam Schedule</a></li>
                @endcan
                @can('access-marksheets')
                <li><a href="{{ route('marksheet.index') }}"><i class='bx bx-radio-circle'></i>MarkSheet</a></li>
                @endcan
                @can('access-results')
                <li><a href="{{ route('result.index') }}"><i class='bx bx-radio-circle'></i>Result</a></li>
                @endcan
                <li class="menu-item {{ request()->routeIs('marksheet_template.*') ? 'active' : '' }}">
                    <a href="{{ route('marksheet_template.index') }}" class="menu-link">
                        <i class='bx bx-radio-circle'></i>
                        <div>Design Marksheet</div>
                    </a>
                </li>
                @can('access-marksheets')
                <li class="menu-item {{ request()->routeIs('print_marksheet.*') ? 'active' : '' }}">
                    <a href="{{ route('print_marksheet.index') }}" class="menu-link">
                        <i class='bx bx-radio-circle'></i>
                        <div>Print Marksheet</div>
                    </a>
                </li>
                @endcan
                @can('access-skill-categories')
                <li class="menu-item {{ request()->routeIs('skill_categories.*') ? 'active' : '' }}">
                    <a href="{{ route('skill_categories.index') }}" class="menu-link">
                        <i class='bx bx-radio-circle'></i>
                        <div>Skill Categories</div>
                    </a>
                </li>
                @endcan
                @can('access-skill-assessment-areas')
                <li class="menu-item {{ request()->routeIs('skill_assessment_areas.*') ? 'active' : '' }}">
                    <a href="{{ route('skill_assessment_areas.index') }}" class="menu-link">
                        <i class='bx bx-radio-circle'></i>
                        <div>Skill Assessment Areas</div>
                    </a>
                </li>
                @endcan
                @can('access-skill-assessment-entry')
                <li class="nav-item">
                    <a href="{{ route('skill_assessment_entry.index') }}" class="nav-link {{ request()->routeIs('skill_assessment_entry.*') ? 'active' : '' }}">
                        <i class='bx bx-radio-circle'></i>
                        <p>Skill Assessment Entry</p>
                    </a>
                </li>
                @endcan
                @can('access-report-card-template')
                <li class="menu-item {{ request()->routeIs('report_card_template.*') ? 'active' : '' }}">
                    <a href="{{ route('report_card_template.index') }}" class="menu-link">
                        <i class='bx bx-radio-circle'></i>
                        <div>Design Report Card</div>
                    </a>
                </li>
                @endcan
                @can('access-print-report-card')
                <li class="menu-item {{ request()->routeIs('print_report_card.*') ? 'active' : '' }}">
                    <a href="{{ route('print_report_card.index') }}" class="menu-link">
                        <i class='bx bx-radio-circle'></i>
                        <div>Print Report Card</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endif

        @if(auth()->user()->can('access-attendance') || auth()->user()->can('access-staff-attendance'))
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-check-shield'></i></div>
                <div class="menu-title">Attendance</div>
            </a>
            <ul>
                @can('access-attendance')
                <li>
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Student Attendance</a>
                    <ul>
                        <li><a href="{{ route('attendance.create') }}"><i class='bx bx-right-arrow-alt'></i>Mark Attendance</a></li>
                        <li><a href="{{ route('attendance.scan') }}"><i class='bx bx-right-arrow-alt'></i>Scan Attendance</a></li>
                        <li><a href="{{ route('attendance.index') }}"><i class='bx bx-right-arrow-alt'></i>Attendance Report</a></li>
                    </ul>
                </li>
                @endcan

                @can('access-staff-attendance')
                <li>
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Staff Attendance</a>
                    <ul>
                        <li><a href="{{ route('staff_attendance.create') }}"><i class='bx bx-right-arrow-alt'></i>Mark Attendance</a></li>
                        <li><a href="{{ route('staff_attendance.report') }}"><i class='bx bx-right-arrow-alt'></i>Attendance Report</a></li>
                    </ul>
                </li>
                @endcan
            </ul>
        </li>
        @endif

        @if(auth()->user()->can('access-staff-salaries') || auth()->user()->can('access-salary-slips'))
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-wallet-alt'></i></div>
                <div class="menu-title">Payroll</div>
            </a>
            <ul>
                @can('access-staff-salaries')
                <li><a href="{{ route('staff_salaries.index') }}"><i class='bx bx-radio-circle'></i>Salary Structure</a></li>
                @endcan
                @can('access-salary-slips')
                <li><a href="{{ route('salary_slips.index') }}"><i class='bx bx-radio-circle'></i>Salary Slips</a></li>
                @endcan
            </ul>
        </li>
        @endif

        @can('access-finance-report')
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-line-chart'></i></div>
                <div class="menu-title">Finance Overview</div>
            </a>
            <ul>
                <li><a href="{{ route('finance_report.index') }}"><i class='bx bx-radio-circle'></i>Finance Report</a></li>
            </ul>
        </li>
        @endcan

        @if(auth()->user()->can('access-front-office'))
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-store-alt'></i></div>
                <div class="menu-title">Front Office</div>
            </a>
            <ul>
                <li>
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Front Office Setup</a>
                    <ul>
                        <li><a href="{{ route('settings.purpose.index') }}"><i class='bx bx-chevron-right'></i>Purpose</a></li>
                        <li><a href="{{ route('settings.complaint-type.index') }}"><i class='bx bx-chevron-right'></i>Complaint-Type</a></li>
                        <li><a href="{{ route('settings.source.index') }}"><i class='bx bx-chevron-right'></i>Source</a></li>
                    </ul>
                </li>
                <li><a href="{{ route('admission-enquiry.index') }}"><i class='bx bx-radio-circle'></i>Admission Enquiry</a></li>
                <li><a href="{{ route('visitor-book.index') }}"><i class='bx bx-radio-circle'></i>Visitor Book</a></li>
                <li><a href="{{ route('complain.index') }}"><i class='bx bx-radio-circle'></i>Complaint</a></li>
            </ul>
        </li>
        @endif

        @if(auth()->user()->can('access-fee-types') || auth()->user()->can('access-fee-structures') || auth()->user()->can('access-discount-policies') || auth()->user()->can('access-fine-policies') || auth()->user()->can('access-expense-categories') || auth()->user()->can('access-expenses'))
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-money"></i></div>
                <div class="menu-title">Fees Collection</div>
            </a>
            <ul>
                @can('access-fee-types')
                <li><a href="{{ route('fee_types.index') }}"><i class='bx bx-radio-circle'></i>Fee Type</a></li>
                @endcan

                @can('access-fee-structures')
                <li><a href="{{ route('fee_structure.index') }}"><i class='bx bx-radio-circle'></i>Fee Structure</a></li>
                <li><a href="{{ route('fees.collect.index') }}"><i class='bx bx-radio-circle'></i>Collect Fees</a></li>
                <li><a href="{{ route('fees.collect.report') }}"><i class='bx bx-radio-circle'></i>Fee Reports</a></li>
                <li><a href="{{ route('bank_accounts.index') }}"><i class='bx bx-radio-circle'></i>Bank Accounts</a></li>
                @endcan

                @if(auth()->user()->can('access-discount-policies') || auth()->user()->can('access-fine-policies'))
                <li>
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Fee Settings</a>
                    <ul>
                        @can('access-discount-policies')
                        <li><a href="{{ route('discount_policies.index') }}"><i class='bx bx-radio-circle'></i>Discount Policies</a></li>
                        @endcan
                        @can('access-fine-policies')
                        <li><a href="{{ route('fine_policies.index') }}"><i class='bx bx-radio-circle'></i>Fine Policies</a></li>
                        @endcan
                    </ul>
                </li>
                @endif

                @if(auth()->user()->can('access-expense-categories') || auth()->user()->can('access-expenses'))
                <li>
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Expenses</a>
                    <ul>
                        @can('access-expense-categories')
                        <li><a href="{{ route('expense_categories.index') }}"><i class='bx bx-radio-circle'></i>Expense Categories</a></li>
                        @endcan
                        @can('access-expenses')
                        <li><a href="{{ route('expenses.index') }}"><i class='bx bx-radio-circle'></i>Expense</a></li>
                        @endcan
                    </ul>
                </li>
                @endif
            </ul>
        </li>
        @endif

        {{-- Library Menu --}}
        @canany(['access-book-categories', 'access-books', 'access-book-issues', 'access-library-settings', 'access-library-members'])
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-book-alt"></i></div>
                <div class="menu-title">Library</div>
            </a>
            <ul>
                @can('access-book-categories')
                <li><a href="{{ route('book_category.index') }}"><i class='bx bx-radio-circle'></i>Book Category</a></li>
                @endcan
                @can('access-books')
                <li><a href="{{ route('books.index') }}"><i class='bx bx-radio-circle'></i>Books</a></li>
                @endcan
                @can('access-book-issues')
                <li><a href="{{ route('book_issue.index') }}"><i class='bx bx-radio-circle'></i>Book Issues</a></li>
                @endcan

                @can('access-library-settings')
                <li>
                    <a href="javascript:;" class="has-arrow"><i class='bx bx-radio-circle'></i>Library Settings</a>
                    <ul>
                        <li><a href="{{ route('library_settings.index') }}"><i class='bx bx-radio-circle'></i>General Settings</a></li>
                        @can('access-library-members')
                        <li><a href="{{ route('library_settings.members.student') }}"><i class='bx bx-radio-circle'></i>Library Students</a></li>
                        <li><a href="{{ route('library_settings.members.staff') }}"><i class='bx bx-radio-circle'></i>Library Staff</a></li>
                        @endcan
                    </ul>
                </li>
                @endcan
            </ul>
        </li>
        @endcanany

        {{-- Setup / Settings Parent Menu --}}
<li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bx-layer'></i></div>
        <div class="menu-title">Site Setup</div>
    </a>
    <ul>
        @can('access-site-settings')
        <li><a href="{{ route('site_setting.edit') }}"><i class='bx bx-radio-circle'></i>Site Settings</a></li>
        @endcan
        @can('access-school-timing')
        <li><a href="{{ route('school_timing.index') }}"><i class='bx bx-radio-circle'></i>School Timing</a></li>
        @endcan
    </ul>
</li>

        {{-- ID Cards Menu --}}
        @canany(['access-id-card-templates', 'access-staff-id-card-templates'])
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-id-card'></i></div>
                <div class="menu-title">ID Cards</div>
            </a>
            <ul>
                @can('access-id-card-templates')
                <li><a href="{{ route('id_card_template.index') }}"><i class='bx bx-radio-circle'></i>Design Student ID Card</a></li>
                <li><a href="{{ route('print_id_card.index') }}"><i class='bx bx-radio-circle'></i>Print Student ID Card</a></li>
                @endcan

                @can('access-staff-id-card-templates')
                <li><a href="{{ route('staff_id_card_template.index') }}"><i class='bx bx-radio-circle'></i>Design Staff ID Card</a></li>
                <li><a href="{{ route('print_staff_id_card.index') }}"><i class='bx bx-radio-circle'></i>Print Staff ID Card</a></li>
                @endcan
            </ul>
        </li>
        @endcanany
    </ul>
</div>