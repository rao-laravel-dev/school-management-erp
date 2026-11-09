<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 8mm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            color: #000;
            margin: 0;
        }

        .page-break {
            page-break-after: always;
        }

        .cut-line {
            border-top: 1px dashed #666;
            text-align: center;
            font-size: 7px;
            color: #000;
            margin: 16px 0;
            padding-top: 2px;
        }

        .copies-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .copies-table>tbody>tr>td {
            vertical-align: top;
            padding: 0 3px;
        }

        .copy-box {
            border: 1px solid #999;
            padding: 6px;
            page-break-inside: avoid;
        }

        .head-wrap,
        .info-table,
        .bill-table,
        .sum-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 4px;
        }

        .head-wrap td {
            vertical-align: top;
        }

        .head-wrap img.logo {
            height: 34px;
        }

        .school-name {
            font-size: 12px;
            font-weight: bold;
            margin: 0;
        }

        .school-sub {
            font-size: 7px;
            color: #000;
            margin: 1px 0 0;
        }

        .month-box {
            border: 1px solid #333;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            padding: 3px;
        }

        .copy-label {
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            background: #000;
            color: #fff;
            padding: 3px;
            margin-bottom: 4px;
        }

        .barcode-wrap {
            text-align: center;
            margin-top: 2px;
        }

        .barcode-wrap img {
            height: 62px;
            width: 62px;
        }

        .info-table td {
            padding: 1px 0;
            vertical-align: top;
        }

        .bill-title {
            font-weight: bold;
            text-align: center;
            background: #f2f2f2;
            border: 1px solid #999;
            padding: 2px;
            margin-bottom: 0;
        }

        .bill-table th,
        .bill-table td {
            border: 1px solid #ccc;
            padding: 2px 3px;
            text-align: left;
            word-wrap: break-word;
        }

        .bill-table th {
            background: #f7f7f7;
        }

        .bill-table .amt,
        .sum-table .amt {
            text-align: right;
            width: 30%;
        }

        .sum-table td {
            padding: 1.5px 3px;
        }

        .sum-table tr.total td {
            font-weight: bold;
            border-top: 1px solid #999;
        }

        .box {
            border: 1px solid #bbb;
            padding: 3px;
            margin-bottom: 4px;
            font-size: 7.5px;
        }

        .note {
            font-size: 7px;
            color: #000;
            margin-top: 3px;
        }

        .paid-stamp {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            color: #198754;
            border: 1px solid #198754;
            padding: 2px;
            margin-bottom: 4px;
        }

        /* Compact copies (School / Bank) */
        .compact {
            font-size: 7px;
        }

        .compact .school-name {
            font-size: 9px;
        }

        .compact .month-box {
            font-size: 8px;
            padding: 2px;
        }

        .compact .head-wrap img.logo {
            height: 24px;
        }

        .compact .barcode-wrap img {
            height: 44px;
            width: 44px;
        }

        .compact .box,
        .compact .note,
        .compact .school-sub {
            font-size: 6.5px;
        }
    </style>
</head>

<body>
    @php
    $money = fn($x) => number_format((float) $x, floor((float) $x) == (float) $x ? 0 : 2);
    @endphp

    @foreach($items as $item)
    @php
    $v = $item['voucher'];
    $bank = $v->bankAccount;
    $barcode = DNS2D::getBarcodePNG((string) $v->voucher_no, 'QRCODE', 4, 4);
    $lowerCopies = $bank ? ['SCHOOL COPY', 'BANK COPY'] : ['SCHOOL COPY'];
    @endphp

    <div class="{{ $loop->last ? '' : 'page-break' }}">
        @include('admin.fee_voucher.voucher_copy', ['copyLabel' => 'STUDENT FEE SLIP', 'compact' => false])

        <div class="cut-line">- - - - - - - - cut here - - - - - - - -</div>

        <table class="copies-table">
            <tr>
                @foreach($lowerCopies as $copyLabel)
                <td style="width: {{ round(100 / count($lowerCopies), 2) }}%;">
                    @include('admin.fee_voucher.voucher_copy', ['copyLabel' => $copyLabel, 'compact' => true])
                </td>
                @endforeach
            </tr>
        </table>
    </div>
    @endforeach
</body>

</html>
