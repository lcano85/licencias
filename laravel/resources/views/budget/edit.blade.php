@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
<style>
.number-input, .currency-input {
    text-align: right;
    font-family: 'Courier New', monospace;
    font-weight: 500;
}

.number-input:focus, .currency-input:focus {
    border-color: #405189;
    box-shadow: 0 0 0 0.1rem rgba(64, 81, 137, 0.25);
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
    z-index: 1;
}

.currency-symbol input {
    padding-left: 25px;
}
</style>
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Budget Edit') }}</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form id="budgetEditForm" method="POST" action="{{ route('budget.update', $budget->id) }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="commercialName" class="form-label">Commercial Name</label>
                                <select class="form-control" id="commercialName" name="commercialName" data-choices data-choices-sorting-false placeholder="{{ __('Select Commercial Name...') }}">
                                    <option value="">{{ __('Select Commercial Name...') }}</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ (isset($budget) && $budget->commercialID == $client->id) ? 'selected' : '' }}>
                                            {{ $client->commercialName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('commercialName') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="user_type" class="form-label">{{ __('Licensed Concept') }}</label>
                                <input type="text" class="form-control" name="user_type" id="user_type" readonly
                                       value="{{ $budget->user_type ?? '' }}">
                                @error('user_type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="licensedConcept" class="form-label">{{ __('Licensed Concept (Display)') }}</label>
                                <input type="text" class="form-control" id="licensedConcept" readonly
                                       value="{{ $budget->licensedConcept ?? '' }}">
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="licensedEnvironment" class="form-label">{{ __('Licensed Environment') }}</label>
                                <input type="text" class="form-control" name="licensedEnvironment" id="licensedEnvironment" readonly
                                       value="{{ $budget->licensedEnvironment ?? '' }}">
                                @error('licensedEnvironment') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="category" class="form-label">{{ __('Category') }}</label>
                                <input type="text" class="form-control" id="category" name="category" readonly
                                       value="{{ $budget->category ?? '' }}">
                            </div>
                            <div class="col-xxl-6 col-md-6">
                                <label for="subcategory" class="form-label">{{ __('Sub Category') }}</label>
                                <input type="text" class="form-control" id="subcategory" name="subcategory" readonly
                                       value="{{ $budget->subcategory ?? '' }}">
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="company" class="form-label">{{ __('Company') }}</label>
                                <input type="text" name="company" id="company" class="form-control"
                                       value="{{ $budget->company ?? '' }}">
                                @error('company') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label class="form-label">{{ __('Frequency') }}</label>
                                @php $freq = $budget->billing_frequency ?? 'Monthly'; @endphp
                                <select class="form-control" name="frequency" id="frequency">
                                    <option value="Monthly"   {{ $freq==='Monthly' ? 'selected' : '' }}>{{ __('Monthly') }}</option>
                                    <option value="Quarterly" {{ $freq==='Quarterly' ? 'selected' : '' }}>{{ __('Quarterly') }}</option>
                                    <option value="Annual"    {{ $freq==='Annual' ? 'selected' : '' }}>{{ __('Annual') }}</option>
                                </select>
                                @error('frequency') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        @php
                            $bY = (int)($budget->begin_year ?? now()->year);
                            $bM = (int)($budget->begin_month ?? now()->month);
                            $beginVal = sprintf('%04d-%02d', $bY, $bM);
                            $fY = (int)($budget->finish_year ?? $bY);
                            $fM = (int)($budget->finish_month ?? $bM);
                            $finishVal = sprintf('%04d-%02d', $fY, $fM);
                            
                            // Format numbers for display
                            $annualValueFormatted = $budget->annual_value ? number_format($budget->annual_value, 2, ',', '.') : '';
                            $monthlyValueFormatted = $budget->monthly_value ? number_format($budget->monthly_value, 2, ',', '.') : '';
                            $subTotalFormatted = $budget->subTotal ? number_format($budget->subTotal, 2, ',', '.') : '';
                            $totalFormatted = $budget->total ? number_format($budget->total, 2, ',', '.') : '';
                        @endphp

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-4 col-md-4">
                                <label class="form-label">{{ __('Begin (Month/Year)') }}</label>
                                <input type="month" class="form-control" id="begin" value="{{ $beginVal }}">
                                <input type="hidden" name="begin_month" id="begin_month" value="{{ $bM }}">
                                <input type="hidden" name="begin_year"  id="begin_year"  value="{{ $bY }}">
                            </div>

                            <div class="col-xxl-4 col-md-4">
                                <label class="form-label">{{ __('Finish (Month/Year)') }}</label>
                                <input type="month" class="form-control" id="finish" value="{{ $finishVal }}">
                                <input type="hidden" name="finish_month" id="finish_month" value="{{ $fM }}">
                                <input type="hidden" name="finish_year"  id="finish_year"  value="{{ $fY }}">
                                <small class="text-muted">{{ __('For Monthly, defaults to 12 months from Begin (only if Finish is empty).') }}</small>
                            </div>

                            <div class="col-xxl-4 col-md-4">
                                <label class="form-label">{{ __('Annual Value') }}</label>
                                <div class="currency-symbol">
                                    <input type="text" class="form-control number-input" name="annual_value" id="annual_value"
                                           value="{{ $annualValueFormatted }}" placeholder="1.000,00" data-thousand-separator>
                                </div>
                                @error('annual_value') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-3 col-md-3">
                                <label class="form-label">{{ __('Total Months') }}</label>
                                <input type="number" class="form-control" id="months_total" readonly
                                       value="{{ $budget->total_months ?? '' }}">
                            </div>
                            <div class="col-xxl-3 col-md-3">
                                <label class="form-label">{{ __('Monthly Amount (Auto)') }}</label>
                                <div class="currency-symbol">
                                    <input type="text" class="form-control number-input" id="monthly_amount" readonly
                                           value="{{ $monthlyValueFormatted }}" data-thousand-separator>
                                </div>
                            </div>
                            <div class="col-xxl-3 col-md-3">
                                <label for="subTotal" class="form-label">{{ __('Sub Total *') }}</label>
                                <div class="currency-symbol">
                                    <input type="text" name="subTotal" id="subTotal" class="form-control number-input"
                                           value="{{ $subTotalFormatted }}" required data-thousand-separator>
                                </div>
                                @error('subTotal') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-xxl-3 col-md-3">
                                <label for="vat" class="form-label">{{ __('VAT % *') }}</label>
                                <input type="number" step="0.01" name="vat" id="vat" class="form-control"
                                       value="{{ $budget->vat ?? '12' }}" required>
                                @error('vat') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-3 col-md-3">
                                <label for="total" class="form-label">{{ __('Total *') }}</label>
                                <div class="currency-symbol">
                                    <input type="text" name="total" id="total" class="form-control number-input"
                                           value="{{ $totalFormatted }}" required data-thousand-separator>
                                </div>
                                @error('total') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-3 col-md-3">
                                <label for="budget_month" class="form-label">{{ __('Budget Month') }}</label>
                                <select class="form-control" name="budget_month" id="budget_month">
                                    <option value="">{{ __('Select Month...') }}</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ (int)($budget->budget_month ?? $bM) === $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0,0,0,$m,1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-xxl-3 col-md-3">
                                <label for="budget_year" class="form-label">{{ __('Budget Year') }}</label>
                                <select class="form-control" name="budget_year" id="budget_year">
                                    <option value="">{{ __('Select Year...') }}</option>
                                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                        <option value="{{ $y }}" {{ (int)($budget->budget_year ?? $bY) === (int)$y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-xxl-3 col-md-3">
                                <label for="company2" class="form-label">{{ __('Commercial Name (Display)') }}</label>
                                <input type="text" class="form-control" id="company2" readonly value="{{ $budget->commercialName ?? '' }}">
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label class="form-label">{{ __('Condition') }}</label>
                                <select class="form-control" name="condition" data-choices data-choices-sorting-false>
                                    <option value="" disabled>{{ __('Select condition...') }}</option>
                                    <option value="1" {{ (isset($budget) && $budget->condition == 1) ? 'selected' : '' }}>{{ __('Awaiting Purchase Order') }}</option>
                                    <option value="2" {{ (isset($budget) && $budget->condition == 2) ? 'selected' : '' }}>{{ __('Invoiced') }}</option>
                                    <option value="3" {{ (isset($budget) && $budget->condition == 3) ? 'selected' : '' }}>{{ __('New Agreement') }}</option>
                                    <option value="4" {{ (isset($budget) && $budget->condition == 4) ? 'selected' : '' }}>{{ __('Portfolio') }}</option>
                                </select>
                                @error('condition') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select class="form-control" name="status" data-choices data-choices-sorting-false>
                                    <option value="" disabled>{{ __('Select status...') }}</option>
                                    <option value="1" {{ (isset($budget) && $budget->status == 1) ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                    <option value="2" {{ (isset($budget) && $budget->status == 2) ? 'selected' : '' }}>{{ __('Invoiced') }}</option>
                                    <option value="3" {{ (isset($budget) && $budget->status == 3) ? 'selected' : '' }}>{{ __('Discarded') }}</option>
                                </select>
                                @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Update Budget') }}</button>
                                <a href="{{ route('budgets') }}" class="btn btn-dark">Back</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
// Enhanced number formatting with thousand separators (dots)
function formatNumberWithDots(value) {
    if (!value) return '';
    
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
    return decimalPart ? integerPart + ',' + decimalPart : integerPart;
}

function unformatNumber(value) {
    if (!value) return '';
    // Remove dots (thousand separators) and replace comma with dot for decimal
    return value.replace(/\./g, '').replace(',', '.');
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
            const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End'];
            const isNumber = /^\d$/.test(e.key);
            const isComma = e.key === ',';
            const isDot = e.key === '.';
            const hasComma = this.value.includes(',');
            
            // Allow: numbers, comma (only one), navigation keys, ctrl+a, ctrl+c, ctrl+v, ctrl+x
            if (!isNumber && 
                !(isComma && !hasComma) && 
                !isDot && // Allow dot for compatibility
                !allowedKeys.includes(e.key) &&
                !(e.ctrlKey && ['a', 'c', 'v', 'x'].includes((e.key || '').toLowerCase()))) {
                e.preventDefault();
            }
        });
    });
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

document.addEventListener('DOMContentLoaded', function() {
    // Initialize number formatting
    initializeNumberFormatting();
    
    const commercialSelect  = document.getElementById('commercialName');
    const userTypeInput     = document.getElementById('user_type');
    const licensedConceptEl = document.getElementById('licensedConcept');
    const licensedEnvEl     = document.getElementById('licensedEnvironment');
    const categoryEl        = document.getElementById('category');
    const subcategoryEl     = document.getElementById('subcategory');
    const frequencySel      = document.getElementById('frequency');

    const beginInput        = document.getElementById('begin');
    const finishInput       = document.getElementById('finish');
    const beginMonthHidden  = document.getElementById('begin_month');
    const beginYearHidden   = document.getElementById('begin_year');
    const finishMonthHidden = document.getElementById('finish_month');
    const finishYearHidden  = document.getElementById('finish_year');

    const monthsTotalEl     = document.getElementById('months_total');
    const monthlyAmountEl   = document.getElementById('monthly_amount');
    const annualValueEl     = document.getElementById('annual_value');
    const subTotalEl        = document.getElementById('subTotal');
    const vatEl             = document.getElementById('vat');
    const totalEl           = document.getElementById('total');

    function ymToParts(val) {
        if (!val) return null;
        const [y, m] = val.split('-').map(v => parseInt(v, 10));
        return { y, m };
    }
    function partsToYm(y, m) {
        return y.toString().padStart(4, '0') + '-' + m.toString().padStart(2, '0');
    }
    function addMonths(y, m, add) {
        const d = new Date(y, m - 1, 1);
        d.setMonth(d.getMonth() + add);
        return { y: d.getFullYear(), m: d.getMonth() + 1 };
    }
    function monthsDiffInclusive(bY, bM, fY, fM) {
        const a = new Date(bY, bM - 1, 1);
        const b = new Date(fY, fM - 1, 1);
        let months = (b.getFullYear() - a.getFullYear()) * 12 + (b.getMonth() - a.getMonth()) + 1;
        if (months < 1) months = 1;
        return months;
    }
    function syncHidden(which) {
        const src = which === 'begin' ? beginInput : finishInput;
        const parts = ymToParts(src.value);
        if (!parts) return;
        if (which === 'begin') {
            beginYearHidden.value  = parts.y;
            beginMonthHidden.value = parts.m;
        } else {
            finishYearHidden.value  = parts.y;
            finishMonthHidden.value = parts.m;
        }
    }
    // Only auto-set Finish when Frequency is Monthly AND Finish is empty
    function setFinishFromBeginIfMonthly() {
        const freq = frequencySel.value;
        if (freq !== 'Monthly') return;
        if (!finishInput.value) {
            const b = ymToParts(beginInput.value);
            if (!b) return;
            const f = addMonths(b.y, b.m, 11); // inclusive 12 months
            finishInput.value       = partsToYm(f.y, f.m);
            finishYearHidden.value  = f.y;
            finishMonthHidden.value = f.m;
        }
    }
    function recomputeMonthsAndAmounts() {
        const b = ymToParts(beginInput.value);
        const f = ymToParts(finishInput.value);
        const annual = parseFormattedNumber(annualValueEl.value);
        const vat    = parseFloat(vatEl.value || '0');

        if (b && f) {
            const months = monthsDiffInclusive(b.y, b.m, f.y, f.m);
            monthsTotalEl.value = months;

            if (annual > 0 && months > 0) {
                const monthly = (annual / months);
                monthlyAmountEl.value = formatNumberForDisplay(monthly);

                // reflect on SubTotal/Total
                subTotalEl.value = formatNumberForDisplay(monthly);
                const total = monthly + (monthly * vat / 100.0);
                totalEl.value = formatNumberForDisplay(total);
            }
        }
    }

    // Calculate total from subtotal and VAT
    function calculateTotalFromSubtotalAndVat() {
        const subTotal = parseFormattedNumber(subTotalEl.value);
        const vat = parseFloat(vatEl.value || '0');
        const total = subTotal + (subTotal * vat / 100);
        totalEl.value = formatNumberForDisplay(total);
    }

    // Events
    commercialSelect.addEventListener('change', function() {
        const clientId = this.value;
        if (clientId) {
            fetch(`/budget/get-user-type/${clientId}`)
                .then(response => response.json())
                .then(data => {
                    userTypeInput.value      = data.userType || '';
                    licensedConceptEl.value  = data.userType || '';
                    licensedEnvEl.value      = data.licensedEnvironment || '';
                    categoryEl.value         = data.categoryVal || '';
                    subcategoryEl.value      = data.subcategoryVal || '';

                    if (data.billingFrequency) {
                        frequencySel.value = data.billingFrequency; // Monthly/Quarterly/Annual
                        setFinishFromBeginIfMonthly(); // will not overwrite if Finish already filled
                        recomputeMonthsAndAmounts();
                    }
                })
                .catch(error => console.error('Error:', error));
        } else {
            userTypeInput.value     = '';
            licensedConceptEl.value = '';
            licensedEnvEl.value     = '';
            categoryEl.value        = '';
            subcategoryEl.value     = '';
        }
    });
    frequencySel.addEventListener('change', function() {
        setFinishFromBeginIfMonthly(); // no overwrite if Finish exists
        recomputeMonthsAndAmounts();
    });
    beginInput.addEventListener('change', function() {
        syncHidden('begin');
        setFinishFromBeginIfMonthly(); // no overwrite if Finish exists
        recomputeMonthsAndAmounts();
    });
    finishInput.addEventListener('change', function() {
        syncHidden('finish');
        recomputeMonthsAndAmounts();
    });
    annualValueEl.addEventListener('input', function() {
        recomputeMonthsAndAmounts();
    });
    vatEl.addEventListener('input', function() {
        calculateTotalFromSubtotalAndVat();
        recomputeMonthsAndAmounts();
    });
    subTotalEl.addEventListener('input', function() {
        calculateTotalFromSubtotalAndVat();
    });

    // Form submission - unformat numbers before sending
    document.getElementById('budgetEditForm').addEventListener('submit', function() {
        // Unformat all number inputs before submission
        const numberInputs = this.querySelectorAll('.number-input, [data-thousand-separator]');
        numberInputs.forEach(input => {
            const formattedValue = input.value;
            if (formattedValue) {
                const unformatted = unformatNumber(formattedValue);
                input.value = unformatted;
            }
        });
    });

    syncHidden('begin');
    syncHidden('finish');
    setFinishFromBeginIfMonthly();
    recomputeMonthsAndAmounts();
});
</script>
@stop