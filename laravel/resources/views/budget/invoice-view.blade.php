@extends('layouts.app') @section('title', $pageTitle) @section('styles')
<link href="{{ asset('admin/css/historydata.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('admin/css/sidebar-slider.css') }}" rel="stylesheet" type="text/css" />
<style>
    .kv-label {
        font-weight: 600;
        color: #475569;
    }
    .kv-box {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 18px;
    }
    .kv-row {
        margin-bottom: 10px;
    }
</style>
@stop @section('content')
<div class="loader--ripple" style="display: none;">
    <div></div>
    <div></div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Register Invoice View') }}</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('budgets') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
            </div>

            <div class="card-body">
                <div class="live-preview" style="padding-left: 25px; padding-right: 25px;">
                    {{-- Invoice header --}}
                    <div class="row gy-4">
                        <div class="col-xxl-4 col-md-6">
                            <div class="kv-box">
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Invoice Number:') }}</span>
                                    <div>{{ $display['invoiceNumber'] }}</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Invoice Date:') }}</span>
                                    <div>{{ $display['invoiceDate'] }}</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Created By:') }}</span>
                                    <div>{{ $display['created_by'] }}</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Created At:') }}</span>
                                    <div>{{ $display['created_at'] }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-4 col-md-6">
                            <div class="kv-box">
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Paid Period:') }}</span>
                                    <div>{{ $display['paidPeriod'] }} ({{ $display['periodPaid'] }})</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Criterion:') }}</span>
                                    <div>{{ $display['criterion'] }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-4 col-md-12">
                            <div class="kv-box">
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Sub Total:') }}</span>
                                    <div>{{ $display['subTotal'] }}</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('VAT Rate:') }}</span>
                                    <div>{{ $display['vat_rate'] }}</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('VAT Amount:') }}</span>
                                    <div>{{ $display['vat_amount'] }}</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Total:') }}</span>
                                    <div>{{ $display['total'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Client / License --}}
                    <div class="row gy-4 mt-2">
                        <div class="col-xxl-6 col-md-6">
                            <div class="kv-box">
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Commercial Name:') }}</span>
                                    <div>{{ $display['commercialName'] }}</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Company:') }}</span>
                                    <div>{{ $display['company'] }}</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('User Type:') }}</span>
                                    <div>{{ $display['userType'] }}</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Licensed Concept:') }}</span>
                                    <div>{{ $display['licensedConcept'] }}</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Licensed Environment:') }}</span>
                                    <div>{{ $display['licensedEnvironment'] }}</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Category:') }}</span>
                                    <div>{{ $display['category'] }}</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Subcategory:') }}</span>
                                    <div>{{ $display['subcategory'] }}</div>
                                </div>
                                <?php /* 
                                    @if($display['license_pdf_url'])
                                    <div class="kv-row">
                                        <span class="kv-label">{{ __('License PDF:') }}</span>
                                        <div><a class="link-primary" target="_blank" href="{{ $display['license_pdf_url'] }}">{{ __('View PDF') }}</a></div>
                                    </div>
                                    @endif
                                */ ?>
                            </div>
                        </div>

                        <div class="col-xxl-6 col-md-6">
                            <div class="kv-box">
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Begin:') }}</span>
                                    <div>{{ $display['begin_period'] }}</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Finish:') }}</span>
                                    <div>{{ $display['finish_period'] }}</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Frequency:') }}</span>
                                    <div>{{ $display['frequency'] }}</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Total Months:') }}</span>
                                    <div>{{ $display['total_months'] }}</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Status:') }}</span>
                                    <div>{{ $display['status'] }}</div>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-label">{{ __('Condition:') }}</span>
                                    <div>{{ $display['condition'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Concept --}}
                    <div class="row gy-4 mt-2">
                        <div class="col-12">
                            <div class="kv-box">
                                <span class="kv-label">{{ __('Concept:') }}</span>
                                <div class="mt-2">{{ $display['concept'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection @section('script') @stop
