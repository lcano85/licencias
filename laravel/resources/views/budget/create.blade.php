@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Create Budget') }}</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form id="licensesAgreementsDetails" method="POST" action="{{ route('budget.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="commercialName" class="form-label">Commercial Name</label>
                                <select class="form-control" id="commercialName" name="commercialName" data-choices data-choices-sorting-false placeholder="{{ __('Select Commercial Name...') }}">
                                    <option value="">{{ __('Select Commercial Name...') }}</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" >
                                            {{ $client->commercialName ?: ($client->legalName ?: 'Client #' . $client->id) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('commercialName') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="user_type" class="form-label">{{ __('User Type') }}</label>
                                <input type="text" class="form-control" name="user_type" id="user_type" placeholder="{{ __('Enter user type') }}" readonly>
                                @error('user_type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="licensedConcept" class="form-label">{{ __('Licensed Concept') }}</label>
                                <input type="text" class="form-control" name="licensedConcept" id="licensedConcept" placeholder="{{ __('Enter licensed concept') }}" readonly>
                                @error('licensedConcept') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="licensedEnvironment" class="form-label">{{ __('Licensed Environment') }}</label>
                                <input type="text" class="form-control" name="licensedEnvironment" id="licensedEnvironment" placeholder="{{ __('Enter licensed environment') }}" readonly>
                                @error('licensedEnvironment') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="company" class="form-label">{{ __('Company') }}</label>
                                <input type="text" name="company" id="company" class="form-control" placeholder="{{ __('Enter company') }}">
                                @error('company') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="subTotal" class="form-label">{{ __('Sub Total') }}</label>
                                <input type="text" name="subTotal" id="subTotal" class="form-control" placeholder="{{ __('Enter sub total') }}">
                                @error('subTotal') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="vat" class="form-label">{{ __('Vat') }}</label>
                                <input type="text" name="vat" id="vat" class="form-control" placeholder="{{ __('Enter vat') }}">
                                @error('vat') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="total" class="form-label">{{ __('Total') }}</label>
                                <input type="text" name="total" id="total" class="form-control" placeholder="{{ __('Enter total') }}">
                                @error('total') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="concept" class="form-label">{{ __('Concept') }}</label>
                                <textarea class="form-control" name="concept" id="concept" placeholder="{{ __('Enter concept') }}"></textarea>
                                @error('concept') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="choices-single-no-sorting" class="form-label">{{ __('Condition') }}</label>
                                <select class="form-control" id="choices-single-no-sorting" name="condition" data-choices data-choices-sorting-false>
                                    <option value="" disabled>{{ __('Select condition...') }}</option>
                                    <option value="1">{{ __('Awaiting Purchase Order') }}</option>
                                    <option value="2">{{ __('Invoiced') }}</option>
                                    <option value="3">{{ __('New Agreement') }}</option>
                                    <option value="4">{{ __('Portfolio') }}</option>
                                </select>
                                @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="choices-single-no-sorting" class="form-label">{{ __('Status') }}</label>
                                <select class="form-control" id="choices-single-no-sorting" name="status" data-choices data-choices-sorting-false>
                                    <option value="" disabled>{{ __('Select status...') }}</option>
                                    <option value="1">{{ __('Pending') }}</option>
                                    <option value="2">{{ __('Invoiced') }}</option>
                                    <option value="3">{{ __('Discarded') }}</option>
                                </select>
                                @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Save Budget') }}</button>
                                <a href="{{ route('licenses-agreements') }}" class="btn btn-dark">Back</a>
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
    const userTypeInput = document.getElementById('user_type');
    const licensedConceptInput = document.getElementById('licensedConcept');
    const licensedEnvironmentInput = document.getElementById('licensedEnvironment');

    commercialSelect.addEventListener('change', function() {
        const clientId = this.value;
        if (clientId) {
            fetch(`/budget/get-user-type/${clientId}`)
                .then(response => response.json())
                .then(data => {
                    userTypeInput.value = data.userType || '';
                    licensedConceptInput.value = data.licensedConcept || '';
                    licensedEnvironmentInput.value = data.licensedEnvironment || '';
                })
                .catch(error => console.error('Error:', error));
        } else {
            userTypeInput.value = '';
        }
    });
});
</script>
@stop