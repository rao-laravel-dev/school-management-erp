{{-- Shared middle part of one marksheet for the Modern and Formal designs: student block, body text, marks table, summary.
     Needs $template, $exam, $enrollment, $student, $result, $subjects, $sheet --}}
@php
    $fields = [];
    if ($template->show_name) { $fields[] = ['Name', $student->full_name ?? '-']; }
    if ($template->show_father_name) { $fields[] = ['Father Name', optional($student->parent)->father_name ?: '-']; }
    if ($template->show_mother_name) { $fields[] = ['Mother Name', optional($student->parent)->mother_name ?: '-']; }
    if ($template->show_admission_no) { $fields[] = ['Admission No', $student->admission_no ?? '-']; }
    if ($template->show_roll_number) { $fields[] = ['Roll Number', $enrollment->roll_no ?? '-']; }
    if ($template->show_class) { $fields[] = ['Class', $enrollment->schoolClass->name ?? '-']; }
    if ($template->show_section) { $fields[] = ['Section', $enrollment->section->name ?? '-']; }
    if ($template->show_dob) { $fields[] = ['Date Of Birth', $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d-m-Y') : '-']; }
    if ($template->show_exam_session) { $fields[] = ['Exam Session', $exam->academicYear->name ?? '-']; }
    if ($template->show_division) { $fields[] = ['Division', \App\Services\PrintDesignService::division($result->percentage ?? null, $result->status ?? null)]; }
    if ($template->show_rank) { $fields[] = ['Rank', $result->position ?? '-']; }
@endphp

@if(count($fields) || $template->show_photo)
<div class="ep-info">
    <div class="ep-info-grid">
        @foreach($fields as $f)
        <div class="ep-field"><span class="lbl">{{ $f[0] }}:</span><span class="val">{{ $f[1] }}</span></div>
        @endforeach
    </div>
    @if($template->show_photo)
    <img class="ep-photo" src="{{ $student->photo_url }}" alt="">
    @endif
</div>
@endif

@if($sheet['body_text'])
<div class="ep-body-text">{{ $sheet['body_text'] }}</div>
@endif

@php
    $grades = $sheet['grades'] ?? collect();
    $details = $template->show_subject_details;
    $colCount = 3 + ($details ? 4 : 0) + ($template->show_remark ? 1 : 0);
    $wordsText = ($result->obtained_marks ?? null) ? strtoupper(\App\Services\PrintDesignService::numberToWords($result->obtained_marks)) : '-';
    $position = $result->position ?? null;
    $totalStudents = $sheet['total_students'] ?? null;
@endphp
<table class="ep-table">
    <thead>
        <tr>
            <th class="l">Subject</th>
            <th>Total Marks</th>
            @if($details)
            <th>Pass Marks</th>
            @endif
            <th>Obtained Marks</th>
            @if($details)
            <th>%</th>
            <th>Grade</th>
            <th>Pass/Fail</th>
            @endif
            @if($template->show_remark)
            <th>Remark</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @forelse($subjects as $mark)
        @php
            // pass marks come from the exam schedule ('-' when the subject is not scheduled); grade from the exam's marks_grades
            $max = is_numeric($mark->max_marks ?? null) ? (float) $mark->max_marks : null;
            $pass = is_numeric($mark->passing_marks ?? null) ? (float) $mark->passing_marks : null;
            $got = is_numeric($mark->marks_obtained ?? null) ? (float) $mark->marks_obtained : null;
            $pct = ($max && $got !== null) ? round($got / $max * 100, 2) : null;
            $failed = ($pass !== null && $got !== null) ? $got < $pass : null;
        @endphp
        <tr>
            <td class="l">{{ $mark->subject->name ?? '-' }}</td>
            <td>{{ $mark->max_marks ?? '-' }}</td>
            @if($details)
            <td>{{ $mark->passing_marks ?? '-' }}</td>
            @endif
            <td>{{ $mark->marks_obtained ?? '-' }}</td>
            @if($details)
            <td>{{ $pct !== null ? $pct . '%' : '-' }}</td>
            <td>{{ $pct !== null ? \App\Services\PrintDesignService::gradeFor($pct, $grades) : '-' }}</td>
            <td class="{{ $failed ? 'ep-fail' : '' }}">{{ $failed === null ? '-' : ($failed ? 'Fail' : 'Pass') }}</td>
            @endif
            @if($template->show_remark)
            <td>{{ $loop->first ? ($result->grade->remark ?? '-') : '' }}</td>
            @endif
        </tr>
        @empty
        <tr>
            {{-- old: colspan = show_remark ? 4 : 3 --}}
            <td colspan="{{ $colCount }}">No marks recorded.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td class="l">Total</td>
            <td>{{ $result->total_marks ?? '-' }}</td>
            @if($details)
            <td>-</td>
            @endif
            <td>{{ $result->obtained_marks ?? '-' }}</td>
            @if($details)
            <td>{{ isset($result->percentage) ? $result->percentage . '%' : '-' }}</td>
            <td>{{ $result->grade->grade_name ?? '-' }}</td>
            <td>-</td>
            @endif
            @if($template->show_remark)
            <td>-</td>
            @endif
        </tr>
    </tfoot>
</table>

<div class="ep-lower {{ ($template->show_summary_boxes && $template->show_grade_scale && $grades->count()) ? 'two' : '' }}">
@if($template->show_summary_boxes)
<div class="ep-words"><strong>Marks in words:</strong> {{ $wordsText }}</div>
<div class="ep-summary boxes">
    <div><span class="k">Percentage</span><span class="v">{{ isset($result->percentage) ? $result->percentage . '%' : '-' }}</span></div>
    <div><span class="k">Grade</span><span class="v">{{ $result->grade->grade_name ?? '-' }}</span></div>
    <div><span class="k">Result</span><span class="v">{{ $result->status ?? '-' }}</span></div>
    <div><span class="k">Position</span><span class="v">{{ $position ? $position . ($totalStudents ? ' / ' . $totalStudents : '') : '-' }}</span></div>
</div>
@else
<div class="ep-summary">
    <div><span class="k">Marks In Words</span><span class="v">{{ $wordsText }}</span></div>
    <div><span class="k">Percentage</span><span class="v">{{ $result->percentage ?? '-' }}%</span></div>
    <div><span class="k">Grade</span><span class="v">{{ $result->grade->grade_name ?? '-' }}</span></div>
    <div><span class="k">Status</span><span class="v">{{ $result->status ?? '-' }}</span></div>
</div>
@endif

@if($template->show_grade_scale && $grades->count())
<table class="ep-grades">
    <tr>
        <th class="l">Grade</th>
        @foreach($grades as $g)
        <td>{{ $g->grade_name }}</td>
        @endforeach
    </tr>
    <tr>
        <th class="l">Marks %</th>
        @foreach($grades as $g)
        <td>{{ (float) $g->min_percentage }}-{{ (float) $g->max_percentage }}</td>
        @endforeach
    </tr>
</table>
@endif
</div>
