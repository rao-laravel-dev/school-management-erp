@props(['excelRoute' => null, 'pdfRoute' => null, 'importModal' => null])

@php $qs = request()->getQueryString() ? '?' . request()->getQueryString() : ''; @endphp

@once
<style>
    @media (max-width: 535px) {
        .btn-text { display: none; }
    }
</style>
@endonce

<div class="d-inline-flex gap-1">
    @if($excelRoute)
        <a href="{{ $excelRoute . $qs }}" class="btn btn-sm btn-success" title="Export Excel">
            <i class="bx bx-spreadsheet"></i><span class="btn-text ms-1">Excel</span>
        </a>
    @endif
    @if($pdfRoute)
        <a href="{{ $pdfRoute . $qs }}" class="btn btn-sm btn-danger" title="Export PDF">
            <i class="bx bxs-file-pdf"></i><span class="btn-text ms-1">PDF</span>
        </a>
    @endif
    @if($importModal)
        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="{{ $importModal }}" title="Import Excel">
            <i class="bx bx-upload"></i><span class="btn-text ms-1">Import</span>
        </button>
    @endif
</div>