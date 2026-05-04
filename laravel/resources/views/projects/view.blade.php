@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
<link href="{{ asset('admin/css/sidebar-slider.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('admin/css/historydata.css') }}" rel="stylesheet" type="text/plain" />
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
    
    /* Integrated Timeline Styles */
    .timeline-item {
        position: relative;
        padding-left: 50px;
        padding-bottom: 30px;
        border-left: 2px solid #e9ecef;
    }
    
    .timeline-item:last-child {
        border-left-color: transparent;
    }
    
    .timeline-icon {
        position: absolute;
        left: -15px;
        top: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: white;
    }
    
    .timeline-icon.comment {
        background: #0d6efd;
    }
    
    .timeline-icon.history {
        background: #6c757d;
    }
    
    .timeline-icon.activity {
        background: #198754;
    }
    
    .timeline-content {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        border: 1px solid #dee2e6;
    }
    
    .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .timeline-user {
        font-weight: 600;
        color: #212529;
    }
    
    .timeline-date {
        font-size: 12px;
        color: #6c757d;
    }
    
    .timeline-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    
    .timeline-badge.created {
        background: #d1e7dd;
        color: #0a3622;
    }
    
    .timeline-badge.updated {
        background: #cfe2ff;
        color: #052c65;
    }
    
    .timeline-badge.activity-created {
        background: #d1e7dd;
        color: #0a3622;
    }
    
    .timeline-badge.activity-updated {
        background: #fff3cd;
        color: #664d03;
    }
    
    .change-item {
        background: white;
        padding: 8px 12px;
        border-radius: 4px;
        margin-bottom: 8px;
        border-left: 3px solid #0d6efd;
    }
    
    .change-item:last-child {
        margin-bottom: 0;
    }
    
    .old-value {
        color: #dc3545;
        text-decoration: line-through;
    }
    
    .new-value {
        color: #198754;
        font-weight: 600;
    }
    
    .attachementFileComment {
        margin-top: 10px;
    }
    
    .attachementFileComment a {
        display: inline-block;
        padding: 4px 12px;
        background: #e7f1ff;
        color: #0d6efd;
        border-radius: 4px;
        text-decoration: none;
        font-size: 13px;
        margin-right: 8px;
        margin-bottom: 8px;
    }
    
    .attachementFileComment a:hover {
        background: #cfe2ff;
    }
    
    .timeline-actions {
        margin-top: 10px;
        display: flex;
        gap: 8px;
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
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Project View') }}</h4>
                <a href="{{ route('project.add-activity', $project->id) }}" class="btn btn-primary btn-sm" style="margin-right: 10px;">+ {{ __('messages.add_activity') }}</a>
                <button class="btn btn-secondary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample">
                    {{ __('messages.update_information') }}
                </button>
            </div>
            <div class="card-body">
                <ul class="nav nav-pills mb-2">
                    <li class="nav-item">
                        <a href="#generalInformation" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                            <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
                            <span class="d-none d-sm-block">{{ __('General Information') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#activities" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                            <span class="d-block d-sm-none"><i class="bx bx-user"></i></span>
                            <span class="d-none d-sm-block">{{ __('Activities') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#commentsTimeline" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                            <span class="d-block d-sm-none"><i class="bx bx-chat"></i></span>
                            <span class="d-none d-sm-block">{{ __('Comments & Updates') }}</span>
                        </a>
                    </li>
                </ul>
                <div class="tab-content pt-2 text-muted">
                    <div class="tab-pane show active" id="generalInformation">
                        <div class="live-preview" style="padding-left: 25px;padding-right: 25px;">
                            <div class="row gy-4 mb-2">
                                <div class="col-xxl-6 col-md-6">
                                    <label for="project_title" class="form-label"><strong>{{ __('Project Name') }}: </strong></label>
                                    <p>{{ $project->project_title }}</p>
                                </div>

                                <div class="col-xxl-6 col-md-6">
                                    <label for="creator" class="form-label"><strong>{{ __('Creator:') }}</strong></label>
                                    <p>{{ $creator }}</p>
                                </div>
                            </div>

                            <div class="row gy-4 mb-2">
                                <div class="col-xxl-6 col-md-6">
                                    <label for="participants" class="form-label"><strong>{{ __('Participants:') }}</strong></label>
                                    <p>{!! $participants !!}</p>
                                </div>

                                <div class="col-xxl-6 col-md-6">
                                    <label for="status" class="form-label"><strong>{{ __('Project Status:') }}</strong></label>
                                    @if($project->status == 1)
                                        <p><span class="badge bg-success me-1">{{ __('Active') }}</span></p>
                                    @elseif($project->status == 2)
                                        <p><span class="badge bg-danger me-1">{{ __('In Active') }}</span></p>
                                    @elseif($project->status == 3)
                                        <p><span class="badge bg-dark me-1">{{ __('Finished') }}</span></p>
                                    @else
                                        <p>{{ __('N/A') }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="row gy-4 mb-2">
                                <div class="col-xxl-12 col-md-12">
                                    <label for="description" class="form-label"><strong>{{ __('Project Description:') }}</strong></label>
                                    <div>{!! $project->description !!}</div>
                                </div>
                            </div>

                            <div class="row gy-4 mb-4">
                                <div class="col-xxl-12 col-md-12">
                                    <label for="comments" class="form-label"><strong>{{ __('Project Comments:') }}</strong></label>
                                    <div>{!! $project->comments !!}</div>
                                </div>
                            </div>

                            @if(isset($attachments) && count($attachments) > 0)
                                <div class="row gy-4 mb-2">
                                    <div class="col-xxl-12 col-md-12">
                                        <label for="comments" class="form-label mb-3"><strong>{{ __('Project Attachment:') }}</strong></label>
                                        @foreach($attachments as $value)
                                            <div class="position-relative ms-2">
                                                <span class="position-absolute start-0 top-0 border border-dashed h-100"></span>
                                                <div class="position-relative ps-4">
                                                    <div class="mb-4">
                                                        <span class="position-absolute start-0 avatar-sm translate-middle-x bg-light d-inline-flex align-items-center justify-content-center rounded-circle text-success fs-20">
                                                            <i class="bx bx-check-circle"></i>
                                                        </span>
                                                        <div class="ms-2 d-flex flex-wrap gap-2 align-items-center justify-content-between">
                                                            <div>
                                                                <h5 class="mb-1 text-dark fw-medium fs-15">{{ $value->attachment_file }}</h5>
                                                                <a href="{{ asset('storage/projects_attachment/' . $value->attachment_file) }}" class="btn btn-primary" download>{{ __('Download Attachment') }}</a>
                                                            </div>
                                                            <p class="mb-0">{{ $value->created_at->format('F d, Y, h:i a') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="tab-pane" id="activities">
                        <div class="live-preview" style="padding-left: 25px;padding-right: 25px;">
                            @if($activities->count() > 0)
                            <div class="accordion" id="activitiesAccordion">
                                @foreach($activities as $index => $activity)
                                    @php
                                        $isFirst = $index === 0;
                                        $collapseId = 'collapse' . $activity->id;
                                        $headingId = 'heading' . $activity->id;
                                    @endphp

                                    <div class="accordion-item mb-2">
                                        <h2 class="accordion-header" id="{{ $headingId }}">
                                            <button 
                                                class="accordion-button fw-medium {{ !$isFirst ? 'collapsed' : '' }}" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#{{ $collapseId }}" 
                                                aria-expanded="{{ $isFirst ? 'true' : 'false' }}" 
                                                aria-controls="{{ $collapseId }}">
                                                {{ $activity->activity_name }} 
                                            </button>
                                        </h2>

                                        <div 
                                            id="{{ $collapseId }}" 
                                            class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}" 
                                            aria-labelledby="{{ $headingId }}" 
                                            data-bs-parent="#activitiesAccordion">
                                            <div class="accordion-body">
                                                <p><strong>{{ __('Short Description:') }}</strong> {{ $activity->short_description ?? __('N/A') }}</p>
                                                <p><strong>{{ __('Description:') }}</strong> {!! $activity->main_description ?? __('N/A') !!}</p>
                                                <p><strong>{{ __('Extra Notes:') }}</strong> {{ $activity->comments ?? __('N/A') }}</p>
                                                <p><strong>{{ __('Created By') }}:</strong> {{ $activity->creator->name ?? __('N/A') }}</p>
                                                <p><strong>{{ __('Responsible:') }}</strong> {{ implode(', ', $activity->assignedUsersList ?? []) }}</p>
                                                <p><strong>{{ __('Activity Status:') }}</strong> 
                                                @if($activity->status == 1)
                                                    <span class="badge bg-dark me-1">On time</span>
                                                @elseif($activity->status == 2)
                                                    <span class="badge bg-secondary me-1">Delayed</span>
                                                @elseif($activity->status == 3)
                                                    <span class="badge bg-warning me-1">Priority</span>
                                                @elseif($activity->status == 4)
                                                    <span class="badge bg-success me-1">Completed</span>
                                                @else
                                                    <span class="badge bg-dark me-1">N/A</span>
                                                @endif
                                                </p>
                                                <p><strong>{{ __('Sub Status:') }}</strong> 
@if($activity->sub_status == 1)
    <span class="badge bg-success">Completed</span>
@elseif($activity->sub_status == 2)
    <span class="badge bg-secondary">Reject</span>
@elseif($activity->sub_status == 3)
    <span class="badge bg-warning">Review</span>
@elseif($activity->sub_status == 4)
    <span class="badge bg-dark">Cancel</span>
@elseif($activity->sub_status == 5)
    <span class="badge bg-dark">Created</span>
@else
    <span>N/A</span>
@endif
                                                <p><strong>{{ __('Due Date:') }}</strong> {{ $activity->due_date ? \Carbon\Carbon::parse($activity->due_date)->format('d M Y') : __('N/A') }}</p>
                                                <p><strong>{{ __('Activity Type:') }}</strong> <span class="badge bg-secondary ms-2">{{ $activity->activity_type }}</span></p>

                                                <p style="margin-top: 25px;text-align: right;"><a href="{{ route('activity.view', $activity->id) }}" class="btn btn-secondary btn-sm" target="_blank">{{ __('View Activity') }}</a></p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @else
                                <p class="text-muted">{{ __('No activities found for this project.') }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- NEW TAB: Integrated Comments & Updates Timeline -->
                    <div class="tab-pane" id="commentsTimeline">
                        <div class="live-preview" style="padding-left: 25px;padding-right: 25px;">
                            <!-- Comment Form at Top -->
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">{{ __('Add Comment') }}</h5>
                                    <form id="commentForm" method="POST" action="{{ route('project.comment.store') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="projectID" id="projectID" value="{{ $projectID }}">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1 me-2">
                                                <textarea 
                                                    name="prj_comment" 
                                                    id="prj_comment" 
                                                    class="form-control" 
                                                    rows="2" 
                                                    placeholder="{{ __('Type your message...') }}" 
                                                    required></textarea>
                                            </div>
                                            <div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bx bx-send"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <label class="form-label">{{ __('Attach Files') }}</label>
                                            <input 
                                                type="file" 
                                                name="attachment_file[]" 
                                                id="attachment_file" 
                                                class="form-control" 
                                                multiple>
                                            <small class="text-muted">{{ __('You can select multiple files.') }}</small>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Integrated Timeline -->
                            <div class="timeline-container" style="max-height: 600px; overflow-y: auto;padding: 25px;">
                                @if(isset($integratedTimeline) && count($integratedTimeline) > 0)
                                    @foreach($integratedTimeline as $item)
                                        <div class="timeline-item">
                                            @if($item['type'] == 'comment')
                                                <!-- User Comment -->
                                                <div class="timeline-icon comment">
                                                    <i class="bx bx-message-dots"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="timeline-header">
                                                        <div>
                                                            <span class="timeline-user">{{ $item['user_name'] }}</span>
                                                            <span class="timeline-badge" style="background: #cfe2ff; color: #052c65;">{{ __('Comment') }}</span>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="timeline-date">{{ $item['created_at']->format('d M Y, h:i A') }}</span>
                                                            @if(Auth::id() === $item['user_id'])
                                                                <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteProjectComment('{{ $item['id'] }}')">
                                                                    <i class="bx bx-trash"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div>{!! $item['content'] !!}</div>
                                                    @if($item['attachments']->count() > 0)
                                                        <div class="attachementFileComment">
                                                            @foreach($item['attachments'] as $attachment)
                                                                <a href="{{ asset('storage/projects_comment_attachment/' . $attachment->attachment_file) }}" target="_blank">
                                                                    <i class="bx bx-paperclip"></i> {{ $attachment->attachment_file }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            
                                            @elseif($item['type'] == 'history')
                                                <!-- Project History -->
                                                <div class="timeline-icon history">
                                                    @if($item['action'] == 'created')
                                                        <i class="bx bx-plus-circle"></i>
                                                    @else
                                                        <i class="bx bx-edit"></i>
                                                    @endif
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="timeline-header">
                                                        <div>
                                                            <span class="timeline-user">{{ $item['user_name'] }}</span>
                                                            <span class="timeline-badge {{ $item['action'] }}">
                                                                {{ __('Project') }} {{ ucfirst($item['action']) }}
                                                            </span>
                                                        </div>
                                                        <span class="timeline-date">{{ $item['created_at']->format('d M Y, h:i A') }}</span>
                                                    </div>
                                                    
                                                    @if($item['action'] == 'updated' && !empty($item['changes']))
                                                        <div class="mt-2">
                                                            <strong>{{ __('Changes made:') }}</strong>
                                                            <div class="mt-2">
                                                                @foreach($item['changes'] as $field => $change)
                                                                    @php
                                                                        $fieldNames = [
                                                                            'project_title' => 'Project Name',
                                                                            'status' => 'Status',
                                                                            'description' => 'Description',
                                                                            'due_date' => 'Due Date',
                                                                            'comments' => 'Comments',
                                                                            'assign_by' => 'Assigned To',
                                                                            'created_by' => 'Created By',
                                                                            'clientID' => 'Client',
                                                                        ];
                                                                        $fieldDisplay = $fieldNames[$field] ?? ucfirst(str_replace('_', ' ', $field));
                                                                    @endphp
                                                                    
                                                                    <div class="change-item">
                                                                        <strong>{{ $fieldDisplay }}:</strong>
                                                                        <span class="old-value">{!! $change['old'] !!}</span>
                                                                        <i class="bx bx-right-arrow-alt mx-1"></i>
                                                                        <span class="new-value">{!! $change['new'] !!}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @elseif($item['action'] == 'created')
                                                        <div class="mt-2">
                                                            <span class="badge bg-success">{{ __('Project was created') }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            
                                            @elseif($item['type'] == 'activity_history')
                                                <!-- Activity History -->
                                                <div class="timeline-icon activity">
                                                    @if($item['action'] == 'created')
                                                        <i class="bx bx-task"></i>
                                                    @else
                                                        <i class="bx bx-edit-alt"></i>
                                                    @endif
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="timeline-header">
                                                        <div>
                                                            <span class="timeline-user">{{ $item['user_name'] }}</span>
                                                            <span class="timeline-badge activity-{{ $item['action'] }}">
                                                                {{ __('Activity') }} {{ ucfirst($item['action']) }}
                                                            </span>
                                                        </div>
                                                        <span class="timeline-date">{{ $item['created_at']->format('d M Y, h:i A') }}</span>
                                                    </div>
                                                    
                                                    <div class="mt-2">
                                                        <strong>{{ __('Activity') }}:</strong> {{ $item['activity_name'] }}
                                                    </div>
                                                    
                                                    @if($item['action'] == 'updated' && !empty($item['changes']))
                                                        <div class="mt-2">
                                                            <strong>{{ __('Changes made:') }}</strong>
                                                            <div class="mt-2">
                                                                @foreach($item['changes'] as $field => $change)
                                                                    @php
                                                                        $activityFieldNames = [
                                                                            'activity_name' => 'Activity Name',
                                                                            'status' => 'Status',
                                                                            'short_description' => 'Short Description',
                                                                            'main_description' => 'Description',
                                                                            'due_date' => 'Due Date',
                                                                            'assign_by' => 'Assigned To',
                                                                            'activity_type' => 'Activity Type',
                                                                        ];
                                                                        $fieldDisplay = $activityFieldNames[$field] ?? ucfirst(str_replace('_', ' ', $field));
                                                                    @endphp
                                                                    
                                                                    <div class="change-item">
                                                                        <strong>{{ $fieldDisplay }}:</strong>
                                                                        <span class="old-value">{{ $change['old'] }}</span>
                                                                        <i class="bx bx-right-arrow-alt mx-1"></i>
                                                                        <span class="new-value">{{ $change['new'] }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="alert alert-info">
                                        {{ __('No comments or updates yet. Be the first to add a comment!') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasExampleLabel">{{ __('Update Project Information') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>

    <div class="offcanvas-body">
        <form id="projectDetailsOffcanvas" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="projectID" id="off_projectID" value="{{ $project->id }}">

            <div class="row gy-4 mb-2">
                <div class="col-xxl-12 col-md-12">
                    <label class="form-label">{{ __('Project Name') }}</label>
                    <input type="text" class="form-control" name="project_title" id="off_project_title" required>
                    <span class="text-danger project_title-error"></span>
                </div>
            </div>

            <div class="row gy-4 mb-2">
                <div class="col-xxl-12 col-md-12">
                    <label class="form-label">{{ __('Assign Project') }}</label>
                    <select class="form-control" id="off_assign_by" name="assign_by[]" multiple>
                        @foreach($managersnew as $value)
                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                        @endforeach
                    </select>
                    <span class="text-danger assign_by-error"></span>
                </div>
            </div>

            <div class="row gy-4 mb-2">
                <div class="col-xxl-12 col-md-12 mt-2">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select class="form-control mb-0" id="off_status" name="status">
                        <option value="1">{{ __('Active') }}</option>
                        <option value="2">{{ __('In active') }}</option>
                        <option value="3">{{ __('Finished') }}</option>
                    </select>
                    <span class="text-danger status-error"></span>
                </div>

                <div class="col-xxl-12 col-md-12 mt-0">
                    <label class="form-label">{{ __('Due Date') }}</label>
                    <input type="date" name="due_date" id="off_due_date" class="form-control">
                    <span class="text-danger due_date-error"></span>
                </div>

                <div class="col-xxl-12 col-md-12 mt-2">
                    <label class="form-label">{{ __('Clients Linked') }}</label>
                    <select class="form-control" id="off_clientID" name="clientID">
                        <option value="">{{ __('Select client...') }}</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->commercialName }}</option>
                        @endforeach
                    </select>
                    <span class="text-danger clientID-error"></span>
                </div>
            </div>

            <div class="row gy-4 mb-2">
                <div class="col-xxl-12 col-md-12">
                    <label class="form-label">{{ __('Description') }}</label>

                    <!-- Hidden input for Quill -->
                    <input type="hidden" name="description" id="off_description">

                    <!-- Quill Container -->
                    <div id="off_quill_editor" style="height:200px;"></div>

                    <span class="text-danger description-error"></span>
                </div>
            </div>

            <div class="row gy-4 mb-2">
                <div class="col-xxl-12 col-md-12">
                    <label class="form-label">{{ __('Comments') }}</label>
                    <textarea class="form-control" name="comments" id="off_comments" rows="3"></textarea>
                    <span class="text-danger comments-error"></span>
                </div>
            </div>

            <div class="row gy-4 mb-2">
                <div class="col-xxl-12 col-md-12">
                    <label class="form-label">{{ __('Attachment File') }}</label>
                    <input type="file" name="attachment_file[]" id="off_attachment_file" class="form-control" multiple>
                    <small class="text-muted">{{ __('You can select multiple files.') }}</small>
                </div>
            </div>

            <div class="row mt-4">
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">{{ __('Update Project') }}</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">{{ __('Cancel') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>


@endsection

@section('script')

<script type="text/javascript">
    function deleteProjectComment(commentID) {
        var recID = commentID;
        Swal.fire({
            html: '<div class="mt-3"><lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon><div class="mt-4 pt-2 fs-15 mx-5"><p class="text-muted mx-4 mb-0">{{ __("Are you sure you want to delete this comment?") }}</p></div></div>',
            showCancelButton: !0,
            customClass: {
                confirmButton: "btn btn-primary w-xs me-2 mb-1",
                cancelButton: "btn btn-danger w-xs mb-1"
            },
            confirmButtonText: "{{ __("Yes, Delete It!") }}",
            buttonsStyling: !1,
            showCloseButton: !0
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('project.comment.delete', ['id' => ':id']) }}".replace(':id', recID),
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(response) {
                        Swal.fire(
                            '{{ __("Deleted!") }}',
                            response.success,
                            'success'
                        ).then((result) => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire(
                            '{{ __("Error!") }}',
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
    $(document).ready(function () {
        $('#commentForm').on('submit', function (e) {
            e.preventDefault();
            $('.loader--ripple').show();
            let formData = new FormData(this);
            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function () {
                    $('#commentForm button[type="submit"]').prop('disabled', true);
                },
                success: function (response) {
                    if (response.success) {
                        $('.loader--ripple').hide();
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("Success") }}',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#commentForm')[0].reset();
                        location.reload();
                    } else {
                        $('.loader--ripple').hide();
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("Error") }}',
                            text: response.message
                        });
                    }
                },
                error: function (xhr) {
                    $('.loader--ripple').hide();
                    let errorMsg = '{{ __("Something went wrong!") }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("Error") }}',
                        text: errorMsg
                    });
                },
                complete: function () {
                    $('.loader--ripple').hide();
                    $('#commentForm button[type="submit"]').prop('disabled', false);
                }
            });
        });
    });
</script>

<script type="text/javascript">
let offQuill;
let offAssignChoices;
let offStatusChoices;
let offClientChoices;

$(document).ready(function () {

    // Choices Init
    offAssignChoices = new Choices('#off_assign_by', { removeItemButton: true });
    offStatusChoices = new Choices('#off_status', { searchEnabled: false });
    offClientChoices = new Choices('#off_clientID', { searchEnabled: true });

    // Quill Init
    offQuill = new Quill('#off_quill_editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'font': [] }, { 'size': [] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'script': 'super' }, { 'script': 'sub' }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }, 'blockquote', 'code-block'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'indent': '-1' }, { 'indent': '+1' }],
                ['direction', { 'align': [] }],
                ['link', 'image', 'video'],
                ['clean']
            ]
        }
    });

    // When offcanvas open => load details
    $('#offcanvasExample').on('shown.bs.offcanvas', function () {
        loadProjectDetails();
    });

    function loadProjectDetails() {
        $('.loader--ripple').show();

        let projectId = "{{ $project->id }}";

        $.ajax({
            url: "{{ route('project.get-details', ':id') }}".replace(':id', projectId),
            type: "GET",
            success: function (res) {
                if (res.success) {
                    populateOffcanvas(res.data);
                }
                $('.loader--ripple').hide();
            },
            error: function () {
                $('.loader--ripple').hide();
                Swal.fire("{{ __("Error") }}", "{{ __("Failed to load project details") }}", "error");
            }
        });
    }

    function populateOffcanvas(data) {
        $('#off_project_title').val(data.project_title ?? '');
        $('#off_due_date').val(data.due_date ?? '');
        $('#off_comments').val(data.comments ?? '');

        // Quill HTML
        offQuill.root.innerHTML = data.description ?? '';

        // Status
        offStatusChoices.setChoiceByValue(String(data.status));

        // Client
        if (data.clientID) {
            offClientChoices.setChoiceByValue(String(data.clientID));
        }

        // Assign by (multiple)
        if (data.assign_by) {
            let users = data.assign_by.split(',').filter(Boolean);

            offAssignChoices.removeActiveItems();

            users.forEach(function (id) {
                offAssignChoices.setChoiceByValue(String(id));
            });
        }

        // Set action URL
        $('#projectDetailsOffcanvas').attr('action',
            "{{ route('project.updateViaAjax', ':id') }}".replace(':id', data.id)
        );
    }

    // Submit update
    $('#projectDetailsOffcanvas').on('submit', function (e) {
        e.preventDefault();
        $('.loader--ripple').show();

        // Save Quill HTML into hidden input
        $('#off_description').val(offQuill.root.innerHTML);

        let formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                $('.loader--ripple').hide();

                if (response.success) {
                    Swal.fire("{{ __("Success") }}", response.message, "success").then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire("{{ __("Error") }}", response.message, "error");
                }
            },
            error: function (xhr) {
                $('.loader--ripple').hide();

                let msg = "{{ __("Something went wrong!") }}";
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join("<br>");
                }

                Swal.fire("{{ __("Error") }}", msg, "error");
            }
        });
    });

});
</script>

@stop