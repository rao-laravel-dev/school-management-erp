{{-- Formal marksheet: plain serif, double border frame in the template / school colour, one fixed A4 page per student --}}
@include('partials.exam_print.base')
<style>
    .ep-page.formal { padding: 3mm; font-family: 'Times New Roman', 'DejaVu Serif', serif; }
    .ep-page.formal .fo-frame { flex: 1; min-height: 0; overflow: hidden; display: flex; flex-direction: column; gap: 3.5mm; border: .7mm solid var(--p); outline: .3mm solid var(--p); outline-offset: 1mm; margin: 1.5mm; padding: 6mm 9mm 5mm; }
    .ep-page.formal .fo-head { padding-bottom: 3mm; border-bottom: .5mm solid var(--p); }
    .ep-page.formal .ep-school { color: var(--p); text-transform: uppercase; letter-spacing: .5pt; }
    /* the empty logo side shrinks to a thin slot so the school name gets the width (the logo side keeps its size) */
    /* old: .ep-page.formal .ep-head-side:not(:has(img)) { width: 8mm; } (removed: Formal now uses the same equal slots as Classic / Modern) */
    .ep-page.formal .fo-title { color: var(--p); font-size: 13pt; }
    .ep-page.formal .fo-main { flex: 1; min-height: 0; overflow: hidden; display: flex; flex-direction: column; gap: 3.5mm; }
    .ep-page.formal table.ep-table th { background: transparent; color: #000; border-bottom: .6mm solid var(--p); }
    .ep-page.formal table.ep-table tfoot td { background: transparent; border-top: .6mm solid var(--p); }
    .ep-page.formal .ep-summary { border-color: var(--p); }
    .ep-page.formal .ep-summary > div { border-left-color: var(--p); }
    .ep-page.formal .ep-sign-line { border-top-color: #000; }
</style>

@foreach($sheets as $sheet)
@php
    $enrollment = $sheet['enrollment'];
    $student = $sheet['student'];
    $result = $sheet['result'];
    $subjects = $sheet['subjects'];
    $bg = $template->imageUrl('background_image');
@endphp
<div class="ep-page formal {{ $orientation }} {{ \App\Services\PrintDesignService::fitLevel($subjects->count(), $orientation, $template->show_summary_boxes || $template->show_grade_scale) }}"
    style="--p: {{ $branding['primary'] }}; --on: {{ $branding['on_primary'] }}; --a: {{ $branding['accent'] }};@if($bg && !str_starts_with($bg, 'data:')) background-image: url('{{ $bg }}');@endif">
    <div class="fo-frame">
        <div class="fo-head">
            @include('partials.exam_print.brand_header', ['lines' => [$template->exam_name ?: $exam->name, $template->exam_center]])
        </div>
        <div class="fo-main">
            <div class="ep-doc-title fo-title">Marksheet</div>
            @include('admin.print_marksheet.designs._sheet_body')
        </div>
        @include('partials.exam_print.signatures', ['labels' => ['left' => 'Class Teacher', 'middle' => 'Examiner', 'right' => 'Principal']])
        @if($template->footer_text)
        <div class="ep-footer-text">{{ $template->footer_text }}</div>
        @endif
        @if($template->printing_date)
        <div class="ep-footer-text">Printed on: {{ \Carbon\Carbon::parse($template->printing_date)->format('d-m-Y') }}</div>
        @endif
    </div>
</div>
@endforeach
