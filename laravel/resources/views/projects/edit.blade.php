
@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Project Edit') }}</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form id="projectDetails" method="POST" action="{{ route('project.update', $project->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="project_title" class="form-label">{{ __('Project Name') }}</label>
                                <input type="text" class="form-control" name="project_title" id="project_title" required placeholder="{{ __('Enter project name') }}" @if(isset($project->project_title)) value="{{ $project->project_title }}" @endif>
                                @error('project_title') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        @if(isset($managers))
                            <div class="row gy-4 mb-2">
                                @php
                                    $assignBy = !empty($project->assign_by) ? explode(',', $project->assign_by) : [];
                                @endphp
                                <div class="col-xxl-12 col-md-12">
                                    <label for="assign_by" class="form-label">{{ __('Assign Project') }}</label>
                                    <select class="form-control" id="choices-multiple-remove-button" data-choices data-choices-removeItem name="assign_by[]" multiple  placeholder="{{ __('Select users...') }}">
                                        <option value="">{{ __('Select users...') }}</option>
                                        @foreach($managers as $value)
                                            <option value="{{ $value->id }}" {{ in_array($value->id, $assignBy) ? 'selected' : '' }}>{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('assign_by') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-4 col-md-4">
                                <label for="choices-single-no-sorting" class="form-label">{{ __('Status') }}</label>
                                <select class="form-control" id="choices-single-no-sorting" name="status" data-choices data-choices-sorting-false>
                                    <option value="" disabled>{{ __('Select status...') }}</option>
                                    <option value="1" {{ (isset($project) && $project->status == 1) ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="2" {{ (isset($project) && $project->status == 2) ? 'selected' : '' }}>{{ __('In active') }}</option>
                                    <option value="3" {{ (isset($project) && $project->status == 3) ? 'selected' : '' }}>{{ __('Finished') }}</option>
                                </select>
                                @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-4 col-md-4">
                                <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                                <input type="date" name="due_date" id="due_date" class="form-control" placeholder="{{ __('Select due date') }}" @if(isset($project->due_date)) value="{{ date('Y-m-d', strtotime($project->due_date)) }}" @endif>
                                @error('due_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-4 col-md-4">
                                <label for="clientID" class="form-label">{{ __('Clients Linked') }}</label>
                                <select class="form-control" id="clientID" name="clientID" data-choices data-choices-sorting-false placeholder="{{ __('Select client...') }}">
                                    <option value="">{{ __('Select client...') }}</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ (isset($project) && $project->clientID == $client->id) ? 'selected' : '' }}>
                                            {{ $client->commercialName ?: ($client->legalName ?: 'Client #' . $client->id) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('clientID') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="description" class="form-label">{{ __('Description') }}</label>
                                <textarea name="description" id="description" class="form-control d-none">
                                    {{ old('description', $project->description ?? '') }}
                                </textarea>
                                @error('description') <span class="text-danger">{{ $message }}</span> @enderror

                                <div id="quill-editor" style="height:300px;">
                                    {!! old('description', $project->description ?? '') !!}
                                </div>
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="comments" class="form-label">{{ __('Comments') }}</label>
                                <textarea class="form-control" name="comments" id="comments" style="height: 100px;">@if(isset($project->comments)){!! $project->comments !!}@endif</textarea>
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
                                <button type="submit" class="btn btn-primary">{{ __('Update Project') }}</button>
                                <a href="{{ route('projects') }}" class="btn btn-dark">{{ __('Back') }}</a>
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

    var form = document.getElementById('projectDetails');
    form.onsubmit = function () {
        document.getElementById('description').value = quill.root.innerHTML;
    };
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script type="text/javascript">
Dropzone.autoDiscover = false;
let uploadedFiles = [];
var myDropzone = new Dropzone("#file-dropzone", {
    url: "{{ route('project.upload') }}",
    maxFilesize: 10, // MB
    addRemoveLinks: true,
    paramName: "file",
    acceptedFiles: ".jpg,.jpeg,.png,.pdf,.docx",
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    success: function (file, response) {
        $('#projectDetails').append('<input type="hidden" name="attachment_file[]" value="'+response.file_name+'">');
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
                    url: '/project/upload-remove/' + recID,
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
@stop
