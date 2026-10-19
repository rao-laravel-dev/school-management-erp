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
		{{-- Dashboard (Overview) --}}
		<li>
			<a href="{{ route('teacher.dashboard') }}">
				<div class="parent-icon"><i class='bx bx-home-alt'></i></div>
				<div class="menu-title">Dashboard</div>
			</a>
		</li>
		{{-- Attendance --}}
		@can('access-attendance')
		@php $open = request()->routeIs('attendance.*'); @endphp
		<li class="{{ $open ? 'mm-active' : '' }}">
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class="bx bx-calendar-check"></i></div>
				<div class="menu-title">Attendance</div>
			</a>
			<ul class="{{ $open ? 'mm-show' : '' }}">
				@can('mark-attendance')
				<li>
					<a href="{{ route('attendance.create') }}">
						<i class='bx bx-radio-circle'></i>Mark Attendance
					</a>
				</li>
				@endcan
				@can('access-scan-attendance')
				<li>
					<a href="{{ route('attendance.scan') }}">
						<i class='bx bx-radio-circle'></i>Scan Attendance
					</a>
				</li>
				@endcan
				<li>
					<a href="{{ route('attendance.report') }}">
						<i class='bx bx-radio-circle'></i>My Class Report
					</a>
				</li>
				<li class="{{ request()->routeIs('attendance.class_report', 'attendance.student_report') ? 'mm-active' : '' }}">
					<a href="{{ route('attendance.class_report') }}">
						<i class='bx bx-radio-circle'></i>Class Attendance Report
					</a>
				</li>
			</ul>
		</li>
		@endcan
		{{-- Academics: Timetable / Homework / Lesson Plan / Syllabus Status --}}
		@canany(['access-my-timetable', 'access-class-timetable', 'access-home-works', 'access-my-lesson-plan', 'access-my-syllabus-status'])
		@php $open = request()->routeIs('teacher.timetable.*', 'class_timetable.*', 'home_work.*', 'teacher.lesson_plan.*', 'teacher.syllabus_status.*'); @endphp
		<li class="{{ $open ? 'mm-active' : '' }}">
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class="bx bx-book-open"></i></div>
				<div class="menu-title">Academics</div>
			</a>
			<ul class="{{ $open ? 'mm-show' : '' }}">
				{{-- Timetable --}}
				@can('access-my-timetable')
				<li><a href="{{ route('teacher.timetable.index') }}"><i class='bx bx-radio-circle'></i>My Timetable</a></li>
				@endcan
				{{-- Class Timetable (school-wide, read-only) --}}
				@can('access-class-timetable')
				<li><a href="{{ route('class_timetable.index') }}"><i class='bx bx-radio-circle'></i>Class Timetable</a></li>
				@endcan
				{{-- Homework --}}
				@can('access-home-works')
				<li><a href="{{ route('home_work.index') }}"><i class='bx bx-radio-circle'></i>Manage Homework</a></li>
				@endcan
				{{-- Lesson Plan / Lesson / Topic --}}
				@can('access-my-lesson-plan')
				<li><a href="{{ route('teacher.lesson_plan.index') }}"><i class='bx bx-radio-circle'></i>Lesson Plan</a></li>
				@endcan
				{{-- Syllabus Status --}}
				@can('access-my-syllabus-status')
				<li><a href="{{ route('teacher.syllabus_status.index') }}"><i class='bx bx-radio-circle'></i>My Syllabus Status</a></li>
				@endcan
			</ul>
		</li>
		@endcanany
		{{-- Examination: Exam Schedule / Results / Marks Entry --}}
		@canany(['access-my-exam-schedule', 'manage-marksheets', 'access-results', 'access-marksheets'])
		@php $open = request()->routeIs('teacher.exam_schedule.*', 'marksheet.*', 'result.*', 'print_marksheet.*'); @endphp
		<li class="{{ $open ? 'mm-active' : '' }}">
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class="bx bx-calendar-event"></i></div>
				<div class="menu-title">Examination</div>
			</a>
			<ul class="{{ $open ? 'mm-show' : '' }}">
				@can('access-my-exam-schedule')
				<li><a href="{{ route('teacher.exam_schedule.index') }}"><i class='bx bx-radio-circle'></i>Exam Schedule</a></li>
				@endcan
				{{-- Marks entry: server pe class_timetables se teacher access dobara check hota hai --}}
				@can('manage-marksheets')
				<li><a href="{{ route('marksheet.index') }}"><i class='bx bx-radio-circle'></i>Enter Marks</a></li>
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
		{{-- Leave Application --}}
		@can('access-leave-application')
		@php $open = request()->routeIs('staffleaveapp.create'); @endphp
		<li class="{{ $open ? 'mm-active' : '' }}">
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class="bx bx-envelope"></i></div>
				<div class="menu-title">Leave Application</div>
			</a>
			<ul class="{{ $open ? 'mm-show' : '' }}">
				{{-- create route index page par redirect karta hai (apply modal wahin hai) --}}
				<li>
					<a href="{{ route('staffleaveapp.create') }}">
						<i class='bx bx-radio-circle'></i>Apply Leave
					</a>
				</li>
			</ul>
		</li>
		@endcan
		{{-- Reports: logged-in user ki apni reports (purana Salary block yahan merge, aage aur links aayenge) --}}
		@canany(['access-my-attendance', 'access-leave-application', 'access-my-salary-slips'])
		@php $open = request()->routeIs('my.*', 'staffleaveapp.*') && ! request()->routeIs('staffleaveapp.create'); @endphp
		<li class="{{ $open ? 'mm-active' : '' }}">
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class="bx bx-file"></i></div>
				<div class="menu-title">Reports</div>
			</a>
			<ul class="{{ $open ? 'mm-show' : '' }}">
				@can('access-my-attendance')
				<li>
					<a href="{{ route('my.attendance.index') }}">
						<i class='bx bx-radio-circle'></i>My Attendance Report
					</a>
				</li>
				@endcan
				@can('access-leave-application')
				<li>
					<a href="{{ route('staffleaveapp.index') }}">
						<i class='bx bx-radio-circle'></i>My Leave Report
					</a>
				</li>
				@endcan
				@can('access-my-salary-slips')
				<li>
					<a href="{{ route('my.salary_slips.index') }}">
						<i class='bx bx-radio-circle'></i>My Salary Slip
					</a>
				</li>
				@endcan
			</ul>
		</li>
		@endcanany
		{{-- Logout (hamesha last) --}}
		<li>
			<a href="{{ route('teacher.logout') }}">
				<div class="parent-icon"><i class="bx bx-log-out-circle"></i></div>
				<div class="menu-title">Logout</div>
			</a>
		</li>
	</ul>
	<!--end navigation-->
</div>
