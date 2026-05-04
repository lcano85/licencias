<!-- Modal Content for Viewing/Validating -->
<!-- File: resources/views/budget/validations/modal-content.blade.php -->
@php
    // Helper function to safely format dates
    function formatDate($date, $format = 'M d, Y') {
        if (!$date) return '';
        try {
            return is_string($date) ? 
                \Carbon\Carbon::parse($date)->format($format) : 
                $date->format($format);
        } catch (\Exception $e) {
            return $date; // Return as-is if can't parse
        }
    }
    
    function formatDateTime($date, $format = 'M d, Y H:i') {
        return formatDate($date, $format);
    }
@endphp

<div class="validation-detail-container">
    <!-- Header Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h5 class="mb-3">{{ __('Report Information') }}</h5>
            <table class="table table-sm table-borderless">
                <tr>
                    <td class="text-muted" width="140">{{ __('Report Type:') }}</td>
                    <td>
                        <span class="badge bg-{{ $validation->report_type === 'billing' ? 'primary' : 'success' }}">
                            {{ ucfirst($validation->report_type) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">{{ __('Period:') }}</td>
                    <td>
                        <strong>{{ formatDate($validation->period_start) }} - {{ formatDate($validation->period_end) }}</strong>
                    </td>
                </tr>
                @if($validation->title)
                <tr>
                    <td class="text-muted">{{ __('Title:') }}</td>
                    <td>{{ $validation->title }}</td>
                </tr>
                @endif
                <tr>
                    <td class="text-muted">{{ __('Created By:') }}</td>
                    <td>{{ $validation->creator->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="text-muted">{{ __('Created Date:') }}</td>
                    <td>{{ formatDateTime($validation->created_at) }}</td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h5 class="mb-3">{{ __('Validation Status') }}</h5>
            <table class="table table-sm table-borderless">
                <tr>
                    <td class="text-muted" width="140">{{ __('Overall Status:') }}</td>
                    <td>
                        @php
                            $statusColors = [
                                'Pending Accountant Validation' => 'warning',
                                'Pending Management Validation' => 'info',
                                'Approved' => 'success',
                                'Rejected by Accountant' => 'danger',
                                'Rejected by Management' => 'danger'
                            ];
                            $color = $statusColors[$validation->status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $color }}">{{ $validation->status }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">{{ __('Accountant:') }}</td>
                    <td>
                        @if($validation->accountant)
                            <div>{{ $validation->accountant->name ?? 'N/A' }}</div>
                            @if($validation->accountant_validated_at)
                                <small class="text-muted">{{ formatDateTime($validation->accountant_validated_at) }}</small>
                            @endif
                            <span class="badge bg-{{ $validation->accountant_status === 'approved' ? 'success' : ($validation->accountant_status === 'rejected' ? 'danger' : 'warning') }} ms-2">
                                {{ ucfirst($validation->accountant_status) }}
                            </span>
                        @else
                            <span class="text-muted">{{ __('Pending') }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">{{ __('Management:') }}</td>
                    <td>
                        @if($validation->management)
                            <div>{{ $validation->management->name ?? 'N/A' }}</div>
                            @if($validation->management_validated_at)
                                <small class="text-muted">{{ formatDateTime($validation->management_validated_at) }}</small>
                            @endif
                            <span class="badge bg-{{ $validation->management_status === 'approved' ? 'success' : ($validation->management_status === 'rejected' ? 'danger' : 'warning') }} ms-2">
                                {{ ucfirst($validation->management_status) }}
                            </span>
                        @else
                            <span class="text-muted">{{ __('Pending') }}</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Summary by Concept -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">{{ __('Summary by Concept') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Concept') }}</th>
                            <th class="text-end">{{ __('Count') }}</th>
                            <th class="text-end">{{ __('Subtotal') }}</th>
                            <th class="text-end">{{ __('VAT') }}</th>
                            <th class="text-end">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summary as $row)
                        <tr>
                            <td>{{ $row->concept }}</td>
                            <td class="text-end">{{ $row->count }}</td>
                            <td class="text-end">{{ number_format((float) $row->subtotal, 2, '.', ',') }}</td>
                            <td class="text-end">{{ number_format((float) $row->vat, 2, '.', ',') }}</td>
                            <td class="text-end">{{ number_format((float) $row->total, 2, '.', ',') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">{{ __('No records found') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-dark">
                        @php
                            $summaryCount = collect($summary)->sum('count');
                            $summarySubtotal = collect($summary)->sum('subtotal');
                            $summaryVat = collect($summary)->sum('vat');
                            $summaryTotal = collect($summary)->sum('total');
                        @endphp
                        <tr>
                            <th>{{ __('Grand Total') }}</th>
                            <th class="text-end">{{ $summaryCount }}</th>
                            <th class="text-end">{{ number_format((float) $summarySubtotal, 2, '.', ',') }}</th>
                            <th class="text-end">{{ number_format((float) $summaryVat, 2, '.', ',') }}</th>
                            <th class="text-end">{{ number_format((float) $summaryTotal, 2, '.', ',') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Validation Form -->
    @if($canValidate)
    <form id="validationDetailForm">
        @csrf
        <input type="hidden" name="validation_id" value="{{ $validation->id }}">
    @endif

        <!-- Detailed Items -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">{{ __('Detailed Items') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('No. RC') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Client') }}</th>
                                <th>{{ __('Concept') }}</th>
                                <th class="text-end">{{ __('Value') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($details as $item)
                        <tr>
                            <td>{{ $item->no_fc }}</td>
                            <td>{{ $item->date }}</td>
                            <td>{{ $item->client }}</td>
                            <td>{{ $item->concept }}</td>
                            <td>{{ number_format($item->value, 2) }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Validation Notes -->
        @if($canValidate)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">{{ __('Overall Notes') }}</h5>
            </div>
            <div class="card-body">
                <textarea class="form-control" 
                          name="notes" 
                          rows="3" 
                          placeholder="{{ __('Add your overall notes here (required for rejection)...') }}"></textarea>
            </div>
        </div>
        @else
        <!-- Display existing notes -->
        @if($validation->accountant_notes || $validation->management_notes)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">{{ __('Validation Notes') }}</h5>
            </div>
            <div class="card-body">
                @if($validation->accountant_notes)
                <div class="mb-3">
                    <strong>{{ __('Accountant Notes:') }}</strong>
                    <p class="mb-0">{{ $validation->accountant_notes }}</p>
                </div>
                @endif
                @if($validation->management_notes)
                <div>
                    <strong>{{ __('Management Notes:') }}</strong>
                    <p class="mb-0">{{ $validation->management_notes }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
        @endif

    @if($canValidate)
    </form>
    @endif

    <!-- Action Buttons -->
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
        
        @if($canValidate)
        <button type="button" class="btn btn-danger" onclick="submitValidation('reject', {{ $validation->id }})">
            <i class="ri-close-circle-line me-1"></i> Reject
        </button>
        <button type="button" class="btn btn-success" onclick="submitValidation('approve', {{ $validation->id }})">
            <i class="ri-check-circle-line me-1"></i> Approve
        </button>
        @endif
        
        @if(auth()->user()->hasRole('admin') && 
            ($validation->accountant_status === 'rejected' || $validation->management_status === 'rejected'))
        <button type="button" class="btn btn-warning" onclick="showResendModal({{ $validation->id }})">
            <i class="ri-restart-line me-1"></i> Resend for Review
        </button>
        @endif
    </div>
</div>

