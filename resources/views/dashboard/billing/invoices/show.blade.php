@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center my-4">
            <div>
                <h5 class="fw-semibold mb-1">Invoice #{{ $invoice->invoice_number }}</h5>
                <p class="text-muted mb-0">View invoice details</p>
            </div>
            <div class="btn-group" role="group">
                <a href="{{ route('billing.invoices.index') }}" class="btn btn-light">
                    <i class="bx bx-arrow-back me-1"></i>Back
                </a>
                @if ($invoice->isPaid())
                    <a href="{{ route('billing.invoices.download', $invoice->id) }}" class="btn btn-primary">
                        <i class="bx bx-download me-1"></i>Download PDF
                    </a>
                @endif
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-body p-5">
                {{-- Invoice Header --}}
                <div class="row mb-5">
                    <div class="col-md-6">
                        <h2 class="mb-3">{{ config('app.name') }}</h2>
                        <p class="text-muted mb-0">InvoicePing Inc.</p>
                        <p class="text-muted mb-0">invoicing@example.com</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h3 class="text-primary mb-3">INVOICE</h3>
                        <p class="mb-1"><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                        <p class="mb-1"><strong>Issue Date:</strong> {{ $invoice->created_at->format('M d, Y') }}</p>
                        <p class="mb-1"><strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}</p>
                        <p class="mb-0">
                            <span class="badge bg-{{ $invoice->status->color() }} fs-6">
                                {{ $invoice->status->label() }}
                            </span>
                        </p>
                    </div>
                </div>

                <hr>

                {{-- Bill To Section --}}
                <div class="row mb-5">
                    <div class="col-md-6">
                        <h6 class="mb-3">Bill To:</h6>
                        <p class="mb-0"><strong>{{ $invoice->user->name }}</strong></p>
                        <p class="mb-0">{{ $invoice->user->email }}</p>
                    </div>
                    @if ($invoice->subscription)
                        <div class="col-md-6 text-md-end">
                            <h6 class="mb-3">Subscription:</h6>
                            <p class="mb-0"><strong>{{ $invoice->subscription->plan->name }}</strong></p>
                            <p class="mb-0 text-muted">{{ ucfirst($invoice->subscription->billing_cycle->value) }} billing</p>
                        </div>
                    @endif
                </div>

                {{-- Billing Period --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-light">
                            <strong>Billing Period:</strong> 
                            {{ $invoice->period_start->format('F d, Y') }} - {{ $invoice->period_end->format('F d, Y') }}
                        </div>
                    </div>
                </div>

                {{-- Invoice Items --}}
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Description</th>
                                <th class="text-end" width="150">Amount</th>
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
                                    <div class="small text-muted">
                                        {{ $invoice->period_start->format('M d, Y') }} - {{ $invoice->period_end->format('M d, Y') }}
                                    </div>
                                </td>
                                <td class="text-end">${{ number_format((float) $invoice->subtotal, 2) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-end">Subtotal:</th>
                                <td class="text-end">${{ number_format((float) $invoice->subtotal, 2) }}</td>
                            </tr>
                            @if ($invoice->discount > 0)
                                <tr>
                                    <th class="text-end">Discount:</th>
                                    <td class="text-end text-success">-${{ number_format((float) $invoice->discount, 2) }}</td>
                                </tr>
                            @endif
                            @if ($invoice->tax > 0)
                                <tr>
                                    <th class="text-end">Tax:</th>
                                    <td class="text-end">${{ number_format((float) $invoice->tax, 2) }}</td>
                                </tr>
                            @endif
                            <tr class="table-primary">
                                <th class="text-end fs-5">Total:</th>
                                <td class="text-end fs-5 fw-bold">${{ number_format((float) $invoice->total, 2) }} {{ strtoupper($invoice->currency) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Payment Information --}}
                @if ($invoice->isPaid())
                    <div class="alert alert-success d-flex align-items-center">
                        <i class="bx bx-check-circle fs-4 me-3"></i>
                        <div>
                            <strong>Payment Received</strong>
                            <p class="mb-0">This invoice was paid on {{ $invoice->paid_at->format('F d, Y \a\t H:i') }}</p>
                        </div>
                    </div>
                @elseif ($invoice->isOverdue())
                    <div class="alert alert-danger d-flex align-items-center">
                        <i class="bx bx-error-circle fs-4 me-3"></i>
                        <div>
                            <strong>Payment Overdue</strong>
                            <p class="mb-0">This invoice was due on {{ $invoice->due_date->format('F d, Y') }}</p>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning d-flex align-items-center">
                        <i class="bx bx-time fs-4 me-3"></i>
                        <div>
                            <strong>Payment Pending</strong>
                            <p class="mb-0">This invoice is due on {{ $invoice->due_date->format('F d, Y') }}</p>
                        </div>
                    </div>
                @endif

                {{-- Footer Notes --}}
                <div class="row mt-5">
                    <div class="col-12">
                        <hr>
                        <p class="text-muted small text-center mb-0">
                            Thank you for your business!<br>
                            If you have any questions about this invoice, please contact us at billing@invoiceping.com
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

