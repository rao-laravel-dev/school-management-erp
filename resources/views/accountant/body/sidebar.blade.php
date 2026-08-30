<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div><img src="{{ asset('backend/assets/images/logo-icon.png') }}" class="logo-icon" alt="logo icon"></div>
        <div>
            <h4 class="logo-text">Accountant</h4>
        </div>
        <div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i></div>
    </div>

    <ul class="metismenu" id="menu">
        <li>
            <a href="{{ route('accountant.dashboard') }}">
                <div class="parent-icon"><i class='bx bx-home-alt'></i></div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>

        @can('access-receptionist')
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-user-circle"></i></div>
                <div class="menu-title">Receptionist</div>
            </a>
            <ul>
                <li><a href="{{ route('receptionist.index') }}"><i class='bx bx-radio-circle'></i>Receptionist List</a></li>
            </ul>
        </li>
        @endcan

        @can('access-teacher')
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-chalkboard"></i></div>
                <div class="menu-title">Teacher Management</div>
            </a>
            <ul>
                <li><a href="{{ route('teacher.index') }}"><i class='bx bx-radio-circle'></i>Teachers List</a></li>
            </ul>
        </li>
        @endcan

        @can('access-students')
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-user-pin"></i></div>
                <div class="menu-title">Student Information</div>
            </a>
            <ul>
                <li><a href="{{ route('students.index') }}"><i class='bx bx-radio-circle'></i>Students List</a></li>
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
                <li><a href="{{ route('attendance.index') }}"><i class='bx bx-radio-circle'></i>Attendance Report</a></li>
            </ul>
        </li>
        @endcan

        @can('access-leave-application')
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-calendar-x'></i></div>
                <div class="menu-title">Leave Applications</div>
            </a>
            <ul>
                <li><a href="{{ route('staffleaveapp.index') }}"><i class='bx bx-radio-circle'></i>Leave List</a></li>
            </ul>
        </li>
        @endcan
    </ul>
</div>