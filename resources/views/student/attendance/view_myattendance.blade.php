@extends('student.layout.app')

@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Attendance</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">My Attendance</li>
            </ol>
        </nav>
    </div>
</div>

<div class="page-content">
    <div class="container-fluid">
        <!-- 5 Cards Layout -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-5 mb-3">
            <!-- 1. Total Present (Included Present, Late, Half-Day) -->
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small">Total Present</p>
                                <h5 class="my-0 text-success fw-bold">{{ $allAttendance->whereIn('status', [1, 2, 3])->count() }}</h5>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto" style="width: 45px; height: 45px; line-height: 45px; text-align: center;">
                                <i class='bx bxs-check-circle'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 2. Total Absent -->
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-danger shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small">Total Absent</p>
                                <h5 class="my-0 text-danger fw-bold">{{ $allAttendance->where('status', 0)->count() }}</h5>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto" style="width: 45px; height: 45px; line-height: 45px; text-align: center;">
                                <i class='bx bxs-x-circle'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 3. Total Late -->
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-warning shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small">Total Late</p>
                                <h5 class="my-0 text-warning fw-bold">{{ $allAttendance->where('status', 2)->count() }}</h5>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto" style="width: 45px; height: 45px; line-height: 45px; text-align: center;">
                                <i class='bx bxs-time'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 4. Total Half-Day -->
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-info shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small">Total Half-Day</p>
                                <h5 class="my-0 text-info fw-bold">{{ $allAttendance->where('status', 3)->count() }}</h5>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto" style="width: 45px; height: 45px; line-height: 45px; text-align: center;">
                                <i class='bx bxs-hourglass-bottom'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 5. Total Leaves -->
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-secondary shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small">Total Leaves</p>
                                <h5 class="my-0 text-secondary fw-bold">{{ $allAttendance->where('status', 4)->count() }}</h5>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-secondary text-secondary ms-auto" style="width: 45px; height: 45px; line-height: 45px; text-align: center;">
                                <i class='bx bxs-calendar-event'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card radius-10 border-top border-0 border-4 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Attendance Overview: {{ now()->format('F, Y') }}</h4>
                    <div class="small text-muted">
                        <strong>Legend:</strong>
                        <span class="text-success fw-bold">P</span>: Present,
                        <span class="text-warning fw-bold">L</span>: Late,
                        <span class="text-danger fw-bold">A</span>: Absent,
                        <span class="text-info fw-bold">H</span>: Half-Day,
                        <span class="text-secondary fw-bold">F</span>: Leave
                    </div>
                </div>

                <hr>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center align-middle" style="font-size: 0.8rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25px;">Month</th>
                                @for($day = 1; $day <= 31; $day++) <th style="width: 25px;">{{ $day }}</th> @endfor
                                    <th class="bg-success text-white" style="width: 25px;">P</th>
                                    <th class="bg-danger text-white" style="width: 25px;">A</th>
                                    <th class="bg-warning text-white" style="width: 25px;">L</th>
                                    <th class="bg-info text-white" style="width: 25px;">H</th>
                                    <th class="bg-secondary text-white" style="width: 25px;">F</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold bg-light">{{ now()->format('M') }}</td>
                                @for($day = 1; $day <= 31; $day++)
                                    @php
                                    $dateCheck=\Carbon\Carbon::create(now()->year, now()->month, $day);
                                    $isSunday = $dateCheck->isSunday();
                                    @endphp

                                    {{-- Yahan style="background-color: #fff3cd;" ek light yellow shade hai --}}
                                    <td style="{{ $isSunday ? 'background-color: #fff3cd;' : '' }}">
                                        @if($dateCheck->isFuture())
                                        <span class="text-muted">-</span>
                                        @elseif($isSunday)
                                        {{-- Sunday ka text 'text-danger' --}}
                                        <span class="text-danger fw-bold">S</span>
                                        @else
                                        @php $status = $matrix[$day] ?? null; @endphp
                                        @if($status !== null)
                                        <span class="{{ match($status) { 1=>'text-success', 2=>'text-warning', 3=>'text-info', 0=>'text-danger', 4=>'text-secondary', default=>'' } }} fw-bold">
                                            {{ match($status) { 1=>'P', 2=>'L', 3=>'H', 0=>'A', 4=>'F', default=>'' } }}
                                        </span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                        @endif
                                    </td>
                                    @endfor
                                    <!-- Table Footer Stats (Aligned with card logic) -->
                                    <td class="fw-bold text-success">{{ $allAttendance->whereIn('status', [1, 2, 3])->count() }}</td>
                                    <td class="fw-bold text-danger">{{ $allAttendance->where('status', 0)->count() }}</td>
                                    <td class="fw-bold text-warning">{{ $allAttendance->where('status', 2)->count() }}</td>
                                    <td class="fw-bold text-info">{{ $allAttendance->where('status', 3)->count() }}</td>
                                    <td class="fw-bold text-secondary">{{ $allAttendance->where('status', 4)->count() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection