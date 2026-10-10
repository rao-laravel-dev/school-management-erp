@extends('superadmin.dashboard')
@section('content')
 <!--breadcrumb--> 
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Admin Profile</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Admin Profile</li>
                    </ol>
                </nav>
            </div>
           
        </div>
        <!--end breadcrumb-->
        <div class="container">
            <div class="main-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-column align-items-center text-center">
                                    <img src="{{ $profileData->photo_url }}" alt="Super Admin Profile Image" class="rounded-circle p-1 bg-primary" width="110">
                                
                                    <div class="mt-3">

                                        <h4>{{ $profileData->name }}</h4>
                                        <p class="text-secondary mb-1">{{ $profileData->username }}</p>
                                       
                                        <p class="text-secondary mb-1">Since {{ $profileData->created_at }}</p>
                                    </div>
                                </div>
                                <hr class="my-4" />
                                
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="card">
                            <form method="post" action="{{ route('adminsuperadmin.profile.update') }}" enctype="multipart/form-data">
                                @csrf
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Full Name</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="profileName" class="form-control" value="{{ old('profileName', $profileData->name) }}"  />
                                        <span class="text-danger small">@error('profileName'){{ $message }}@enderror</span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Email</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="email" class="form-control" value="{{ $profileData->email }}" readonly />
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Phone</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" class="form-control" name="profilePhone" value="{{ old('profilePhone', $profileData->phone) }}" />
                                        <span class="text-danger small">@error('profilePhone'){{ $message }}@enderror</span>
                                    </div>
                                </div>
                                 <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Gender</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="gender" id="gender" class="form-control">
                                        <option value="male" {{ old('gender', $profileData->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $profileData->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    <span class="text-danger small">@error('gender'){{ $message }}@enderror</span>
                                </div>
                            </div>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Address</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" class="form-control" name="address" value="{{ old('address', $profileData->address) }}" />
                                        <span class="text-danger small">@error('address'){{ $message }}@enderror</span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Image</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="file" class="form-control" id="inputimage" name="photo" />
                                        <span class="text-danger small">@error('photo'){{ $message }}@enderror</span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0"></h6>
                                    </div>
    <div class="col-sm-9 text-secondary">
    <img id="showImage" src="{{ $profileData->photo_url }}" alt="Admin Profile Image" class="rounded-circle p-1 bg-primary" width="80">
    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="submit" class="btn btn-primary px-4" value="Update Profile" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                       
                    </div>
                </div>
            </div>
        </div>

@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function(){
        @if($errors->any())
            toastr.error(@json($errors->first()));
        @endif
        $('#inputimage').change(function(e){
            var reader = new FileReader();
            reader.onload = function(e){
                $('#showImage').attr('src',e.target.result);
            }
            reader.readAsDataURL(e.target.files['0']);
        });
    });
</script>
@endpush
