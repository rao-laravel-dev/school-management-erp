@extends('student.layout.app')

@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academics</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Class Teacher</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card radius-10">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0 text-white"><i class="bx bx-user-pin"></i> Class Teacher Details</h5>
    </div>
    <div class="card-body text-center py-4">
        @if($assignedTeacher)
            <div class="mb-3">
                @php
                    // $assignedTeacher stdClass hai (DB::table), isliye Teacher model se photo_url liya
                    $imgUrl = (new \App\Models\Teacher)->forceFill(['photo' => $assignedTeacher->photo])->photo_url;
                @endphp

                @if(!empty($assignedTeacher->photo))
                    <img src="{{ $imgUrl }}"
                         class="rounded-circle p-1 border border-primary shadow" 
                         width="120" height="120" alt="Teacher Photo" 
                         style="object-fit: cover;">
                @else
                    <div class="rounded-circle bg-light-primary d-flex align-items-center justify-content-center mx-auto" 
                         style="width: 120px; height: 120px; border: 2px dashed #0d6efd;">
                        <i class='bx bxs-user-rectangle text-primary' style="font-size: 60px;"></i>
                    </div>
                @endif
            </div>

            <h4 class="mt-3">Sir/Ms. {{ ucfirst($assignedTeacher->first_name) }} {{ ucfirst($assignedTeacher->last_name) }}</h4>
            
            <div class="my-3">
                <p class="text-muted mb-1">Assigned Teacher for</p>
                <span class="badge bg-light-primary text-primary fs-6">
                    {{ $assignedTeacher->class_name }} - {{ $assignedTeacher->section_name }}
                </span>
            </div>
            
            @if($assignedTeacher->email)
                <p class="text-secondary"><i class='bx bx-envelope'></i> {{ $assignedTeacher->email }}</p>
            @endif

            <div class="mt-3">
                <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bx bx-check-circle"></i> Active</span>
            </div>

        @else
            <div class="text-muted py-5">
                <i class='bx bx-info-circle fs-1'></i>
                <p class="mt-2">No teacher has been assigned to your class section yet.</p>
            </div>
        @endif
    </div>
</div>

@endsection