@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
<style>
    .table tbody tr:last-child td { border-bottom: inherit; }
    .table thead { background: #f1f1f1; }
    #categoryData_wrapper > .row:nth-of-type(2) { overflow-x: auto !important; }
    #categoryData_wrapper > .row:nth-of-type(3) { margin-top: 15px !important; }
</style>
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Client Category') }}</h4>
                <div class="flex-shrink-0">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModalCenter">{{ __('+ Add Category') }}</button>
                </div>
            </div>
            <div class="card-body">
                <table id="categoryData" class="display table table-bordered table-responsive" style="margin-top: 20px !important;">
                    <thead>
                        <tr>
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('Category Name') }}</th>
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

<!-- Modal (Add + Edit shared) -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">{{ __('Add Category') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>

            <form id="categoryDetails" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="category_id" id="category_id">
                <div class="modal-body">
                    <div class="row gy-4 mb-2">
                        <div class="col-xxl-12 col-md-12">
                            <label for="category_name" class="form-label">{{ __('Category Name') }}</label>
                            <input type="text" name="category_name" id="category_name" class="form-control">
                            <span class="text-danger error-text category_name_error"></span>
                        </div>

                        {{-- If you want status later, uncomment:
                        <div class="col-xxl-12 col-md-12">
                            <label for="category_status" class="form-label">{{ __('Status') }}</label>
                            <select name="category_status" id="category_status" class="form-select">
                                <option value="1">{{ __('Active') }}</option>
                                <option value="2">{{ __('In Active') }}</option>
                            </select>
                            <span class="text-danger error-text category_status_error"></span>
                        </div>
                        --}}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary" id="categorySubmitBtn">{{ __('Save changes') }}</button>
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
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $('#categoryData').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('get-category.data') }}",
            language: window.codexDataTableLanguage(),
            columns: [
                { data: 'id', name: 'id' },
                { data: 'category_name', name: 'category_name' },
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
            html: '<div class="mt-3"><lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon><div class="mt-4 pt-2 fs-15 mx-5"><p class="text-muted mx-4 mb-0">Are you Sure You want to Delete this category ?</p></div></div>',
            showCancelButton: !0,
            customClass: {
                confirmButton: "btn btn-primary w-xs me-2 mb-1",
                cancelButton: "btn btn-danger w-xs mb-1"
            },
            confirmButtonText: "Yes, Delete It!",
            buttonsStyling: !1,
            showCloseButton: !0
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/category/delete/' + recID,
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "recID": recID,
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', response.success, 'success')
                            .then(() => { $('#categoryData').DataTable().ajax.reload(null, false); });
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', (xhr.responseJSON?.error || 'Failed to delete'), 'error');
                    }
                });
            }
        });
    }
</script>

<script type="text/javascript">
let editId = null;

$(document).on('click', '.editBtn', function () {
    editId = $(this).data('id');
    $(".error-text").text('');

    $.get("{{ url('/category/edit') }}/" + editId, function (res) {
        if(res.success){
            const c = res.data;
            $('#category_id').val(c.id);
            $('#category_name').val(c.category_name);
            // If you add status later:
            // $('#category_status').val(c.category_status).trigger('change');

            $('#exampleModalCenterTitle').text('Edit Category');
            $('#categorySubmitBtn').text('Update');
            $('#exampleModalCenter').modal('show');
        } else {
            Swal.fire({icon:'error', title:'Error', text:'Record not found!'});
        }
    }).fail(function(xhr){
        Swal.fire({icon:'error', title:'Error', text:(xhr.responseJSON?.error || 'Failed to load record')});
    });
});

// Clicking "Add Category" button resets modal to ADD mode
$('[data-bs-target="#exampleModalCenter"]').on('click', function () {
    editId = null;
    $('#category_id').val('');
    $('#categoryDetails')[0].reset();
    $(".error-text").text('');
    $('#exampleModalCenterTitle').text('Add Category');
    $('#categorySubmitBtn').text('Save changes');
});

// Submit handler for both Add and Edit
$(document).ready(function () {
    $("#categoryDetails").on('submit', function (e) {
        e.preventDefault();

        let formData = new FormData(this);
        let url = (editId)
            ? "{{ url('/category/update') }}/" + editId
            : "{{ route('category.store') }}";

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
                        title: 'Success',
                        text: response.message,
                        showConfirmButton: true,
                        timer: 2000
                    });
                    $("#categoryDetails")[0].reset();
                    $("#exampleModalCenter").modal('hide');
                    $('#categoryData').DataTable().ajax.reload(null, false);
                    editId = null;
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Something went wrong!' });
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const errs = xhr.responseJSON.errors || {};
                    Object.keys(errs).forEach(function (key) {
                        $("." + key + "_error").text(errs[key][0]);
                    });
                    Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Please fix the highlighted errors and try again.' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Server Error', text: "Error: " + (xhr.responseJSON?.message || xhr.statusText) });
                }
            }
        });
    });
});
</script>
@stop
