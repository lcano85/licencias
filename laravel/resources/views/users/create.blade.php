@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')

@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('User') }}</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form id="roleDetails" method="POST" action="{{ route('user.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="name" class="form-label">{{ __('Username') }}</label>
                                <input type="text" class="form-control" name="name" id="name" required>
                                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="email" class="form-label">{{ __('Email') }}</label>
                                <input type="text" class="form-control" name="email" id="email" required>
                                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="roles" class="form-label">{{ __('Roles') }}</label>
                                <select class="form-control" name="roles" id="roles">
                                    <option value="">{{ __('Please select role') }}</option>
                                    @if(!empty($roles))
                                        @foreach($roles as $value)
                                            <option value="{{ $value->name }}">{{ \App\Support\UiText::role($value->name) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('role') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Save User') }}</button>
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
