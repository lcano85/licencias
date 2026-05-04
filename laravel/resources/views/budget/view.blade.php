@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
<link href="{{ asset('admin/css/historydata.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('admin/css/sidebar-slider.css') }}" rel="stylesheet" type="text/css" />
<style>
    .kv-label{font-weight:600;color:#475569}
    .kv-box{border:1px solid #e5e7eb;border-radius:12px;padding:18px}
    .kv-row{margin-bottom:10px}
</style>
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Budget View') }}</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('budgets') }}" class="btn btn-secondary btn-sm">Back to Budget</a>
                </div>
            </div>

            <div class="card-body">
                <div class="live-preview" style="padding-left: 25px; padding-right: 25px;">
                    {{-- Top summary --}}
                    <div class="row gy-4">
                        <div class="col-xxl-4 col-md-6">
                            <div class="kv-box">
                                <div class="kv-row"><span class="kv-label">Commercial Name:</span><div>{{ $display['commercialName'] }}</div></div>
                                <div class="kv-row"><span class="kv-label">{{ __('Company:') }}</span><div>{{ $display['company'] }}</div></div>
                                <div class="kv-row"><span class="kv-label">{{ __('Licensed Concept:') }}</span><div>{{ $display['licensedConcept'] }}</div></div>
                                <div class="kv-row"><span class="kv-label">{{ __('Licensed Environment:') }}</span><div>{{ $display['licensedEnvironment'] }}</div></div>
                                <div class="kv-row"><span class="kv-label">{{ __('Category:') }}</span><div>{{ $display['category'] }}</div></div>
                                <div class="kv-row"><span class="kv-label">{{ __('Subcategory:') }}</span><div>{{ $display['subcategory'] }}</div></div>
                                <?php /* 
                                    @if($display['license_pdf_url'])
                                        <div class="kv-row"><span class="kv-label">{{ __('License PDF:') }}</span>
                                            <div><a href="{{ $display['license_pdf_url'] }}" target="_blank" class="link-primary">{{ __('View PDF') }}</a></div>
                                        </div>
                                    @endif
                                */ ?>
                            </div>
                        </div>

                        {{-- Period & frequency --}}
                        @php
                            $map = ['1'=>'Monthly', '2'=>'Quarterly', '3'=>'Annual', 'monthly'=>'Monthly', 'quarterly'=>'Quarterly', 'annual'=>'Annual'];
                            $raw = $display['frequency'] ?? null;
                            $frequencyText = $map[is_string($raw) ? strtolower(trim($raw)) : (string)$raw] ?? ($raw ?: 'N/A');
                        @endphp
                        <div class="col-xxl-4 col-md-6">
                            <div class="kv-box">
                                <div class="kv-row"><span class="kv-label">{{ __('Budget Period (Filter):') }}</span><div>{{ $display['budget_period'] }}</div></div>
                                <div class="kv-row"><span class="kv-label">{{ __('Begin:') }}</span><div>{{ $display['begin_period'] }}</div></div>
                                <div class="kv-row"><span class="kv-label">{{ __('Finish:') }}</span><div>{{ $display['finish_period'] }}</div></div>
                                <div class="kv-row"><span class="kv-label">{{ __('Frequency:') }}</span><div>{{ $frequencyText }}</div></div>
                                <div class="kv-row"><span class="kv-label">{{ __('Annual Value:') }}</span><div>{{ $display['annual_value'] }}</div></div>
                                <div class="kv-row"><span class="kv-label">{{ __('Total Months:') }}</span><div>{{ $display['total_months'] }}</div></div>
                                <div class="kv-row"><span class="kv-label">{{ __('Monthly Amount:') }}</span><div>{{ $display['monthly_value'] }}</div></div>
                            </div>
                        </div>

                        {{-- Money & status --}}
                        <div class="col-xxl-4 col-md-12">
                            <div class="kv-box">
                                <div class="kv-row"><span class="kv-label">{{ __('Sub Total:') }}</span><div>{{ $display['subTotal'] }}</div></div>
                                <div class="kv-row"><span class="kv-label">{{ __('VAT:') }}</span><div>{{ $display['vat'] }}</div></div>
                                <div class="kv-row"><span class="kv-label">{{ __('Total:') }}</span><div>{{ $display['total'] }}</div></div>
                                <hr>
                                <div class="kv-row"><span class="kv-label">{{ __('Condition:') }}</span><div>{{ $display['condition'] }}</div></div>
                                <div class="kv-row"><span class="kv-label">{{ __('Status:') }}</span><div>{{ $display['status'] }}</div></div>
                                <div class="kv-row"><span class="kv-label">{{ __('Created By:') }}</span><div>{{ $display['created_by'] }}</div></div>
                                <div class="kv-row"><span class="kv-label">{{ __('Created At:') }}</span><div>{{ $display['created_at'] }}</div></div>
                            </div>
                        </div>
                    </div>

                    {{-- Concept full text --}}
                    <div class="row gy-4 mt-2">
                        <div class="col-12">
                            <div class="kv-box">
                                <span class="kv-label">{{ __('Concept:') }}</span>
                                <div class="mt-2">{{ $display['concept'] }}</div>
                            </div>
                        </div>
                    </div>
                </div> {{-- live-preview --}}
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@stop
