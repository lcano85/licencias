@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Client') }}</h4>
            </div>
            <div class="card-body">
                <ul class="nav nav-pills mb-2">
                    <li class="nav-item">
                        <a href="#basicInformation" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                            <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
                            <span class="d-none d-sm-block">{{ __('Basic Information') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#contactInformation" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                            <span class="d-block d-sm-none"><i class="bx bx-user"></i></span>
                            <span class="d-none d-sm-block">{{ __('Contact Information') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#additionalInformation" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                            <span class="d-block d-sm-none"><i class="bx bx-envelope"></i></span>
                            <span class="d-none d-sm-block">{{ __('Additional Information') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#statusInformation" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                            <span class="d-block d-sm-none"><i class="bx bx-envelope"></i></span>
                            <span class="d-none d-sm-block">{{ __('Status Information') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#accountingManagement" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                            <span class="d-block d-sm-none"><i class="bx bx-envelope"></i></span>
                            <span class="d-none d-sm-block">{{ __('Accounting Management') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#documentRepository" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                            <span class="d-block d-sm-none"><i class="bx bx-envelope"></i></span>
                            <span class="d-none d-sm-block">{{ __('Document Repository') }}</span>
                        </a>
                    </li>
                </ul>
                <form id="clientDetails" method="POST" action="{{ route('client.update', $client->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="tab-content pt-2 text-muted">
                        <div class="tab-pane show active" id="basicInformation">
                            <div class="row gy-4 mb-2 justify-content-center">
                                <div class="col-xxl-11">
                                    <div class="row gy-4 mb-2">
                                        <div class="col-xxl-6 col-md-6">
                                            <label for="commercialName" class="form-label">Commercial Name</label>
                                            <input type="text" class="form-control" name="commercialName" id="commercialName" required placeholder="{{ __('Enter Commercial Name') }}" value="{{ old('commercialName', $client->commercialName) }}">
                                            @error('commercialName') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="col-xxl-6 col-md-6">
                                            <label for="legalName" class="form-label">{{ __('Legal Name') }}</label>
                                            <input type="text" class="form-control" name="legalName" id="legalName" placeholder="{{ __('Enter Legal Name') }}" value="{{ old('legalName', $client->legalName) }}" >
                                            @error('legalName') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="row gy-4 mb-2">
                                        <div class="col-xxl-6 col-md-6">
                                            <label for="nit" class="form-label">{{ __('NIT') }}</label>
                                            <input type="text" class="form-control" name="nit" id="nit" placeholder="{{ __('Enter NIT') }}" value="{{ old('nit', $client->nit) }}" >
                                            @error('nit') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>

                                        @if(isset($categories))
                                            <div class="col-xxl-6 col-md-6">
                                                <label for="categoryID" class="form-label">{{ __('Category') }}</label>
                                                <select class="form-control" name="categoryID" id="categoryID" required>
                                                    <option>{{ __('Please Select Category') }}</option>
                                                    @foreach($categories as $value)
                                                        <option value="{{ $value->id }}" {{ $client->categoryID == $value->id ? 'selected' : '' }}>
                                                            {{ $value->category_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="text-danger error-text roles_error"></span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="row gy-4 mb-2">
                                        <div class="col-xxl-6 col-md-6">
                                            <label for="subcategoryID" class="form-label">{{ __('Sub Category') }}</label>
                                            <select class="form-control" name="subcategoryID" id="subcategoryID" >
                                                <option value="">{{ __('Please Select Sub Category') }}</option>
                                                @foreach($subcategories as $sub)
                                                    <option value="{{ $sub->id }}" {{ $client->subcategoryID == $sub->id ? 'selected' : '' }}>
                                                        {{ $sub->subcategory_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger error-text subcategory_error"></span>
                                        </div>

                                        @if(isset($useTypes))
                                            <div class="col-xxl-6 col-md-6">
                                                <label for="useTypes" class="form-label">{{ __('Use Types') }}</label>
                                                <select class="form-control" name="useTypes" id="useTypes" required>
                                                    <option>{{ __('Please Select Use Types') }}</option>
                                                    @foreach($useTypes as $types)
                                                        <option value="{{ $types->id }}" {{ $client->useTypes == $types->id ? 'selected' : '' }}>
                                                            {{ $types->use_types_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="text-danger error-text roles_error"></span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="row gy-4 mb-2">
                                        <div class="col-xxl-6 col-md-6">
                                            <label for="website_link" class="form-label">{{ __('Website Link') }}</label>
                                            <input type="text" class="form-control" name="website_link" id="website_link" value="{{ old('website_link', $client->website_link) }}">
                                        </div>

                                        @if(isset($useTypes))
                                            <div class="col-xxl-6 col-md-6">
                                                <label for="client_status" class="form-label">{{ __('Status') }}</label>
                                                <select class="form-control" name="client_status" id="client_status" required>
                                                    <option>{{ __('Please Select Status') }}</option>
                                                    @foreach($biStatus as $value)
                                                        <option value="{{ $value->id }}" {{ $client->client_status == $value->id ? 'selected' : '' }}>
                                                            {{ $value->status_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="text-danger error-text roles_error"></span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="row gy-4 mb-2">
                                        <div class="col-xxl-12 col-md-12">
                                            <label for="annotations" class="form-label">{{ __('Annotations') }}</label>
                                            <textarea class="form-control" name="annotations" id="annotations">@if(!empty($client->annotations)){{$client->annotations}}@endif</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane" id="contactInformation">
                            <div class="row gy-4 mb-2 justify-content-center">
                                <div class="col-xxl-11">
                                    <div class="perTitle" style="border-bottom: 1px solid #ddd;padding-bottom: 10px;margin-bottom: 20px;display: flex;justify-content: space-between;gap: 10px;">
                                        <h4>{{ __('Personal contact data') }}</h4>
                                        <button type="button" class="btn btn-success addPersonalContactData">+</button>
                                    </div>
                                    <div id="personalContactData">
                                        @foreach ($personalContacts as $index => $contact)
                                            <div class="contactDataP">
                                                <div class="row gy-4 mb-2">
                                                    <div class="col-xxl-6 col-md-6">
                                                        <label class="form-label">{{ __('Full Name') }}</label>
                                                        <input type="text" class="form-control" 
                                                               name="personalContactData[{{ $index }}][fullName]" 
                                                               value="{{ $contact['fullName'] ?? '' }}" 
                                                               placeholder="{{ __('Enter full name') }}">
                                                    </div>

                                                    <div class="col-xxl-6 col-md-6">
                                                        <label class="form-label">{{ __('Position') }}</label>
                                                        <input type="text" class="form-control" 
                                                               name="personalContactData[{{ $index }}][designation]" 
                                                               value="{{ $contact['designation'] ?? '' }}" 
                                                               placeholder="{{ __('Enter position') }}">
                                                    </div>
                                                </div>
                                                <div class="row gy-4 mb-2">
                                                    <div class="col-xxl-4 col-md-4">
                                                        <label class="form-label">{{ __('Email') }}</label>
                                                        <input type="text" class="form-control"
                                                               name="personalContactData[{{ $index }}][contactEmail]"
                                                               value="{{ is_array($contact['contactEmail'] ?? '') ? implode('; ', $contact['contactEmail']) : ($contact['contactEmail'] ?? '') }}"
                                                               placeholder="{{ __('Enter emails separated by \';\'') }}">
                                                    </div>
                                                    <div class="col-xxl-4 col-md-4">
                                                        <label class="form-label">{{ __('Contact Number') }}</label>
                                                        <input type="text" class="form-control" 
                                                               name="personalContactData[{{ $index }}][contactNumber]" 
                                                               value="{{ $contact['contactNumber'] ?? '' }}" 
                                                               placeholder="{{ __('Enter phone number') }}">
                                                    </div>
                                                    <div class="col-xxl-3 col-md-3">
                                                        <label for="cnt_company_date" class="form-label">{{ __('Persnal Contact Date') }}</label>
                                                        <input type="date" class="form-control" name="personalContactData[{{ $index }}][companyDate]" id="cnt_company_date" placeholder="{{ __('Persnal Contact Date') }}" value="{{ $contact['companyDate'] ?? '' }}">
                                                    </div>
                                                    <div class="col-xxl-1 col-md-1">
                                                        <label class="form-label">{{ __('Remove') }}</label>
                                                        <button type="button" class="btn btn-danger removePersonalContactData">-</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <h4 style="border-bottom: 1px solid #ddd;padding-bottom: 10px;margin-bottom: 20px;margin-top: 30px;">{{ __('Basic company data') }}</h4>
                                    <!-- <div class="row gy-4 mb-2">
                                        <div class="col-xxl-12 col-md-12">
                                            <label for="judicialNotificationAddress" class="form-label">{{ __('Judicial Notification Address') }}</label>
                                            <textarea class="form-control" name="judicialNotificationAddress" id="judicialNotificationAddress" placeholder="{{ __('Enter Judicial Notification Address') }}">@if(!empty($client->judicialNotificationAddress)){{$client->judicialNotificationAddress}}@endif</textarea>
                                        </div>
                                    </div> -->

                                    <div class="row gy-4 mb-2">
                                        <div class="col-xxl-12 col-md-12">
                                            <label class="form-label">{{ __('Judicial Notification') }}</label>
                                            <div id="contacts-wrapper">
                                                @php
                                                    $judicials = json_decode($client->judicialNotification, true) ?? [];
                                                @endphp
                                                @foreach($judicials as $i => $jn)
                                                    <div class="d-flex mb-2" style="display: grid !important;grid-template-columns: repeat(3, 1fr);gap: 10px;">
                                                        <input type="email" name="judicialNotification[{{ $i }}][email]" class="form-control me-2"
                                                               placeholder="{{ __('Judicial Notification Emails') }}" value="{{ $jn['email'] ?? '' }}">
                                                        <input type="text" name="judicialNotification[{{ $i }}][phone]" class="form-control me-2"
                                                               placeholder="{{ __('Judicial Notification Phone') }}" value="{{ $jn['phone'] ?? '' }}">
                                                        <input type="text" name="judicialNotification[{{ $i }}][eb_email]" class="form-control me-2"
                                                               placeholder="{{ __('Electronic Billing Email') }}" value="{{ $jn['eb_email'] ?? '' }}">
                                                        <input type="text" name="judicialNotification[{{ $i }}][information]" class="form-control me-2"
                                                               placeholder="{{ __('Contact Person Information') }}" value="{{ $jn['information'] ?? '' }}">
                                                        <input type="text" name="judicialNotification[{{ $i }}][city]" class="form-control me-2" placeholder="{{ __('Judicial Notification City') }}" value="{{ $jn['city'] ?? '' }}">
                                                        <input type="text" name="judicialNotification[{{ $i }}][country]" class="form-control me-2" placeholder="{{ __('Judicial Notification Country') }}" value="{{ $jn['country'] ?? '' }}">
                                                        <input type="text" name="judicialNotification[{{ $i }}][address]" class="form-control me-2" placeholder="{{ __('Judicial Notification Address') }}" value="{{ $jn['address'] ?? '' }}">
                                                        <input type="date" name="judicialNotification[{{ $i }}][personalContactDate]" class="form-control me-2" placeholder="{{ __('Company Date') }}" value="{{ $jn['personalContactDate'] ?? '' }}">
                                                        @if($i == 0)
                                                            <button type="button" class="btn btn-success addJudicialNotification" style="width: 50px;">+</button>
                                                        @else
                                                            <button type="button" class="btn btn-danger removeJudicialNotification" style="width: 50px;">-</button>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane" id="additionalInformation">
                            <h4 style="border-bottom: 1px solid #ddd;padding-bottom: 5px;margin-bottom: 20px;">{{ __('Characteristics') }}</h4>
                            <div class="row gy-4 mb-2">
                                <div class="col-xxl-6 col-md-6">
                                    <label for="companySize" class="form-label">{{ __('Company Size') }}</label>
                                    <input type="text" class="form-control" name="companySize" id="companySize" placeholder="{{ __('Enter Company Size') }}" value="{{ old('companySize', $client->companySize) }}" >
                                    @error('companySize') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-xxl-6 col-md-6">
                                    <label for="companyType" class="form-label">{{ __('Company Type') }}</label>
                                    <input type="text" class="form-control" name="companyType" id="companyType" placeholder="{{ __('Enter Company Type') }}" value="{{ old('companyType', $client->companyType) }}" >
                                    @error('companyType') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <h4 style="border-bottom: 1px solid #ddd;padding-bottom: 5px;margin-bottom: 20px;margin-top: 20px;">{{ __('Financial Information') }}</h4>
                            <div class="row gy-4 mb-2">
                                <div class="col-xxl-6 col-md-6">
                                    <label for="annualIncome" class="form-label">{{ __('Annual Income') }}</label>
                                    <input type="text" class="form-control" name="annualIncome" id="annualIncome" placeholder="{{ __('Enter Annual Income') }}" value="{{ old('annualIncome', $client->annualIncome) }}" >
                                    @error('annualIncome') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="statusInformation">
                            <div class="row gy-4 mb-2 justify-content-center">
                                <div class="col-md-11">
                                    <label for="happenClient" class="form-label">{{ __('What happen with this client?') }}</label>
                                    <select class="form-control" name="happenClient" id="happenClient" required>
                                        <option>{{ __('Please Select Option') }}</option>
                                        <option value="1" {{ $client->happenClient == 1 ? 'selected' : '' }}>Preselection</option>
                                        <option value="2" {{ $client->happenClient == 2 ? 'selected' : '' }}>To contact</option>
                                        <option value="3" {{ $client->happenClient == 3 ? 'selected' : '' }}>In Conversation</option>
                                        <option value="4" {{ $client->happenClient == 4 ? 'selected' : '' }}>Licenses</option>
                                        <option value="5" {{ $client->happenClient == 5 ? 'selected' : '' }}>In Legal Procedures</option>
                                        <option value="6" {{ $client->happenClient == 6 ? 'selected' : '' }}>Discarded</option>
                                    </select>
                                    <span class="text-danger error-text roles_error"></span>
                                </div>
                                <div class="col-md-11 mt-2">
                                    <label for="licenseInformation" class="form-label">{{ __('License Information') }}</label>
                                    <textarea class="form-control" name="licenseInformation" id="licenseInformation">{{ old('licenseInformation', $client->licenseInformation) }}</textarea>
                                </div>
                                <div class="col-md-11 mt-2">
                                    <label for="licenseAttachement" class="form-label">{{ __('License Attachement') }}</label>
                                    <input type="file" name="licenseAttachement" class="form-control" id="licenseAttachement" >
                                    @if(!empty($client->licenseAttachement))
                                        <div class="mt-2">
                                            @if($client->licenseAttachement)
                                            @php $folderID = str_replace('#MT-', '', $client->client_acc_ID); @endphp
                                                <img src="{{ asset('uploads/clients/' . $folderID . '/licenses/' . $client->licenseAttachement) }}" alt="License Attachment" style="width: 100px; height: auto; border: 1px solid #ddd; padding: 2px;">
                                            @else
                                                <span>{{ __('No license uploaded') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="accountingManagement">
                            <div class="row gy-4 mb-2 justify-content-center">
                                <div class="col-md-11">
                                    <label for="bankName" class="form-label">{{ __('Bank Name') }}</label>
                                    <input type="text" class="form-control" name="bankName" id="bankName" value="{{ old('bankName', $client->bankName) }}">
                                </div>
                                <div class="col-md-11 mt-2">
                                    <label for="bankAccountNumber" class="form-label">{{ __('Bank Account Number') }}</label>
                                    <input type="text" name="bankAccountNumber" class="form-control" id="bankAccountNumber" value="{{ old('bankAccountNumber', $client->bankAccountNumber) }}">
                                </div>
                                <div class="col-md-11 mt-2">
                                    <label for="bankCode" class="form-label">{{ __('Bank Code') }}</label>
                                    <input type="text" name="bankCode" class="form-control" id="bankCode" value="{{ old('bankCode', $client->bankCode) }}">
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="documentRepository">
                            <div class="row gy-4 mb-2">
                                <div class="col-xxl-11 col-md-11">
                                    <label for="commercialName" class="form-label">Document Repository</label>
                                </div>
                                <div id="addDocumentRepository" style="margin-top: 5px;">
                                    @php
                                        $documentRepository = json_decode($client->documentRepository, true) ?? [];
                                    @endphp
                                    @if(isset($documentRepository))
                                        @foreach($documentRepository as $i => $dor)
                                            <div class="mb-2">
                                                <div class="mb-2 col-md-11">
                                                    <input type="text" name="documentName[{{ $i }}][name]" class="form-control me-2" placeholder="{{ __('Document Name') }}" value="{{ $dor['name'] ?? '' }}">
                                                </div>
                                                <div class="mb-2 col-md-11">
                                                    @if(!empty($dor['file']))
                                                        <input type="file" name="documentFile[{{ $i }}][file]" class="form-control me-2" style="display: none;">
                                                    @else
                                                        <input type="file" name="documentFile[{{ $i }}][file]" class="form-control me-2">
                                                    @endif
                                                    @if(!empty($dor['file']))
                                                    @php $folderIDS = str_replace('#MT-', '', $client->client_acc_ID); @endphp
                                                        <div class="mt-2">
                                                            <img src="{{ asset('uploads/clients/' . $folderIDS . '/documents/' . $dor['file']) }}" alt="License Attachment" style="width: 100px; height: auto; border: 1px solid #ddd; padding: 2px;">
                                                        </div>
                                                    @endif
                                                </div>
                                                <button type="button" class="btn btn-danger removeDocumentRepository" style="float: right;margin-top: -100px;margin-right: 40px;border-radius: 100%;"><span style="font-weight: 700;">{{ __('x') }}</span></button>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="mb-2">
                                            <div class="mb-2 col-md-11">
                                                <input type="text" name="documentName[0][name]" class="form-control me-2" placeholder="{{ __('Document Name') }}">
                                            </div>
                                            <div class="mb-2 col-md-11">
                                                <input type="file" name="documentFile[0][file]" class="form-control me-2">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="mb-2 col-md-11 mt-0">
                                    <button type="button" class="btn btn-success addDocumentRepository mt-2" style="width: 150px;">{{ __('Add +') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">{{ __('Update Details') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')

<script>
    $(document).ready(function() {
        // Subcategory load based on category
        $('#categoryID').on('change', function() {
            var categoryId = $(this).val();
            $('.loader--ripple').show();
            if (categoryId) {
                $.ajax({
                    url: "{{ url('/get-subcategories') }}/" + categoryId,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('.loader--ripple').hide();
                        $('#subcategoryID').empty().append('<option value="">{{ __('Please Select Sub Category') }}</option>');
                        $.each(data, function(key, value) {
                            var option = `<option value="${value.id}">${value.subcategory_name}</option>`;
                            $('#subcategoryID').append(option);
                        });
                    }
                });
            } else {
                $('#subcategoryID').empty().append('<option value="">{{ __('Please Select Sub Category') }}</option>');
            }
        });
    });
</script>

<script>
$(document).ready(function () {
    let contactIndex = {{ count($judicials) }};
    $(document).on('click', '.addJudicialNotification', function () {
        $('#contacts-wrapper').append(`
            <div class="d-flex mb-2" style="display: grid !important;grid-template-columns: repeat(3, 1fr);gap: 10px;">
                <input type="email" name="judicialNotification[${contactIndex}][email]" class="form-control me-2" placeholder="{{ __('Judicial Notification Emails') }}">
                <input type="text" name="judicialNotification[${contactIndex}][phone]" class="form-control me-2" placeholder="{{ __('Judicial Notification Phone') }}">
                <input type="email" name="judicialNotification[${contactIndex}][eb_email]" class="form-control me-2" placeholder="{{ __('Electronic Billing Email') }}">
                <input type="text" name="judicialNotification[${contactIndex}][information]" class="form-control me-2" placeholder="{{ __('Contact Person Information') }}">
                <input type="text" name="judicialNotification[${contactIndex}][city]" class="form-control me-2" placeholder="{{ __('Judicial Notification City') }}">
                <input type="text" name="judicialNotification[${contactIndex}][country]" class="form-control me-2" placeholder="{{ __('Judicial Notification Country') }}">
                <input type="text" name="judicialNotification[${contactIndex}][address]" class="form-control me-2" placeholder="{{ __('Judicial Notification Address') }}">
                <input type="date" name="judicialNotification[${contactIndex}][personalContactDate]" class="form-control me-2" placeholder="{{ __('Company Date') }}">
                <button type="button" class="btn btn-danger removeJudicialNotification" style="width: 50px;">-</button>
            </div>
        `);
        contactIndex++;
    });

    // Remove a contact row
    $(document).on('click', '.removeJudicialNotification', function () {
        $(this).parent('div').remove();
    });
});
</script>

<script>
$(document).ready(function () {
    let contactIndex = 1;

    // Add new DocumentRepository row
    $(document).on('click', '.addDocumentRepository', function () {
        $('#addDocumentRepository').append(`
            <div class="mb-2">
                <div class="mb-2 col-md-11">
                    <input type="text" name="documentName[${contactIndex}][name]" class="form-control me-2" placeholder="{{ __('Document Name') }}">
                </div>
                <div class="mb-2 col-md-11">
                    <input type="file" name="documentFile[${contactIndex}][file]" class="form-control me-2">
                </div>
                <button type="button" class="btn btn-danger removeDocumentRepository" style="float: right;margin-top: -100px;margin-right: 40px;border-radius: 100%;"><span style="font-weight: 700;">{{ __('x') }}</span></button>
            </div>
        `);
        contactIndex++;
    });

    // Remove a DocumentRepository row
    $(document).on('click', '.removeDocumentRepository', function () {
        $(this).parent('div').remove();
    });
});
</script>

<script>
$(document).ready(function () {
    let contactIndexP = $('#personalContactData .contactDataP').length; // Continue from last index

    // Add new contact
    $(document).on('click', '.addPersonalContactData', function () {
        $('#personalContactData').append(`
            <div class="contactDataP">
                <div class="row gy-4 mb-2">
                    <div class="col-xxl-6 col-md-6">
                        <label class="form-label">{{ __('Full Name') }}</label>
                        <input type="text" class="form-control" name="personalContactData[${contactIndexP}][fullName]" placeholder="{{ __('Enter full name') }}">
                    </div>
                    <div class="col-xxl-6 col-md-6">
                        <label class="form-label">{{ __('Position') }}</label>
                        <input class="form-control" name="personalContactData[${contactIndexP}][designation]" placeholder="{{ __('Enter position') }}">
                    </div>
                </div>
                <div class="row gy-4 mb-2">
                    <div class="col-xxl-4 col-md-4">
                        <label class="form-label">{{ __('Email') }}</label>
                        <input type="text" class="form-control" name="personalContactData[${contactIndexP}][contactEmail]" placeholder="{{ __('Enter emails separated by \';\'') }}">
                    </div>
                    <div class="col-xxl-4 col-md-4">
                        <label class="form-label">{{ __('Contact Number') }}</label>
                        <input type="text" class="form-control" name="personalContactData[${contactIndexP}][contactNumber]" placeholder="{{ __('Enter phone number') }}">
                    </div>
                    <div class="col-xxl-3 col-md-3">
                        <label for="cnt_company_date" class="form-label">{{ __('Persnal Contact Date') }}</label>
                        <input type="date" class="form-control" name="personalContactData[${contactIndexP}][companyDate]" id="cnt_company_date" placeholder="{{ __('Enter company date') }}">
                    </div>
                    <div class="col-xxl-1 col-md-1">
                        <label class="form-label">{{ __('Remove') }}</label>
                        <button type="button" class="btn btn-danger removePersonalContactData">-</button>
                    </div>
                </div>
            </div>
        `);
        contactIndexP++;
    });

    // Remove contact
    $(document).on('click', '.removePersonalContactData', function () {
        if ($('.contactDataP').length > 1) {
            $(this).closest('.contactDataP').remove();
        } else {
            alert('At least one contact section is required.');
        }
    });
});
</script>
@stop
