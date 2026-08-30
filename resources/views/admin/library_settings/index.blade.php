@extends($current_layout)

@section('title', 'Library Settings')

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Library', 'url' => '#'],
    ['label' => 'Settings', 'url' => route('library_settings.index')],
]" />

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Library Settings</h5>
                </div>
                <div class="card-body">
                    <form id="settingsForm">
                        @csrf

                        <div class="mb-3">
                            <label for="max_issue_days" class="form-label form-label-sm">
                                Max Issue Days <span class="text-danger">*</span>
                            </label>
                            <input type="number" min="1" name="max_issue_days" id="max_issue_days"
                                class="form-control form-control-sm" value="{{ $settings->max_issue_days }}">
                            <span class="text-danger error-text" data-error="max_issue_days"></span>
                        </div>

                        <div class="mb-3">
                            <label for="fine_per_day" class="form-label form-label-sm">
                                Fine Per Day <span class="text-danger">*</span>
                            </label>
                            <input type="number" step="0.01" min="0" name="fine_per_day" id="fine_per_day"
                                class="form-control form-control-sm" value="{{ $settings->fine_per_day }}">
                            <span class="text-danger error-text" data-error="fine_per_day"></span>
                        </div>

                        <div class="mb-3">
                            <label for="max_books_per_student" class="form-label form-label-sm">
                                Max Books (Student) <span class="text-danger">*</span>
                            </label>
                            <input type="number" min="1" name="max_books_per_student" id="max_books_per_student"
                                class="form-control form-control-sm" value="{{ $settings->max_books_per_student }}">
                            <span class="text-danger error-text" data-error="max_books_per_student"></span>
                        </div>

                        <div class="mb-3">
                            <label for="max_books_per_staff" class="form-label form-label-sm">
                                Max Books (Staff) <span class="text-danger">*</span>
                            </label>
                            <input type="number" min="1" name="max_books_per_staff" id="max_books_per_staff"
                                class="form-control form-control-sm" value="{{ $settings->max_books_per_staff }}">
                            <span class="text-danger error-text" data-error="max_books_per_staff"></span>
                        </div>

                        <button type="submit" class="btn btn-sm btn-success w-100" id="btnSubmit">
                            Update Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {
        const updateUrl = "{{ route('library_settings.update') }}";

        function clearErrors() {
            $('.error-text').text('');
            $('.form-control').removeClass('is-invalid');
        }

        $('#settingsForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors();

            $('#btnSubmit').prop('disabled', true);

            $.ajax({
                url: updateUrl,
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    toastr.success(res.message || 'Settings updated successfully.');
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach(field => {
                            $(`[data-error="${field}"]`).text(errors[field][0]);
                            $(`#${field}`).addClass('is-invalid');
                        });
                        toastr.error('Please check the highlighted field(s).');
                    } else {
                        toastr.error('Something went wrong. Please try again.');
                    }
                },
                complete: function() {
                    $('#btnSubmit').prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush