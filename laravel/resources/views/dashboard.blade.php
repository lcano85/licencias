@extends('layouts.app')
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
    #activityData_wrapper > .row:nth-of-type(2) {
        overflow-x: auto !important;
    }
    #activityData_wrapper > .row:nth-of-type(3) {
        margin-top: 15px !important;
    }
</style>
@stop

@section('content')

<!-- Start here.... -->
<div class="row">
    <div class="col-xxl-5">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-primary text-truncate mb-3" role="alert">
                    {{ __('We regret to inform you that our server is currently experiencing technical difficulties.') }}
                </div>
            </div>

            <div class="col-md-6">
                <div class="card overflow-hidden">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="avatar-md bg-soft-primary rounded">
                                    <iconify-icon icon="solar:cart-5-bold-duotone" class="avatar-title fs-32 text-primary"></iconify-icon>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-6 text-end">
                                <p class="text-muted mb-0 text-truncate">{{ __('Total Orders') }}</p>
                                <h3 class="text-dark mt-1 mb-0">13, 647</h3>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row-->
                    </div>
                    <!-- end card body -->
                    <div class="card-footer py-2 bg-light bg-opacity-50">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-success"> <i class="bx bxs-up-arrow fs-12"></i> 2.3%</span>
                                <span class="text-muted ms-1 fs-12">{{ __('Last Week') }}</span>
                            </div>
                            <a href="#!" class="text-reset fw-semibold fs-12">{{ __('View More') }}</a>
                        </div>
                    </div>
                    <!-- end card body -->
                </div>
                <!-- end card -->
            </div>
            <!-- end col -->
            <div class="col-md-6">
                <div class="card overflow-hidden">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="avatar-md bg-soft-primary rounded">
                                    <i class="bx bx-award avatar-title fs-24 text-primary"></i>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-6 text-end">
                                <p class="text-muted mb-0 text-truncate">{{ __('New Leads') }}</p>
                                <h3 class="text-dark mt-1 mb-0">9, 526</h3>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row-->
                    </div>
                    <!-- end card body -->
                    <div class="card-footer py-2 bg-light bg-opacity-50">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-success"> <i class="bx bxs-up-arrow fs-12"></i> 8.1%</span>
                                <span class="text-muted ms-1 fs-12">{{ __('Last Month') }}</span>
                            </div>
                            <a href="#!" class="text-reset fw-semibold fs-12">{{ __('View More') }}</a>
                        </div>
                    </div>
                    <!-- end card body -->
                </div>
                <!-- end card -->
            </div>
            <!-- end col -->
            <div class="col-md-6">
                <div class="card overflow-hidden">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="avatar-md bg-soft-primary rounded">
                                    <i class="bx bxs-backpack avatar-title fs-24 text-primary"></i>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-6 text-end">
                                <p class="text-muted mb-0 text-truncate">{{ __('Deals') }}</p>
                                <h3 class="text-dark mt-1 mb-0">976</h3>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row-->
                    </div>
                    <!-- end card body -->
                    <div class="card-footer py-2 bg-light bg-opacity-50">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-danger"> <i class="bx bxs-down-arrow fs-12"></i> 0.3%</span>
                                <span class="text-muted ms-1 fs-12">{{ __('Last Month') }}</span>
                            </div>
                            <a href="#!" class="text-reset fw-semibold fs-12">{{ __('View More') }}</a>
                        </div>
                    </div>
                    <!-- end card body -->
                </div>
                <!-- end card -->
            </div>
            <!-- end col -->
            <div class="col-md-6">
                <div class="card overflow-hidden">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="avatar-md bg-soft-primary rounded">
                                    <i class="bx bx-dollar-circle avatar-title text-primary fs-24"></i>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-6 text-end">
                                <p class="text-muted mb-0 text-truncate">{{ __('Booked Revenue') }}</p>
                                <h3 class="text-dark mt-1 mb-0">$123.6k</h3>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row-->
                    </div>
                    <!-- end card body -->
                    <div class="card-footer py-2 bg-light bg-opacity-50">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-danger"> <i class="bx bxs-down-arrow fs-12"></i> 10.6%</span>
                                <span class="text-muted ms-1 fs-12">{{ __('Last Month') }}</span>
                            </div>
                            <a href="#!" class="text-reset fw-semibold fs-12">{{ __('View More') }}</a>
                        </div>
                    </div>
                    <!-- end card body -->
                </div>
                <!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>
    <!-- end col -->

    <div class="col-xxl-7">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ __('Performance') }}</h4>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-light">{{ __('ALL') }}</button>
                        <button type="button" class="btn btn-sm btn-outline-light">{{ __('1M') }}</button>
                        <button type="button" class="btn btn-sm btn-outline-light">{{ __('6M') }}</button>
                        <button type="button" class="btn btn-sm btn-outline-light active">{{ __('1Y') }}</button>
                    </div>
                </div>
                <!-- end card-title-->

                <div dir="ltr">
                    <div id="dash-performance-chart" class="apex-charts"></div>
                </div>
            </div>
            <!-- end card body -->
        </div>
        <!-- end card -->
    </div>
    <!-- end col -->
