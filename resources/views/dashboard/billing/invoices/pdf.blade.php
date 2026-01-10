<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            margin-bottom: 40px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            color: #4f46e5;
        }
        .header-info {
            margin-top: 20px;
        }
        .header-info table {
            width: 100%;
        }
        .header-info td {
            padding: 5px 0;
        }
        .company-info {
            text-align: left;
        }
        .invoice-info {
            text-align: right;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .billing-info {
            margin: 30px 0;
        }
        .billing-info table {
            width: 100%;
        }
        .billing-info td {
            padding: 5px 0;
            vertical-align: top;
        }
        .period-info {
            background-color: #f3f4f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .items-table th {
            background-color: #4f46e5;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table tfoot td {
            font-weight: bold;
            padding: 10px 12px;
        }
        .items-table tfoot tr:last-child {
            background-color: #eef2ff;
        }
        .items-table tfoot tr:last-child td {
            font-size: 16px;
            color: #4f46e5;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #10b981;
            color: white;
        }
        .status-pending {
            background-color: #f59e0b;
            color: white;
        }
        .status-overdue {
            background-color: #ef4444;
            color: white;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%;" class="company-info">
                        <h1>{{ config('app.name') }}</h1>
                        <p style="margin: 5px 0;">InvoicePing Inc.</p>
                        <p style="margin: 5px 0;">invoicing@example.com</p>
                    </td>
                    <td style="width: 50%; text-align: right;" class="invoice-info">
                        <h2 style="color: #4f46e5; margin: 0 0 15px 0;">INVOICE</h2>
                        <p style="margin: 5px 0;"><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                        <p style="margin: 5px 0;"><strong>Issue Date:</strong> {{ $invoice->created_at->format('M d, Y') }}</p>
                        <p style="margin: 5px 0;"><strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}</p>
                        <p style="margin: 10px 0 0 0;">
                            @if ($invoice->isPaid())
                                <span class="status-badge status-paid">Paid</span>
                            @elseif ($invoice->isOverdue())
                                <span class="status-badge status-overdue">Overdue</span>
                            @else
                                <span class="status-badge status-pending">Pending</span>
                            @endif
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Billing Information --}}
        <div class="billing-info">
            <table>
                <tr>
                    <td style="width: 50%;">
                        <div class="section-title">Bill To</div>
                        <p style="margin: 5px 0;"><strong>{{ $invoice->user->name }}</strong></p>
                        <p style="margin: 5px 0;">{{ $invoice->user->email }}</p>
                    </td>
                    @if ($invoice->subscription)
                        <td style="width: 50%; text-align: right;">
                            <div class="section-title">Subscription</div>
                            <p style="margin: 5px 0;"><strong>{{ $invoice->subscription->plan->name }}</strong></p>
                            <p style="margin: 5px 0; color: #6b7280;">{{ ucfirst($invoice->subscription->billing_cycle->value) }} billing</p>
                        </td>
                    @endif
                </tr>
            </table>
        </div>

        {{-- Billing Period --}}
        <div class="period-info">
            <strong>Billing Period:</strong> 
            {{ $invoice->period_start->format('F d, Y') }} - {{ $invoice->period_end->format('F d, Y') }}
        </div>

        {{-- Invoice Items --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right" style="width: 150px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>
                            @if ($invoice->subscription)
                                {{ $invoice->subscription->plan->name }} Subscription
                            @else
                                Subscription Fee
                            @endif
                        </strong>
                        <div style="font-size: 10px; color: #6b7280; margin-top: 5px;">
                            {{ $invoice->period_start->format('M d, Y') }} - {{ $invoice->period_end->format('M d, Y') }}
                        </div>
                    </td>
                    <td class="text-right">${{ number_format((float) $invoice->subtotal, 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-right">Subtotal:</td>
                    <td class="text-right">${{ number_format((float) $invoice->subtotal, 2) }}</td>
                </tr>
                @if ($invoice->discount > 0)
                    <tr>
                        <td class="text-right">Discount:</td>
                        <td class="text-right" style="color: #10b981;">-${{ number_format((float) $invoice->discount, 2) }}</td>
                    </tr>
                @endif
                @if ($invoice->tax > 0)
                    <tr>
                        <td class="text-right">Tax:</td>
                        <td class="text-right">${{ number_format((float) $invoice->tax, 2) }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="text-right">Total:</td>
                    <td class="text-right">${{ number_format((float) $invoice->total, 2) }} {{ strtoupper($invoice->currency) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Payment Status --}}
        @if ($invoice->isPaid())
            <div style="background-color: #d1fae5; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <strong style="color: #065f46;">Payment Received</strong>
                <p style="margin: 5px 0 0 0; color: #065f46;">This invoice was paid on {{ $invoice->paid_at->format('F d, Y \a\t H:i') }}</p>
            </div>
        @elseif ($invoice->isOverdue())
            <div style="background-color: #fee2e2; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <strong style="color: #991b1b;">Payment Overdue</strong>
                <p style="margin: 5px 0 0 0; color: #991b1b;">This invoice was due on {{ $invoice->due_date->format('F d, Y') }}</p>
            </div>
        @else
            <div style="background-color: #fef3c7; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <strong style="color: #92400e;">Payment Pending</strong>
                <p style="margin: 5px 0 0 0; color: #92400e;">This invoice is due on {{ $invoice->due_date->format('F d, Y') }}</p>
            </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <p style="margin: 0;">Thank you for your business!</p>
            <p style="margin: 5px 0 0 0;">If you have any questions about this invoice, please contact us at billing@invoiceping.com</p>
        </div>
    </div>
</body>
</html>

