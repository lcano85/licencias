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
    margin-bottom: 5px;
}
.attachment-item {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 10px;
    margin-bottom: 10px;
}
.existing-attachment {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 12px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.existing-attachment:hover {
    background: #f8f9fa;
}
.file-icon {
    font-size: 2rem;
    margin-right: 15px;
    color: #6c757d;
}
.file-details {
    flex-grow: 1;
}
.file-name {
    font-weight: 600;
    color: #495057;
    margin-bottom: 3px;
}
.file-meta {
    font-size: 0.875rem;
    color: #6c757d;
}
.file-actions {
    display: flex;
    gap: 8px;
}
</style>
@stop

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('Edit License / Agreement') }}</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form id="licensesAgreementsDetails" method="POST" action="{{ route('licenses-agreements.update', $licenses->id) }}" enctype="multipart/form-data">
                        @csrf
                        <!-- Basic Information -->
                        <div class="section-title"><i class="ri-information-line me-2"></i>Basic Information</div>
                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-4 col-md-4">
                                <label for="commercialName" class="form-label">Commercial Name *</label>
                                <select class="form-control" id="commercialName" name="commercialName" data-choices data-choices-sorting-false placeholder="{{ __('Select Commercial Name...') }}" required>
                                    <option value="">{{ __('Select Commercial Name...') }}</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ (isset($licenses) && $licenses->commercialID == $client->id) ? 'selected' : '' }}>
                                            {{ $client->commercialName ?: ($client->legalName ?: 'Client #' . $client->id) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('commercialName') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-4 col-md-4">
                                <label for="category" class="form-label">{{ __('Category *') }}</label>
                                <input type="text" class="form-control" name="category" id="category" required placeholder="{{ __('Enter category') }}" readonly value="{{ $licenses->category ?? '' }}">
                                @error('category') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-4 col-md-4">
                                <label for="subcategory" class="form-label">{{ __('Sub Category *') }}</label>
                                <input type="text" class="form-control" name="subcategory" id="subcategory" required placeholder="{{ __('Enter sub category') }}" readonly value="{{ $licenses->subcategory ?? '' }}">
                                @error('subcategory') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="licensedConcept" class="form-label">{{ __('Licensed Concept *') }}</label>
                                <input type="text" class="form-control" name="licensedConcept" id="licensedConcept" required placeholder="{{ __('Enter licensed concept') }}" readonly value="{{ $licenses->licensedConcept ?? '' }}">
                                @error('licensedConcept') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="licensedEnvironment" class="form-label">{{ __('Licensed Environment * (Multipick)') }}</label>
                                <select class="form-control" id="licensedEnvironment" name="licensedEnvironment[]" multiple data-choices data-choices-sorting-false placeholder="{{ __('Select Licensed Environment...') }}" required>
                                    @php
                                        $selectedEnvironments = [];
                                        if(isset($licenses->licensedEnvironment)) {
                                            if(is_array($licenses->licensedEnvironment)) {
                                                $selectedEnvironments = $licenses->licensedEnvironment;
                                            } elseif(is_string($licenses->licensedEnvironment)) {
                                                $decoded = json_decode($licenses->licensedEnvironment, true);
                                                $selectedEnvironments = $decoded ?: [$licenses->licensedEnvironment];
                                            } else {
                                                $selectedEnvironments = [$licenses->licensedEnvironment];
                                            }
                                        }
                                    @endphp
                                    @foreach($environments as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, $selectedEnvironments) || in_array((string)$id, $selectedEnvironments) ? 'selected' : '' }}>{{ __($name) }}</option>
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
                                    <option value="License" {{ (isset($licenses) && $licenses->origin == 'License') ? 'selected' : '' }}>{{ __('License') }}</option>
                                    <option value="Transaction" {{ (isset($licenses) && $licenses->origin == 'Transaction') ? 'selected' : '' }}>{{ __('Transaction') }}</option>
                                    <option value="Conciliation" {{ (isset($licenses) && $licenses->origin == 'Conciliation') ? 'selected' : '' }}>{{ __('Conciliation') }}</option>
                                    <option value="Sentences" {{ (isset($licenses) && $licenses->origin == 'Sentences') ? 'selected' : '' }}>{{ __('Sentences') }}</option>
                                </select>
                                @error('origin') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="status" class="form-label">{{ __('Status *') }}</label>
                                <select class="form-control" id="status" name="status" data-choices data-choices-sorting-false required>
                                    <option value="">{{ __('Select status...') }}</option>
                                    <option value="1" {{ (isset($licenses) && $licenses->status == 1) ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="2" {{ (isset($licenses) && $licenses->status == 2) ? 'selected' : '' }}>{{ __('Canceled') }}</option>
                                    <option value="3" {{ (isset($licenses) && $licenses->status == 3) ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                                    <option value="4" {{ (isset($licenses) && $licenses->status == 4) ? 'selected' : '' }}>{{ __('Expired') }}</option>
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
                                <input type="date" name="startDate" id="startDate" class="form-control" required value="{{ date('Y-m-d', strtotime($licenses->startDate)) ?? '' }}">
                                @error('startDate') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="endDate" class="form-label">{{ __('End Date (License Validity) *') }}</label>
                                <input type="date" name="endDate" id="endDate" class="form-control" required value="{{ date('Y-m-d', strtotime($licenses->endDate)) ?? '' }}">
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
                                    <option value="Monthly" {{ (isset($licenses) && ($licenses->billing_frequency == 'Monthly' || $licenses->billing_frequency == 1)) ? 'selected' : '' }}>{{ __('Monthly') }}</option>
                                    <option value="Quarterly" {{ (isset($licenses) && ($licenses->billing_frequency == 'Quarterly' || $licenses->billing_frequency == 2)) ? 'selected' : '' }}>{{ __('Quarterly') }}</option>
                                    <option value="Annual" {{ (isset($licenses) && ($licenses->billing_frequency == 'Annual' || $licenses->billing_frequency == 3)) ? 'selected' : '' }}>{{ __('Annual') }}</option>
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
                                        <option value="{{ $m }}" {{ (isset($licenses) && $licenses->begin_month == $m) ? 'selected' : '' }}>
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
                                    @for($y = date('Y') - 5; $y <= date('Y') + 10; $y++)
                                        <option value="{{ $y }}" {{ (isset($licenses) && $licenses->begin_year == $y) ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                                @error('begin_year') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-3 col-md-3">
                                <label for="finish_month" class="form-label">{{ __('Finish - Month') }}</label>
                                <select class="form-control" id="finish_month" name="finish_month">
                                    <option value="">{{ __('Select month...') }}</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ (isset($licenses) && $licenses->finish_month == $m) ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                                @error('finish_month') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-3 col-md-3">
                                <label for="finish_year" class="form-label">{{ __('Finish - Year') }}</label>
                                <select class="form-control" id="finish_year" name="finish_year">
                                    <option value="">{{ __('Select year...') }}</option>
                                    @for($y = date('Y') - 5; $y <= date('Y') + 10; $y++)
                                        <option value="{{ $y }}" {{ (isset($licenses) && $licenses->finish_year == $y) ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                                @error('finish_year') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gy-4 mb-2">
                            <div class="col-xxl-6 col-md-6">
                                <label for="monthlyValue" class="form-label">Monthly Value ($1.000,00)</label>
                                <input type="text" name="monthlyValue" id="monthlyValue" class="form-control currency-input" placeholder="0,00" value="{{ $licenses->monthlyValue ?? '' }}">
                                @error('monthlyValue') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <label for="annualValue" class="form-label">Annual Value ($1.000,00)</label>
                                <input type="text" name="annualValue" id="annualValue" class="form-control currency-input" placeholder="0,00" value="{{ $licenses->annualValue ?? '' }}">
                                @error('annualValue') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Document Attachments -->
                        <div class="section-divider"></div>
                        <div class="section-title">{{ __('Document Attachments') }}</div>

                        @if($licenses->attachments && $licenses->attachments->count() > 0)
                        <div class="mb-3">
                            <h6 class="text-muted mb-3">Existing Documents ({{ $licenses->attachments->count() }})</h6>
                            @foreach($licenses->attachments as $attachment)
                            <div class="existing-attachment" data-attachment-id="{{ $attachment->id }}">
                                <i class="{{ $attachment->file_icon }} file-icon"></i>
                                <div class="file-details">
                                    <div class="file-name"><iconify-icon icon="solar:file-text-outline" class="align-middle fs-18"></iconify-icon> {{ $attachment->original_name }}</div>
                                    <div class="file-meta">
                                        <span class="mt-1">Date : {{ $attachment->created_at->format('d-m-Y H:i') }}</span>
                                        @if($attachment->description)
                                            <div class="mt-1">Description{{ $attachment->description }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="file-actions">
                                    <a href="{{ route('licenses-agreements.attachment.download', $attachment->id) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Download') }}">
                                        <iconify-icon icon="solar:download-minimalistic-bold" class="align-middle fs-18"></iconify-icon>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-existing-attachment" data-id="{{ $attachment->id }}" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                        <iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle fs-18"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="alert alert-secondary">
                            <i class="ri-information-line me-2"></i>No documents attached yet.
                        </div>
                        @endif

                        <div class="alert alert-info mt-3">
                            <strong>{{ __('Add New Documents:') }}</strong> Upload additional documents related to this license/agreement (PDF, DOC, images, etc.). Max 10MB per file.
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
                                        <input type="text" name="attachment_descriptions[]" class="form-control" placeholder="{{ __('e.g., Amendment document') }}">
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
                                <button type="submit" class="btn btn-primary">{{ __('Update License') }}</button>
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

    // Delete existing attachment
    document.querySelectorAll('.delete-existing-attachment').forEach(btn => {
        btn.addEventListener('click', function() {
            const attachmentId = this.getAttribute('data-id');
            
            Swal.fire({
                html: '<div class="mt-3"><lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon><div class="mt-4 pt-2 fs-15 mx-5"><p class="text-muted mx-4 mb-0">Are you sure you want to delete this attachment?</p></div></div>',
                showCancelButton: true,
                customClass: {
                    confirmButton: "btn btn-primary w-xs me-2 mb-1",
                    cancelButton: "btn btn-danger w-xs mb-1"
                },
                confirmButtonText: "Yes, Delete It!",
                buttonsStyling: false,
                showCloseButton: true
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/licenses-agreements/attachment/delete/${attachmentId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.fire('Deleted!', data.success, 'success');
                        document.querySelector(`[data-attachment-id="${attachmentId}"]`).remove();
                    })
                    .catch(error => {
                        Swal.fire('Error!', 'Failed to delete attachment', 'error');
                    });
                }
            });
        });
    });

    // New attachment management
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
                    <input type="text" name="attachment_descriptions[]" class="form-control" placeholder="{{ __('e.g., Updated contract') }}">
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

    // Currency formatting (same as create page)
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

    function parseCurrency(value) {
        const parsed = parseFloat(unformatCurrency(value || ''));
        return Number.isNaN(parsed) ? 0 : parsed;
    }

    function formatCurrencyFromNumber(value) {
        if (!Number.isFinite(value)) {
            value = 0;
        }
        let formatted = value.toFixed(2).replace('.', ',');
        let parts = formatted.split(',');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return parts.join(',');
    }

    function billingMonths() {
        const frequency = (document.getElementById('billing_frequency')?.value || '').toLowerCase();
        const beginMonth = parseInt(document.getElementById('begin_month')?.value || '', 10);
        const beginYear = parseInt(document.getElementById('begin_year')?.value || '', 10);
        const finishMonth = parseInt(document.getElementById('finish_month')?.value || '', 10);
        const finishYear = parseInt(document.getElementById('finish_year')?.value || '', 10);

        if (beginMonth && beginYear && finishMonth && finishYear) {
            return Math.max(1, ((finishYear - beginYear) * 12) + (finishMonth - beginMonth) + 1);
        }

        if (frequency === 'quarterly' || frequency === '2') return 3;
        if (frequency === 'annual' || frequency === '3') return 12;
        if (frequency === 'one-time payment' || frequency === '4') return 1;
        return 12;
    }

    function recalculateLicenseAmounts(changedField = '') {
        const monthlyInput = document.getElementById('monthlyValue');
        const annualInput = document.getElementById('annualValue');
        if (!monthlyInput || !annualInput) return;

        const months = billingMonths();
        const monthly = parseCurrency(monthlyInput.value);
        const annual = parseCurrency(annualInput.value);

        if (changedField === 'annual' && annual > 0 && monthly === 0) {
            monthlyInput.value = formatCurrencyFromNumber(annual / months);
            return;
        }

        if (monthly > 0) {
            annualInput.value = formatCurrencyFromNumber(monthly * months);
        }
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
            recalculateLicenseAmounts(this.id === 'annualValue' ? 'annual' : 'monthly');
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
            recalculateLicenseAmounts(this.id === 'annualValue' ? 'annual' : 'monthly');
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

    ['billing_frequency', 'begin_month', 'begin_year', 'finish_month', 'finish_year'].forEach(id => {
        const field = document.getElementById(id);
        if (field) {
            field.addEventListener('change', () => recalculateLicenseAmounts());
        }
    });

    recalculateLicenseAmounts();

    // Date validation
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');

    startDateInput.addEventListener('change', function() {
        const startDate = new Date(this.value);
        startDate.setDate(startDate.getDate() + 1);
        endDateInput.min = startDate.toISOString().split('T')[0];
    });

    // Initialize tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
});
</script>
@stop
