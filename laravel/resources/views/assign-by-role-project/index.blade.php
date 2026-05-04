@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
<style>
    .table tbody tr:last-child td {
        border-bottom: inherit;
    }
    .table thead {
        background: #f1f1f1;
    }
    #projectsData_wrapper > .row:nth-of-type(2) {
        overflow-x: auto !important;
    }
    #projectsData_wrapper > .row:nth-of-type(3) {
        margin-top: 15px !important;
    }
</style>
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Manage By Roles Project Assignment') }}</h4>
                <div class="flex-shrink-0">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModalCenter"> + {{ __('Add Role') }} </button>
                </div>
            </div>
            <div class="card-body">
                <table id="projectsData" class="display table table-bordered table-responsive" style="margin-top: 20px !important;">
                    <thead>
                        <tr>
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('Role Name') }}</th>
                            <th>{{ __('Created Date') }}</th>
                            <th style="width: 150px;">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">{{ __('Add Role') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <form id="projectDetails" enctype="multipart/form-data">
            @csrf
                <div class="modal-body">
                    <div class="row gy-4 mb-2">
                        <div class="col-xxl-12 col-md-12">
                            <label for="roles" class="form-label">{{ __('Roles') }}</label>
                            <select class="form-control" name="roles" id="roles">
                                <option value="">{{ __('Please select role') }}</option>
                                @if(!empty($roles))
                                    @foreach($roles as $value)
                                        <option value="{{ $value->id }}">{{ \App\Support\UiText::role($value->name) }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <span class="text-danger error-text roles_error"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        let table = $('#projectsData').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('get-assign-by-role-project.data') }}",
            language: window.codexDataTableLanguage(),
            columns: [
                { data: 'id', name: 'id' },
                { data: 'role_id', name: 'role_id' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            error: function (xhr, error, code) {
                console.log(xhr, error, code);
            }
        });
    });
</script>

<script type="text/javascript">
    function deleteUser(userId) {
        var recID = userId;
        Swal.fire({
            html: '<div class="mt-3"><lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon><div class="mt-4 pt-2 fs-15 mx-5"><p class="text-muted mx-4 mb-0">{{ __('Are you Sure You want to Delete this assigned role ?') }}</p></div></div>',
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
                    url: '/assign-by-role-project/delete/' + recID,
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
                            $('#projectsData').DataTable().ajax.reload(null, false);
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

<script type="text/javascript">
$(document).ready(function () {
    $("#projectDetails").on('submit', function (e) {
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('assign-by-role-project.store') }}",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $(".error-text").text('');
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __('Success') }}',
                        text: response.message,
                        showConfirmButton: true,
                        timer: 2000
                    });
                    $("#projectDetails")[0].reset();
                    $("#exampleModalCenter").modal('hide');
                    $('#projectsData').DataTable().ajax.reload(null, false);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('Error') }}',
                        text: '{{ __('Something went wrong!') }}',
                    });
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function (key, value) {
                        $("." + key + "_error").text(value[0]);
                    });

                    Swal.fire({
                        icon: 'warning',
                        title: '{{ __('Validation Error') }}',
                        text: '{{ __('Please fix the highlighted errors and try again.') }}',
                    });

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('Server Error') }}',
                        text: "Error: " + xhr.statusText,
                    });
                }
            }
        });
    });
});
</script>
@stop
