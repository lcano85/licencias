<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Billing Report') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4472C4;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #4472C4;
            margin: 0;
            font-size: 24px;
        }
        .header .period {
            color: #666;
            margin-top: 5px;
            font-size: 12px;
        }
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background: #f8f9fa;
        }
        .summary-card h3 {
            margin: 0 0 5px 0;
            font-size: 11px;
            color: #666;
        }
        .summary-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        .summary-card.billing { border-left: 3px solid #4472C4; }
        .summary-card.cn { border-left: 3px solid #DC3545; }
        .summary-card.receipts { border-left: 3px solid #28A745; }
        .summary-card.portfolio { border-left: 3px solid #FFC107; }
        
        h2 {
            color: #4472C4;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-top: 25px;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9px;
        }
        th {
            background-color: #4472C4;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 5px 4px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #E7E6E6 !important;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('Billing Report') }}</h1>
        <div class="period">
            Period: {{ $start_date }} to {{ $end_date }}
        </div>
    </div>

    <div class="summary-cards">
        <div class="summary-card billing">
            <h3>{{ __('Total Billing') }}</h3>
            <div class="value">$ {{ number_format($totalBilling, 2) }}</div>
        </div>
        <div class="summary-card cn">
            <h3>{{ __('Credit Notes') }}</h3>
            <div class="value">$ {{ number_format($totalCN, 2) }}</div>
        </div>
        <div class="summary-card receipts">
            <h3>{{ __('Cash Receipts') }}</h3>
            <div class="value">$ {{ number_format($totalReceipts, 2) }}</div>
        </div>
        <div class="summary-card portfolio">
            <h3>{{ __('Portfolio') }}</h3>
            <div class="value">$ {{ number_format($portfolio, 2) }}</div>
        </div>
    </div>

    <h2>Invoices ({{ count($invoices) }} records)</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">{{ __('Invoice No.') }}</th>
                <th style="width: 8%;">{{ __('Date') }}</th>
                <th style="width: 12%;">{{ __('Commercial Name') }}</th>
                <th style="width: 10%;">{{ __('User Type') }}</th>
                <th style="width: 10%;">{{ __('Period') }}</th>
                <th style="width: 12%;">{{ __('Criterion') }}</th>
                <th style="width: 8%;" class="text-right">{{ __('Subtotal') }}</th>
                <th style="width: 5%;" class="text-center">{{ __('VAT %') }}</th>
                <th style="width: 8%;" class="text-right">{{ __('Total') }}</th>
                <th style="width: 8%;" class="text-right">{{ __('Balance') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalInvoiced = 0;
                $criteriaMap = [
                    1 => 'Min. Guaranteed, 8% Income',
                    2 => 'Min. Guaranteed + 8%',
                    3 => 'Monthly Fee',
                    4 => 'Annual Fee',
                    5 => 'Special Arrangement'
                ];
                $paidPeriodMap = [1 => 'M&Y', 2 => 'Year', 3 => 'Multi-Year'];
            @endphp
            
            @foreach($invoices as $invoice)
                @php
                    $budget = \App\Models\Budget::find($invoice->budgetID);
                    $userTypeName = \App\Models\UseTypes::where('id', $invoice->user_type)->value('use_types_name') ?? $invoice->user_type;
                    $period = ($paidPeriodMap[$invoice->paidPeriod] ?? 'N/A') . ($invoice->periodPaid ? ' (' . $invoice->periodPaid . ')' : '');
                    $criterion = $criteriaMap[$invoice->criterion] ?? 'N/A';
                    
                    $paid = (float) \App\Models\CashReceipt::where('invoice_id', $invoice->id)->sum('amount');
                    $cn = \App\Models\CreditNote::where('invoice_id', $invoice->id)->first();
                    $cnTotal = $cn ? (float)$cn->total : 0.0;
                    $balance = max((float)$invoice->total - $paid - $cnTotal, 0);
                    
                    $totalInvoiced += (float)$invoice->total;
                @endphp
                <tr>
                    <td>{{ $invoice->invoiceNumber ?? 'N/A' }}</td>
                    <td>{{ $invoice->invoiceDate ? \Carbon\Carbon::parse($invoice->invoiceDate)->format('d-m-Y') : 'N/A' }}</td>
                    <td>{{ $invoice->commercialName ?? 'N/A' }}</td>
                    <td>{{ $userTypeName }}</td>
                    <td>{{ $period }}</td>
                    <td>{{ $criterion }}</td>
                    <td class="text-right">$ {{ number_format($invoice->subTotal, 2) }}</td>
                    <td class="text-center">{{ $invoice->vat }}%</td>
                    <td class="text-right">$ {{ number_format($invoice->total, 2) }}</td>
                    <td class="text-right">$ {{ number_format($balance, 2) }}</td>
                </tr>
            @endforeach
            
            <tr class="total-row">
                <td colspan="8" class="text-right"><strong>{{ __('TOTAL INVOICED:') }}</strong></td>
                <td class="text-right"><strong>$ {{ number_format($totalInvoiced, 2) }}</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    @if(count($creditNotes) > 0)
    <div class="page-break"></div>
    
    <h2>Credit Notes ({{ count($creditNotes) }} records)</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">{{ __('CN No.') }}</th>
                <th style="width: 8%;">{{ __('Date') }}</th>
                <th style="width: 15%;">{{ __('Commercial Name') }}</th>
                <th style="width: 12%;">{{ __('User Type') }}</th>
                <th style="width: 12%;">{{ __('Period') }}</th>
                <th style="width: 15%;">{{ __('Reason') }}</th>
                <th style="width: 8%;" class="text-right">{{ __('Subtotal') }}</th>
                <th style="width: 5%;" class="text-center">{{ __('VAT %') }}</th>
                <th style="width: 10%;" class="text-right">{{ __('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalCNAmount = 0;
            @endphp
            
            @foreach($creditNotes as $cn)
                @php
                    $invoice = $cn->invoice;
                    $userTypeName = \App\Models\UseTypes::where('id', $invoice?->user_type)->value('use_types_name') ?? $invoice?->user_type ?? 'N/A';
                    $period = ($paidPeriodMap[$invoice?->paidPeriod] ?? 'N/A') . ($invoice?->periodPaid ? ' (' . $invoice->periodPaid . ')' : '');
                    $totalCNAmount += (float)$cn->total;
                @endphp
                <tr>
                    <td>{{ $cn->cn_number }}</td>
                    <td>{{ $cn->cn_date ? \Carbon\Carbon::parse($cn->cn_date)->format('d-m-Y') : 'N/A' }}</td>
                    <td>{{ $invoice?->commercialName ?? 'N/A' }}</td>
                    <td>{{ $userTypeName }}</td>
                    <td>{{ $period }}</td>
                    <td>{{ $cn->reason ?? '—' }}</td>
                    <td class="text-right">$ {{ number_format($cn->subTotal, 2) }}</td>
                    <td class="text-center">{{ $cn->vat }}%</td>
                    <td class="text-right">$ {{ number_format($cn->total, 2) }}</td>
                </tr>
            @endforeach
            
            <tr class="total-row">
                <td colspan="8" class="text-right"><strong>{{ __('TOTAL CREDIT NOTES:') }}</strong></td>
                <td class="text-right"><strong>$ {{ number_format($totalCNAmount, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>
    @endif

    <div class="footer">
        Generated on: {{ $generated_at }}<br>
        This is a system-generated report
    </div>
</body>
</html>