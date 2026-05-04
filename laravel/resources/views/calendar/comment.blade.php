@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
<link href="{{ asset('admin/css/historydata.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('admin/css/sidebar-slider.css') }}" rel="stylesheet" type="text/css" />
<style>
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
<div class="loader--ripple" style="display: none;">
    <div></div><div></div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Comments') }}</h4>
                <a href="{{ route('calendar.list') }}" class="btn btn-secondary btn-sm">{{ __('Back to Schedule List') }}</a>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <div class="card mb-4">
                        <div class="card-body" style="padding-top: 0px;">
                            <h5 class="card-title mb-3">{{ __('Add Comment') }}</h5>
                            <form id="calendarCommentForm" method="POST" action="{{ route('calendar.comment.store') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="calendarID" id="calendarID" value="{!! $calendarID !!}">
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

                    <div class="row gy-4 mb-2" style="max-height: 600px; overflow-y: auto;padding: 25px;">
                        <div class="col-xxl-12 col-md-12">
                            @if(isset($comments))
                                @foreach($comments as $value)
                                    <div class="commentBox @if(Auth::id() === $value->user_id) commentBoxRight @endif">
                                        <div class="comment-titleBOX">
                                            <h6>{!! $value->creator->name !!}</h6>
                                            @if(Auth::id() === $value->user_id)
                                                <a href="javascript:void(0)" class="btn btn-soft-danger btn-sm" onclick="deleteActivityComment('{{ $value->id }}')"><iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon></a>
                                            @endif
                                        </div>
                                        <p>{!! $value->act_comment !!}</p>
                                        <div class="attachementFileComment">
                                            @if(isset($commentDocuments))
                                                @foreach($commentDocuments as $cm_value)
                                                    @if($cm_value->calendarCommentID  == $value->id)
                                                        <span><a href="{{ asset('storage/calendar_comment_attachment/' . $cm_value->attachment_file) }}" >{{ $cm_value->attachment_file }}</a></span>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script>
$(document).ready(function () {
    $("#calendarCommentForm").on("submit", function (e) {
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
                    text: response.message || "Comment has been added successfully.",
                    timer: 2000,
                    showConfirmButton: false
                });
                $("#calendarCommentForm")[0].reset();
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
                    url: "{{ route('calendar.comment.delete', ['id' => ':id']) }}".replace(':id', recID),
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