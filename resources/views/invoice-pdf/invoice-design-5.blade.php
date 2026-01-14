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
            background: #e8f4f8;
        }

        /* Wave Header */
        .wave-header {
            height: 60px;
            background: linear-gradient(180deg, #a8d4e6 0%, #c5e3ed 50%, #e8f4f8 100%);
        }

        /* Main Content */
        .main-content {
            padding: 30px 40px;
        }

        /* Header Section */
        .header {
            margin-bottom: 25px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-title {
            font-size: 36px;
            font-weight: 700;
            color: #4a8fa8;
            font-style: italic;
        }

        .invoice-number {
            font-size: 24px;
            font-weight: 400;
            color: #4a8fa8;
            font-style: italic;
            margin-left: 5px;
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
            background: linear-gradient(135deg, #5fb8d4 50%, #4a8fa8 50%);
            border-radius: 50%;
            vertical-align: middle;
        }

        .company-name {
            display: inline-block;
            font-size: 24px;
            font-weight: 700;
            color: #4a8fa8;
            vertical-align: middle;
            margin-left: 8px;
        }

        /* Info Section */
        .info-section {
            margin-bottom: 20px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            vertical-align: top;
            padding: 5px 0;
        }

        .info-label {
            font-size: 11px;
            font-weight: 700;
            color: #4a8fa8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .info-value {
            font-size: 12px;
            color: #333;
            margin-bottom: 3px;
        }

        .info-value-company {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .info-value-link {
            color: #4a8fa8;
            text-decoration: none;
        }

        .invoice-details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-details-table td {
            padding: 4px 0;
            font-size: 12px;
        }

        .invoice-details-label {
            font-weight: 700;
            color: #4a8fa8;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.3px;
            text-align: right;
            padding-right: 15px;
        }

        .invoice-details-value {
            text-align: right;
            color: #333;
        }

        /* Summary Section */
        .summary-section {
            margin-bottom: 25px;
        }

        .summary-title {
            font-size: 13px;
            font-weight: 700;
            color: #4a8fa8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .summary-text {
            font-size: 12px;
            color: #555;
            line-height: 1.7;
            max-width: 600px;
        }

        /* Items Table Section */
        .items-section {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table thead tr {
            background: #e8f4f8;
        }

        .items-table th {
            padding: 12px 15px;
            font-size: 11px;
            font-weight: 700;
            color: #4a8fa8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
            border-bottom: 2px solid #4a8fa8;
        }

        .items-table th.text-center {
            text-align: center;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 12px 15px;
            font-size: 12px;
            color: #333;
            border-bottom: 1px solid #e9ecef;
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

        /* Totals in Items Section */
        .totals-row td {
            border-bottom: none;
            padding-top: 8px;
        }

        .totals-label {
            text-align: right;
            font-weight: 600;
            color: #555;
        }

        .totals-value {
            text-align: right;
            width: 120px;
        }

        .totals-discount {
            color: #dc3545;
        }

        .totals-grand td {
            font-size: 14px;
            font-weight: 700;
            color: #4a8fa8;
            border-top: 2px solid #4a8fa8;
            padding-top: 12px;
        }

        /* Footer Section */
        .footer-section {
            background: #e8f4f8;
            padding: 30px 40px;
            text-align: center;
        }

        .footer-line {
            border-top: 1px solid #c5e3ed;
            position: relative;
            margin-bottom: 15px;
        }

        .page-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background: #fff;
            border: 2px solid #4a8fa8;
            border-radius: 50%;
            text-align: center;
            line-height: 26px;
            font-size: 12px;
            color: #4a8fa8;
            position: relative;
            top: -15px;
        }

        .footer-text {
            font-size: 11px;
            color: #4a8fa8;
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
        <!-- Wave Header -->
        <div class="wave-header"></div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <table class="header-table">
                    <tr>
                        <td style="width: 60%;">
                            <span class="invoice-title">INVOICE</span>
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
                            <div class="info-label">FROM</div>
                            <div class="info-value info-value-company">{{ $invoice->business?->business_name ?? 'N/A' }}
                            </div>
                            @if ($invoice->business?->address)
                                <div class="info-value">{{ $invoice->business->address }}</div>
                            @endif
                            @if ($invoice->business?->email)
                                <div class="info-value"><a href="mailto:{{ $invoice->business->email }}"
                                        class="info-value-link">{{ $invoice->business->email }}</a></div>
                            @endif
                            @if ($invoice->business?->tax_id)
                                <div class="info-value">Tax ID: {{ $invoice->business->tax_id }}</div>
                            @endif
                        </td>
                        <td style="width: 30%; padding-right: 20px;">
                            <div class="info-label">TO</div>
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
                                    <td class="invoice-details-label">INVOICE NO.</td>
                                    <td class="invoice-details-value">{{ $invoice->invoice_number }}</td>
                                </tr>
                                <tr>
                                    <td class="invoice-details-label">INVOICE DATE</td>
                                    <td class="invoice-details-value">{{ $invoice->issue_date->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="invoice-details-label">PAYMENT DUE</td>
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
                    <div class="summary-title">NOTE</div>
                    <div class="summary-text">{{ $invoice->notes }}</div>
                </div>
            @endif

            <!-- Items Section -->
            <div class="items-section">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">DESCRIPTION</th>
                            <th class="text-center" style="width: 15%;">QTY</th>
                            <th class="text-center" style="width: 17%;">UNIT PRICE</th>
                            <th class="text-right" style="width: 18%;">TOTAL</th>
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
                                <td class="text-center">{{ $currencySymbol }}{{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="text-right">{{ $currencySymbol }}{{ number_format($itemTotal, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: #888;">No items</td>
                            </tr>
                        @endforelse
                        <!-- Totals -->
                        <tr class="totals-row">
                            <td colspan="2"></td>
                            <td class="totals-label">Subtotal:</td>
                            <td class="totals-value">{{ $currencySymbol }}{{ number_format($subtotal, 2) }}</td>
                        </tr>
                        @if ($discountAmount > 0)
                            <tr class="totals-row">
                                <td colspan="2"></td>
                                <td class="totals-label">Discount @if ($discountPercent > 0)
                                        ({{ number_format($discountPercent, 1) }}%)
                                    @endif:</td>
                                <td class="totals-value totals-discount">
                                    -{{ $currencySymbol }}{{ number_format($discountAmount, 2) }}</td>
                            </tr>
                        @endif
                        @if ($taxAmount > 0)
                            <tr class="totals-row">
                                <td colspan="2"></td>
                                <td class="totals-label">Tax @if ($taxPercent > 0)
                                        ({{ number_format($taxPercent, 1) }}%)
                                    @endif:</td>
                                <td class="totals-value">{{ $currencySymbol }}{{ number_format($taxAmount, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="totals-row totals-grand">
                            <td colspan="2"></td>
                            <td class="totals-label">Total:</td>
                            <td class="totals-value">{{ $currencySymbol }}{{ number_format($totalAmount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-section">
            <div class="footer-line">
                <span class="page-number">1</span>
            </div>
            <div class="footer-text">Invoice generated for {{ $invoice->client?->name ?? 'Client' }} by
                {{ $invoice->business?->business_name ?? 'Company' }}</div>
        </div>
    </div>
</body>

</html>
