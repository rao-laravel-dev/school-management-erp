<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 6mm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            color: #222;
            margin: 0;
        }

        .wrapper-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .wrapper-table>tbody>tr>td {
            width: 33.33%;
            vertical-align: top;
            padding: 0 4px;
        }

        .copy-box {
            border: 1px solid #999;
            padding: 6px;
        }

        .school-header {
            text-align: center;
            margin-bottom: 5px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
        }

        .school-header h2 {
            margin: 0;
            font-size: 12px;
        }

        .copy-label {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 5px;
            background: #f2f2f2;
            padding: 3px;
        }

        .detail-table {
            width: 100%;
            margin-bottom: 4px;
            table-layout: fixed;
        }

        .detail-table td {
            vertical-align: top;
            font-size: 8px;
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

        .student-name {
            font-size: 10px;
            font-weight: bold;
        }

        .fee-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            table-layout: fixed;
        }

        .fee-table th,
        .fee-table td {
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            padding: 2px 1px;
            font-size: 7.5px;
            text-align: left;
            word-wrap: break-word;
        }

        .fee-table th {
            background: #f7f7f7;
        }

        .fee-table tfoot td {
            font-weight: bold;
            border-top: 2px solid #999;
        }

                .col-date  { width: 10%; }
                .col-fee   { width: 20%; }
                .col-mode  { width: 15%; white-space: nowrap; }
                .col-total { width: 13%; }
                .col-amt   { width: 12%; }
                .col-disc  { width: 10%; }
                .col-fine  { width: 10%; }
                .col-bal   { width: 10%; }

        .note {
            margin-top: 5px;
            font-size: 7px;
            color: #555;
        }
    </style>
</head>

<body>
    @php
    $first = $transactions->first();
    $enrollment = $student->currentEnrollment;
    $className = $enrollment->schoolClass->name ?? '-';
    $sectionName = $enrollment->section->name ?? '-';
    $collectedBy = $first->user->name ?? '-';
    $receiptNo = $first->payment_group_id ?? $first->id;
    $totalAmount = $transactions->sum('amount');

    $rowDiscounts = [];
    $totalDiscount = 0;
    $totalBalance = 0; // ← naya

    foreach ($transactions as $txn) {
    $feeAmount = $txn->studentFee->amount ?? 0;
    $feeDiscount = $txn->studentFee->discount ?? 0;
    $proportion = $feeAmount > 0 ? ($txn->amount / $feeAmount) : 0;
    $txnDiscount = $feeDiscount * $proportion;

    $rowDiscounts[$txn->id] = $txnDiscount;
    $totalDiscount += $txnDiscount;

    // ← naya: is fee ka current balance total mein jodo
    $totalBalance += $balanceAsOf[$txn->id] ?? 0;
    }

    $copies = ['Student Copy', 'School Copy', 'Bank Copy'];
    @endphp

    <table class="wrapper-table">
        <tr>
            @foreach($copies as $copyLabel)
            <td>
                <div class="copy-box">

                    <div class="school-header">
                        @if(!empty($school->logo))
                        <img src="{{ public_path('uploads/school/' . $school->logo) }}" alt="{{ $school->name }}">
                        @endif
                        <h2>{{ $school->name ?? config('app.name') }}</h2>
                        @if(!empty($school->address))
                        <p>{{ $school->address }}</p>
                        @endif
                    </div>

                    <div class="copy-label">{{ $copyLabel }}</div>

                    <table class="detail-table">
                        <tr>
                            <td class="left">
                                <div class="student-name">{{ $student->full_name }}</div>
                                <div>({{ $student->admission_no }})</div>
                                <div>Father: {{ $student->parent->father_name ?? '-' }}</div>
                                <div>Class: {{ $className }} ({{ $sectionName }})</div>
                            </td>
                            <td class="right">
                                <div>Date: {{ \Carbon\Carbon::parse($first->transaction_date)->format('d/m/Y') }}</div>
                                <div>Receipt No: {{ $receiptNo }}</div>
                                <div>By: {{ $collectedBy }}</div>
                            </td>
                        </tr>
                    </table>

                    <table class="fee-table">
                        <thead>
                            <tr>
                                <th class="col-date">Date</th>
                                <th class="col-fee">Fees</th>
                                <th class="col-mode">Mode</th>
                                <th class="col-total">Total</th>
                                <th class="col-amt">Paid</th>
                                <th class="col-disc">Disc</th>
                                <th class="col-fine">Fine</th>
                                <th class="col-bal">Due</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $txn)
                            @php
                            $feeTotal = $txn->studentFee->amount ?? 0;
                            $feeBalance = $balanceAsOf[$txn->id] ?? 0;
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($txn->transaction_date)->format('d/m') }}</td>
                                <td>{{ $txn->studentFee->feeType->name ?? '-' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $txn->payment_method)) }}</td>
                                <td>{{ number_format($feeTotal, 0) }}</td>
                                <td>{{ number_format($txn->amount, 0) }}</td>
                                <td>{{ number_format($rowDiscounts[$txn->id], 0) }}</td>
                                <td>0</td>
                                <td>{{ number_format(max($feeBalance, 0), 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        @if($transactions->count() > 1)
                        <tfoot>
                            <tr>
                                <td colspan="3">Total</td>
                                <td>{{ number_format($transactions->sum(fn($t) => $t->studentFee->amount ?? 0), 0) }}</td>
                                <td>{{ number_format($totalAmount, 0) }}</td>
                                <td>{{ number_format($totalDiscount, 0) }}</td>
                                <td>0</td>
                                <td>{{ number_format($totalBalance ?? 0, 0) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>

                    @if($first->reference_no)
                    <div class="note">Ref: {{ $first->reference_no }}</div>
                    @endif
                    @if($first->note)
                    <div class="note">Note: {{ $first->note }}</div>
                    @endif
                    <div class="note">Computer generated — no signature required.</div>
                </div>
            </td>
            @endforeach
        </tr>
    </table>
</body>

</html>