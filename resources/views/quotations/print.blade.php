<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation #{{ $quotation->quotation_number }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #242424;
            line-height: 1.5;
            margin: 0;
            padding: 40px;
            background: #fff;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #280047;
            padding-bottom: 24px;
            margin-bottom: 30px;
        }
        .brand-title {
            font-size: 28px;
            font-weight: 800;
            color: #280047;
            margin: 0;
        }
        .quote-badge {
            font-size: 20px;
            font-weight: 700;
            color: #F74D01;
            text-align: right;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .info-box h3 {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6B7280;
            margin: 0 0 8px 0;
        }
        .info-box p {
            margin: 2px 0;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background-color: #280047;
            color: #ffffff;
            text-align: left;
            padding: 10px 14px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        td {
            padding: 12px 14px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 14px;
        }
        .text-right {
            text-align: right;
        }
        .totals-table {
            width: 320px;
            margin-left: auto;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 6px 12px;
            border: none;
        }
        .total-row {
            font-size: 18px;
            font-weight: 800;
            color: #280047;
            border-top: 2px solid #280047 !important;
        }
        .terms-box {
            background-color: #F9FAFB;
            border-left: 4px solid #F74D01;
            padding: 16px;
            font-size: 12px;
            color: #4B5563;
            margin-top: 40px;
            white-space: pre-line;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="background-color: #F74D01; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;">
            Print / Save as PDF
        </button>
    </div>

    <div class="header">
        <div>
            <h1 class="brand-title">{{ $organization->name }}</h1>
            <p style="margin: 4px 0; color: #6B7280; font-size: 13px;">{{ $organization->billing_email }} | {{ $organization->phone }}</p>
        </div>
        <div class="quote-badge">
            QUOTATION
            <div style="font-size: 14px; color: #242424; font-weight: normal; margin-top: 4px;">#{{ $quotation->quotation_number }}</div>
            <div style="font-size: 12px; color: #6B7280; font-weight: normal;">Date: {{ $quotation->created_at->format('M d, Y') }}</div>
            @if($quotation->validity_date)
                <div style="font-size: 12px; color: #DC2626; font-weight: normal;">Valid Until: {{ $quotation->validity_date->format('M d, Y') }}</div>
            @endif
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <h3>Issued To:</h3>
            <p><strong>{{ $quotation->customer_name }}</strong></p>
            @if($quotation->company_name)<p>{{ $quotation->company_name }}</p>@endif
            @if($quotation->customer_email)<p>{{ $quotation->customer_email }}</p>@endif
            @if($quotation->customer_phone)<p>{{ $quotation->customer_phone }}</p>@endif
        </div>
        <div class="info-box" style="text-align: right;">
            <h3>Quotation Details:</h3>
            <p>Status: <strong style="text-transform: capitalize;">{{ $quotation->status->value }}</strong></p>
            <p>Currency: <strong>{{ $quotation->currency }}</strong></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th class="text-right" style="width: 15%;">Quantity</th>
                <th class="text-right" style="width: 15%;">Unit Price</th>
                <th class="text-right" style="width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ number_format((float) $item->quantity, 2) }}</td>
                    <td class="text-right">{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="text-right"><strong>{{ number_format((float) $item->total_price, 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td style="color: #6B7280;">Subtotal:</td>
            <td class="text-right">{{ $quotation->currency }} {{ number_format((float) $quotation->subtotal, 2) }}</td>
        </tr>
        @if((float) $quotation->discount_amount > 0)
            <tr>
                <td style="color: #10B981;">Discount:</td>
                <td class="text-right" style="color: #10B981;">- {{ $quotation->currency }} {{ number_format((float) $quotation->discount_amount, 2) }}</td>
            </tr>
        @endif
        @if((float) $quotation->tax_rate > 0)
            <tr>
                <td style="color: #6B7280;">VAT Tax ({{ $quotation->tax_rate }}%):</td>
                <td class="text-right">{{ $quotation->currency }} {{ number_format((float) $quotation->tax_amount, 2) }}</td>
            </tr>
        @endif
        <tr class="total-row">
            <td>Grand Total:</td>
            <td class="text-right">{{ $quotation->currency }} {{ number_format((float) $quotation->total_amount, 2) }}</td>
        </tr>
    </table>

    @if($quotation->terms_and_conditions)
        <div class="terms-box">
            <strong>Terms & Conditions:</strong>
            {{ $quotation->terms_and_conditions }}
        </div>
    @endif
</body>
</html>
