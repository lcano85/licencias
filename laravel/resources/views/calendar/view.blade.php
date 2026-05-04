@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
<link href="{{ asset('admin/css/sidebar-slider.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('content')
<div class="loader--ripple" style="display: none;">
    <div></div><div></div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Schedule View') }}</h4>
                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" style="margin-right: 10px;">+ {{ __('Add Attachment') }}</button>
                <a href="{{ route('calendar.list') }}" class="btn btn-secondary btn-sm">{{ __('Back to Schedule List') }}</a>
            </div>
            <div class="card-body">
                <div class="row gy-4 mb-2">
                    @if(isset($event->schedule_title))
                        <div class="col-xxl-6 col-md-6">
                            <label for="activity_name" class="form-label"><strong>{{ __('Schedule Title') }}:</strong> </label>
                            <div>{{ $event->schedule_title }}</div>
                        </div>
                    @endif

                    @if(isset($event->type))
                        <div class="col-xxl-6 col-md-6">
                            <label for="activity_name" class="form-label"><strong>{{ __('Schedule Type') }}:</strong> </label>
                            @if($event->type == 1)
                                <div><span class="badge bg-primary me-1">{{ __('Activity') }}</span></div>
                            @elseif($event->type == 2)
                                <div><span class="badge bg-info me-1">{{ __('Meeting') }}</span></div>
                            @elseif($event->type == 3)
                                <div><span class="badge bg-success me-1">{{ __('Hearing') }}</span></div>
                            @elseif($event->type == 4)
                                <div><span class="badge bg-secondary me-1">{{ __('Deadline') }}</span></div>
                            @else
                                <div>{{ __('N/A') }}</div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="row gy-4 mb-2">
                    @if(isset($event->start))
                        <div class="col-xxl-6 col-md-6">
                            <label for="activity_name" class="form-label"><strong>{{ __('Day & Time') }}:</strong> </label>
                            <div>{{ date('d-m-Y h:i A', strtotime($event->start)) }}</div>
                        </div>
                    @endif

                    @if(isset($creator))
                        <div class="col-xxl-6 col-md-6">
                            <label for="activity_name" class="form-label"><strong>{{ __('Creator') }}:</strong> </label>
                            <div>{{ $creator }}</div>
                        </div>
                    @endif
                </div>

                @if(isset($event->location))
                    <div class="row gy-4 mb-2">
                        <div class="col-xxl-12 col-md-12">
                            <label for="activity_name" class="form-label"><strong>{{ __('Location, Department/Office') }}:</strong> </label>
                            <div>{!! $event->location !!}</div>
                        </div>
                    </div>
                @endif

                @if(isset($event->description))
                    <div class="row gy-4 mb-2">
                        <div class="col-xxl-12 col-md-12">
                            <label for="activity_name" class="form-label"><strong>{{ __('Description') }}:</strong> </label>
                            <div>{!! $event->description !!}</div>
                        </div>
                    </div>
                @endif

                @if(isset($guestNames))
                    <div class="row gy-4 mb-2">
                        <div class="col-xxl-12 col-md-12">
                            <label for="activity_name" class="form-label"><strong>{{ __('Guest') }}:</strong> </label>
                            <div>{!! $guestNames !!}</div>
                        </div>
                    </div>
                @endif

                @if(isset($attachments))
                    <div class="row gy-4 mb-2 mt-2">
                        <div class="col-xxl-12 col-md-12">
                            <label for="comments" class="form-label mb-3"><strong>{{ __('Project Attachment:') }} </strong></label>
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
                                                    <a href="{{ asset('storage/calendar_attachment/' . $value->attachment_file) }}" class="btn btn-primary btn-sm" download style="margin-right: 10px;">{{ __('Download Attachment') }}</a>
                                                    <a href="javascript:void(0)" class="btn btn-primary btn-sm" onclick="removeAttachment('{{$value->id}}')">{{ __('Remove Attachment') }}</a>
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
    </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header" style="border-bottom: 1px solid #f1f1f1;">
        <h5 id="offcanvasRightLabel">{{ __('Add Attachment') }}</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>
    <div class="offcanvas-body">
        <form id="calendaruploadDetails" method="POST" action="{{ route('calendar.upload.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="calendarID" id="calendarID" value="{{ $event->id }}">
            <div class="row gy-4 mb-2">
                <div class="col-xxl-12 col-md-12">
                    <label for="attachment_file" class="form-label">{{ __('Attachment File') }}</label>
                    <div class="dropzone" id="file-dropzone"></div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">{{ __('Save Attachment') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script>
Dropzone.autoDiscover = false;
let uploadedFiles = [];
var myDropzone = new Dropzone("#file-dropzone", {
    url: "{{ route('calendar.upload') }}",
    maxFilesize: 10,
    addRemoveLinks: true,
    paramName: "file",
    acceptedFiles: ".jpg,.jpeg,.png,.pdf,.docx",
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    success: function (file, response) {
        $('#calendaruploadDetails').append('<input type="hidden" name="attachment_file[]" value="'+response.file_name+'">');
    },
    removedfile: function (file) {
        let name = file.upload.filename;
        $('input[value="'+name+'"]').remove();

        if(file.previewElement != null){
            file.previewElement.parentNode.removeChild(file.previewElement);
        }
    }
});
</script>
<script>
$(document).ready(function () {
    $("#calendaruploadDetails").on("submit", function (e) {
        $('.loader--ripple').show();
        e.preventDefault();
        let formData = new FormData(this);
        $.ajax({
            url: $(this).attr("action"),
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                // Optional: disable button or show loader
            },
            success: function (response) {
                $('.loader--ripple').hide();
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: response.message || "Attachment has been uploaded successfully.",
                    timer: 2000,
                    showConfirmButton: false
                });
                $("#calendaruploadDetails")[0].reset();
                $("#offcanvasRight").offcanvas('hide');
                location.reload();
            },
            error: function (xhr) {
                $('.loader--ripple').hide();
                let errorMessage = "Something went wrong!";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: errorMessage,
                });
            }
        });
    });
});
</script>
<script type="text/javascript">
    function removeAttachment(picId) {
        var recID = picId;
        Swal.fire({
            html: '<div class="mt-3"><lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon><div class="mt-4 pt-2 fs-15 mx-5"><p class="text-muted mx-4 mb-0">Are you Sure You want to Delete this attachment ?</p></div></div>',
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
                    url: '/calendar/upload-remove/' + recID,
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