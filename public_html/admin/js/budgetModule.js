const trans = window.translations || {};
const __ = (key) => trans[key] || key;

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
            if (
                !isNumber && 
                !(isComma && !hasComma) && 
                !isDot && // Allow dot for compatibility
                !(isMinus && !hasMinus && canPlaceMinus) &&
                !allowedKeys.includes(key) &&
                !(e.ctrlKey && ['a', 'c', 'v', 'x'].includes(key.toLowerCase()))
            ) {
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
        return { y, m };
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
                .catch(error => console.error(__('Error'), error));
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
            'X-CSRF-TOKEN': window.BUDGET_ROUTES.csrfToken
        }
    });
    
    checkDeadlineAlert();
    
    // DataTable initialization with ALL filters properly configured
    let table = $('#budgetData').DataTable({
        processing: true,
        serverSide: true,
        language: window.codexDataTableLanguage(),
        ajax: {
            url: window.BUDGET_ROUTES.budgetsData,
            data: function(d) {
                // Send ALL filter values - use empty string if no value
                d.month = $('#monthFilter').val() || '';
                d.year = $('#yearFilter').val() || '';
                d.start_date = $('#startDateFilter').val() || '';
                d.end_date = $('#endDateFilter').val() || '';
                d.conceptFilter = $('#conceptFilter').val() || '';
                d.conditionFilter = $('#conditionFilter').val() || '';
                
                // Debug log to see what's being sent
            console.log(__('Search...'), {
                    month: d.month,
                    year: d.year,
                    start_date: d.start_date,
                    end_date: d.end_date,
                    conceptFilter: d.conceptFilter,
                    conditionFilter: d.conditionFilter
                });
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'user_type', name: 'user_type' },
            { data: 'company', name: 'company' },
            { data: 'commercialName', name: 'commercialName' },
            { data: 'subTotal', name: 'subTotal' },
            { data: 'vat', name: 'vat' },
            { data: 'total', name: 'total' },
            { data: 'condition', name: 'condition' },
            { data: 'status', name: 'status' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        createdRow: function(row, data, dataIndex) {
            if (data.row_class) {
                $(row).addClass(data.row_class);
            }
        },
        error: function(xhr, error, code) {
            console.log(__('Error'), xhr, error, code);
        }
    });

    // Filter change handlers - reload table when ANY filter changes
    $('#monthFilter, #yearFilter, #startDateFilter, #endDateFilter, #conceptFilter, #conditionFilter').on('change keyup', function() {
        console.log(__('Condition'), $(this).attr('id'), '=', $(this).val());
        table.ajax.reload(null, false);
        refreshConceptTotals(); // Refresh totals when filters change
    });
    
    // Initialize tooltips after table draw
    table.on('draw', function() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
    });
});

