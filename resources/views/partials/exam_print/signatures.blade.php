{{-- Shared signature row for the new designs. $template, $branding, $labels = ['left' => ..., 'middle' => ..., 'right' => ...].
     Right slot = head of school (site signature + name + title) when the template shows it, else the template's right sign + label. Stamp sits in the middle slot. --}}
<div class="ep-sign-row {{ $template->show_parent_sign ? 'four' : '' }}">
    <div class="ep-sign-block">
        @if($template->left_sign)
        <img class="ep-sign-img" data-preview-img="left_sign" src="{{ $template->imageUrl('left_sign') }}" alt="">
        @endif
        <div class="ep-sign-line">{{ $labels['left'] }}</div>
    </div>
    <div class="ep-sign-block">
        @if($template->middle_sign)
        <img class="ep-sign-img" data-preview-img="middle_sign" src="{{ $template->imageUrl('middle_sign') }}" alt="">
        @endif
        @if($branding['stamp_url'])
        <img class="ep-stamp" src="{{ $branding['stamp_url'] }}" alt="">
        @endif
        <div class="ep-sign-line">{{ $labels['middle'] }}</div>
    </div>
    <div class="ep-sign-block">
        @if($branding['signature_url'])
        <img class="ep-sign-img" src="{{ $branding['signature_url'] }}" alt="">
        @elseif($template->right_sign)
        <img class="ep-sign-img" data-preview-img="right_sign" src="{{ $template->imageUrl('right_sign') }}" alt="">
        @endif
        <div class="ep-sign-line">
            @if($template->show_head_signature)
            @if($branding['head_name'])<strong>{{ $branding['head_name'] }}</strong><br>@endif
            {{ $branding['head_title'] }}
            @else
            {{ $labels['right'] }}
            @endif
        </div>
    </div>
    @if($template->show_parent_sign)
    <div class="ep-sign-block">
        <div class="ep-sign-line">Parent / Guardian</div>
    </div>
    @endif
</div>
