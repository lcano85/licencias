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
    #calendarData_wrapper > .row:nth-of-type(2) {
        overflow-x: auto !important;
    }
    #calendarData_wrapper > .row:nth-of-type(3) {
        margin-top: 15px !important;
    }
</style>
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Schedule List') }}</h4>
            </div>

            <div class="card-body">
                <table id="calendarData" class="display table table-bordered table-responsive" style="margin-top: 20px !important;">
                    <thead>
                        <tr>
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('Schedule Name') }}</th>
                            <th>{{ __('Schedule Type') }}</th>
                            <th>{{ __('Day & Time') }}</th>
                            <th>{{ __('Location') }}</th>
                            <th>{{ __('Creator') }}</th>
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

        let table = $('#calendarData').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('get-calendars.data') }}",
            order: [[0, "desc"]], // default sorting by ID
            language: window.codexDataTableLanguage(),
            columns: [
                { data: 'id', name: 'calendar.id' },
                { data: 'schedule_title', name: 'calendar.schedule_title' },
                { data: 'type', name: 'calendar.type' },
                { data: 'start', name: 'calendar.start' },
                { data: 'location', name: 'calendar.location' },
                { data: 'creator_name', name: 'users.name' },
                { data: 'created_at', name: 'calendar.created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        table.on('draw', function () {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
        });

    });
</script>
@stop
