@extends('reception.layout.app')

@section('content')

<!-- Page Title -->
<h3>{{ ucfirst(auth()->user()->roles->first()->name) }} Dashboard</h3>


<!-- Cards Row -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">

    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Total Orders</p>
                        <h4 class="my-1 text-info">4805</h4>
                        <p class="mb-0 font-13">+2.5% from last week</p>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto">
                        <i class='bx bxs-cart'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-danger">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Total Revenue</p>
                        <h4 class="my-1 text-danger">$84,245</h4>
                        <p class="mb-0 font-13">+5.4% from last week</p>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto">
                        <i class='bx bxs-wallet'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Bounce Rate</p>
                        <h4 class="my-1 text-success">34.6%</h4>
                        <p class="mb-0 font-13">-4.5% from last week</p>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                        <i class='bx bxs-bar-chart-alt-2'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Total Customers</p>
                        <h4 class="my-1 text-warning">8.4K</h4>
                        <p class="mb-0 font-13">+8.4% from last week</p>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto">
                        <i class='bx bxs-group'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Charts Section -->
<div class="row">

    <!-- Sales Overview -->
    <div class="col-12 col-lg-8 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header d-flex align-items-center">
                <h6 class="mb-0">Sales Overview</h6>
            </div>

            <div class="card-body">
                <div class="chart-container-1">
                    <canvas id="chart1"></canvas>
                </div>
            </div>

            <div class="row text-center border-top">
                <div class="col">
                    <div class="p-3">
                        <h5>24.15M</h5>
                        <small>Overall Visitor</small>
                    </div>
                </div>
                <div class="col">
                    <div class="p-3">
                        <h5>12:38</h5>
                        <small>Visitor Duration</small>
                    </div>
                </div>
                <div class="col">
                    <div class="p-3">
                        <h5>639.82</h5>
                        <small>Pages/Visit</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trending Products -->
    <div class="col-12 col-lg-4 d-flex">
        <div class="card radius-10 w-100">
            <div class="card-header">
                <h6 class="mb-0">Trending Products</h6>
            </div>

            <div class="card-body">
                <div class="chart-container-2">
                    <canvas id="chart2"></canvas>
                </div>
            </div>

            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    Jeans <span class="badge bg-success">25</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    T-Shirts <span class="badge bg-danger">10</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    Shoes <span class="badge bg-primary">65</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    Lingerie <span class="badge bg-warning">14</span>
                </li>
            </ul>
        </div>
    </div>

</div>

@endsection