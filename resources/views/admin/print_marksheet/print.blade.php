<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Marksheet - {{ $exam->name }}</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 13px;
            color: #1a1a1a;
            margin: 0;
        }

        .marksheet {
            position: relative;
            width: 100%;
            min-height: 277mm;
            padding: 10mm;
            page-break-after: always;
            @if($template->background_image) background-image: url('{{ asset('uploads/marksheet_template/' . $template->background_image) }}');
            background-size: cover;
            background-position: center;
            @endif
        }

        .marksheet:last-child {
            page-break-after: auto;
        }

        .header-image {
            width: 100%;
            max-height: 200px;
            object-fit: contain;
            margin-bottom: 8px;
        }

        .top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .top-row img {
            max-height: 70px;
            max-width: 90px;
            object-fit: contain;
        }

        .school-name {
            text-align: center;
            font-size: 40px;
            font-weight: bold;
            margin: 0;
        }

        .exam-name {
            text-align: center;
            font-size: 22px;
            margin: 2px 0 0;
        }

        .exam-center {
            text-align: center;
            font-size: 16px;
            color: #555;
            margin: 2px 0 0;
        }

        .school-contact {
            text-align: center;
            font-size: 11px;
            color: #555;
            margin: 2px 0 0;
        }

        .student-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .student-info table td {
            padding: 2px 6px 2px 0;
            vertical-align: top;
        }

        .student-photo {
            width: 90px;
            height: 100px;
            object-fit: cover;
            border: 1px solid #999;
        }

        .body-text {
            margin-bottom: 10px;
            line-height: 1.5;
        }

        table.marks-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table.marks-table th,
        table.marks-table td {
            border: 1px solid #333;
            padding: 5px 8px;
            text-align: center;
            font-size: 12.5px;
        }

        table.marks-table th {
            background: #f0f0f0;
        }

        table.marks-table tfoot td {
            background: #f0f0f0;
        }

        table.summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 12.5px;
        }

        table.summary-table td {
            border: 1px solid #333;
            padding: 5px 8px;
        }

        table.summary-table td.label-cell {
            background: #f0f0f0;
            font-weight: bold;
            white-space: nowrap;
            width: 1%;
        }

        table.summary-table td.value-cell {
            text-align: center;
        }

        table.summary-table td.full-row {
            text-align: left;
        }

        .sign-row {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }

        .sign-block {
            text-align: center;
            width: 30%;
        }

        .sign-block img {
            max-height: 45px;
            object-fit: contain;
        }

        .sign-line {
            border-top: 1px solid #333;
            margin-top: 30px;
            padding-top: 4px;
            font-size: 12px;
        }

        .footer-text {
            text-align: center;
            font-size: 11px;
            color: #555;
            margin-top: 20px;
        }
    </style>
</head>


