@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Validation Report') }}</h4>
            </div>
            <div class="card-body">
                @include('budget.validations.modal-content')
            </div>
        </div>
    </div>
</div>
@endsection
