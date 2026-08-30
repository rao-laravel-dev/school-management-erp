@extends($current_layout)
@section('content')
<x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => route('student.dashboard')],
    ['label' => 'Academic', 'url' => '#'],
    ['label' => 'Results / Grades', 'url' => '']
]" />

<div class="card">
    <div class="card-header">
        <h5 class="fw-semi-bold text-primary mb-0">My Results</h5>
    </div>
    <div class="card-body">
        @if($results->isEmpty())
            <div class="alert alert-info">
                <i class="bx bx-info-circle"></i> No published results available yet.
            </div>
        @else
            <div class="table-responsive">
                <table id="results-table" class="table table-sm table-bordered w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Exam</th>
                            <th>Exam Type</th>
                            <th>Percentage</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $index => $result)
                        @php
                            $percentage = $result->percentage;
                            if ($percentage >= 80) {
                                $percentageBadge = 'bg-success-subtle text-success';
                            } elseif ($percentage >= 60) {
                                $percentageBadge = 'bg-primary-subtle text-primary';
                            } elseif ($percentage >= 50) {
                                $percentageBadge = 'bg-warning-subtle text-warning';
                            } else {
                                $percentageBadge = 'bg-danger-subtle text-danger';
                            }

                            $gradeName = $result->grade->grade_name ?? '-';
                            $gradeBadge = match (true) {
                                str_starts_with($gradeName, 'A') => 'bg-success-subtle text-success',
                                str_starts_with($gradeName, 'B') => 'bg-primary-subtle text-primary',
                                str_starts_with($gradeName, 'C') => 'bg-info-subtle text-info',
                                str_starts_with($gradeName, 'D') => 'bg-warning-subtle text-warning',
                                str_starts_with($gradeName, 'F') => 'bg-danger-subtle text-danger',
                                default => 'bg-secondary-subtle text-secondary',
                            };

                            // Status check for Pass/Fail string matching
                            $isPassed = strcasecmp(trim($result->status ?? ''), 'Pass') == 0;
                            $statusBadge = $isPassed ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                            $statusText = $isPassed ? 'Pass' : 'Fail';
                        @endphp
                        <tr class="{{ $index === 0 ? 'table-light fw-semi-bold' : '' }}">
                            <td>
                                {{ $index + 1 }}
                                @if($index === 0)
                                    <span class="badge bg-primary-subtle text-primary ms-1">Latest</span>
                                @endif
                            </td>
                            <td>{{ $result->exam->name }}</td>
                            <td>{{ $result->exam->examType->name ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $percentageBadge }} px-3">{{ number_format($percentage, 2) }}%</span>
                            </td>
                            <td>
                                <span class="badge {{ $gradeBadge }} px-3">{{ $gradeName }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $statusBadge }} px-3">{{ $statusText }}</span>
                            </td>
                            <td>
                                <a href="{{ route('student.results.details', $result->id) }}" class="btn btn-outline-warning btn-sm px-3">
                                    View Details
                                </a>
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

@push('scripts')
<script>
    @if($errors->any())
        toastr.error("{{ $errors->first() }}");
    @endif

    $('#results-table').DataTable({
        order: [[0, 'asc']], // keep controller's order (latest exam first)
        columnDefs: [
            { orderable: false, targets: -1 } // Action column not sortable
        ],
        language: {
            emptyTable: '<div class="alert alert-danger d-flex align-items-center mb-0"><i class="bx bx-error-circle me-2"></i> No data available in the table</div>'
        }
    });
</script>
@endpush