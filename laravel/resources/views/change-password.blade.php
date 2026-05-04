@extends('layouts.app')
@section('styles')
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Change Password') }}</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form id="changePasswordForm" method="POST" action="{{ route('changePassword.update') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="userID" id="userID" value="{{ $user->id }}">

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="current_password" class="form-label">{{ __('Current Password') }}</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" placeholder="*******">
                                @error('current_password') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="new_password" class="form-label">{{ __('New Password') }}</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="*******">
                                @error('new_password') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="new_password_confirmation" class="form-label">{{ __('Confirm New Password') }}</label>
                                <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" placeholder="*******">
                                @error('new_password_confirmation') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Change Password') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script>
$(document).ready(function() {
    $('#changePasswordForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: "{{ route('changePassword.update') }}",
            method: "POST",
            data: $(this).serialize(),
            beforeSend: function() {
                $('button[type="submit"]').prop('disabled', true).text("{{ __('Processing...') }}");
            },
            success: function(response) {
                $('button[type="submit"]').prop('disabled', false).text("{{ __('Change Password') }}");

                if (response.status === true) {
                    Swal.fire({
                        icon: 'success',
                        title: "{{ __('Success') }}",
                        text: response.message,
                    });

                    $('#changePasswordForm')[0].reset();
                } else {
                    if (response.errors) {
                        let errorMsg = '';
                        $.each(response.errors, function(key, value) {
                            errorMsg += value[0] + '<br>';
                        });
                        Swal.fire({
                            icon: 'error',
                            title: "{{ __('Validation Error') }}",
                            html: errorMsg,
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: "{{ __('Error') }}",
                            text: response.message,
                        });
                    }
                }
            },
            error: function(xhr) {
                $('button[type="submit"]').prop('disabled', false).text("{{ __('Change Password') }}");
                Swal.fire({
                    icon: 'error',
                    title: "{{ __('Error') }}",
                    text: "{{ __('Something went wrong!') }}",
                });
            }
        });
    });
});
</script>
@stop
