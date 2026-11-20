{{-- Modern marksheet: colour band header (template colour or school theme colour), one fixed A4 page per student --}}
@include('partials.exam_print.base')
<style>
    .ep-page.modern .mo-band { background: var(--p); color: var(--on); padding: 6mm 10mm 5mm; }
    /* old: padding 1mm */
    .ep-page.modern .mo-band .ep-logo { background: #fff; padding: .4mm; border-radius: 1.5mm; }
    .ep-page.modern .mo-stripe { height: 1.6mm; background: var(--a); }
    .ep-page.modern .mo-main { flex: 1; min-height: 0; overflow: hidden; display: flex; flex-direction: column; gap: 3.5mm; padding: 4mm 10mm 0; }
    .ep-page.modern .mo-title { color: var(--p); border-left: 1.8mm solid var(--p); padding-left: 3mm; text-align: left; font-size: 14pt; }
    .ep-page.modern .mo-foot { display: flex; flex-direction: column; gap: 2mm; padding: 2mm 10mm 4mm; }
    .ep-page.modern .mo-bottom { height: 3mm; background: var(--p); }
    .ep-page.modern .ep-summary .v { color: var(--p); }
    .ep-page.modern .ep-sign-line { border-top-color: var(--p); }
</style>

@foreach($sheets as $sheet)
@php
    $enrollment = $sheet['enrollment'];
    $student = $sheet['student'];
    $result = $sheet['result'];
    $subjects = $sheet['subjects'];
    $bg = $template->imageUrl('background_image');
@endphp
<div class="ep-page modern {{ $orientation }} {{ \App\Services\PrintDesignService::fitLevel($subjects->count(), $orientation, $template->show_summary_boxes || $template->show_grade_scale) }}"
    style="--p: {{ $branding['primary'] }}; --on: {{ $branding['on_primary'] }}; --a: {{ $branding['accent'] }};@if($bg && !str_starts_with($bg, 'data:')) background-image: url('{{ $bg }}');@endif">
    <div class="mo-band">
        @include('partials.exam_print.brand_header', ['lines' => [$template->exam_name ?: $exam->name, $template->exam_center]])
    </div>
    <div class="mo-stripe"></div>
    <div class="mo-main">
        <div class="ep-doc-title mo-title">Marksheet</div>
        @include('admin.print_marksheet.designs._sheet_body')
    </div>
    <div class="mo-foot">
        @include('partials.exam_print.signatures', ['labels' => ['left' => 'Class Teacher', 'middle' => 'Examiner', 'right' => 'Principal']])
        @if($template->footer_text)
        <div class="ep-footer-text">{{ $template->footer_text }}</div>
        @endif
        @if($template->printing_date)
        <div class="ep-footer-text">Printed on: {{ \Carbon\Carbon::parse($template->printing_date)->format('d-m-Y') }}</div>
        @endif
    </div>
    <div class="mo-bottom"></div>
</div>
@endforeach
