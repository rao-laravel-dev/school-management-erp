<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <img src="{{ asset('backend/assets/images/logo-icon.png') }}" class="logo-icon" alt="logo icon">
        </div>
        <div>
            <h4 class="logo-text">{{ ucfirst(auth()->user()->roles->first()->name) }}</h4>
        </div>
        <div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i>
        </div>
    </div>
    <ul class="metismenu" id="menu">
        <li class="{{ request()->routeIs('parent.dashboard') ? 'mm-active' : '' }}">
            <a href="{{ route('parent.dashboard') }}">
                <div class="parent-icon"><i class='bx bx-home-alt'></i>
                </div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>

        {{-- Child links without an active child go to ?select=1 (redirects to the clean dashboard), so they never exact-match the current URL for app.js --}}
        @php $open = request()->routeIs('parent.students.dashboard', 'parent.students.details'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-category"></i>
                </div>
                <div class="menu-title">Student Information</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                <li class="{{ $open ? 'mm-active' : '' }}"> <a href="{{ $parentChildId ? route('parent.students.dashboard', $parentChildId) : route('parent.dashboard', ['select' => 1]) }}"><i class='bx bx-radio-circle'></i>Students Information</a>
                </li>
            </ul>
        </li>

        @canany(['access-parent-timetable', 'access-parent-results'])
        @php $open = request()->routeIs('parent.students.class_schedule.*', 'parent.students.results.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-category"></i>
                </div>
                <div class="menu-title">Academics</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                @can('access-parent-timetable')
                @php $openItem = request()->routeIs('parent.students.class_schedule.*'); @endphp
                <li class="{{ $openItem ? 'mm-active' : '' }}"> <a href="{{ $parentChildId ? route('parent.students.class_schedule.index', $parentChildId) : route('parent.dashboard', ['select' => 1]) }}"><i class='bx bx-radio-circle'></i>Class Schedule</a>
                </li>
                @endcan
                @can('access-parent-results')
                @php $openItem = request()->routeIs('parent.students.results.*'); @endphp
                <li class="{{ $openItem ? 'mm-active' : '' }}"> <a href="{{ $parentChildId ? route('parent.students.results.index', $parentChildId) : route('parent.dashboard', ['select' => 1]) }}"><i class='bx bx-radio-circle'></i>Results</a>
                </li>
                @endcan
            </ul>
        </li>
        @endcanany

        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-category"></i>
                </div>
                <div class="menu-title">Attendance</div>
            </a>
            <ul>
                <li> <a href="#"><i class='bx bx-radio-circle'></i>Attendance</a>
                </li>
            </ul>
        </li>

        @can('access-parent-homework')
        @php $open = request()->routeIs('parent.students.homework.*'); @endphp
        <li class="{{ $open ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-category"></i>
                </div>
                <div class="menu-title">Home Work</div>
            </a>
            <ul class="{{ $open ? 'mm-show' : '' }}">
                <li class="{{ $open ? 'mm-active' : '' }}"> <a href="{{ $parentChildId ? route('parent.students.homework.index', $parentChildId) : route('parent.dashboard', ['select' => 1]) }}"><i class='bx bx-radio-circle'></i>Home Work</a>
                </li>
            </ul>
        </li>
        @endcan
    </ul>
    </div>