<!DOCTYPE html>
<html lang="en">

<head>
    <title>Smart School</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://fonts.googleapis.com/css?family=Work+Sans:100,200,300,400,500,600,700,800,900" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Fredericka+the+Great" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('frontend/assets/css/open-iconic-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/animate.css') }}">

    <link rel="stylesheet" href="{{ asset('frontend/assets/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/magnific-popup.css') }}">

    <link rel="stylesheet" href="{{ asset('frontend/assets/css/aos.css') }}">

    <link rel="stylesheet" href="{{ asset('frontend/assets/css/ionicons.min.css') }}">

    <link rel="stylesheet" href="{{ asset('frontend/assets/css/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/icomoon.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/style.css') }}">

     <!-- start css for progressbar -->
        <style>
    @keyframes bounce {
        0%, 100% { transform: translateY(-15%); animation-timing-function: cubic-bezier(0.8,0,1,1); }
        50% { transform: translateY(0); animation-timing-function: cubic-bezier(0,0,0.2,1); }
    }
    
    progress {
        display: inline-block;
        width: 250px;
        height: 20px;
        padding: 15px 0 0 0;
        margin: 0;
        background: none;
        border: 0;
        border-radius: 15px;
        text-align: left;
        position: relative;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 0.8em;
    }
    progress::-webkit-progress-bar {
        height: 11px;
        width: 210px;
        margin: 0 auto;
        background-color: #e5e7eb; 
        border-radius: 15px;
        box-shadow: 0px 0px 6px #ccc inset;
    }
    progress::-webkit-progress-value {
        display: inline-block;
        float: left;
        height: 11px;
        margin: 0px -10px 0 0;
        background: rgb(27, 236, 80); /* Orange color */
        border-radius: 15px;
        box-shadow: 0px 0px 6px #777 inset;
    }
    progress:after {
        margin: -26px 0 0 -7px;
        padding: 0;
        display: inline-block;
        float: left;
        content: attr(value) '%';
        color: #1b1b18;
        font-weight: bold;
    }
</style>
        <!-- end css for progressbar -->
</head>

<body>

 <!-- start progressbar -->
         <div id="school-loader" style="position: fixed; inset: 0; z-index: 9999; display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: #FDFDFC; transition: opacity 1s ease-out;">
    
    <img src="{{ asset('uploads/images/school-system-logo.png') }}" 
         alt="School System" 
         style="width: 300px; margin-bottom: 20px; animation: bounce 1s infinite;">

    <h1 style="font-size: 2.25rem; font-weight: 800; color: #1b1b18; letter-spacing: 0.05em; margin-bottom: 0.5rem; font-family: sans-serif;">SMART SCHOOL</h1>
    <p style="font-size: 1.25rem; color: #706f6c; margin-bottom: 0.5rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.1em; font-family: sans-serif;">
    Management System
</p>

<span style="display: block; margin-bottom: 30px;">
    <strong>Powered By:</strong> webdevelopersacademy.com
</span>

    <progress id="progressBar" max="100" value="0"></progress>
</div>
        <!-- end progressbar -->
    <div class="kiddos-container-fluid" style="max-width: 100% !important; padding: 0 !important;">
        <div class="py-2 bg-primary">
            <div class="container">
                <div class="row no-gutters d-flex align-items-start align-items-center px-3 px-md-0">
                    <div class="col-lg-12 d-block">
                        <div class="row d-flex">
                            <div class="col-md-5 pr-4 d-flex topper align-items-center">
                                <div class="icon bg-fifth mr-2 d-flex justify-content-center align-items-center"><span class="icon-map"></span></div>
                                <span class="text">Plot D-16, Block 13-D 1, Gulshan-e-Iqbal, Karachi 73500</span>
                            </div>
                            <div class="col-md pr-4 d-flex topper align-items-center">
                                <div class="icon bg-secondary mr-2 d-flex justify-content-center align-items-center"><span class="icon-paper-plane"></span></div>
                                <span class="text">webdevelopersacademy@gmail.com</span>
                            </div>
                            <div class="col-md pr-4 d-flex topper align-items-center">
                                <div class="icon bg-tertiary mr-2 d-flex justify-content-center align-items-center"><span class="icon-phone2"></span></div>
                                <span class="text">+92 335 2977902</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- start header -->
        @include('frontend.home.body.header')
        <!-- end header -->

        <!-- start carousel -->
        @include('frontend.home.body.carousel')
        <!-- end carousel -->

        <div class="page-wrapper">
            <div class="page-content">
                @yield('content')
            </div>
        </div>

        @include('frontend.home.body.footer')





        <script src="{{ asset('frontend/assets/js/jquery.min.js') }}"></script>
        <script src="{{ asset('frontend/assets/js/jquery-migrate-3.0.1.min.js') }}"></script>
        <script src="{{ asset('frontend/assets/js/popper.min.js') }}"></script>
        <script src="{{ asset('frontend/assets/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('frontend/assets/js/jquery.easing.1.3.js') }}"></script>
        <script src="{{ asset('frontend/assets/js/jquery.waypoints.min.js') }}"></script>
        <script src="{{ asset('frontend/assets/js/jquery.stellar.min.js') }}"></script>
        <script src="{{ asset('frontend/assets/js/owl.carousel.min.js') }}"></script>
        <script src="{{ asset('frontend/assets/js/jquery.magnific-popup.min.js') }}"></script>
        <script src="{{ asset('frontend/assets/js/aos.js') }}"></script>
        <script src="{{ asset('frontend/assets/js/jquery.animateNumber.min.js') }}"></script>
        <script src="{{ asset('frontend/assets/js/scrollax.min.js') }}"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
        <script src="{{ asset('frontend/assets/js/google-map.js') }}"></script>
        <script src="{{ asset('frontend/assets/js/main.js') }}"></script>
    </div>

      <!-- start progressbar script -->
     <script>
    document.addEventListener("DOMContentLoaded", function() {
        const loader = document.getElementById('school-loader');
        const progressBar = document.getElementById('progressBar');
        
        if (!loader || !progressBar) return;

        let currentVal = 0;
        
        const interval = setInterval(() => {
            currentVal += 1; 
            
            if (currentVal >= 100) {
                currentVal = 100;
                clearInterval(interval); 
                
                loader.style.opacity = '0';
                loader.style.pointerEvents = 'none'; 
                
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 1000);
            }
            
            progressBar.value = currentVal;
            progressBar.setAttribute('value', currentVal);
            
        }, 20); 
    });
</script>
        <!-- end progressbar script -->

</body>

</html>