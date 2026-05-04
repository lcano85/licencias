@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
<style>
    .kv { display:grid; grid-template-columns: 220px 1fr; gap:8px 16px; }
    .kv .k { color:#6c757d; font-weight:600; }
    .badge-soft { padding: .35em .6em; border-radius:.5rem; font-size:.8rem; }
    .badge-soft-success { background:#e6f4ea; color:#198754; }
    .badge-soft-danger  { background:#fde7e9; color:#dc3545; }
    .badge-soft-warning { background:#fff3cd; color:#b58100; }
    .badge-soft-secondary{ background:#e2e3e5; color:#41464b; }
    .card-section-title { font-size: .95rem; font-weight:700; color:#495057; margin-bottom: .5rem; }
    .hr { border-top:1px solid #eee; margin: 1rem 0; }
    
    .attachment-card {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .attachment-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-color: #0d6efd;
    }
    .attachment-icon {
        font-size: 2.5rem;
        min-width: 50px;
        text-align: center;
    }
    .attachment-icon.pdf { color: #dc3545; }
    .attachment-icon.doc { color: #2b579a; }
    .attachment-icon.xls { color: #217346; }
    .attachment-icon.img { color: #6f42c1; }
    .attachment-icon.zip { color: #fd7e14; }
    .attachment-icon.default { color: #6c757d; }
    
    .attachment-details {
        flex: 1;
    }
    .attachment-name {
        font-weight: 600;
        font-size: 1rem;
        color: #212529;
        margin-bottom: 5px;
        word-break: break-word;
    }
    .attachment-meta {
        font-size: 0.875rem;
        color: #6c757d;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    .attachment-meta-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .attachment-description {
        font-size: 0.875rem;
        color: #495057;
        margin-top: 5px;
        font-style: italic;
    }
    .attachment-actions {
        display: flex;
        gap: 8px;
    }
    .no-attachments {
        text-align: center;
        padding: 40px 20px;
        background: #f8f9fa;
        border-radius: 8px;
        color: #6c757d;
    }
    .no-attachments i {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }
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

    .commentBox{
        background-color: #f1f1f1;
        padding: 15px;
        margin-bottom: 20px;
    }
    .commentBox a {
        color: #d95c28;
        text-decoration: underline;
        font-weight: 600;
    }
    .commentBox span {
        margin-right: 8px;
    }
    .comment-titleBOX {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .comment-titleBOX h6{
        margin: 0;
        font-size: 14px;
        font-weight: 600;
    }
    .commentBox.commentBoxRight .comment-titleBOX {
        flex-direction: row-reverse;
    }
    .commentBoxRight{
        text-align: right;
    }
</style>
@stop

@section('content')
@php
    $concepts = [
        1 => 'Public Communication of Music Videos',
        2 => 'Digital Storage of Phonograms',
        3 => 'Reproduction Compensation of Phonograms',
    ];

    $environments = [
        1 => 'Musical Ambience',
        2 => 'Public Establishments',
        3 => 'Public Events',
        4 => 'Broadcasting',
        5 => 'WebCasting',
        6 => 'SimulCasting',
        7 => 'Subscription TV Operators',
        8 => 'Social Networks',
    ];

    $statusBadges = [
        1 => ['label' => 'Active',   'class' => 'badge-soft-success'],
        2 => ['label' => 'Canceled', 'class' => 'badge-soft-danger'],
        3 => ['label' => 'Suspended','class' => 'badge-soft-warning'],
        4 => ['label' => 'Expired',  'class' => 'badge-soft-secondary'],
    ];

    $originLabels = [
        'License'      => 'License',
        'Transaction'  => 'Transaction',
        'Conciliation' => 'Conciliation',
        'Sentences'    => 'Sentences',
    ];

    $freqMap = [
        '1' => 'Monthly', 'Monthly' => 'Monthly',
        '2' => 'Quarterly', 'Quarterly' => 'Quarterly',
        '3' => 'Annual', 'Annual' => 'Annual',
    ];
    $frequencyText = __($freqMap[$licensesAgreements->billing_frequency ?? ''] ?? ($licensesAgreements->billing_frequency ?: 'N/A'));

    $startDate = !empty($licensesAgreements->startDate) ? \Carbon\Carbon::parse($licensesAgreements->startDate)->format('d-m-Y') : __('N/A');
    $endDate   = !empty($licensesAgreements->endDate)   ? \Carbon\Carbon::parse($licensesAgreements->endDate)->format('d-m-Y')   : __('N/A');
    $createdAt = !empty($licensesAgreements->created_at)? \Carbon\Carbon::parse($licensesAgreements->created_at)->format('d-m-Y H:i') : __('N/A');

    $money = function ($v) {
        if ($v === null || $v === '') return __('N/A');
        return '$ ' . number_format((float)$v, 2, '.', ',');
    };

    $conceptText = __($concepts[(int)($licensesAgreements->licensedConcept ?? 0)] ?? ($licensesAgreements->licensedConcept ?: 'N/A'));
    $envVals = is_array($licensesAgreements->licensedEnvironment)
        ? $licensesAgreements->licensedEnvironment
        : (is_string($licensesAgreements->licensedEnvironment) && json_decode($licensesAgreements->licensedEnvironment, true)
            ? json_decode($licensesAgreements->licensedEnvironment, true)
            : (isset($licensesAgreements->licensedEnvironment) ? [(string)$licensesAgreements->licensedEnvironment] : []));

    $envLabels = [];
    foreach ($envVals as $id) {
        $key = (int)$id;
        if (isset($environments[$key])) $envLabels[] = __($environments[$key]);
    }
    $environmentText = $envLabels ? implode(', ', $envLabels) : __('N/A');
    $statusInfo = $statusBadges[(int)($licensesAgreements->status ?? 0)] ?? null;
    
    // Function to get file type class
    function getFileTypeClass($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext === 'pdf') return 'pdf';
        if (in_array($ext, ['doc', 'docx'])) return 'doc';
        if (in_array($ext, ['xls', 'xlsx'])) return 'xls';
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) return 'img';
        if (in_array($ext, ['zip', 'rar'])) return 'zip';
        return 'default';
    }
@endphp

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Licenses / Agreements — Details') }}</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('licenses-agreements') }}" class="btn btn-secondary btn-sm">
                        <iconify-icon icon="solar:arrow-left-linear" class="align-middle fs-18"></iconify-icon> {{ __('Back to list') }}
                    </a>
                </div>
            </div>

            <div class="card-body">
                <ul class="nav nav-pills mb-2">
                    <li class="nav-item">
                        <a href="#generalInformation" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                            <span class="d-block d-sm-none"><i class="bx bx-home"></i></span>
                            <span class="d-none d-sm-block">{{ __('Licenses / Agreements — Details') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#commentsTimeline" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                            <span class="d-block d-sm-none"><i class="bx bx-chat"></i></span>
                            <span class="d-none d-sm-block">{{ __('Comments') }}</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content pt-2 text-muted">
                    <div class="tab-pane show active" id="generalInformation" style="padding-left: 25px;padding-right: 25px;">
                        <div class="card-section-title">{{ __('Identification') }}</div>
                        <div class="kv">
                            <div class="k">{{ __('Commercial Name') }}</div>        <div>{{ $licensesAgreements->commercialName ?? __('N/A') }}</div>
                            <div class="k">{{ __('User Types') }}</div>             <div>{{ $licensesAgreements->userType ?? __('N/A') }}</div>
                            <div class="k">{{ __('Category') }}</div>               <div>{{ $licensesAgreements->category ?? __('N/A') }}</div>
                            <div class="k">{{ __('Subcategory') }}</div>            <div>{{ $licensesAgreements->subcategory ?? __('N/A') }}</div>
                        </div>

                        <div class="hr"></div>

                        <div class="card-section-title">{{ __('License Scope') }}</div>
                        <div class="kv">
                            <div class="k">{{ __('Licensed Concept') }}</div>       <div>{{ $conceptText }}</div>
                            <div class="k">{{ __('Licensed Environment') }}</div>   <div>{{ $environmentText }}</div>
                            <div class="k">{{ __('Origin') }}</div>                 <div>{{ __($originLabels[$licensesAgreements->origin ?? ''] ?? ($licensesAgreements->origin ?: 'N/A')) }}</div>
                            <div class="k">{{ __('Status') }}</div>
                            <div>
                                @if($statusInfo)
                                    <span class="badge badge-soft {{ $statusInfo['class'] }}">{{ __($statusInfo['label']) }}</span>
                                @else
                                    {{ __('N/A') }}
                                @endif
                            </div>
                        </div>

                        <div class="hr"></div>

                        <div class="card-section-title">{{ __('Validity') }}</div>
                        <div class="kv">
                            <div class="k">{{ __('Start Date') }}</div>             <div>{{ $startDate }}</div>
                            <div class="k">{{ __('End Date') }}</div>               <div>{{ $endDate }}</div>
                        </div>

                        <div class="hr"></div>

                        <div class="card-section-title">{{ __('Billing') }}</div>
                        <div class="kv">
                            <div class="k">{{ __('Billing Frequency') }}</div> <div>{{ $frequencyText }}</div>
                            <div class="k">{{ __('Begin (Month / Year)') }}</div>   
                            <div>
                                {{ $licensesAgreements->begin_month ? str_pad($licensesAgreements->begin_month, 2, '0', STR_PAD_LEFT) : 'N/A' }}
                                /
                                {{ $licensesAgreements->begin_year ?? 'N/A' }}
                            </div>
                            <div class="k">{{ __('Finish (Month / Year)') }}</div>  <div>
                                {{ $licensesAgreements->finish_month ? str_pad($licensesAgreements->finish_month, 2, '0', STR_PAD_LEFT) : 'N/A' }}
                                /
                                {{ $licensesAgreements->finish_year ?? 'N/A' }}
                            </div>
                            <div class="k">{{ __('Sub Total') }}</div> <div>$ {{ $licensesAgreements->monthlyValue }}</div>
                            <div class="k">{{ __('VAT') }}</div> <div>{{ $licensesAgreements->vat }} %</div>
                            <div class="k">{{ __('Monthly Total Value') }}</div> <div>$ {{ $licensesAgreements->month_total_value }}</div>
                            <div class="k">{{ __('Annual Value') }}</div> <div>$ {{ $licensesAgreements->annualValue }}</div>
                        </div>

                        <div class="hr"></div>

                        <div class="card-section-title">
                            <i class="ri-attachment-2 me-2"></i>Attached Documents 
                            @if($licensesAgreements->attachments && $licensesAgreements->attachments->count() > 0)
                                <span class="badge bg-info">{{ $licensesAgreements->attachments->count() }}</span>
                            @endif
                        </div>
                        
                        @if($licensesAgreements->attachments && $licensesAgreements->attachments->count() > 0)
                            <div class="attachments-list">
                                @foreach($licensesAgreements->attachments as $attachment)
                                    <div class="attachment-card">
                                        <div class="attachment-details">
                                            <div class="attachment-name"><iconify-icon icon="solar:file-text-outline" class="align-middle fs-18"></iconify-icon> {{ $attachment->original_name }}</div>
                                            <div class="attachment-meta">
                                                <span class="attachment-meta-item">
                                                    <span>Date : {{ $attachment->created_at->format('d-m-Y H:i') }}</span>
                                                </span>
                                            </div>
                                            @if($attachment->description)
                                                <div class="attachment-description">
                                                    Description : {{ $attachment->description }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="attachment-actions">
                                            <a href="{{ route('licenses-agreements.attachment.download', $attachment->id) }}" 
                                               class="btn btn-primary btn-sm" 
                                               data-bs-toggle="tooltip" 
                                               title="{{ __('Download Document') }}">
                                                <iconify-icon icon="solar:download-minimalistic-bold" class="align-middle fs-18"></iconify-icon> Download
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="no-attachments">
                                <i class="ri-file-list-line"></i>
                                <p class="mb-0"><strong>{{ __('No documents attached') }}</strong></p>
                                <p class="text-muted mb-0">{{ __('There are no documents associated with this license/agreement yet.') }}</p>
                            </div>
                        @endif

                        <div class="hr"></div>

                        <div class="card-section-title">{{ __('Audit Information') }}</div>
                        <div class="kv">
                            <div class="k">Created By</div><div>{{ $licensesAgreements->user ? $licensesAgreements->user->name : 'N/A' }}</div>
                            <div class="k">{{ __('Created At') }}</div><div>{{ $createdAt }}</div>
                        </div>
                    </div>
                    
                    <div class="tab-pane" id="commentsTimeline">
                        <div class="live-preview">
                            <div class="mb-2">
                                <div class="card-body" style="padding-top: 0px;">
                                    <h5 class="card-title mb-2">{{ __('Add Comment') }}</h5>
                                    <form id="licenseForm" method="POST" action="{{ route('licenses.comment.store') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="licensesID" id="licensesID" value="{{ $licensesID }}">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1 me-2">
                                                <textarea name="lic_comment" id="lic_comment" class="form-control" rows="2" placeholder="{{ __('Type your comment...') }}" required></textarea>
                                            </div>
                                            <div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bx bx-send"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Integrated Timeline -->
                            @if(isset($licensesComments))
                                @foreach($licensesComments as $value)
                                    <div class="commentBox @if(Auth::id() === $value->user_id) commentBoxRight @endif">
                                        <div class="comment-titleBOX">
                                            <h6>{!! $value->creator->name !!}</h6>
                                            @if(Auth::id() === $value->user_id)
                                                <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteLicenseComment('{{ $value->id }}')"><iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon></a>
                                            @endif
                                        </div>
                                        <p>{!! $value->lic_comment !!}</p>
                                    </div>
                                @endforeach
                            @else
                                <p>{{ __('No Comments Found!') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
});
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#licenseForm').on('submit', function (e) {
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
                    $('#licenseForm button[type="submit"]').prop('disabled', true);
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
                        $('#licenseForm')[0].reset();
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
                    $('#licenseForm button[type="submit"]').prop('disabled', false);
                }
            });
        });
    });

    function deleteLicenseComment(commentID) {
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
                    url: "{{ route('licenses.comment.delete', ['id' => ':id']) }}".replace(':id', recID),
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
