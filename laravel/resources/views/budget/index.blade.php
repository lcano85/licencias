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

    #budgetData_wrapper>.row:nth-of-type(3) {
        margin-top: 15px !important;
    }

    #budgetData th,
    #budgetData td {
        white-space: normal !important;
        word-break: break-word;
    }

    #registerInvoice_wrapper>.row:nth-of-type(3) {
        margin-top: 15px !important;
    }

    #registerInvoice th,
    #registerInvoice td {
        white-space: normal !important;
        word-break: break-word;
    }

    #incomeTable_wrapper>.row:nth-of-type(3) {
        margin-top: 15px !important;
    }

    #incomeTable th,
    #incomeTable td {
        white-space: normal !important;
        word-break: break-word;
    }

    #validationsTable_wrapper>.row:nth-of-type(3) {
        margin-top: 15px !important;
    }

    #validationsTable th,
    #validationsTable td {
        white-space: normal !important;
        word-break: break-word;
    }

    #validatedIncomesTable_wrapper>.row:nth-of-type(3) {
        margin-top: 15px !important;
    }

    #validatedIncomesTable th,
    #validatedIncomesTable td {
        white-space: normal !important;
        word-break: break-word;
    }

    #distributableIncomesTable_wrapper>.row:nth-of-type(2) {
        overflow-x: auto !important;
    }

    #distributableIncomesTable_wrapper>.row:nth-of-type(3) {
        margin-top: 15px !important;
    }

    #settlementsTable_wrapper>.row:nth-of-type(2) {
        overflow-x: auto !important;
    }

    #settlementsTable_wrapper>.row:nth-of-type(3) {
        margin-top: 15px !important;
    }

    .table-danger {
        background-color: #f8d7da !important;
    }

    .disabled {
        pointer-events: none;
        opacity: 0.5;
    }

    .totals-card {
        background: #f8f9fa;
        border-left: 4px solid #ff6c2f;
        padding: 15px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
        border-radius: 8px;
    }

    .totals-card.filtered {
        border-left-color: #28a745;
        background: #f0fff4;
    }

    .totals-card.bg-light {
        background: #e9ecef !important;
        border-left: 4px solid #495057;
    }

    .concept-section-table {
        margin-bottom: 0;
    }

    .section-total-row {
        background-color: rgba(0, 0, 0, 0.03);
        font-weight: 600;
    }

    #grandTotalsRow {
        background-color: rgba(255, 108, 47, 0.1);
        font-size: 1.1em;
    }

    .alert-deadline {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    .header-controls {
        background: #fff;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .nav-tabs .nav-link {
        color: #495057;
    }

    .nav-tabs .nav-link.active {
        color: #ff6c2f;
        font-weight: 600;
    }

    #billingInvoicesTable_wrapper>.row:nth-of-type(2) {
        overflow-x: auto !important;
    }

    #billingInvoicesTable_wrapper>.row:nth-of-type(3) {
        margin-top: 15px !important;
    }

    #creditNotesTable_wrapper>.row:nth-of-type(3) {
        margin-top: 15px !important;
    }

    #creditNotesTable th,
    #creditNotesTable td {
        white-space: normal !important;
        word-break: break-word;
    }

    .section-empty {
        opacity: 0.6;
        background: #f8f9fa !important;
    }

    .section-has-data {
        background: #fff;
    }

    /* Number formatting styles */
    .number-input,
    .currency-input {
        text-align: right;
        font-family: 'Courier New', monospace;
        font-weight: 500;
    }

    .number-input:focus,
    .currency-input:focus {
        border-color: #405189;
        box-shadow: 0 0 0 0.1rem rgba(64, 81, 137, 0.25);
    }

    .number-input.formatted::after {
        content: " ✓";
        color: #28a745;
        font-weight: bold;
    }

    .currency-symbol {
        position: relative;
    }

    .currency-symbol::before {
        content: "$";
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        font-weight: 500;
    }

    .currency-symbol input {
        padding-left: 25px;
    }
</style>
@stop

