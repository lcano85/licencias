@extends('layouts.app')
@section('styles')
<link href="{{ asset('admin/css/historydata.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Change History') }}</h4>
                <a href="{{ route('activities') }}" class="btn btn-secondary btn-sm">Back to Activities</a>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    @if(isset($history) && count($history) > 0)
                        <div class="timeline-new">
                            @foreach($history as $log)
                                <div class="history-item position-relative mb-4">
                                    <span class="history-badge">
                                        @if($log->action == 'created')
                                            <i class="bx bx-plus-circle text-success"></i>
                                        @else
                                            <i class="bx bx-edit text-primary"></i>
                                        @endif
                                    </span>
                                    
                                    <div class="ms-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0">
                                                <strong>{{ $log->user->name }}</strong> 
                                                {{ $log->action }} this activity
                                            </h6>
                                            <span class="text-muted small">{{ $log->created_at->format('d M Y H:i') }}</span>
                                        </div>
                                        
                                        @if($log->action == 'updated')
                                            <div class="changes-container mt-2">
                                                <strong>{{ __('Changes made:') }}</strong>
                                                <div class="mt-2">
                                                    @php
                                                        $changes = is_array($log->changes) ? $log->changes : [];
                                                    @endphp
                                                    
                                                    @if(!empty($changes))
                                                        @foreach($changes as $field => $change)
                                                            @if(is_array($change) && isset($change['old']) && isset($change['new']))
                                                                @php
                                                                    // Define human-readable field names
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
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <div class="text-muted">{{ __('No specific changes recorded') }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif($log->action == 'created')
                                            <div class="mt-2">
                                                <span class="badge bg-success">{{ __('Activity was created') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            No history records found for this activity.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@stop