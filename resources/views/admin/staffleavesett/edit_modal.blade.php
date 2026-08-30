<form action="{{ route('staffleavesett.update', $teacher->id) }}" method="POST" id="updateLeaveForm">
    @csrf
    @method('PUT')

    <div class="row">
        @foreach($teacher->leaveSettings as $setting)
        <div class="col-md-6 mb-3">
            <label>{{ ucfirst($setting->leave_type) }}</label>
            <input type="number" name="days[{{ $setting->id }}]"
                value="{{ $setting->total_days }}" class="form-control">
        </div>
        @endforeach
    </div>

    <button type="submit" class="btn btn-primary">Update All Quotas</button>
</form>

<script>
    $('#updateLeaveForm').on('submit', function(e) {
        e.preventDefault(); // Page ko refresh hone se rokein

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // 1. Success Message
                toastr.success(response.success);

                // 2. Modal band karein
                $('#editModal').modal('hide');

                // 3. 1.5 second ka wait karein taake user message parh sake, phir reload karein
                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                // 4. Server se aaye huye specific error message ko catch karein
                let errorMessage = xhr.responseJSON && xhr.responseJSON.error ?
                    xhr.responseJSON.error :
                    'Error updating records!';

                toastr.error(errorMessage);
            }
        });
    });
</script>