@section('content')
<div class="loader--ripple" style="display: none;">
    <div></div>
    <div></div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#budget" role="tab"><i class="ri-money-dollar-circle-line me-1"></i> {{ __('Budget') }}</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#invoice" role="tab"><i class="ri-bill-line me-1"></i> {{ __('Invoice') }}</a></li>
                    <?php /*
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#billing" role="tab"><i class="ri-bill-line me-1"></i> Billing</a></li> */ ?>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#income" role="tab"><i class="ri-line-chart-line me-1"></i> {{ __('Income') }}</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#validations" role="tab"><i class="ri-check-double-line me-1"></i> {{ __('Validations') }}</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#distributions" role="tab"><i class="ri-share-line me-1"></i> {{ __('Distributions') }}</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#portfolio" role="tab"><i class="ri-folder-chart-line me-1"></i> {{ __('Portfolio') }}</a></li>
                </ul>

                <div class="tab-content pt-3">

                    <!-- Budget Tab -->
                    <div class="tab-pane active" id="budget" role="tabpanel">
                        <div class="col-lg-12">
                            <div class="header-controls">
                                <div class="row align-items-end g-2">
                                    <div class="col-md-2">
                                        <label class="form-label mb-1">{{ __('Month') }}</label>
                                        <select class="form-select form-select-sm" id="monthFilter">
                                            <option value="">{{ __('All Months') }}</option>
                                            @for($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                </option>
                                                @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label mb-1">{{ __('Year') }}</label>
                                        <select class="form-select form-select-sm" id="yearFilter">
                                            <option value="">{{ __('All Years') }}</option>
                                            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                            <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <!-- NEW timeframe filters -->
                                    <div class="col-md-3">
                                        <label class="form-label mb-1">{{ __('Initial (Month)') }}</label>
                                        <input type="month" id="startDateFilter" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label mb-1">{{ __('Final (Month)') }}</label>
                                        <input type="month" id="endDateFilter" class="form-control form-control-sm">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label mb-1">{{ __('Concept') }}</label>
                                        <input type="text" id="conceptFilter" class="form-control form-control-sm" placeholder="{{ __('Search...') }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label mb-1">{{ __('Condition') }}</label>
                                        <select id="conditionFilter" class="form-select form-select-sm">
                                            <option value="">{{ __('Select condition...') }}</option>
                                            <option value="1">{{ __('Portfolio') }}</option>
                                            <option value="2">{{ __('New Agreement') }}</option>
                                            <option value="3">{{ __('Awaiting Purchase Order') }}</option>
                                            <option value="4">Acuerdos</option>
                                            <option value="5">{{ __('Others') }}</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-secondary btn-sm w-100" id="clearFilters">
                                            <i class="ri-refresh-line me-1"></i> Clear Filters
                                        </button>
                                    </div>

                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newRecordModal">
                                            <i class="ri-add-line"></i> + Create Budget
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Deadline Alert -->
                        <div id="deadlineAlert" style="display: none;" class="alert alert-danger alert-deadline" role="alert">
                            <i class="ri-alert-line me-2"></i> <strong id="alertMessage"></strong>
                        </div>

                        <!-- SEPARATED TOTALS BY CONCEPT SECTIONS -->
                        @foreach($totals['sections'] as $sectionKey => $section)
                        <div class="totals-card mb-3 section-has-data" id="section-{{ $sectionKey }}">
                            <h5 class="card-title mb-3">{{ $section['title'] }}</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless concept-section-table" data-section="{{ $sectionKey }}">
                                    <tbody>
                                        @foreach($section['concepts'] as $concept)
                                        <tr data-concept="{{ $concept['name'] }}">
                                            <td style="width: 35%;"><strong>{{ $concept['name'] }}</strong></td>
                                            <td class="subtotal">Subtotal: $ {{ number_format($concept['subTotal'], 2) }}</td>
                                            <td class="vat">VAT: $ {{ number_format($concept['vat'], 2) }}</td>
                                            <td class="total">Total: $ {{ number_format($concept['total'], 2) }}</td>
                                        </tr>
                                        @endforeach
                                        <tr class="border-top section-total-row">
                                            <td><strong>Section Total</strong></td>
                                            <td class="subtotal"><strong>Subtotal: $ {{ number_format($section['sectionTotal']['subTotal'], 2) }}</strong></td>
                                            <td class="vat"><strong>VAT: $ {{ number_format($section['sectionTotal']['vat'], 2) }}</strong></td>
                                            <td class="total"><strong>Total: $ {{ number_format($section['sectionTotal']['total'], 2) }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endforeach

                        <!-- GRAND TOTAL -->
                        <div class="totals-card mb-3 bg-light">
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                        <tr class="border-top" id="grandTotalsRow">
                                            <td style="width: 35%;"><strong>GRAND TOTAL BUDGETED</strong></td>
                                            <td class="subtotal"><strong>Subtotal: $ {{ number_format($totals['grandTotal']['subTotal'], 2) }}</strong></td>
                                            <td class="vat"><strong>VAT: $ {{ number_format($totals['grandTotal']['vat'], 2) }}</strong></td>
                                            <td class="total"><strong>Total: $ {{ number_format($totals['grandTotal']['total'], 2) }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="table-responsive table-centered">
                            <table id="budgetData" class="display table table-bordered table-responsive mb-0">
                                <thead>
                                    <tr>
                                        <th>User Type</th>
                                        <th>Company</th>
                                        <th>Commercial Name</th>
                                        <th>Subtotal</th>
                                        <th>VAT</th>
                                        <th>Month Total</th>
                                        <!-- <th>Annual Total</th> -->
                                        <th>Condition</th>
                                        <th>Status</th>
                                        <th>Created Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Invoice Tab -->
                    <div class="tab-pane" id="invoice" role="tabpanel">
                        <div class="row mb-3">
                            <!-- Enhanced Filter Section -->
                            <div class="filter-section">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select form-select-sm" id="invoiceStatusFilter">
                                            <option value="">All Status</option>
                                            <option value="D">Delay</option>
                                            <option value="On">On Time</option>
                                            <option value="P">Payed</option>
                                            <option value="B">Balance</option>
                                            <option value="C">Canceled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Commercial Name</label>
                                        <input type="text" class="form-control form-control-sm" id="invoiceCommercialFilter" placeholder="Search...">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Client Category</label>
                                        <select class="form-select form-select-sm" id="invoiceCategoryFilter">
                                            <option value="">All Categories</option>
                                            @foreach($clientCategories ?? [] as $category)
                                            <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Concept</label>
                                        <input type="text" class="form-control form-control-sm" id="invoiceConceptFilter" placeholder="Search concept...">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Criterion</label>
                                        <select class="form-select form-select-sm" id="invoiceCriterionFilter">
                                            <option value="">All Criteria</option>
                                            <option value="1">Min. Guaranteed, 8% Income</option>
                                            <option value="2">Min. Guaranteed + 8%</option>
                                            <option value="3">Monthly Fee</option>
                                            <option value="4">Annual Fee</option>
                                            <option value="5">Special Arrangement</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Date Range</label>
                                        <div class="input-group input-group-sm">
                                            <input type="date" class="form-control" id="invoiceDateStart" value="{{ date('Y-m-01') }}">
                                            <span class="input-group-text">to</span>
                                            <input type="date" class="form-control" id="invoiceDateEnd" value="{{ date('Y-m-t') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <button type="button" class="btn btn-primary btn-sm" id="applyInvoiceFilters">
                                            Apply Filters
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="resetInvoiceFilters">
                                            Reset
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm" onclick="downloadInvoiceReport()">
                                            Download Excel
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Totals Section -->
                            <div class="totals-card mb-3" style="margin-bottom: 25px;margin-top: 25px;">
                                <h5 class="card-title mb-3">Total Invoices by Concepts</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless" id="invoiceConceptTotalsTable">
                                        <tbody>
                                            <!-- AJAX Loaded -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="table-responsive table-centered">
                                <table id="registerInvoice" class="display table table-bordered table-responsive mb-0">
                                    <thead>
                                        <tr>
                                            <th>Client Category</th>
                                            <th>Sub Category</th>
                                            <th>Commercial Name</th>
                                            <th>Invoice Number</th>
                                            <th>Invoice Date</th>
                                            <th>Concept</th>
                                            <th>Period</th>
                                            <th>Criterion</th>
                                            <th>Subtotal</th>
                                            <th>VAT</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div class="mt-4">
                                <h5 class="mb-3">Credit Notes</h5>
                                <div class="table-responsive">
                                    <table id="creditNotesTable" class="display table table-bordered table-responsive mb-0" style="background-color: #fff3cd;">
                                        <thead>
                                            <tr>
                                                <th>User Type</th>
                                                <th>Company</th>
                                                <th>Commercial Name</th>
                                                <th>Concept</th>
                                                <th>CN No.</th>
                                                <th>CN Date</th>
                                                <th>Period</th>
                                                <th>Criterion</th>
                                                <th>Subtotal</th>
                                                <th>VAT</th>
                                                <th>Total</th>
                                                <th>Supporting Doc</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Billing Tab -->
                    <?php /*
                        <div class="tab-pane" id="billing" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <h6 class="text-white-50">Total Billing</h6>
                                            <h3 class="mb-0" id="totalBilling">$ 0.00</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body">
                                            <h6 class="text-white-50">Total Credit Notes</h6>
                                            <h3 class="mb-0" id="totalCreditNotes">$ 0.00</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body">
                                            <h6 class="text-white-50">Total Portfolio</h6>
                                            <h3 class="mb-0" id="totalPortfolio">$ 0.00</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <label class="form-label me-2 mb-0">Period:</label>
                                            <input type="date" id="billingPeriodStart" class="form-control form-control-sm d-inline-block" style="width: 150px;" value="{{ date('Y-m-01') }}">
                                            <span class="mx-2">–</span>
                                            <input type="date" id="billingPeriodEnd" class="form-control form-control-sm d-inline-block" style="width: 150px;" value="{{ date('Y-m-t') }}">
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-success btn-sm" onclick="openQuickRegister()">
                                                <i class="ri-add-line"></i> Register Inv.
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="downloadBillingExcel()">
                                                <i class="ri-download-2-line"></i> Download
                                            </button>
                                            <button type="button" class="btn btn-primary btn-sm" onclick="generateBillingReport()">
                                                <i class="ri-file-text-line"></i> Report
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="billingInvoicesTable" class="display table table-bordered table-hover text-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>User Type</th>
                                            <th>Company</th>
                                            <th>Commercial Name</th>
                                            <th>Concept</th>
                                            <th>Invoice No.</th>
                                            <th>Invoice Date</th>
                                            <th>Period</th>
                                            <th>Criterion</th>
                                            <th>Subtotal</th>
                                            <th>VAT</th>
                                            <th>Total</th>
                                            <th>Balance</th>
                                            <th>Supporting Doc</th>
                                            <th style="width: 120px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    */ ?>

                    <!-- Income Tab -->
                    <div class="tab-pane" id="income" role="tabpanel">
                        <!-- Period Filter -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label me-2 mb-0">Period:</label>
                                        <input
                                            type="date"
                                            id="incomePeriodStart"
                                            class="form-control form-control-sm d-inline-block"
                                            style="width: 150px"
                                            value="{{ date('Y-m-01') }}" />
                                        <span class="mx-2">–</span>
                                        <input
                                            type="date"
                                            id="incomePeriodEnd"
                                            class="form-control form-control-sm d-inline-block"
                                            style="width: 150px"
                                            value="{{ date('Y-m-t') }}" />
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-success btn-sm" onclick="openIncomeModal()">
                                            <i class="ri-add-line"></i> {{ __('Register Income') }}
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="downloadIncomeReport()">
                                            <i class="ri-download-2-line"></i> Download Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Totals Cards -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h6 class="text-white-50">Total Income</h6>
                                        <h3 class="mb-0" id="totalIncomeAmount">$ 0.00</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="text-white-50">Other Amounts</h6>
                                        <h3 class="mb-0" id="totalOtherAmounts">$ 0.00</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="text-white-50">Total Paid</h6>
                                        <h3 class="mb-0" id="totalPaidAmount">$ 0.00</h3>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h6 class="text-white-50">Conciliatory Items</h6>
                                        <h3 class="mb-0" id="conciliatoryItems">$ 0.00</h3>
                                    </div>
                                </div>
                            </div> -->
                        </div>

                        <!-- Concept Totals -->
                        <div class="totals-card mb-3">
                            <h5 class="card-title mb-3">Income by Concept</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless" id="incomeConceptTotalsTable">
                                    <tbody>
                                        <!-- Will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Income Records Table -->
                        <div class="table-responsive">
                            <p class="text-muted mb-2">
                                <i class="fas fa-info-circle"></i> * Data is filtered based on **Payment Date**.
                            </p>
                            <table id="incomeTable" class="display table table-bordered table-responsive mb-0">
                                <thead>
                                    <tr>
                                        <th>Payment Date</th>
                                        <th>Mode</th>
                                        <th>Bank</th>
                                        <th>Company</th>
                                        <th>Commercial Name</th>
                                        <th>Income Amount</th>
                                        <th>Other Amounts</th>
                                        <th>Total Paid</th>
                                        <th>No. Billing</th>
                                        <th>Inv. Date</th>
                                        <th>Concept</th>
                                        <th>Invoice Period</th>
                                        <th>Invoice Value</th>
                                        <th>Balance</th>
                                        <th>RC No.</th>
                                        <th>RC Date</th>
                                        <th style="width: 100px">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Income Modal -->
                    <!-- Income Modal - Replace the existing income modal in your view -->
                    <div class="modal fade" id="incomeModal" tabindex="-1" aria-labelledby="incomeModalTitle" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="incomeModalTitle">{{ __('Register Income') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <form id="incomeForm">
                                    @csrf
                                    <input type="hidden" id="incomeId" name="income_id" />
                                    <div class="modal-body">

                                        <!-- Basic Information -->
                                        <div class="card mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="ri-bank-line me-2"></i>{{ __('Basic Information') }}</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row mb-3">
                                                    <div class="col-md-3">
                                                        <label for="incomeMode" class="form-label">{{ __('Mode') }} *</label>
                                                        <select class="form-control" id="incomeMode" name="mode" required>
                                                            <option value="Transfer" selected>{{ __('Transfer') }}</option>
                                                            <option value="Deposit">{{ __('Deposit') }}</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <label for="incomeBank" class="form-label">{{ __('Bank') }} *</label>
                                                        <select class="form-control" id="incomeBank" name="bank_code" required>
                                                            <option value="NONE">{{ __('None Bank') }}</option>
                                                            @if(isset($banks) && count($banks) > 0)
                                                            @foreach($banks as $bank)
                                                            <option value="{{ $bank->id }}" {{ $bank->default ? 'selected' : '' }}>
                                                                {{ $bank->bank_code }} - {{ $bank->bank_name }}
                                                            </option>
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <label for="incomeDate" class="form-label">{{ __('Income Date') }} *</label>
                                                        <input
                                                            type="date"
                                                            class="form-control"
                                                            id="incomeDate"
                                                            name="income_date"
                                                            value="{{ date('Y-m-d') }}"
                                                            required />
                                                    </div>

                                                    <div class="col-md-3">
                                                        <label for="incomeAmount" class="form-label">{{ __('Income Amount') }} *</label>
                                                        <div class="currency-symbol">
                                                            <input
                                                                type="text"
                                                                class="form-control number-input"
                                                                id="incomeAmount"
                                                                name="income_amount"
                                                                data-thousand-separator
                                                                required />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Company & Invoice Selection -->
                                        <div class="card mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="ri-building-line me-2"></i>{{ __('Company & Invoice (Optional)') }}</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="alert alert-info">
                                                    <strong>{{ __('Note') }}:</strong> {{ __('Note: You can register income without selecting a company/invoice.') }}
                                                    {{ __('Select a company to see their pending invoices and apply this payment.') }}
                                                </div>

                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label for="incomeCompany" class="form-label">
                                                            <i class="ri-building-2-line me-1"></i>Company
                                                        </label>
                                                        <select class="form-control" id="incomeCompany" name="company_id">
                                                            <option value="">{{ __('Select Company...') }}</option>
                                                            @foreach($clients as $client)
                                                            <option
                                                                value="{{ $client->id }}"
                                                                data-company="{{ $client->legalName }}"
                                                                data-commercial="{{ $client->commercialName }}">
                                                                {{ $client->commercialName }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label for="incomeInvoice" class="form-label">
                                                            <i class="ri-file-list-line me-1"></i>{{ __('Pending Invoice') }}
                                                        </label>
                                                        <select class="form-control" id="incomeInvoice" name="invoice_ids[]" multiple disabled>
                                                            <option value="">{{ __('Select Invoice...') }}</option>
                                                        </select>
                                                        <small class="text-muted">
                                                            Select company first to load pending invoices
                                                        </small>
                                                        <small class="text-muted">Hold Ctrl/Cmd to select multiple invoices</small>
                                                        <div id="selectedInvoicesSection" style="display:none; margin-top:10px;">
                                                            <label>Selected Invoices</label>
                                                            <table class="table table-sm table-bordered" id="selectedInvoicesTable">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Invoice No.</th>
                                                                        <th>Balance</th>
                                                                        <th>Amount to Apply</th>
                                                                        <th>Remove</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="selectedInvoicesBody"></tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mb-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label">{{ __('Company Name') }}</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            id="incomeCompanyName"
                                                            name="company"
                                                            readonly />
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">{{ __('Commercial Name') }}</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            id="incomeCommercialName"
                                                            name="commercial_name"
                                                            readonly />
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">{{ __('Other Amounts') }}</label>
                                                        <div class="currency-symbol">
                                                            <input
                                                                type="text"
                                                                class="form-control number-input"
                                                                id="incomeOtherAmounts"
                                                                name="other_amounts"
                                                                value="0,00"
                                                                data-thousand-separator />
                                                        </div>
                                                        <small class="text-muted">{{ __('Use for legal deductions or adjustments (can be negative)') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Invoice Details (Auto-filled) -->
                                        <div class="card mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="ri-file-text-line me-2"></i>{{ __('Invoice Details (Auto-filled)') }}</h6>
                                            </div>
                                            <div class="card-body" id="invoice-details-container">
                                                <div class="text-muted text-center py-3">{{ __('Select invoices to see details') }}</div>
                                            </div>
                                        </div>

                                        <!-- Cash Receipt Info -->
                                        <div class="card mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="ri-receipt-line me-2"></i>{{ __('Cash Receipt (RC)') }}</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="alert alert-warning">
                                                    <i class="ri-alert-line me-2"></i>
                                                    <strong>{{ __('Important:') }}</strong> {{ __('Invoice is not cross-matched until RC Number is registered.') }}
                                                </div>

                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">{{ __('RC Consecutive') }}</label>
                                                        <select class="form-select" id="incomeReceiptConsecutive" name="receipt_consecutive_id">
                                                            <option value="">{{ __('Select receipt consecutive (optional)') }}</option>
                                                            @if(isset($receiptConsecutives) && count($receiptConsecutives) > 0)
                                                            @foreach($receiptConsecutives as $rc)
                                                            <option value="{{ $rc->id }}">{{ $rc->consecutive_name }} (next: {{ $rc->next_number }})</option>
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                        <small class="text-muted">{{ __('If RC No is empty, the system will auto-generate using this consecutive.') }}</small>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label">{{ __('RC No. (Cash Receipt Number)') }}</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            id="incomeRCNumber"
                                                            name="rc_number"
                                                            placeholder="{{ __('Leave blank to auto-generate (if RC Consecutive is selected)') }}" />
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label">{{ __('RC Date') }}</label>
                                                        <input
                                                            type="date"
                                                            class="form-control"
                                                            id="incomeRCDate"
                                                            name="rc_date"
                                                            value="{{ date('Y-m-t') }}" />
                                                        <small class="text-muted">{{ __('Default: Last day of current month') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Summary -->
                                        <div class="card border-primary">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0"><i class="ri-calculator-line me-2"></i>{{ __('Payment Summary') }}</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="alert alert-info mb-0">
                                                    <div class="row g-2">
                                                        <div class="col-md-4">
                                                            <h6 class="mb-0">
                                                                <strong>{{ __('Income Amount') }}:</strong>
                                                                <span id="summaryInvoiceTotalDisplay" class="text-dark">$ 0.00</span>
                                                            </h6>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <h6 class="mb-0">
                                                                <strong>{{ __('Balance (RC)') }}:</strong>
                                                                <span id="summaryRemainingBalanceDisplay" class="text-warning">$ 0.00</span>
                                                            </h6>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <h6 class="mb-0">
                                                                <strong>{{ __('Current Payment') }}:</strong>
                                                                <span id="totalPaidDisplay" class="text-primary">$ 0.00</span>
                                                            </h6>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="ri-close-line me-1"></i> {{ __('Close') }}
                                        </button>
                                        <button type="button" class="btn btn-success" id="saveAndAddNewIncome">
                                            <i class="ri-add-line me-1"></i> {{ __('Save & Add New') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Validations Tab -->
                    <div class="tab-pane" id="validations" role="tabpanel">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="row align-items-center g-2">
                                        <div class="col-md-2">
                                            <label class="form-label mb-1">Report Type</label>
                                            <select class="form-select form-select-sm" id="validationReportTypeFilter">
                                                <option value="">All Types</option>
                                                <option value="billing">Billing</option>
                                                <option value="income">Income</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label mb-1">Status</label>
                                            <select class="form-select form-select-sm" id="validationStatusFilter">
                                                <option value="">All Status</option>
                                                <option value="pending_accountant">Pending Accountant</option>
                                                <option value="pending_management">Pending Management</option>
                                                <option value="approved">Approved</option>
                                                <option value="rejected">Rejected</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label mb-1">Period Start</label>
                                            <input type="date" class="form-control form-control-sm" id="validationPeriodStartFilter">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label mb-1">Period End</label>
                                            <input type="date" class="form-control form-control-sm" id="validationPeriodEndFilter">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary btn-sm w-100 mt-4" id="clearValidationFilters">
                                                <i class="ri-refresh-line me-1"></i> Clear
                                            </button>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-primary btn-sm w-100 mt-4" data-bs-toggle="modal" data-bs-target="#createValidationModal">
                                                <i class="ri-add-line me-1"></i> Create Validation
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="validationsTable" class="display table table-bordered table-responsive mb-0" style="width:100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Period</th>
                                                    <th>Title</th>
                                                    <th>Total</th>
                                                    <th>Status</th>
                                                    <th>Accountant</th>
                                                    <th>Management</th>
                                                    <th>Created By</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Create Validation Modal -->
                    <div class="modal fade" id="createValidationModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Create Validation Report</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="createValidationForm">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Report Type <span class="text-danger">*</span></label>
                                                <select class="form-control" name="report_type" id="validationReportType" required>
                                                    <option value="">Select Type</option>
                                                    <option value="billing">Billing</option>
                                                    <option value="income">Income</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Title (Optional)</label>
                                                <input type="text" class="form-control" name="title" placeholder="e.g., Q1 2025 Validation">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Period Start <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="period_start" id="validationPeriodStart" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Period End <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="period_end" id="validationPeriodEnd" required>
                                            </div>
                                        </div>
                                        <div class="alert alert-info d-flex align-items-center">
                                            <i class="ri-information-line me-2 fs-20"></i>
                                            <div>
                                                <strong>Validation Process:</strong><br>
                                                1. Accountant validates first<br>
                                                2. If approved, Management validates<br>
                                                3. Both must approve for final approval
                                            </div>
                                        </div>
                                        <div id="validationPreview" class="mt-3" style="display: none;">
                                            <h6 class="mb-2">Preview</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Concept</th>
                                                            <th class="text-end">Count</th>
                                                            <th class="text-end">Total Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="previewData"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-info" id="previewValidationBtn">
                                            <i class="ri-eye-line me-1"></i> Preview
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-check-line me-1"></i> Create Report
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Validation Modal -->
                    <div class="modal fade" id="editValidationModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Validation Report</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="editValidationForm">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" id="editValidationId" name="validation_id">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Title</label>
                                            <input type="text" class="form-control" id="editValidationTitle" name="title">
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Period Start <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="editValidationPeriodStart" name="period_start" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Period End <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="editValidationPeriodEnd" name="period_end" required>
                                            </div>
                                        </div>
                                        <div class="alert alert-warning">
                                            <i class="ri-alert-line me-2"></i>
                                            You can only edit reports pending accountant validation.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line me-1"></i> Update
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- View/Validate Modal -->
                    <div class="modal fade" id="viewValidationModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="viewValidationModalLabel">Validation Report</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body" id="validationModalBody">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Distributions Tab -->
                    <div class="tab-pane" id="distributions" role="tabpanel">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="row align-items-center g-2">
                                        <div class="col-md-3">
                                            <label class="form-label mb-1">View</label>
                                            <select class="form-select form-select-sm" id="distributionViewFilter">
                                                <option value="validated">Validated Incomes</option>
                                                <option value="distributable">Distributable Incomes</option>
                                                <option value="settled">Settlements</option>
                                                <option value="paid">Paid Settlements</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label mb-1">Concept</label>
                                            <input type="text" class="form-control form-control-sm" id="distributionConceptFilter" placeholder="Filter by concept...">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label mb-1">Period Start</label>
                                            <input type="date" class="form-control form-control-sm" id="distributionPeriodStart">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label mb-1">Period End</label>
                                            <input type="date" class="form-control form-control-sm" id="distributionPeriodEnd">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary btn-sm w-100 mt-4" id="clearDistributionFilters">
                                                <i class="ri-refresh-line me-1"></i> Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Validated Incomes Section (View 1) -->
                                <div class="card-body" id="validatedIncomesSection">
                                    <h5 class="card-title mb-3">Validated Incomes</h5>
                                    <div class="alert alert-info">
                                        <i class="ri-information-line me-2"></i>
                                        Select invoices to distribute by concept, then click "Distribute" to move them to Distributable Incomes.
                                    </div>

                                    <!-- Concept Totals -->
                                    <div class="totals-card mb-3">
                                        <h6 class="mb-2">Concept Totals</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-borderless" id="conceptTotalsTable">
                                                <tbody>
                                                    <!-- AJAX loaded -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAllValidated">
                                            <label class="form-check-label" for="selectAllValidated">
                                                Select all invoices
                                            </label>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm mt-2" id="distributeSelected">
                                            <i class="ri-share-forward-line me-1"></i> Distribute Selected
                                        </button>
                                    </div>

                                    <!-- Validated Incomes Table -->
                                    <div class="table-responsive">
                                        <table id="validatedIncomesTable" class="display table table-bordered table-responsive mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="50">
                                                        <input type="checkbox" id="selectAllHeader">
                                                    </th>
                                                    <th>Company</th>
                                                    <th>Commercial Name</th>
                                                    <th>Concept</th>
                                                    <th>Invoice No.</th>
                                                    <th>Invoice Date</th>
                                                    <th>RC No.</th>
                                                    <th>RC Date</th>
                                                    <th>Base Value</th>
                                                    <th>VAT</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Distributable Incomes Section (View 2) -->
                                <div class="card-body" id="distributableIncomesSection" style="display: none;">
                                    <h5 class="card-title mb-3">Distributable Incomes</h5>
                                    <div class="alert alert-warning">
                                        <i class="ri-alert-line me-2"></i>
                                        List of selected items ready to liquidate. Click the settle icon to create a settlement.
                                    </div>

                                    <!-- Distributable Incomes Table -->
                                    <div class="table-responsive">
                                        <table id="distributableIncomesTable" class="table table-bordered table-hover nowrap">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>DIST No.</th>
                                                    <th>Origin</th>
                                                    <th>Concept</th>
                                                    <th>Dist. Date</th>
                                                    <th>Inv. No.</th>
                                                    <th>RC No.</th>
                                                    <th>Base Value</th>
                                                    <th>VAT</th>
                                                    <th>Associate Subtotal</th>
                                                    <th>Admin Subtotal</th>
                                                    <th>Admin VAT</th>
                                                    <th>Admin Total</th>
                                                    <th>Total to Pay</th>
                                                    <th>Status</th>
                                                    <th width="100">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Settlements Section (View 3) -->
                                <!-- Settlements Section (View 3) -->
                                <div class="card-body" id="settlementsSection" style="display: none;">
                                    <h5 class="card-title mb-3" id="settlementsTitle">Settlements</h5>
                                    <div class="alert alert-success" id="settlementsAlert">
                                        <i class="ri-check-double-line me-2"></i>
                                        Finalized liquidation records. View details or mark as paid.
                                    </div>

                                    <!-- Settlement Filters -->
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Payment Status</label>
                                            <select class="form-select form-select-sm" id="settlementPaymentFilter">
                                                <option value="">All</option>
                                                <option value="pending">Not Paid</option>
                                                <option value="paid">Paid</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Associate Filter</label>
                                            <select class="form-select form-select-sm" id="settlementAssociateFilter">
                                                <option value="">All Associates</option>
                                                <!-- AJAX loaded -->
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Download Report</label>
                                            <button type="button" class="btn btn-success btn-sm w-100" onclick="downloadSettlementsReport()">
                                                <i class="ri-download-line me-1"></i> Export
                                            </button>
                                        </div>
                                        <!-- <div class="col-md-3">
                                            <label class="form-label">Current View</label>
                                            <div class="form-control form-control-sm bg-light">
                                                <span class="badge bg-info" id="currentViewBadge">Settlements</span>
                                                <span class="ms-2" id="recordCount">0 records</span>
                                            </div>
                                        </div> -->
                                    </div>

                                    <!-- Settlements Table -->
                                    <div class="table-responsive">
                                        <table id="settlementsTable" class="table table-bordered table-hover nowrap">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>SETT No.</th>
                                                    <th>Origin</th>
                                                    <th>Concept</th>
                                                    <th>Income Month</th>
                                                    <th>Period</th>
                                                    <th>Distribution Formula</th>
                                                    <th>Total to Distribute</th>
                                                    <th>Amount to Pay</th>
                                                    <th>Distribution Type</th>
                                                    <th>Status</th>
                                                    <th width="150">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settlement Modal -->
                    <div class="modal fade" id="settlementModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="settlementModalTitle">Create Settlement</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="settlementForm">
                                    @csrf
                                    <input type="hidden" id="distributionId" name="distribution_id">
                                    <div class="modal-body">
                                        <!-- Basic Information -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Origin *</label>
                                                <input type="text" class="form-control" id="settlementOrigin" name="origin" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Concept</label>
                                                <input type="text" class="form-control" id="settlementConcept" readonly>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Income Month Start *</label>
                                                <input type="date" class="form-control" id="incomeMonthStart" name="income_month_start" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Income Month End</label>
                                                <input type="date" class="form-control" id="incomeMonthEnd" name="income_month_end">
                                                <small class="text-muted">Leave empty for single month</small>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Period Covered</label>
                                                <input type="text" class="form-control" id="periodCovered" name="period_covered" placeholder="e.g., Q1 2025">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Distribution Formula</label>
                                                <input type="text" class="form-control" id="distributionFormula" name="distribution_formula" placeholder="e.g., 80% Associates, 20% Admin">
                                            </div>
                                        </div>

                                        <!-- Distribution Type -->
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label class="form-label">Distribution Type *</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="distribution_type" id="ownershipType" value="ownership" checked>
                                                    <label class="form-check-label" for="ownershipType">
                                                        Ownership Identifications
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="distribution_type" id="manualType" value="manual">
                                                    <label class="form-check-label" for="manualType">
                                                        Manual Distribution
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Manual Distribution Section -->
                                        <div id="manualDistributionSection" style="display: none;">
                                            <div class="alert alert-info">
                                                <i class="ri-information-line me-2"></i>
                                                Add associates and assign percentages or fixed values. Total percentage must equal 100%.
                                            </div>

                                            <div id="associatesContainer">
                                                <!-- Associates will be added here dynamically -->
                                            </div>

                                            <div class="mb-3">
                                                <button type="button" class="btn btn-sm btn-primary" onclick="addAssociateRow()">
                                                    <i class="ri-add-line me-1"></i> Add Associate
                                                </button>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label class="form-label">Total Percentage</label>
                                                    <input type="text" class="form-control" id="totalPercentage" readonly value="0%">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Total Fixed</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">$</span>
                                                        <input type="text" class="form-control" id="totalFixed" readonly value="0.00">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Total to Distribute</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">$</span>
                                                        <input type="text" class="form-control" id="totalToDistribute" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Amount Summary -->
                                        <div class="alert alert-primary mt-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong>Amount to Distribute:</strong>
                                                    <span id="amountToDistributeDisplay" class="text-primary">$ 0.00</span>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Amount to Pay:</strong>
                                                    <span id="amountToPayDisplay" class="text-success">$ 0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-check-line me-1"></i> Create Settlement
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Settlement Details Modal -->
                    <div class="modal fade" id="settlementDetailsModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Settlement Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body" id="settlementDetailsBody">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mark as Paid Modal -->
                    <div class="modal fade" id="markPaidModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Mark Settlement as Paid</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="markPaidForm">
                                    <input type="hidden" id="settlementIdToMarkPaid">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Payment Date *</label>
                                            <input type="date" class="form-control" id="paidDate" name="paid_date" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                        <div class="alert alert-info">
                                            <i class="ri-information-line me-2"></i>
                                            This will mark the settlement and all associated associate payments as paid.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success">Mark as Paid</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Portfolio Tab -->
                    <div class="tab-pane" id="portfolio" role="tabpanel">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="row align-items-center g-2">
                                        <div class="col-md-2">
                                            <label class="form-label mb-1">Period Month</label>
                                            <select class="form-select form-select-sm" id="portfolioPeriodMonth">
                                                @for($m = 1; $m <= 12; $m++)
                                                    <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                    </option>
                                                    @endfor
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label mb-1">Period Year</label>
                                            <select class="form-select form-select-sm" id="portfolioPeriodYear">
                                                @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                                <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label mb-1">Client Filter</label>
                                            <input type="text" class="form-control form-control-sm" id="portfolioClientFilter" placeholder="Search client...">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label mb-1">Aging Filter</label>
                                            <select class="form-select form-select-sm" id="portfolioAgingFilter">
                                                <option value="">All Aging</option>
                                                <option value="1-30">1-30 days</option>
                                                <option value="31-90">31-90 days</option>
                                                <option value="90+">90+ days</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <button type="button" class="btn btn-sm btn-primary mt-4" onclick="loadPortfolioData()">
                                                <i class="ri-refresh-line me-1"></i> Refresh
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success mt-4" onclick="exportPortfolio()">
                                                <i class="ri-download-line me-1"></i> Export
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <!-- Summary Cards -->
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <div class="card bg-info text-white mb-0">
                                                <div class="card-body p-3">
                                                    <h6 class="text-white-50 mb-1">1-30 Days</h6>
                                                    <h4 class="mb-0" id="total_1_30">$0.00</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-warning text-white mb-0">
                                                <div class="card-body p-3">
                                                    <h6 class="text-white-50 mb-1">31-90 Days</h6>
                                                    <h4 class="mb-0" id="total_31_90">$0.00</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-danger text-white mb-0">
                                                <div class="card-body p-3">
                                                    <h6 class="text-white-50 mb-1">90+ Days</h6>
                                                    <h4 class="mb-0" id="total_90_plus">$0.00</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-primary text-white mb-0">
                                                <div class="card-body p-3">
                                                    <h6 class="text-white-50 mb-1">Total A/R</h6>
                                                    <h4 class="mb-0" id="total_ar">$0.00</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Portfolio Data Container -->
                                    <div id="portfolioDataContainer">
                                        <div class="text-center py-5">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="mt-2">Loading portfolio data...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comment Modal -->
                    <div class="modal fade" id="commentModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="commentModalTitle">Add Comment</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="commentForm">
                                    <input type="hidden" id="commentClientId" name="client_id">
                                    <input type="hidden" id="commentInvoiceId" name="invoice_id">
                                    <input type="hidden" id="commentPeriodMonth" name="period_month">
                                    <input type="hidden" id="commentPeriodYear" name="period_year">

                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Client</label>
                                            <input type="text" class="form-control" id="commentClientName" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Invoice</label>
                                            <input type="text" class="form-control" id="commentInvoiceNumber" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Comment *</label>
                                            <textarea class="form-control" id="commentText" name="comment" rows="4" required maxlength="1000"></textarea>
                                            <small class="text-muted">Maximum 1000 characters</small>
                                        </div>
                                        <div class="alert alert-info mb-0">
                                            <strong>Note:</strong> Comments require approval from Admin/Manager. Once approved, they cannot be modified.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line me-1"></i> Save Comment
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Approve Comment Modal -->
                    <div class="modal fade" id="approveCommentModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Review Comment</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="approveCommentForm">
                                    <input type="hidden" id="approveCommentId">

                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Comment</label>
                                            <div class="p-3 bg-light border rounded" id="approveCommentText"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Created By</label>
                                            <input type="text" class="form-control" id="approveCommentCreator" readonly>
                                        </div>
                                        <div class="mb-3" id="rejectionReasonGroup" style="display: none;">
                                            <label class="form-label">Rejection Reason</label>
                                            <textarea class="form-control" id="rejectionReason" name="rejection_reason" rows="3" maxlength="500"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-danger" onclick="submitCommentApproval('reject')">
                                            <i class="ri-close-line me-1"></i> Reject
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="submitCommentApproval('approve')">
                                            <i class="ri-check-line me-1"></i> Approve
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Register Invoice Modal -->
<div class="modal fade" id="exampleModalScrollable" tabindex="-1" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalScrollableTitle">{{ __('Register Invoice') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="invoiceForm" style="overflow-y: auto;">
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Generate Invoice') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- New Record Modal -->
<div class="modal fade" id="newRecordModal" tabindex="-1" aria-labelledby="newRecordModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newRecordModalTitle">New Record - Other Expected Income</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="newRecordForm" method="POST" action="{{ route('budget.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="newCommercialName" class="form-label">Commercial Name *</label>
                            <select class="form-control" id="newCommercialName" name="commercialName" data-choices data-choices-sorting-false placeholder="Select Commercial Name..." required>
                                <option value="">Select Commercial Name...</option>
                                @foreach($clients ?? [] as $client)
                                <option value="{{ $client->id }}">{{ $client->commercialName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="newUserType" class="form-label">Licensed Concept (Auto-filled) *</label>
                            <input type="text" class="form-control" name="user_type" id="newUserType" readonly required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="licensedEnvironment" class="form-label">Licensed Environment</label>
                            <select class="form-control" id="licensedEnvironment" name="licensedEnvironment" data-choices data-choices-sorting-false placeholder="Select Licensed Environment..." required>
                                @foreach($environments as $name)
                                <option value="{{ $name }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('licensedEnvironment') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="category" class="form-label">Category</label>
                            <input type="text" class="form-control" name="category" id="category" placeholder="Enter category" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="subcategory" class="form-label">Sub Category</label>
                            <input type="text" class="form-control" name="subcategory" id="subcategory" placeholder="Enter sub category" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="newCompany" class="form-label">Company *</label>
                            <input type="text" name="company" id="newCompany" class="form-control" required readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="newCondition" class="form-label">Condition *</label>
                            <select class="form-control" id="newCondition" name="condition" required>
                                <option value="">Select condition...</option>
                                <option value="1">Portfolio</option>
                                <option value="2">New Agreement</option>
                                <option value="3">Awaiting Purchase Order</option>
                                <option value="4">Acuerdos</option>
                                <option value="5">Others</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="newStatus" class="form-label">Status *</label>
                            <select class="form-control" name="status" id="newStatus" required>
                                <option value="">Select status...</option>
                                <option value="1" selected>Pending</option>
                                <option value="2">Invoiced</option>
                                <option value="3">Discarded</option>
                            </select>
                        </div>
                    </div>

                    <!-- Frequency / Begin / Finish / Annual Value -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Frequency</label>
                            <select class="form-control" name="frequency" id="newFrequency">
                                <option value="1" selected>Monthly</option>
                                <option value="2">Quarterly</option>
                                <option value="3">Annual</option>
                                <option value="4">One-Time Payment</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Begin (Month/Year)</label>
                            <input type="month" class="form-control" id="newBegin" />
                            <input type="hidden" name="begin_month" id="begin_month">
                            <input type="hidden" name="begin_year" id="begin_year">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Finish (Month/Year)</label>
                            <input type="month" class="form-control" id="newFinish" />
                            <input type="hidden" name="finish_month" id="finish_month">
                            <input type="hidden" name="finish_year" id="finish_year">
                            <small class="text-muted">Auto 12 months for Monthly; you can override.</small>
                        </div>
                    </div>

                    <!-- Monthly amount + Annual Value with number formatting -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Annual Value</label>
                            <div class="currency-symbol">
                                <input type="text" class="form-control number-input" name="annual_value" id="newAnnualValue" placeholder="1.000,00" data-thousand-separator>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Months</label>
                            <input type="number" class="form-control" id="newMonthsTotal" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Monthly Amount (Auto)</label>
                            <div class="currency-symbol">
                                <input type="text" class="form-control number-input" id="newMonthlyAmount" readonly data-thousand-separator>
                            </div>
                        </div>
                    </div>

                    <!-- Financial values with number formatting -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="newSubTotal" class="form-label">Subtotal *</label>
                            <div class="currency-symbol">
                                <input type="text" name="subTotal" id="newSubTotal" class="form-control number-input" required data-thousand-separator>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="newVat" class="form-label">VAT % *</label>
                            <input type="number" step="0.01" name="vat" id="newVat" class="form-control" value="12" required>
                        </div>
                        <div class="col-md-4">
                            <label for="newTotal" class="form-label">Total *</label>
                            <div class="currency-symbol">
                                <input type="text" name="total" id="newTotal" class="form-control number-input" readonly required data-thousand-separator>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="newBudgetMonth" class="form-label">Budget Month</label>
                            <select class="form-control" name="budget_month" id="newBudgetMonth">
                                <option value="">Select Month...</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                    @endfor
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="newBudgetYear" class="form-label">Budget Year</label>
                            <select class="form-control" name="budget_year" id="newBudgetYear">
                                <option value="">Select Year...</option>
                                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Record</button>
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

<script>
    window.translations = window.codexTranslations || {};
</script>

<script type="text/javascript">
    // Enhanced number formatting with thousand separators (dots)
    function formatNumberWithDots(value) {
        if (!value) return '';
        const isNegative = /^\s*-/.test(value);

        // Remove all non-digit characters except dots and commas
        let cleaned = value.replace(/[^\d,.]/g, '');

        // Split by comma to handle decimal part
        let parts = cleaned.split(',');
        let integerPart = parts[0];
        let decimalPart = parts[1] || '';

        // Remove existing dots from integer part
        integerPart = integerPart.replace(/\./g, '');

        // Add thousand separators (dots) to integer part
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        // Limit decimal to 2 digits
        if (decimalPart.length > 2) {
            decimalPart = decimalPart.substring(0, 2);
        }

        // Combine parts
        const formatted = decimalPart ? integerPart + ',' + decimalPart : integerPart;
        if (isNegative && formatted) return '-' + formatted;
        return isNegative ? '-' : formatted;
    }

    function unformatNumber(value) {
        if (!value) return '';
        const isNegative = /^\s*-/.test(value);
        // Remove dots (thousand separators) and replace comma with dot for decimal
        const cleaned = value.replace(/[^\d.,]/g, '').replace(/\./g, '').replace(',', '.');
        return isNegative ? '-' + cleaned : cleaned;
    }

    function initializeNumberFormatting() {
        const numberInputs = document.querySelectorAll('.number-input, .currency-input, [data-thousand-separator]');

        numberInputs.forEach(input => {
            // Format as user types
            input.addEventListener('input', function(e) {
                let cursorPosition = this.selectionStart;
                let oldLength = this.value.length;
                let oldValue = this.value;

                // Format the value
                let formattedValue = formatNumberWithDots(this.value);
                this.value = formattedValue;

                // Adjust cursor position after formatting
                let newLength = this.value.length;
                let lengthDiff = newLength - oldLength;

                // Keep cursor in correct position
                if (lengthDiff > 0) {
                    cursorPosition += lengthDiff;
                }
                this.setSelectionRange(cursorPosition, cursorPosition);
            });

            // Add focus handler
            input.addEventListener('focus', function() {
                if (!this.value) {
                    this.placeholder = '0,00';
                }
            });

            // Final formatting on blur
            input.addEventListener('blur', function() {
                if (this.value) {
                    let unformatted = unformatNumber(this.value);
                    let numValue = parseFloat(unformatted);

                    if (!isNaN(numValue)) {
                        // Format with 2 decimal places
                        let formatted = numValue.toFixed(2).replace('.', ',');
                        // Add thousand separators
                        let parts = formatted.split(',');
                        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                        this.value = parts.join(',');
                    }
                }
            });

            // Allow only numbers, comma, and specific keys
            input.addEventListener('keydown', function(e) {
                const key = e.key || '';
                const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End'];
                const isNumber = /^\d$/.test(key);
                const isComma = key === ',';
                const isDot = key === '.';
                const isMinus = key === '-' || key === 'Subtract';
                const hasComma = (this.value || '').includes(',');
                const hasMinus = (this.value || '').includes('-');
                const canPlaceMinus = this.selectionStart === 0;

                // Allow: numbers, comma (only one), navigation keys, ctrl+a, ctrl+c, ctrl+v, ctrl+x
                if (!isNumber &&
                    !(isComma && !hasComma) &&
                    !isDot && // Allow dot for compatibility
                    !(isMinus && !hasMinus && canPlaceMinus) &&
                    !allowedKeys.includes(key) &&
                    !(e.ctrlKey && ['a', 'c', 'v', 'x'].includes(key.toLowerCase()))) {
                    e.preventDefault();
                }
            });
        });
    }

    // Enhanced budget calculations with formatted numbers
    $(document).ready(function() {
        // Initialize number formatting
        initializeNumberFormatting();

        function ymToParts(val) {
            if (!val) return null;
            const [y, m] = val.split('-').map(v => parseInt(v, 10));
            return {
                y,
                m
            };
        }

        function partsToYm(y, m) {
            return y.toString().padStart(4, '0') + '-' + m.toString().padStart(2, '0');
        }

        function addMonths(y, m, add) {
            const d = new Date(y, m - 1, 1);
            d.setMonth(d.getMonth() + add);
            return {
                y: d.getFullYear(),
                m: (d.getMonth() + 1)
            };
        }

        function monthsDiffInclusive(bY, bM, fY, fM) {
            const a = new Date(bY, bM - 1, 1);
            const b = new Date(fY, fM - 1, 1);
            let months = (b.getFullYear() - a.getFullYear()) * 12 + (b.getMonth() - a.getMonth()) + 1;
            if (months < 1) months = 1;
            return months;
        }

        // Parse formatted number to float
        function parseFormattedNumber(value) {
            if (!value) return 0;
            const unformatted = unformatNumber(value);
            return parseFloat(unformatted) || 0;
        }

        // Format number for display
        function formatNumberForDisplay(value) {
            if (!value && value !== 0) return '';
            const num = typeof value === 'string' ? parseFormattedNumber(value) : value;
            return formatNumberWithDots(num.toFixed(2).replace('.', ','));
        }

        const now = new Date();
        const beginDefault = partsToYm(now.getFullYear(), now.getMonth() + 1);
        $('#newBegin').val(beginDefault);
        $('#begin_year').val(now.getFullYear());
        $('#begin_month').val(now.getMonth() + 1);

        function setFinishFromBeginIfMonthly() {
            const freq = $('#newFrequency').val();
            const b = ymToParts($('#newBegin').val());
            if (!b) return;
            if (freq === '1') {
                const f = addMonths(b.y, b.m, 11); // inclusive 12 months
                $('#newFinish').val(partsToYm(f.y, f.m));
                $('#finish_year').val(f.y);
                $('#finish_month').val(f.m);
            }
        }

        function syncHiddenYM(which) {
            const $input = (which === 'begin') ? $('#newBegin') : $('#newFinish');
            const p = ymToParts($input.val());
            if (!p) return;
            $('#' + which + '_year').val(p.y);
            $('#' + which + '_month').val(p.m);
        }

        function recomputeMonthsAndAmounts() {
            const b = ymToParts($('#newBegin').val());
            const f = ymToParts($('#newFinish').val());
            const annual = parseFormattedNumber($('#newAnnualValue').val());
            const vat = parseFloat($('#newVat').val() || '0');

            if (b && f) {
                const months = monthsDiffInclusive(b.y, b.m, f.y, f.m);
                $('#newMonthsTotal').val(months);

                if (annual > 0 && months > 0) {
                    const monthly = (annual / months);
                    $('#newMonthlyAmount').val(formatNumberForDisplay(monthly));

                    $('#newSubTotal').val(formatNumberForDisplay(monthly));
                    const total = monthly + (monthly * vat / 100.0);
                    $('#newTotal').val(formatNumberForDisplay(total));
                }
            }
        }

        // Calculate total from subtotal and VAT
        function calculateTotalFromSubtotalAndVat() {
            const subTotal = parseFormattedNumber($('#newSubTotal').val());
            const vat = parseFloat($('#newVat').val() || '0');
            const total = subTotal + (subTotal * vat / 100);
            $('#newTotal').val(formatNumberForDisplay(total));
        }

        // Event handlers
        $('#newFrequency').on('change', function() {
            setFinishFromBeginIfMonthly();
            recomputeMonthsAndAmounts();
        });

        $('#newBegin').on('change', function() {
            syncHiddenYM('begin');
            setFinishFromBeginIfMonthly();
            recomputeMonthsAndAmounts();
            const b = ymToParts($('#newBegin').val());
            if (b) {
                $('#newBudgetMonth').val(b.m);
                $('#newBudgetYear').val(b.y);
            }
        });

        $('#newFinish').on('change', function() {
            syncHiddenYM('finish');
            recomputeMonthsAndAmounts();
        });

        $('#newAnnualValue, #newVat').on('input', function() {
            recomputeMonthsAndAmounts();
        });

        $('#newSubTotal, #newVat').on('input', function() {
            calculateTotalFromSubtotalAndVat();
        });

        // Commercial Name change handler
        $('#newCommercialName').on('change', function() {
            const clientId = $(this).val();
            if (clientId) {
                fetch(`/budget/get-user-type/${clientId}`)
                    .then(response => response.json())
                    .then(data => {
                        $('#newUserType').val(data.userType || '');
                        $('#subcategory').val(data.subcategoryVal || '');
                        $('#category').val(data.categoryVal || '');
                        $('#newLicensedEnvironmentId').val(data.licensedEnvironment || '');
                        $('#newCompany').val(data.companyVal || '');
                        if (data.billingFrequency) {
                            $('#newFrequency').val(data.billingFrequency);
                            setFinishFromBeginIfMonthly();
                            recomputeMonthsAndAmounts();
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });

        // Form submission - unformat numbers before sending
        $('#newRecordForm').on('submit', function() {
            // Unformat all number inputs before submission
            $('.number-input').each(function() {
                const formattedValue = $(this).val();
                if (formattedValue) {
                    const unformatted = unformatNumber(formattedValue);
                    $(this).val(unformatted);
                }
            });
        });

        setFinishFromBeginIfMonthly();
        syncHiddenYM('finish');
        recomputeMonthsAndAmounts();
    });

    // Main DataTable initialization
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        checkDeadlineAlert();

        // DataTable initialization with ALL filters properly configured
        let table = $('#budgetData').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('get-budgets.data') }}",
                data: function(d) {
                    // Send ALL filter values - use empty string if no value
                    d.month = $('#monthFilter').val() || '';
                    d.year = $('#yearFilter').val() || '';
                    d.start_date = $('#startDateFilter').val() || '';
                    d.end_date = $('#endDateFilter').val() || '';
                    d.conceptFilter = $('#conceptFilter').val() || '';
                    d.conditionFilter = $('#conditionFilter').val() || '';
                }
            },
            columns: [{
                    data: 'user_type',
                    name: 'user_type'
                },
                {
                    data: 'company',
                    name: 'company'
                },
                {
                    data: 'commercialName',
                    name: 'commercialName'
                },
                {
                    data: 'subTotal',
                    name: 'subTotal'
                },
                {
                    data: 'vat',
                    name: 'vat'
                },
                {
                    data: 'total',
                    name: 'total'
                },
                // { data: 'annual_value', name: 'annual_value' },
                {
                    data: 'condition',
                    name: 'condition'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            createdRow: function(row, data, dataIndex) {
                if (data.row_class) {
                    $(row).addClass(data.row_class);
                }
            },
            error: function(xhr, error, code) {
                console.log('DataTables Error:', xhr, error, code);
            }
        });

        // Filter change handlers - reload table when ANY filter changes
        $('#monthFilter, #yearFilter, #startDateFilter, #endDateFilter, #conceptFilter, #conditionFilter').on('change keyup', function() {
            table.ajax.reload(null, false);
            refreshConceptTotals(); // Refresh totals when filters change
        });

        $('#clearFilters').on('click', function() {
            $('#monthFilter, #yearFilter, #startDateFilter, #endDateFilter').val('');
            $('#conceptFilter, #conditionFilter').val('');
            table.ajax.reload(null, false);
            refreshConceptTotals();
        });

        // Initialize tooltips after table draw
        table.on('draw', function() {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
        });
    });

    // Enhanced totals refresh function
    function refreshConceptTotals() {
        const url = "{{ route('get-budgets.data') }}";
        const month = $('#monthFilter').val() || '';
        const year = $('#yearFilter').val() || '';
        const start_date = $('#startDateFilter').val() || '';
        const end_date = $('#endDateFilter').val() || '';
        const conceptFilter = $('#conceptFilter').val() || '';
        const conditionFilter = $('#conditionFilter').val() || '';

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                get_totals: 1,
                month: month,
                year: year,
                start_date: start_date,
                end_date: end_date,
                conceptFilter: conceptFilter,
                conditionFilter: conditionFilter
            },
            dataType: 'json',
            success: function(res) {
                if (!res.success) return;

                // Update each section with filtered data
                Object.keys(res.sections).forEach(sectionKey => {
                    const section = res.sections[sectionKey];
                    const sectionElement = $(`#section-${sectionKey}`);

                    // Update section title
                    sectionElement.find('.card-title').text(section.title);

                    // Update each concept in the section
                    section.concepts.forEach(function(concept) {
                        const selector = `#section-${sectionKey} tr[data-concept="${concept.name}"]`;
                        const row = $(selector);
                        const sub = formatEuro(concept.subTotal);
                        const vat = formatEuro(concept.vat);
                        const tot = formatEuro(concept.total);

                        if (row.length) {
                            row.find('.subtotal').html('Subtotal: $ ' + sub);
                            row.find('.vat').html('VAT: $ ' + vat);
                            row.find('.total').html('Total: $ ' + tot);
                        }
                    });

                    // Update section total
                    const sectionTotalRow = $(`#section-${sectionKey} .section-total-row`);
                    sectionTotalRow.find('.subtotal').html('<strong>Subtotal: $ ' + formatEuro(section.sectionTotal.subTotal) + '</strong>');
                    sectionTotalRow.find('.vat').html('<strong>VAT: $ ' + formatEuro(section.sectionTotal.vat) + '</strong>');
                    sectionTotalRow.find('.total').html('<strong>Total: $ ' + formatEuro(section.sectionTotal.total) + '</strong>');

                    // Show/hide section based on whether it has data
                    const hasData = section.sectionTotal.total > 0;
                    if (hasData) {
                        sectionElement.show().removeClass('section-empty').addClass('section-has-data');
                    } else {
                        sectionElement.hide().addClass('section-empty').removeClass('section-has-data');
                    }
                });

                // Update grand total
                $('#grandTotalsRow .subtotal').html('<strong>Subtotal: $ ' + formatEuro(res.grandTotal.subTotal) + '</strong>');
                $('#grandTotalsRow .vat').html('<strong>VAT: $ ' + formatEuro(res.grandTotal.vat) + '</strong>');
                $('#grandTotalsRow .total').html('<strong>Total: $ ' + formatEuro(res.grandTotal.total) + '</strong>');
            },
            error: function(xhr) {
                console.error(@json(__('Failed to refresh concept totals')), xhr);
            }
        });
    }

    function formatEuro(num) {
        num = parseFloat(num).toFixed(2);
        let parts = num.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return parts.join('.');
    }

    function checkDeadlineAlert() {
        $.ajax({
            url: '/budget/check-deadline',
            type: 'GET',
            success: function(response) {
                if (response.alert) {
                    $('#deadlineAlert').show();
                    $('#alertMessage').text(response.message);
                }
            }
        });
    }

    // Delete
    function deleteActivity(userId) {
        var recID = userId;
        Swal.fire({
            html: '<div class="mt-3"><lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon><div class="mt-4 pt-2 fs-15 mx-5"><p class="text-muted mx-4 mb-0">Are you Sure You want to Delete this budget?</p></div></div>',
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
                    url: '/budget/delete/' + recID,
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "recID": recID
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', response.success, 'success').then((result) => {
                            $('#budgetData').DataTable().ajax.reload(null, false);
                            refreshConceptTotals();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON.error, 'error');
                    }
                });
            }
        });
    }

    // Invoice modal
    function invoiceGenerate(recordID) {
        var myModalEl = document.getElementById('exampleModalScrollable');
        var myModal = new bootstrap.Modal(myModalEl);
        myModal.show();

        $('#exampleModalScrollableTitle').text(@json(__('Loading...')));
        $('#exampleModalScrollable .modal-body').html('<p>{{ __('
            Loading...') }}</p>');

        $.ajax({
            url: '/get-budget-record/' + recordID,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let criterionOptions = '<option value="">Select criterion...</option>';
                if (Array.isArray(data.criterions)) {
                    data.criterions.forEach(function(item) {
                        criterionOptions += `
                        <option value="${item.id}">
                            ${item.criterion_name}
                        </option>
                    `;
                    });
                }

                let consecutiveOptions = '<option value="">Select consecutive...</option>';
                if (Array.isArray(data.consecutives)) {
                    data.consecutives.forEach(function(item) {
                        consecutiveOptions += `
                        <option value="${item.id}">
                            ${item.consecutive_name}
                        </option>
                    `;
                    });
                }

                const defaultInvoiceDate = @json(date('Y-m-d'));

                $('#exampleModalScrollableTitle').text(@json(__('Register Invoice')));
                $('#exampleModalScrollable .modal-body').html(`
            <input type="hidden" name="budgetID" id="budgetID" value="${recordID}">
            <div class="row">
              <div class="col-md-6"><label class="form-label fw-bold">Company (Auto)</label><p class="form-control-static">${data.company}</p></div>
              <div class="col-md-6"><label class="form-label fw-bold">Commercial Name (Auto)</label><p class="form-control-static">${data.commercialName}</p></div>
            </div>
            <div class="row">
              <div class="col-md-6"><label class="form-label fw-bold">Category (Auto)</label><p class="form-control-static">${data.category || 'N/A'}</p></div>
              <div class="col-md-6"><label class="form-label fw-bold">Subcategory (Auto)</label><p class="form-control-static">${data.subcategory || 'N/A'}</p></div>
            </div>
            <div class="row">
              <div class="col-md-6"><label class="form-label fw-bold">Use Type (Auto)</label><p class="form-control-static">${data.licensedConcept || 'N/A'}</p></div>
            </div>
            <hr>
            <div class="row mb-2">
              <div class="col-md-12">
                <label for="invoiceConsecutive" class="form-label">Invoice Consecutive *</label>
                <select class="form-control" id="invoiceConsecutive" name="invoiceConsecutive" required>
                  ${consecutiveOptions}
                </select>
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-md-6">
                <label for="periodPaid" class="form-label">Period Paid *</label>
                <input type="text" name="periodPaid" id="periodPaid" class="form-control" placeholder="e.g., January 2025 or 2025" required>
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-md-12">
                <label for="criterion" class="form-label">Criterion *</label>
                <select class="form-control" id="criterion" name="criterion" required>
                  ${criterionOptions}
                </select>
              </div>
            </div>
            <hr>
            <div class="row mb-2">
              <div class="col-md-4"><label class="form-label fw-bold">Subtotal (Auto)</label><p class="form-control-static text-success">$ ${data.subTotal}</p></div>
              <div class="col-md-4"><label class="form-label fw-bold">VAT (Auto)</label><p class="form-control-static">${data.vat}%</p></div>
              <div class="col-md-4"><label class="form-label fw-bold">Total (Auto)</label><p class="form-control-static text-primary fw-bold">$ ${data.total}</p></div>
            </div>
            <div class="row mb-2">
              <div class="col-md-6"><label class="form-label fw-bold">Licensed Environment</label><p class="form-control-static">${data.licensedEnvironment || 'N/A'}</p></div>
            </div>
            <div class="mb-3">
              <label for="invoice_date" class="form-label">Invoice Date</label>
              <input type="date" name="invoice_date" id="invoice_date" class="form-control" value="${defaultInvoiceDate}">
            </div>
          `);
            },
            error: function() {
                $('#exampleModalScrollable .modal-body').html('<p class="text-danger">Error loading data.</p>');
            }
        });
    }

    // Submit invoice form
    $(document).on('submit', '#invoiceForm', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $('.loader--ripple').show();
        $.ajax({
            url: '/generate-invoice',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                $('.loader--ripple').hide();
                Swal.fire({
                    icon: 'success',
                    title: @json(__('Success')),
                    text: response.message || @json(__('Invoice generated successfully.')),
                    confirmButtonColor: '#3085d6',
                }).then(() => {
                    $('#exampleModalScrollable').modal('hide');
                    $('#budgetData').DataTable().ajax.reload(null, false);
                    refreshConceptTotals();
                    $('#registerInvoice').DataTable().ajax.reload(null, false);
                });
            },
            error: function(xhr) {
                $('.loader--ripple').hide();
                Swal.fire({
                    icon: 'error',
                    title: @json(__('Error!')),
                    text: xhr.responseJSON?.message || @json(__('Something went wrong. Please try again.')),
                    confirmButtonColor: '#d33',
                });
            }
        });
    });

    // Register Invoice DataTable
    $(document).ready(function() {
        let invoiceTable = $('#registerInvoice').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('get-invoice.data') }}",
                data: function(d) {
                    d.start_date = $('#invoiceDateStart').val();
                    d.end_date = $('#invoiceDateEnd').val();
                    d.status = $('#invoiceStatusFilter').val();
                    d.commercial_name = $('#invoiceCommercialFilter').val();
                    d.client_category = $('#invoiceCategoryFilter').val();
                    d.concept = $('#invoiceConceptFilter').val();
                    d.criterion = $('#invoiceCriterionFilter').val();
                }
            },
            columns: [{
                    data: 'client_category',
                    name: 'client_category'
                },
                {
                    data: 'sub_category',
                    name: 'sub_category'
                },
                {
                    data: 'commercialName',
                    name: 'commercialName'
                },
                {
                    data: 'invoiceNumber',
                    name: 'invoiceNumber'
                },
                {
                    data: 'invoiceDate',
                    name: 'invoiceDate'
                },
                {
                    data: 'concept',
                    name: 'concept'
                },
                {
                    data: 'period',
                    name: 'period'
                },
                {
                    data: 'criterion',
                    name: 'criterion'
                },
                {
                    data: 'subTotal',
                    name: 'subTotal'
                },
                {
                    data: 'vat',
                    name: 'vat'
                },
                {
                    data: 'total',
                    name: 'total'
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            drawCallback: function() {
                refreshInvoiceConceptTotals();
            },
            error: function(xhr, error, code) {
                console.log(xhr, error, code);
            }
        });

        $('#applyInvoiceFilters').on('click', function() {
            invoiceTable.ajax.reload();
        });

        $('#resetInvoiceFilters').on('click', function() {
            $('#invoiceStatusFilter, #invoiceCategoryFilter, #invoiceCriterionFilter').val('');
            $('#invoiceCommercialFilter, #invoiceConceptFilter').val('');
            $('#invoiceDateStart').val("{{ date('Y-m-01') }}");
            $('#invoiceDateEnd').val("{{ date('Y-m-t') }}");
            invoiceTable.ajax.reload();
        });

        invoiceTable.on('draw', function() {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
        });
        refreshInvoiceConceptTotals();
    });

    function refreshInvoiceConceptTotals() {
        const url = "{{ route('invoice.concepts.total') }}";
        const start = $('#invoiceDateStart').val() || '';
        const end = $('#invoiceDateEnd').val() || '';
        const status = $('#invoiceStatusFilter').val() || '';
        const commercial = $('#invoiceCommercialFilter').val() || '';
        const category = $('#invoiceCategoryFilter').val() || '';
        const concept = $('#invoiceConceptFilter').val() || '';
        const criterion = $('#invoiceCriterionFilter').val() || '';

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                start_date: start,
                end_date: end,
                status: status,
                commercial_name: commercial,
                client_category: category,
                concept: concept,
                criterion: criterion
            },
            dataType: 'json',
            success: function(response) {
                let html = "";

                // Check if there are any concepts with data
                const hasData = response.concepts && response.concepts.length > 0;

                if (hasData) {
                    response.concepts.forEach(c => {
                        html += `
                            <tr>
                                <td style="width: 35%;"><strong>${c.licensedConcept}</strong></td>
                                <td>Subtotal: $ ${formatEuroNew(c.subTotal)}</td>
                                <td>VAT: $ ${formatEuroNew(c.vat)}</td>
                                <td>Total: $ ${formatEuroNew(c.total)}</td>
                            </tr>
                        `;
                    });

                    html += `
                        <tr class="border-top">
                            <td><strong>Total Invoiced</strong></td>
                            <td><strong>Subtotal: $ ${formatEuroNew(response.grandTotal.subTotal)}</strong></td>
                            <td><strong>VAT: $ ${formatEuroNew(response.grandTotal.vat)}</strong></td>
                            <td><strong>Total: $ ${formatEuroNew(response.grandTotal.total)}</strong></td>
                        </tr>
                    `;
                } else {
                    html = `
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                <i class="ri-information-line me-2"></i>No invoices found for the selected filters
                            </td>
                        </tr>
                    `;
                }

                $("#invoiceConceptTotalsTable tbody").html(html);

                // Add visual indicator if filters are active
                const filtersActive = status || commercial || category || concept || criterion;
                if (filtersActive) {
                    $("#invoiceConceptTotalsTable").closest('.totals-card').addClass('filtered');
                } else {
                    $("#invoiceConceptTotalsTable").closest('.totals-card').removeClass('filtered');
                }
            },
            error: function(xhr) {
                console.error('Error loading invoice totals:', xhr.responseText);
                $("#invoiceConceptTotalsTable tbody").html(`
                    <tr>
                        <td colspan="4" class="text-center text-danger">
                            <i class="ri-error-warning-line me-2"></i>Error loading totals
                        </td>
                    </tr>
                `);
            }
        });
    }

    $(document).ready(function() {
        let invoiceTable = $('#registerInvoice').DataTable();
        // Enhanced invoice filters
        $('#applyInvoiceFilters').on('click', function() {
            invoiceTable.ajax.reload();
        });

        function reloadInvoices() {
            invoiceTable.ajax.reload(null, false);
        }
        $('#resetInvoiceFilters').on('click', function() {
            $('#invoiceStatusFilter, #invoiceCategoryFilter, #invoiceCriterionFilter').val('');
            $('#invoiceCommercialFilter, #invoiceConceptFilter').val('');
            $('#invoiceDateStart').val("{{ date('Y-m-01') }}");
            $('#invoiceDateEnd').val("{{ date('Y-m-t') }}");
            invoiceTable.ajax.reload();
        });

        invoiceTable.on('draw', function() {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
        });

        // Add after the invoiceTable initialization
        window.downloadInvoiceReport = function() {
            const start = $('#invoiceDateStart').val();
            const end = $('#invoiceDateEnd').val();
            const status = $('#invoiceStatusFilter').val();
            const commercial = $('#invoiceCommercialFilter').val();
            const category = $('#invoiceCategoryFilter').val();
            const concept = $('#invoiceConceptFilter').val();
            const criterion = $('#invoiceCriterionFilter').val();
            if (!start || !end) {
                Swal.fire('Error', 'Please select both start and end dates', 'error');
                return;
            }
            Swal.fire({
                title: 'Downloading...',
                html: 'Please wait while we prepare your Excel file',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            let url = "{{ route('invoice.download') }}?start_date=" + start + "&end_date=" + end;
            if (status) url += "&status=" + status;
            if (commercial) url += "&commercial_name=" + encodeURIComponent(commercial);
            if (category) url += "&client_category=" + category;
            if (concept) url += "&concept=" + encodeURIComponent(concept);
            if (criterion) url += "&criterion=" + criterion;
            window.location.href = url;
            setTimeout(() => {
                Swal.close();
            }, 1000);
        }
    });

    function formatEuroNew(num) {
        num = parseFloat(num).toFixed(2);
        let parts = num.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return parts.join('.');
    }

    function loadInvoiceConceptTotals() {
        $.ajax({
            url: "{{ route('invoice.concepts.total') }}",
            type: "GET",
            success: function(response) {
                let html = "";
                response.concepts.forEach(c => {
                    html += `
              <tr>
                <td style="width: 35%;"><strong>${c.licensedConcept}</strong></td>
                <td>Subtotal: $ ${formatEuroNew(c.subTotal)}</td>
                <td>VAT: $ ${formatEuroNew(c.vat)}</td>
                <td>Total: $ ${formatEuroNew(c.total)}</td>
              </tr>
            `;
                });
                html += `
            <tr class="border-top">
              <td><strong>Total Invoiced</strong></td>
              <td><strong>Subtotal: $ ${formatEuroNew(response.grandTotal.subTotal)}</strong></td>
              <td><strong>VAT: $ ${formatEuroNew(response.grandTotal.vat)}</strong></td>
              <td><strong>Total: $ ${formatEuroNew(response.grandTotal.total)}</strong></td>
            </tr>
          `;
                $("#invoiceConceptTotalsTable tbody").html(html);
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });
    }
    $(document).ready(function() {
        loadInvoiceConceptTotals();
    });

    // Billing and Credit Notes JavaScript remains the same
    $(function() {
        const $start = $('#billingPeriodStart');
        const $end = $('#billingPeriodEnd');

        const billingTable = $('#billingInvoicesTable').DataTable({
            processing: true,
            serverSide: true,
            order: [
                [4, 'asc']
            ],
            ajax: {
                url: "{{ route('billing.list') }}",
                data: function(d) {
                    d.start_date = $start.val();
                    d.end_date = $end.val();
                }
            },
            columns: [{
                    data: 'user_type',
                    name: 'user_type'
                },
                {
                    data: 'company',
                    name: 'company'
                },
                {
                    data: 'commercialName',
                    name: 'commercialName'
                },
                {
                    data: 'concept',
                    name: 'concept'
                },
                {
                    data: 'invoice_no',
                    name: 'invoice_no'
                },
                {
                    data: 'invoice_date',
                    name: 'invoice_date'
                },
                {
                    data: 'period',
                    name: 'period'
                },
                {
                    data: 'criterion',
                    name: 'criterion'
                },
                {
                    data: 'subtotal',
                    name: 'subtotal'
                },
                {
                    data: 'vat',
                    name: 'vat'
                },
                {
                    data: 'total',
                    name: 'total'
                },
                {
                    data: 'balance',
                    name: 'balance'
                },
                {
                    data: 'supporting_doc',
                    name: 'supporting_doc',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],
            drawCallback: function(settings) {
                const json = settings.json || {};
                if (json.totals) {
                    $('#totalBilling').text(json.totals.billing);
                    $('#totalCreditNotes').text(json.totals.creditNotes);
                    $('#totalPortfolio').text(json.totals.portfolio);
                }
            }
        });

        const cnTable = $('#creditNotesTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('billing.creditnotes') }}",
                data: function(d) {
                    d.start_date = $start.val();
                    d.end_date = $end.val();
                }
            },
            order: [
                [4, 'asc']
            ],
            columns: [{
                    data: 'user_type',
                    name: 'user_type'
                },
                {
                    data: 'company',
                    name: 'company'
                },
                {
                    data: 'commercialName',
                    name: 'commercialName'
                },
                {
                    data: 'concept',
                    name: 'concept'
                },
                {
                    data: 'cn_no',
                    name: 'cn_no'
                },
                {
                    data: 'cn_date',
                    name: 'cn_date'
                },
                {
                    data: 'period',
                    name: 'period'
                },
                {
                    data: 'criterion',
                    name: 'criterion'
                },
                {
                    data: 'subtotal',
                    name: 'subtotal'
                },
                {
                    data: 'vat',
                    name: 'vat'
                },
                {
                    data: 'total',
                    name: 'total'
                },
                {
                    data: 'supporting_doc',
                    name: 'supporting_doc',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        function reloadBoth() {
            billingTable.ajax.reload(null, false);
            cnTable.ajax.reload(null, false);
        }

        $start.add($end).on('change', reloadBoth);
        // window.generateBillingReport = function() {
        //     window.location = "{{ route('billing.report') }}?start_date=" + $start.val() + "&end_date=" + $end.val();
        // }
        // window.downloadBillingExcel = function() {
        //     window.location = "{{ route('billing.download') }}?start_date=" + $start.val() + "&end_date=" + $end.val();
        // }
        window.uploadBillingExcel = function() {
            $.post("{{ route('billing.upload') }}", {
                _token: "{{ csrf_token() }}"
            }, function(res) {
                Swal.fire('Upload', res.message || 'OK', 'info');
            });
        }

        window.generateBillingReport = function() {
            const start = $('#billingPeriodStart').val();
            const end = $('#billingPeriodEnd').val();

            if (!start || !end) {
                Swal.fire('Error', 'Please select both start and end dates', 'error');
                return;
            }

            Swal.fire({
                title: 'Generating Report...',
                html: 'Please wait while we generate your PDF report',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Open in new tab to trigger download
            window.open("{{ route('billing.report') }}?start_date=" + start + "&end_date=" + end, '_blank');

            setTimeout(() => {
                Swal.close();
            }, 1000);
        }

        window.downloadBillingExcel = function() {
            const start = $('#billingPeriodStart').val();
            const end = $('#billingPeriodEnd').val();

            if (!start || !end) {
                Swal.fire('Error', 'Please select both start and end dates', 'error');
                return;
            }

            Swal.fire({
                title: 'Downloading...',
                html: 'Please wait while we prepare your Excel file',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Create a hidden form to submit
            const form = $('<form>', {
                'method': 'GET',
                'action': "{{ route('billing.download') }}"
            });

            form.append($('<input>', {
                'type': 'hidden',
                'name': 'start_date',
                'value': start
            }));

            form.append($('<input>', {
                'type': 'hidden',
                'name': 'end_date',
                'value': end
            }));

            $('body').append(form);
            form.submit();
            form.remove();

            setTimeout(() => {
                Swal.close();
            }, 1000);
        }

        window.openQuickRegister = function() {
            $('#newRecordModal').modal('show');
        }

        window.openCreditNoteModal = function(invoiceId) {
            const html = `
              <form id="cnForm" class="text-start">
                <input type="hidden" name="invoice_id" value="${invoiceId}">
                <div class="mb-2">
                  <label class="form-label">CN No. *</label>
                  <input class="form-control" name="cn_number" required>
                </div>
                <div class="mb-2">
                  <label class="form-label">CN Date *</label>
                  <input type="date" class="form-control" name="cn_date" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="mb-2">
                  <label class="form-label">Reason</label>
                  <input class="form-control" name="reason" placeholder="Optional">
                </div>
                <div class="row g-2 mb-2">
                  <div class="col-md-4">
                    <label class="form-label">Subtotal *</label>
                    <input type="number" step="0.01" class="form-control" id="cnSubTotal" name="subTotal" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">VAT % *</label>
                    <input type="number" step="0.01" class="form-control" id="cnVat" name="vat" value="12" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Total *</label>
                    <input type="number" step="0.01" class="form-control" id="cnTotal" name="total" readonly required>
                  </div>
                </div>
                <div class="mb-2">
                  <label class="form-label">Supporting Doc (File)</label>
                  <input type="file" class="form-control" id="cnSupportingDoc" name="supporting_doc" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                  <small class="text-muted">Upload supporting document (PDF, Image, or Word)</small>
                </div>
              </form>
            `;

            Swal.fire({
                title: 'Register Credit Note',
                html: html,
                width: '600px',
                showCancelButton: true,
                confirmButtonText: 'Save',
                didOpen: () => {
                    // Auto-calculate total when subtotal or VAT changes
                    const $subTotal = $('#cnSubTotal');
                    const $vat = $('#cnVat');
                    const $total = $('#cnTotal');

                    function calculateTotal() {
                        const subTotal = parseFloat($subTotal.val()) || 0;
                        const vatPercent = parseFloat($vat.val()) || 0;
                        const total = subTotal + (subTotal * vatPercent / 100);
                        $total.val(total.toFixed(2));
                    }

                    $subTotal.on('input', calculateTotal);
                    $vat.on('input', calculateTotal);
                },
                preConfirm: () => {
                    const $f = $('#cnForm');
                    const formData = new FormData($f[0]);

                    // Validate file size (max 5MB)
                    const fileInput = document.getElementById('cnSupportingDoc');
                    if (fileInput.files.length > 0) {
                        const fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
                        if (fileSize > 5) {
                            Swal.showValidationMessage('File size must be less than 5MB');
                            return false;
                        }
                    }

                    return $.ajax({
                            url: "{{ route('billing.cn.store') }}",
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            }
                        }).then(res => res)
                        .catch(xhr => {
                            const msg = xhr.responseJSON?.message || 'Validation error';
                            Swal.showValidationMessage(msg);
                        });
                }
            }).then(result => {
                if (result.isConfirmed) {
                    Swal.fire('Saved', result.value?.message || 'Credit Note saved', 'success');
                    reloadBoth();
                }
            });
        }

        window.deleteCN = function(id) {
            Swal.fire({
                title: 'Delete Credit Note?',
                icon: 'warning',
                showCancelButton: true
            }).then(ok => {
                if (!ok.isConfirmed) return;
                $.ajax({
                    url: "{{ url('/billing/credit-note') }}/" + id,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        Swal.fire('Deleted', res.message || 'OK', 'success');
                        reloadBoth();
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed', 'error');
                    }
                });
            });
        }
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        // Initialize number formatting for income form
        initializeNumberFormatting();

        // Income DataTable
        const $incomeStart = $('#incomePeriodStart');
        const $incomeEnd = $('#incomePeriodEnd');

        const incomeTable = $('#incomeTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('income.data') }}",
                data: function(d) {
                    d.start_date = $incomeStart.val();
                    d.end_date = $incomeEnd.val();
                }
            },
            columns: [{
                    data: 'income_date',
                    name: 'income_date'
                },
                {
                    data: 'mode',
                    name: 'mode'
                },
                {
                    data: 'bank_code',
                    name: 'bank_code'
                },
                {
                    data: 'company',
                    name: 'company'
                },
                {
                    data: 'commercial_name',
                    name: 'commercial_name'
                },
                {
                    data: 'income_amount',
                    name: 'income_amount'
                },
                {
                    data: 'other_amounts',
                    name: 'other_amounts'
                },
                {
                    data: 'total_paid',
                    name: 'total_paid'
                },
                {
                    data: 'invoice_number',
                    name: 'invoice_number',
                    render: function(data, type, row) {
                        if (!row.invoice_ids || !Array.isArray(row.invoice_ids)) {
                            return data || '-';
                        }
                        return row.invoice_ids.map(inv => `<span class="badge bg-light text-dark border me-1">${inv}</span>`).join("");
                    }
                },
                {
                    data: 'invoice_date',
                    name: 'invoice_date'
                },
                {
                    data: 'concept',
                    name: 'concept'
                },
                {
                    data: 'invoice_period',
                    name: 'invoice_period'
                },
                {
                    data: 'invoice_value',
                    name: 'invoice_value'
                },
                {
                    data: 'balance',
                    name: 'balance'
                },
                {
                    data: 'rc_number',
                    name: 'rc_number'
                },
                {
                    data: 'rc_date',
                    name: 'rc_date'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            drawCallback: function(settings) {
                const json = settings.json || {};
                if (json.totals) {
                    updateIncomeTotals(json.totals);
                }
            }
        });

        // Period change → reload table
        $incomeStart.add($incomeEnd).on('change', function() {
            incomeTable.ajax.reload();
        });

        // Update summary cards + concept totals table
        function updateIncomeTotals(totals) {
            const start = $('#incomePeriodStart').val();
            const end = $('#incomePeriodEnd').val();

            $.get("{{ route('income.totals.by.concept') }}", {
                start_date: start,
                end_date: end
            }, function(res) {

                let html = '';

                if (res.rows.length === 0) {
                    html = `<tr><td colspan="4">No data found</td></tr>`;
                } else {
                    res.rows.forEach(row => {
                        html += `
                            <tr>
                                <td><strong>${row.concept}</strong></td>
                                <td>Income: ${formatEuro(row.income.toFixed(2))}</td>
                                <td>Other: ${formatEuro(row.other.toFixed(2))}</td>
                                <td>Total Paid: ${formatEuro(row.paid.toFixed(2))}</td>
                            </tr>
                        `;
                    });
                }

                $('#incomeConceptTotalsTable tbody').html(html);

                // Update cards
                $('#totalIncomeAmount').text(formatEuro(res.summary.income.toFixed(2)));
                $('#totalOtherAmounts').text(formatEuro(res.summary.other.toFixed(2)));
                $('#totalPaidAmount').text(formatEuro(res.summary.paid.toFixed(2)));
            });
        }

        // ========== Company & Invoice Behaviour ==========

        // Company selection – load invoices and fill names
        $('#incomeCompany').on('change', function() {
            const companyId = $(this).val();
            const selectedOption = $(this).find('option:selected');
            const incomeDate = $('#incomeDate').val();

            if (companyId) {
                $('#incomeCompanyName').val(selectedOption.data('company') || '');
                $('#incomeCommercialName').val(selectedOption.data('commercial') || '');

                // Load invoices for selected company (considering income date)
                $.get(`/income/company-invoices/${companyId}`, {
                    income_date: incomeDate
                }, function(data) {
                    let options = '<option value="">Select Invoice...</option>';

                    data.forEach(inv => {
                        options += `
                            <option value="${inv.id}"
                                data-invoice-number="${inv.invoice_number}"
                                data-invoice-date="${inv.invoice_date}"
                                data-concept="${inv.concept}"
                                data-period="${inv.invoice_period}"
                                data-value="${inv.invoice_value}"
                                data-balance="${inv.balance}"
                                data-company="${inv.company}"
                                data-commercial="${inv.commercial_name}">
                                ${inv.invoice_number} - (Balance: $${inv.balance.toFixed(2)})
                            </option>
                        `;
                    });

                    $('#incomeInvoice').html(options).prop('disabled', false);

                    // Also populate surplus invoice dropdown (excluding currently selected invoice)
                    updateSurplusInvoiceDropdown(data, null);
                });
            } else {
                clearInvoiceFields();
                $('#incomeInvoice')
                    .html('<option value="">Select Invoice...</option>')
                    .prop('disabled', true);
                $('#surplusSection').hide();
            }
        });

        // Update surplus invoice dropdown
        function updateSurplusInvoiceDropdown(invoices, excludeId) {
            let options = '<option value="">Select another invoice...</option>';

            invoices.forEach(inv => {
                if (inv.id != excludeId) {
                    options += `
                        <option value="${inv.id}"
                            data-invoice-number="${inv.invoice_number}"
                            data-concept="${inv.concept}"
                            data-period="${inv.invoice_period}"
                            data-balance="${inv.balance}">
                            ${inv.invoice_number} - (Balance: $${inv.balance.toFixed(2)})
                        </option>
                    `;
                }
            });

            $('#surplusInvoice').html(options);
        }

        // Unified Invoice selection handler for multi-invoice support
        $(document).on('change', '#incomeInvoice', function() {
            const selectedOptions = $(this).find('option:selected');
            const container = $('#invoice-details-container');
            const selectedInvoicesSection = $('#selectedInvoicesSection');
            const selectedInvoicesBody = $('#selectedInvoicesBody');

            container.empty();
            selectedInvoicesBody.empty();

            if (selectedOptions.length > 0 && selectedOptions.first().val() !== '') {
                let totalInvoiceValue = 0;
                let totalRemainingBalance = 0;

                selectedOptions.each(function() {
                    const opt = $(this);
                    const id = opt.val();
                    const number = opt.data('invoice-number') || opt.text();
                    const date = opt.data('invoice-date') || '-';
                    const concept = opt.data('concept') || '-';
                    const period = opt.data('period') || '-';
                    const value = parseFloat(opt.data('value')) || 0;
                    const balance = parseFloat(opt.data('balance')) || 0;
                    const company = opt.data('company') || '';
                    const commercial = opt.data('commercial') || '';

                    totalInvoiceValue += value;
                    totalRemainingBalance += balance;

                    // Fill Auto-filled details container
                    container.append(`
                        <div class="card bg-light border mb-2">
                            <div class="card-body p-2">
                                <div class="row g-2">
                                    <div class="col-md-3"><strong>No:</strong> ${number}</div>
                                    <div class="col-md-3"><strong>Date:</strong> ${date}</div>
                                    <div class="col-md-3"><strong>Period:</strong> ${period}</div>
                                    <div class="col-md-3 text-end"><strong>Balance:</strong> ${formatEuro(balance)}</div>
                                </div>
                                <div class="row g-2 mt-1">
                                    <div class="col-md-6"><small><strong>Concept:</strong> ${concept}</small></div>
                                    <div class="col-md-6 text-end"><small><strong>Value:</strong> ${formatEuro(value)}</small></div>
                                </div>
                            </div>
                        </div>
                    `);

                    // Fill table for amounts allocation
                    const row = `<tr data-invoice-id="${id}">
                        <td>${number}</td>
                        <td>${formatEuro(balance)}</td>
                        <td>
                            <div class="currency-symbol">
                                <input type="text" class="form-control number-input applyAmount" value="${formatEuro(balance)}" data-thousand-separator>
                            </div>
                        </td>
                        <td><button type="button" class="btn btn-sm btn-danger removeInvoiceRow"><i class="ri-delete-bin-line"></i></button></td>
                    </tr>`;
                    selectedInvoicesBody.append(row);

                    // Set company fields from first selected invoice (they should all be from same company)
                    $('#incomeCompanyName').val(company);
                    $('#incomeCommercialName').val(commercial);
                });

                // Store totals for summary
                $('#invoice-details-container').data('total-value', totalInvoiceValue);
                $('#invoice-details-container').data('total-balance', totalRemainingBalance);

                selectedInvoicesSection.show();
                initializeNumberFormatting(); // Re-init for new inputs
            } else {
                container.html('<div class="text-muted text-center py-3">Select invoices to see details</div>');
                selectedInvoicesSection.hide();
                $('#incomeCompanyName, #incomeCommercialName').val('');
                $('#invoice-details-container').removeData('total-value');
                $('#invoice-details-container').removeData('total-balance');
            }

            updatePaymentSummaryDisplay();
            checkForSurplus();
        });

        $(document).on('click', '.removeInvoiceRow', function() {
            const id = $(this).closest('tr').data('invoice-id');
            const select = $('#incomeInvoice');
            const values = select.val() || [];
            const newValues = values.filter(v => v != id);
            select.val(newValues).trigger('change');
        });

        $(document).on('click', '.removeInvoiceRow', function() {
            $(this).closest('tr').remove();
            if ($('#selectedInvoicesBody tr').length === 0) {
                $('#selectedInvoicesSection').hide();
            }
        });

        // Surplus invoice selection
        $('#surplusInvoice').on('change', function() {
            const selectedOption = $(this).find('option:selected');

            if ($(this).val()) {
                $('#surplusInvoiceNumber').val(selectedOption.data('invoice-number') || '');
                $('#surplusInvoiceBalance').val(formatEuro(selectedOption.data('balance') || 0));
                $('#surplusConcept').val(selectedOption.data('concept') || '');
                $('#surplusPeriod').val(selectedOption.data('period') || '');

                const maxSurplus = calculateSurplus();
                $('#maxSurplusAmount').text(formatEuro(maxSurplus));

                $('#surplusInvoiceDetails').show();
            } else {
                $('#surplusInvoiceDetails').hide();
                $('#surplusAmount').val('');
            }
        });

        // Monitor surplus amount input
        $('#surplusAmount').on('input', function() {
            const surplusValue = parseFormattedNumber($(this).val() || '0');
            const maxSurplus = calculateSurplus();
            const remaining = Math.max(maxSurplus - surplusValue, 0);

            $('#remainingSurplus').val(formatEuro(remaining));

            // Validation
            if (surplusValue > maxSurplus) {
                $(this).addClass('is-invalid');
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Amount',
                    text: `Surplus amount cannot exceed ${formatEuro(maxSurplus)}`,
                    toast: true,
                    position: 'top-end',
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Helper to clear invoice fields
        function clearInvoiceFields() {
            $('#invoice-details-container').html('<div class="text-muted text-center py-3">Select invoices to see details</div>');
            $('#invoice-details-container').removeData('total-value');
            $('#invoice-details-container').removeData('total-balance');
            $('#incomeCompanyName, #incomeCommercialName').val('');
            updatePaymentSummaryDisplay();
            $('#surplusSection').hide();
        };

        function updatePaymentSummaryDisplay() {
            const income = parseFormattedNumber($('#incomeAmount').val() || '0');
            const other = parseFormattedNumber($('#incomeOtherAmounts').val() || '0');
            const totalPaid = income + other;

            let allocated = 0;
            $('.applyAmount').each(function() {
                allocated += parseFormattedNumber($(this).val() || '0');
            });

            const currentPayment = allocated > 0 ? allocated : totalPaid;
            const rcBalance = Math.max(totalPaid - currentPayment, 0);

            $('#summaryInvoiceTotalDisplay').text(formatEuro(totalPaid));
            $('#summaryRemainingBalanceDisplay').text(formatEuro(rcBalance));
            $('#totalPaidDisplay').text(formatEuro(currentPayment));
        }

        // Calculate surplus amount
        function calculateSurplus() {
            const invoiceTotal = parseFloat($('#invoice-details-container').data('total-value')) || 0;
            const totalPaid = parseFormattedNumber($('#incomeAmount').val() || '0') + parseFormattedNumber($('#incomeOtherAmounts').val() || '0');

            const surplus = Math.max(totalPaid - invoiceTotal, 0);
            return surplus;
        }

        // Check for surplus and show/hide surplus section
        function checkForSurplus() {
            const surplus = calculateSurplus();

            if (surplus > 0) {
                $('#surplusSection').show();
                $('#surplusAmountDisplay').text(formatEuro(surplus));
            } else {
                $('#surplusSection').hide();
            }
        }

        // Total Paid = Income Amount + Other Amounts
        function calculateTotalPaid() {
            updatePaymentSummaryDisplay();

            // Check for surplus whenever payment amounts change
            if ($('#incomeInvoice').val()) {
                checkForSurplus();
            }
        }

        // Recalculate when user types
        $('#incomeAmount, #incomeOtherAmounts').on('input', calculateTotalPaid);

        // Form submission handling
        $('#incomeForm').on('submit', function(e) {
            e.preventDefault();

            // Validate surplus if applicable
            const surplus = calculateSurplus();
            const surplusInvoiceId = $('#surplusInvoice').val();
            const surplusAmount = parseFormattedNumber($('#surplusAmount').val() || '0');

            if (surplus > 0 && surplusInvoiceId && surplusAmount > surplus) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Surplus Amount',
                    text: `Surplus amount cannot exceed ${formatEuro(surplus)}`
                });
                return;
            }

            // RC Number is now optional for surplus as well
            /*
            if (surplus > 0 && surplusInvoiceId && !$('#surplusRCNumber').val()) {
                Swal.fire({
                    icon: 'error',
                    title: 'RC Number Required',
                    text: 'Please provide an RC Number for the surplus invoice'
                });
                return;
            }
            */

            // Proceed with saving
            saveIncome(false);
        });

        // Save & Add New button
        $('#saveAndAddNewIncome').on('click', function() {
            saveIncome(true);
        });

        function saveIncome(addAnother) {
            // Collect selected invoices and amounts
            var selectedRows = $('#selectedInvoicesBody tr');
            var invoiceIds = [];
            var invoiceAmounts = [];

            if (selectedRows.length > 0) {
                selectedRows.each(function() {
                    invoiceIds.push($(this).data('invoice-id'));
                    invoiceAmounts.push(unformatNumber($(this).find('.applyAmount').val()));
                });
            }

            if (invoiceIds.length > 0) {
                let totalDistributed = 0;
                $('.applyAmount').each(function() {
                    let val = unformatNumber($(this).val() || '0').trim();
                    totalDistributed += parseFloat(val) || 0;
                });

                let incomeAmount = unformatNumber($('#incomeAmount').val() || '0').trim();
                incomeAmount = parseFloat(incomeAmount) || 0;

                if (Math.abs(totalDistributed - incomeAmount) > 0.01) {
                    Swal.fire({
                        icon: 'error',
                        title: @json(__('Validation Error')),
                        text: @json(__('Total distributed amount must match income amount'))
                    });
                    return;
                }
            }

            const formData = new FormData($('#incomeForm')[0]);
            const url = $('#incomeId').val() ?
                `/income/update/${$('#incomeId').val()}` :
                "{{ route('income.store') }}";

            // Append to FormData after it's created
            if (invoiceIds.length > 0) {
                formData.delete('invoice_ids[]');
                invoiceIds.forEach(function(id) {
                    formData.append('invoice_ids[]', id);
                });

                formData.delete('invoice_amounts[]');
                invoiceAmounts.forEach(function(amt) {
                    formData.append('invoice_amounts[]', amt);
                });
            }

            // Unformat numbers before submission
            const fieldsToUnformat = ['income_amount', 'other_amounts', 'surplus_amount'];
            fieldsToUnformat.forEach(field => {
                const input = $(`[name="${field}"]`);
                if (input.length && input.val()) {
                    const unformatted = unformatNumber(input.val());
                    formData.set(field, unformatted);
                }
            });

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.status) {
                        Swal.fire(@json(__('Success')), response.message || @json(__('Income registered successfully')), 'success');
                        incomeTable.ajax.reload();

                        if (addAnother) {
                            // Reset form for new entry
                            $('#incomeForm')[0].reset();
                            $('#incomeId').val('');
                            clearInvoiceFields();
                            $('#incomeDate').val('{{ date("Y-m-d") }}');
                            $('#incomeRCDate').val("{{ date('Y-m-t') }}");
                            $('#incomeMode').val('Transfer');
                            $('#incomeBank').val($('#incomeBank option:first').val());
                            updatePaymentSummaryDisplay();
                            $('#surplusSection').hide();
                            $('#incomeInvoice').html('<option value="">Select Invoice...</option>').prop('disabled', true);
                            initializeNumberFormatting(); // Re-initialize formatting for new inputs
                        } else {
                            $('#incomeModal').modal('hide');
                        }
                    }
                },
                error: function(xhr) {
                    Swal.fire(@json(__('Error!')), xhr.responseJSON?.message || @json(__('Failed to save income')), 'error');
                }
            });
        }

        // Open modal function
        window.openIncomeModal = function() {
            $('#incomeForm')[0].reset();
            $('#incomeId').val('');
            $('#incomeModalTitle').text(@json(__('Register Income')));
            $('#incomeDate').val('{{ date("Y-m-d") }}');
            $('#incomeRCDate').val("{{ date('Y-m-t') }}");
            $('#incomeMode').val('Transfer');
            const defaultBank = $('#incomeBank option[selected]');
            if (defaultBank.length) {
                $('#incomeBank').val(defaultBank.val());
            }
            updatePaymentSummaryDisplay();
            $('#surplusSection').hide();
            $('#surplusInvoiceDetails').hide();
            $('#incomeInvoice').html('<option value="">Select Invoice...</option>').prop('disabled', true);
            clearInvoiceFields();
            initializeNumberFormatting();
            $('#incomeModal').modal('show');
        }

        // Edit income function
        // Edit income function - FIXED VERSION for invoice selection
        window.editIncome = function(id) {
            $.get(`/income/edit/${id}`, function(data) {
                // Reset form first
                $('#incomeForm')[0].reset();
                $('#surplusSection').hide();
                $('#surplusInvoiceDetails').hide();

                // Set basic fields
                $('#incomeId').val(data.id);
                $('#incomeMode').val(data.mode);
                $('#incomeBank').val(data.bank_code);
                $('#incomeDate').val(data.income_date);

                // Format and set number fields
                $('#incomeAmount').val(formatNumberForDisplay(data.income_amount));
                $('#incomeOtherAmounts').val(formatNumberForDisplay(data.other_amounts));

                // Set RC fields
                $('#incomeRCNumber').val(data.rc_number);
                $('#incomeRCDate').val(data.rc_date);

                // Set company and invoice fields
                if (data.company_id) {
                    $('#incomeCompany').val(data.company_id);

                    // Load invoices for this company
                    $.get(`/income/company-invoices/${data.company_id}`, {
                        income_date: data.income_date,
                        include_paid: true // Add this parameter to include all invoices
                    }, function(invoices) {
                        let options = '<option value="">Select Invoice...</option>';
                        let invoiceFound = false;

                        invoices.forEach(inv => {
                            const isSelected = inv.id == data.invoice_id;
                            if (isSelected) invoiceFound = true;

                            options += `
                                <option value="${inv.id}" ${isSelected ? 'selected' : ''}
                                    data-invoice-number="${inv.invoice_number}"
                                    data-invoice-date="${inv.invoice_date}"
                                    data-concept="${inv.concept}"
                                    data-period="${inv.invoice_period}"
                                    data-value="${inv.invoice_value}"
                                    data-balance="${inv.balance}"
                                    data-company="${inv.company}"
                                    data-commercial="${inv.commercial_name}">
                                    ${inv.invoice_number} - (Balance: $${inv.balance.toFixed(2)})
                                </option>
                            `;
                        });

                        // If the invoice wasn't found in the list (fully paid), add it manually
                        if (data.invoice_id && !invoiceFound) {
                            options += `
                                <option value="${data.invoice_id}" selected
                                    data-invoice-number="${data.invoice_number}"
                                    data-invoice-date="${data.invoice_date}"
                                    data-concept="${data.concept}"
                                    data-period="${data.invoice_period}"
                                    data-value="${data.invoice_value}"
                                    data-balance="${data.balance || 0}"
                                    data-company="${data.company}"
                                    data-commercial="${data.commercial_name}">
                                    ${data.invoice_number} - (Paid Invoice)
                                </option>
                            `;
                        }

                        $('#incomeInvoice').html(options).prop('disabled', false);

                        // Set the invoice value
                        if (data.invoice_id) {
                            $('#incomeInvoice').val(data.invoice_id);
                        }

                        // Populate invoice-related fields
                        $('#incomeInvoiceNumber').val(data.invoice_number || '');
                        $('#incomeInvoiceDate').val(data.invoice_date || '');
                        $('#incomeConcept').val(data.concept || '');
                        $('#incomeInvoicePeriod').val(data.invoice_period || '');
                        if (data.invoice_value) {
                            $('#incomeInvoiceValue').val(formatEuro(data.invoice_value));
                        }
                        $('#incomeInvoiceValue').data('raw', Number(data.invoice_value || 0));
                        $('#incomeRemainingBalance').val(formatEuro(data.balance || 0));
                        $('#incomeRemainingBalance').data('raw', Number(data.balance || 0));

                        // Set company name fields
                        $('#incomeCompanyName').val(data.company || '');
                        $('#incomeCommercialName').val(data.commercial_name || '');
                    }).fail(function() {
                        // If loading invoices fails, manually populate the invoice
                        $('#incomeInvoice').html(`
                            <option value="${data.invoice_id}" selected>
                                ${data.invoice_number} - (Recorded Invoice)
                            </option>
                        `).prop('disabled', false);

                        // Populate all fields manually
                        $('#incomeInvoiceNumber').val(data.invoice_number || '');
                        $('#incomeInvoiceDate').val(data.invoice_date || '');
                        $('#incomeConcept').val(data.concept || '');
                        $('#incomeInvoicePeriod').val(data.invoice_period || '');
                        if (data.invoice_value) {
                            $('#incomeInvoiceValue').val(formatEuro(data.invoice_value));
                        }
                        $('#incomeInvoiceValue').data('raw', Number(data.invoice_value || 0));
                        $('#incomeRemainingBalance').val(formatEuro(data.balance || 0));
                        $('#incomeRemainingBalance').data('raw', Number(data.balance || 0));
                        $('#incomeCompanyName').val(data.company || '');
                        $('#incomeCommercialName').val(data.commercial_name || '');
                    });
                } else {
                    // No company - just set the name fields
                    $('#incomeCompanyName').val(data.company || '');
                    $('#incomeCommercialName').val(data.commercial_name || '');
                    $('#incomeInvoice').html('<option value="">Select Invoice...</option>').prop('disabled', true);

                    // Manually populate invoice fields if they exist
                    if (data.invoice_number) {
                        $('#incomeInvoiceNumber').val(data.invoice_number);
                        $('#incomeInvoiceDate').val(data.invoice_date || '');
                        $('#incomeConcept').val(data.concept || '');
                        $('#incomeInvoicePeriod').val(data.invoice_period || '');
                        if (data.invoice_value) {
                            $('#incomeInvoiceValue').val(formatEuro(data.invoice_value));
                        }
                        $('#incomeInvoiceValue').data('raw', Number(data.invoice_value || 0));
                        $('#incomeRemainingBalance').val(formatEuro(data.balance || 0));
                        $('#incomeRemainingBalance').data('raw', Number(data.balance || 0));
                    }
                }

                // Calculate and display total paid
                calculateTotalPaid();

                // Change modal title
                $('#incomeModalTitle').text(@json(__('Edit Income')));

                // Re-initialize number formatting
                initializeNumberFormatting();

                // Show modal
                $('#incomeModal').modal('show');
            }).fail(function(xhr) {
                Swal.fire('Error!', 'Failed to load income data', 'error');
                console.error('Error loading income:', xhr);
            });
        }

        // Delete income function
        window.deleteIncome = function(id) {
            Swal.fire({
                title: 'Delete Income?',
                text: 'This action cannot be undone',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/income/delete/${id}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire('Deleted!', response.message, 'success');
                            incomeTable.ajax.reload();
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete', 'error');
                        }
                    });
                }
            });
        }

        // Download income report
        window.downloadIncomeReport = function() {
            const start = $('#incomePeriodStart').val();
            const end = $('#incomePeriodEnd').val();
            window.location.href = `/income/download/report?start_date=${start}&end_date=${end}`;
        }

        // Helper functions
        function formatEuro(num) {
            num = parseFloat(num).toFixed(2);
            let parts = num.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return '$ ' + parts.join('.');
        }

        function parseFormattedNumber(value) {
            if (!value) return 0;
            const unformatted = unformatNumber(value);
            return parseFloat(unformatted) || 0;
        }

        function formatNumberForDisplay(value) {
            if (!value && value !== 0) return '';
            const num = typeof value === 'string' ? parseFormattedNumber(value) : value;
            return formatNumberWithDots(num.toFixed(2).replace('.', ','));
        }
    });
