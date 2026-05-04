@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
<!-- <link rel="stylesheet" href="{{ asset('admin/css/sweetalert2.min.css') }}" /> -->
<style>
    .table tbody tr:last-child td {
        border-bottom: inherit;
    }
    .table thead {
        background: #f1f1f1;
    }
</style>
@stop

@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Clients List') }}</h4>
                <div class="flex-shrink-0">
                    <a href="{{ route('client.create') }}" class="btn btn-primary btn-sm"> + {{ __('Add Client') }}</a>
                </div>
            </div>
            <div class="row" style="margin-top: 15px;margin-left: 25px;">
                <div class="col-md-3">
                    <select id="filterCategory" class="form-select">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filterUseType" class="form-select">
                        <option value="">{{ __('All Use Types') }}</option>
                        @foreach($useTypes as $use)
                            <option value="{{ $use->id }}">{{ $use->use_types_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="card-body">
                <table id="userDataTable" class="display table table-bordered table-responsive" style="width:100%">
                    <thead>
                        <tr>
                            <th>{{ __('Commercial Name') }}</th>
                            <th>{{ __('Legal Name') }}</th>
                            <th>{{ __('User Category') }}</th>
                            <th>{{ __('Sub Category') }}</th>
                            <th>{{ __('Types of Uses') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Action') }}</th>
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
        let table = $('#userDataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('get-clients.data') }}",
                data: function (d) {
                    d.categoryID = $('#filterCategory').val();
                    d.useTypes   = $('#filterUseType').val();
                }
            },
            language: window.codexDataTableLanguage(),
            columns: [
                { data: 'commercialName', name: 'commercialName' },
                { data: 'legalName', name: 'legalName' },
                { data: 'categoryID', name: 'categoryID' },
                { data: 'subcategoryID', name: 'subcategoryID' },
                { data: 'useTypes', name: 'useTypes' },
                { data: 'client_status', name: 'client_status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            error: function (xhr, error, code) {
                console.log(xhr, error, code);
            }
        });
        $('#filterCategory, #filterUseType').change(function () {
            table.draw();
        });
    });
</script>

<!-- <script src="{{ asset('admin/js/sweetalert2.min.js')}}"></script> -->
<script type="text/javascript">
    function deleteUser(userId) {
        var recID = userId;
        Swal.fire({
            html: '<div class="mt-3"><lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon><div class="mt-4 pt-2 fs-15 mx-5"><p class="text-muted mx-4 mb-0">{{ __('Are you Sure You want to Delete this clients ?') }}</p></div></div>',
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
                    url: '/client/delete/' + recID,
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
                            $('#userDataTable').DataTable().ajax.reload(null, false);
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
    $(document).on('click', '.send-reset-link', function () {
        const userId = $(this).data('id');

        Swal.fire({
            html: '<div class="mt-3"><div class="mt-4 pt-2 fs-15 mx-5"><p class="text-muted mx-4 mb-0">{{ __('Are you sure you want to send a password reset email to this user?') }}</p></div></div>',
            showCancelButton: true,
            customClass: {
                confirmButton: "btn btn-primary w-xs me-2 mb-1",
                cancelButton: "btn btn-danger w-xs mb-1"
            },
            confirmButtonText: '{{ __('Yes, send it!') }}'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route('user.sendResetLink') }}',
                    type: 'POST',
                    data: {
                        user_id: userId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        Swal.fire(
                            '{{ __('Sent!') }}',
                            response.message,
                            'success'
                        );
                    },
                    error: function (xhr) {
                        Swal.fire(
                            '{{ __('Error!') }}',
                            xhr.responseJSON?.message || '{{ __('Failed to send reset link.') }}',
                            'error'
                        );
                    }
                });
            }
        });
    });
</script>
@stop
