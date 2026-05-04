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
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Invoice Consecutive List') }}</h4>
                <div class="flex-shrink-0">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModalCenter"> + {{ __('Add Invoice Consecutive') }} </button>
                </div>
            </div>

            <div class="card-body">
                <table id="invoiceConsecutiveDataTable" class="display table table-bordered table-responsive" style="width:100%">
                    <thead>
                        <tr>
                            <th>{{ __('Invoice Consecutive Name') }}</th>
                            <th>{{ __('Next Number') }}</th>
                            <th>{{ __('Created At') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal (Add + Edit shared) -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">{{ __('Add Consecutive') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>

            <form id="consecutiveDetails" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="consecutiveID" id="consecutiveID">
                <div class="modal-body">
                    <div class="row gy-4 mb-2">
                        <div class="col-xxl-12 col-md-12">
                            <label for="consecutive_name" class="form-label">{{ __('Consecutive Name') }}</label>
                            <input type="text" name="consecutive_name" id="consecutive_name" class="form-control" placeholder="{{ __('Consecutive Name') }}">
                            <span class="text-danger error-text consecutive_name_error"></span>
                        </div>
                    </div>
                    <div class="row gy-4 mb-2">
                        <div class="col-xxl-12 col-md-12">
                            <label for="next_number" class="form-label">{{ __('Set Starting Number') }}</label>
                            <input type="number" min="1" name="next_number" id="next_number" class="form-control" placeholder="{{ __('e.g., 1254') }}">
                            <small class="text-muted">{{ __('This will be used as the next invoice number in the sequence.') }}</small>
                            <span class="text-danger error-text next_number_error"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary" id="statusSubmitBtn">{{ __('Save changes') }}</button>
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
        let table = $('#invoiceConsecutiveDataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('get-invoice-consecutive.data') }}"
            },
            language: window.codexDataTableLanguage(),
            columns: [
                { data: 'consecutive_name', name: 'consecutive_name' },
                { data: 'next_number', name: 'next_number' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            error: function (xhr, error, code) {
                console.log(xhr, error, code);
            }
        });
    });
</script>

<!-- <script src="{{ asset('admin/js/sweetalert2.min.js')}}"></script> -->
<script type="text/javascript">
    let editId = null;

    $(document).on('click', '.editBtn', function () {
        editId = $(this).data('id');
        $(".error-text").text('');

        $.get("{{ url('/invoice-consecutive/edit') }}/" + editId, function (res) {
            if(res.success){
                const c = res.data;
                $('#consecutiveID').val(c.id);
                $('#consecutive_name').val(c.consecutive_name);
                $('#next_number').val(c.next_number);
                $('#exampleModalCenterTitle').text("{{ __('Edit Invoice Consecutive') }}");
                $('#statusSubmitBtn').text("{{ __('Update') }}");
                $('#exampleModalCenter').modal('show');
            } else {
                Swal.fire({icon:'error', title:"{{ __('Error') }}", text:"{{ __('Record not found!') }}"});
            }
        }).fail(function(xhr){
            Swal.fire({icon:'error', title:"{{ __('Error') }}", text:(xhr.responseJSON?.error || "{{ __('Failed to load record') }}")});
        });
    });

    // Clicking "Add Status" button resets modal to ADD mode
    $('[data-bs-target="#exampleModalCenter"]').on('click', function () {
        editId = null;
        $('#consecutiveID').val('');
        $('#consecutiveDetails')[0].reset();
        $(".error-text").text('');
        $('#exampleModalCenterTitle').text("{{ __('Add Invoice Consecutive') }}");
        $('#statusSubmitBtn').text("{{ __('Save changes') }}");
    });

    // Submit handler for both Add and Edit
    $(document).ready(function () {
        $("#consecutiveDetails").on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            let url = (editId)
                ? "{{ url('/invoice-consecutive/update') }}/" + editId
                : "{{ route('invoice-consecutive.store') }}";

            $.ajax({
                url: url,
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () { $(".error-text").text(''); },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __('Success') }}',
                            text: response.message,
                            showConfirmButton: true,
                            timer: 2000
                        });
                        $("#consecutiveDetails")[0].reset();
                        $("#exampleModalCenter").modal('hide');
                        $('#invoiceConsecutiveDataTable').DataTable().ajax.reload(null, false);
                        editId = null;
                    } else {
                        Swal.fire({ icon: 'error', title: '{{ __('Error') }}', text: response.message || '{{ __('Something went wrong!') }}' });
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        const errs = xhr.responseJSON.errors || {};
                        Object.keys(errs).forEach(function (key) {
                            $("." + key + "_error").text(errs[key][0]);
                        });
                        Swal.fire({ icon: 'warning', title: '{{ __('Validation Error') }}', text: '{{ __('Please fix the highlighted errors and try again.') }}' });
                    } else {
                        Swal.fire({ icon: 'error', title: '{{ __('Server Error') }}', text: "Error: " + (xhr.responseJSON?.message || xhr.statusText) });
                    }
                }
            });
        });
    });
</script>
<script type="text/javascript">
    function deleteStatus(consecutiveId) {
        var recID = consecutiveId;
        Swal.fire({
            html: '<div class="mt-3"><lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon><div class="mt-4 pt-2 fs-15 mx-5"><p class="text-muted mx-4 mb-0">{{ __('Are you Sure You want to Delete this consecutive ?') }}</p></div></div>',
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
                    url: '/invoice-consecutive/delete/' + recID,
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "recID": recID,
                    },
                    success: function(response) {
                        Swal.fire('{{ __('Deleted!') }}', response.success, 'success')
                            .then(() => { $('#invoiceConsecutiveDataTable').DataTable().ajax.reload(null, false); });
                    },
                    error: function(xhr) {
                        Swal.fire('{{ __('Error!') }}', (xhr.responseJSON?.error || '{{ __('Delete') }}'), 'error');
                    }
                });
            }
        });
    }
</script>
@stop
