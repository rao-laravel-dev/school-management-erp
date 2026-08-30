@extends('student.layout.app')

@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Student Dashboard</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item active">{{ $student->first_name }} {{ $student->last_name }}'s Overview</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Attendance</p>
                        <h4 class="mb-0">{{ $attendancePercentage ?? 'N/A' }}%</h4>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto"><i class='bx bxs-calendar-check'></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-danger">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Fees Due</p>
                        <h4 class="my-1 text-danger">PKR {{ number_format($pendingFees ?? 0) }}</h4>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto"><i class='bx bxs-wallet'></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Active Subjects</p>
                        <h4 class="my-1 text-success">{{ $activeSubjectsCount }}</h4>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto"><i class='bx bxs-book'></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Pending Assignments</p>
                        <h4 class="my-1 text-warning">{{ $pendingAssignments }}</h4>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto"><i class='bx bxs-edit-alt'></i></div>
                </div>
            </div>
        </div>
    </div> 
</div><div class="row">
   <div class="col-12 col-lg-8 d-flex">
      <div class="card radius-10 w-100">
        <div class="card-header"><h6 class="mb-0">Academic Performance Trend</h6></div>
         <div class="card-body">
            <div class="chart-container-1">
                <canvas id="academicChart"></canvas>
            </div>
         </div>
      </div>
   </div>
   <div class="col-12 col-lg-4 d-flex">
       <div class="card radius-10 w-100">
        <div class="card-header"><h6 class="mb-0">Recent Results</h6></div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                @foreach($recentExams as $exam)
                <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center border-top">
                    {{ $exam->name }} 
                    <span class="badge {{ $exam->grade == 'A' ? 'bg-success' : 'bg-primary' }} rounded-pill">{{ $exam->grade }}</span>
                </li>
                @endforeach
            </ul>
        </div>
       </div>
   </div>
</div>@endsection

@push('scripts')
    <script src="{{ asset('backend/assets/plugins/chartjs/js/chart.js') }}"></script>
    <script src="{{ asset('backend/assets/js/student-charts.js') }}"></script> 
@endpush