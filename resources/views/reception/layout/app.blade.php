<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Cache Fix -->
	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="0" />

	<!-- CSRF -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<!-- Favicon -->
	<link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png') }}" type="image/png"/>

	<!-- Google Fonts -->
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">

	<!-- Plugins CSS -->
	<link href="{{ asset('backend/assets/plugins/vectormap/jquery-jvectormap-2.0.2.css') }}" rel="stylesheet"/>
	<link href="{{ asset('backend/assets/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet"/>
	<link href="{{ asset('backend/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet"/>
	<link href="{{ asset('backend/assets/plugins/metismenu/css/metisMenu.min.css') }}" rel="stylesheet"/>
	<link href="{{ asset('backend/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet"/>
	<link href="{{ asset('backend/assets/plugins/input-tags/css/tagsinput.css') }}" rel="stylesheet"/>

	<!-- Loader -->
	<link href="{{ asset('backend/assets/css/pace.min.css') }}" rel="stylesheet"/>
	<script src="{{ asset('backend/assets/js/pace.min.js') }}"></script>

	<!-- Bootstrap CSS -->
	<link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/bootstrap-extended.css') }}" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/app.css') }}" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/icons.css') }}" rel="stylesheet">

	<!-- Theme -->
	<link href="{{ asset('backend/assets/css/dark-theme.css') }}" rel="stylesheet"/>
	<link href="{{ asset('backend/assets/css/semi-dark.css') }}" rel="stylesheet"/>
	<link href="{{ asset('backend/assets/css/header-colors.css') }}" rel="stylesheet"/>

	<!-- 🔥 Toastr CSS -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"/>

	<!-- 🔥 SweetAlert2 CSS -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

	<!-- jQuery (Required) -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

	<title>{{ auth()->user()->getRoleNames()->first() ?? 'Dashboard' }} | SmartSchool</title>
	
</head>

<body>
<div class="wrapper">

	@include('reception.body.sidebar')
	@include('reception.body.header')

	<!-- Page Wrapper -->
	<div class="page-wrapper">
		<div class="page-content">
			@yield('content')
		</div>
	</div>

	<!-- Overlay -->
	<div class="overlay toggle-icon"></div>

	<!-- Back To Top -->
	<a href="javascript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>

	@include('reception.body.footer')

</div>

<!-- Search Modal -->
<div class="modal" id="SearchModal" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down">
		<div class="modal-content">
			<div class="modal-header gap-2">
				<div class="position-relative popup-search w-100">
					<input class="form-control form-control-lg ps-5 border border-3 border-primary" type="search" placeholder="Search">
					<span class="position-absolute top-50 search-show ms-3 translate-middle-y start-0 fs-4">
						<i class='bx bx-search'></i>
					</span>
				</div>
				<button type="button" class="btn-close d-md-none" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<div class="search-list">Search content...</div>
			</div>
		</div>
	</div>
</div>

<!-- JS (ORDER VERY IMPORTANT ⚠️) -->

<!-- jQuery -->
<script src="{{ asset('backend/assets/js/jquery.min.js') }}"></script>

<!-- Bootstrap -->
<script src="{{ asset('backend/assets/js/bootstrap.bundle.min.js') }}"></script>

<!-- Plugins -->
<script src="{{ asset('backend/assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
<script src="{{ asset('backend/assets/plugins/metismenu/js/metisMenu.min.js') }}"></script>
<script src="{{ asset('backend/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>

<!-- Datatable -->
<script src="{{ asset('backend/assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>

<!-- Charts + Vector -->
<script src="{{ asset('backend/assets/plugins/chartjs/js/chart.js') }}"></script>
<script src="{{ asset('backend/assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js') }}"></script>
<script src="{{ asset('backend/assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js') }}"></script>

<!-- Editors -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<!-- Axios -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Validation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>

<!-- App JS -->
<script src="{{ asset('backend/assets/js/app.js') }}"></script>

<!-- ✅ Datatable Init -->
<script>
$(document).ready(function() {
	if ($('#example').length) {
		$('#example').DataTable();
	}
});
</script>

<!-- ✅ Toastr Flash Message -->
<script>
@if(Session::has('message'))
	var type = "{{ Session::get('alert-type','info') }}";

	switch(type){
		case 'info':
			toastr.info("{{ Session::get('message') }}");
			break;
		case 'success':
			toastr.success("{{ Session::get('message') }}");
			break;
		case 'warning':
			toastr.warning("{{ Session::get('message') }}");
			break;
		case 'error':
			toastr.error("{{ Session::get('message') }}");
			break;
	}
@endif
</script>

<!-- ✅ CKEditor Init -->
<script>
if(document.querySelector('#editor')){
	ClassicEditor
		.create(document.querySelector('#editor'))
		.catch(error => console.error(error));
}
</script>

<!-- Custom Scripts -->
@stack('scripts')

</body>
</html>