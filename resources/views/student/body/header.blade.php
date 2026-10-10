<header>
	<div class="topbar d-flex align-items-center">
		<nav class="navbar navbar-expand gap-3">
			<div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
			</div>

			<div class="position-relative search-bar d-lg-block d-none" data-bs-toggle="modal"
				data-bs-target="#SearchModal">
				<input class="form-control px-5" disabled type="search" placeholder="Search">
				<span class="position-absolute top-50 search-show ms-3 translate-middle-y start-0 top-50 fs-5"><i
						class='bx bx-search'></i></span>
			</div>


			<div class="top-menu ms-auto">
				<ul class="navbar-nav align-items-center gap-1">
					<li class="nav-item mobile-search-icon d-flex d-lg-none" data-bs-toggle="modal"
						data-bs-target="#SearchModal">
						<a class="nav-link" href="avascript:;"><i class='bx bx-search'></i>
						</a>
					</li>
					
					<li class="nav-item dark-mode d-none d-sm-flex">
						<a class="nav-link dark-mode-icon" href="javascript:;"><i class='bx bx-moon'></i>
						</a>
					</li>

					@if(isset($assignedTeacher) && $assignedTeacher)
            <li class="nav-item dropdown dropdown-large d-none d-md-flex me-2">
                <div class="d-flex align-items-center bg-light-primary px-3 py-1 rounded-pill border" style="gap: 10px;">
                    @if(!empty($assignedTeacher->photo))
                        {{-- $assignedTeacher stdClass hai (DB::table), isliye Teacher model se photo_url liya --}}
                        <img src="{{ (new \App\Models\Teacher)->forceFill(['photo' => $assignedTeacher->photo])->photo_url }}"
                             class="rounded-circle border border-white" 
                             width="32" height="32" style="object-fit: cover;">
                    @else
                        <div class="widgets-icons-2 rounded-circle bg-primary text-white" style="width:32px; height:32px; font-size:16px; line-height:32px;">
                            <i class='bx bxs-user'></i>
                        </div>
                    @endif
                    
                    <div class="text-start" style="line-height: 1.2;">
                        <span class="d-block text-uppercase text-primary fw-bold" style="font-size: 9px; letter-spacing: 0.5px;">Class Teacher</span>
                        <span class="fw-bold text-dark text-truncate d-inline-block" style="font-size: 13px; max-width: 130px;">
                            {{ ucfirst($assignedTeacher->first_name) }} {{ ucfirst($assignedTeacher->last_name) }}
                        </span>
                    </div>

                    <span class="badge bg-primary rounded-pill px-2 py-1" style="font-size: 11px;">
                        {{ $assignedTeacher->class_name }}-{{ $assignedTeacher->section_name }}
                    </span>
                </div>
            </li>
        @endif

					<li class="nav-item dropdown dropdown-app">
    <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown" href="javascript:;"><i class='bx bx-grid-alt'></i></a>
    <div class="dropdown-menu dropdown-menu-end p-0">
        <div class="app-container p-2 my-2">
            <div class="row gx-0 gy-2 row-cols-3 justify-content-center p-2">
                <div class="col"><a href="javascript:;"><div class="app-box text-center"><div class="app-icon"><img src="{{ asset('backend/assets/images/app/canvas-lms-logo.png') }}" width="30" alt=""></div><div class="app-name"><p class="mb-0 mt-1">Canvas</p></div></div></a></div>
                <div class="col"><a href="javascript:;"><div class="app-box text-center"><div class="app-icon"><img src="{{ asset('backend/assets/images/app/google-drive.png') }}" width="30" alt=""></div><div class="app-name"><p class="mb-0 mt-1">Drive</p></div></div></a></div>
                <div class="col"><a href="javascript:;"><div class="app-box text-center"><div class="app-icon"><img src="{{ asset('backend/assets/images/app/outlook.png') }}" width="30" alt=""></div><div class="app-name"><p class="mb-0 mt-1">Mail</p></div></div></a></div>
                <div class="col"><a href="javascript:;"><div class="app-box text-center"><div class="app-icon"><img src="{{ asset('backend/assets/images/app/google-calendar.png') }}" width="30" alt=""></div><div class="app-name"><p class="mb-0 mt-1">Schedule</p></div></div></a></div>
                <div class="col"><a href="javascript:;"><div class="app-box text-center"><div class="app-icon"><img src="{{ asset('backend/assets/images/app/library-lms.png') }}" width="30" alt=""></div><div class="app-name"><p class="mb-0 mt-1">Library</p></div></div></a></div>
                <div class="col"><a href="javascript:;"><div class="app-box text-center"><div class="app-icon"><img src="{{ asset('backend/assets/images/app/notepad with pen.png') }}" width="30" alt=""></div><div class="app-name"><p class="mb-0 mt-1">Notes</p></div></div></a></div>
            </div></div>
    </div>
