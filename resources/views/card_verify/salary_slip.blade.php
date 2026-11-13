<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    {{-- <link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png') }}" type="image/png" /> --}}
    <link rel="icon" href="{{ $schoolBrand->icon_url ?: ($mssIcon ?: asset('backend/assets/images/favicon-32x32.png')) }}" type="{{ $schoolBrand->icon_url ? 'image/webp' : 'image/png' }}" />
    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <title>Verify Salary Slip</title>
</head>

<body class="bg-light">
    <div class="container py-4" style="max-width: 480px;">
        <div class="card shadow-sm">
            <div class="card-body">
                {{-- Basic salary slip verify: school + staff name/ID/role + month only (no amounts, no status, no slip id) --}}
                <div class="text-center mb-3">
                    @if($school->logo_url)
                        <img src="{{ $school->logo_url }}" alt="" style="max-height: 70px;" class="mb-2">
                    @endif
                    <h5 class="fw-bold mb-0">{{ $school->school_name }}</h5>
                </div>

                @if(!$slip)
                    <div class="alert alert-danger text-center mb-0">Invalid slip link.</div>
                @else
                    <div class="text-center mb-3">
                        <span class="badge bg-success fs-6">Genuine salary slip</span>
                    </div>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Name</span><strong>{{ $slip->user->name }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Staff ID</span><strong>{{ $slip->user->staff_code }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Role</span><strong>{{ ucfirst($slip->user->roles->first()->name ?? '-') }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Slip Month</span><strong>{{ \Carbon\Carbon::parse($slip->month)->format('F Y') }}</strong></li>
                    </ul>
                @endif
            </div>
        </div>
    </div>
</body>

</html>
