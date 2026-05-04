@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Permission') }}</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form id="roleDetails" method="POST" action="{{ route('permission.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="permission" class="form-label">{{ __('Permission Name') }}</label>
                                <input type="text" class="form-control" name="permission" id="permission" required>
                                @error('permission') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Save Permission') }}</button>
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
