@extends($current_layout)
@section('content')
    <div class="container" style="max-width: 900px;">
        <div class="card mb-3">
            <div class="card-body">
                @include('card_verify._basic')
            </div>
        </div>
        <div class="card">
            <div class="card-body" id="verify-details">
                <div class="text-center text-muted">Loading details...</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $.get(@json($detailsUrl), function(html) {
                $('#verify-details').html(html);
            }).fail(function() {
                $('#verify-details').html('<div class="text-danger text-center">Could not load details.</div>');
            });
        });
    </script>
@endpush
