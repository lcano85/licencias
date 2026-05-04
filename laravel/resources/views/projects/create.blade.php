@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Project') }}</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form id="projectDetails" method="POST" action="{{ route('project.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="project_title" class="form-label">{{ __('Project Name') }}</label>
                                <input type="text" class="form-control" name="project_title" id="project_title" required placeholder="{{ __('Enter project name') }}" value="{{ old('project_title') }}">
                                @error('project_title') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        @if(isset($managers))
                            <div class="row gy-4 mb-2">
                                <div class="col-xxl-12 col-md-12">
                                    <label for="assign_by" class="form-label">{{ __('Assign Project') }}</label>
                                    <select class="form-control" id="choices-multiple-remove-button" data-choices data-choices-removeItem name="assign_by[]" multiple  placeholder="{{ __('Select users...') }}">
                                        <option value="">{{ __('Select users...') }}</option>
                                        @foreach($managers as $value)
                                            <option value="{{ $value->id }}" 
                                                {{ in_array($value->id, old('assign_by', [])) ? 'selected' : '' }}>
                                                {{ $value->name }}
                                            </option>
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
                                    <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="2" {{ old('status') == '2' ? 'selected' : '' }}>{{ __('In active') }}</option>
                                    <option value="3" {{ old('status') == '3' ? 'selected' : '' }}>{{ __('Finished') }}</option>
                                </select>
                                @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-4 col-md-4">
                                <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                                <input type="date" name="due_date" id="due_date" class="form-control" placeholder="{{ __('Select due date') }}" value="{{ old('due_date') }}">
                                @error('due_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-4 col-md-4">
                                <label for="clientID" class="form-label">{{ __('Clients Linked') }}</label>
                                <select class="form-control" id="clientID" name="clientID" data-choices data-choices-sorting-false placeholder="{{ __('Select client...') }}">
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
                            <div class="col-xxl-12 col-md-12">
                                <label for="description" class="form-label">{{ __('Description') }}</label>
                                <textarea class="form-control" name="description" id="description" style="height: 300px;">{{ old('description') }}</textarea>
                                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-12 col-md-12">
                                <label for="comments" class="form-label">{{ __('Comments') }}</label>
                                <textarea class="form-control" name="comments" id="comments">{{ old('comments') }}</textarea>
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
                                <button type="submit" class="btn btn-primary">{{ __('Save Project') }}</button>
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
    var quill = new Quill('#description', {
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
<script>
    document.getElementById('due_date').flatpickr();
</script>
@stop
