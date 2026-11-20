{{-- Classic marksheet = the original layout, unchanged. Only additions: optional school logo, head signature + name, school stamp (all off for old templates) --}}
<style>
    .marksheet {
        position: relative;
        width: 100%;
        min-height: {{ $orientation === 'landscape' ? '189mm' : '277mm' }};
        padding: 10mm;
        page-break-after: always;
        @if($template->background_image && !str_starts_with((string) $template->imageUrl('background_image'), 'data:')) background-image: url('{{ $template->imageUrl('background_image') }}');
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

    /* "School logo" switch on: bigger logo (24mm tall, 18mm landscape), both sides same width so the school name stays centred. Not used when the switch is off */
    /* old: side-spacer 40mm, logo max 40mm x 24mm (18mm landscape). Now 30mm tall (22mm landscape), width max 45mm (60mm landscape) */
    .top-row.has-logo .side-spacer { width: {{ $orientation === 'landscape' ? '60mm' : '45mm' }} !important; }
    .top-row.has-logo > img.site-logo,
    .top-row.has-logo > img:last-child {
        max-width: {{ $orientation === 'landscape' ? '60mm' : '45mm' }};
        max-height: {{ $orientation === 'landscape' ? '22mm' : '30mm' }};
        flex: none;
        /* equal fixed slots left/right (logo fitted inside, spacer on the empty side) so the name block is centred on the page */
        width: {{ $orientation === 'landscape' ? '60mm' : '45mm' }};
        height: {{ $orientation === 'landscape' ? '22mm' : '30mm' }};
        object-fit: contain;
    }

    /* logo ON only: school name on ONE line (middle box takes the free width, print script shrinks the font, min 12pt). Logo OFF keeps the old wrapping */
    .top-row.has-logo > div:not(.side-spacer) { flex: 1; min-width: 0; }
    .top-row.has-logo .school-name { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

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

    .sign-block img.stamp-img {
        max-height: 70px;
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

{{-- Live preview only: same A4 page box (printable area) as Modern / Formal so the preview scales the same way. Print output is untouched. --}}
@if($isPreview)
<style>
    body.ep-preview { background: #eee; padding: 10px; }
    .cl-preview-page { width: {{ $orientation === 'landscape' ? '277mm' : '190mm' }}; height: {{ $orientation === 'landscape' ? '189mm' : '276mm' }}; margin: 0 auto; overflow: hidden; background: #fff; box-shadow: 0 2px 10px rgba(0, 0, 0, .25); }
</style>
<div class="cl-preview-page">
@endif
@foreach($sheets as $sheet)
@php
    $enrollment = $sheet['enrollment'];
    $student = $sheet['student'];
    $result = $sheet['result'];
    $subjects = $sheet['subjects'];
@endphp
<div class="marksheet">

    @if($template->header_image)
    <img src="{{ $template->imageUrl('header_image') }}" data-preview-img="header_image" class="header-image">
    @endif

    <div class="top-row {{ $branding['logo_url'] ? 'has-logo' : '' }}">
        {{-- old: school logo block was commented out here (side logo). Now only shown when the template switch "School logo" is on --}}
        @if($branding['logo_url'])
        <img src="{{ $branding['logo_url'] }}" class="site-logo">
        @else
        <div style="width:90px;" class="side-spacer"></div>
        @endif

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
        <img src="{{ $template->imageUrl('right_logo') }}" data-preview-img="right_logo">
        @else
        <div style="width:90px;" class="side-spacer"></div>
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
                {{-- old: {{ $student->mother_name ?? '-' }} (students table mein column nahi, hamesha "-") --}}
                <td>{{ optional($student->parent)->mother_name ?: '-' }}</td>
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
            {{-- Division / Rank: classic never printed these two switches before; old templates have them off, so their print is unchanged --}}
            @if($template->show_division)
            <tr>
                <td><strong>Division:</strong></td>
                <td>{{ \App\Services\PrintDesignService::division($result->percentage ?? null, $result->status ?? null) }}</td>
            </tr>
            @endif
            @if($template->show_rank)
            <tr>
                <td><strong>Rank:</strong></td>
                <td>{{ $result->position ?? '-' }}</td>
            </tr>
            @endif
        </table>

        @if($template->show_photo)
        <img src="{{ $student->photo_url }}" class="student-photo">
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
            <tr>
                <td>{{ $mark->subject->name ?? '-' }}</td>
                {{-- old: ExamSchedule query per row; the controller already puts max_marks on every mark --}}
                <td>{{ $mark->max_marks ?? '-' }}</td>
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
            <td class="value-cell">{{ ($result->obtained_marks ?? null) ? strtoupper(\App\Services\PrintDesignService::numberToWords($result->obtained_marks)) : '-' }}</td>
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
            <img src="{{ $template->imageUrl('left_sign') }}" data-preview-img="left_sign">
            @endif
            <div class="sign-line">Class Teacher</div>
        </div>
        <div class="sign-block">
            @if($template->middle_sign)
            <img src="{{ $template->imageUrl('middle_sign') }}" data-preview-img="middle_sign">
            @endif
            @if($branding['stamp_url'])
            <img src="{{ $branding['stamp_url'] }}" class="stamp-img">
            @endif
            <div class="sign-line">Examiner</div>
        </div>
        <div class="sign-block">
            @if($branding['signature_url'])
            <img src="{{ $branding['signature_url'] }}">
            @elseif($template->right_sign)
            <img src="{{ $template->imageUrl('right_sign') }}" data-preview-img="right_sign">
            @endif
            <div class="sign-line">
                @if($template->show_head_signature)
                @if($branding['head_name'])<strong>{{ $branding['head_name'] }}</strong><br>@endif
                {{ $branding['head_title'] }}
                @else
                Principal
                @endif
            </div>
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
@if($isPreview)
</div>
@endif