</script>

<!-- Validation Module JavaScript -->
<script>
    // Validation Module JavaScript
    $(document).ready(function() {
        // Initialize Validation DataTable
        const validationTable = $('#validationsTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "/validations/data",
                data: function(d) {
                    d.report_type = $('#validationReportTypeFilter').val();
                    d.status = $('#validationStatusFilter').val();
                    d.period_start = $('#validationPeriodStartFilter').val();
                    d.period_end = $('#validationPeriodEndFilter').val();
                }
            },
            columns: [{
                    data: 'report_type',
                    name: 'report_type',
                    width: '80px'
                },
                {
                    data: 'period',
                    name: 'period',
                    width: '150px'
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'totals',
                    name: 'totals',
                    width: '100px',
                    className: 'text-end'
                },
                {
                    data: 'status',
                    name: 'status',
                    width: '150px'
                },
                {
                    data: 'accountant',
                    name: 'accountant',
                    width: '120px'
                },
                {
                    data: 'management',
                    name: 'management',
                    width: '120px'
                },
                {
                    data: 'creator_name',
                    name: 'creator.name',
                    width: '100px'
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    width: '120px'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    width: '150px'
                }
            ],
            order: [
                [0, 'desc']
            ],
            pageLength: 25,
            responsive: true,
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });

        // Filter change handlers
        $('#validationReportTypeFilter, #validationStatusFilter, #validationPeriodStartFilter, #validationPeriodEndFilter').on('change', function() {
            validationTable.ajax.reload();
        });

        // Clear filters
        $('#clearValidationFilters').on('click', function() {
            $('#validationReportTypeFilter, #validationStatusFilter').val('');
            $('#validationPeriodStartFilter, #validationPeriodEndFilter').val('');
            validationTable.ajax.reload();
        });

        // Set default dates for create modal (current month)
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

        $('#validationPeriodStart').val(firstDay.toISOString().split('T')[0]);
        $('#validationPeriodEnd').val(lastDay.toISOString().split('T')[0]);

        // Reset preview when modal opens
        $('#createValidationModal').on('show.bs.modal', function() {
            $('#validationPreview').hide();
            $('#previewData').html('');
        });

        // Preview validation
        $('#previewValidationBtn').on('click', function() {
            const reportType = $('#validationReportType').val();
            const periodStart = $('#validationPeriodStart').val();
            const periodEnd = $('#validationPeriodEnd').val();

            if (!reportType || !periodStart || !periodEnd) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Information',
                    text: 'Please fill all required fields'
                });
                return;
            }

            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Loading...');

            $.ajax({
                url: "/validations/preview",
                type: 'GET',
                data: {
                    report_type: reportType,
                    period_start: periodStart,
                    period_end: periodEnd
                },
                success: function(response) {
                    if (response.status) {
                        $('#validationPreview').slideDown();
                        $('#previewData').html(response.html);
                    } else {
                        Swal.fire('Info', response.message || 'No data found', 'info');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Failed to load preview', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="ri-eye-line me-1"></i> Preview');
                }
            });
        });

        // Create validation form
        $('#createValidationForm').on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Creating Validation Report...',
                text: 'Please wait while we process the data',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "/validations/create",
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.close();
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            $('#createValidationModal').modal('hide');
                            $('#createValidationForm')[0].reset();
                            $('#validationPreview').hide();
                            validationTable.ajax.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    const errorMsg = xhr.responseJSON?.message || 'Failed to create validation report';
                    Swal.fire('Error!', errorMsg, 'error');
                }
            });
        });

        // Edit validation form
        $('#editValidationForm').on('submit', function(e) {
            e.preventDefault();
            const validationId = $('#editValidationId').val();

            Swal.fire({
                title: 'Updating...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "/validations/" + validationId,
                type: 'PUT',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.close();
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            $('#editValidationModal').modal('hide');
                            validationTable.ajax.reload();
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to update', 'error');
                }
            });
        });
    });

    // View validation
    function viewValidation(id) {
        $('#validationModalBody').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading validation report...</p>
            </div>
        `);

        $('#viewValidationModal').modal('show');

        $.ajax({
            url: "/validations/view/" + id,
            type: 'GET',
            dataType: 'html',
            success: function(html) {
                $('#validationModalBody').html(html);
            },
            error: function(xhr) {
                $('#validationModalBody').html(`
                    <div class="alert alert-danger">
                        <i class="ri-error-warning-line me-2"></i>
                        Failed to load validation report: ${xhr.responseJSON?.message || 'Unknown error'}
                    </div>
                `);
            }
        });
    }

    // Edit validation
    function editValidation(id) {
        $.ajax({
            url: "/validations/" + id,
            type: 'GET',
            success: function(response) {
                if (response.status && response.can_edit) {
                    const validation = response.validation;
                    $('#editValidationId').val(id);
                    $('#editValidationTitle').val(validation.title || '');
                    $('#editValidationPeriodStart').val(validation.period_start.split(' ')[0]);
                    $('#editValidationPeriodEnd').val(validation.period_end.split(' ')[0]);
                    $('#editValidationModal').modal('show');
                } else {
                    Swal.fire('Error', 'You cannot edit this validation report', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Failed to load validation data', 'error');
            }
        });
    }

    // Delete validation
    function deleteValidation(id) {
        Swal.fire({
            title: 'Delete Validation Report?',
            text: 'This action cannot be undone. All associated data will be permanently deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "/validations/" + id,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                $('#validationsTable').DataTable().ajax.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete', 'error');
                    }
                });
            }
        });
    }

    // Submit validation (approve/reject)
    // Submit validation (approve/reject)
    function submitValidation(action, validationId) {
        console.log('submitValidation called with:', action, validationId);

        const form = document.getElementById('validationDetailForm');

        if (!form) {
            console.error('Form not found!');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Validation form not found'
            });
            return;
        }

        const formData = new FormData(form);
        formData.append('action', action);

        // Debug: Log all form data
        console.log('Form data entries:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        // Validation checks
        const notes = formData.get('notes');

        if (action === 'reject' && (!notes || notes.trim() === '')) {
            Swal.fire({
                icon: 'error',
                title: 'Notes Required',
                text: 'You must provide notes when rejecting a validation report'
            });
            return;
        }

        // Check if any items are rejected
        let hasRejectedItems = false;
        let missingNotes = false;
        let missingNotesItemId = null;

        const itemSelects = form.querySelectorAll('select[name^="items["]');
        console.log('Found item selects:', itemSelects.length);

        itemSelects.forEach(select => {
            console.log('Item status:', select.name, '=', select.value);

            if (select.value === 'rejected') {
                hasRejectedItems = true;
                const itemId = select.name.match(/\d+/)[0];
                const notesField = document.querySelector(`textarea[name="items[${itemId}][notes]"]`);

                console.log('Rejected item found:', itemId, 'Notes field:', notesField);

                if (!notesField || !notesField.value.trim()) {
                    missingNotes = true;
                    missingNotesItemId = itemId;
                }
            }
        });

        // Check for missing notes on rejected items
        if (missingNotes) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Item Notes',
                text: `Please provide notes for rejected item #${missingNotesItemId}`
            });
            return;
        }

        if (hasRejectedItems && action === 'approve') {
            Swal.fire({
                icon: 'error',
                title: 'Cannot Approve',
                text: 'You cannot approve when some items are rejected. Please reject the entire report or approve all items.'
            });
            return;
        }

        // Confirmation
        Swal.fire({
            title: `${action === 'approve' ? 'Approve' : 'Reject'} Validation?`,
            text: `Are you sure you want to ${action} this validation report?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: action === 'approve' ? '#28a745' : '#dc3545',
            confirmButtonText: `Yes, ${action}!`,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                console.log('Sending AJAX request to:', "/validations/" + validationId + "/submit");

                $.ajax({
                    url: "/validations/" + validationId + "/submit",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log('Success response:', response);
                        Swal.close();

                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                $('#viewValidationModal').modal('hide');

                                // Reload the DataTable if it exists
                                const table = $('#validationsTable').DataTable();
                                if (table) {
                                    table.ajax.reload(null, false); // false = stay on current page
                                }
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        console.error('Response:', xhr.responseText);
                        console.error('Status Code:', xhr.status);

                        Swal.close();

                        let errorMessage = 'Something went wrong';

                        if (xhr.responseJSON) {
                            errorMessage = xhr.responseJSON.message || errorMessage;
                            console.error('Error details:', xhr.responseJSON);
                        } else if (xhr.responseText) {
                            errorMessage = xhr.responseText;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMessage,
                            footer: `Status: ${xhr.status}`
                        });
                    }
                });
            }
        });
    }

    // Show resend modal
    function showResendModal(validationId) {
        Swal.fire({
            title: 'Resend for Review',
            html: `
                <div class="text-start">
                    <p>Select who should review this validation report:</p>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="resend_to" id="resendAccountant" value="accountant" checked>
                        <label class="form-check-label" for="resendAccountant">
                            Resend to Accountant
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="resend_to" id="resendManagement" value="management">
                        <label class="form-check-label" for="resendManagement">
                            Resend to Management
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="unlockReport">
                        <label class="form-check-label" for="unlockReport">
                            Unlock report for editing
                        </label>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Resend',
            preConfirm: () => {
                const resendTo = document.querySelector('input[name="resend_to"]:checked').value;
                const unlock = document.getElementById('unlockReport').checked;
                return {
                    resendTo,
                    unlock
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/validations/" + validationId + "/resend",
                    type: 'POST',
                    data: {
                        resend_to: result.value.resendTo,
                        unlock: result.value.unlock
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                timer: 2000
                            }).then(() => {
                                $('#viewValidationModal').modal('hide');
                                $('#validationsTable').DataTable().ajax.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to resend', 'error');
                    }
                });
            }
        });
    }

    // Helper function to format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }
