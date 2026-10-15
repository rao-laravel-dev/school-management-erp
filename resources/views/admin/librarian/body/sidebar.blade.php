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
	<!--navigation-->
	<ul class="metismenu" id="menu">
		<li>
			<a href="{{ route('librarian.dashboard') }}">
				<div class="parent-icon"><i class='bx bx-home-alt'></i>
				</div>
				<div class="menu-title">Dashboard</div>
			</a>
		</li>

		{{-- Library --}}
		@canany(['access-book-categories', 'access-books', 'access-book-issues', 'access-library-settings'])
		@php $open = request()->routeIs('book_category.*', 'books.*', 'book_issue.index', 'book_issue.show', 'library_settings.index', 'library_settings.update'); @endphp
		<li class="{{ $open ? 'mm-active' : '' }}">
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class='bx bx-library'></i></div>
				<div class="menu-title">Library</div>
			</a>
			<ul class="{{ $open ? 'mm-show' : '' }}">
				@can('access-book-categories')
				<li><a href="{{ route('book_category.index') }}"><i class='bx bx-radio-circle'></i>Book Categories</a></li>
				@endcan
				@can('access-books')
				<li><a href="{{ route('books.index') }}"><i class='bx bx-radio-circle'></i>Books</a></li>
				@endcan
				@can('access-book-issues')
				<li><a href="{{ route('book_issue.index') }}"><i class='bx bx-radio-circle'></i>Issue / Return Book</a></li>
				@endcan
				@can('access-library-settings')
				<li><a href="{{ route('library_settings.index') }}"><i class='bx bx-radio-circle'></i>Library Settings</a></li>
				@endcan
			</ul>
		</li>
		@endcanany

		{{-- Members (student aur staff alag) --}}
		@can('access-library-members')
		@php $open = request()->routeIs('library_settings.members.*'); @endphp
		<li class="{{ $open ? 'mm-active' : '' }}">
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class='bx bx-user-plus'></i></div>
				<div class="menu-title">Members</div>
			</a>
			<ul class="{{ $open ? 'mm-show' : '' }}">
				<li><a href="{{ route('library_settings.members.student') }}"><i class='bx bx-radio-circle'></i>Student Members</a></li>
				<li><a href="{{ route('library_settings.members.staff') }}"><i class='bx bx-radio-circle'></i>Staff Members</a></li>
			</ul>
		</li>
		@endcan

		{{-- Leave --}}
		@can('access-leave-application')
		@php $open = request()->routeIs('staffleaveapp.create'); @endphp
		<li class="{{ $open ? 'mm-active' : '' }}">
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class='bx bx-calendar-x'></i></div>
				<div class="menu-title">Leave</div>
			</a>
			<ul class="{{ $open ? 'mm-show' : '' }}">
				{{-- create route index page par redirect karta hai (apply modal wahin hai) --}}
				<li><a href="{{ route('staffleaveapp.create') }}"><i class='bx bx-radio-circle'></i>Apply Leave</a></li>
			</ul>
		</li>
		@endcan

		{{-- Reports --}}
		@canany(['access-book-issues', 'access-my-attendance', 'access-leave-application', 'access-my-salary-slips'])
		@php $open = request()->routeIs('book_issue.reports*', 'my.*', 'staffleaveapp.*') && ! request()->routeIs('staffleaveapp.create'); @endphp
		<li class="{{ $open ? 'mm-active' : '' }}">
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class='bx bx-file'></i></div>
				<div class="menu-title">Reports</div>
			</a>
			<ul class="{{ $open ? 'mm-show' : '' }}">
				@can('access-book-issues')
				<li><a href="{{ route('book_issue.reports') }}"><i class='bx bx-radio-circle'></i>Library Reports</a></li>
				@endcan
				@can('access-my-attendance')
				<li><a href="{{ route('my.attendance.index') }}"><i class='bx bx-radio-circle'></i>My Attendance Report</a></li>
				@endcan
				@can('access-leave-application')
				<li><a href="{{ route('staffleaveapp.index') }}"><i class='bx bx-radio-circle'></i>My Leave Report</a></li>
				@endcan
				@can('access-my-salary-slips')
				<li><a href="{{ route('my.salary_slips.index') }}"><i class='bx bx-radio-circle'></i>My Salary Slip</a></li>
				@endcan
			</ul>
		</li>
		@endcanany

		{{-- Logout (hamesha last) --}}
		<li>
			<a href="{{ route('librarian.logout') }}">
				<div class="parent-icon"><i class='bx bx-log-out-circle'></i></div>
				<div class="menu-title">Logout</div>
			</a>
		</li>
	</ul>
	<!--end navigation-->
</div>
