@extends('layouts.app')

@section('styles')
@endsection

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Invoice Details</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Invoice #{{ $invoice->invoice_number }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Page Header Close -->

        <!-- Start::row-1 -->
        <div class="row">
            <div class="col-xl-9">
                <div class="card custom-card">
                    <div class="card-header d-md-flex d-block">
                        <div class="h5 mb-0 d-sm-flex d-bllock align-items-center">
                            <div class="avatar avatar-sm">
                                <img src="{{ asset('build/assets/images/brand-logos/toggle-logo.png') }}" alt="">
                            </div>
                            <div class="ms-sm-2 ms-0 mt-sm-0 mt-2">
                                <div class="h6 fw-semibold mb-0">INVOICE : <span
                                        class="text-primary">#{{ $invoice->invoice_number }}</span></div>
                            </div>
                        </div>
                        <div class="ms-auto mt-md-0 mt-2">
                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm btn-warning me-1">
                                <i class="ri-edit-line me-1 align-middle d-inline-block"></i>Edit
                            </a>
                            <button class="btn btn-sm btn-secondary me-1" onclick="javascript:window.print();">Print<i
                                    class="ri-printer-line ms-1 align-middle d-inline-block"></i></button>
                            <button class="btn btn-sm btn-primary">Save As PDF<i
                                    class="ri-file-pdf-line ms-1 align-middle d-inline-block"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row gy-3">
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                        <p class="text-muted mb-2">
                                            Billing From :
                                        </p>
                                        <p class="fw-bold mb-1">
                                            {{ $invoice->business->business_name }}
                                        </p>
                                        @if($invoice->business->address)
                                        <p class="mb-1 text-muted">
                                            {{ $invoice->business->address }}
                                        </p>
                                        @endif
                                        <p class="mb-1 text-muted">
                                            {{ $invoice->business->email }}
                                        </p>
                                        <p class="mb-1 text-muted">
                                            {{ $invoice->business->whatsapp_number }}
                                        </p>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 ms-auto mt-sm-0 mt-3">
                                        <p class="text-muted mb-2">
                                            Billing To :
                                        </p>
                                        <p class="fw-bold mb-1">
                                            {{ $invoice->client->name }}
                                        </p>
                                        @if($invoice->client->address)
                                        <p class="text-muted mb-1">
                                            {{ $invoice->client->address }}
                                        </p>
                                        @endif
                                        <p class="text-muted mb-1">
                                            {{ $invoice->client->email }}
                                        </p>
                                        <p class="text-muted">
                                            {{ $invoice->client->whatsapp_number }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3">
                                <p class="fw-semibold text-muted mb-1">Invoice ID :</p>
                                <p class="fs-15 mb-1">#{{ $invoice->invoice_number }}</p>
                            </div>
                            <div class="col-xl-3">
                                <p class="fw-semibold text-muted mb-1">Date Issued :</p>
                                <p class="fs-15 mb-1">{{ $invoice->issue_date->format('d M, Y') }}</p>
                            </div>
                            <div class="col-xl-3">
                                <p class="fw-semibold text-muted mb-1">Due Date :</p>
                                <p class="fs-15 mb-1">{{ $invoice->due_date->format('d M, Y') }}</p>
                            </div>
                            <div class="col-xl-3">
                                <p class="fw-semibold text-muted mb-1">Total Amount :</p>
                                <p class="fs-16 mb-1 fw-semibold">{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</p>
                            </div>
                            <div class="col-xl-12">
                                <div class="table-responsive">
                                    <table class="table nowrap text-nowrap border mt-4">
                                        <thead>
                                            <tr>
                                                <th>PRODUCT NAME</th>
                                                <th>DESCRIPTION</th>
                                                <th>QUANTITY</th>
                                                <th>PRICE PER UNIT</th>
                                                <th>TOTAL</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $subtotal = 0;
                                            @endphp
                                            @foreach($invoice->items as $item)
                                            @php
                                                $subtotal += $item->line_total;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">
                                                        {{ $item->name }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-muted">
                                                        {{ $item->description ?? '-' }}
                                                    </div>
                                                </td>
                                                <td class="product-quantity-container">
                                                    {{ number_format($item->quantity, 2) }}
                                                </td>
                                                <td>
                                                    {{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}
                                                </td>
                                                <td>
                                                    {{ $invoice->currency }} {{ number_format($item->line_total, 2) }}
                                                </td>
                                            </tr>
                                            @endforeach
                                            <tr>
                                                <td colspan="3"></td>
                                                <td colspan="2">
                                                    <table class="table table-sm text-nowrap mb-0 table-borderless">
                                                        <tbody>
                                                            <tr>
                                                                <th scope="row">
                                                                    <p class="mb-0">Sub Total :</p>
                                                                </th>
                                                                <td>
                                                                    <p class="mb-0 fw-semibold fs-15">{{ $invoice->currency }} {{ number_format($subtotal, 2) }}</p>
                                                                </td>
                                                            </tr>
                                                            @if($invoice->discount_amount > 0)
                                                            <tr>
                                                                <th scope="row">
                                                                    <p class="mb-0">Discount
                                                                        @if($invoice->discount_type === 'percent')
                                                                            <span class="text-success">({{ number_format(($invoice->discount_amount / $subtotal) * 100, 2) }}%)</span>
                                                                        @endif
                                                                        :
                                                                    </p>
                                                                </th>
                                                                <td>
                                                                    <p class="mb-0 fw-semibold fs-15 text-danger">-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2) }}</p>
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            @if($invoice->tax_amount > 0)
                                                            <tr>
                                                                <th scope="row">
                                                                    <p class="mb-0">Tax
                                                                        @php
                                                                            $taxableAmount = $subtotal - $invoice->discount_amount;
                                                                            $taxPercentage = $taxableAmount > 0 ? ($invoice->tax_amount / $taxableAmount) * 100 : 0;
                                                                        @endphp
                                                                        <span class="text-danger">({{ number_format($taxPercentage, 2) }}%)</span>
                                                                        :
                                                                    </p>
                                                                </th>
                                                                <td>
                                                                    <p class="mb-0 fw-semibold fs-15">+{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</p>
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            <tr>
                                                                <th scope="row">
                                                                    <p class="mb-0 fs-14">Total :</p>
                                                                </th>
                                                                <td>
                                                                    <p class="mb-0 fw-semibold fs-16 text-success">
                                                                        {{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</p>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @if($invoice->notes)
                            <div class="col-xl-12">
                                <div>
                                    <label for="invoice-note" class="form-label">Note:</label>
                                    <textarea class="form-control form-control-light" id="invoice-note" rows="3" readonly>{{ $invoice->notes }}</textarea>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <a href="{{ route('invoices.index') }}" class="btn btn-secondary me-1">
                            <i class="ri-arrow-left-line me-1 align-middle"></i>Back to List
                        </a>
                        <button class="btn btn-success">Download <i
                                class="ri-download-2-line ms-1 align-middle"></i></button>
                    </div>
                </div>
            </div>
            <div class="col-xl-3">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            Invoice Information
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row gy-3">
                            <div class="col-xl-12">
                                <p class="fs-14 fw-semibold">
                                    Status
                                </p>
                                <p>
                                    @php
                                        $computedStatus = $invoice->getComputedStatus();
                                        $badgeClass = match($computedStatus) {
                                            \App\Enums\InvoiceStatus::DRAFT => 'warning',
                                            \App\Enums\InvoiceStatus::SENT => 'info',
                                            \App\Enums\InvoiceStatus::PAID => 'success',
                                            \App\Enums\InvoiceStatus::OVERDUE => 'danger',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }}-transparent">
                                        {{ $computedStatus->label() }}
                                    </span>
                                </p>
                                
                                <p class="fs-14 fw-semibold mt-3">
                                    Financial Details
                                </p>
                                <p>
                                    <span class="fw-semibold text-muted fs-12">Currency :</span> {{ $invoice->currency }}
                                </p>
                                <p>
                                    <span class="fw-semibold text-muted fs-12">Total Amount :</span> <span
                                        class="text-success fw-semibold fs-14">{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</span>
                                </p>
                                <p>
                                    <span class="fw-semibold text-muted fs-12">Issue Date :</span> {{ $invoice->issue_date->format('d M, Y') }}
                                </p>
                                <p>
                                    <span class="fw-semibold text-muted fs-12">Due Date :</span> {{ $invoice->due_date->format('d M, Y') }}
                                    @if($invoice->isOverdue())
                                        <span class="badge bg-danger-transparent ms-1">Overdue</span>
                                    @endif
                                </p>
                                
                                @if($invoice->paid_at)
                                <p>
                                    <span class="fw-semibold text-muted fs-12">Paid At :</span> {{ $invoice->paid_at->format('d M, Y H:i') }}
                                </p>
                                @endif
                                
                                @if($invoice->sent_at)
                                <p>
                                    <span class="fw-semibold text-muted fs-12">Sent At :</span> {{ $invoice->sent_at->format('d M, Y H:i') }}
                                </p>
                                @endif
                                
                                <div class="alert alert-info mt-3" role="alert">
                                    <strong>Public Link:</strong><br>
                                    <small class="text-break">{{ url('/invoice/' . $invoice->public_token) }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--End::row-1 -->

    </div>
@endsection

@section('scripts')
@endsection
