<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; }

        .header-table { width: 100%; margin-bottom: 10px; border-bottom: 2px solid #4472C4; padding-bottom: 8px; }
        .header-table td { border: none; padding: 0; vertical-align: middle; }
        .logo-cell { width: 120px; text-align: left; }
        .logo-cell img { max-height: 90px; max-width: 110px; }
        .title-cell { text-align: center; }
        .spacer-cell { width: 120px; }

        h3 { margin: 0; font-size: 18px; color: #2c3e50; }
        p.sub { margin: 3px 0 0 0; color: #555; font-size: 10px; }

        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        table.data-table th { background: #f0f0f0; }
        table.data-table tbody tr:nth-child(even) { background-color: #f5f7fa; }
        table.data-table tbody tr:nth-child(odd)  { background-color: #ffffff; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($logoPath)
                <img src="{{ $logoPath }}" alt="Logo">
                @endif
            </td>
            <td class="title-cell">
    <h3>{{ $siteSetting->school_name ?? config('app.name') }}</h3>
    <p class="sub">Student List — {{ $classLabel }} &nbsp;|&nbsp; Generated: {{ now()->format('d M Y, h:i A') }}</p>
    <p class="sub">
        {{ $siteSetting->address ?? '' }}
        @if($siteSetting->phone) &nbsp;|&nbsp; Ph: {{ $siteSetting->phone }} @endif
        @if($siteSetting->email) &nbsp;|&nbsp; {{ $siteSetting->email }} @endif
    </p>
</td>
            <td class="spacer-cell">{{-- balance space, symmetry ke liye --}}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>#</th><th>Adm No</th><th>Name</th><th>Father Name</th>
                <th>Class</th><th>Section</th><th>Group</th><th>Roll No</th><th>Phone</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($enrollments as $i => $e)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $e->student->admission_no }}</td>
                <td>{{ $e->student->first_name }} {{ $e->student->last_name }}</td>
                <td>{{ $e->student->parent->father_name ?? '' }}</td>
                <td>{{ $e->schoolClass->name ?? '' }}</td>
                <td>{{ $e->section->name ?? '' }}</td>
                <td>{{ $e->group->name ?? '-' }}</td>
                <td>{{ $e->roll_no }}</td>
                <td>{{ $e->student->parent->father_phone ?? '' }}</td>
                <td>{{ optional($e->student->user)->status == 1 ? 'Active' : 'Inactive' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($siteSetting->footer_note)
    <p style="text-align:center; margin-top:15px; color:#777; font-size:9px;">{{ $siteSetting->footer_note }}</p>
    @endif

</body>
</html>