@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Billing Edit') }}</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form id="licensesAgreementsDetails" method="POST" action="{{ route('billing-list.update', $billing->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="commercialName" class="form-label">Commercial Name</label>
                                <select class="form-control" id="commercialName" name="commercialName" data-choices data-choices-sorting-false placeholder="{{ __('Select Commercial Name...') }}">
                                    <option value="">{{ __('Select Commercial Name...') }}</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ (isset($billing) && $billing->commercialID == $client->id) ? 'selected' : '' }} >
                                            {{ $client->commercialName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('commercialName') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="user_type" class="form-label">{{ __('User Type') }}</label>
                                <input type="text" class="form-control" name="user_type" id="user_type" placeholder="{{ __('Enter user type') }}" readonly @if(isset($billing->user_type)) value="{{ $billing->user_type }}" @endif >
                                @error('user_type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="company" class="form-label">{{ __('Company') }}</label>
                                <input type="text" class="form-control" name="company" id="company" placeholder="{{ __('Enter company') }}" readonly @if(isset($billing->company)) value="{{ $billing->company }}" @endif >
                                @error('company') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="concept" class="form-label">{{ __('Concept') }}</label>
                                <input type="text" class="form-control" name="concept" id="concept" placeholder="{{ __('Enter concept') }}" readonly @if(isset($billing->concept)) value="{{ $billing->concept }}" @endif >
                                @error('concept') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="licensedConcept" class="form-label">{{ __('Licensed Concept') }}</label>
                                <input type="text" class="form-control" name="licensedConcept" id="licensedConcept" placeholder="{{ __('Enter licensed concept') }}" readonly @if(isset($billing->licensedConcept)) value="{{ $billing->licensedConcept }}" @endif >
                                @error('licensedConcept') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="licensedEnvironment" class="form-label">{{ __('Licensed Environment') }}</label>
                                <input type="text" class="form-control" name="licensedEnvironment" id="licensedEnvironment" placeholder="{{ __('Enter licensed environment') }}" readonly @if(isset($billing->licensedEnvironment)) value="{{ $billing->licensedEnvironment }}" @endif >
                                @error('licensedEnvironment') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="invoiceNumber" class="form-label">{{ __('Invoice No') }}</label>
                                <select class="form-control" id="invoiceNumber" name="invoiceNumber">
                                    <option value="">{{ __('Select Invoice No...') }}</option>
                                    @if(isset($invoice))
                                        <option value="{{ $invoice->id }}" selected>{{ $invoice->invoiceNumber }}</option>
                                    @endif
                                </select>
                                @error('invoiceNumber') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="invoiceDate" class="form-label">{{ __('Invoice Date') }}</label>
                                <input type="text" class="form-control" name="invoiceDate" id="invoiceDate" placeholder="{{ __('Enter invoice date') }}" readonly @if(isset($billing->licensedConcept)) value="{{ date('d-m-Y', strtotime($billing->invoiceDate)) }}" @endif>
                                @error('invoiceDate') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="criterion" class="form-label">{{ __('Criterion') }}</label>
                                <input type="text" class="form-control" name="criterion" id="criterion" placeholder="{{ __('Enter criterion') }}" readonly @if(isset($billing->criterion)) value="{{ $billing->criterion }}" @endif >
                                @error('criterion') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="period" class="form-label">{{ __('Period') }}</label>
                                <input type="text" class="form-control" name="period" id="period" placeholder="{{ __('Enter period') }}" readonly @if(isset($billing->periodPaid)) value="{{ $billing->periodPaid }}" @endif >
                                @error('period') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="periodDetails" class="form-label">{{ __('Period Details') }}</label>
                                <input type="text" class="form-control" name="periodDetails" id="periodDetails" readonly @if(isset($billing->paidPeriod)) value="{{ $billing->paidPeriod }}" @endif >
                                @error('periodDetails') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="subTotal" class="form-label">{{ __('Sub Total') }}</label>
                                <input type="text" class="form-control" name="subTotal" id="subTotal" placeholder="{{ __('Enter sub total') }}" readonly @if(isset($billing->subTotal)) value="{{ $billing->subTotal }}" @endif >
                                @error('subTotal') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="vat" class="form-label">{{ __('Vat') }}</label>
                                <input type="text" class="form-control" name="vat" id="vat" placeholder="{{ __('Enter vat') }}" readonly @if(isset($billing->vat)) value="{{ $billing->vat }}" @endif >
                                @error('vat') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="total" class="form-label">{{ __('Total') }}</label>
                                <input type="text" class="form-control" name="total" id="total" placeholder="{{ __('Enter sub total') }}" readonly @if(isset($billing->total)) value="{{ $billing->total }}" @endif >
                                @error('total') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="balance" class="form-label">{{ __('Balance') }}</label>
                                <input type="text" class="form-control" name="balance" id="balance" placeholder="{{ __('Enter balance') }}" @if(isset($billing->balance)) value="{{ $billing->balance }}" @endif >
                                @error('balance') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="supportingDocument" class="form-label">{{ __('Supporting Document') }}</label>
                                <select class="form-control" id="supportingDocument" name="supportingDocument" data-choices data-choices-sorting-false placeholder="{{ __('Select Supporting Document...') }}">
                                    <option value="">{{ __('Select Supporting Document...') }}</option>
                                    <option value="1" {{ (isset($billing) && $billing->supportingDocument == 1) ? 'selected' : '' }}>{{ __('Cash Receipt number') }}</option>
                                    <option value="2" {{ (isset($billing) && $billing->supportingDocument == 2) ? 'selected' : '' }}>{{ __('Credit Note No') }}</option>
                                </select>
                                @error('supportingDocument') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="documentDetail" class="form-label">{{ __('Document No') }}</label>
                                <input type="text" class="form-control" name="documentDetail" id="documentDetail" placeholder="{{ __('Enter document detail') }}" @if(isset($billing->documentDetail)) value="{{ $billing->documentDetail }}" @endif >
                                @error('documentDetail') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        
                        <div class="row mt-4">
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Update Billing') }}</button>
                                <a href="{{ route('billing-list') }}" class="btn btn-dark">Back</a>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const commercialSelect = document.getElementById('commercialName');
    const invoiceSelect = document.getElementById('invoiceNumber');
    const userTypeInput = document.getElementById('user_type');
    const licensedConceptInput = document.getElementById('licensedConcept');
    const licensedEnvironmentInput = document.getElementById('licensedEnvironment');
    const companyInput = document.getElementById('company');
    const conceptInput = document.getElementById('concept');
    commercialSelect.addEventListener('change', function() {
        const clientId = this.value;
        if (clientId) {
            fetch(`/billing-list/get-user-type/${clientId}`)
                .then(response => response.json())
                .then(data => {
                    userTypeInput.value = data.userType || '';
                    licensedConceptInput.value = data.licensedConcept || '';
                    licensedEnvironmentInput.value = data.licensedEnvironment || '';
                    companyInput.value = data.company || '';
                    conceptInput.value = data.concept || '';

                    if (data.invoices && Object.keys(data.invoices).length > 0) {
                        Object.entries(data.invoices).forEach(([id, number]) => {
                            const option = document.createElement('option');
                            option.value = id;
                            option.textContent = number;
                            invoiceSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        } else {
            userTypeInput.value = '';
            licensedConceptInput.value = '';
            licensedEnvironmentInput.value = '';
            companyInput.value = '';
            conceptInput.value = '';
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const invoiceSelect = document.getElementById('invoiceNumber');
    const invoiceDateInput = document.getElementById('invoiceDate');
    const invoiceCriterionInput = document.getElementById('criterion');
    const invoicePeriodInput = document.getElementById('period');
    const invoicePeriodDetailsInput = document.getElementById('periodDetails');
    const invoiceSubTotalInput = document.getElementById('subTotal');
    const invoiceVatInput = document.getElementById('vat');
    const invoiceTotalInput = document.getElementById('total');

    invoiceSelect.addEventListener('change', function() {
        const invoiceId = this.value;
        if (invoiceId) {
            fetch(`/billing-list/get-invoices/${invoiceId}`)
                .then(response => response.json())
                .then(data => {
                    invoiceDateInput.value = data.invoiceDate || '';
                    invoiceCriterionInput.value = data.criterion || '';
                    invoicePeriodInput.value = data.period || '';
                    invoicePeriodDetailsInput.value = data.periodDetails || '';
                    invoiceSubTotalInput.value = data.subTotal || '';
                    invoiceVatInput.value = data.vat || '';
                    invoiceTotalInput.value = data.total || '';
                })
                .catch(error => console.error('Error:', error));
        } else {
            userTypeInput.value = '';
        }
    });
});
</script>
@stop