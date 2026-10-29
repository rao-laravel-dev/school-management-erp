<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png') }}" type="image/png" />
    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <title>Verify ID Card</title>
</head>

<body class="bg-light">
    <div class="container py-4" style="max-width: 480px;">
        <div class="card shadow-sm">
            <div class="card-body">
                @include('card_verify._basic')
            </div>
        </div>
    </div>
</body>

</html>
