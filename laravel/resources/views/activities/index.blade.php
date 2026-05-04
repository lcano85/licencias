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
    #activityData_wrapper > .row:nth-of-type(3) {
        margin-top: 15px !important;
    }
    #activityData th, 
    #activityData td {
        white-space: normal !important;
        word-break: break-word;
    }

    #activityDataPersonal_wrapper > .row:nth-of-type(3) {
        margin-top: 15px !important;
    }
    #activityDataPersonal th, 
    #activityDataPersonal td {
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
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Client Activities') }}</h4>
                <div class="flex-shrink-0">
                    <a href="{{ route('activity.create') }}" class="btn btn-primary btn-sm"> + {{ __('Create Activity') }}</a>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="statusFilter" class="form-label">{{ __('Filter by Status') }}</label>
                    <select id="statusFilter" class="form-select" style="width:200px;">
                        <option value="">{{ __('All') }}</option>
                        <option value="1">{{ __('On time') }}</option>
                        <option value="2">{{ __('Delayed') }}</option>
                        <option value="3">{{ __('Priority') }}</option>
                        <option value="4">{{ __('Completed') }}</option>
                    </select>
                </div>
                <div class="table-responsive table-centered">
                    <table id="activityData" class="display table table-bordered table-responsive mb-0" style="margin-top: 20px !important;">
                        <thead>
                            <tr>
                                <th>{{ __('Related Client') }}</th>
                                <th>{{ __('Activity Name') }}</th>
                                <th>{{ __('Activity Type') }}</th>
                                <th>{{ __('Creator') }}</th>
                                <th>{{ __('Responsible') }}</th>
                                <th>{{ __('Linked Project') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Sub Status') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Created Date') }}</th>
                                <th>{{ __('Last Updated Date') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Personal Activities') }}</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="personalStatusFilter" class="form-label">{{ __('Filter by Status') }}</label>
                    <select id="personalStatusFilter" class="form-select" style="width:200px;">
                        <option value="">{{ __('All') }}</option>
                        <option value="1">{{ __('On time') }}</option>
                        <option value="2">{{ __('Delayed') }}</option>
                        <option value="3">{{ __('Priority') }}</option>
                        <option value="4">{{ __('Completed') }}</option>
                    </select>
                </div>
                <div class="table-responsive table-centered">
                    <table id="activityDataPersonal" class="display table table-bordered table-responsive mb-0" style="margin-top: 20px !important;">
                        <thead>
                            <tr>
                                <th>{{ __('Related Client') }}</th>
                                <th>{{ __('Activity Name') }}</th>
                                <th>{{ __('Activity Type') }}</th>
                                <th>{{ __('Creator') }}</th>
                                <th>{{ __('Responsible') }}</th>
                                <th>{{ __('Linked Project') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Sub Status') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Created Date') }}</th>
                                <th>{{ __('Last Updated Date') }}</th>
                                <th>{{ __('Action') }}</th>
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
    const activityTableLanguage = {
        search: "{{ __('Search...') }}",
        zeroRecords: "{{ __('No matching records found') }}",
        info: "{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}",
        infoEmpty: "{{ __('Showing 0 to 0 of 0 entries') }}",
        infoFiltered: "{{ __('(filtered from _MAX_ total entries)') }}",
        lengthMenu: "{{ __('Show _MENU_ entries') }}",
        loadingRecords: "{{ __('Loading...') }}",
        processing: "{{ __('Processing') }}",
        emptyTable: "{{ __('No data available in table') }}",
        paginate: {
            first: "{{ __('First') }}",
            last: "{{ __('Last') }}",
            next: "{{ __('Next') }}",
            previous: "{{ __('Previous') }}"
        },
        aria: {
            sortAscending: "{{ __('Sort ascending') }}",
            sortDescending: "{{ __('Sort descending') }}"
        }
    };

    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        let table = $('#activityData').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('get-activities.data') }}",
                data: function (d) {
                    d.status = $('#statusFilter').val();
                }
            },
            language: activityTableLanguage,
            columns: [
                { data: 'clientID', name: 'clientID' },
                { data: 'activity_name', name: 'activity_name' },
                { data: 'activity_type', name: 'activity_type' },
                { data: 'created_by', name: 'created_by' },
                { data: 'assign_by', name: 'assign_by' },
                { data: 'projectID', name: 'projectID' },
                { data: 'status', name: 'status' },
                { data: 'sub_status', name: 'sub_status' },
                { data: 'due_date', name: 'due_date' },
                { data: 'created_at', name: 'created_at' },
                { data: 'updated_at', name: 'updated_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            error: function (xhr, error, code) {
                console.log(xhr, error, code);
            }
        });
        $('#statusFilter').change(function () {
            table.ajax.reload();
        });

        table.on('draw', function () {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        let table = $('#activityDataPersonal').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('get-personal-activities.data') }}",
                type: 'POST',
                data: function (d) {
                    d.status = $('#personalStatusFilter').val();
                }
            },
            language: activityTableLanguage,
            columns: [
                { data: 'clientID', name: 'clientID' },
                { data: 'activity_name', name: 'activity_name' },
                { data: 'activity_type', name: 'activity_type' },
                { data: 'created_by', name: 'created_by' },
                { data: 'assign_by', name: 'assign_by' },
                { data: 'projectID', name: 'projectID' },
                { data: 'status', name: 'status' },
                { data: 'sub_status', name: 'sub_status' },
                { data: 'due_date', name: 'due_date' },
                { data: 'created_at', name: 'created_at' },
                { data: 'updated_at', name: 'updated_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            error: function (xhr, error, code) {
                console.log(xhr, error, code);
            }
        });
        $('#personalStatusFilter').change(function () {
            table.ajax.reload();
        });

        table.on('draw', function () {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
        });
    });
</script>
<script type="text/javascript">
    function deleteActivity(userId) {
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
                    url: '/activity/delete/' + recID,
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
                            $('#activityData').DataTable().ajax.reload(null, false);
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
