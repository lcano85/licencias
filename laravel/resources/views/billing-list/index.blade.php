@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
<link href="{{ asset('admin/css/sidebar-slider.css') }}" rel="stylesheet" type="text/css" />
<style>
    .table tbody tr:last-child td {
        border-bottom: inherit;
    }
    .table thead {
        background: #f1f1f1;
    }
    #budgetData_wrapper > .row:nth-of-type(2) {
        overflow-x: auto !important;
    }
    #budgetData_wrapper > .row:nth-of-type(3) {
        margin-top: 15px !important;
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
                <h4 class="card-title mb-0 flex-grow-1">{{ __('menu.billing-list') }}</h4>
                <div class="flex-shrink-0">
                    <a href="{{ route('billing-list.create') }}" class="btn btn-primary btn-sm"> + {{ __('Create Billing') }}</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive table-centered">
                    <table id="budgetData" class="display table table-bordered table-responsive text-nowrap mb-0" style="margin-top: 20px !important;">
                        <thead>
                            <tr>
                                <th>{{ __('Invoice No') }}</th>
                                <th>{{ __('Commercial Name') }}</th>
                                <th>{{ __('Use Type') }}</th>
                                <th>{{ __('Company') }}</th>
                                <th>{{ __('Vat') }}</th>
                                <th>{{ __('Sub Total') }}</th>
                                <th>{{ __('Total') }}</th>
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
        let table = $('#budgetData').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('get-billing-list.data') }}"
            },
            language: window.codexDataTableLanguage(),
            columns: [
                { data: 'invoiceNumber', name: 'invoiceNumber' },
                { data: 'commercialName', name: 'commercialName' },
                { data: 'user_type', name: 'user_type' },
                { data: 'company', name: 'company' },
                { data: 'vat', name: 'vat' },
                { data: 'subTotal', name: 'subTotal' },
                { data: 'total', name: 'total' },
                { data: 'created_at', name: 'created_at' },
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
    function deleteActivity(userId) {
        var recID = userId;
        Swal.fire({
            html: '<div class="mt-3"><lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon><div class="mt-4 pt-2 fs-15 mx-5"><p class="text-muted mx-4 mb-0">{{ __('Are you Sure You want to Delete this billing?') }}</p></div></div>',
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
                    url: '/billing-list/delete/' + recID,
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
                            $('#budgetData').DataTable().ajax.reload(null, false);
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
