@extends($current_layout)
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => route('student.dashboard')],
    ['label' => 'Fees', 'url' => '#'],
    ['label' => 'My Fees', 'url' => ''],
]" />

<div class="mt-3">
    @include('student.fee_vouchers.partials.fee_tables', [
        'section'    => 'vouchers',
        'voucherUrl' => fn($voucher) => route('student.fees.voucher', $voucher->id),
        'receiptUrl' => fn($receiptId) => route('student.fees.receipt', $receiptId),
    ])
</div>

<div class="mt-3">
    @include('student.fee_vouchers.partials.fee_tables', [
        'section'    => 'ledger',
        'voucherUrl' => fn($voucher) => route('student.fees.voucher', $voucher->id),
        'receiptUrl' => fn($receiptId) => route('student.fees.receipt', $receiptId),
    ])
</div>

@endsection
