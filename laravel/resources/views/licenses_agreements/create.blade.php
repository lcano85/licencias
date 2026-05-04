@extends('layouts.app')
@section('title', $pageTitle)
@section('styles')
<style>
.section-divider {
    border-top: 2px solid #e9ecef;
    margin: 25px 0;
    padding-top: 20px;
}
.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 15px;
}
.attachment-item {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 10px;
    margin-bottom: 10px;
}
.attachment-item input[type="file"] {
    margin-bottom: 5px;
}
</style>
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Create License / Agreement') }}</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form id="licensesAgreementsDetails" method="POST" action="{{ route('licenses-agreements.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="section-title"><i class="ri-information-line me-2"></i>Basic Information</div>
                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-4 col-md-4">
                                <label for="commercialName" class="form-label">Commercial Name *</label>
                                <select class="form-control" id="commercialName" name="commercialName" data-choices data-choices-sorting-false placeholder="{{ __('Select Commercial Name...') }}" required>
                                    <option value="">{{ __('Select Commercial Name...') }}</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->commercialName ?: ($client->legalName ?: 'Client #' . $client->id) }}</option>
                                    @endforeach
                                </select>
                                @error('commercialName') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-4 col-md-4">
                                <label for="category" class="form-label">{{ __('Category *') }}</label>
                                <input type="text" class="form-control" name="category" id="category" required placeholder="{{ __('Enter category') }}" readonly>
                                @error('category') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-4 col-md-4">
                                <label for="subcategory" class="form-label">{{ __('Sub Category *') }}</label>
                                <input type="text" class="form-control" name="subcategory" id="subcategory" required placeholder="{{ __('Enter sub category') }}" readonly>
                                @error('subcategory') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="licensedConcept" class="form-label">{{ __('Licensed Concept *') }}</label>
                                <input type="text" class="form-control" name="licensedConcept" id="licensedConcept" required placeholder="{{ __('Enter user type') }}" readonly>
                                @error('licensedConcept') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="licensedEnvironment" class="form-label">{{ __('Licensed Environment * (Multipick)') }}</label>
                                <select class="form-control" id="licensedEnvironment" name="licensedEnvironment[]" multiple data-choices data-choices-sorting-false placeholder="{{ __('Select Licensed Environment...') }}" required>
                                    @foreach($environments as $id => $name)
                                        <option value="{{ $id }}">{{ __($name) }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">{{ __('Hold Ctrl/Cmd to select multiple') }}</small>
                                @error('licensedEnvironment') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="origin" class="form-label">{{ __('Origin *') }}</label>
                                <select class="form-control" id="origin" name="origin" data-choices data-choices-sorting-false required>
                                    <option value="">{{ __('Select origin...') }}</option>
                                    <option value="License">{{ __('License') }}</option>
                                    <option value="Transaction">{{ __('Transaction') }}</option>
                                    <option value="Conciliation">{{ __('Conciliation') }}</option>
                                    <option value="Sentences">{{ __('Sentences') }}</option>
                                </select>
                                @error('origin') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="status" class="form-label">{{ __('Status *') }}</label>
                                <select class="form-control" id="status" name="status" data-choices data-choices-sorting-false required>
                                    <option value="">{{ __('Select status...') }}</option>
                                    <option value="1" selected>{{ __('Active') }}</option>
                                    <option value="2">{{ __('Canceled') }}</option>
                                    <option value="3">{{ __('Suspended') }}</option>
                                    <option value="4">{{ __('Expired') }}</option>
                                </select>
                                @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- License Validity Period -->
                        <div class="section-divider"></div>
                        <div class="section-title"><i class="ri-calendar-check-line me-2"></i>License Validity Period</div>
                        <div class="alert alert-info">
                            <strong>{{ __('Note:') }}</strong> Start Date and End Date refer to the validity period of the license itself.
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="startDate" class="form-label">{{ __('Start Date (License Validity) *') }}</label>
                                <input type="date" name="startDate" id="startDate" class="form-control" required>
                                @error('startDate') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="endDate" class="form-label">{{ __('End Date (License Validity) *') }}</label>
                                <input type="date" name="endDate" id="endDate" class="form-control" required>
                                @error('endDate') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Budget/Billing Information -->
                        <div class="section-divider"></div>
                        <div class="section-title"><i class="ri-money-dollar-circle-line me-2"></i>Budget / Billing Information</div>
                        <div class="alert alert-warning">
                            <strong>{{ __('Important:') }}</strong> Frequency, Begins (month/year), and Finish (month/year) are linked to Budget billing, not license validity.
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="billing_frequency" class="form-label">{{ __('Frequency (for Budget) *') }}</label>
                                <select class="form-control" id="billing_frequency" name="billing_frequency" data-choices data-choices-sorting-false required>
                                    <option value="">{{ __('Select frequency...') }}</option>
                                    <option value="Monthly" selected>{{ __('Monthly') }}</option>
                                    <option value="Quarterly">{{ __('Quarterly') }}</option>
                                    <option value="Annual">{{ __('Annual') }}</option>
                                    <option value="One-Time Payment">{{ __('One-Time Payment') }}</option>
                                </select>
                                @error('billing_frequency') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-3 col-md-3">
                                <label for="begin_month" class="form-label">{{ __('Begins - Month *') }}</label>
                                <select class="form-control" id="begin_month" name="begin_month" required>
                                    <option value="">{{ __('Select month...') }}</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                                @error('begin_month') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-3 col-md-3">
                                <label for="begin_year" class="form-label">{{ __('Begins - Year *') }}</label>
                                <select class="form-control" id="begin_year" name="begin_year" required>
                                    <option value="">{{ __('Select year...') }}</option>
                                    @for($y = date('Y'); $y <= date('Y') + 10; $y++)
                                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                                @error('begin_year') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-3 col-md-3">
                                <label for="finish_month" class="form-label">{{ __('Finish - Month') }}</label>
                                <select class="form-control" id="finish_month" name="finish_month">
                                    <option value="">{{ __('Select month...') }}</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endfor
                                </select>
                                @error('finish_month') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-3 col-md-3">
                                <label for="finish_year" class="form-label">{{ __('Finish - Year') }}</label>
                                <select class="form-control" id="finish_year" name="finish_year">
                                    <option value="">{{ __('Select year...') }}</option>
                                    @for($y = date('Y'); $y <= date('Y') + 10; $y++)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                                @error('finish_year') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-4 col-md-4">
                                <label for="monthlyValue" class="form-label">Monthly Value ($1.000,00)</label>
                                <input type="text" name="monthlyValue" id="monthlyValue" class="form-control currency-input" placeholder="0,00">
                                @error('monthlyValue') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-4 col-md-4">
                                <label for="vat" class="form-label">{{ __('Vat') }}</label>
                                <input type="text" name="vat" id="vat" class="form-control" placeholder="12%">
                                @error('vat') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-4 col-md-4">
                                <label for="annualValue" class="form-label">Annual Value ($1.000,00)</label>
                                <input type="text" name="annualValue" id="annualValue" class="form-control currency-input" placeholder="0,00">
                                @error('annualValue') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Document Attachments -->
                        <div class="section-divider"></div>
                        <div class="section-title"><i class="ri-attachment-2 me-2"></i>Document Attachments</div>
                        <div class="alert alert-info">
                            Upload original documents related to this license/agreement for easy reference (PDF, DOC, images, etc.). Max 10MB per file.
                        </div>
                        <div id="attachments-container">
                            <div class="attachment-item" data-index="0">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Document File') }}</label>
                                        <input type="file" name="attachments[]" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.zip,.rar">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">{{ __('Description (Optional)') }}</label>
                                        <input type="text" name="attachment_descriptions[]" class="form-control" placeholder="{{ __('e.g., Original signed contract') }}">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger btn-sm remove-attachment" style="display:none;">
                                            <iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-12">
                                <button type="button" class="btn btn-secondary btn-sm" id="add-attachment">
                                    <i class="ri-add-line me-1"></i> Add Another Document
                                </button>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Save License') }}</button>
                                <a href="{{ route('licenses-agreements') }}" class="btn btn-dark">Back</a>
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
document.addEventListener('DOMContentLoaded', function() {
    const commercialSelect = document.getElementById('commercialName');
    const licensedConceptInput = document.getElementById('licensedConcept');
    const categoryInput = document.getElementById('category');
    const subcategoryInput = document.getElementById('subcategory');

    commercialSelect.addEventListener('change', function() {
        const clientId = this.value;

        if (clientId) {
            fetch(`/licenses-agreements/get-user-type/${clientId}`)
                .then(response => response.json())
                .then(data => {
                    licensedConceptInput.value = data.userType || '';
                    categoryInput.value = data.categoryVal || '';
                    subcategoryInput.value = data.subcategoryVal || '';
                })
                .catch(error => console.error('Error:', error));
        } else {
            licensedConceptInput.value = '';
            categoryInput.value = '';
            subcategoryInput.value = '';
        }
    });

    // Attachment management
    let attachmentIndex = 1;
    
    document.getElementById('add-attachment').addEventListener('click', function() {
        const container = document.getElementById('attachments-container');
        const newItem = document.createElement('div');
        newItem.className = 'attachment-item';
        newItem.setAttribute('data-index', attachmentIndex);
        newItem.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Document File') }}</label>
                    <input type="file" name="attachments[]" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.zip,.rar">
                </div>
                <div class="col-md-5">
                    <label class="form-label">{{ __('Description (Optional)') }}</label>
                    <input type="text" name="attachment_descriptions[]" class="form-control" placeholder="{{ __('e.g., Amendment document') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-attachment">
                        <iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newItem);
        attachmentIndex++;
        updateRemoveButtons();
    });

    document.getElementById('attachments-container').addEventListener('click', function(e) {
        if (e.target.closest('.remove-attachment')) {
            e.target.closest('.attachment-item').remove();
            updateRemoveButtons();
        }
    });

    function updateRemoveButtons() {
        const items = document.querySelectorAll('.attachment-item');
        items.forEach((item, index) => {
            const removeBtn = item.querySelector('.remove-attachment');
            if (items.length > 1) {
                removeBtn.style.display = 'block';
            } else {
                removeBtn.style.display = 'none';
            }
        });
    }

    // Currency formatting
    function formatCurrency(value) {
        let cleaned = value.replace(/[^\d,]/g, '');
        let parts = cleaned.split(',');
        let integerPart = parts[0];
        let decimalPart = parts[1] || '';
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        if (decimalPart.length > 2) {
            decimalPart = decimalPart.substring(0, 2);
        }
        return decimalPart ? integerPart + ',' + decimalPart : integerPart;
    }

    function unformatCurrency(value) {
        return value.replace(/\./g, '').replace(',', '.');
    }

    const currencyInputs = document.querySelectorAll('.currency-input');
    currencyInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let cursorPosition = this.selectionStart;
            let oldLength = this.value.length;
            let formattedValue = formatCurrency(this.value);
            this.value = formattedValue;
            let newLength = this.value.length;
            let lengthDiff = newLength - oldLength;
            if (lengthDiff > 0) {
                cursorPosition += lengthDiff;
            }
            this.setSelectionRange(cursorPosition, cursorPosition);
        });

        input.addEventListener('focus', function() {
            this.placeholder = '0,00';
        });

        input.addEventListener('blur', function() {
            if (this.value) {
                let unformatted = unformatCurrency(this.value);
                let numValue = parseFloat(unformatted);
                if (!isNaN(numValue)) {
                    let formatted = numValue.toFixed(2).replace('.', ',');
                    let parts = formatted.split(',');
                    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    this.value = parts.join(',');
                }
            }
        });

        input.addEventListener('keydown', function(e) {
            const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End'];
            const isNumber = /^\d$/.test(e.key);
            const isComma = e.key === ',';
            const hasComma = this.value.includes(',');
            if (!isNumber && 
                !(isComma && !hasComma) && 
                !allowedKeys.includes(e.key) &&
                !(e.ctrlKey && ['a', 'c', 'v', 'x'].includes((e.key || '').toLowerCase()))) {
                e.preventDefault();
            }
        });
    });

    // Date validation
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');

    startDateInput.addEventListener('change', function() {
        const startDate = new Date(this.value);
        startDate.setDate(startDate.getDate() + 1);
        endDateInput.min = startDate.toISOString().split('T')[0];
    });
});
</script>
@stop