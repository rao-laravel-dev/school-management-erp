{{-- Shared header for the new designs. $branding, $template, $lines (extra lines under the school name). Left slot = school logo (if the template shows it) else the template's left logo --}}
@if($template->header_image)
<img class="ep-header-image" data-preview-img="header_image" src="{{ $template->imageUrl('header_image') }}" alt="">
@endif
<div class="ep-head">
    <div class="ep-head-side">
        @if($branding['logo_url'])
        <img class="ep-logo" src="{{ $branding['logo_url'] }}" alt="">
        @elseif($template->left_logo)
        <img class="ep-logo" data-preview-img="left_logo" src="{{ $template->imageUrl('left_logo') }}" alt="">
        @endif
    </div>
    <div class="ep-head-main">
        <p class="ep-school">{{ $branding['school_name'] }}</p>
        @foreach($lines as $line)
        @if($line)
        <p class="ep-line">{{ $line }}</p>
        @endif
        @endforeach
        @if($branding['address'] || $branding['phone'] || $branding['email'])
        <p class="ep-contact">
            {{ $branding['address'] }}
            @if($branding['phone']) | Ph: {{ $branding['phone'] }} @endif
            @if($branding['email']) | {{ $branding['email'] }} @endif
        </p>
        @endif
    </div>
    <div class="ep-head-side">
        @if($template->right_logo)
        <img class="ep-logo" data-preview-img="right_logo" src="{{ $template->imageUrl('right_logo') }}" alt="">
        @endif
    </div>
</div>
