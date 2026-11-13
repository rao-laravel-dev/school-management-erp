<!doctype html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="{{ $schoolBrand->icon_url ?: ($mssIcon ?: asset('backend/assets/images/favicon-32x32.png')) }}" type="{{ $schoolBrand->icon_url ? 'image/webp' : 'image/png' }}" />
	<!--plugins-->
	<link href="{{ asset('backend/assets/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet" />
	<link href="{{ asset('backend/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet" />
	<link href="{{ asset('backend/assets/plugins/metismenu/css/metisMenu.min.css') }}" rel="stylesheet" />
	<!-- loader-->
	<link href="{{ asset('backend/assets/css/pace.min.css') }}" rel="stylesheet" />
	<script src="{{ asset('backend/assets/js/pace.min.js') }}"></script>
	<!-- Bootstrap CSS -->
	<link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/bootstrap-extended.css') }}" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/app.css') }}" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/icons.css') }}" rel="stylesheet">
	{{-- <title>Rocker - Bootstrap 5 Admin Dashboard Template</title> --}}
	<title>Login</title>
</head>

<body class="">
	<!--wrapper-->
	<div class="wrapper">
		<div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
			<div class="container">
				<div class="row">
					<div class="col-12 mx-auto">
						<div class="card mb-0 overflow-hidden login-card-outer">
							<div class="card-body p-0">
								<div class="row g-0">
								{{-- Left: Modern School System product branding --}}
								<div class="col-12 col-lg-6 login-brand-panel d-flex flex-column align-items-center justify-content-center text-center py-4">
									<a href="https://modernschoolsystem.com" target="_blank" rel="noopener" class="login-brand-link mb-3">
										@if($mssLogo)
											<img src="{{ $mssLogo }}" class="login-brand-logo" alt="Modern School System">
										@else
											<span class="login-brand-name">Modern School System</span>
										@endif
									</a>
									<p class="login-brand-credits mb-0 px-3">
										<span class="brand-part">Developed by <a href="https://coresynergylabs.com" target="_blank" rel="noopener">Core Synergy Labs</a></span>
										<span class="brand-sep"> · </span>
										<span class="brand-part">Educational Partner: <a href="https://webdevelopersacademy.com" target="_blank" rel="noopener">Web Developers Academy</a></span>
									</p>
								</div>
								<div class="col-12 col-lg-6">
								<div class="p-4">
									{{-- School's own logo (Site Settings); nothing shown when not uploaded --}}
									@if($schoolBrand->logo_url)
									<div class="mb-3 text-center">
										<img src="{{ $schoolBrand->logo_url }}" class="login-school-logo" alt="{{ $schoolBrand->school_name }}">
									</div>
									@endif
									<div class="text-center mb-4">
										<!-- <h5 class="">Rocker Admin</h5> -->
										<p class="mb-0">Please log in to your account</p>
									</div>
									<div class="form-body">
										<form method="POST" action="{{ route('login') }}" class="row g-1">
											@csrf
											<div class="col-12">
												<label for="email" class="form-label">Email (Staff), CNIC (Parent) or Admission No (Student)</label>
												<input type="text" class="form-control @error('email') is-invalid @enderror" name="email" id="email"
													value="{{ old('email') }}" placeholder="Enter Email, CNIC or Admission No">
												<span class="text-danger text-uppercase">{{ $errors->first('email') }}</span>
											</div>
											<div class="col-12">
												<label for="password" class="form-label">Password</label>
												<div class="input-group" id="show_hide_password">
													<input type="password" class="form-control border-end-0 @error('password') is-invalid @enderror" name="password" id="password" placeholder="Enter Password"> <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
												</div>
												<span class="text-danger text-uppercase">{{ $errors->first('password') }}</span>
											</div>
											<div class="col-md-6">
												<div class="form-check form-switch">
													<input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked">
													<label class="form-check-label" for="flexSwitchCheckChecked">Remember Me</label>
												</div>
											</div>
											<div class="col-md-6 text-end"> <a href="{{ route('password.request') }}">Forgot Password ?</a>
											</div>
											<div class="col-12">
												<div class="d-grid">
													<button type="submit" class="btn btn-primary">Login</button>
												</div>
											</div>
											<div class="col-12">
												<div class="text-center ">
													<p class="mb-0">Don't have an account yet? <a href="{{ route('register') }}">Register here</a>
													</p>
												</div>
											</div>
										</form>
									</div>



								</div>
								</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!--end row-->
			</div>
		</div>
	</div>
	<!--end wrapper-->
	<!-- Bootstrap JS -->
	<script src="{{ asset('backend/assets/js/bootstrap.bundle.min.js') }}"></script>
	<!--plugins-->
	<script src="{{ asset('backend/assets/js/jquery.min.js') }}"></script>
	<script src="{{ asset('backend/assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
	<script src="{{ asset('backend/assets/plugins/metismenu/js/metisMenu.min.js') }}"></script>
	<script src="{{ asset('backend/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
	<!--Password show & hide js -->
	<script>
		$(document).ready(function() {
			$("#show_hide_password a").on('click', function(event) {
				event.preventDefault();
				if ($('#show_hide_password input').attr("type") == "text") {
					$('#show_hide_password input').attr('type', 'password');
					$('#show_hide_password i').addClass("bx-hide");
					$('#show_hide_password i').removeClass("bx-show");
				} else if ($('#show_hide_password input').attr("type") == "password") {
					$('#show_hide_password input').attr('type', 'text');
					$('#show_hide_password i').removeClass("bx-hide");
					$('#show_hide_password i').addClass("bx-show");
				}
			});
		});
	</script>
	<!--app JS-->
	<script src="{{ asset('backend/assets/js/app.js') }}"></script>
</body>

</html>