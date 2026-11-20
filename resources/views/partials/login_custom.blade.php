{{-- T340b: school's own login page text / background (Site Settings > Appearance > Login page). Prints nothing when the fields are empty.
     Usage: @include('partials.login_custom', ['part' => 'style']) in <head> (role logins only), ['part' => 'text'] above the form. Needs $schoolBrand. --}}
@if(($part ?? '') === 'style')
    @if($schoolBrand->login_background_url)
    <style>
        .auth-cover-left { background: #f7f7ff url('{{ $schoolBrand->login_background_url }}') center / cover no-repeat; }
        .auth-cover-left .auth-img-cover-login { display: none; }
    </style>
    @endif
@elseif(($part ?? '') === 'text')
    @php
        $loginHeading = trim((string) $schoolBrand->login_heading);
        $loginMessage = trim((string) $schoolBrand->login_message);
    @endphp
    @if($loginHeading !== '' || $loginMessage !== '')
    <div class="text-center mb-4 login-custom-text">
        @if($loginHeading !== '')
        <h6 class="mb-1 fw-semibold">{{ $loginHeading }}</h6>
        @endif
        @if($loginMessage !== '')
        <p class="mb-0 text-muted">{!! nl2br(e($loginMessage)) !!}</p>
        @endif
    </div>
    @endif
@endif