</div>
<!-- end row -->

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ __('Conversions') }}</h5>
                <div id="conversions" class="apex-charts mb-2 mt-n2"></div>
                <div class="row text-center">
                    <div class="col-6">
                        <p class="text-muted mb-2">{{ __('This Week') }}</p>
                        <h3 class="text-dark mb-3">{{ __('23.5k') }}</h3>
                    </div>
                    <!-- end col -->
                    <div class="col-6">
                        <p class="text-muted mb-2">{{ __('Last Week') }}</p>
                        <h3 class="text-dark mb-3">{{ __('41.05k') }}</h3>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
                <div class="text-center">
                    <button type="button" class="btn btn-light shadow-none w-100">{{ __('View Details') }}</button>
                </div>
                <!-- end row -->
            </div>
        </div>
    </div>
    <!-- end left chart card -->

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ __('Sessions by Country') }}</h5>
                <div id="world-map-markers" style="height: 316px;"></div>
                <div class="row text-center">
                    <div class="col-6">
                        <p class="text-muted mb-2">{{ __('This Week') }}</p>
                        <h3 class="text-dark mb-3">{{ __('23.5k') }}</h3>
                    </div>
                    <!-- end col -->
                    <div class="col-6">
                        <p class="text-muted mb-2">{{ __('Last Week') }}</p>
                        <h3 class="text-dark mb-3">{{ __('41.05k') }}</h3>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
            </div>
        </div>
        <!-- end card-->
    </div>
    <!-- end col -->

    <div class="col-lg-4">
        <div class="card card-height-100">
            <div class="card-header d-flex align-items-center justify-content-between gap-2">
                <h4 class="card-title flex-grow-1">{{ __('Top Pages') }}</h4>

                <a href="#" class="btn btn-sm btn-soft-primary">{{ __('View All') }}</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-nowrap table-centered m-0">
                    <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th class="text-muted ps-3">{{ __('Page Path') }}</th>
                            <th class="text-muted">{{ __('Page Views') }}</th>
                            <th class="text-muted">{{ __('Exit Rate') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-3"><a href="#" class="text-muted">{{ __('larkon/ecommerce.html') }}</a></td>
                            <td>465</td>
                            <td><span class="badge badge-soft-success">4.4%</span></td>
                        </tr>
                        <tr>
                            <td class="ps-3"><a href="#" class="text-muted">{{ __('larkon/dashboard.html') }}</a></td>
                            <td>426</td>
                            <td><span class="badge badge-soft-danger">20.4%</span></td>
                        </tr>
                        <tr>
                            <td class="ps-3"><a href="#" class="text-muted">{{ __('larkon/chat.html') }}</a></td>
                            <td>254</td>
                            <td><span class="badge badge-soft-warning">12.25%</span></td>
                        </tr>
                        <tr>
                            <td class="ps-3"><a href="#" class="text-muted">{{ __('larkon/auth-login.html') }}</a></td>
                            <td>3369</td>
                            <td><span class="badge badge-soft-success">5.2%</span></td>
                        </tr>
                        <tr>
                            <td class="ps-3"><a href="#" class="text-muted">{{ __('larkon/email.html') }}</a></td>
                            <td>985</td>
                            <td><span class="badge badge-soft-danger">64.2%</span></td>
                        </tr>
                        <tr>
                            <td class="ps-3"><a href="#" class="text-muted">{{ __('larkon/social.html') }}</a></td>
                            <td>653</td>
                            <td><span class="badge badge-soft-success">2.4%</span></td>
                        </tr>
                        <tr>
                            <td class="ps-3"><a href="#" class="text-muted">{{ __('larkon/blog.html') }}</a></td>
                            <td>478</td>
                            <td><span class="badge badge-soft-danger">1.4%</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- end col -->

    <div class="col-xl-4 d-none">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">{{ __('Recent Transactions') }}</h4>
                <div>
                    <a href="#!" class="btn btn-sm btn-primary"> <i class="bx bx-plus me-1"></i>Add </a>
                </div>
            </div>
            <!-- end card-header-->
            <div class="card-body p-0">
                <div class="px-3" data-simplebar style="max-height: 398px;">
                    <table class="table table-hover mb-0 table-centered">
                        <tbody>
                            <tr>
                                <td>{{ __('24 April, 2024') }}</td>
                                <td>$120.55</td>
                                <td><span class="badge bg-success">{{ __('Cr') }}</span></td>
                                <td>{{ __('Commisions') }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('24 April, 2024') }}</td>
                                <td>$9.68</td>
                                <td><span class="badge bg-success">{{ __('Cr') }}</span></td>
                                <td>{{ __('Affiliates') }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('20 April, 2024') }}</td>
                                <td>$105.22</td>
                                <td><span class="badge bg-danger">{{ __('Dr') }}</span></td>
                                <td>{{ __('Grocery') }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('18 April, 2024') }}</td>
                                <td>$80.59</td>
                                <td><span class="badge bg-success">{{ __('Cr') }}</span></td>
                                <td>{{ __('Refunds') }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('18 April, 2024') }}</td>
                                <td>$750.95</td>
                                <td><span class="badge bg-danger">{{ __('Dr') }}</span></td>
                                <td>{{ __('Bill Payments') }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('17 April, 2024') }}</td>
                                <td>$455.62</td>
                                <td><span class="badge bg-danger">{{ __('Dr') }}</span></td>
                                <td>{{ __('Electricity') }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('17 April, 2024') }}</td>
                                <td>$102.77</td>
                                <td><span class="badge bg-success">{{ __('Cr') }}</span></td>
                                <td>{{ __('Interest') }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('16 April, 2024') }}</td>
                                <td>$79.49</td>
                                <td><span class="badge bg-success">{{ __('Cr') }}</span></td>
                                <td>{{ __('Refunds') }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('05 April, 2024') }}</td>
                                <td>$980.00</td>
                                <td><span class="badge bg-danger">{{ __('Dr') }}</span></td>
                                <td>{{ __('Shopping') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- end card body -->
        </div>
        <!-- end card-->
    </div>
    <!-- end col-->
</div>
<!-- end row -->

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Activity List') }}</h4>
                <div class="flex-shrink-0">
                    <a href="{{ route('activity.create') }}" class="btn btn-primary btn-sm"> + {{ __('Add Activity') }}</a>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="statusFilter" class="form-label">{{ __('Filter by Status') }}</label>
                    <select id="statusFilter" class="form-select" style="width:200px;">
                        <option value="">{{ __('All') }}</option>
                        <option value="1">{{ __('On time') }}</option>
                        <option value="2">{{ __('Delayed') }}</option>
                        <option value="3">{{ __('Completed') }}</option>
                        <option value="4">{{ __('More than one week') }}</option>
                        <option value="5">{{ __('Today') }}</option>
                        <option value="6">{{ __('This week') }}</option>
                    </select>
                </div>
                <div class="table-responsive table-centered">
                    <table id="activityData" class="display table table-bordered table-responsive text-nowrap mb-0" style="margin-top: 20px !important;">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Related Client') }}</th>
                                <th>{{ __('Activity Name') }}</th>
                                <th>{{ __('Activity Type') }}</th>
                                <th>{{ __('Creator') }}</th>
                                <th>{{ __('Responsible') }}</th>
                                <th>{{ __('Linked Project') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Created Date') }}</th>
                                <th>{{ __('Last Updated Date') }}</th>
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
<script src="{{ asset('admin/js/dashboard.js') }}"></script>
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
        let table = $('#activityData').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('dashboard.get-activities.data') }}",
                data: function (d) {
                    d.status = $('#statusFilter').val();
                }
            },
            language: window.codexDataTableLanguage(),
            columns: [
                { data: 'id', name: 'id' },
                { data: 'clientID', name: 'clientID' },
                { data: 'activity_name', name: 'activity_name' },
                { data: 'activity_type', name: 'activity_type' },
                { data: 'created_by', name: 'created_by' },
                { data: 'assign_by', name: 'assign_by' },
                { data: 'projectID', name: 'projectID' },
                { data: 'status', name: 'status' },
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
    });
</script>
@stop