<body>

    @php
    if (!function_exists('numberToWordsMarksheet')) {
    function numberToWordsMarksheet($num) {
    $num = (int) $num;
    if ($num == 0) return 'Zero';
    $ones = ['', 'One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten',
    'Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
    $tens = ['', '', 'Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
    $words = '';
    if (intdiv($num, 1000) >= 1) {
    $words .= numberToWordsMarksheet(intdiv($num, 1000)) . ' Thousand ';
    $num %= 1000;
    }
    if (intdiv($num, 100) >= 1) {
    $words .= $ones[intdiv($num, 100)] . ' Hundred ';
    $num %= 100;
    }
    if ($num > 0) {
    if (!empty($words)) $words .= 'And ';
    $words .= $num < 20 ? $ones[$num] : $tens[intdiv($num, 10)] . (($num % 10) ? '-' . $ones[$num % 10] : '' );
        }
        return trim($words);
        }
        }
        @endphp


        @foreach($sheets as $sheet)
        @php
        $enrollment=$sheet['enrollment'];
        $student=$sheet['student'];
        $result=$sheet['result'];
        $subjects=$sheet['subjects'];
        @endphp
        <div class="marksheet">

        @if($template->header_image)
        <img src="{{ asset('uploads/marksheet_template/' . $template->header_image) }}" class="header-image">
        @endif

        <div class="top-row">
            <!-- YE NICHY WALA CODE MENE HIDE KR DIA HA SIDE-LOGO SHOW HOTA HA IS SE -->
            <!-- @if($siteSetting->logo)
            <img src="{{ asset('uploads/site_setting/' . $siteSetting->logo) }}">
            @else
            <div style="width:90px;"></div>
            @endif -->

            <div>
                <p class="school-name">{{ $siteSetting->school_name }}</p>
                <p class="exam-name">{{ $template->exam_name ?? $exam->name }}</p>
                @if($template->exam_center)
                <p class="exam-center">{{ $template->exam_center }}</p>
                @endif
                @if($siteSetting->address || $siteSetting->phone || $siteSetting->email)
                <p class="school-contact">
                    {{ $siteSetting->address }}
                    @if($siteSetting->phone) | Ph: {{ $siteSetting->phone }} @endif
                    @if($siteSetting->email) | {{ $siteSetting->email }} @endif
                </p>
                @endif
            </div>

            @if($template->right_logo)
            <img src="{{ asset('uploads/marksheet_template/' . $template->right_logo) }}">
            @else
            <div style="width:90px;"></div>
            @endif
        </div>

        <div class="student-info">
            <table>
                @if($template->show_name)
                <tr>
                    <td><strong>Name:</strong></td>
                    <td>{{ $student->full_name ?? '-' }}</td>
                </tr>
                @endif
                @if($template->show_father_name)
                <tr>
                    <td><strong>Father Name:</strong></td>
                    <td>{{ $student->parent->father_name ?? '-' }}</td>
                </tr>
                @endif
                @if($template->show_mother_name)
                <tr>
                    <td><strong>Mother Name:</strong></td>
                    <td>{{ $student->mother_name ?? '-' }}</td>
                </tr>
                @endif
                @if($template->show_admission_no)
                <tr>
                    <td><strong>Admission No:</strong></td>
                    <td>{{ $student->admission_no ?? '-' }}</td>
                </tr>
                @endif
                @if($template->show_roll_number)
                <tr>
                    <td><strong>Roll Number:</strong></td>
                    <td>{{ $enrollment->roll_no ?? '-' }}</td>
                </tr>
                @endif
                @if($template->show_class)
                <tr>
                    <td><strong>Class:</strong></td>
                    <td>{{ $enrollment->schoolClass->name ?? '-' }}</td>
                </tr>
                @endif
                @if($template->show_section)
                <tr>
                    <td><strong>Section:</strong></td>
                    <td>{{ $enrollment->section->name ?? '-' }}</td>
                </tr>
                @endif
                @if($template->show_dob)
                <tr>
                    <td><strong>Date Of Birth:</strong></td>
                    <td>{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d-m-Y') : '-' }}</td>
                </tr>
                @endif
                @if($template->show_exam_session)
                <tr>
                    <td><strong>Exam Session:</strong></td>
                    <td>{{ $exam->academicYear->name ?? '-' }}</td>
                </tr>
                @endif
            </table>

            @if($template->show_photo)
            <img src="{{ $student->photo ? asset('uploads/students/' . $student->photo) : asset('assets/img/default-avatar.png') }}" class="student-photo">
            @endif
        </div>

        @if($sheet['body_text'])
        <div class="body-text">{{ $sheet['body_text'] }}</div>
        @endif

        <table class="marks-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Total Marks</th>
                    <th>Obtained Marks</th>
                    @if($template->show_remark)
                    <th>Remark</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @php $colspan = $template->show_remark ? 4 : 3; @endphp
                @forelse($subjects as $mark)
                @php
                $totalMarks = \App\Models\ExamSchedule::where('exam_id', $exam->id)
                ->where('class_id', $enrollment->class_id)
                ->where('subject_id', $mark->subject_id)
                ->value('max_marks');
                @endphp
                <tr>
                    <td>{{ $mark->subject->name ?? '-' }}</td>
                    <td>{{ $totalMarks ?? '-' }}</td>
                    <td>{{ $mark->marks_obtained ?? '-' }}</td>
                    @if($template->show_remark)
                    <td>{{ $loop->first ? ($result->grade->remark ?? '-') : '' }}</td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $colspan }}" class="text-center">No marks recorded.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td><strong>Total</strong></td>
                    <td><strong>{{ $result->total_marks ?? '-' }}</strong></td>
                    <td><strong>{{ $result->obtained_marks ?? '-' }}</strong></td>
                    @if($template->show_remark)
                    <td>-</td>
                    @endif
                </tr>
            </tfoot>
        </table>

        <table class="summary-table">
            <tr>
                <td class="label-cell">Marks In Words</td>
                <td class="value-cell">{{ $result->obtained_marks ? strtoupper(numberToWordsMarksheet($result->obtained_marks)) : '-' }}</td>
                <td class="label-cell">Percentage</td>
                <td class="value-cell">{{ $result->percentage ?? '-' }}%</td>
                <td class="label-cell">Grade</td>
                <td class="value-cell">{{ $result->grade->grade_name ?? '-' }}</td>
                <td class="label-cell">Status</td>
                <td class="value-cell">{{ $result->status ?? '-' }}</td>
            </tr>
        </table>

        <div class="sign-row">
            <div class="sign-block">
                @if($template->left_sign)
                <img src="{{ asset('uploads/marksheet_template/' . $template->left_sign) }}">
                @endif
                <div class="sign-line">Class Teacher</div>
            </div>
            <div class="sign-block">
                @if($template->middle_sign)
                <img src="{{ asset('uploads/marksheet_template/' . $template->middle_sign) }}">
                @endif
                <div class="sign-line">Examiner</div>
            </div>
            <div class="sign-block">
                @if($template->right_sign)
                <img src="{{ asset('uploads/marksheet_template/' . $template->right_sign) }}">
                @endif
                <div class="sign-line">Principal</div>
            </div>
        </div>

        @if($template->footer_text)
        <div class="footer-text">{{ $template->footer_text }}</div>
        @endif
        @if($template->printing_date)
        <div class="footer-text">Printed on: {{ \Carbon\Carbon::parse($template->printing_date)->format('d-m-Y') }}</div>
        @endif

        </div>
        @endforeach

        <script>
            window.onload = function() {
                window.print();
            };
        </script>

</body>

</html>