// Enhanced totals refresh function
function refreshConceptTotals() {
    const url = window.BUDGET_ROUTES.budgetsData;
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
            
            console.log(__('Total Amount'), res);
            
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
                        row.find('.subtotal').html(__('Subtotal') + ': $ ' + sub);
                        row.find('.vat').html(__('VAT') + ': $ ' + vat);
                        row.find('.total').html(__('Total') + ': $ ' + tot);
                    }
                });
                
                // Update section total
                const sectionTotalRow = $(`#section-${sectionKey} .section-total-row`);
                sectionTotalRow.find('.subtotal').html('<strong>' + __('Subtotal') + ': $ ' + formatEuro(section.sectionTotal.subTotal) + '</strong>');
                sectionTotalRow.find('.vat').html('<strong>' + __('VAT') + ': $ ' + formatEuro(section.sectionTotal.vat) + '</strong>');
                sectionTotalRow.find('.total').html('<strong>' + __('Total') + ': $ ' + formatEuro(section.sectionTotal.total) + '</strong>');
                
                // Show/hide section based on whether it has data
                const hasData = section.sectionTotal.total > 0;
                if (hasData) {
                    sectionElement.show().removeClass('section-empty').addClass('section-has-data');
                } else {
                    sectionElement.hide().addClass('section-empty').removeClass('section-has-data');
                }
            });
            
            // Update grand total
            $('#grandTotalsRow .subtotal').html('<strong>' + __('Subtotal') + ': $ ' + formatEuro(res.grandTotal.subTotal) + '</strong>');
            $('#grandTotalsRow .vat').html('<strong>' + __('VAT') + ': $ ' + formatEuro(res.grandTotal.vat) + '</strong>');
            $('#grandTotalsRow .total').html('<strong>' + __('Total') + ': $ ' + formatEuro(res.grandTotal.total) + '</strong>');
        },
        error: function(xhr) {
            console.error(__('Failed to refresh concept totals'), xhr);
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
        html: '<div class="mt-3"><lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon><div class="mt-4 pt-2 fs-15 mx-5"><p class="text-muted mx-4 mb-0">' + __('Are you Sure You want to Delete this budget?') + '</p></div></div>',
        showCancelButton: !0,
        customClass: {
            confirmButton: "btn btn-primary w-xs me-2 mb-1",
            cancelButton: "btn btn-danger w-xs mb-1"
        },
        confirmButtonText: __('Yes, Delete It!'),
        buttonsStyling: !1,
        showCloseButton: !0
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/budget/delete/' + recID,
                type: 'POST',
                data: {
                    "_token": window.BUDGET_ROUTES.csrfToken,
                    "recID": recID
                },
                success: function(response) {
                    Swal.fire(__('Deleted!'), response.success, 'success').then((result) => {
                        $('#budgetData').DataTable().ajax.reload(null, false);
                        refreshConceptTotals();
                    });
                },
                error: function(xhr) {
                    Swal.fire(__('Error!'), xhr.responseJSON.error, 'error');
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

    $('#exampleModalScrollableTitle').text(__('Loading...'));
    $('#exampleModalScrollable .modal-body').html('<p>' + __('Loading...') + '</p>');

    $.ajax({
        url: '/get-budget-record/' + recordID,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#exampleModalScrollableTitle').text(__('Register Invoice'));
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
            <label for="invoiceConsecutive" class="form-label">Invoice Consecutive</label>
            <input type="text" name="invoiceConsecutive" id="invoiceConsecutive" class="form-control" placeholder="Auto-generated" readonly>
            <small class="text-muted">Will be auto-generated upon submission</small>
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
              <option value="">Select criterion...</option>
              <option value="1">Min. Guaranteed, 8% Income</option>
              <option value="2">Min. Guaranteed + 8%</option>
              <option value="3">Monthly Fee</option>
              <option value="4">Annual Fee</option>
              <option value="5">Special Arrangement</option>
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
        <div class="alert alert-info"><i class="ri-information-line me-2"></i><strong>Invoice Date:</strong> Will be set to current date/time automatically </div>
      `);
        },
        error: function() {
            $('#exampleModalScrollable .modal-body').html('<p class="text-danger">' + __('Error') + '</p>');
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
                title: __('Success'),
                text: response.message || __('Invoice generated successfully.'),
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
                title: __('Error!'),
                text: xhr.responseJSON?.message || 'Something went wrong. Please try again.',
                confirmButtonColor: '#d33',
            });
        }
    });
});

// Register Invoice DataTable
$(document).ready(function() {
    const $invStart = $('#invoiceDateStart');
    const $invEnd   = $('#invoiceDateEnd');

    let invoiceTable = $('#registerInvoice').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.BUDGET_ROUTES.invoiceData,
            data: function(d){
                d.start_date = $invStart.val();
                d.end_date   = $invEnd.val();
            }
        },
        columns: [
            { data: 'invoiceNumber', name: 'invoiceNumber' },
            { data: 'invoiceDate', name: 'invoiceDate' },
            { data: 'commercialName', name: 'commercialName' },
            { data: 'criterion', name: 'criterion' },
            { data: 'subTotal', name: 'subTotal' },
            { data: 'vat', name: 'vat' },
            { data: 'total', name: 'total' },
            { data: 'created_by', name: 'created_by' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        error: function(xhr, error, code) {
            console.log(xhr, error, code);
        }
    });

    function reloadInvoices() {
        invoiceTable.ajax.reload(null, false);
    }

    $invStart.add($invEnd).on('change', reloadInvoices);

    $('#invoiceDateReset').on('click', function() {
        $invStart.val(window.BUDGET_ROUTES.defaultInvoiceStart);
        $invEnd.val(window.BUDGET_ROUTES.defaultInvoiceEnd);
        reloadInvoices();
    });

    invoiceTable.on('draw', function() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
    });
});

function formatEuroNew(num) {
    num = parseFloat(num).toFixed(2);
    let parts = num.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    return parts.join('.');
}

function loadInvoiceConceptTotals() {
    $.ajax({
        url: window.BUDGET_ROUTES.invoiceConceptTotals,
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
$(document).ready(function(){ loadInvoiceConceptTotals(); });

// Billing and Credit Notes JavaScript
$(function() {
    const $start = $('#billingPeriodStart');
    const $end = $('#billingPeriodEnd');

    const billingTable = $('#billingInvoicesTable').DataTable({
        processing: true,
        serverSide: true,
        order: [[4, 'asc']],
        ajax: {
            url: window.BUDGET_ROUTES.billingList,
            data: function(d) {
                d.start_date = $start.val();
                d.end_date = $end.val();
            }
        },
        columns: [
            { data: 'user_type', name: 'user_type' },
            { data: 'company', name: 'company' },
            { data: 'commercialName', name: 'commercialName' },
            { data: 'concept', name: 'concept' },
            { data: 'invoice_no', name: 'invoice_no' },
            { data: 'invoice_date', name: 'invoice_date' },
            { data: 'period', name: 'period' },
            { data: 'criterion', name: 'criterion' },
            { data: 'subtotal', name: 'subtotal' },
            { data: 'vat', name: 'vat' },
            { data: 'total', name: 'total' },
            { data: 'balance', name: 'balance' },
            { data: 'supporting_doc', name: 'supporting_doc', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
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
        searching: false,
        ajax: {
            url: window.BUDGET_ROUTES.billingCreditNotes,
            data: function(d) {
                d.start_date = $start.val();
                d.end_date = $end.val();
            }
        },
        order: [[4, 'asc']],
        columns: [
            { data: 'user_type', name: 'user_type' },
            { data: 'company', name: 'company' },
            { data: 'commercialName', name: 'commercialName' },
            { data: 'concept', name: 'concept' },
            { data: 'cn_no', name: 'cn_no' },
            { data: 'cn_date', name: 'cn_date' },
            { data: 'period', name: 'period' },
            { data: 'criterion', name: 'criterion' },
            { data: 'subtotal', name: 'subtotal' },
            { data: 'vat', name: 'vat' },
            { data: 'total', name: 'total' },
            { data: 'supporting_doc', name: 'supporting_doc', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });

    function reloadBoth() {
        billingTable.ajax.reload(null, false);
        cnTable.ajax.reload(null, false);
    }

    $start.add($end).on('change', reloadBoth);

    window.uploadBillingExcel = function() {
        $.post(
            window.BUDGET_ROUTES.billingUpload,
            { _token: window.BUDGET_ROUTES.csrfToken },
            function(res) {
                Swal.fire('Upload', res.message || 'OK', 'info');
            }
        );
    };

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
        window.open(
            window.BUDGET_ROUTES.billingReport + "?start_date=" + start + "&end_date=" + end,
            '_blank'
        );
        
        setTimeout(() => {
            Swal.close();
        }, 1000);
    };

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
            method: 'GET',
            action: window.BUDGET_ROUTES.billingDownload
        });
        
        form.append($('<input>', {
            type: 'hidden',
            name: 'start_date',
            value: start
        }));
        
        form.append($('<input>', {
            type: 'hidden',
            name: 'end_date',
            value: end
        }));
        
        $('body').append(form);
        form.submit();
        form.remove();
        
        setTimeout(() => {
            Swal.close();
        }, 1000);
    };

    window.openQuickRegister = function() {
        $('#newRecordModal').modal('show');
    };

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
              <input type="date" class="form-control" name="cn_date" value="${window.BUDGET_ROUTES.today}" required>
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
                    url: window.BUDGET_ROUTES.billingCnStore,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': window.BUDGET_ROUTES.csrfToken
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
    };

    window.deleteCN = function(id) {
        Swal.fire({
            title: 'Delete Credit Note?',
            icon: 'warning',
            showCancelButton: true
        }).then(ok => {
            if (!ok.isConfirmed) return;
            $.ajax({
                url: '/billing/credit-note/' + id,
                type: 'DELETE',
                data: {
                    _token: window.BUDGET_ROUTES.csrfToken
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
    };
});

// Income payment summary helpers (display only)
$(document).ready(function() {
    if (!$('#incomeInvoice').length) return;

    function formatSummaryCurrency(value) {
        const num = Number(value) || 0;
        return '$ ' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateIncomePaymentSummaryDisplay() {
        const container = $('#invoice-details-container');
        const hasInvoice = container.length && container.data('total-value') !== undefined;
        const invoiceTotal = hasInvoice ? Number(container.data('total-value') || 0) : 0;
        const remainingBalance = hasInvoice ? Number(container.data('total-balance') || 0) : 0;
        const totalPaid = parseFormattedNumber($('#incomeAmount').val() || '0') + parseFormattedNumber($('#incomeOtherAmounts').val() || '0');

        if ($('#incomeRemainingBalance').length) {
            $('#incomeRemainingBalance').val(formatSummaryCurrency(remainingBalance));
        }
        if ($('#summaryInvoiceTotalDisplay').length) {
            $('#summaryInvoiceTotalDisplay').text(formatSummaryCurrency(invoiceTotal));
        }
        if ($('#summaryRemainingBalanceDisplay').length) {
            $('#summaryRemainingBalanceDisplay').text(formatSummaryCurrency(remainingBalance));
        }
        if ($('#totalPaidDisplay').length) {
            $('#totalPaidDisplay').text(formatSummaryCurrency(totalPaid));
        }
    }

    $(document).on('change', '#incomeInvoice', updateIncomePaymentSummaryDisplay);
    $(document).on('input', '#incomeAmount, #incomeOtherAmounts', updateIncomePaymentSummaryDisplay);
    updateIncomePaymentSummaryDisplay();
});
