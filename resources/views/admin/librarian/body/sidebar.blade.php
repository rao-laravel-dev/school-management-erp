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
		<li>
			<a href="{{ route('librarian.profile') }}">
				<div class="parent-icon"><i class='bx bx-user'></i>
				</div>
				<div class="menu-title">My Profile</div>
			</a>
		</li>

	</ul>
	<!--end navigation-->
</div>