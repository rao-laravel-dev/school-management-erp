@props(['id', 'title', 'templateRoute', 'actionRoute'])

@php
    $importErrors = session('import_errors', []);
    $hasErrors = !empty($importErrors) || $errors->has('file');
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ $actionRoute }}" method="POST" enctype="multipart/form-data" class="modal-content"
              onsubmit="this.querySelector('[type=submit]').disabled = true">
            @csrf
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white">{{ $title }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="mb-2 small"><strong>Step 1:</strong> Sample template download karke fill karein.</p>
                <a href="{{ $templateRoute }}" class="btn btn-sm btn-outline-success mb-3">
                    <i class="bx bx-download"></i> Download Sample Template
                </a>

                <p class="mb-2 small"><strong>Step 2:</strong> Filled file upload karein.</p>
                <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls" required>

                @if($hasErrors)
                    <div class="alert alert-danger small mt-3 mb-0" style="max-height:200px; overflow:auto;">
                        <strong>Import failed, nothing was saved:</strong>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->get('file') as $message)<li>{{ $message }}</li>@endforeach
                            @foreach($importErrors as $message)<li>{{ $message }}</li>@endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-success">Upload &amp; Import</button>
            </div>
        </form>
    </div>
</div>

@if($hasErrors)
    <script>
        window.addEventListener('load', function () {
            new bootstrap.Modal(document.getElementById(@json($id))).show();
        });
    </script>
@endif