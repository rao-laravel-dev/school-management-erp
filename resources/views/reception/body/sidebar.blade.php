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
                <li>
                    <a href="{{ route('staffleaveapp.create') }}">
                        <i class='bx bx-radio-circle'></i>Apply Leave
                    </a>
                </li>
            </ul>
        </li>
        @endcan

        @can('access-attendance')
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-check-shield'></i></div>
                <div class="menu-title">Attendance</div>
            </a>
            <ul>
                <li><a href="{{ route('attendance.create') }}"><i class='bx bx-radio-circle'></i>Mark Attendance</a></li>
                <li><a href="{{ route('attendance.index') }}"><i class='bx bx-radio-circle'></i>View Report</a></li>
            </ul>
        </li>
        @endcan

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
                        <li><a href="{{ route('classes.create') }}"><i class='bx bx-right-arrow-alt'></i>Add Class</a></li>
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
            </ul>
        </li>
        @endcan
    </ul>
</div>