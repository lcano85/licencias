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
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Role Edit') }}</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form id="roleDetails" method="POST" action="{{ route('role.update', $role->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="role" class="form-label">{{ __('Role Name') }}</label>
                                <input type="text" class="form-control" name="role" id="role" required @if(!empty($role->name)) value="{{$role->name}}" @endif>
                                @error('role') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="role" class="form-label">{{ __('Permissions') }}</label>
                            </div>
                            <div class="col-xxl-12 col-md-12 permissionCheckBox" style="margin-top: 0px;">
                                @foreach($permissions as $value)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="formCheck{{$value->id}}" name="permission[{{$value->id}}]" value="{{$value->id}}" {{ in_array($value->id, $rolePermissions) ? 'checked' : ''}}>
                                        <label class="form-check-label" for="formCheck{{$value->id}}">{{ \App\Support\UiText::permission($value->name) }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Update Role') }}</button>
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
