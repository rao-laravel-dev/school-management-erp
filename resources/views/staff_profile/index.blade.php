@extends($current_layout)
@section('content')
 <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">{{ ucfirst($staffRole) }} Profile</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ ucfirst($staffRole) }} Profile</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->
        @php
            $staffProfileId = $staffProfile->{$staffRole . '_id'};
            $staffProfileSalary = $staffProfile->salary;
            $staffProfileBank = $staffProfile->bankDetail;
        @endphp
        <div class="container">
            <div class="main-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-column align-items-center text-center">
                                    <img src="{{ $staffProfile->photo_url }}" alt="{{ ucfirst($staffRole) }} Profile Image" class="rounded-circle p-1 bg-primary" width="110">

                                    <div class="mt-3">
                                        <h4>{{ $user->name }}</h4>
                                        <p class="text-secondary mb-1">{{ $staffProfileId ? $staffProfileId : '-' }}</p>
                                        <p class="text-secondary mb-1">Joined {{ $staffProfile->joining_date ? \Carbon\Carbon::parse($staffProfile->joining_date)->format('d-m-Y') : '-' }}</p>
                                        <p class="text-secondary mb-1">Basic Salary {{ $staffProfileSalary ? number_format($staffProfileSalary->basic_salary) : '-' }}</p>
                                    </div>
                                </div>
                                <hr class="my-4" />
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="card">
                            <form method="post" id="staffProfileForm" action="{{ route('staff_profile.update') }}" enctype="multipart/form-data">
                                @csrf
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Full Name</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" class="form-control form-control-sm" value="{{ $user->name }}" readonly />
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Email</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="email" class="form-control form-control-sm" value="{{ $user->email }}" readonly />
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">CNIC</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" class="form-control form-control-sm" value="{{ $staffProfile->cnic }}" readonly />
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Phone</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" class="form-control form-control-sm @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $staffProfile->phone) }}" />
                                        <div class="invalid-feedback">@error('phone'){{ $message }}@enderror</div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Gender</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <select name="gender" id="gender" class="form-select form-select-sm @error('gender') is-invalid @enderror">
                                            <option value="male" {{ old('gender', $staffProfile->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ old('gender', $staffProfile->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                        </select>
                                        <div class="invalid-feedback">@error('gender'){{ $message }}@enderror</div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Address</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" class="form-control form-control-sm @error('address') is-invalid @enderror" name="address" value="{{ old('address', $staffProfile->address) }}" />
                                        <div class="invalid-feedback">@error('address'){{ $message }}@enderror</div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Image</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="file" class="form-control form-control-sm @error('photo') is-invalid @enderror" id="staffProfilePhoto" name="photo" />
                                        <div class="invalid-feedback">@error('photo'){{ $message }}@enderror</div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0"></h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <img id="staffProfilePhotoPreview" src="{{ $staffProfile->photo_url }}" alt="{{ ucfirst($staffRole) }} Profile Image" class="rounded-circle p-1 bg-primary" width="80">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="submit" class="btn btn-primary btn-sm px-4" value="Update Profile" />
                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>

                        <!-- Bank details (read-only) -->
                        <div class="card mt-3">
                            <div class="card-body">
                                <h6 class="mb-3">Bank Details</h6>
                                <div class="row mb-3">
                                    <div class="col-sm-3"><h6 class="mb-0">Bank Name</h6></div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" class="form-control form-control-sm" value="{{ $staffProfileBank ? $staffProfileBank->bank_name : '' }}" readonly />
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3"><h6 class="mb-0">Account Title</h6></div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" class="form-control form-control-sm" value="{{ $staffProfileBank ? $staffProfileBank->account_title : '' }}" readonly />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3"><h6 class="mb-0">Account No</h6></div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" class="form-control form-control-sm" value="{{ $staffProfileBank ? $staffProfileBank->account_number : '' }}" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>

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
        // Field badalne par sirf usi field ka error saaf, submit se pehle sab saaf
        $('#staffProfileForm').on('submit', function() {
            $(this).find('.is-invalid').removeClass('is-invalid').next('.invalid-feedback').text('');
        });
        $(document).on('input change', '#staffProfileForm .is-invalid', function() {
            $(this).removeClass('is-invalid').next('.invalid-feedback').text('');
        });
        $('#staffProfilePhoto').change(function(e){
            var reader = new FileReader();
            reader.onload = function(e){
                $('#staffProfilePhotoPreview').attr('src',e.target.result);
            }
            reader.readAsDataURL(e.target.files['0']);
        });
    });
</script>
@endpush
