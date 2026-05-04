@extends('layouts.app')
@section('styles')
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Personal Information') }}</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form id="projectDetails" method="POST" action="{{ route('user-profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="userID" id="userID" value="{{ $user->id }}">
                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="name" class="form-label">{{ __('User Name') }}</label>
                                <input type="text" class="form-control" name="name" id="name" required placeholder="{{ __('Enter user name') }}"  @if(!empty($user->name)) value="{{$user->name}}" @endif>
                                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="email" class="form-label">{{ __('Email') }}</label>
                                <input type="email" class="form-control" name="email" id="email" required placeholder="{{ __('Enter email') }}" @if(!empty($user->email)) value="{{$user->email}}" @endif>
                                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="first_name" class="form-label">{{ __('First Name') }}</label>
                                <input type="text" class="form-control" name="first_name" id="first_name" placeholder="{{ __('Enter first name') }}"  @if(!empty($user->first_name)) value="{{$user->first_name}}" @endif>
                                @error('first_name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="last_name" class="form-label">{{ __('Last Name') }}</label>
                                <input type="text" class="form-control" name="last_name" id="last_name" placeholder="{{ __('Enter last name') }}" @if(!empty($user->last_name)) value="{{$user->last_name}}" @endif>
                                @error('last_name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="phone_number" class="form-label">{{ __('Phone Number') }}</label>
                                <input type="text" class="form-control" name="phone_number" id="phone_number" placeholder="{{ __('Enter phone number') }}"  @if(!empty($user->phone_number)) value="{{$user->phone_number}}" @endif>
                                @error('phone_number') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="city" class="form-label">{{ __('City') }}</label>
                                <input type="text" class="form-control" name="city" id="city" placeholder="{{ __('Enter city name') }}" @if(!empty($user->city)) value="{{$user->city}}" @endif>
                                @error('city') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="state" class="form-label">{{ __('State') }}</label>
                                <input type="text" class="form-control" name="state" id="state" placeholder="{{ __('Enter state name') }}"  @if(!empty($user->state)) value="{{$user->state}}" @endif>
                                @error('state') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="country" class="form-label">{{ __('Country') }}</label>
                                <input type="text" class="form-control" name="country" id="country" placeholder="{{ __('Enter country name') }}" @if(!empty($user->country)) value="{{$user->country}}" @endif>
                                @error('country') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="address" class="form-label">{{ __('Address') }}</label>
                                <textarea class="form-control" name="address" id="address" placeholder="{{ __('Enter address') }}">@if(!empty($user->address)){{$user->address}}@endif</textarea>
                                @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="country" class="form-label">{{ __('Short Description') }}</label>
                                <textarea class="form-control" name="short_description" id="short_description" placeholder="{{ __('Enter short description') }}">@if(!empty($user->short_description)){{$user->short_description}}@endif</textarea>
                                @error('country') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="photo" class="form-label">{{ __('Photo') }}</label>
                                <input type="file" class="form-control" name="photo" id="photo">
                                @if(!empty($user->photo))
                                    <div style="margin-top: 10px;">
                                        <img src="{{ asset('uploads/profile_photos/' . $user->photo) }}" alt="User Photo" style="width: 100px;border: 3px solid #ddd;">
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Update Profile') }}</button>
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
@stop
