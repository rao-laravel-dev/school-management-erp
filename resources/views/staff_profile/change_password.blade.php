@extends($current_layout)
@section('content')
 <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Change Password</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Change Password</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ $updateRoute }}" method="POST" autocomplete="off">
                                @csrf

                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <div class="input-group input-group-sm">
                                        <input type="password" name="current_password" id="current_password"
                                            class="form-control form-control-sm @error('current_password') is-invalid @enderror"
                                            placeholder="Enter current password" autocomplete="current-password">
                                        <button type="button" class="btn btn-outline-secondary btn-sm toggle-password" data-target="#current_password" tabindex="-1">
                                            <i class="bx bx-show"></i>
                                        </button>
                                        @error('current_password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <div class="input-group input-group-sm">
                                        <input type="password" name="password" id="password"
                                            class="form-control form-control-sm @error('password') is-invalid @enderror"
                                            placeholder="Enter new password" autocomplete="new-password">
                                        <button type="button" class="btn btn-outline-secondary btn-sm toggle-password" data-target="#password" tabindex="-1">
                                            <i class="bx bx-show"></i>
                                        </button>
                                        @error('password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">Minimum 8 characters.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                    <div class="input-group input-group-sm">
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                            class="form-control form-control-sm" placeholder="Confirm new password"
                                            autocomplete="new-password">
                                        <button type="button" class="btn btn-outline-secondary btn-sm toggle-password" data-target="#password_confirmation" tabindex="-1">
                                            <i class="bx bx-show"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm px-4">Update Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('scripts')
<script>
$(function () {
    // Eye toggle: password <-> text
    $('.toggle-password').on('click', function () {
        var input = $($(this).data('target'));
        var icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bx-show').addClass('bx-hide');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bx-hide').addClass('bx-show');
        }
    });

    // Typing par us field ki error state clear (New Password type ho to Confirm bhi clear)
    function clearFieldError(input) {
        input.removeClass('is-invalid');
        input.closest('.input-group').find('.invalid-feedback').remove();
    }
    $('#current_password, #password, #password_confirmation').on('input', function () {
        clearFieldError($(this));
        if (this.id === 'password') {
            clearFieldError($('#password_confirmation'));
        }
        if (this.id === 'password_confirmation') {
            clearFieldError($('#password'));
        }
    });

    // Validation error par ek error toast (field errors inputs ke neeche bhi dikhte hain)
    @if($errors->any())
        toastr.error(@json($errors->first()));
    @endif
});
</script>
@endpush
