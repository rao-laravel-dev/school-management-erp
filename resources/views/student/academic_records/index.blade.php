@extends($current_layout)
@section('content')
<x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => route('student.dashboard')],
    ['label' => 'My Profile', 'url' => '#'],
    ['label' => 'Academic Records', 'url' => '']
]" />

@php
    // Same status mapping as the admin student list
    $stStatus = $student->status;
    $stClass = $stStatus == 1 ? 'success' : ($stStatus == 2 ? 'warning' : 'danger');
    $stText = $stStatus == 1 ? 'Active' : ($stStatus == 2 ? 'Pending' : 'Inactive');

    $enrollStatusMap = [
        0 => ['Inactive', 'danger'],
        1 => ['Active', 'success'],
        2 => ['Promoted', 'primary'],
        3 => ['Detained', 'warning'],
        4 => ['Pending Approval', 'secondary'],
    ];
    $enrollmentsById = $enrollments->keyBy('id');
@endphp

{{-- Admission details --}}
<div class="card">
    <div class="card-header">
        <h5 class="fw-semi-bold text-primary mb-0">Admission Details</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <span class="text-muted small d-block">Admission No</span>
                <span class="fw-semibold">{{ $student->admission_no ?: '-' }}</span>
            </div>
            <div class="col-md-3 col-6">
                <span class="text-muted small d-block">Admission Date</span>
                <span class="fw-semibold">{{ $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('d M Y') : '-' }}</span>
            </div>
            <div class="col-md-3 col-6">
                <span class="text-muted small d-block">Category</span>
                <span class="fw-semibold">{{ optional($student->category)->name ?: '-' }}</span>
            </div>
            <div class="col-md-3 col-6">
                <span class="text-muted small d-block">House</span>
                <span class="fw-semibold">{{ optional($student->house)->name ?: '-' }}</span>
            </div>
            <div class="col-md-3 col-6">
                <span class="text-muted small d-block">Status</span>
                <span class="badge bg-{{ $stClass }}">{{ $stText }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Enrollment history (one row per academic year) --}}
<div class="card mt-3">
    <div class="card-header">
        <h5 class="fw-semi-bold text-primary mb-0">Class / Session History</h5>
    </div>
    <div class="card-body">
        @if($enrollments->isEmpty())
            <div class="alert alert-info mb-0"><i class="bx bx-info-circle"></i> No enrollment record found.</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-bordered w-100 mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Session</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Group</th>
                            <th>Roll No</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enrollments as $index => $enrollment)
                        @php $es = $enrollStatusMap[$enrollment->enroll_status] ?? ['Unknown', 'secondary']; @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ optional($enrollment->academicYear)->name ?: '-' }}</td>
                            <td>{{ optional($enrollment->schoolClass)->name ?: '-' }}</td>
                            <td>{{ optional($enrollment->section)->name ?: '-' }}</td>
                            <td>{{ optional($enrollment->group)->name ?: '-' }}</td>
                            <td>{{ $enrollment->roll_no ?: '-' }}</td>
                            <td><span class="badge bg-{{ $es[1] }}-subtle text-{{ $es[1] }}">{{ $es[0] }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Published results across sessions --}}
<div class="card mt-3">
    <div class="card-header">
        <h5 class="fw-semi-bold text-primary mb-0">Published Results</h5>
    </div>
    <div class="card-body">
        @if($results->isEmpty())
            <div class="alert alert-info mb-0"><i class="bx bx-info-circle"></i> No published results available yet.</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-bordered w-100 mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Session</th>
                            <th>Exam</th>
                            <th>Percentage</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $index => $result)
                        @php
                            $isPassed = strcasecmp(trim($result->status ?? ''), 'Pass') == 0;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ optional(optional($enrollmentsById->get($result->enrollment_id))->academicYear)->name ?: '-' }}</td>
                            <td>{{ optional($result->exam)->name ?: '-' }}</td>
                            <td>{{ $result->percentage }}%</td>
                            <td>{{ optional($result->grade)->grade_name ?: '-' }}</td>
                            <td>
                                <span class="badge {{ $isPassed ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $isPassed ? 'Pass' : 'Fail' }}</span>
                            </td>
                            <td>
                                @if($result->enrollment_id == $activeEnrollmentId)
                                    <a href="{{ route('student.results.details', $result->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
