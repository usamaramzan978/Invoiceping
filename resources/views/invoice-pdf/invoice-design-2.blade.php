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
            padding: 0;
        }

        /* Header with Logo */
        .logo-header {
            text-align: center;
            padding: 40px 40px 30px 40px;
            background: #fff;
        }

        .logo-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            vertical-align: middle;
        }

        .logo-placeholder {
            display: inline-block;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #e8a838 50%, #d4882a 50%);
            border-radius: 50%;
            vertical-align: middle;
        }

        .company-name {
            display: inline-block;
            font-size: 32px;
            font-weight: 700;
            color: #d4882a;
            vertical-align: middle;
            margin-left: 10px;
        }

        /* Main Content */
        .main-content {
            padding: 0 40px;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Left Sidebar */
        .left-sidebar {
            width: 200px;
            vertical-align: top;
            padding-right: 30px;
        }

        .invoice-title {
            font-size: 36px;
            font-weight: 700;
            color: #333;
            font-style: italic;
            margin-bottom: 25px;
        }

        .sidebar-label {
            font-size: 11px;
            font-weight: 700;
            color: #d4882a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .sidebar-value {
            font-size: 12px;
            color: #333;
            margin-bottom: 15px;
        }

        .sidebar-link {
            color: #d4882a;
            text-decoration: underline;
        }

        /* Right Content */
        .right-content {
            vertical-align: top;
        }

        /* Company Info - Right aligned */
        .company-info {
            text-align: right;
            margin-bottom: 30px;
        }

        .company-info-name {
            font-weight: 600;
            margin-bottom: 3px;
        }

        .company-info-text {
            color: #555;
            margin-bottom: 2px;
        }

        /* Summary Section */
        .summary-section {
            margin-bottom: 30px;
        }

        .summary-title {
            font-size: 13px;
            font-weight: 700;
            color: #d4882a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 2px solid #d4882a;
        }

        .summary-text {
            font-size: 12px;
            color: #555;
            line-height: 1.7;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            padding: 10px 12px;
            font-size: 11px;
            font-weight: 700;
            color: #d4882a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
            border-bottom: 2px solid #d4882a;
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

        /* Totals */
        .totals-row td {
            padding: 6px 12px;
            font-size: 12px;
            border-bottom: none;
        }

        .totals-label {
            text-align: right;
            color: #555;
        }

        .totals-value {
            text-align: right;
            width: 100px;
        }

        .totals-discount {
            color: #dc3545;
        }

        .totals-grand td {
            font-weight: 700;
            font-size: 14px;
            color: #d4882a;
        }

        /* Footer */
        .footer {
            padding: 50px 40px 30px 40px;
            text-align: center;
        }

        .footer-line {
            border-top: 1px solid #ddd;
            position: relative;
            margin-bottom: 15px;
        }

        .page-number {
            display: inline-block;
            width: 28px;
            height: 28px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 50%;
            text-align: center;
            line-height: 26px;
            font-size: 11px;
            color: #555;
            position: relative;
            top: -14px;
        }

        .footer-text {
            font-size: 11px;
            color: #d4882a;
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
        <!-- Logo Header -->
        <div class="logo-header">
            @if ($logoPath)
                <img src="{{ $logoPath }}" alt="Logo" class="logo-img">
            @else
                <span class="logo-placeholder"></span>
            @endif
            <span class="company-name">{{ $invoice->business?->business_name ?? 'Company' }}</span>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <table class="content-table">
                <tr>
                    <!-- Left Sidebar -->
                    <td class="left-sidebar">
                        <div class="invoice-title">INVOICE</div>

                        <div class="sidebar-label">INVOICE NO.</div>
                        <div class="sidebar-value">{{ $invoice->invoice_number }}</div>

                        <div class="sidebar-label">INVOICE DATE</div>
                        <div class="sidebar-value">{{ $invoice->issue_date->format('M d, Y') }}</div>

                        <div class="sidebar-label">PAYMENT DUE</div>
                        <div class="sidebar-value">{{ $invoice->due_date->format('M d, Y') }}</div>

                        <div class="sidebar-label">TO</div>
                        <div class="sidebar-value">
                            <strong>{{ $invoice->client?->name ?? 'N/A' }}</strong><br>
                            @if ($invoice->client?->email)
                                {{ $invoice->client->email }}<br>
                            @endif
                            @if ($invoice->client?->whatsapp_number)
                                {{ $invoice->client->whatsapp_number }}
                            @endif
                        </div>
                    </td>

                    <!-- Right Content -->
                    <td class="right-content">
                        <!-- Company Info -->
                        <div class="company-info">
                            <div class="company-info-name">{{ $invoice->business?->business_name ?? 'N/A' }}</div>
                            @if ($invoice->business?->address)
                                <div class="company-info-text">{{ $invoice->business->address }}</div>
                            @endif
                            @if ($invoice->business?->email)
                                <div class="company-info-text"><a href="mailto:{{ $invoice->business->email }}" class="sidebar-link">{{ $invoice->business->email }}</a></div>
                            @endif
                            @if ($invoice->business?->tax_id)
                                <div class="company-info-text">Tax ID: {{ $invoice->business->tax_id }}</div>
                            @endif
                        </div>

                        <!-- Summary -->
                        @if ($invoice->notes)
                            <div class="summary-section">
                                <div class="summary-title">NOTE</div>
                                <div class="summary-text">{{ $invoice->notes }}</div>
                            </div>
                        @endif

                        <!-- Items Table -->
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
                                        <td class="text-center">{{ $currencySymbol }}{{ number_format($item->unit_price, 2) }}</td>
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
                                    <td class="totals-label">Subtotal</td>
                                    <td class="totals-value">{{ $currencySymbol }}{{ number_format($subtotal, 2) }}</td>
                                </tr>
                                @if ($discountAmount > 0)
                                    <tr class="totals-row">
                                        <td colspan="2"></td>
                                        <td class="totals-label">Discount @if ($discountPercent > 0)({{ number_format($discountPercent, 1) }}%)@endif</td>
                                        <td class="totals-value totals-discount">-{{ $currencySymbol }}{{ number_format($discountAmount, 2) }}</td>
                                    </tr>
                                @endif
                                @if ($taxAmount > 0)
                                    <tr class="totals-row">
                                        <td colspan="2"></td>
                                        <td class="totals-label">Tax @if ($taxPercent > 0)({{ number_format($taxPercent, 1) }}%)@endif</td>
                                        <td class="totals-value">{{ $currencySymbol }}{{ number_format($taxAmount, 2) }}</td>
                                    </tr>
                                @endif
                                <tr class="totals-row totals-grand">
                                    <td colspan="2"></td>
                                    <td class="totals-label">Total</td>
                                    <td class="totals-value">{{ $currencySymbol }}{{ number_format($totalAmount, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-line">
                <span class="page-number">1</span>
            </div>
            <div class="footer-text">Invoice generated for {{ $invoice->client?->name ?? 'Client' }} by {{ $invoice->business?->business_name ?? 'Company' }}</div>
        </div>
    </div>
</body>

</html>
