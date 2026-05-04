@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
<link rel="stylesheet" href="{{ asset('admin/fullcalendar/main.min.css') }}" />
<style>
    .fc-v-event .fc-event-main-frame {
        height: 100%;
        display: flex;
        flex-direction: inherit;
    }
    .fc-daygrid-event-dot {
        display: none;
    }
</style>
@stop

@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Calendar List') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-3">
                        <div class="d-grid">
                            <button type="button" class="btn btn-primary" id="btn-new-event">
                                <i class="bx bx-plus fs-18 me-2"></i>
                                {{ __('Add New Schedule') }}
                            </button>
                        </div>
                        <div id="external-events">
                            <br>
                            <p class="text-muted">{{ __('Drag and drop your type or click in the calendar') }}</p>
                            <div class="external-event bg-soft-primary text-primary" data-class="bg-primary">
                                <i class="bx bxs-circle me-2 vertical-middle"></i>{{ __('Activity') }}
                            </div>
                            <div class="external-event bg-soft-info text-info" data-class="bg-info">
                                <i class="bx bxs-circle me-2 vertical-middle"></i>{{ __('Meeting') }}
                            </div>
                            <div class="external-event bg-soft-success text-success" data-class="bg-success">
                                <i class="bx bxs-circle me-2 vertical-middle"></i>{{ __('Hearing') }}
                            </div>
                            <div class="external-event bg-soft-warning text-warning" data-class="bg-warning">
                                <i class="bx bxs-circle me-2 vertical-middle"></i>{{ __('Deadline') }}
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-9">
                        <div class="mt-4 mt-lg-0">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add New Event MODAL -->
        <div class="modal fade" id="event-modal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form class="needs-validation" name="event-form" id="forms-event" enctype="multipart/form-data" novalidate>
                        <div class="modal-header p-3 border-bottom-0">
                            <h5 class="modal-title" id="modal-title">{{ __('Schedule') }} </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                        </div>
                        <div class="modal-body px-3 pb-3 pt-0">
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-2">
                                        <label class="control-label form-label">{{ __('Schedule Title') }}</label>
                                        <input class="form-control" placeholder="{{ __('Enter schedule title') }}" type="text" name="title" id="event-title" required/>
                                        <div class="invalid-feedback">{{ __('Please provide a valid event name') }}</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-2">
                                        <label class="control-label form-label">{{ __('Category') }}</label>
                                        <select class="form-select" name="category" id="event-category" required>
                                            <option value="1" data-ecolor="bg-primary">{{ __('Activity') }}</option>
                                            <option value="2" data-ecolor="bg-info">{{ __('Meeting') }}</option>
                                            <option value="3" data-ecolor="bg-success">{{ __('Hearing') }}</option>
                                            <option value="4" data-ecolor="bg-warning">{{ __('Deadline') }}</option>
                                        </select>
                                        <div class="invalid-feedback">{{ __('Please select a valid event category') }}</div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-2">
                                        <label class="control-label form-label">{{ __('Day & Time') }}</label>
                                        <input type="text" name="due_date" id="due_date" class="form-control" placeholder="{{ __('Select Day & Time') }}" required>
                                        <div class="invalid-feedback">{{ __('Please select a valid event category') }}</div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-2">
                                        <label class="control-label form-label">{{ __('Location, Department/Office') }}</label>
                                        <textarea name="location" id="location" class="form-control" placeholder="{{ __('Location, Department/Office') }}"  required> </textarea>
                                        <div class="invalid-feedback">{{ __('Please select a valid event category') }}</div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-2">
                                        <label class="control-label form-label">{{ __('Description') }}</label>
                                        <textarea name="description" id="description" class="form-control" placeholder="{{ __('Description') }}" required> </textarea>
                                        <div class="invalid-feedback">{{ __('Please select a valid event category') }}</div>
                                    </div>
                                </div>

                                @if(isset($users))
                                <div class="col-12 mb-2">
                                    <label for="guests" class="form-label">{{ __('Guests') }}</label>
                                    <select class="form-control" id="choices-multiple-default" name="guests[]" multiple>
                                        @foreach($users as $value)
                                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <button type="button" class="btn btn-danger" id="btn-delete-event">{{ __('Delete') }}</button>
                                </div>
                                <div class="col-6 text-end">
                                    <button type="button" class="btn btn-light me-1" data-bs-dismiss="modal">{{ __('Close') }}</button>
                                    <button type="submit" class="btn btn-primary" id="btn-save-event">{{ __('Save') }}</button>
                                </div>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script src="{{ asset('admin/fullcalendar/main.min.js')}}"></script>
@if(app()->getLocale() === 'es')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>
@endif
<script src="{{ asset('admin/fullcalendar/app-calendar.js')}}"></script>
<script>
    document.getElementById('due_date').flatpickr({
        enableTime: true,
        dateFormat: "Y-m-d H:i"
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let guestChoices = null;
        document.getElementById('event-modal').addEventListener('shown.bs.modal', function () {
            const element = document.getElementById('choices-multiple-default');
            if (guestChoices) {
                guestChoices.destroy();
            }
            guestChoices = new Choices(element, {
                removeItemButton: true,
                placeholder: true,
                placeholderValue: "{{ __('Select users...') }}",
            });
        });
    });
</script>
@stop
