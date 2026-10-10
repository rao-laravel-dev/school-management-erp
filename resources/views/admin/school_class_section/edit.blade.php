@extends($current_layout)

@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academics</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active">Edit Class-Section Mapping</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="{{ route('school_class_section.index') }}" class="btn btn-secondary btn-sm">
            <i class="bx bx-arrow-back"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card" style="border-top: 5px solid #0d6efd; border-radius: 10px;">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bx bx-edit"></i> Edit Mapping for: {{ $mapping->name }}</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('school_class_section.update', $mapping->id) }}" method="POST">
                    @csrf
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Class Name</label>
                        <input type="text" class="form-control" value="{{ $mapping->name }}" disabled>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Select Sections</label>
                        <div class="border p-3" style="height: 250px; overflow-y: scroll; background: #fff;">
                            @foreach($sections as $sec)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="section_ids[]" value="{{ $sec->id }}" 
                                    {{ $mapping->mappedSections->contains($sec->id) ? 'checked' : '' }} 
                                    id="sec{{ $sec->id }}">
                                <label class="form-check-label" for="sec{{ $sec->id }}">{{ $sec->name }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-save"></i> Update Mapping</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection