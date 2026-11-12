<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 12mm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #222;
            margin: 0;
        }

        .head-wrap {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            border-bottom: 1px solid #ccc;
        }

        .head-wrap td {
            vertical-align: middle;
            padding-bottom: 6px;
        }

        .head-wrap img.logo {
            height: 44px;
        }

        .head-wrap .school-name {
            font-size: 15px;
            font-weight: bold;
            margin: 0;
        }

        .head-wrap .school-sub {
            font-size: 8px;
            color: #000;
            margin: 1px 0 0;
        }

        .head-wrap .qr {
            text-align: center;
        }

        .head-wrap .qr img {
            width: 56px;
            height: 56px;
        }

        .copy-label {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 8px;
            background: #f2f2f2;
            padding: 4px;
        }

        .detail-table {
            width: 100%;
            margin-bottom: 8px;
            table-layout: fixed;
        }

        .detail-table td {
            vertical-align: top;
            font-size: 10px;
            padding: 1px 0;
        }

        .detail-table td.left {
            width: 55%;
            text-align: left;
        }

        .detail-table td.right {
            width: 45%;
            text-align: right;
        }

        .staff-name {
            font-size: 12px;
            font-weight: bold;
        }

        .slip-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .slip-table th,
        .slip-table td {
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            padding: 3px 4px;
            font-size: 10px;
            text-align: left;
        }

        .slip-table th {
            background: #f7f7f7;
        }

        .slip-table td.amt {
            text-align: right;
            width: 30%;
        }

        .slip-table tr.net td {
            font-weight: bold;
            border-top: 2px solid #999;
        }

        .note {
            margin-top: 8px;
            font-size: 8px;
            color: #000;
        }
    </style>
</head>

<body>
    @php
    $role = ucfirst($slip->user->roles->first()->name ?? '-');
    $monthLabel = \Carbon\Carbon::parse($slip->month)->format('F Y');
    $qrText = \Illuminate\Support\Facades\URL::signedRoute('card_verify.salary_slip', ['id' => $slip->id]); // URL only, no name/amount
    $qr = DNS2D::getBarcodePNG($qrText, 'QRCODE', 4, 4);
    @endphp

    <table class="head-wrap">
        <tr>
            @if($logo)
            <td style="width: 80px;"><img class="logo" src="{{ $logo }}" alt=""></td>
            @endif
            <td style="text-align: center;">
                <p class="school-name">{{ $school->school_name ?: config('app.name') }}</p>
                @if($school->address)<p class="school-sub">{{ $school->address }}</p>@endif
                @if($school->phone)<p class="school-sub">Phone: {{ $school->phone }}</p>@endif
            </td>
            <td style="width: 60px;" class="qr">
                <img src="data:image/png;base64,{{ $qr }}" alt="">
            </td>
        </tr>
    </table>

    <div class="copy-label">Salary Slip — {{ $monthLabel }}</div>

    <table class="detail-table">
        <tr>
            <td class="left">
                <div class="staff-name">{{ $slip->user->name }}</div>
                <div>Staff ID: {{ $slip->user->staff_code }}</div>
                <div>Role: {{ $role }}</div>
            </td>
            <td class="right">
                <div>Month: {{ $monthLabel }}</div>
                <div>Slip No: {{ $slip->id }}</div>
                <div>Status: {{ ucfirst($slip->status) }}</div>
            </td>
        </tr>
    </table>

    <table class="slip-table">
        <tr><th colspan="2">Earnings</th></tr>
        <tr><td>Basic Salary</td><td class="amt">{{ number_format($slip->basic_salary, 2) }}</td></tr>
        <tr><td>Allowance</td><td class="amt">{{ number_format($slip->allowance, 2) }}</td></tr>

        <tr><th colspan="2">Attendance Summary</th></tr>
        <tr><td>Total Working Days</td><td class="amt">{{ $slip->total_days }}</td></tr>
        <tr><td>Present</td><td class="amt">{{ $slip->present_days }}</td></tr>
        <tr><td>Absent</td><td class="amt">{{ $slip->absent_days }}</td></tr>
        <tr><td>Half Day</td><td class="amt">{{ $slip->half_days }}</td></tr>
        <tr><td>Leave</td><td class="amt">{{ $slip->leave_days }}</td></tr>
        <tr><td>Per Day Rate</td><td class="amt">{{ number_format($slip->per_day_rate, 2) }}</td></tr>

        <tr><th colspan="2">Deductions</th></tr>
        <tr><td>Attendance Deduction</td><td class="amt">{{ number_format($slip->attendance_deduction, 2) }}</td></tr>
        <tr><td>Manual Deduction</td><td class="amt">{{ number_format($slip->manual_deduction, 2) }}</td></tr>
        <tr><td>Advance Deduction</td><td class="amt">{{ number_format($slip->advance_deduction, 2) }}</td></tr>
        <tr><td>Total Deduction</td><td class="amt">{{ number_format($slip->deduction, 2) }}</td></tr>

        <tr class="net"><td>Net Salary</td><td class="amt">{{ number_format($slip->net_salary, 2) }}</td></tr>
    </table>

    @if($slip->status == 'paid')
    <table class="slip-table" style="margin-top: 10px;">
        <tr><th colspan="2">Payment</th></tr>
        <tr><td>Payment Date</td><td class="amt">{{ $slip->payment_date ? $slip->payment_date->format('d-m-Y') : '-' }}</td></tr>
        <tr><td>Paid By</td><td class="amt">{{ $slip->paidBy->name ?? '-' }}</td></tr>
        <tr><td>Payment Method</td><td class="amt">{{ ucfirst(str_replace('_', ' ', $slip->transaction->payment_method ?? '-')) }}</td></tr>
        <tr><td>Reference No</td><td class="amt">{{ $slip->transaction->reference_no ?? '-' }}</td></tr>
    </table>
    @endif

    <div class="note">Computer generated — no signature required.</div>
</body>

</html>
