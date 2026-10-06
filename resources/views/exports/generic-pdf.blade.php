<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 30px 30px 45px 30px;
        }

        body {
            font-family: sans-serif;
            font-size: 11px;
        }

        .header-table {
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 2px solid #4472C4;
            padding-bottom: 8px;
        }

        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .logo-cell {
            width: 120px;
            text-align: left;
        }

        .logo-cell img {
            max-height: 90px;
            max-width: 110px;
        }

        .title-cell {
            text-align: center;
        }

        .spacer-cell {
            width: 120px;
        }

        h3 {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
        }

        p.sub {
            margin: 3px 0 0 0;
            color: #555;
            font-size: 10px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #999;
            padding: 4px 6px;
            text-align: left;
        }

        table.data-table th {
            background: #f0f0f0;
        }

        table.data-table thead {
            display: table-header-group;
        }

        table.data-table tr {
            page-break-inside: avoid;
        }

        table.data-table tbody tr:nth-child(even) {
            background-color: #f5f7fa;
        }

        table.data-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
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
                <p class="sub">{{ $title }} @if(!empty($subtitle)) &nbsp;|&nbsp; {{ $subtitle }} @endif &nbsp;|&nbsp; Generated: {{ now()->format('d M Y, h:i A') }}</p>
                <p class="sub">
                    {{ $siteSetting->address ?? '' }}
                    @if($siteSetting->phone) &nbsp;|&nbsp; Ph: {{ $siteSetting->phone }} @endif
                    @if($siteSetting->email) &nbsp;|&nbsp; {{ $siteSetting->email }} @endif
                </p>
            </td>
            <td class="spacer-cell">{{-- symmetry ke liye --}}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                @foreach($headings as $heading)
                <th @if($loop->first && $heading === '#') style="text-align:center;" @endif>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
            <tr>
                @foreach($row as $cell)
                <td @if($loop->first && ($headings[0] ?? '') === '#') style="text-align:center;" @endif>{{ $cell }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($siteSetting->footer_note)
    <p style="text-align:center; margin-top:15px; color:#777; font-size:9px;">{{ $siteSetting->footer_note }}</p>
    @endif

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont('helvetica', 'normal');
            $pdf->page_text($pdf->get_width() - 90, $pdf->get_height() - 28, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, 8, [0.4, 0.4, 0.4]);
        }
    </script>

</body>

</html>