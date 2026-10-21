<nav class="navbar navbar-expand-lg navbar-dark bg-dark ftco_navbar ftco-navbar-light" id="ftco-navbar">
        <div class="container d-flex align-items-center">
            <a class="navbar-brand" href="{{ route('dashboard') }}">Smart School</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="oi oi-menu"></span> Menu
            </button>
            <div class="collapse navbar-collapse" id="ftco-nav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item active"><a href="{{ route('login') }}" class="nav-link pl-0">Home</a></li>
                    <li class="nav-item"><a href="about.html" class="nav-link">About</a></li>
                    <li class="nav-item"><a href="teacher.html" class="nav-link">Teacher</a></li>
                    <li class="nav-item"><a href="courses.html" class="nav-link">Courses</a></li>
                    <li class="nav-item"><a href="pricing.html" class="nav-link">Pricing</a></li>
                    <li class="nav-item"><a href="blog.html" class="nav-link">Blog</a></li>
                    <li class="nav-item"><a href="contact.html" class="nav-link">Contact</a></li>
                    @guest
                    <li class="nav-item"><a href="{{ route('login') }}" class="nav-link">Login</a></li>
                    <li class="nav-item"><a href="{{ route('register') }}" class="nav-link">Register</a></li>
                    @endguest
                    @auth
                    @php
                        // Logged-in user: naam + active role (layout wala same source: activeRoleName)
                        $navRole = auth()->user()->activeRoleName();
                        $navRoleLabel = $navRole === 'superadmin' ? 'Super Admin' : ucfirst((string) $navRole);
                    @endphp
                    <li class="nav-item dropdown d-none d-lg-block">
                        <a class="nav-link" href="#" id="navUserMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><strong>{{ auth()->user()->name }}</strong>@if ($navRole) <span class="text-primary font-weight-normal">({{ $navRoleLabel }})</span>@endif</a>
                        <div class="dropdown-menu" aria-labelledby="navUserMenu">
                            <a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </div>
                    </li>
                    {{-- Mobile (below lg): hover + data-toggle clash on tap, so plain links instead of the dropdown --}}
                    <li class="nav-item d-lg-none"><span class="nav-link"><strong>{{ auth()->user()->name }}</strong>@if ($navRole) <span class="text-primary font-weight-normal">({{ $navRoleLabel }})</span>@endif</span></li>
                    <li class="nav-item d-lg-none"><a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a></li>
                    <li class="nav-item d-lg-none">
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="nav-link bg-transparent border-0 w-100 text-left" style="font-size: 15px; padding: .9rem 0; color: rgba(255, 255, 255, 0.7);">Logout</button>
                        </form>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
    <!-- END nav -->