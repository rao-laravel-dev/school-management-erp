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
			<a href="{{ route('librarian.dashboard') }}" class="has-arrow">
				<div class="parent-icon"><i class='bx bx-home-alt'></i>
				</div>
				<div class="menu-title">Dashboard</div>
			</a>


		</li>
		<li>
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class="bx bx-category"></i>
				</div>
				<div class="menu-title">Receptionist Details</div>
			</a>
			<ul>
				<li> <a href="#"><i class='bx bx-radio-circle'></i>Receptionist Details</a>
				</li>


			</ul>
		</li>

		<!-- Account Details -->
		<li>
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class="bx bx-category"></i>
				</div>
				<div class="menu-title">Accountant Details</div>
			</a>
			<ul>
				<li> <a href="#"><i class='bx bx-radio-circle'></i>Accountant Details</a>
				</li>


			</ul>
		</li>

		<!-- logo -->
		<li>
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class="bx bx-category"></i>
				</div>
				<div class="menu-title">Logo</div>
			</a>
			<ul>
				<li> <a href="#"><i class='bx bx-radio-circle'></i>Logo</a>
				</li>


			</ul>
		</li>

		<!-- Front Office -->
		<li>
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class="bx bx-category"></i>
				</div>
				<div class="menu-title">Front Office</div>
			</a>
			<ul>
				<li> <a href="#"><i class='bx bx-radio-circle'></i>Front Office</a>
				</li>


			</ul>
		</li>


		<!-- Student Information -->
		<li>
			<a href="javascript:;" class="has-arrow">
				<div class="parent-icon"><i class="bx bx-category"></i>
				</div>
				<div class="menu-title">Student Information</div>
			</a>
			<ul>
				<li> <a href="#"><i class='bx bx-radio-circle'></i>Students Information</a>
				</li>
					</ul>
				</li>


				<!-- Teacher Information -->
				<li>
					<a href="javascript:;" class="has-arrow">
						<div class="parent-icon"><i class="bx bx-category"></i>
						</div>
						<div class="menu-title">Accountant Details</div>
					</a>
					<ul>
						<li> <a href="#"><i class='bx bx-radio-circle'></i>Accountant Details</a>
						</li>


					</ul>
				</li>


				<!-- Acedemics -->
				<li>
					<a href="javascript:;" class="has-arrow">
						<div class="parent-icon"><i class="bx bx-category"></i>
						</div>
						<div class="menu-title">Acedemics</div>
					</a>
					<ul>
						<li> <a href="#"><i class='bx bx-radio-circle'></i>Acedemics</a>
						</li>


					</ul>
				</li>

				<!-- Attendance -->
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
				<!-- Home Work -->
				<li>
					<a href="javascript:;" class="has-arrow">
						<div class="parent-icon"><i class="bx bx-category"></i>
						</div>
						<div class="menu-title">Home Work</div>
					</a>
					<ul>
						<li> <a href="#"><i class='bx bx-radio-circle'></i>Home Work</a>
						</li>
			</ul>
		</li>

	</ul>
	<!--end navigation-->
</div>