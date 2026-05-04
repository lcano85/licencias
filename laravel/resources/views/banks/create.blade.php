@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
<style>
    .permissionCheckBox .form-check{
        display: block;
        margin-right: 10px;
        margin-bottom: 5px;
    }
    .permissionCheckBox {
        column-count: 4;
    }
</style>
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Bank') }}</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form id="roleDetails" method="POST" action="{{ route('bank.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="bank_name" class="form-label">{{ __('Bank Name') }}</label>
                                <input type="text" class="form-control" name="bank_name" id="bank_name" required>
                                @error('bank_name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="bank_code" class="form-label">{{ __('Bank code') }}</label>
                                <input type="text" class="form-control" name="bank_code" id="bank_code" required>
                                @error('bank_code') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Save Bank') }}</button>
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