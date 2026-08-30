<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <img src="{{ asset('backend/assets/images/logo-icon.png') }}" class="logo-icon" alt="logo icon">
        </div>
        <div>
            <h4 class="logo-text">Student</h4>
        </div>
        <div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i></div>
    </div>

    <ul class="metismenu" id="menu">
        <li>
            <a href="{{ route('student.dashboard') }}">
                <div class="parent-icon"><i class='bx bx-home-alt'></i></div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-user-circle"></i></div>
                <div class="menu-title">My Profile</div>
            </a>
            <ul>
                <li><a href="#"><i class='bx bx-radio-circle'></i>Personal Details</a></li>
                <li><a href="#"><i class='bx bx-radio-circle'></i>Academic Records</a></li>
            </ul>
        </li>

        @can('access-academics')
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-book-reader"></i></div>
                <div class="menu-title">Academics</div>
            </a>
            <ul>
                <li><a href="{{ route('student.subjects.view') }}"><i class='bx bx-radio-circle'></i>My Subjects</a></li>
                <li><a href="#"><i class='bx bx-radio-circle'></i>Class Schedule</a></li>
                <li><a href="{{ route('student.teacher.myteacher') }}"><i class='bx bx-radio-circle'></i>My Teachers</a></li>
                <li><a href="{{ route('student.results.index') }}"><i class='bx bx-radio-circle'></i>Results/Grades</a></li>
            </ul>
        </li>
        @endcan

        @can('access-attendance')
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-calendar-check"></i></div>
                <div class="menu-title">Attendance</div>
            </a>
            <ul>
                <li><a href="{{ route('student.attendance.view') }}"><i class='bx bx-radio-circle'></i>View Attendance</a></li>
            </ul>
        </li>
        @endcan

        @can('access-assignments')
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-edit"></i></div>
                <div class="menu-title">Assignments</div>
            </a>
            <ul>
                <li><a href="#"><i class='bx bx-radio-circle'></i>Pending Assignments</a></li>
                <li><a href="#"><i class='bx bx-radio-circle'></i>Submitted Work</a></li>
            </ul>
        </li>
        @endcan

        @can('access-fee')
        <li>
            <a href="javascript:;">
                <div class="parent-icon"><i class="bx bx-wallet"></i></div>
                <div class="menu-title">Fee Details</div>
            </a>
        </li>
        @endcan
    </ul>
</div>