</script>

<!-- Distributions Module JavaScript -->
<script>
    // Distributions Module JavaScript
    $(document).ready(function() {
        let currentDistributionView = 'validated';
        let validatedTable = null;
        let distributableTable = null;
        let settlementsTable = null;
        let associatesList = [];

        // Initialize tables
        function initializeValidatedTable() {
            validatedTable = $('#validatedIncomesTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                ajax: {
                    url: "/validated-incomes",
                    data: function(d) {
                        d.concept = $('#distributionConceptFilter').val();
                        d.period_start = $('#distributionPeriodStart').val();
                        d.period_end = $('#distributionPeriodEnd').val();
                    }
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        width: '50px'
                    },
                    {
                        data: 'company',
                        name: 'company'
                    },
                    {
                        data: 'commercial_name',
                        name: 'commercial_name'
                    },
                    {
                        data: 'concept',
                        name: 'concept'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'invoice_date',
                        name: 'invoice_date'
                    },
                    {
                        data: 'rc_no',
                        name: 'rc_no'
                    },
                    {
                        data: 'rc_date',
                        name: 'rc_date'
                    },
                    {
                        data: 'base_value',
                        name: 'base_value'
                    },
                    {
                        data: 'vat',
                        name: 'vat'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    }
                ],
                drawCallback: function(settings) {
                    updateConceptTotals(settings.json?.concept_totals || []);
                    updateSelectAllCheckbox();
                }
            });
        }

        function initializeDistributableTable() {
            distributableTable = $('#distributableIncomesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/distributions/data",
                    data: function(d) {
                        d.status = 'distributable';
                        d.concept = $('#distributionConceptFilter').val();
                        d.period_start = $('#distributionPeriodStart').val();
                        d.period_end = $('#distributionPeriodEnd').val();
                    }
                },
                columns: [{
                        data: 'distribution_no',
                        name: 'distribution_no'
                    },
                    {
                        data: 'origin',
                        name: 'origin'
                    },
                    {
                        data: 'concept',
                        name: 'concept'
                    },
                    {
                        data: 'distribution_date',
                        name: 'distribution_date'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'rc_no',
                        name: 'rc_no'
                    },
                    {
                        data: 'base_value',
                        name: 'base_value'
                    },
                    {
                        data: 'vat',
                        name: 'vat'
                    },
                    {
                        data: 'associate_subtotal',
                        name: 'associate_subtotal'
                    },
                    {
                        data: 'admin_subtotal',
                        name: 'admin_subtotal'
                    },
                    {
                        data: 'admin_vat',
                        name: 'admin_vat'
                    },
                    {
                        data: 'admin_total',
                        name: 'admin_total'
                    },
                    {
                        data: 'total_to_pay',
                        name: 'total_to_pay'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        }

        function initializeSettlementsTable(status = null) {
            settlementsTable = $('#settlementsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/settlements/data",
                    data: function(d) {
                        // Pass the current view status
                        d.status = status || (currentDistributionView === 'paid' ? 'paid' : 'settled');
                        d.concept = $('#distributionConceptFilter').val();
                        d.period_start = $('#distributionPeriodStart').val();
                        d.period_end = $('#distributionPeriodEnd').val();
                        d.payment_status = $('#settlementPaymentFilter').val();
                        d.associate_id = $('#settlementAssociateFilter').val();
                    }
                },
                columns: [{
                        data: 'settlement_no',
                        name: 'settlement_no'
                    },
                    {
                        data: 'origin',
                        name: 'origin'
                    },
                    {
                        data: 'concept',
                        name: 'concept'
                    },
                    {
                        data: 'income_month',
                        name: 'income_month'
                    },
                    {
                        data: 'period_covered',
                        name: 'period_covered'
                    },
                    {
                        data: 'distribution_formula',
                        name: 'distribution_formula'
                    },
                    {
                        data: 'total_to_distribute',
                        name: 'total_to_distribute'
                    },
                    {
                        data: 'amount_to_pay',
                        name: 'amount_to_pay'
                    },
                    {
                        data: 'distribution_type',
                        name: 'distribution_type'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                drawCallback: function(settings) {
                    // Update view title based on current status
                    updateSettlementsViewTitle();
                }
            });
        }

        // View switcher
        $('#distributionViewFilter').on('change', function() {
            const view = $(this).val();
            currentDistributionView = view;

            // Hide all sections
            $('#validatedIncomesSection, #distributableIncomesSection, #settlementsSection').hide();

            // Show selected section
            if (view === 'validated') {
                $('#validatedIncomesSection').show();
                if (!validatedTable) initializeValidatedTable();
            } else if (view === 'distributable') {
                $('#distributableIncomesSection').show();
                if (!distributableTable) initializeDistributableTable();
            } else if (view === 'settled' || view === 'paid') {
                $('#settlementsSection').show();

                // Destroy existing table if it exists
                if (settlementsTable) {
                    settlementsTable.destroy();
                    settlementsTable = null;
                }

                // Initialize with correct status
                initializeSettlementsTable(view === 'paid' ? 'paid' : 'settled');
            }
        });

        // Load associates list
        function loadAssociates() {
            $.get("/associates/list", function(data) {
                associatesList = data;
                let options = '<option value="">All Associates</option>';
                data.forEach(associate => {
                    options += `<option value="${associate.id}">${associate.name}</option>`;
                });
                $('#settlementAssociateFilter').html(options);
            });
        }

        // Initialize on page load
        initializeValidatedTable();
        loadAssociates();

        // Update settlements view title
        function updateSettlementsViewTitle() {
            const title = $('#settlementsSection .card-title');
            if (currentDistributionView === 'paid') {
                title.text('Paid Settlements');
            } else {
                title.text('Settlements');
            }
        }

        // Filter change handlers
        $('#distributionConceptFilter, #distributionPeriodStart, #distributionPeriodEnd').on('change keyup', function() {
            reloadCurrentTable();
        });

        $('#settlementPaymentFilter').on('change', function() {
            const paymentStatus = $(this).val();

            if (currentDistributionView === 'paid' && paymentStatus === 'pending') {
                // If viewing paid settlements and selecting "Not Paid", switch to settled view
                $('#distributionViewFilter').val('settled').trigger('change');
                return;
            } else if (currentDistributionView === 'settled' && paymentStatus === 'paid') {
                // If viewing settlements and selecting "Paid", switch to paid view
                $('#distributionViewFilter').val('paid').trigger('change');
                return;
            }

            // Reload the settlements table
            if (settlementsTable) {
                settlementsTable.ajax.reload();
            }
        });

        $('#settlementAssociateFilter').on('change', function() {
            if (settlementsTable) {
                settlementsTable.ajax.reload();
            }
        });

        $('#clearDistributionFilters').on('click', function() {
            $('#distributionConceptFilter, #distributionPeriodStart, #distributionPeriodEnd').val('');
            $('#settlementPaymentFilter, #settlementAssociateFilter').val('');
            reloadCurrentTable();
        });

        function reloadCurrentTable() {
            if (currentDistributionView === 'validated' && validatedTable) {
                validatedTable.ajax.reload();
            } else if (currentDistributionView === 'distributable' && distributableTable) {
                distributableTable.ajax.reload();
            } else if ((currentDistributionView === 'settled' || currentDistributionView === 'paid') && settlementsTable) {
                settlementsTable.ajax.reload();
            }
        }

        // Select All functionality
        $('#selectAllHeader').on('click', function() {
            const isChecked = $(this).prop('checked');
            $('.validated-income-checkbox').prop('checked', isChecked);
            updateConceptTotalsFromSelection();
        });

        $('#selectAllValidated').on('click', function() {
            const isChecked = $(this).prop('checked');
            $('.validated-income-checkbox').prop('checked', isChecked);
            updateConceptTotalsFromSelection();
        });

        $(document).on('change', '.validated-income-checkbox', function() {
            updateSelectAllCheckbox();
            updateConceptTotalsFromSelection();
        });

        function updateSelectAllCheckbox() {
            const totalCheckboxes = $('.validated-income-checkbox').length;
            const checkedCheckboxes = $('.validated-income-checkbox:checked').length;
            $('#selectAllHeader').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
            $('#selectAllValidated').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
        }

        // Concept totals
        function updateConceptTotals(conceptTotals) {
            let html = '';
            let totalBase = 0,
                totalVat = 0,
                totalAmount = 0;

            conceptTotals.forEach(concept => {
                totalBase += parseFloat(concept.total_amount) || 0;
                totalAmount += parseFloat(concept.total_amount) || 0;

                html += `
                    <tr>
                        <td style="width: 35%;"><strong>${concept.concept || 'Uncategorized'}</strong></td>
                        <td>Base Value: ${formatCurrency(concept.total_amount)}</td>
                        <td>Items: ${concept.item_count}</td>
                    </tr>
                `;
            });

            html += `
                <tr class="border-top fw-bold">
                    <td>Total Values to Distribute</td>
                    <td>Base Value: ${formatCurrency(totalBase)}</td>
                    <td>Total Amount: ${formatCurrency(totalAmount)}</td>
                </tr>
            `;

            $('#conceptTotalsTable tbody').html(html);
        }

        function updateConceptTotalsFromSelection() {
            let conceptTotals = {};
            let totalBase = 0,
                totalVat = 0,
                totalAmount = 0;

            $('.validated-income-checkbox:checked').each(function() {
                const concept = $(this).data('concept') || 'Uncategorized';
                const amount = parseFloat($(this).data('amount')) || 0;

                if (!conceptTotals[concept]) {
                    conceptTotals[concept] = {
                        concept: concept,
                        total_amount: 0,
                        item_count: 0
                    };
                }

                conceptTotals[concept].total_amount += amount;
                conceptTotals[concept].item_count += 1;
                totalAmount += amount;
            });

            const totalsArray = Object.values(conceptTotals);
            updateConceptTotals(totalsArray);
        }

        // Distribute selected items
        $('#distributeSelected').on('click', function() {
            const selectedIds = [];
            $('.validated-income-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                Swal.fire('Error', 'Please select at least one item to distribute', 'error');
                return;
            }

            Swal.fire({
                title: 'Confirm Distribution',
                html: `
                    <p>You are about to distribute ${selectedIds.length} selected items.</p>
                    <div class="mb-3">
                        <label class="form-label">Distribution Date</label>
                        <input type="date" class="form-control" id="distributionDate" value="${new Date().toISOString().split('T')[0]}">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Distribute',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    const distributionDate = $('#distributionDate').val();
                    if (!distributionDate) {
                        Swal.showValidationMessage('Please select a distribution date');
                        return false;
                    }

                    return $.ajax({
                            url: "/distributions/create",
                            type: 'POST',
                            data: {
                                item_ids: selectedIds,
                                distribution_date: distributionDate
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        }).then(response => response)
                        .catch(error => {
                            Swal.showValidationMessage(error.responseJSON?.message || 'Error');
                        });
                }
            }).then((result) => {
                if (result.isConfirmed && result.value.status) {
                    Swal.fire('Success', result.value.message, 'success');
                    // Clear selection
                    $('.validated-income-checkbox').prop('checked', false);
                    updateSelectAllCheckbox();
                    // Reload tables
                    if (validatedTable) validatedTable.ajax.reload();
                    // Switch to distributable view
                    $('#distributionViewFilter').val('distributable').trigger('change');
                }
            });
        });

        // Settlement modal
        window.openSettlementModal = function(distributionId) {
            // Get distribution details
            $.get(`/distributions/${distributionId}`, function(response) {
                if (response.status) {
                    const dist = response.distribution;

                    $('#distributionId').val(dist.id);
                    $('#settlementOrigin').val(dist.origin || 'Accounting Department');
                    $('#settlementConcept').val(dist.concept || '');
                    $('#amountToDistributeDisplay').text(formatCurrency(dist.total_to_pay));
                    $('#amountToPayDisplay').text(formatCurrency(dist.total_to_pay));
                    $('#totalToDistribute').val(formatCurrency(dist.total_to_pay));

                    // Set default dates
                    const today = new Date();
                    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                    $('#incomeMonthStart').val(firstDay.toISOString().split('T')[0]);

                    $('#settlementModalTitle').text(`Settle Distribution: ${dist.distribution_no}`);
                    $('#settlementModal').modal('show');
                }
            }).fail(function() {
                Swal.fire('Error', 'Failed to load distribution data', 'error');
            });
        };

        // Distribution type change
        $('input[name="distribution_type"]').on('change', function() {
            if ($(this).val() === 'manual') {
                $('#manualDistributionSection').slideDown();
                loadAssociatesForManual();
            } else {
                $('#manualDistributionSection').slideUp();
            }
        });

        function loadAssociatesForManual() {
            // Clear existing rows
            $('#associatesContainer').html('');

            // Add first row
            addAssociateRow();
        }

        window.addAssociateRow = function() {
            const rowCount = $('#associatesContainer .associate-row').length;
            const rowId = rowCount + 1;

            let associateOptions = '<option value="">Select Associate...</option>';
            associatesList.forEach(associate => {
                associateOptions += `<option value="${associate.id}">${associate.name}</option>`;
            });

            const rowHtml = `
                <div class="row associate-row mb-2" id="associateRow${rowId}">
                    <div class="col-md-4">
                        <select class="form-control form-control-sm associate-select" data-row="${rowId}" onchange="updateAssociateCalculation(${rowId})">
                            ${associateOptions}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control form-control-sm distribution-type-select" data-row="${rowId}" onchange="updateAssociateCalculation(${rowId})">
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm value-input" data-row="${rowId}" 
                            placeholder="Value" oninput="updateAssociateCalculation(${rowId})">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control form-control-sm calculated-amount" 
                            data-row="${rowId}" readonly placeholder="Calculated">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeAssociateRow(${rowId})">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
            `;

            $('#associatesContainer').append(rowHtml);
            updateTotals();
        };

        window.removeAssociateRow = function(rowId) {
            $(`#associateRow${rowId}`).remove();
            updateTotals();
        };

        window.updateAssociateCalculation = function(rowId) {
            const type = $(`.distribution-type-select[data-row="${rowId}"]`).val();
            const value = parseFloat($(`.value-input[data-row="${rowId}"]`).val()) || 0;
            const totalToDistribute = parseFloat($('#totalToDistribute').val().replace(/[^0-9.-]+/g, '')) || 0;

            let calculatedAmount = 0;
            if (type === 'percentage') {
                calculatedAmount = (totalToDistribute * value) / 100;
            } else {
                calculatedAmount = value;
            }

            $(`.calculated-amount[data-row="${rowId}"]`).val(formatCurrency(calculatedAmount));
            updateTotals();
        };

        function updateTotals() {
            let totalPercentage = 0;
            let totalFixed = 0;
            let totalCalculated = 0;

            $('.associate-row').each(function() {
                const type = $(this).find('.distribution-type-select').val();
                const value = parseFloat($(this).find('.value-input').val()) || 0;
                const calculated = parseFloat($(this).find('.calculated-amount').val().replace(/[^0-9.-]+/g, '')) || 0;

                if (type === 'percentage') {
                    totalPercentage += value;
                } else {
                    totalFixed += value;
                }

                totalCalculated += calculated;
            });

            $('#totalPercentage').val(totalPercentage + '%');
            $('#totalFixed').val(formatCurrency(totalFixed));

            // Validate totals
            const totalToDistribute = parseFloat($('#totalToDistribute').val().replace(/[^0-9.-]+/g, '')) || 0;
            const difference = Math.abs(totalCalculated - totalToDistribute);

            if (difference > 0.01) {
                $('#totalPercentage').addClass('is-invalid');
                $('#totalFixed').addClass('is-invalid');
            } else {
                $('#totalPercentage').removeClass('is-invalid');
                $('#totalFixed').removeClass('is-invalid');
            }
        }

        // Settlement form submission
        $('#settlementForm').on('submit', function(e) {
            e.preventDefault();

            const distributionType = $('input[name="distribution_type"]:checked').val();
            const associates = [];
            let validationError = false;

            if (distributionType === 'manual') {
                $('.associate-row').each(function() {
                    const associateId = $(this).find('.associate-select').val();
                    const type = $(this).find('.distribution-type-select').val();
                    const value = parseFloat($(this).find('.value-input').val()) || 0;

                    if (!associateId) {
                        Swal.fire('Error', 'Please select an associate for all rows', 'error');
                        validationError = true;
                        return false;
                    }

                    if (value <= 0) {
                        Swal.fire('Error', 'Please enter a valid value for all associates', 'error');
                        validationError = true;
                        return false;
                    }

                    associates.push({
                        id: associateId,
                        type: type,
                        value: value
                    });
                });

                if (validationError) return;

                // Validate percentage totals
                const hasPercentages = associates.some(a => a.type === 'percentage');
                if (hasPercentages) {
                    const totalPercentage = associates
                        .filter(a => a.type === 'percentage')
                        .reduce((sum, a) => sum + a.value, 0);

                    if (Math.abs(totalPercentage - 100) > 0.01) {
                        Swal.fire('Error', 'Total percentage must equal 100%', 'error');
                        return;
                    }
                }
            }

            // Create form data
            const formData = new FormData(this);

            // Add associates as array if manual distribution
            if (distributionType === 'manual') {
                // First, remove any existing associates data
                for (let key of formData.keys()) {
                    if (key.startsWith('associates')) {
                        formData.delete(key);
                    }
                }

                // Add associates as array entries
                associates.forEach((associate, index) => {
                    formData.append(`associates[${index}][id]`, associate.id);
                    formData.append(`associates[${index}][type]`, associate.type);
                    formData.append(`associates[${index}][value]`, associate.value);
                });
            }

            Swal.fire({
                title: 'Creating Settlement...',
                text: 'Please wait',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "/settlements/create",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.close();
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            $('#settlementModal').modal('hide');
                            // Reset form
                            $('#settlementForm')[0].reset();
                            $('#associatesContainer').html('');
                            // Reload tables
                            if (distributableTable) distributableTable.ajax.reload();
                            // Switch to settlements view
                            $('#distributionViewFilter').val('settled').trigger('change');
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    const errorMsg = xhr.responseJSON?.message || 'Failed to create settlement';
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        });

        // View settlement details
        window.viewSettlementDetails = function(settlementId) {
            $('#settlementDetailsBody').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading settlement details...</p>
                </div>
            `);

            $('#settlementDetailsModal').modal('show');

            $.ajax({
                url: "/settlements/" + settlementId,
                type: 'GET',
                success: function(response) {
                    if (response.status) {
                        const settlement = response.settlement;
                        const associates = response.associates;

                        let html = `
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Settlement Information</h6>
                                    <table class="table table-sm">
                                        <tr><td><strong>Settlement No:</strong></td><td>${settlement.settlement_no}</td></tr>
                                        <tr><td><strong>Origin:</strong></td><td>${settlement.origin}</td></tr>
                                        <tr><td><strong>Concept:</strong></td><td>${settlement.concept}</td></tr>
                                        <tr><td><strong>Income Month:</strong></td><td>${settlement.income_month_start ? new Date(settlement.income_month_start).toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) : 'N/A'}</td></tr>
                                        <tr><td><strong>Period Covered:</strong></td><td>${settlement.period_covered || 'N/A'}</td></tr>
                                        <tr><td><strong>Distribution Formula:</strong></td><td>${settlement.distribution_formula || 'N/A'}</td></tr>
                                        <tr><td><strong>Distribution Type:</strong></td><td><span class="badge bg-${settlement.distribution_type === 'ownership' ? 'primary' : 'info'}">${settlement.distribution_type}</span></td></tr>
                                        <tr><td><strong>Status:</strong></td><td><span class="badge bg-${settlement.status === 'paid' ? 'success' : 'info'}">${settlement.status}</span></td></tr>
                                        <tr><td><strong>Created:</strong></td><td>${new Date(settlement.created_at).toLocaleDateString()}</td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Amounts</h6>
                                    <table class="table table-sm">
                                        <tr class="table-primary"><td><strong>Total to Distribute:</strong></td><td class="text-end">${formatCurrency(settlement.total_to_distribute)}</td></tr>
                                        <tr class="table-success"><td><strong>Amount to Pay:</strong></td><td class="text-end">${formatCurrency(settlement.amount_to_pay)}</td></tr>
                                        ${settlement.paid_date ? `<tr><td><strong>Paid Date:</strong></td><td class="text-end">${new Date(settlement.paid_date).toLocaleDateString()}</td></tr>` : ''}
                                    </table>
                                </div>
                            </div>
                            
                            <h6>Associates Distribution</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Associate</th>
                                            <th>Email</th>
                                            <th>Percentage</th>
                                            <th>Fixed Amount</th>
                                            <th>Calculated Amount</th>
                                            <th>Status</th>
                                            <th>Paid Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;

                        associates.forEach(associate => {
                            html += `
                                <tr>
                                    <td>${associate.name}</td>
                                    <td>${associate.email}</td>
                                    <td>${associate.percentage ? associate.percentage + '%' : '-'}</td>
                                    <td>${associate.fixed_amount ? formatCurrency(associate.fixed_amount) : '-'}</td>
                                    <td>${formatCurrency(associate.calculated_amount)}</td>
                                    <td><span class="badge bg-${associate.status === 'paid' ? 'success' : 'warning'}">${associate.status}</span></td>
                                    <td>${associate.paid_date ? new Date(associate.paid_date).toLocaleDateString() : '-'}</td>
                                </tr>
                            `;
                        });

                        html += `
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-3">
                                <button type="button" class="btn btn-primary" onclick="downloadSettlementReport(${settlementId})">
                                    <i class="ri-download-line me-1"></i> Download Report
                                </button>
                                ${settlement.status === 'settled' ? `
                                    <button type="button" class="btn btn-success" onclick="markSettlementPaid(${settlementId})">
                                        <i class="ri-check-line me-1"></i> Mark as Paid
                                    </button>
                                ` : ''}
                            </div>
                        `;

                        $('#settlementDetailsBody').html(html);
                    }
                },
                error: function(xhr) {
                    $('#settlementDetailsBody').html(`
                        <div class="alert alert-danger">
                            <i class="ri-error-warning-line me-2"></i>
                            Failed to load settlement details: ${xhr.responseJSON?.message || 'Unknown error'}
                        </div>
                    `);
                }
            });
        };

        // Mark settlement as paid
        window.markSettlementPaid = function(settlementId) {
            $('#settlementIdToMarkPaid').val(settlementId);
            $('#markPaidModal').modal('show');
        };

        $('#markPaidForm').on('submit', function(e) {
            e.preventDefault();

            const settlementId = $('#settlementIdToMarkPaid').val();

            Swal.fire({
                title: 'Mark as Paid?',
                text: 'This will mark the settlement and all associate payments as paid.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, mark as paid',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                            url: "/settlements/" + settlementId + "/mark-paid",
                            type: 'POST',
                            data: $(this).serialize(),
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        }).then(response => response)
                        .catch(error => {
                            Swal.showValidationMessage(error.responseJSON?.message || 'Error');
                        });
                }
            }).then((result) => {
                if (result.isConfirmed && result.value.status) {
                    Swal.fire('Success', result.value.message, 'success');
                    $('#markPaidModal').modal('hide');
                    $('#markPaidForm')[0].reset();

                    // Reload tables
                    if (settlementsTable) settlementsTable.ajax.reload();
                    $('#settlementDetailsModal').modal('hide');

                    // Switch to paid view if not already there
                    if (currentDistributionView === 'settled') {
                        $('#distributionViewFilter').val('paid').trigger('change');
                    }
                }
            });
        });

        // Download settlement report
        window.downloadSettlementReport = function(settlementId) {
            window.open("/settlements/" + settlementId + "/download", '_blank');
        };

        // Download settlements report
        window.downloadSettlementsReport = function() {
            const status = currentDistributionView === 'paid' ? 'paid' :
                currentDistributionView === 'settled' ? 'settled' :
                $('#settlementPaymentFilter').val();
            const associateId = $('#settlementAssociateFilter').val();
            const concept = $('#distributionConceptFilter').val();
            const periodStart = $('#distributionPeriodStart').val();
            const periodEnd = $('#distributionPeriodEnd').val();

            let url = "/settlements/report/download?";
            if (status) url += "status=" + status + "&";
            if (associateId) url += "associate_id=" + associateId + "&";
            if (concept) url += "concept=" + encodeURIComponent(concept) + "&";
            if (periodStart) url += "period_start=" + periodStart + "&";
            if (periodEnd) url += "period_end=" + periodEnd;

            window.open(url, '_blank');
        };

        // Helper function to format currency
        function formatCurrency(amount) {
            if (typeof amount !== 'number') {
                amount = parseFloat(amount) || 0;
            }
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2
            }).format(amount);
        }

        // View settlement associates function
        window.viewSettlementAssociates = function(settlementId) {
            $.ajax({
                url: "/settlements/" + settlementId,
                type: 'GET',
                success: function(response) {
                    if (response.status) {
                        const settlement = response.settlement;
                        const associates = response.associates;

                        let html = `
                            <div class="modal-header">
                                <h5 class="modal-title">Settlement Associates - ${settlement.settlement_no}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="ri-information-line me-2"></i>
                                    Distribution Type: <strong>${settlement.distribution_type}</strong> | 
                                    Total to Distribute: <strong>${formatCurrency(settlement.total_to_distribute)}</strong>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Associate</th>
                                                <th>Email</th>
                                                <th>Percentage</th>
                                                <th>Fixed Amount</th>
                                                <th>Calculated Amount</th>
                                                <th>Status</th>
                                                <th>Paid Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                        `;

                        associates.forEach(associate => {
                            html += `
                                <tr>
                                    <td>${associate.name}</td>
                                    <td>${associate.email}</td>
                                    <td>${associate.percentage ? associate.percentage + '%' : '-'}</td>
                                    <td>${associate.fixed_amount ? formatCurrency(associate.fixed_amount) : '-'}</td>
                                    <td>${formatCurrency(associate.calculated_amount)}</td>
                                    <td><span class="badge bg-${associate.status === 'paid' ? 'success' : 'warning'}">${associate.status}</span></td>
                                    <td>${associate.paid_date ? new Date(associate.paid_date).toLocaleDateString() : '-'}</td>
                                </tr>
                            `;
                        });

                        html += `
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="button" class="btn btn-primary" onclick="downloadSettlementReport(${settlementId})">
                                        <i class="ri-download-line me-1"></i> Download Report
                                    </button>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        `;

                        // Create a new modal for associates
                        const modalId = 'associatesModal' + settlementId;
                        let modalHtml = `
                            <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        ${html}
                                    </div>
                                </div>
                            </div>
                        `;

                        // Remove existing modal if any
                        $('#' + modalId).remove();

                        // Add modal to body
                        $('body').append(modalHtml);

                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById(modalId));
                        modal.show();
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Failed to load associates data', 'error');
                }
            });
        };
    });
