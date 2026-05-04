@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
<link href="{{ asset('admin/css/historydata.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('admin/css/sidebar-slider.css') }}" rel="stylesheet" type="text/css" />
<style>
    .commentTypes input[type="radio"] {
      display: none !important;
    }
    .commentTypes input[type="radio"]:checked + label span {
      transform: scale(1.25);
    }
    .commentTypes label {
      display: inline-block;
      margin-right: 15px;
      cursor: pointer;
    }
    .commentTypes label:hover span {
      transform: scale(1.25);
    }
    .commentTypes label span {
      display: block;
      width: 100%;
      height: 100%;
      transition: transform 0.2s ease-in-out;
      padding: 5px;
    }
    .attachementFileComment {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
</style>
@stop

@section('content')
<div class="loader--ripple" style="display: none;">
    <div></div><div></div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Billing View') }}</h4>
                <a href="{{ route('billing-list') }}" class="btn btn-secondary btn-sm">Back to Billing</a>
            </div>
            <div class="card-body">
                <div class="live-preview" style="padding-left: 25px;padding-right: 25px;">
                    <div class="row gy-4">
                        <div class="col-xxl-6 col-md-6">
                            <label for="commercialName" class="form-label"><strong>Commercial Name: </strong></label>
                            <p>{{ $billing->commercialName }}</p>
                        </div>

                        <div class="col-xxl-6 col-md-6">
                            <label for="user_type" class="form-label"><strong>{{ __('User Type:') }}</strong></label>
                            <p>{{ $billing->user_type }}</p>
                        </div>
                    </div>

                    <div class="row gy-4">
                        <div class="col-xxl-6 col-md-6">
                            <label for="company" class="form-label"><strong>{{ __('Company:') }}</strong></label>
                            <p>{{ $billing->company }}</p>
                        </div>

                        <div class="col-xxl-6 col-md-6">
                            <label for="concept" class="form-label"><strong>{{ __('Concept:') }}</strong></label>
                            <p>{{ $billing->concept }}</p>
                        </div>
                    </div>

                    <div class="row gy-4">
                        <div class="col-xxl-6 col-md-6">
                            <label for="licensedConcept" class="form-label"><strong>{{ __('Licensed Concept:') }}</strong></label>
                            <p>{{ $billing->licensedConcept }}</p>
                        </div>

                        <div class="col-xxl-6 col-md-6">
                            <label for="licensedEnvironment" class="form-label"><strong>{{ __('Licensed Environment:') }}</strong></label>
                            <p>{{ $billing->licensedEnvironment }}</p>
                        </div>
                    </div>

                    <div class="row gy-4">
                        <div class="col-xxl-6 col-md-6">
                            <label for="invoiceNumber" class="form-label"><strong>{{ __('Invoice No:') }}</strong></label>
                            <p>{{ $billing->invoiceNumber }}</p>
                        </div>

                        <div class="col-xxl-6 col-md-6">
                            <label for="invoiceDate" class="form-label"><strong>{{ __('Invoice Date:') }}</strong></label>
                            <p>{{ date('d-m-Y', strtotime($billing->invoiceDate)) }}</p>
                        </div>
                    </div>

                    <div class="row gy-4">
                        <div class="col-xxl-6 col-md-6">
                            <label for="criterion" class="form-label"><strong>{{ __('Criterion:') }}</strong></label>
                            <p>{{ $billing->criterion }}</p>
                        </div>

                        <div class="col-xxl-6 col-md-6">
                            <label for="periodPaid" class="form-label"><strong>{{ __('Period Paid:') }}</strong></label>
                            <p>{{ $billing->periodPaid }}</p>
                        </div>
                    </div>

                    <div class="row gy-4">
                        <div class="col-xxl-6 col-md-6">
                            <label for="paidPeriod" class="form-label"><strong>{{ __('Period Details:') }}</strong></label>
                            <p>{{ $billing->paidPeriod }}</p>
                        </div>

                        <div class="col-xxl-6 col-md-6">
                            <label for="subTotal" class="form-label"><strong>{{ __('Sub Total:') }}</strong></label>
                            <p>₱ {{ $billing->subTotal }}</p>
                        </div>
                    </div>

                    <div class="row gy-4">
                        <div class="col-xxl-6 col-md-6">
                            <label for="vat" class="form-label"><strong>{{ __('Vat:') }}</strong></label>
                            <p>{{ $billing->vat }}%</p>
                        </div>

                        <div class="col-xxl-6 col-md-6">
                            <label for="total" class="form-label"><strong>{{ __('Total:') }}</strong></label>
                            <p>₱ {{ $billing->total }}</p>
                        </div>
                    </div>

                    <div class="row gy-4">
                        <div class="col-xxl-6 col-md-6">
                            <label for="balance" class="form-label"><strong>{{ __('Balance:') }}</strong></label>
                            <p>₱ {{ $billing->balance }}</p>
                        </div>

                        <div class="col-xxl-6 col-md-6">
                            <label for="supportingDocument" class="form-label"><strong>{{ __('Supporting Document:') }}</strong></label>
                            @if($billing->supportingDocument == 1)
                                <p>{{ __('Cash Receipt number') }}</p>
                            @elseif($billing->supportingDocument == 2)
                                <p>{{ __('Credit Note No') }}</p>
                            @else
                                <p>{{ __('N/A') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="row gy-4">
                        <div class="col-xxl-6 col-md-6">
                            <label for="documentDetail" class="form-label"><strong>{{ __('Document No:') }}</strong></label>
                            <p>{{ $billing->documentDetail }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@stop