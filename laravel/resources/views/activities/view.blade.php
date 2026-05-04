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
    
    .comment-status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        margin-left: 8px;
    }
    
    .status-complete { background: #d1e7dd; color: #0a3622; }
    .status-reject { background: #f8d7da; color: #721c24; }
    .status-review { background: #fff3cd; color: #664d03; }
    .status-cancel { background: #e2e3e5; color: #2b2f32; }
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
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Activity View') }}</h4>
                <a href="{{ route('activities') }}" class="btn btn-secondary btn-sm">Back to Activities</a>
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
                        <a href="#projectInformation" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                            <span class="d-block d-sm-none"><i class="bx bx-user"></i></span>
                            <span class="d-none d-sm-block">{{ __('Project Information') }}</span>
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
                        <!-- Your existing general information content -->
                        <div class="live-preview" style="padding-left: 25px;padding-right: 25px;">
                            <div class="row gy-4 mb-2">
                                <div class="col-xxl-6 col-md-6">
                                    <label for="activity_name" class="form-label"><strong>Activity Name: </strong></label>
                                    <p>{{ $activity->activity_name }}</p>
                                </div>

                                <div class="col-xxl-6 col-md-6">
                                    <label for="creator" class="form-label"><strong>{{ __('Creator:') }}</strong></label>
                                    <p>{{ $creator }}</p>
                                </div>
                            </div>

                            <div class="row gy-4 mb-2">
                                <div class="col-xxl-6 col-md-6">
                                    <label for="participants" class="form-label"><strong>{{ __('Responsible:') }}</strong></label>
                                    <p>{!! $participants !!}</p>
                                </div>

                                <div class="col-xxl-6 col-md-6">
                                    <label for="status" class="form-label"><strong>{{ __('Activity Status:') }}</strong></label>
                                    @if($activity->status == 1)
                                        <p><span class="badge bg-dark me-1">{{ __('On time') }}</span></p>
                                    @elseif($activity->status == 2)
                                        <p><span class="badge bg-secondary me-1">{{ __('Delayed') }}</span></p>
                                    @elseif($activity->status == 3)
                                        <p><span class="badge bg-warning me-1">{{ __('Priority') }}</span></p>
                                    @elseif($activity->status == 4)
                                        <p><span class="badge bg-success me-1">{{ __('Completed') }}</span></p>
                                    @else
                                        <p>{{ __('N/A') }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="row gy-4 mb-3">
                                <div class="col-xxl-6 col-md-6">
                                    <label for="due_date" class="form-label"><strong>{{ __('Due Date:') }}</strong></label>
                                    <div>{!! date('Y-m-d', strtotime($activity->due_date)) !!}</div>
                                </div>

                                <div class="col-xxl-6 col-md-6">
                                    <label for="activity_type" class="form-label"><strong>{{ __('Activity Type:') }}</strong></label>
                                    <div>{!! $activity->activity_type !!}</div>
                                </div>
                            </div>

                            <div class="row gy-4 mb-3">
                                <div class="col-xxl-6 col-md-6">
                                    <label for="due_date" class="form-label"><strong>{{ __('Linked Project Name:') }}</strong></label>
                                    <div>{!! $project ?? 'N/A' !!}</div>
                                </div>

                                <div class="col-xxl-6 col-md-6">
                                    <label for="sub_status" class="form-label"><strong>{{ __('Sub Status:') }}</strong></label>
                                    @if($activity->sub_status == 1)
                                        <p><span class="badge bg-success me-1">{{ __('Completed') }}</span></p>
                                    @elseif($activity->sub_status == 2)
                                        <p><span class="badge bg-secondary me-1">{{ __('Reject') }}</span></p>
                                    @elseif($activity->sub_status == 3)
                                        <p><span class="badge bg-warning me-1">{{ __('Review') }}</span></p>
                                    @elseif($activity->sub_status == 4)
                                        <p><span class="badge bg-dark me-1">{{ __('Cancel') }}</span></p>
                                    @elseif($activity->sub_status == 5)
                                        <p><span class="badge bg-dark me-1">{{ __('Cancel') }}</span></p>
                                    @else
                                        <p>{{ __('N/A') }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="row gy-4 mb-3">
                                <div class="col-xxl-12 col-md-12">
                                    <label for="short_description" class="form-label"><strong>{{ __('Short Description:') }}</strong></label>
                                    <div>{!! $activity->short_description !!}</div>
                                </div>
                            </div>

                            <div class="row gy-4 mb-2">
                                <div class="col-xxl-12 col-md-12">
                                    <label for="main_description" class="form-label"><strong>{{ __('Description:') }}</strong></label>
                                    <div>{!! $activity->main_description !!}</div>
                                </div>
                            </div>

                            <div class="row gy-4 mb-4">
                                <div class="col-xxl-12 col-md-12">
                                    <label for="comments" class="form-label"><strong>{{ __('Extra Notes:') }}</strong></label>
                                    <div>{!! $activity->comments !!}</div>
                                </div>
                            </div>

                            @if(isset($attachments))
                                <div class="row gy-4 mb-2">
                                    <div class="col-xxl-12 col-md-12">
                                        <label for="comments" class="form-label mb-3"><strong>{{ __('Activity Attachment:') }}</strong></label>
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
                                                                <a href="{{ asset('storage/activities_attachment/' . $value->attachment_file) }}" class="btn btn-primary" download>Download Attachment</a>
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
                    
                    <div class="tab-pane" id="projectInformation">
                        @if(isset($projectdetail) && $projectdetail)
                            <div class="live-preview" style="padding-left: 25px;padding-right: 25px;">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">{{ __('Project Title:') }}</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="mb-0">{{ $projectdetail->project_title }}</p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">{{ __('Description:') }}</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="mb-0">{!! $projectdetail->description !!}</p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">{{ __('Created By:') }}</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="mb-0">{{ $projectCreator }}</p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">{{ __('Assigned To:') }}</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="mb-0">{{ $projectParticipants ?: 'Not assigned' }}</p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">{{ __('Due Date:') }}</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="mb-0">{{ $projectdetail->due_date ? \Carbon\Carbon::parse($projectdetail->due_date)->format('d M Y') : 'Not set' }}</p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">{{ __('Status:') }}</label>
                                    </div>
                                    <div class="col-md-9">
                                        @if($projectdetail->status == 1)
                                            <p><span class="badge bg-success me-1">{{ __('Active') }}</span></p>
                                        @elseif($projectdetail->status == 2)
                                            <p><span class="badge bg-danger me-1">{{ __('In Active') }}</span></p>
                                        @elseif($projectdetail->status == 3)
                                            <p><span class="badge bg-dark me-1">{{ __('Finished') }}</span></p>
                                        @else
                                            <p>{{ __('N/A') }}</p>
                                        @endif
                                    </div>
                                </div>

                                @if($projectdetail->comments)
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">{{ __('Comments:') }}</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="mb-0">{!! nl2br(e($projectdetail->comments)) !!}</p>
                                    </div>
                                </div>
                                @endif

                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <a href="{{ route('project.view', $projectdetail->id) }}" class="btn btn-primary btn-sm">
                                            <i class="ri-eye-line"></i> View Full Project
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="live-preview" style="padding-left: 25px;padding-right: 25px;">
                                <p style="font-weight: 600;text-align: center;">{{ __('No project assigned!') }}</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- NEW TAB: Integrated Comments & Updates Timeline -->
                    <div class="tab-pane" id="commentsTimeline">
                        <div class="live-preview" style="padding-left: 25px;padding-right: 25px;">
                            <!-- Comment Form at Top -->
                            <div class="card mb-4">
                                <div class="card-body" style="padding-top: 0px;">
                                    <h5 class="card-title mb-3">{{ __('Add Comment') }}</h5>
                                    <form id="commentForm" method="POST" action="{{ route('activity.comment.store') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="activityID" id="activityID" value="{{ $activityID }}">
                                        <div class="commentTypes mb-2">
                                            <input type="radio" name="commentTypes" id="complete" value="1" />
                                            <label for="complete"><span class="badge bg-success me-1">{{ __('Complete') }}</span></label>

                                            <input type="radio" name="commentTypes" id="reject" value="2" />
                                            <label for="reject"><span class="badge bg-secondary me-1">{{ __('Reject') }}</span></label>

                                            <input type="radio" name="commentTypes" id="review" value="3" />
                                            <label for="review"><span class="badge bg-warning me-1">{{ __('Review') }}</span></label>

                                            <input type="radio" name="commentTypes" id="cancel" value="4" />
                                            <label for="cancel"><span class="badge bg-dark me-1">{{ __('Cancel') }}</span></label>
                                        </div>
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1 me-2">
                                                <textarea 
                                                    name="act_comment" 
                                                    id="act_comment" 
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
                                                            @if(isset($item['commentTypes']))
                                                                @php
                                                                    $statusClasses = [
                                                                        1 => 'status-complete',
                                                                        2 => 'status-reject', 
                                                                        3 => 'status-review',
                                                                        4 => 'status-cancel',
                                                                        5 => 'status-cancel'
                                                                    ];
                                                                    $statusTexts = [
                                                                        1 => 'Complete',
                                                                        2 => 'Reject',
                                                                        3 => 'Review',
                                                                        4 => 'Cancel',
                                                                        5 => 'Created'
                                                                    ];
                                                                @endphp
                                                                <span class="comment-status-badge {{ $statusClasses[$item['commentTypes']] ?? 'status-complete' }}">
                                                                    {{ $statusTexts[$item['commentTypes']] ?? 'Complete' }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="timeline-date">{{ $item['created_at']->format('d M Y, h:i A') }}</span>
                                                            @if(Auth::id() === $item['user_id'])
                                                                <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteActivityComment('{{ $item['id'] }}')">
                                                                    <i class="bx bx-trash"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div>{!! $item['content'] !!}</div>
                                                    @if($item['attachments']->count() > 0)
                                                        <div class="attachementFileComment">
                                                            @foreach($item['attachments'] as $attachment)
                                                                <a href="{{ asset('storage/activities_comment_attachment/' . $attachment->attachment_file) }}" target="_blank">
                                                                    <i class="bx bx-paperclip"></i> {{ $attachment->attachment_file }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            
                                            @elseif($item['type'] == 'history')
                                                <!-- Activity History -->
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
                                                                Activity {{ ucfirst($item['action']) }}
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
                                                                            'activity_name' => 'Activity Name',
                                                                            'activity_type' => 'Activity Type',
                                                                            'status' => 'Status',
                                                                            'main_description' => 'Main Description',
                                                                            'due_date' => 'Due Date',
                                                                            'short_description' => 'Short Description',
                                                                            'comments' => 'Comments',
                                                                            'assign_by' => 'Assigned To',
                                                                            'created_by' => 'Created By',
                                                                        ];
                                                                        $fieldDisplay = $fieldNames[$field] ?? ucfirst(str_replace('_', ' ', $field));
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
                                                    @elseif($item['action'] == 'created')
                                                        <div class="mt-2">
                                                            <span class="badge bg-success">{{ __('Activity was created') }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="alert alert-info">
                                        No comments or updates yet. Be the first to add a comment!
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
@endsection

@section('script')
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
                        title: 'Success',
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
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function (xhr) {
                $('.loader--ripple').hide();
                let errorMsg = 'Something went wrong!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
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

function deleteActivityComment(commentID) {
    var recID = commentID;
    Swal.fire({
        html: '<div class="mt-3"><lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon><div class="mt-4 pt-2 fs-15 mx-5"><p class="text-muted mx-4 mb-0">Are you sure you want to delete this comment ?</p></div></div>',
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
                url: "{{ route('activity.comment.delete', ['id' => ':id']) }}".replace(':id', recID),
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                },
                success: function(response) {
                    Swal.fire(
                        'Deleted!',
                        response.success,
                        'success'
                    ).then((result) => {
                        location.reload();
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
@stop