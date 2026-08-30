@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Examination', 'url' => '#'],
    ['label' => 'Exam Schedule', 'url' => route('teacher.exam_schedule.index')],
]" />

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semi-bold text-primary">My Exam Schedule</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="examScheduleTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Exam Type</th>
                        <th>Exam</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Max Marks</th>
                        <th>Passing Marks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $index => $schedule)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $schedule->exam->examType->name ?? '-' }}</td>
                        <td>{{ $schedule->exam->name ?? '-' }}</td>
                        <td>{{ $schedule->schoolClass->name ?? '-' }}</td>
                        <td>{{ $schedule->subject->name ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($schedule->date)->format('d M, Y') }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($schedule->time_from)->format('g:i A') }}
                            -
                            {{ \Carbon\Carbon::parse($schedule->time_to)->format('g:i A') }}
                        </td>
                        <td>{{ $schedule->max_marks }}</td>
                        <td>{{ $schedule->passing_marks }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {
        $('#examScheduleTable').DataTable({
            order: [[5, 'asc']],
            language: {
                emptyTable: `<div class="alert alert-danger d-flex align-items-center justify-content-center text-center mb-0" role="alert"><i class='bx bx-error-circle me-2'></i><span>No exam schedule available for your classes yet</span></div>`
            }
        });
    });
</script>
@endpush