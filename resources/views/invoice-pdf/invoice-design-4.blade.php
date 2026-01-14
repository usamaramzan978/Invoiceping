<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 0;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
            background: #fff;
        }

        .page-container {
            width: 100%;
            min-height: 100%;
            padding: 40px;
        }

        /* Header Section */
        .header {
            margin-bottom: 30px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: 700;
            color: #4a7c9b;
        }

        .invoice-number {
            font-size: 32px;
            font-weight: 400;
            color: #4a7c9b;
        }

        .company-logo {
            text-align: right;
        }

        .logo-img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            vertical-align: middle;
        }

        .logo-placeholder {
            display: inline-block;
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #5ba3c8 50%, #4a7c9b 50%);
            border-radius: 50%;
            vertical-align: middle;
        }

        .company-name {
            display: inline-block;
            font-size: 24px;
            font-weight: 700;
            color: #4a7c9b;
            vertical-align: middle;
            margin-left: 8px;
        }

        /* Info Section */
        .info-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e5e5;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            vertical-align: top;
        }

        .info-label {
            font-size: 11px;
            font-weight: 400;
            color: #888;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 12px;
            color: #333;
            margin-bottom: 3px;
        }

        .info-value-company {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .info-value-link {
            color: #4a7c9b;
            text-decoration: none;
        }

        .invoice-details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-details-table td {
            padding: 3px 0;
            font-size: 12px;
        }

        .invoice-details-label {
            font-weight: 400;
            color: #888;
            text-align: right;
            padding-right: 15px;
        }

        .invoice-details-value {
            text-align: right;
            color: #333;
            font-weight: 600;
        }

        /* Summary Section */
        .summary-section {
            margin-bottom: 30px;
        }

        .summary-title {
            font-size: 12px;
            font-weight: 400;
            color: #888;
            margin-bottom: 8px;
        }

        .summary-text {
            font-size: 12px;
            color: #555;
            line-height: 1.7;
            max-width: 600px;
        }

        /* Items Section */
        .items-section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #333;
            text-align: center;
            margin-bottom: 15px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th {
            padding: 10px 12px;
            font-size: 10px;
            font-weight: 400;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .items-table th.text-center {
            text-align: center;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 12px;
            font-size: 12px;
            color: #333;
            border-bottom: 1px solid #eee;
        }

        .items-table td.text-center {
            text-align: center;
        }

        .items-table td.text-right {
            text-align: right;
        }

        .item-name {
            font-weight: 600;
        }

        .item-desc {
            font-size: 11px;
            color: #666;
        }

        /* Totals Section */
        .totals-section {
            margin-bottom: 25px;
        }

        .totals-title {
            font-size: 14px;
            font-weight: 700;
            color: #4a7c9b;
            text-align: center;
            margin-bottom: 15px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px 12px;
            font-size: 12px;
            border-bottom: 1px solid #eee;
        }

        .totals-label {
            color: #333;
        }

        .totals-value {
            text-align: right;
        }

        .totals-discount {
            color: #dc3545;
        }

        .total-due td {
            font-size: 16px;
            font-weight: 700;
            border-bottom: none;
            padding-top: 15px;
        }

        .total-due .totals-value {
            color: #4a7c9b;
        }

        /* Notes Section */
        .notes-section {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 15px;
            font-size: 11px;
            color: #555;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    @php
        $currency = $invoice->currency ?? 'USD';
        $currencySymbol = match ($currency) {
            'USD' => '$',
            'EUR' => '€',
            'PKR' => 'Rs',
            'GBP' => '£',
            'INR' => '₹',
            'AED' => 'AED ',
            'SAR' => 'SAR ',
            default => '$',
        };

        $subtotal = 0;
        foreach ($invoice->items as $item) {
            $subtotal += $item->quantity * $item->unit_price;
        }

        $discountAmount = $invoice->discount_amount ?? 0;
        $taxAmount = $invoice->tax_amount ?? 0;
        $totalAmount = $invoice->total_amount ?? 0;

        $discountPercent = 0;
        if ($invoice->discount_type && $invoice->discount_type->value === 'percent' && $subtotal > 0) {
            $discountPercent = ($discountAmount / $subtotal) * 100;
        }

        $taxPercent = 0;
        $taxableAmount = $subtotal - $discountAmount;
        if ($taxAmount > 0 && $taxableAmount > 0) {
            $taxPercent = ($taxAmount / $taxableAmount) * 100;
        }

        $logoPath = null;
        if ($invoice->business?->image) {
            $publicPath = public_path('storage/' . $invoice->business->image);
            $storagePath = storage_path('app/public/' . $invoice->business->image);
            if (file_exists($publicPath)) {
                $logoPath = $publicPath;
            } elseif (file_exists($storagePath)) {
                $logoPath = $storagePath;
            }
        }
    @endphp

    <div class="page-container">
        <!-- Header -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td style="width: 60%;">
                        <span class="invoice-title">Invoice</span>
                        <span class="invoice-number">#{{ $invoice->invoice_number }}</span>
                    </td>
                    <td style="width: 40%; text-align: right;">
                        <div class="company-logo">
                            @if ($logoPath)
                                <img src="{{ $logoPath }}" alt="Logo" class="logo-img">
                            @else
                                <span class="logo-placeholder"></span>
                            @endif
                            <span class="company-name">{{ $invoice->business?->business_name ?? 'Company' }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <table class="info-table">
                <tr>
                    <td style="width: 30%; padding-right: 20px;">
                        <div class="info-label">From</div>
                        <div class="info-value info-value-company">{{ $invoice->business?->business_name ?? 'N/A' }}</div>
                        @if ($invoice->business?->address)
                            <div class="info-value">{{ $invoice->business->address }}</div>
                        @endif
                        @if ($invoice->business?->email)
                            <div class="info-value"><a href="mailto:{{ $invoice->business->email }}" class="info-value-link">{{ $invoice->business->email }}</a></div>
                        @endif
                        @if ($invoice->business?->tax_id)
                            <div class="info-value">Tax ID: {{ $invoice->business->tax_id }}</div>
                        @endif
                    </td>
                    <td style="width: 30%; padding-right: 20px;">
                        <div class="info-label">To</div>
                        <div class="info-value info-value-company">{{ $invoice->client?->name ?? 'N/A' }}</div>
                        @if ($invoice->client?->email)
                            <div class="info-value">{{ $invoice->client->email }}</div>
                        @endif
                        @if ($invoice->client?->whatsapp_number)
                            <div class="info-value">{{ $invoice->client->whatsapp_number }}</div>
                        @endif
                    </td>
                    <td style="width: 40%;">
                        <table class="invoice-details-table">
                            <tr>
                                <td class="invoice-details-label">Invoice No.</td>
                                <td class="invoice-details-value">{{ $invoice->invoice_number }}</td>
                            </tr>
                            <tr>
                                <td class="invoice-details-label">Invoice Date</td>
                                <td class="invoice-details-value">{{ $invoice->issue_date->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td class="invoice-details-label">Payment Due</td>
                                <td class="invoice-details-value">{{ $invoice->due_date->format('M d, Y') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Summary Section -->
        @if ($invoice->notes)
            <div class="summary-section">
                <div class="summary-title">Summary</div>
                <div class="summary-text">{{ $invoice->notes }}</div>
            </div>
        @endif

        <!-- Items Section -->
        <div class="items-section">
            <div class="section-title">Invoice Items</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">ITEM DESCRIPTION</th>
                        <th class="text-center" style="width: 15%;">QTY</th>
                        <th class="text-center" style="width: 17%;">PRICE PER UNIT</th>
                        <th class="text-right" style="width: 18%;">TOTAL PRICE</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoice->items as $item)
                        @php $itemTotal = $item->quantity * $item->unit_price; @endphp
                        <tr>
                            <td>
                                <div class="item-name">{{ $item->name }}</div>
                                @if ($item->description)
                                    <div class="item-desc">{{ $item->description }}</div>
                                @endif
                            </td>
                            <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                            <td class="text-center">{{ $currencySymbol }}{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-right">{{ $currencySymbol }}{{ number_format($itemTotal, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: #888;">No items</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Totals Section -->
        <div class="totals-section">
            <div class="totals-title">Invoice Totals</div>
            <table class="totals-table">
                <tr>
                    <td class="totals-label">Invoice Subtotal</td>
                    <td class="totals-value">{{ $currencySymbol }}{{ number_format($subtotal, 2) }}</td>
                </tr>
                @if ($discountAmount > 0)
                    <tr>
                        <td class="totals-label">Discount @if ($discountPercent > 0)({{ number_format($discountPercent, 1) }}%)@endif</td>
                        <td class="totals-value totals-discount">-{{ $currencySymbol }}{{ number_format($discountAmount, 2) }}</td>
                    </tr>
                @endif
                @if ($taxAmount > 0)
                    <tr>
                        <td class="totals-label">Tax @if ($taxPercent > 0)({{ number_format($taxPercent, 1) }}%)@endif</td>
                        <td class="totals-value">{{ $currencySymbol }}{{ number_format($taxAmount, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-due">
                    <td class="totals-label">Total Due</td>
                    <td class="totals-value">{{ $currency }} {{ $currencySymbol }}{{ number_format($totalAmount, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Notes Section -->
        @if ($invoice->notes)
            <div class="notes-section">
                {{ $invoice->notes }}
            </div>
        @endif
    </div>
</body>

</html>
