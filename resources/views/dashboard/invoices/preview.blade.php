@extends('layouts.app')

@section('styles')
    <style>
        .preview-watermark {
            position: relative;
        }

        .preview-watermark::before {
            content: 'PREVIEW';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 4rem;
            font-weight: bold;
            color: rgba(255, 0, 0, 0.1);
            z-index: 2;
            pointer-events: none;
            white-space: nowrap;
        }

        .preview-watermark>* {
            position: relative;
            z-index: 1;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Invoice Preview</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Invoice</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Preview</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Page Header Close -->

        <!-- Alert Banner -->
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong><i class="ri-alert-line me-2"></i>Preview Mode:</strong> This is a preview only. No data has been saved
            to the database.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <!-- Start::row-1 -->
        <div class="row">
            <div class="col-xl-9">
                <div class="card custom-card preview-watermark">
                    <div class="card-header d-md-flex d-block">
                        <div class="h5 mb-0 d-sm-flex d-bllock align-items-center">
                            <div class="avatar avatar-sm">
                                <img src="{{ asset('build/assets/images/brand-logos/toggle-logo.png') }}" alt="">
                            </div>
                            <div class="ms-sm-2 ms-0 mt-sm-0 mt-2">
                                <div class="h6 fw-semibold mb-0">INVOICE : <span
                                        class="text-primary">#{{ $invoiceData->invoice_number }}</span></div>
                            </div>
                        </div>
                        <div class="ms-auto mt-md-0 mt-2">
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
                                            {{ $invoiceData->billing_from['name'] ?? 'N/A' }}
                                        </p>
                                        @if (!empty($invoiceData->billing_from['address']))
                                            <p class="mb-1 text-muted">
                                                {{ $invoiceData->billing_from['address'] }}
                                            </p>
                                        @endif
                                        <p class="mb-1 text-muted">
                                            {{ $invoiceData->billing_from['email'] ?? 'N/A' }}
                                        </p>
                                        <p class="mb-1 text-muted">
                                            {{ $invoiceData->billing_from['phone'] ?? 'N/A' }}
                                        </p>
                                        @if (!empty($invoiceData->billing_from['subject']))
                                            <p class="text-muted mt-2">
                                                <strong>Subject:</strong> {{ $invoiceData->billing_from['subject'] }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 ms-auto mt-sm-0 mt-3">
                                        <p class="text-muted mb-2">
                                            Billing To :
                                        </p>
                                        <p class="fw-bold mb-1">
                                            {{ $invoiceData->billing_to['name'] ?? 'N/A' }}
                                        </p>
                                        <p class="text-muted mb-1">
                                            {{ $invoiceData->billing_to['email'] ?? 'N/A' }}
                                        </p>
                                        <p class="text-muted">
                                            {{ $invoiceData->billing_to['phone'] ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3">
                                <p class="fw-semibold text-muted mb-1">Invoice ID :</p>
                                <p class="fs-15 mb-1">#{{ $invoiceData->invoice_number }}</p>
                            </div>
                            <div class="col-xl-3">
                                <p class="fw-semibold text-muted mb-1">Date Issued :</p>
                                <p class="fs-15 mb-1">
                                    {{ \Carbon\Carbon::parse($invoiceData->issue_date)->format('d M, Y') }}</p>
                            </div>
                            <div class="col-xl-3">
                                <p class="fw-semibold text-muted mb-1">Due Date :</p>
                                <p class="fs-15 mb-1">{{ \Carbon\Carbon::parse($invoiceData->due_date)->format('d M, Y') }}
                                </p>
                            </div>
                            <div class="col-xl-3">
                                <p class="fw-semibold text-muted mb-1">Total Amount :</p>
                                <p class="fs-16 mb-1 fw-semibold">
                                    {{ $invoiceData->currency_symbol }}{{ number_format($invoiceData->total_amount, 2) }}
                                </p>
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
                                            @forelse($invoiceData->items as $item)
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">
                                                            {{ $item['name'] ?? 'N/A' }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-muted">
                                                            {{ $item['description'] ?? '-' }}
                                                        </div>
                                                    </td>
                                                    <td class="product-quantity-container">
                                                        {{ $item['quantity'] ?? 0 }}
                                                    </td>
                                                    <td>
                                                        {{ $invoiceData->currency_symbol }}{{ number_format($item['price'] ?? 0, 2) }}
                                                    </td>
                                                    <td>
                                                        {{ $invoiceData->currency_symbol }}{{ number_format($item['total'] ?? 0, 2) }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">No items added</td>
                                                </tr>
                                            @endforelse
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
                                                                    <p class="mb-0 fw-semibold fs-15">
                                                                        {{ $invoiceData->currency_symbol }}{{ number_format($invoiceData->subtotal, 2) }}
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">
                                                                    <p class="mb-0">Discount :</p>
                                                                </th>
                                                                <td>
                                                                    <p class="mb-0 fw-semibold fs-15 text-danger">
                                                                        -{{ $invoiceData->currency_symbol }}{{ number_format($invoiceData->discount_amount, 2) }}
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">
                                                                    <p class="mb-0">Tax :</p>
                                                                </th>
                                                                <td>
                                                                    <p class="mb-0 fw-semibold fs-15">
                                                                        +{{ $invoiceData->currency_symbol }}{{ number_format($invoiceData->tax_amount, 2) }}
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">
                                                                    <p class="mb-0 fs-14">Total :</p>
                                                                </th>
                                                                <td>
                                                                    <p class="mb-0 fw-semibold fs-16 text-success">
                                                                        {{ $invoiceData->currency_symbol }}{{ number_format($invoiceData->total_amount, 2) }}
                                                                    </p>
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
                            @if (!empty($invoiceData->note))
                                <div class="col-xl-12">
                                    <div>
                                        <label for="invoice-note" class="form-label">Note:</label>
                                        <textarea class="form-control form-control-light" id="invoice-note" rows="3" readonly>{{ $invoiceData->note }}</textarea>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-secondary" onclick="window.close();">Close Preview <i
                                class="ri-close-line ms-1 align-middle"></i></button>
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
                                    Currency
                                </p>
                                <p>
                                    <span class="fw-semibold text-muted fs-12">Currency :</span>
                                    {{ $invoiceData->currency }}
                                </p>
                                <p>
                                    <span class="fw-semibold text-muted fs-12">Symbol :</span>
                                    {{ $invoiceData->currency_symbol }}
                                </p>
                                <p>
                                    <span class="fw-semibold text-muted fs-12">Total Amount :</span> <span
                                        class="text-success fw-semibold fs-14">{{ $invoiceData->currency_symbol }}{{ number_format($invoiceData->total_amount, 2) }}</span>
                                </p>
                                <p>
                                    <span class="fw-semibold text-muted fs-12">Due Date :</span>
                                    {{ \Carbon\Carbon::parse($invoiceData->due_date)->format('d M, Y') }}
                                </p>
                                <p>
                                    <span class="fw-semibold text-muted fs-12">Status : <span
                                            class="badge bg-info-transparent">Preview</span></span>
                                </p>
                                <div class="alert alert-info" role="alert">
                                    This is a preview of your invoice. Click "Send Invoice" on the create page to save and
                                    send.
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
