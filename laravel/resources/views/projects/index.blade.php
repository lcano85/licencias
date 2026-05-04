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
    #projectsData_wrapper > .row:nth-of-type(3) {
        margin-top: 15px !important;
    }
    #projectsData th, 
    #projectsData td {
        white-space: normal !important;
        word-break: break-word;
    }
</style>
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Project List') }}</h4>
                <div class="flex-shrink-0">
                    <a href="{{ route('project.create') }}" class="btn btn-primary btn-sm"> + {{ __('Add Project') }}</a>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="statusFilter" class="form-label">{{ __('Filter by Status') }}</label>
                    <select id="statusFilter" class="form-select" style="width:200px;">
                        <option value="">{{ __('All') }}</option>
                        <option value="1">{{ __('Active') }}</option>
                        <option value="2">{{ __('In active') }}</option>
                        <option value="3">{{ __('Finished') }}</option>
                    </select>
                </div>
                <div class="table-responsive table-centered">
                    <table id="projectsData" class="display table table-bordered table-responsive mb-0" style="margin-top: 20px !important;">
                        <thead>
                            <tr>
                                <th>{{ __('Project Name') }}</th>
                                <th>{{ __('Creator') }}</th>
                                <th>{{ __('Participants') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created Date') }}</th>
                                <th>{{ __('Last Update Date') }}</th>
                                <th>{{ __('Number of Activities') }}</th>
                                <th>{{ __('Related Client') }}</th>
                                <th style="width: 150px;">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
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
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('get-projects.data') }}",
                data: function (d) {
                    d.status = $('#statusFilter').val();
                }
            },
            language: window.codexDataTableLanguage(),
            columns: [
                { data: 'project_title', name: 'project_title' },
                { data: 'created_by', name: 'created_by' },
                { data: 'assign_by', name: 'assign_by' },
                { data: 'status', name: 'status' },
                { data: 'created_at', name: 'created_at' },
                { data: 'updated_at', name: 'updated_at' },
                { data: 'activities_count', name: 'activities_count' },
                { data: 'clientID', name: 'clientID' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            error: function (xhr, error, code) {
                console.log(xhr, error, code);
            }
        });
        $('#statusFilter').change(function () {
            table.ajax.reload();
        });
    });
</script>

<script type="text/javascript">
    function deleteUser(userId) {
        var recID = userId;
        Swal.fire({
            html: '<div class="mt-3"><lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon><div class="mt-4 pt-2 fs-15 mx-5"><p class="text-muted mx-4 mb-0">{{ __('Are you Sure You want to Delete this project ?') }}</p></div></div>',
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
                    url: '/project/delete/' + recID,
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
@stop