</li>

					<li class="nav-item dropdown dropdown-large">
						<a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#"
							data-bs-toggle="dropdown"><span class="alert-count">7</span>
							<i class='bx bx-bell'></i>
						</a>
						<div class="dropdown-menu dropdown-menu-end">
							<a href="javascript:;">
								<div class="msg-header">
									<p class="msg-header-title">Notifications</p>
									<p class="msg-header-badge">4 New</p>
								</div>
							</a>
							<div class="header-notifications-list">
								
								<a class="dropdown-item" href="javascript:;">
									<div class="d-flex align-items-center">
										<div class="user-online">
											<img src="{{ asset('backend/assets/images/avatars/avatar-4.png') }}"
												class="msg-avatar" alt="user avatar">
										</div>
										<div class="flex-grow-1">
											<h6 class="msg-name">Katherine Pechon <span class="msg-time float-end">15
													min ago</span></h6>
											<p class="msg-info">Making this the first true generator</p>
										</div>
									</div>
								</a>
								<a class="dropdown-item" href="javascript:;">
									<div class="d-flex align-items-center">
										<div class="notify bg-light-success text-success"><i
												class='bx bx-check-square'></i>
										</div>
										<div class="flex-grow-1">
											<h6 class="msg-name">Your item is shipped <span class="msg-time float-end">5
													hrs
													ago</span></h6>
											<p class="msg-info">Successfully shipped your item</p>
										</div>
									</div>
								</a>
								<a class="dropdown-item" href="javascript:;">
									<div class="d-flex align-items-center">
										<div class="notify bg-light-primary">
											<img src="{{ asset('backend/assets/images/app/github.png') }}" width="25"
												alt="user avatar">
										</div>
										<div class="flex-grow-1">
											<h6 class="msg-name">New 24 authors<span class="msg-time float-end">1 day
													ago</span></h6>
											<p class="msg-info">24 new authors joined last week</p>
										</div>
									</div>
								</a>
								<a class="dropdown-item" href="javascript:;">
									<div class="d-flex align-items-center">
										<div class="user-online">
											<img src="{{ asset('backend/assets/images/avatars/avatar-8.png') }}"
												class="msg-avatar" alt="user avatar">
										</div>
										<div class="flex-grow-1">
											<h6 class="msg-name">Peter Costanzo <span class="msg-time float-end">6 hrs
													ago</span></h6>
											<p class="msg-info">It was popularised in the 1960s</p>
										</div>
									</div>
								</a>
							</div>
							<a href="javascript:;">
								<div class="text-center msg-footer">
									<button class="btn btn-primary w-100">View All Notifications</button>
								</div>
							</a>
						</div>
					</li>
					
				</ul>
			</div>

			<div class="user-box dropdown px-3">
				<a class="d-flex align-items-center nav-link dropdown-toggle gap-3 dropdown-toggle-nocaret" href="#"
					role="button" data-bs-toggle="dropdown" aria-expanded="false">
					<img src="{{ (Auth::check() && Auth::user()->studentProfile)
	? Auth::user()->studentProfile->photo_url
	: url('uploads/no_image.jpg') }}" class="user-img" alt="user avatar">

					<div class="user-info">
    <p class="user-name mb-0">{{ Auth::user()->name }}</p>
    
    {{-- Yahan Sahi Code Lagayein --}}
    <p class="designattion mb-0">
        {{ ucfirst(Auth::user()->getRoleNames()->first()) }}
    </p>
</div>
				</a>
				<ul class="dropdown-menu dropdown-menu-end">
					<li><a class="dropdown-item d-flex align-items-center" href="{{ route('student.profile') }}"><i
								class="bx bx-user fs-5"></i><span>Profile</span></a>
					</li>
					<li><a class="dropdown-item d-flex align-items-center" href=""><i
								class="bx bx-cog fs-5"></i><span>Change Password</span></a>
					</li>
					<li><a class="dropdown-item d-flex align-items-center" href=""><i
								class="bx bx-home-circle fs-5"></i><span>Dashboard</span></a>
					</li>
					<li>
						<div class="dropdown-divider mb-0"></div>
					</li>
					<li><a class="dropdown-item d-flex align-items-center" href="{{ route('student.logout')}}"><i
								class="bx bx-log-out-circle"></i><span>Logout</span></a>
					</li>
				</ul>
			</div>
		</nav>
	</div>
</header>