@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
<style>
    .table tbody tr:last-child td { border-bottom: inherit; }
    .table thead { background: #f1f1f1; }
    .filter-section { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    .filter-title { font-weight: 600; margin-bottom: 15px; color: #495057; }
    #licensesAgreements_wrapper > .row:nth-of-type(3) {
        margin-top: 15px !important;
    }
    #licensesAgreements th, 
    #licensesAgreements td {
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
                <h4 class="card-title mb-0 flex-grow-1">{{ __('menu.licenses_agreements') }}</h4>
                <div class="flex-shrink-0">
                    <a href="{{ route('licenses-agreements.create') }}" class="btn btn-primary btn-sm">
                        <i class="ri-add-line me-1"></i> {{ __('Create License') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Advanced Filters -->
                <div class="filter-section">
                    <div class="filter-title">
                        <i class="ri-filter-3-line me-2"></i>{{ __('Advanced Filters') }}
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Commercial Name') }}</label>
                            <input type="text" id="commercialNameFilter" class="form-control form-control-sm" placeholder="{{ __('Search...') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Category') }}</label>
                            <input type="text" id="categoryFilter" class="form-control form-control-sm" placeholder="{{ __('Search...') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Subcategory') }}</label>
                            <input type="text" id="subcategoryFilter" class="form-control form-control-sm" placeholder="{{ __('Search...') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Licensed Concept') }}</label>
                            <input type="text" id="conceptFilter" class="form-control form-control-sm" placeholder="{{ __('Search...') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Licensed Environment') }}</label>
                            <select id="environmentFilter" class="form-select form-select-sm">
                                <option value="">{{ __('All') }}</option>
                                @foreach($environments as $id => $name)
                                    <option value="{{ $id }}">{{ __($name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Origin') }}</label>
                            <select id="originFilter" class="form-select form-select-sm">
                                <option value="">{{ __('All') }}</option>
                                <option value="License">{{ __('License') }}</option>
                                <option value="Transaction">{{ __('Transaction') }}</option>
                                <option value="Conciliation">{{ __('Conciliation') }}</option>
                                <option value="Sentences">{{ __('Sentences') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Billing Frequency') }}</label>
                            <select id="frequencyFilter" class="form-select form-select-sm">
                                <option value="">{{ __('All') }}</option>
                                <option value="Monthly">{{ __('Monthly') }}</option>
                                <option value="Quarterly">{{ __('Quarterly') }}</option>
                                <option value="Annual">{{ __('Annual') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select id="statusFilter" class="form-select form-select-sm">
                                <option value="">{{ __('All') }}</option>
                                <option value="1">{{ __('Active') }}</option>
                                <option value="2">{{ __('Canceled') }}</option>
                                <option value="3">{{ __('Suspended') }}</option>
                                <option value="4">{{ __('Expired') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Expiration') }}</label>
                            <select id="expirationFilter" class="form-select form-select-sm">
                                <option value="">{{ __('All') }}</option>
                                <option value="valid">{{ __('Valid') }}</option>
                                <option value="expired">{{ __('Expired') }}</option>
                                <option value="expiring_30">{{ __('Expiring in 30 days') }}</option>
                                <option value="no_end_date">{{ __('No end date') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-secondary btn-sm w-100" id="clearFilters">
                                <i class="ri-refresh-line me-1"></i> {{ __('Clear Filters') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive table-centered">
                    <table id="licensesAgreements" class="display table table-bordered table-responsive mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Commercial Name') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Subcategory') }}</th>
                                <th>{{ __('Licensed Concept') }}</th>
                                <th>{{ __('Licensed Environment') }}</th>
                                <th>{{ __('Total') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Start Date') }}</th>
                                <th>{{ __('End Date') }}</th>
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
    $(document).ready(function () {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
        
        let table = $('#licensesAgreements').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('get-licenses-agreements.data') }}",
                data: function (d) {
                    d.commercialNameFilter = $('#commercialNameFilter').val();
                    d.categoryFilter       = $('#categoryFilter').val();
                    d.subcategoryFilter    = $('#subcategoryFilter').val();
                    d.conceptFilter        = $('#conceptFilter').val();
                    d.environmentFilter    = $('#environmentFilter').val();
                    d.originFilter         = $('#originFilter').val();
                    d.frequencyFilter      = $('#frequencyFilter').val();
                    d.statusFilter         = $('#statusFilter').val();
                    d.expirationFilter     = $('#expirationFilter').val();
                }
            },
            language: window.codexDataTableLanguage(),
            columns: [
                { data: 'commercialName', name: 'commercialName' },
                { data: 'category', name: 'category' },
                { data: 'subcategory', name: 'subcategory' },
                { data: 'licensedConcept', name: 'licensedConcept' },
                { data: 'licensedEnvironment', name: 'licensedEnvironment' },
                { data: 'annualValue', name: 'annualValue' },
                { data: 'status', name: 'status' },
                { data: 'startDate', name: 'startDate' },
                { data: 'endDate', name: 'endDate' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            error: function (xhr, error, code) {
                console.log(xhr, error, code);
            }
        });
        
        // Filter change events
        $('#commercialNameFilter, #categoryFilter, #subcategoryFilter, #conceptFilter, #environmentFilter, #originFilter, #frequencyFilter, #statusFilter, #expirationFilter')
            .on('change keyup', function () { table.ajax.reload(); });

        // Clear filters
        $('#clearFilters').on('click', function() {
            $('#commercialNameFilter, #categoryFilter, #subcategoryFilter, #conceptFilter').val('');
            $('#environmentFilter, #originFilter, #frequencyFilter, #statusFilter, #expirationFilter').val('');
            table.ajax.reload();
        });

        table.on('draw', function () {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
        });
    });

    function deleteActivity(userId) {
        var recID = userId;
        Swal.fire({
            html: '<div class="mt-3"><lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon><div class="mt-4 pt-2 fs-15 mx-5"><p class="text-muted mx-4 mb-0">{{ __('Are you Sure You want to Delete this Licenses / Agreements ?') }}</p></div></div>',
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
                    url: '/licenses-agreements/delete/' + recID,
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "recID": recID,
                    },
                    success: function(response) {
                        Swal.fire('{{ __('Deleted!') }}', response.success, 'success')
                            .then(() => { $('#licensesAgreements').DataTable().ajax.reload(null, false); });
                    },
                    error: function(xhr) {
                        Swal.fire('{{ __('Error!') }}', xhr.responseJSON.error, 'error');
                    }
                });
            }
        });
    }
</script>
@stop
