@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
<style>
	.nav-pills > li > a {
		font-weight: 500;
		padding-left: 10px;
    	padding-right: 10px;
	}
	ul.nav-pills {
	    justify-content: center;
	}
	.upgrade-item {
	    transition: all 0.3s ease;
	}

	.upgrade-item:hover {
	    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
	    transform: translateY(-1px);
	}

	.custom-alert {
	    position: fixed;
	    top: 20px;
	    right: 20px;
	    z-index: 1050;
	    min-width: 300px;
	}
</style>
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Client') }}</h4>
                <a href="{{ route('client.edit', $client->id) }}" class="btn btn-primary btn-sm"> Update Information</a>
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
                    <li class="nav-item">
                        <a href="#upgrades" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                            <span class="d-block d-sm-none"><i class="bx bx-up-arrow"></i></span>
                            <span class="d-none d-sm-block">{{ __('Upgrades') }}</span>
                        </a>
                    </li>
                </ul>
                <div class="tab-content pt-2 text-muted">
                    <div class="tab-pane show active" id="basicInformation">
                    	<div class="card">
				            <div class="card-body py-2">
				                <div class="table-responsive">
				                    <table class="table mb-0">
				                        <tbody>
				                            <tr>
				                            	<td class="px-0">
				                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">Commercial Name :</span> @if(!empty($client->commercialName)) {{$client->commercialName}} @endif</p>
				                            	</td>
				                            </tr>
				                        	<tr>
				                            	<td class="px-0">
				                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">Legal Name :</span> @if(!empty($client->legalName)) {{$client->legalName}} @endif</p>
				                            	</td>
				                            </tr>
				                            <tr>
				                            	<td class="px-0">
				                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">NIT :</span> @if(!empty($client->nit)) {{$client->nit}} @endif</p>
				                            	</td>
				                            </tr>
				                            <tr>
				                            	<td class="px-0">
				                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">Category :</span> @if(!empty($client->categoryID)) {{$client->mainCategory->category_name}} @endif</p>
				                            	</td>
				                            </tr>
				                            <tr>
				                            	<td class="px-0">
				                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">Sub Category :</span> @if(!empty($client->subcategoryID)) {{$client->subCategory->subcategory_name}} @endif</p>
				                            	</td>
				                            </tr>
				                            <tr>
				                            	<td class="px-0">
				                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">Use Types :</span> @if(!empty($client->useTypes)) {{$client->useTypesRecord->use_types_name}} @endif</p>
				                            	</td>
				                            </tr>
				                            <tr>
				                            	<td class="px-0">
				                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">Website Link :</span> @if(!empty($client->website_link)) {{$client->website_link}} @endif</p>
				                            	</td>
				                            </tr>
				                            <tr>
				                            	<td class="px-0">
				                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">Status :</span> @if(!empty($client->client_status)) {{$client->basicInfoStatus->status_name}} @endif</p>
				                            	</td>
				                            </tr>
				                            <tr>
				                            	<td class="px-0">
				                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">Annotations :</span> @if(!empty($client->annotations)) {{$client->annotations}} @endif</p>
				                            	</td>
				                            </tr>
				                        </tbody>
				                    </table>
				                </div>
				            </div>
				        </div>
                    </div>
                    <div class="tab-pane" id="contactInformation">
                    	<div class="card">
				            <div class="card-body py-2">
				            	<div class="row gy-4 mb-2">
				            		<div class="perTitle" style="border-bottom: 1px solid #ddd;padding-bottom: 10px;">
                                        <h4>{{ __('Personal contact data') }}</h4>
                                    </div>
								    <div class="col-xxl-12 col-md-12">
								        @php
								            $personalContacts = json_decode($client->personalContactData, true) ?? [];
								        @endphp

								        @if(count($personalContacts))
								            @foreach($personalContacts as $index => $contact)
								                <div class="border rounded p-3 mb-2">
								                    <div class="row">
								                        <div class="col-md-6 mb-2">
								                            <strong>{{ __('Full Name:') }}</strong>
								                            {{ $contact['fullName'] ?? '—' }}
								                        </div>
								                        <div class="col-md-6 mb-2">
								                            <strong>{{ __('Position:') }}</strong>
								                            {{ $contact['designation'] ?? '—' }}
								                        </div>
								                    </div>
								                    <div class="row">
								                        <div class="col-md-6 mb-2">
								                            <strong>{{ __('Email:') }}</strong>
								                            @if(isset($contact['contactEmail']))
								                                @if(is_array($contact['contactEmail']))
								                                    {{ implode('; ', $contact['contactEmail']) }}
								                                @else
								                                    {{ $contact['contactEmail'] }}
								                                @endif
								                            @else
								                                —
								                            @endif
								                        </div>
								                        <div class="col-md-6 mb-2">
								                            <strong>{{ __('Contact Number:') }}</strong>
								                            {{ $contact['contactNumber'] ?? '—' }}
								                        </div>
								                    </div>
								                </div>
								            @endforeach
								        @else
								            <p class="text-muted">{{ __('No personal contact data available.') }}</p>
								        @endif
								    </div>
								</div>

								<h4 style="border-bottom: 1px solid #ddd;padding-bottom: 10px;margin-bottom: 20px;margin-top: 30px;">{{ __('Basic company data') }}</h4>
				                <div class="row gy-4 mb-2 px-0" style="border-bottom: 1px solid #eaedf1;padding-bottom: 15px;">
	                                <div class="col-xxl-12 col-md-12">
	                                    <label class="align-items-center gap-1 fw-semibold text-dark">{{ __('Judicial Notification Address :') }}</label>
	                                    @if(!empty($client->judicialNotificationAddress)){{$client->judicialNotificationAddress}}@endif
	                                </div>
	                            </div>

	                            <div class="row gy-4 mb-2">
	                                <div class="col-xxl-12 col-md-12">
	                                    <label class="align-items-center gap-1 fw-semibold text-dark mb-2">{{ __('Judicial Notification:') }}</label>
	                                    <div id="contacts-wrapper">
	                                        @php
	                                            $judicials = json_decode($client->judicialNotification, true) ?? [];
	                                            $k = 1;
	                                        @endphp
	                                        @foreach($judicials as $i => $jn)
	                                            <div class="d-flex mb-2" style="justify-content: space-between;">
	                                            	<label class="mb-2">
	                                            		<span class="align-items-center gap-1 fw-semibold text-dark">{{ __('Email:') }}</span> {{ $jn['email'] ?? '' }}
	                                            	</label>
	                                            	<label class="mb-2">
	                                            		<span class="align-items-center gap-1 fw-semibold text-dark">{{ __('Phone:') }}</span> {{ $jn['phone'] ?? '' }}
	                                            	</label>
	                                            	<label class="mb-2">
	                                            		<span class="align-items-center gap-1 fw-semibold text-dark">{{ __('Electronic Billing Email:') }}</span> {{ $jn['eb_email'] ?? '' }}
	                                            	</label>
	                                            	<label class="mb-2">
	                                            		<span class="align-items-center gap-1 fw-semibold text-dark">{{ __('Contact Person Information:') }}</span> {{ $jn['information'] ?? '' }}
	                                            	</label>
	                                            </div>
	                                            @php $k++; @endphp
	                                        @endforeach
	                                    </div>
	                                </div>
	                            </div>
				            </div>
				        </div>
                    </div>
                    <div class="tab-pane" id="additionalInformation">
                    	<div class="card">
				            <div class="card-body py-2">
				                <div class="table-responsive">
				                    <table class="table mb-0">
				                        <tbody>
				                            <tr>
				                            	<td class="px-0">
				                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">Company Size :</span> @if(!empty($client->companySize)) {{$client->companySize}} @endif</p>
				                            	</td>
				                            </tr>
				                            <tr>
				                            	<td class="px-0">
				                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">Company Type :</span> @if(!empty($client->companyType)) {{$client->companyType}} @endif</p>
				                            	</td>
				                            </tr>
				                            <tr>
				                            	<td class="px-0">
				                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">Annual Income :</span> @if(!empty($client->annualIncome)) {{$client->annualIncome}} @endif</p>
				                            	</td>
				                            </tr>
				                        </tbody>
				                    </table>
				                </div>
				            </div>
				        </div>
                    </div>
                    <div class="tab-pane" id="statusInformation">
                    	<div class="card">
				            <div class="card-body py-2">
				                <div class="table-responsive">
				                    <table class="table mb-0">
				                        <tbody>
				                            <tr>
				                            	<td class="px-0">
				                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">License Information :</span> @if(!empty($client->licenseInformation)) {{$client->licenseInformation}} @endif</p>
				                            	</td>
				                            </tr>
				                            <tr>
				                            	<td class="px-0">
				                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">{{ __('License Attachement :') }}</span>
				                            			@if($client->licenseAttachement)
			                                            @php $folderID = str_replace('#MT-', '', $client->client_acc_ID); @endphp
			                                                <img src="{{ asset('uploads/clients/' . $folderID . '/licenses/' . $client->licenseAttachement) }}" alt="License Attachment" style="width: 100px; height: auto; border: 1px solid #ddd; padding: 2px;">
			                                            @else
			                                                <span>{{ __('No license uploaded') }}</span>
			                                            @endif
				                            		</p>
				                            	</td>
				                            </tr>
				                        </tbody>
				                    </table>
				                </div>
				            </div>
				        </div>
                    </div>
                    <div class="tab-pane" id="accountingManagement">
                    	<div class="card">
				            <div class="card-body py-2">
				                <div class="table-responsive">
				                    <table class="table mb-0">
				                        <tbody>
				                            <tr>
				                            	<td class="px-0">
				                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">Bank Name :</span> @if(!empty($client->bankName)) {{$client->bankName}} @endif</p>
				                            	</td>
				                            </tr>
				                            <tr>
				                            	<td class="px-0">
				                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">Bank Account Number :</span> @if(!empty($client->bankAccountNumber)) {{$client->bankAccountNumber}} @endif</p>
				                            	</td>
				                            </tr>
				                            <tr>
				                            	<td class="px-0">
				                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">Bank Code :</span> @if(!empty($client->bankCode)) {{$client->bankCode}} @endif</p>
				                            	</td>
				                            </tr>
				                        </tbody>
				                    </table>
				                </div>
				            </div>
				        </div>
                    </div>
                    <div class="tab-pane" id="documentRepository">
                    	<div class="card">
                    		<div class="card-header align-items-center d-flex">
				                <h4 class="card-title mb-0 flex-grow-1">{{ __('Document Repository') }}</h4>
				            </div>
				            <div class="card-body py-2">
				                <div class="table-responsive">
				                    <table class="table mb-0">
				                        <tbody>
				                        	@php
		                                        $documentRepository = json_decode($client->documentRepository, true) ?? [];
		                                    @endphp
		                                    @if(isset($documentRepository))
		                                        @foreach($documentRepository as $i => $dor)
						                            <tr>
						                            	<td class="px-0">
						                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">{{ __('Document Name :') }}</span> {{ $dor['name'] ?? '' }}</p>
						                            	</td>
						                            </tr>
						                            <tr>
						                            	<td class="px-0">
						                            		<p class="d-flex mb-0"><span class="align-items-center gap-1 fw-semibold text-dark" style="padding-right: 15px;">{{ __('Document File :') }}</span>
						                            			@if(!empty($dor['file']))
			                                                    @php $folderIDS = str_replace('#MT-', '', $client->client_acc_ID); @endphp
			                                                        <div class="mt-2">
			                                                            <img src="{{ asset('uploads/clients/' . $folderIDS . '/documents/' . $dor['file']) }}" alt="License Attachment" style="width: 100px; height: auto; border: 1px solid #ddd; padding: 2px;">
			                                                        </div>
			                                                    @endif
						                            		</p>
						                            	</td>
						                            </tr>
						                        @endforeach
						                    @endif
				                        </tbody>
				                    </table>
				                </div>
				            </div>
				        </div>
                    </div>
                    <div class="tab-pane" id="upgrades">
					    <div class="card">
					        <div class="card-body py-2">
					            <div class="perTitle" style="border-bottom: 1px solid #ddd;padding-bottom: 10px;">
					                <h4>{{ __('Client Upgrades & Comments') }}</h4>
					            </div>

					            <!-- Add Comment Form -->
					            <div class="mb-4 mt-3">
					                <form id="upgradeCommentForm">
									    @csrf

									    <div class="row">
									        <div class="col-md-12 mb-2">
									            <label for="comment_type" class="form-label">{{ __('Type') }}</label>
									            <select class="form-control" id="comment_type" name="type">
									                <option value="comment">{{ __('Comment') }}</option>
									                <option value="upgrade">{{ __('Upgrade') }}</option>
									                <option value="note">{{ __('Note') }}</option>
									            </select>
									        </div>
									    </div>

									    <div class="mb-2">
									        <label for="upgrade_comment" class="form-label">{{ __('Write your comment') }}</label>
									        <textarea class="form-control" id="upgrade_comment" name="comment" rows="4"
									            placeholder="{{ __('Enter your comment here...') }}" required></textarea>
									    </div>

									    <!-- IMPORTANT: type="button" (not submit) -->
									    <button type="button" class="btn btn-primary btn-sm" id="submitUpgradeBtn">
									        <i class="bx bx-plus"></i> Add Comment
									    </button>
									</form>
					            </div>

					            <!-- Comments List -->
					            <div id="upgradesList" class="mt-4">
					                @forelse($upgrades as $upgrade)
					                    <div class="border rounded p-3 mb-3 upgrade-item">
					                        <div class="d-flex justify-content-between align-items-start">
					                            <div class="flex-grow-1">
					                                <div class="d-flex align-items-center mb-2">
					                                    <span class="badge
					                                        @if($upgrade->type == 'upgrade') bg-success
					                                        @elseif($upgrade->type == 'note') bg-warning
					                                        @else bg-primary @endif me-2">
					                                        {{ ucfirst($upgrade->type) }}
					                                    </span>
					                                    <strong>{{ $upgrade->user->name ?? 'Unknown User' }}</strong>
					                                </div>

					                                <div class="small text-muted mb-2">
					                                    <i class="bx bx-time"></i>
					                                    {{ $upgrade->created_at->format('M d, Y \a\t h:i A') }}
					                                </div>

					                                <p class="mb-0 text-dark">{{ $upgrade->comment }}</p>
					                            </div>
					                        </div>
					                    </div>
					                @empty
					                    <div class="text-center py-4 empty-state">
					                        <i class="bx bx-message-rounded text-muted" style="font-size: 48px;"></i>
					                        <p class="text-muted mt-2">{{ __('No comments or upgrades yet.') }}</p>
					                        <p class="text-muted small">{{ __('Be the first to add a comment!') }}</p>
					                    </div>
					                @endforelse
					            </div>

					        </div>
					    </div>
					</div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Activity List') }}</h4>
                <div class="flex-shrink-0">
                    <a href="{{ route('activity.create', ['client_id' => $client->id]) }}" class="btn btn-primary btn-sm"> + Create Activity</a>
                </div>
            </div>
            <div class="card-body">
            	<div class="mb-3 d-flex gap-3">
            		<div>
	                    <label for="statusFilter" class="form-label">{{ __('Filter by Status') }}</label>
	                    <select id="statusFilter" class="form-select" style="width:200px;">
	                        <option value="">{{ __('All') }}</option>
	                        <option value="1">{{ __('On time') }}</option>
	                        <option value="2">{{ __('Delayed') }}</option>
	                        <option value="3">{{ __('Priority') }}</option>
	                        <option value="4">{{ __('Completed') }}</option>
	                    </select>
	                </div>
	            </div>
                <table id="activitiesDataTable" class="display table table-bordered table-responsive" style="width:100%">
                    <thead>
                        <tr>
                            <th>{{ __('Activity Name') }}</th>
                            <th>{{ __('Activity Type') }}</th>
                            <th>{{ __('Creator') }}</th>
                            <th>{{ __('Responsible') }}</th>
                            <th>{{ __('Linked Project') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Sub Status') }}</th>
                            <th>{{ __('Due Date') }}</th>
                            <th>{{ __('Created Date') }}</th>
                            <th>{{ __('Last Updated Date') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                            <tr>
                                <td>{{ $activity->activity_name }}</td>
                                <td>{{ $activity->activity_type }}</td>
                                <td>{{ $activity->creator->name ?? 'N/A' }}</td>
                                <td>
                                    {{ (is_array($activity->assigned_users_list) && count($activity->assigned_users_list)) ? implode(', ', $activity->assigned_users_list) : 'N/A' }}
                                </td>
                                @php
								    $projectTitle = App\Models\Projects::where('id', $activity->projectID)
								        ->value('project_title');
								@endphp
                                <td>{{ $projectTitle ?? 'N/A' }}</td>
                                <td>
                                    @if($activity->status == 1)
                                        <span class="badge bg-success me-1">{{ __('On time') }}</span>
                                    @elseif($activity->status == 2)
                                        <span class="badge bg-secondary me-1">{{ __('Delayed') }}</span>
                                    @elseif($activity->status == 3)
                                        <span class="badge bg-warning me-1">{{ __('Priority') }}</span>
                                    @elseif($activity->status == 4)
                                        <span class="badge bg-dark me-1">{{ __('Completed') }}</span>
                                    @else
                                        <span class="badge bg-primary me-1">--</span>
                                    @endif
                                </td>
                                <td>
                                    @if($activity->sub_status == 1)
                                        <span class="badge bg-success me-1">{{ __('Completed') }}</span>
                                    @elseif($activity->sub_status == 2)
                                        <span class="badge bg-secondary me-1">{{ __('Reject') }}</span>
                                    @elseif($activity->sub_status == 3)
                                        <span class="badge bg-warning me-1">{{ __('Review') }}</span>
                                    @elseif($activity->sub_status == 4)
                                        <span class="badge bg-dark me-1">{{ __('Cancel') }}</span>
                                    @elseif($activity->sub_status == 5)
                                        <span class="badge bg-dark me-1">{{ __('Created') }}</span>
                                    @else
                                        <span class="badge bg-primary me-1">--</span>
                                    @endif
                                </td>
                                <td>{{ date('d-m-Y', strtotime($activity->due_date)) }}</td>
                                <td>{{ date('d-m-Y', strtotime($activity->created_at)) }}</td>
                                <td>{{ date('d-m-Y', strtotime($activity->updated_at)) }}</td>
                                <td>
                                	<a href="{{ route('activity.edit', $activity->id) }}" class="btn btn-soft-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Edit') }}"> <iconify-icon icon="solar:pen-new-square-linear" class="align-middle fs-18"></iconify-icon> </a>
                                	<a href="{{ route('activity.view', $activity->id) }}" class="btn btn-soft-primary btn-sm" title="{{ __('View') }}" data-bs-toggle="tooltip" data-bs-placement="top"> <iconify-icon icon="solar:eye-bold" class="align-middle fs-18"></iconify-icon> </a>
                                	<a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteActivity({{ $activity->id }})" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Delete') }}"> <iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon> </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">{{ __('No Records Found!') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Project List') }}</h4>
                <div class="flex-shrink-0">
                    <a href="{{ route('project.create', ['client_id' => $client->id]) }}" class="btn btn-primary btn-sm"> + Add Project</a>
                </div>
            </div>
            <div class="card-body">
            	<div class="mb-3 d-flex gap-3">
            		<div>
	                    <label for="statusFilterProject" class="form-label">{{ __('Filter by Status') }}</label>
	                    <select id="statusFilterProject" class="form-select" style="width:200px;">
	                        <option value="">{{ __('All') }}</option>
	                        <option value="1">{{ __('Active') }}</option>
	                        <option value="2">{{ __('In Active') }}</option>
	                        <option value="3">{{ __('Finished') }}</option>
	                    </select>
	                </div>
	            </div>
                <table id="projectsDataTable" class="table table-bordered table-responsive display" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>{{ __('Project Name') }}</th>
                            <th>{{ __('Creator') }}</th>
                            <th>{{ __('Participants') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Created Date') }}</th>
                            <th>{{ __('Last Update Date') }}</th>
                            <th>{{ __('Number of Pending Tasks') }}</th>
                            <th>{{ __('Related Client') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr>
                                <td>{{ $project->project_title ?? 'N/A' }}</td>
                                <td>{{ $project->creator->name ?? 'N/A' }}</td>
                                <td>
                                    {{ (is_array($project->assigned_users_list) && count($project->assigned_users_list)) ? implode(', ', $project->assigned_users_list) : 'N/A' }}
                                </td>
                                <td>
                                    @if($project->status == 1)
                                        <span class="badge bg-primary me-1">{{ __('Active') }}</span>
                                    @elseif($project->status == 2)
                                        <span class="badge bg-primary me-1">{{ __('In Active') }}</span>
                                    @elseif($project->status == 3)
                                        <span class="badge bg-primary me-1">{{ __('Finished') }}</span>
                                    @else
                                        <span class="badge bg-primary me-1">{{ __('None') }}</span>
                                    @endif
                                </td>
                                <td>{{ date('Y-m-d', strtotime($project->created_at)) }}</td>
                                <td>{{ date('Y-m-d', strtotime($project->updated_at)) }}</td>
                                <td>{{ (int) ($project->activities_count ?? 0) }}</td>
                                @php
								    $clientname = App\Models\Clients::where('id', $project->clientID)->pluck('commercialName')->first();
								@endphp
                                <td>{{ $clientname }}</td>
                                <td>
                                	<a href="{{ route('project.edit', $project->id) }}" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:pen-new-square-linear" class="align-middle fs-18"></iconify-icon></a>
                                	<a href="{{ route('project.view', $project->id) }}" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:eye-bold" class="align-middle fs-18"></iconify-icon></a>
                                	<a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteProject({{$project->id}})"><iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">{{ __('No Records Found!') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
	document.addEventListener('DOMContentLoaded', function() {
	    const form = document.getElementById('upgradeCommentForm');
	    if (!form) return;
	    form.addEventListener('submit', function(e) {
	        e.preventDefault();
	        const comment = document.getElementById('upgrade_comment').value.trim();
	        if (!comment) {
	            alert('Please enter a comment.');
	            return;
	        }
	        const url = '/clients/{{ $client->id }}/upgrade-comment';
	        const token = document.querySelector('input[name="_token"]').value;

	        fetch(url, {
	            method: 'POST',
	            headers: {
	                'Content-Type': 'application/json',
	                'X-CSRF-TOKEN': token,
	                'Accept': 'application/json'
	            },
	            body: JSON.stringify({ comment })
	        }).then(r => r.json())
	        .then(data => {
	            if (data.success) {
	                // prepend new comment to list
	                const list = document.getElementById('upgradesList');
	                const div = document.createElement('div');
	                div.className = 'border rounded p-2 mb-2';
	                const now = new Date();
	                div.innerHTML = `<div class="d-flex justify-content-between"><div><strong>{{ __('Upgrade comment') }}</strong><div class="small text-muted">by You &nbsp; | &nbsp; ${now.getFullYear()}-${(now.getMonth()+1).toString().padStart(2,'0')}-${now.getDate().toString().padStart(2,'0')} ${now.getHours().toString().padStart(2,'0')}:${now.getMinutes().toString().padStart(2,'0')}</div></div></div><p class="mb-0 mt-2">${comment.replace(/</g,'&lt;')}</p>`;
	                if (list) list.prepend(div);
	                document.getElementById('upgrade_comment').value = '';
	            } else if (data.error) {
	                alert(data.error);
	            }
	        }).catch(err => {
	            console.error(err);
	            alert('Something went wrong while saving the comment.');
	        });
	    });
	});
</script>

<script>
	document.addEventListener("DOMContentLoaded", function () {

	    const commentForm = document.getElementById("upgradeCommentForm");
	    const upgradesList = document.getElementById("upgradesList");
	    const submitBtn = document.getElementById("submitUpgradeBtn");

	    if (!commentForm || !submitBtn) return;

	    // Prevent script double run
	    if (submitBtn.dataset.bound === "1") return;
	    submitBtn.dataset.bound = "1";

	    let isSubmitting = false;

	    submitBtn.addEventListener("click", function (e) {
	        e.preventDefault();

	        if (isSubmitting) return;

	        const commentEl = document.getElementById("upgrade_comment");
	        const typeEl = document.getElementById("comment_type");

	        const comment = commentEl.value.trim();
	        const type = typeEl.value;

	        if (!comment) {
	            alert("Please enter a comment.");
	            return;
	        }

	        isSubmitting = true;
	        submitBtn.disabled = true;

	        const originalText = submitBtn.innerHTML;
	        submitBtn.innerHTML = '<i class="bx bx-loader bx-spin"></i> Adding...';

	        const token = document.querySelector('input[name="_token"]').value;

	        fetch("{{ route('client.upgrade-comment', $client->id) }}", {
	            method: "POST",
	            headers: {
	                "Content-Type": "application/json",
	                "X-CSRF-TOKEN": token,
	                "Accept": "application/json"
	            },
	            body: JSON.stringify({ comment, type })
	        })
	        .then(res => res.json())
	        .then(data => {
	            if (!data.success) {
	                throw new Error(data.error || "Failed to add comment");
	            }

	            // Prevent DOM duplicate
	            if (!document.querySelector(`[data-upgrade-id="${data.upgrade.id}"]`)) {
	                addCommentToList(data.upgrade);
	            }

	            commentEl.value = "";
	            showAlert("Comment added successfully!", "success");
	        })
	        .catch(err => {
	            console.error(err);
	            showAlert(err.message || "Something went wrong.", "error");
	        })
	        .finally(() => {
	            isSubmitting = false;
	            submitBtn.disabled = false;
	            submitBtn.innerHTML = originalText;
	        });
	    });

	    function addCommentToList(upgrade) {
	        const commentDiv = document.createElement("div");
	        commentDiv.className = "border rounded p-3 mb-3 upgrade-item";
	        commentDiv.setAttribute("data-upgrade-id", upgrade.id);

	        const createdAt = new Date(upgrade.created_at);
	        const formattedDate = createdAt.toLocaleString("en-US", {
	            month: "short",
	            day: "2-digit",
	            year: "numeric",
	            hour: "2-digit",
	            minute: "2-digit"
	        });

	        let badgeClass = "bg-primary";
	        if (upgrade.type === "upgrade") badgeClass = "bg-success";
	        else if (upgrade.type === "note") badgeClass = "bg-warning";

	        const userName = upgrade.user && upgrade.user.name ? upgrade.user.name : "You";

	        commentDiv.innerHTML = `
	            <div class="d-flex justify-content-between align-items-start">
	                <div class="flex-grow-1">
	                    <div class="d-flex align-items-center mb-2">
	                        <span class="badge ${badgeClass} me-2">
	                            ${upgrade.type.charAt(0).toUpperCase() + upgrade.type.slice(1)}
	                        </span>
	                        <strong>${userName}</strong>
	                    </div>

	                    <div class="small text-muted mb-2">
	                        <i class="bx bx-time"></i> ${formattedDate}
	                    </div>

	                    <p class="mb-0 text-dark">${escapeHtml(upgrade.comment)}</p>
	                </div>
	            </div>
	        `;

	        const emptyState = upgradesList.querySelector(".empty-state");
	        if (emptyState) emptyState.remove();

	        upgradesList.prepend(commentDiv);
	    }

	    function escapeHtml(text) {
	        return text
	            .replace(/&/g, "&amp;")
	            .replace(/</g, "&lt;")
	            .replace(/>/g, "&gt;")
	            .replace(/"/g, "&quot;")
	            .replace(/'/g, "&#039;");
	    }

	    function showAlert(message, type) {
	        const existingAlerts = document.querySelectorAll(".custom-alert");
	        existingAlerts.forEach(a => a.remove());

	        const alertDiv = document.createElement("div");
	        alertDiv.className = `alert alert-${type === "success" ? "success" : "danger"} custom-alert alert-dismissible fade show`;
	        alertDiv.innerHTML = `
	            ${message}
	            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
	        `;

	        commentForm.parentNode.insertBefore(alertDiv, commentForm);

	        setTimeout(() => {
	            if (alertDiv.parentNode) alertDiv.remove();
	        }, 4000);
	    }
	});
</script>

<script type="text/javascript">
    function deleteActivity(userId) {
        var recID = userId;
        Swal.fire({
            html: '<div class="mt-3"><lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon><div class="mt-4 pt-2 fs-15 mx-5"><p class="text-muted mx-4 mb-0">Are you Sure You want to Delete this activity ?</p></div></div>',
            showCancelButton: !0,
            customClass: {
                confirmButton: "btn btn-primary w-xs me-2 mb-1",
                cancelButton: "btn btn-danger w-xs mb-1"
            },
            confirmButtonText: "Yes, Delete It!",
            buttonsStyling: !1,
            showCloseButton: !0
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/activity/delete/' + recID,
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "recID": recID,
                    },
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            response.success,
                            'success'
                        ).then((result) => {
                            $('#activityData').DataTable().ajax.reload(null, false);
                        });
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            xhr.responseJSON.error,
                            'error'
                        );
                    }
                });
            }
        });
    }
</script>

<script type="text/javascript">
    function deleteProject(userId) {
        var recID = userId;
        Swal.fire({
            html: '<div class="mt-3"><lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon><div class="mt-4 pt-2 fs-15 mx-5"><p class="text-muted mx-4 mb-0">Are you Sure You want to Delete this project ?</p></div></div>',
            showCancelButton: !0,
            customClass: {
                confirmButton: "btn btn-primary w-xs me-2 mb-1",
                cancelButton: "btn btn-danger w-xs mb-1"
            },
            confirmButtonText: "Yes, Delete It!",
            buttonsStyling: !1,
            showCloseButton: !0
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/project/delete/' + recID,
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "recID": recID,
                    },
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            response.success,
                            'success'
                        ).then((result) => {
                            $('#projectsData').DataTable().ajax.reload(null, false);
                        });
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            xhr.responseJSON.error,
                            'error'
                        );
                    }
                });
            }
        });
    }
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script>
$(document).ready(function () {
    let table = $('#activitiesDataTable').DataTable({
        pageLength: 10,
        responsive: true,
        language: window.codexDataTableLanguage()
    });
    $('#statusFilter').on('change', function () {
        let selectedValue = this.value;
        let searchTerm = '';
        switch(selectedValue) {
            case '1':
                searchTerm = 'On time';
                break;
            case '2':
                searchTerm = 'Delayed';
                break;
            case '3':
                searchTerm = 'Priority';
                break;
            case '4':
                searchTerm = 'Completed';
                break;
            default:
                searchTerm = '';
        }
        table.column(5).search(searchTerm).draw();
    });

    let projectTable = $('#projectsDataTable').DataTable({
        pageLength: 10,
        responsive: true,
        language: window.codexDataTableLanguage()
    });
    $('#statusFilterProject').on('change', function () {
        let selectedValue = this.value;
        let searchTerm = '';
        switch(selectedValue) {
            case '1':
                searchTerm = 'Active';
                break;
            case '2':
                searchTerm = 'In Active';
                break;
            case '3':
                searchTerm = 'Finished';
                break;
            default:
                searchTerm = '';
        }
        projectTable.column(3).search(searchTerm).draw();
    });
});
</script>
@stop
