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
					
					<li class="nav-item dark-mode d-flex">
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
                        <span class="fw-bold text-dark dark-readable text-truncate d-inline-block" style="font-size: 13px; max-width: 130px;">
                            {{ ucfirst($assignedTeacher->first_name) }} {{ ucfirst($assignedTeacher->last_name) }}
                        </span>
                    </div>

                    <span class="badge bg-primary rounded-pill px-2 py-1" style="font-size: 11px;">
                        {{ $assignedTeacher->class_name }}-{{ $assignedTeacher->section_name }}
                    </span>
                </div>
            </li>
        @endif


					@include('partials.notification_bell')
					
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
					@can('access-profile')
					<li><a class="dropdown-item d-flex align-items-center" href="{{ route('student.profile') }}"><i
								class="bx bx-user fs-5"></i><span>Profile</span></a>
					</li>
					@endcan
					<li><a class="dropdown-item d-flex align-items-center" href="{{ route('student.change.password') }}"><i
								class="bx bx-cog fs-5"></i><span>Change Password</span></a>
					</li>
					<li><a class="dropdown-item d-flex align-items-center" href="{{ route('student.dashboard') }}"><i
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