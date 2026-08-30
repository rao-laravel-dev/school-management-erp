@extends('parent.layout.app')

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-5">
    <div class="breadcrumb-title pe-3">Parent Portal</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $student->full_name }}</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto d-flex align-items-center gap-2">
        @if(auth()->user()->parentProfile && auth()->user()->parentProfile->students->count() > 1)
            <div class="dropdown">
                <button class="btn btn-outline-primary btn-sm dropdown-toggle px-3 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-refresh"></i> Switch Student
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 radius-10">
                    <li><h6 class="dropdown-header text-secondary">Select Child</h6></li>
                    @foreach(auth()->user()->parentProfile->students as $sibling)
                        @if($sibling->id != $student->id)
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('parent.students.dashboard', $sibling->id) }}">
                                    <img src="{{ $sibling->photo_url ?? asset('assets/images/avatars/avatar-1.png') }}" class="rounded-circle border" width="25" height="25" style="object-fit: cover;">
                                    <span>{{ $sibling->full_name }}</span>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif
        
        <a href="{{ route('parent.dashboard') }}" class="btn btn-primary btn-sm px-3 shadow-sm"><i class="bx bx-arrow-back"></i> Back to Kids</a>
    </div>
</div>
<style>
    /* Card Container */
    .custom-dashboard-card {
        background-color: #f7f2f9 !important; 
        border: 1px solid #efe8f2 !important;
        border-radius: 25px !important;
        box-shadow: -5px 5px 15px rgba(0, 0, 0, 0.15), 
                    5px 5px 15px rgba(0, 0, 0, 0.15) !important;
    }

    /* Text Styles */
    .text-header-custom { color: #0d1e4c !important; font-weight: 800; }
    .text-label-custom { color: #7b839a !important; font-weight: 600; font-size: 0.9rem; min-width: 80px; }
    .text-value-custom { color: #0d1e4c !important; font-weight: 700; font-size: 0.9rem; }

    /* Custom HR Line style */
    .profile-hr {
        margin: 8px 0;
        border: 0;
        border-top: 2px solid #a0a0a0 !important; /* Thickness 2px aur color darker gray */
        opacity: 1 !important; /* Line ko solid banane ke liye */
    }

    /* Avatar Border */
    .img-avatar-custom {
        border: 3px solid #e0d8e6 !important; 
        border-radius: 20px !important;
    }
</style>

<div class="row mb-4">
    <div class="col-12 col-md-8 col-lg-6 mx-auto">
        <div class="card custom-dashboard-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="mb-4" style="display: inline-block;">
    <h4 class="mb-0 text-header-custom">{{ strtoupper($student->full_name) }}</h4>
    <!-- Dynamic Underline -->
    <div class="bg-info" style="height: 4px; width: 100%; border-radius: 2px; margin-top: 5px;"></div>
</div>
                        
                        <div class="d-flex align-items-start">
                            <span class="text-label-custom me-3">Class & Sec:</span> 
                            <span class="text-value-custom">{{ $student->currentEnrollment->schoolClass->name ?? 'N/A' }} - {{ $student->currentEnrollment->section->name ?? 'N/A' }}</span>
                        </div>
                        <hr class="profile-hr">

                        <div class="d-flex align-items-start">
                            <span class="text-label-custom">SB ID:</span> 
                            <span class="text-value-custom">{{ $student->currentEnrollment->sb_id ?? $student->id }}</span>
                        </div>
                        <hr class="profile-hr">
                        
                        <div class="d-flex align-items-start">
                            <span class="text-label-custom">GRN:</span> 
                            <span class="text-value-custom">{{ $student->currentEnrollment->admission_no ?? $student->admission_no ?? 'N/A' }}</span>
                        </div>
                    </div>
                    
                    <div class="flex-shrink-0 ms-3">
                        <img src="{{ $student->photo_url ?? asset('assets/images/avatars/avatar-1.png') }}" 
                             class="img-avatar-custom"
                             style="width: 90px; height: 100px; object-fit: cover;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Profile Card wali style */
    .custom-dashboard-card {
        background-color: #f7f2f9 !important; 
        border: 1px solid #efe8f2 !important;
        border-radius: 25px !important;
        box-shadow: -5px 5px 15px rgba(0, 0, 0, 0.15), 5px 5px 15px rgba(0, 0, 0, 0.15) !important;
        transition: transform 0.3s ease; /* Card ko bhi smooth banane ke liye */
    }

    /* Module Card ke liye styling */
    .module-card-custom {
        background-color: #f7f2f9 !important; 
        border: 1px solid #efe8f2 !important;
        border-radius: 20px !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); /* Smooth hover animation */
        cursor: pointer;
    }
    
    /* Hover effect - Yahan cards upar uthenge */
    .module-card-custom:hover {
        transform: translateY(-8px); /* Card upar shift hoga */
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12) !important; /* Shadow thodi gehri ho jayegi */
        border-color: #dcdcdc !important;
    }

    /* Custom Title Class */
    .module-title { 
        color: #0d1e4c !important; 
        font-weight: 800 !important;
        font-size: 0.95rem !important;
        margin-top: 5px;
        display: block;
    }
</style>

<!-- 2. EXACT 10 GRID MODULE CARDS (As per Reference Image Grid) -->

<div class="row row-cols-3 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-3 mb-4 justify-content-center nav nav-pills" id="app-modules-grid" role="tablist" style="border: none;">
    
    <div class="col" role="presentation">
        <div class="card h-100 module-card-custom nav-link active text-center p-2 p-sm-3" id="pills-notices-tab" data-bs-toggle="pill" data-bs-target="#pills-notices" role="tab">
            <div class="module-icon-box mx-auto mb-2 text-info" style="background-color: #f0faff; border-radius: 12px; padding: 10px;">
                <i class='bx bx-bell' style="font-size: 24px;"></i>
            </div>
            <span class="module-title">Notices</span>
        </div>
    </div>

    <div class="col" role="presentation">
        <div class="card h-100 module-card-custom nav-link text-center p-2 p-sm-3" id="pills-attendance-tab" data-bs-toggle="pill" data-bs-target="#pills-attendance" role="tab">
            <div class="module-icon-box mx-auto mb-2 text-warning" style="background-color: #fffbf0; border-radius: 12px; padding: 10px;">
                <i class='bx bx-calendar-check' style="font-size: 24px;"></i>
            </div>
            <span class="module-title">Attendance</span>
        </div>
    </div>

    <div class="col" role="presentation">
        <div class="card h-100 module-card-custom nav-link text-center p-2 p-sm-3" id="pills-homework-tab" data-bs-toggle="pill" data-bs-target="#pills-homework" role="tab">
            <div class="module-icon-box mx-auto mb-2 text-success" style="background-color: #f1fbf4; border-radius: 12px; padding: 10px;">
                <i class='bx bx-book-open' style="font-size: 24px;"></i>
            </div>
            <span class="module-title">Homework</span>
        </div>
    </div>

    <div class="col" role="presentation">
        <div class="card h-100 module-card-custom nav-link text-center p-2 p-sm-3" id="pills-calendar-tab" data-bs-toggle="pill" data-bs-target="#pills-calendar" role="tab">
            <div class="module-icon-box mx-auto mb-2 text-danger" style="background-color: #fff5f5; border-radius: 12px; padding: 10px;">
                <i class='bx bx-calendar-event' style="font-size: 24px;"></i>
            </div>
            <span class="module-title">Academic Calendar</span>
        </div>
    </div>

    <div class="col" role="presentation">
        <div class="card h-100 module-card-custom nav-link text-center p-2 p-sm-3" id="pills-voucher-tab" data-bs-toggle="pill" data-bs-target="#pills-voucher" role="tab">
            <div class="module-icon-box mx-auto mb-2 text-primary" style="background-color: #f0f3ff; border-radius: 12px; padding: 10px;">
                <i class='bx bx-file-blank' style="font-size: 24px;"></i>
            </div>
            <span class="module-title">Fee Voucher</span>
        </div>
    </div>

    <div class="col" role="presentation">
        <div class="card h-100 module-card-custom nav-link text-center p-2 p-sm-3" id="pills-finance-tab" data-bs-toggle="pill" data-bs-target="#pills-finance" role="tab">
            <div class="module-icon-box mx-auto mb-2 text-dark" style="background-color: #f4f5f7; border-radius: 12px; padding: 10px;">
                <i class='bx bx-credit-card-front' style="font-size: 24px;"></i>
            </div>
            <span class="module-title">Fee Ledger</span>
        </div>
    </div>

    <div class="col" role="presentation">
        <div class="card h-100 module-card-custom nav-link text-center p-2 p-sm-3" id="pills-timetable-tab" data-bs-toggle="pill" data-bs-target="#pills-timetable" role="tab">
            <div class="module-icon-box mx-auto mb-2" style="color: #8e44ad; background-color: #faf0ff; border-radius: 12px; padding: 10px;">
                <i class='bx bx-time-five' style="font-size: 24px;"></i>
            </div>
            <span class="module-title">Time Table</span>
        </div>
    </div>

    <div class="col" role="presentation">
        <div class="card h-100 module-card-custom nav-link text-center p-2 p-sm-3" id="pills-results-tab" data-bs-toggle="pill" data-bs-target="#pills-results" role="tab">
            <div class="module-icon-box mx-auto mb-2 text-success" style="background-color: #edfbf7; border-radius: 12px; padding: 10px;">
                <i class='bx bx-medal' style="font-size: 24px;"></i>
            </div>
            <span class="module-title">Results</span>
        </div>
    </div>

    <div class="col" role="presentation">
        <div class="card h-100 module-card-custom nav-link text-center p-2 p-sm-3" id="pills-books-tab" data-bs-toggle="pill" data-bs-target="#pills-books" role="tab">
            <div class="module-icon-box mx-auto mb-2" style="color: #e67e22; background-color: #fff9f2; border-radius: 12px; padding: 10px;">
                <i class='bx bx-list-ul' style="font-size: 24px;"></i>
            </div>
            <span class="module-title">Book List</span>
        </div>
    </div>

    <div class="col" role="presentation">
        <div class="card h-100 module-card-custom nav-link text-center p-2 p-sm-3" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" role="tab">
            <div class="module-icon-box mx-auto mb-2 text-danger" style="background-color: #fff3f3; border-radius: 12px; padding: 10px;">
                <i class='bx bx-support' style="font-size: 24px;"></i>
            </div>
            <span class="module-title">Contact Us</span>
        </div>
    </div>

</div>

<hr class="my-4" style="opacity: 0.1;">

<!-- 3. RECORSD TABS VIEW WINDOWS -->
<div class="tab-content" id="pills-tabContent">
    
    <!-- 1. NOTICES CONTENT -->
    <div class="tab-pane fade show active" id="pills-notices" role="tabpanel">
        <div class="card radius-10 shadow-sm border-0">
            <div class="card-header bg-transparent border-0 pt-3">
                <h6 class="mb-0 fw-bold text-dark">Latest School Notices</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info border-0 bg-light-info text-dark">
                    <h5 class="font-weight-bold">Summer Vacation Announcement</h5>
                    <p class="mb-0 small">Dear Parents, the school will remain closed for summer vacations starting next week.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 2. ATTENDANCE CONTENT -->
    <div class="tab-pane fade" id="pills-attendance" role="tabpanel">
        <div class="row">
            <div class="col-12 col-lg-4 mb-3">
                @if($attendanceRecords && $attendanceRecords->isNotEmpty())
                    <div class="card radius-10 shadow-sm border-0">
                        <div class="card-body text-center">
                            <h6 class="fw-bold mb-3 text-start text-dark">Attendance Overview</h6>
                            <div id="attendanceChart"></div>
                        </div>
                    </div>
                @else
                    <div class="card radius-10 shadow-sm border-0">
                        <div class="card-body text-center py-4">
                            <p class="text-muted mb-0">No attendance data logs available.</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-12 col-lg-8">
                <div class="card radius-10 shadow-sm border-0">
                    <div class="card-header bg-transparent border-0 pt-3">
                        <h6 class="mb-0 fw-bold text-dark">Recent Attendance Logs</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($attendanceRecords && $attendanceRecords->isNotEmpty())
                                        @foreach($attendanceRecords as $record)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($record->attendance_date)->format('d M, Y') }}</td>
                                            <td>
                                                @if($record->status == 1) <span class="badge bg-success">Present</span>
                                                @elseif($record->status == 0) <span class="badge bg-danger">Absent</span>
                                                @elseif($record->status == 2) <span class="badge bg-warning text-dark">Late</span> 
                                                @elseif($record->status == 3) <span class="badge bg-info text-dark">Leave</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No records found.</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. HOMEWORK CONTENT -->
    <div class="tab-pane fade" id="pills-homework" role="tabpanel">
        <div class="card radius-10 shadow-sm border-0">
            <div class="card-header bg-transparent border-0 pt-3">
                <h6 class="mb-0 fw-bold text-dark">Pending & Recent Homework</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Subject</th>
                                <th>Assign Date</th>
                                <th>Submission Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold">English Literature</td>
                                <td>12 Jun, 2026</td>
                                <td>18 Jun, 2026</td>
                                <td><span class="badge bg-light-warning text-warning px-3">Pending</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. ACADEMIC CALENDAR CONTENT -->
    <div class="tab-pane fade" id="pills-calendar" role="tabpanel">
        <div class="card radius-10 shadow-sm border-0">
            <div class="card-body p-4">
                <h6>Academic Calendar</h6>
                <p class="text-muted small">Academic timeline & scheduled event details go here.</p>
            </div>
        </div>
    </div>

    <!-- 5. FEE VOUCHER CONTENT -->
    <div class="tab-pane fade" id="pills-voucher" role="tabpanel">
        <div class="card radius-10 shadow-sm border-0">
            <div class="card-body p-4">
                <h6>Download Fee Voucher</h6>
                <p class="text-muted small">Active printable fee challan vouchers are structured here.</p>
            </div>
        </div>
    </div>

    <!-- 6. FEE LEDGER CONTENT -->
    <div class="tab-pane fade" id="pills-finance" role="tabpanel">
        <div class="card radius-10 shadow-sm border-0">
            <div class="card-header bg-transparent border-0 pt-3">
                <h6 class="mb-0 fw-bold text-dark">Fee Ledger History</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice Number</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold text-primary">INV-2026-001</td>
                                <td>PKR 5,000</td>
                                <td><span class="badge bg-success">Paid</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 7. TIME TABLE CONTENT -->
    <div class="tab-pane fade" id="pills-timetable" role="tabpanel">
        <div class="card radius-10 shadow-sm border-0">
            <div class="card-body p-4">
                <h6>Class Time Table</h6>
                <p class="text-muted small">Daily subject lecture timetable schedule list.</p>
            </div>
        </div>
    </div>

    <!-- 8. RESULTS CONTENT -->
    <div class="tab-pane fade" id="pills-results" role="tabpanel">
        <div class="card radius-10 shadow-sm border-0">
            <div class="card-header bg-transparent border-0 pt-3">
                <h6 class="mb-0 fw-bold text-dark">Examination Marks Sheet</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Exam Name</th>
                                <th>Obtained Marks</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Mid Term Examination</td>
                                <td>85 / 100</td>
                                <td><span class="badge bg-light-success text-success px-3">A</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 9. BOOK LIST CONTENT -->
   <div class="tab-pane fade" id="pills-books" role="tabpanel">
    <div class="row row-cols-1 row-cols-lg-2 g-3">
        
        <div class="col-lg-4">
            <div class="card radius-10 shadow-sm border-0 h-100">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="mb-0 fw-bold text-dark">Syllabus Breakdown</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    @if(isset($bookList) && count($bookList) > 0)
                        <div style="width: 100%; max-width: 250px; margin: 0 auto;">
                            <div id="booksChart" style="width: 100%;"></div>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bx bx-pie-chart-alt d-block mb-2" style="font-size: 30px;"></i>
                            No data to chart
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card radius-10 shadow-sm border-0 h-100">
                <div class="card-header bg-transparent border-0 pt-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Class Book List</h6>
                        <p class="text-muted small mb-0">Class: <span class="text-primary fw-bold">{{ $className }}</span></p>
                    </div>
                    <span class="badge bg-light-orange text-orange px-3 py-2 radius-10" style="color: #e67e22; background-color: #fff9f2; font-weight: bold;">
                        Total: {{ count($bookList) }} Books
                    </span>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 60px;" class="text-center">Sr.No</th>
                                    <th>Subject Name</th>
                                    <th>Subject Code</th>
                                    <th class="text-center">Full Marks</th>
                                    <th class="text-center">Pass Marks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($bookList) && count($bookList) > 0)
                                    @foreach($bookList as $index => $item)
                                        <tr>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center justify-content-center bg-light text-dark rounded-circle fw-bold mx-auto" style="width: 28px; height: 28px; font-size: 12px;">
                                                    {{ $index + 1 }}
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <span class="fw-bold text-dark">
                                                    {{ $item->subject->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            
                                            <td>
                                                <span class="badge bg-light text-dark border px-2 py-1">
                                                    {{ $item->subject->subject_code ?? 'N/A' }}
                                                </span>
                                            </td>
                                            
                                            <td class="text-center fw-bold text-secondary">
                                                {{ $item->full_marks }}
                                            </td>
                                            
                                            <td class="text-center">
                                                <span class="badge bg-light-success text-success px-3 py-1.5" style="background-color: #e8f5e9; color: #2e7d32;">
                                                    {{ $book->pass_marks ?? '33' }} Marks
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bx bx-book-open d-block mb-2" style="font-size: 40px; color: #ccc;"></i>
                                            No syllabus or books assigned to this class yet.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

    <!-- 10. CONTACT US CONTENT -->
    <div class="tab-pane fade" id="pills-contact" role="tabpanel">
        <div class="card radius-10 shadow-sm border-0">
            <div class="card-body p-4">
                <h6>Support & Contact Info</h6>
                <p class="mb-1 text-dark"><strong>Email:</strong> support@school.edu.pk</p>
                <p class="text-dark"><strong>Phone:</strong> +92-21-111-222-333</p>
            </div>
        </div>
    </div>

</div>

<!-- RE-STYLED MOBILE SYSTEM LOOK CSS -->
<style>
    .style-layout-data {
        font-family: 'Poppins', sans-serif;
    }
    .border-bottom.white-30 {
        border-color: rgba(0, 0, 0, 0.05) !important;
    }
    .module-app-card {
        cursor: pointer;
        background-color: #ffffff !important;
        border: none !important;
        border-radius: 18px !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .module-icon-box {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-size: 24px;
        transition: all 0.2s ease;
    }
    .module-title {
        font-size: 11px;
        color: #495057 !important;
        font-weight: 600 !important;
    }
    
    /* Perfect active tab matching logic */
    .nav-pills .module-app-card.active {
        background-color: #ffffff !important;
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.08) !important;
        transform: translateY(-4px);
    }
    .nav-pills .module-app-card.active .module-title {
        color: #1a237e !important;
    }
    .module-app-card:hover:not(.active) {
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(0,0,0,0.04);
    }

    @media (max-width: 576px) {
        .module-icon-box {
            width: 44px;
            height: 44px;
            font-size: 20px;
        }
        .module-title {
            font-size: 10px;
        }
    }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        
        // 📊 1. ATTENDANCE DONUT CHART
        var attendanceOptions = {
            series: [{{ $presentCount ?? 0 }}, {{ $absentCount ?? 0 }}, {{ $lateCount ?? 0 }}, {{ $leaveCount ?? 0 }}],
            chart: { type: 'donut', height: 250 },
            labels: ['Present', 'Absent', 'Late', 'Leave'],
            colors: ["#17a08e", "#f41127", "#ffc107", "#0dcaf0"],
            dataLabels: { enabled: true },
    
    // Labels aur alignment ka best configuration
            dataLabels: { enabled: true },
            legend: { 
                position: 'right',
                fontSize: '14px',
                markers: { radius: 50 },
                itemMargin: { horizontal: 10, vertical: 8 }
            }
        };
        var attendanceChart = new ApexCharts(document.querySelector("#attendanceChart"), attendanceOptions);
        attendanceChart.render();

        // 📚 2. BOOKS PIE CHART
        var bookOptions = {
            series: [
                @foreach($bookList as $book) {{ $book->full_marks ?? 100 }}, @endforeach
            ],
            chart: { type: 'pie', height: 250 },
            labels: [
                @foreach($bookList as $book) "{{ $book->subject_name ?? 'Subject' }}", @endforeach
            ],
            legend: { position: 'bottom' }
        };
        var booksChart = new ApexCharts(document.querySelector("#booksChart"), bookOptions);
        booksChart.render();
        
    });
</script>
@endpush