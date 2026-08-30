@extends('parent.layout.app')
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">{{ $student->first_name }} {{ $student->last_name }} — Marksheet</div>
</div>

<div class="card radius-10">
    <div class="card-body">
        <form action="{{ route('parent.students.results.marksheet', $student->id) }}" method="GET" target="_blank">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Exam <span class="text-danger">*</span></label>
                    <select name="exam_id" class="form-select form-select-sm" required>
                        <option value="">Select Exam</option>
                        @foreach($exams as $exam)
                        <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Marksheet Template <span class="text-danger">*</span></label>
                    <select name="template_id" class="form-select form-select-sm" required>
                        <option value="">Select Template</option>
                        @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->template_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class='bx bx-printer'></i> View
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection