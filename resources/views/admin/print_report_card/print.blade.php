<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Card</title>
    <style>
        @page { size: A4; margin: 12mm; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 13px; color: #212529; }
        .sheet { page-break-after: always; position: relative; min-height: 270mm; padding: 10px; box-sizing: border-box; }
        .sheet:last-child { page-break-after: auto; }
        .sheet-bg { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0.15; z-index: 0; }
        .sheet-content { position: relative; z-index: 1; }

        .header { text-align: center; margin-bottom: 10px; }
        .header img.header-img { max-height: 60px; margin-bottom: 4px; }
        .school-name { font-size: 22px; font-weight: bold; margin: 0; }
        .school-meta { font-size: 12px; color: #444; margin: 2px 0 0; }

        .student-info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .student-info-table td { padding: 3px 6px; font-size: 13px; }
        .student-info-table td.label { font-weight: bold; width: 110px; }
        .student-photo { width: 80px; height: 90px; object-fit: cover; border: 1px solid #999; float: right; }

        .body-text { margin: 10px 0; text-align: justify; }

        table.marks-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.marks-table th, table.marks-table td { border: 1px solid #666; padding: 4px 6px; font-size: 12px; text-align: center; }
        table.marks-table th { background: #f0f0f0; }

        .section-title { font-weight: bold; font-size: 14px; margin: 10px 0 4px; border-bottom: 1px solid #999; }

        .summary-row { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 8px; }
        .summary-row .box { font-size: 13px; }

        .signatures { display: flex; justify-content: space-between; margin-top: 40px; }
        .signatures div { text-align: center; width: 30%; }
        .signatures img { max-height: 45px; display: block; margin: 0 auto 4px; }
        .signatures .line { border-top: 1px solid #333; margin-top: 30px; padding-top: 4px; font-size: 12px; }

        .footer-text { text-align: center; font-size: 11px; color: #666; margin-top: 16px; }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print text-end p-2">
    <button onclick="window.print()">Print</button>
</div>

@foreach($sheets as $sheet)
    @php
        $enrollment = $sheet['enrollment'];
        $student    = $enrollment->student;
    @endphp
    <div class="sheet">

        @if($template->background_image)
            <img class="sheet-bg" src="{{ asset('uploads/report_card_template/' . $template->background_image) }}">
        @endif

        <div class="sheet-content">

            {{-- Header: always from Site Settings, never from template --}}
            <div class="header">
                @if($template->header_image)
                    <img class="header-img" src="{{ asset('uploads/report_card_template/' . $template->header_image) }}">
                @elseif($siteSetting?->logo)
                    <img class="header-img" src="{{ asset('uploads/site_settings/' . $siteSetting->logo) }}">
                @endif
                <p class="school-name">{{ $siteSetting->school_name ?? '' }}</p>
                <p class="school-meta">
                    {{ $siteSetting->address ?? '' }}
                    @if($siteSetting?->phone) &nbsp;|&nbsp; {{ $siteSetting->phone }} @endif
                    @if($siteSetting?->email) &nbsp;|&nbsp; {{ $siteSetting->email }} @endif
                </p>
                <h5 class="mt-2 mb-0">Report Card — {{ $exam->name }}</h5>
            </div>

            {{-- Student Info --}}
            <table class="student-info-table">
                <tr>
                    <td colspan="4">
                        @if($template->show_photo && $student?->photo)
                            <img class="student-photo" src="{{ $student->photo_url }}">
                        @endif

                        @if($template->show_name)
                            <div><span class="label" style="font-weight:bold;">Name:</span> {{ $student->name ?? '' }}</div>
                        @endif
                        @if($template->show_father_name)
                            <div><span class="label" style="font-weight:bold;">Father Name:</span> {{ $student->father_name ?? '' }}</div>
                        @endif
                        @if($template->show_mother_name)
                            <div><span class="label" style="font-weight:bold;">Mother Name:</span> {{ $student->mother_name ?? '' }}</div>
                        @endif
                        @if($template->show_admission_no)
                            <div><span class="label" style="font-weight:bold;">Admission No:</span> {{ $student->admission_no ?? '' }}</div>
                        @endif
                        @if($template->show_roll_number)
                            <div><span class="label" style="font-weight:bold;">Roll No:</span> {{ $enrollment->roll_no ?? '' }}</div>
                        @endif
                        @if($template->show_class)
                            <div><span class="label" style="font-weight:bold;">Class:</span> {{ $enrollment->schoolClass->name ?? '' }}</div>
                        @endif
                        @if($template->show_section)
                            <div><span class="label" style="font-weight:bold;">Section:</span> {{ $enrollment->section->name ?? '' }}</div>
                        @endif
                        @if($template->show_dob)
                            <div><span class="label" style="font-weight:bold;">Date of Birth:</span> {{ $student->dob ?? '' }}</div>
                        @endif
                    </td>
                </tr>
            </table>

            {{-- Body Text (placeholders already replaced in controller) --}}
            @if($sheet['bodyText'])
                <p class="body-text">{{ $sheet['bodyText'] }}</p>
            @endif

            {{-- Marks & Grade — current exam only --}}
            <div class="section-title">Academic Performance</div>
            <table class="marks-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Marks Obtained</th>
                        <th>Total Marks</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sheet['subjectMarks'] as $mark)
                        <tr>
                            <td>{{ $mark->subject->name ?? '' }}</td>
                            <td>{{ $mark->marks_obtained ?? '-' }}</td>
                            <td>{{ $mark->total_marks ?? '-' }}</td>
                            <td>{{ $mark->grade->grade ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No subject marks found.</td></tr>
                    @endforelse
                </tbody>
                @if($sheet['result'])
                    <tfoot>
                        <tr>
                            <th>Overall</th>
                            <th>{{ $sheet['result']->obtained_marks ?? '-' }}</th>
                            <th>{{ $sheet['result']->total_marks ?? '-' }}</th>
                            <th>{{ $sheet['result']->grade->grade ?? '-' }}</th>
                        </tr>
                    </tfoot>
                @endif
            </table>

            {{-- Attendance Summary — cumulative session-start to this exam --}}
            @if($template->show_attendance_summary)
                <div class="section-title">Attendance Summary</div>
                <div class="summary-row">
                    <div class="box"><strong>Period:</strong> {{ $sheet['attendance']['from']->format('d M Y') }} — {{ $sheet['attendance']['to']->format('d M Y') }}</div>
                    <div class="box"><strong>Total Days:</strong> {{ $sheet['attendance']['total_days'] }}</div>
                    <div class="box"><strong>Present:</strong> {{ $sheet['attendance']['present'] }}</div>
                    <div class="box"><strong>Percentage:</strong> {{ $sheet['attendance']['percentage'] }}%</div>
                </div>
            @endif

            {{-- Co-Curricular --}}
            @if($template->show_co_curricular)
                @php $coCurricular = $sheet['skillMarks']->filter(fn($s) => optional($s->area->category)->type === 'co_curricular'); @endphp
                @if($coCurricular->count())
                    <div class="section-title">Co-Curricular</div>
                    <table class="marks-table">
                        <thead><tr><th>Area</th><th>Grade</th></tr></thead>
                        <tbody>
                            @foreach($coCurricular as $skill)
                                <tr><td>{{ $skill->area->name ?? '' }}</td><td>{{ $skill->grade->grade ?? '-' }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endif

            {{-- Behavioral --}}
            @if($template->show_behavioral)
                @php $behavioral = $sheet['skillMarks']->filter(fn($s) => optional($s->area->category)->type === 'behavioral'); @endphp
                @if($behavioral->count())
                    <div class="section-title">Behavioral Assessment</div>
                    <table class="marks-table">
                        <thead><tr><th>Area</th><th>Grade</th></tr></thead>
                        <tbody>
                            @foreach($behavioral as $skill)
                                <tr><td>{{ $skill->area->name ?? '' }}</td><td>{{ $skill->grade->grade ?? '-' }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endif

            {{-- Class Teacher Remark --}}
            @if($template->show_class_teacher_remark)
                <div class="section-title">Class Teacher's Remark</div>
                <p>{{ $sheet['classTeacherRemark'] ?? '_______________________________________________' }}</p>
            @endif

            {{-- Signatures --}}
            <div class="signatures">
                <div>
                    @if($template->left_sign)
                        <img src="{{ asset('uploads/report_card_template/' . $template->left_sign) }}">
                    @endif
                    <div class="line">Class Teacher</div>
                </div>
                <div>
                    @if($template->middle_sign)
                        <img src="{{ asset('uploads/report_card_template/' . $template->middle_sign) }}">
                    @endif
                    <div class="line">Principal</div>
                </div>
                <div>
                    @if($template->right_sign)
                        <img src="{{ asset('uploads/report_card_template/' . $template->right_sign) }}">
                    @endif
                    <div class="line">Parent / Guardian</div>
                </div>
            </div>

            {{-- Footer Text --}}
            @if($sheet['footerText'])
                <p class="footer-text">{{ $sheet['footerText'] }}</p>
            @endif

        </div>
    </div>
@endforeach

<script>
    window.onload = function() {
        window.print();
    };
</script>

</body>
</html>