</script>

<!-- Portfolio Module JavaScript -->
<script>
    $(document).ready(function() {
        // Auto-load portfolio data when tab is shown
        $('a[href="#portfolio"]').on('shown.bs.tab', function() {
            if (!window.portfolioDataLoaded) {
                loadPortfolioData();
                window.portfolioDataLoaded = true;
            }
        });

        // Filter change handlers
        $('#portfolioPeriodMonth, #portfolioPeriodYear, #portfolioAgingFilter').on('change', function() {
            loadPortfolioData();
        });

        $('#portfolioClientFilter').on('keyup', debounce(function() {
            loadPortfolioData();
        }, 500));

        // Comment form submission
        $('#commentForm').on('submit', function(e) {
            e.preventDefault();
            saveComment();
        });
    });

    // Load portfolio data
    function loadPortfolioData() {
        const periodMonth = $('#portfolioPeriodMonth').val();
        const periodYear = $('#portfolioPeriodYear').val();
        const clientFilter = $('#portfolioClientFilter').val();
        const agingFilter = $('#portfolioAgingFilter').val();

        $('#portfolioDataContainer').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading portfolio data...</p>
            </div>
        `);

        $.ajax({
            url: '/portfolio/data',
            type: 'GET',
            data: {
                period_month: periodMonth,
                period_year: periodYear,
                client_filter: clientFilter,
                aging_filter: agingFilter
            },
            success: function(response) {
                if (response.status) {
                    renderPortfolioData(response.data, response.grand_totals, response.period);
                } else {
                    showError('Failed to load portfolio data');
                }
            },
            error: function(xhr) {
                showError(xhr.responseJSON?.message || 'Error loading portfolio data');
            }
        });
    }

    // Render portfolio data
    function renderPortfolioData(data, grandTotals, period) {
        // Update summary cards
        $('#total_1_30').text(formatCurrency(grandTotals['1_30']));
        $('#total_31_90').text(formatCurrency(grandTotals['31_90']));
        $('#total_90_plus').text(formatCurrency(grandTotals['90_plus']));
        $('#total_ar').text(formatCurrency(grandTotals.total));

        if (data.length === 0) {
            $('#portfolioDataContainer').html(`
                <div class="alert alert-info text-center">
                    <i class="ri-information-line me-2"></i>
                    No outstanding invoices for the selected period
                </div>
            `);
            return;
        }

        let html = '';

        data.forEach(client => {
            html += `
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-0">
                                    <i class="ri-building-2-line me-2"></i>
                                    <strong>${client.client_name}</strong>
                                </h6>
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="badge bg-primary">Total: ${formatCurrency(client.totals.total)}</span>
                                <span class="badge bg-info">1-30: ${formatCurrency(client.totals['1_30'])}</span>
                                <span class="badge bg-warning">31-90: ${formatCurrency(client.totals['31_90'])}</span>
                                <span class="badge bg-danger">90+: ${formatCurrency(client.totals['90_plus'])}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice No.</th>
                                        <th>Invoice Date</th>
                                        <th class="text-end">1-30 Days</th>
                                        <th class="text-end">31-90 Days</th>
                                        <th class="text-end">90+ Days</th>
                                        <th class="text-end">Balance</th>
                                        <th>Comment</th>
                                        <th width="100">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;

            client.invoices.forEach(invoice => {
                const commentBadge = invoice.comment ? getCommentBadge(invoice.comment) : '<span class="badge bg-secondary">No Comment</span>';
                const rowClass = invoice.days_old > 90 ? 'table-danger' : invoice.days_old > 30 ? 'table-warning' : '';
                html += `
                    <tr class="${rowClass}">
                        <td>${invoice.invoice_number}</td>
                        <td>${formatDate(invoice.invoice_date)}</td>
                        <td class="text-end">${invoice.aging_1_30 > 0 ? formatCurrency(invoice.aging_1_30) : '-'}</td>
                        <td class="text-end">${invoice.aging_31_90 > 0 ? formatCurrency(invoice.aging_31_90) : '-'}</td>
                        <td class="text-end">${invoice.aging_90_plus > 0 ? formatCurrency(invoice.aging_90_plus) : '-'}</td>
                        <td class="text-end"><strong>${formatCurrency(invoice.balance)}</strong></td>
                        <td>${commentBadge}</td>
                        <td>
                            ${getActionButtons(client, invoice, period)}
                        </td>
                    </tr>
                `;
            });

            html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        });

        $('#portfolioDataContainer').html(html);
    }

    // Get comment badge HTML
    function getCommentBadge(comment) {
        const statusMap = {
            'pending': {
                color: 'warning',
                icon: 'ri-time-line',
                text: 'Pending'
            },
            'approved': {
                color: 'success',
                icon: 'ri-check-line',
                text: 'Approved'
            },
            'rejected': {
                color: 'danger',
                icon: 'ri-close-line',
                text: 'Rejected'
            }
        };

        const status = statusMap[comment.status] || statusMap.pending;

        return `
            <div class="dropdown">
                <button class="badge bg-${status.color} border-0 type="button" aria-expanded="false">
                    <i class="${status.icon} me-1"></i> ${status.text}
                </button>
            </div>
        `;
    }

    // Get action buttons based on permissions and comment status
    function getActionButtons(client, invoice, period) {
        const hasComment = invoice.comment !== null;
        const canModify = hasComment ? invoice.comment.can_modify : true;
        const canApprove = hasComment ? invoice.comment.can_approve : false;

        let buttons = '<div class="btn-group btn-group-sm">';

        if (canModify) {
            buttons += `
                <button type="button" class="btn btn-soft-primary" 
                    onclick='openCommentModal(${JSON.stringify({
                        client_id: client.client_id,
                        client_name: client.client_name,
                        invoice_id: invoice.invoice_id,
                        invoice_number: invoice.invoice_number,
                        period_month: period.month,
                        period_year: period.year,
                        comment: invoice.comment
                    })})' 
                    title="${hasComment ? 'Edit' : 'Add'} Comment">
                    <iconify-icon icon="solar:chat-round-line-bold"></iconify-icon>    
                </button>
            `;

            if (hasComment) {
                buttons += `
                    <button type="button" class="btn btn-soft-danger" 
                        onclick="deleteComment(${invoice.comment.id})" 
                        title="Delete Comment">
                        <iconify-icon icon="solar:trash-bin-minimalistic-outline"></iconify-icon>   
                    </button>
                `;
            }
        }

        if (canApprove) {
            buttons += `
                <button type="button" class="btn btn-soft-success" 
                    onclick='openApproveModal(${JSON.stringify(invoice.comment)})' 
                    title="Review Comment">
                    <iconify-icon icon="solar:chat-round-check-linear"></iconify-icon>
                </button>
            `;
        }

        if (hasComment && invoice.comment.status === 'approved') {
            buttons += `
                <button type="button" class="btn btn-soft-info" disabled title="Comment Locked">
                    <iconify-icon icon="solar:lock-keyhole-linear"></iconify-icon>
                </button>
            `;
        }

        buttons += '</div>';

        return buttons;
    }

    // Open comment modal
    function openCommentModal(data) {
        $('#commentClientId').val(data.client_id);
        $('#commentInvoiceId').val(data.invoice_id);
        $('#commentPeriodMonth').val(data.period_month);
        $('#commentPeriodYear').val(data.period_year);

        $('#commentClientName').val(data.client_name);
        $('#commentInvoiceNumber').val(data.invoice_number);
        $('#commentText').val(data.comment ? data.comment.text : '');

        $('#commentModalTitle').text(data.comment ? 'Edit Comment' : 'Add Comment');
        $('#commentModal').modal('show');
    }

    // Save comment
    function saveComment() {
        const formData = {
            client_id: $('#commentClientId').val(),
            invoice_id: $('#commentInvoiceId').val(),
            period_month: $('#commentPeriodMonth').val(),
            period_year: $('#commentPeriodYear').val(),
            comment: $('#commentText').val()
        };

        $.ajax({
            url: '/portfolio/comments/store',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        $('#commentModal').modal('hide');
                        $('#commentForm')[0].reset();
                        loadPortfolioData();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to save comment', 'error');
            }
        });
    }

    // Open approve modal
    function openApproveModal(comment) {
        $('#approveCommentId').val(comment.id);
        $('#approveCommentText').text(comment.text);
        $('#approveCommentCreator').val(comment.created_by);
        $('#rejectionReasonGroup').hide();
        $('#rejectionReason').val('');
        $('#approveCommentModal').modal('show');
    }

    // Submit comment approval
    function submitCommentApproval(action) {
        const commentId = $('#approveCommentId').val();

        if (action === 'reject') {
            const reason = $('#rejectionReason').val();
            if (!reason) {
                $('#rejectionReasonGroup').slideDown();
                Swal.fire('Error', 'Please provide a rejection reason', 'error');
                return;
            }
        }

        Swal.fire({
            title: `${action === 'approve' ? 'Approve' : 'Reject'} Comment?`,
            text: action === 'approve' ?
                'This comment will be locked and cannot be modified.' :
                'This comment will be rejected and can be edited.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Yes, ${action}!`,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                        url: `/portfolio/comments/${commentId}/approve`,
                        type: 'POST',
                        data: {
                            action: action,
                            rejection_reason: $('#rejectionReason').val()
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    }).then(response => response)
                    .catch(error => {
                        Swal.showValidationMessage(error.responseJSON?.message || 'Error');
                    });
            }
        }).then((result) => {
            if (result.isConfirmed && result.value.status) {
                Swal.fire('Success', result.value.message, 'success');
                $('#approveCommentModal').modal('hide');
                loadPortfolioData();
            }
        });
    }

    // Delete comment
    function deleteComment(commentId) {
        Swal.fire({
            title: 'Delete Comment?',
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/portfolio/comments/${commentId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire('Deleted!', response.message, 'success');
                            loadPortfolioData();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete', 'error');
                    }
                });
            }
        });
    }

    // Export portfolio
    function exportPortfolio() {
        const periodMonth = $('#portfolioPeriodMonth').val();
        const periodYear = $('#portfolioPeriodYear').val();
        const clientFilter = $('#portfolioClientFilter').val();
        const agingFilter = $('#portfolioAgingFilter').val();

        let url = `/portfolio/export?period_month=${periodMonth}&period_year=${periodYear}`;
        if (clientFilter) url += `&client_filter=${encodeURIComponent(clientFilter)}`;
        if (agingFilter) url += `&aging_filter=${agingFilter}`;

        window.open(url, '_blank');
    }

    // Helper functions
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function showError(message) {
        $('#portfolioDataContainer').html(`
            <div class="alert alert-danger">
                <i class="ri-error-warning-line me-2"></i>
                ${message}
            </div>
        `);
    }
</script>
@stop