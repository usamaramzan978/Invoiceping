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
            background: #f0f1f7;
        }

        .page-container {
            width: 100%;
            min-height: 100%;
            padding: 20px;
            background: #f0f1f7;
        }

        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(10, 10, 10, 0.04);
        }

        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f3f3f3;
        }

        .card-body {
            padding: 20px;
        }

        /* Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-img {
            width: 35px;
            height: 35px;
            border-radius: 6px;
            vertical-align: middle;
        }

        .logo-placeholder {
            display: inline-block;
            width: 35px;
            height: 35px;
            background: #845adf;
            border-radius: 6px;
            vertical-align: middle;
        }

        .invoice-title {
            font-size: 14px;
            font-weight: 600;
            color: #333335;
            vertical-align: middle;
            padding-left: 10px;
        }

        .invoice-number {
            color: #845adf;
        }

        /* Billing Section */
        .billing-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .billing-table td {
            vertical-align: top;
            padding: 10px 0;
        }

        .section-label {
            color: #8c9097;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .company-name {
            font-weight: 700;
            color: #333335;
            margin-bottom: 4px;
        }

        .text-muted {
            color: #8c9097;
            font-size: 11px;
            margin-bottom: 3px;
        }

        .tax-link {
            color: #845adf;
            font-weight: 600;
        }

        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1px solid #f3f3f3;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .info-table td {
            padding: 8px 5px;
            vertical-align: top;
        }

        .info-label {
            font-weight: 600;
            color: #8c9097;
            font-size: 11px;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 13px;
            color: #333335;
        }

        .info-value-large {
            font-size: 14px;
            font-weight: 600;
            color: #333335;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .items-table th {
            background: #fafafa;
            padding: 10px 12px;
            font-size: 10px;
            font-weight: 600;
            color: #8c9097;
            text-transform: uppercase;
            text-align: left;
            border-bottom: 1px solid #f3f3f3;
        }

        .items-table th.text-center {
            text-align: center;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 10px 12px;
            font-size: 12px;
            color: #333335;
            border-bottom: 1px solid #f3f3f3;
            vertical-align: middle;
        }

        .items-table td.text-center {
            text-align: center;
        }

        .items-table td.text-right {
            text-align: right;
        }

        .item-name {
            font-weight: 600;
            color: #333335;
        }

        .item-desc {
            color: #8c9097;
            font-size: 11px;
        }

        /* Summary Table */
        .summary-table {
            width: 250px;
            margin-left: auto;
            border-collapse: collapse;
        }

        .summary-row td {
            padding: 6px 12px;
            font-size: 12px;
        }

        .summary-label {
            text-align: right;
            font-weight: 600;
            color: #333335;
        }

        .summary-value {
            text-align: right;
            color: #333335;
            width: 100px;
        }

        .text-success {
            color: #26bf94;
        }

        .text-danger {
            color: #e6533c;
        }

        .total-row .summary-label {
            font-size: 13px;
        }

        .total-row .summary-value {
            font-size: 15px;
            color: #26bf94;
        }

        /* Note Section */
        .note-section {
            margin-top: 20px;
        }

        .note-label {
            font-weight: 600;
            color: #333335;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .note-box {
            background: #f3f6f8;
            border: 1px solid #e9edf6;
            border-radius: 6px;
            padding: 12px;
            font-size: 11px;
            color: #333335;
            line-height: 1.5;
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
        <div class="card">
            <!-- Card Header -->
            <div class="card-header">
                <table class="header-table">
                    <tr>
                        <td style="width: 40px; vertical-align: middle;">
                            @if ($logoPath)
                                <img src="{{ $logoPath }}" alt="Logo" class="logo-img">
                            @else
                                <span class="logo-placeholder"></span>
                            @endif
                        </td>
                        <td class="invoice-title">
                            INVOICE : <span class="invoice-number">#{{ $invoice->invoice_number }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Card Body -->
            <div class="card-body">
                <!-- Billing Info -->
                <table class="billing-table">
                    <tr>
                        <td style="width: 50%;">
                            <div class="section-label">Billing From :</div>
                            <div class="company-name">{{ $invoice->business?->business_name ?? 'N/A' }}</div>
                            @if ($invoice->business?->address)
                                <div class="text-muted">{{ $invoice->business->address }}</div>
                            @endif
                            @if ($invoice->business?->email)
                                <div class="text-muted">{{ $invoice->business->email }}</div>
                            @endif
                            @if ($invoice->business?->whatsapp_number)
                                <div class="text-muted">{{ $invoice->business->whatsapp_number }}</div>
                            @endif
                            @if ($invoice->business?->tax_id)
                                <div class="text-muted">Tax ID: <span
                                        class="tax-link">{{ $invoice->business->tax_id }}</span></div>
                            @endif
                        </td>
                        <td style="width: 50%; text-align: right;">
                            <div class="section-label">Billing To :</div>
                            <div class="company-name">{{ $invoice->client?->name ?? 'N/A' }}</div>
                            @if ($invoice->client?->email)
                                <div class="text-muted">{{ $invoice->client->email }}</div>
                            @endif
                            @if ($invoice->client?->whatsapp_number)
                                <div class="text-muted">{{ $invoice->client->whatsapp_number }}</div>
                            @endif
                        </td>
                    </tr>
                </table>

                <!-- Invoice Details -->
                <table class="info-table">
                    <tr>
                        <td style="width: 25%;">
                            <div class="info-label">Invoice ID :</div>
                            <div class="info-value">#{{ $invoice->invoice_number }}</div>
                        </td>
                        <td style="width: 25%;">
                            <div class="info-label">Date Issued :</div>
                            <div class="info-value">{{ $invoice->issue_date->format('d M, Y') }}</div>
                        </td>
                        <td style="width: 25%;">
                            <div class="info-label">Due Date :</div>
                            <div class="info-value">{{ $invoice->due_date->format('d M, Y') }}</div>
                        </td>
                        <td style="width: 25%; text-align: right;">
                            <div class="info-label">Due Amount :</div>
                            <div class="info-value-large">{{ $currencySymbol }}{{ number_format($totalAmount, 2) }}
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Items Table -->
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 45%;">PRODUCT</th>
                            <th class="text-center" style="width: 15%;">QTY</th>
                            <th class="text-center" style="width: 20%;">UNIT PRICE</th>
                            <th class="text-right" style="width: 20%;">TOTAL</th>
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
                                <td colspan="4" style="text-align: center; color: #8c9097; padding: 20px;">No items
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Summary -->
                <table class="summary-table">
                    <tr class="summary-row">
                        <td class="summary-label">Sub Total :</td>
                        <td class="summary-value">{{ $currencySymbol }}{{ number_format($subtotal, 2) }}</td>
                    </tr>
                    @if ($discountAmount > 0)
                        <tr class="summary-row">
                            <td class="summary-label">Discount @if ($discountPercent > 0)
                                    <span class="text-success">({{ number_format($discountPercent, 1) }}%)</span>
                                @endif :</td>
                            <td class="summary-value text-danger">
                                -{{ $currencySymbol }}{{ number_format($discountAmount, 2) }}</td>
                        </tr>
                    @endif
                    @if ($taxAmount > 0)
                        <tr class="summary-row">
                            <td class="summary-label">Tax @if ($taxPercent > 0)
                                    <span class="text-danger">({{ number_format($taxPercent, 1) }}%)</span>
                                @endif :</td>
                            <td class="summary-value">{{ $currencySymbol }}{{ number_format($taxAmount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="summary-row total-row">
                        <td class="summary-label">Total :</td>
                        <td class="summary-value text-success">
                            {{ $currencySymbol }}{{ number_format($totalAmount, 2) }}</td>
                    </tr>
                </table>

                <!-- Note Section -->
                @if ($invoice->notes)
                    <div class="note-section">
                        <div class="note-label">Note:</div>
                        <div class="note-box">{{ $invoice->notes }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>

</html>
