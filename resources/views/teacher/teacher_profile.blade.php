@extends('teacher.layout.app')
@section('content')
@php
    $teacherImageUrl = $teacher->photo_url;
@endphp

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => ucfirst(auth()->user()->roles->first()->name) . ' Profile', 'url' => route('teacher.dashboard')],
    ['label' => 'Teacher Profile', 'url' => '#'],
]" />
<!--end breadcrumb-->

<div class="container">
    <div class="main-body">
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-column align-items-center text-center">
                            <img src="{{ $teacherImageUrl }}" alt="Teacher Profile Image" class="rounded-circle p-1 bg-primary" width="110">

                            <div class="mt-3">
                                <h4>{{ $teacher->user->name }}</h4>
                                <p class="text-secondary mb-1">{{ $teacher->user->username }}</p>
                                <p class="text-secondary mb-1">Since {{ $teacher->user->created_at->format('d M, Y') }}</p>
                            </div>
                        </div>
                        <hr class="my-4" />
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <form method="post" action="{{ route('teacher.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-3"><h6 class="mb-0">Full Name</h6></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="text" name="profileName" class="form-control form-control-sm" value="{{ $teacher->user->name }}" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3"><h6 class="mb-0">Email</h6></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="email" class="form-control form-control-sm" value="{{ $teacher->user->email }}" readonly />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3"><h6 class="mb-0">Phone</h6></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="text" class="form-control form-control-sm" name="profilePhone" value="{{ $teacher->user->phone }}" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3"><h6 class="mb-0">Gender</h6></div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="gender" id="gender" class="form-select form-select-sm">
                                        <option value="male" {{ $teacher->user->gender == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ $teacher->user->gender == 'female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3"><h6 class="mb-0">Address</h6></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="text" class="form-control form-control-sm" name="address" value="{{ $teacher->user->address }}" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3"><h6 class="mb-0">Image</h6></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="file" class="form-control form-control-sm" id="inputimage" name="photo" accept="image/*" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <img id="showImage" src="{{ $teacherImageUrl }}" alt="Teacher Profile Image" class="rounded-circle p-1 bg-primary" width="80">
                                </div>
                            </div>

                            {{-- Change Password (optional) --}}
                            <hr>
                            <h6 class="text-primary mb-2">Change Password</h6>
                            <small class="text-muted d-block mb-3">Khali chhor dein agar password change nahi karna.</small>

                            <div class="row mb-3">
                                <div class="col-sm-3"><h6 class="mb-0">Current Password</h6></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="password" name="current_password" class="form-control form-control-sm @error('current_password') is-invalid @enderror">
                                    @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3"><h6 class="mb-0">New Password</h6></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="password" name="new_password" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3"><h6 class="mb-0">Confirm New Password</h6></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="password" name="new_password_confirmation" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="submit" class="btn btn-primary px-4" value="Update Profile" />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        $('#inputimage').change(function(e){
            var reader = new FileReader();
            reader.onload = function(e){
                $('#showImage').attr('src', e.target.result);
            }
            reader.readAsDataURL(e.target.files[0]);
        });
    });
</script>
@endsection