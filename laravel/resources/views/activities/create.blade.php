@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
<style>
    .prjSlct .choices {
        margin-bottom: 0px;
    }
</style>
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Activities') }}</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form id="activityDetails" method="POST" action="{{ route('activity.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="activity_name" class="form-label">{{ __('Activity Name') }}</label>
                                <input type="text" class="form-control" name="activity_name" id="activity_name" placeholder="{{ __('Enter activity name') }}">
                                @error('activity_name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="activity_type" class="form-label">{{ __('Activity Type') }}</label>
                                <input type="text" class="form-control" name="activity_type" id="activity_type" placeholder="{{ __('Enter activity type') }}">
                                @error('activity_type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>


                        @if(isset($managers))
                            <div class="row gy-4 mb-2">
                                <div class="col-xxl-6 col-md-6">
                                    <label for="assign_by" class="form-label">{{ __('Assign Activity') }}</label>
                                    <select class="form-control" id="choices-multiple-remove-button" data-choices data-choices-removeItem name="assign_by[]" multiple  placeholder="{{ __('Select users...') }}">
                                        <option value="">{{ __('Select users...') }}</option>
                                        @foreach($managers as $value)
                                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('assign_by') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-xxl-6 col-md-6">
                                    <label for="choices-single-no-sorting" class="form-label">{{ __('Status') }}</label>
                                    <select class="form-control" id="choices-single-no-sorting" name="status" data-choices data-choices-sorting-false>
                                        <option>{{ __('Select status...') }}</option>
                                        <option value="1">{{ __('On time') }}</option>
                                        <option value="2">{{ __('Delayed') }}</option>
                                        <option value="3">{{ __('Priority') }}</option>
                                        <option value="4" disabled>{{ __('Completed') }}</option>
                                    </select>
                                    @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                                <input type="date" name="due_date" id="due_date" class="form-control" placeholder="{{ __('Select due date') }}">
                                @error('due_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="clientID" class="form-label">{{ __('Clients Linked') }}</label>
                                <select class="form-control" id="clientID" name="clientID" placeholder="{{ __('Select client...') }}">
                                    <option value="">{{ __('Select client...') }}</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ (isset($selectedClientId) && $selectedClientId == $client->id) ? 'selected' : '' }}>
                                            {{ $client->commercialName ?: ($client->legalName ?: 'Client #' . $client->id) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('clientID') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12 prjSlct">
                                <label for="projectID" class="form-label">{{ __('Activity Link to Project') }}</label>
                                <select class="form-control" id="projectID" name="projectID"  placeholder="{{ __('Select project...') }}">
                                    <option value="">{{ __('Select project...') }}</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->project_title }}</option>
                                    @endforeach
                                </select>
                                @error('projectID') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="short_description" class="form-label">{{ __('Short Description') }}</label>
                                <textarea class="form-control" name="short_description" id="short_description"></textarea>
                                @error('short_description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
 -->
                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="main_description" class="form-label">{{ __('Description') }}</label>
                                <textarea class="form-control" name="main_description" id="main_description" style="height: 300px;"></textarea>
                                @error('main_description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>


                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="comments" class="form-label">{{ __('Extra Notes') }}</label>
                                <textarea class="form-control" name="comments" id="comments"></textarea>
                                @error('comments') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="attachment_file" class="form-label">{{ __('Attachment File') }}</label>
                                <div class="dropzone" id="file-dropzone"></div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Save Activity') }}</button>
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
    var quill = new Quill('#main_description', {
        theme: 'snow',
        modules: {
            'toolbar': [[{ 'font': [] }, { 'size': [] }], ['bold', 'italic', 'underline', 'strike'], [{ 'color': [] }, { 'background': [] }], [{ 'script': 'super' }, { 'script': 'sub' }], [{ 'header': [false, 1, 2, 3, 4, 5, 6] }, 'blockquote', 'code-block'], [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'indent': '-1' }, { 'indent': '+1' }], ['direction', { 'align': [] }], ['link', 'image', 'video'], ['clean']]
        },
    });
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script>
Dropzone.autoDiscover = false;
let uploadedFiles = [];
var myDropzone = new Dropzone("#file-dropzone", {
    url: "{{ route('activity.upload') }}",
    maxFilesize: 10,
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
