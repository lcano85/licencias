@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Activity Edit') }}</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form id="activityDetails" method="POST" action="{{ route('activity.update', $activity->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="activity_name" class="form-label">{{ __('Activity Name') }}</label>
                                <input type="text" class="form-control" name="activity_name" id="activity_name" placeholder="{{ __('Enter activity name') }}" @if(isset($activity->activity_name)) value="{{ $activity->activity_name }}" @endif>
                                @error('activity_name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="activity_type" class="form-label">{{ __('Activity Type') }}</label>
                                <input type="text" class="form-control" name="activity_type" id="activity_type" placeholder="{{ __('Enter activity type') }}" @if(isset($activity->activity_type)) value="{{ $activity->activity_type }}" @endif>
                                @error('activity_type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            @if(isset($managers))
                                @php
                                    $assignBy = !empty($activity->assign_by) ? explode(',', $activity->assign_by) : [];
                                @endphp
                                <div class="col-xxl-6 col-md-6">
                                    <label for="assign_by" class="form-label">{{ __('Assign Activity') }}</label>
                                    <select class="form-control" id="choices-multiple-remove-button" data-choices data-choices-removeItem name="assign_by[]" multiple  placeholder="{{ __('Select users...') }}">
                                        <option value="">{{ __('Select users...') }}</option>
                                        @foreach($managers as $value)
                                            <option value="{{ $value->id }}" {{ in_array($value->id, $assignBy) ? 'selected' : '' }}>{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('assign_by') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            <div class="col-xxl-6 col-md-6">
                                <label for="choices-single-no-sorting" class="form-label">{{ __('Status') }}</label>
                                <select class="form-control" id="choices-single-no-sorting" name="status" data-choices data-choices-sorting-false>
                                    <option value="" disabled>{{ __('Select status...') }}</option>
                                    <option value="1" {{ (isset($activity) && $activity->status == 1) ? 'selected' : '' }}>{{ __('On time') }}</option>
                                    <option value="2" {{ (isset($activity) && $activity->status == 2) ? 'selected' : '' }}>{{ __('Delayed') }}</option>
                                    <option value="3" {{ (isset($activity) && $activity->status == 3) ? 'selected' : '' }}>{{ __('Priority') }}</option>
                                    <option value="4" {{ (isset($activity) && $activity->status == 4) ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                </select>
                                @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                                <input type="due_date" name="due_date" id="due_date" class="form-control" placeholder="{{ __('Select due date') }}" @if(isset($activity->due_date)) value="{{ date('Y-m-d', strtotime($activity->due_date)) }}" @endif>
                                @error('due_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="clientID" class="form-label">{{ __('Clients Linked') }}</label>
                                <select class="form-control" id="clientID" name="clientID" placeholder="{{ __('Select client...') }}">
                                    <option value="">{{ __('Select client...') }}</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ (isset($activity) && $activity->clientID == $client->id) ? 'selected' : '' }}>
                                            {{ $client->commercialName ?: ($client->legalName ?: 'Client #' . $client->id) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('clientID') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="projectID" class="form-label">{{ __('Activity Link to Project') }}</label>
                                <select class="form-control" id="projectID" name="projectID"  placeholder="{{ __('Select project...') }}">
                                    <option value="">{{ __('Select project...') }}</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ (isset($activity) && $activity->projectID == $project->id) ? 'selected' : '' }}>{{ $project->project_title }}</option>
                                    @endforeach
                                </select>
                                @error('projectID') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="short_description" class="form-label">{{ __('Short Description') }}</label>
                                <textarea class="form-control" name="short_description" id="short_description">{{ old('short_description', $activity->short_description ?? '') }}</textarea>
                                @error('short_description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="main_description" class="form-label">{{ __('Description') }}</label>
                                <textarea name="main_description" id="main_description" class="form-control d-none">
                                    {{ old('main_description', $activity->main_description ?? '') }}
                                </textarea>
                                @error('main_description') <span class="text-danger">{{ $message }}</span> @enderror

                                <div id="quill-editor" style="height:300px;">
                                    {!! old('main_description', $activity->main_description ?? '') !!}
                                </div>
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="comments" class="form-label">{{ __('Extra Notes') }}</label>
                                <textarea class="form-control" name="comments" id="comments">{{ old('comments', $activity->comments ?? '') }}</textarea>
                                @error('comments') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-4">
                            <div class="col-xxl-12 col-md-12">
                                <label for="attachment_file" class="form-label">{{ __('Attachment File') }}</label>
                                <div class="dropzone" id="file-dropzone"></div>
                            </div>
                        </div>

                        @if(isset($attachments))
                            <div class="row gy-4 mb-2">
                                <div class="col-xxl-12 col-md-12">
                                    <label for="comments" class="form-label mb-3"><strong>{{ __('Activity Attachment:') }} </strong></label>
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
                                                            <a href="javascript:void(0)" class="btn btn-primary" onclick="removeAttachment('{{$value->id}}')">{{ __('Remove Attachment') }}</a>
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
                        
                        <div class="row mt-4">
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Update Activity') }}</button>
                                <a href="{{ route('activities') }}" class="btn btn-dark">{{ __('Back') }}</a>
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
    var quill = new Quill('#quill-editor', {
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
        },
    });

    var form = document.getElementById('activityDetails');
    form.onsubmit = function () {
        document.getElementById('main_description').value = quill.root.innerHTML;
    };
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script type="text/javascript">
Dropzone.autoDiscover = false;
let uploadedFiles = [];
var myDropzone = new Dropzone("#file-dropzone", {
    url: "{{ route('activity.upload') }}",
    maxFilesize: 10, // MB
    addRemoveLinks: true,
    paramName: "file",
    acceptedFiles: ".jpg,.jpeg,.png,.pdf,.docx",
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    success: function (file, response) {
        $('#activityDetails').append('<input type="hidden" name="attachment_file[]" value="'+response.file_name+'">');
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
<script type="text/javascript">
    function removeAttachment(picId) {
        var recID = picId;
        Swal.fire({
            html: '<div class="mt-3"><lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon><div class="mt-4 pt-2 fs-15 mx-5"><p class="text-muted mx-4 mb-0">{{ __('Are you Sure You want to Delete this attachment ?') }}</p></div></div>',
            showCancelButton: !0,
            customClass: {
                confirmButton: "btn btn-primary w-xs me-2 mb-1",
                cancelButton: "btn btn-danger w-xs mb-1"
            },
            confirmButtonText: "{{ __('Yes, Delete It!') }}",
            buttonsStyling: !1,
            showCloseButton: !0
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/activity/upload-remove/' + recID,
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "recID": recID,
                    },
                    success: function(response) {
                        Swal.fire(
                            '{{ __('Deleted!') }}',
                            response.success,
                            'success'
                        ).then((result) => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire(
                            '{{ __('Error!') }}',
                            xhr.responseJSON.error,
                            'error'
                        );
                    }
                });
            }
        });
    }
</script>

<script>
    document.getElementById('due_date').flatpickr();
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("activityDetails");
    form.addEventListener("submit", function (e) {
        e.preventDefault();
        document.querySelectorAll(".text-danger.js-error").forEach(el => el.remove());

        let isValid = true;
        const showError = (element, message) => {
            const error = document.createElement("span");
            error.classList.add("text-danger", "js-error");
            error.innerText = message;
            element.closest(".col-xxl-6, .col-xxl-12, .col-md-6, .col-md-12").appendChild(error);
        };

        const activityName = document.getElementById("activity_name").value.trim();
        const activityType = document.getElementById("activity_type").value.trim();
        const dueDate = document.getElementById("due_date").value;
        const projectID = document.getElementById("projectID").value;
        const shortDesc = document.getElementById("short_description").value.trim();
        const mainDesc = document.getElementById("main_description").value.trim();

        if (activityName === "") {
            isValid = false;
            showError(document.getElementById("activity_name"), "{{ __('Activity name is required.') }}");
        }
        if (activityType === "") {
            isValid = false;
            showError(document.getElementById("activity_type"), "{{ __('Activity type is required.') }}");
        }
        if (dueDate === "") {
            isValid = false;
            showError(document.getElementById("due_date"), "{{ __('Please select a due date.') }}");
        }
        if (projectID === "") {
            isValid = false;
            showError(document.getElementById("projectID"), "{{ __('Please select a project.') }}");
        }
        if (shortDesc === "") {
            isValid = false;
            showError(document.getElementById("short_description"), "{{ __('Short description is required.') }}");
        }
        if (mainDesc === "") {
            isValid = false;
            showError(document.getElementById("main_description"), "{{ __('Main description is required.') }}");
        }
        if (dueDate) {
            const selectedDate = new Date(dueDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                isValid = false;
                showError(document.getElementById("due_date"), "{{ __('Due date cannot be in the past.') }}");
            }
        }
        if (isValid) {
            form.submit();
        } else {
            // Scroll to first error for better UX
            const firstError = document.querySelector(".text-danger.js-error");
            if (firstError) firstError.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    });
});
</script>

<script>
let clientChoices;
let projectChoices;

document.addEventListener('DOMContentLoaded', function () {

    projectChoices = new Choices('#projectID', {
        shouldSort: false,
        searchEnabled: true,
        placeholder: true
    });

    clientChoices = new Choices('#clientID', {
        shouldSort: false,
        searchEnabled: true,
        placeholder: true
    });

});
</script>

<script>
$(document).ready(function () {

    $('#projectID').on('change', function () {

        let projectId = $(this).val();

        if (!projectId) {
            clientChoices.removeActiveItems();
            clientChoices.enable();
            return;
        }

        $.ajax({
            url: `/project/${projectId}/client`,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    clientChoices.setChoiceByValue(String(res.clientID));
                    clientChoices.enable();
                } else {
                    clientChoices.removeActiveItems();
                    clientChoices.enable();
                }
            },
            error: function () {
                alert("{{ __('AJAX error') }}");
            }
        });
    });

});
</script>
@stop
