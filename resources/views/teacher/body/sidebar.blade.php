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
		{{-- My Classes & Subjects --}}
		<li>
			<a href="#">
				<div class="parent-icon"><i class='bx bx-chalkboard'></i></div>
				<div class="menu-title">My Classes</div>
			</a>
		</li>
		{{-- Timetable --}}
		@can('access-my-timetable')
		<li>
			<a href="{{ route('teacher.timetable.index') }}">
				<div class="parent-icon"><i class="bx bx-time"></i></div>
				<div class="menu-title">My Timetable</div>
			</a>
		</li>
		@endcan
		{{-- Attendance --}}
		@can('access-attendance')
		<li>
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class="bx bx-calendar-check"></i></div>
				<div class="menu-title">Attendance</div>
			</a>
			<ul>
				<li>
					<a href="{{ route('attendance.create') }}">
						<i class='bx bx-radio-circle'></i>Mark Attendance
					</a>
				</li>
				<li>
					<a href="{{ route('attendance.report') }}">
						<i class='bx bx-radio-circle'></i>My Class Report
					</a>
				</li>
			</ul>
		</li>
		@endcan
		{{-- Homework --}}
		@can('access-home-works')
		<li>
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class="bx bx-book-content"></i></div>
				<div class="menu-title">Homework</div>
			</a>
			<ul>
				<li><a href="{{ route('home_work.index') }}"><i class='bx bx-radio-circle'></i>Manage Homework</a></li>
			</ul>
		</li>
		@endcan
		{{-- Academics: Lesson Plan / Lesson / Topic --}}
		@can('access-my-lesson-plan')
		<li>
			<a href="{{ route('teacher.lesson_plan.index') }}">
				<div class="parent-icon"><i class="bx bx-book-open"></i></div>
				<div class="menu-title">Lesson Plan</div>
			</a>
		</li>
		@endcan
		{{-- Syllabus Status --}}
		@can('access-my-syllabus-status')
		<li>
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class="bx bx-check-shield"></i></div>
				<div class="menu-title">Syllabus Status</div>
			</a>
			<ul>
				<li>
					<a href="{{ route('teacher.syllabus_status.index') }}">
						<i class='bx bx-radio-circle'></i>My Syllabus Status
					</a>
				</li>
			</ul>
		</li>
		@endcan
		{{-- Examination --}}
		@can('access-my-exam-schedule')
		<li>
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class="bx bx-calendar-event"></i></div>
				<div class="menu-title">Examination</div>
			</a>
			<ul>
				<li>
					<a href="{{ route('teacher.exam_schedule.index') }}">
						<i class='bx bx-radio-circle'></i>Exam Schedule
					</a>
				</li>
			</ul>
		</li>
		@endcan
		{{-- Results / Marks Entry --}}
		@can('access-mark-sheet')
		<li>
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class="bx bx-medal"></i></div>
				<div class="menu-title">Results</div>
			</a>
			<ul>
				<li><a href="#"><i class='bx bx-radio-circle'></i>Enter Marks</a></li>
			</ul>
		</li>
		@endcan
		{{-- Leave Application --}}
		@can('access-leave-application')
		<li>
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class="bx bx-envelope"></i></div>
				<div class="menu-title">Leave Application</div>
			</a>
			<ul>
				<li>
					<a href="{{ route('staffleaveapp.index') }}">
						<i class='bx bx-radio-circle'></i>View/Manage Leaves
					</a>
				</li>
			</ul>
		</li>
		@endcan
		{{-- Reports: logged-in user ki apni reports (purana Salary block yahan merge, aage aur links aayenge) --}}
		@canany(['access-my-attendance', 'access-my-salary-slips'])
		<li>
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class="bx bx-file"></i></div>
				<div class="menu-title">Reports</div>
			</a>
			<ul>
				@can('access-my-attendance')
				<li>
					<a href="{{ route('my.attendance.index') }}">
						<i class='bx bx-radio-circle'></i>My Attendance
					</a>
				</li>
				@endcan
				@can('access-my-salary-slips')
				<li>
					<a href="{{ route('my.salary_slips.index') }}">
						<i class='bx bx-radio-circle'></i>My Salary Slips
					</a>
				</li>
				@endcan
			</ul>
		</li>
		@endcanany
	</ul>
	<!--end navigation-->
</div>