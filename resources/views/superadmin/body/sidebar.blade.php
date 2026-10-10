<div class="sidebar-wrapper" data-simplebar="true">
	<div class="sidebar-header">
		<div>
			<img src="{{ asset('backend/assets/images/logo-icon.png') }}" class="logo-icon" alt="logo icon">
		</div>
		<div>
			<h4 class="logo-text">Super Admin</h4>
		</div>
		<div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i>
		</div>
	</div>
	<!--navigation-->
	<ul class="metismenu" id="menu">

    {{-- Dashboard --}}
    @can('view dashboard')
    <li>
        <a href="{{ route('superadmin.dashboard') }}">
            <div class="parent-icon"><i class='bx bx-home-alt'></i></div>
            <div class="menu-title">Dashboard</div>
        </a>
    </li>
    @endcan


    {{-- Students --}}
    @can('view students')
    <li>
        <a href="javascript:;" class="has-arrow">
            <div class="parent-icon"><i class="bx bx-user"></i></div>
            <div class="menu-title">Students</div>
        </a>
        <ul>
            @can('view students')
            <li><a href="#"><i class='bx bx-radio-circle'></i>All Students</a></li>
            @endcan

            @can('add students')
            <li><a href="#"><i class='bx bx-radio-circle'></i>Add Student</a></li>
            @endcan
        </ul>
    </li>
    @endcan


    {{-- Roles --}}
    @can('view roles')
    <li>
        <a href="javascript:;" class="has-arrow">
            <div class="parent-icon"><i class="bx bx-lock"></i></div>
            <div class="menu-title">Roles & Permissions</div>
        </a>
        <ul>
            <li>
                <a href="{{ route('superadmin.roles.index') }}">
                    <i class='bx bx-radio-circle'></i>All Roles
                </a>
            </li>

            @can('add roles')
            <li>
                <a href="{{ route('superadmin.roles.create') }}">
                    <i class='bx bx-radio-circle'></i>Add Role
                </a>
            </li>
            @endcan
        </ul>
    </li>
    @endcan

</ul>
	<!--end navigation-->
</div>