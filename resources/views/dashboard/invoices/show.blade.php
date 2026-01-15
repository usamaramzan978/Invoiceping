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
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header d-md-flex d-block">
                        <div class="h5 mb-0 d-sm-flex d-bllock align-items-center">
                            <div class="avatar avatar-sm">
                                @if ($invoice->business?->image)
                                    <img src="{{ asset('storage/' . $invoice->business->image) }}" alt="Logo">
                                @else
                                    <img src="{{ asset('build/assets/images/brand-logos/toggle-logo.png') }}"
                                        alt="Logo">
                                @endif
                            </div>
                            <div class="ms-sm-2 ms-0 mt-sm-0 mt-2">
                                <div class="h6 fw-semibold mb-0">INVOICE : <span
                                        class="text-primary">#{{ $invoice->invoice_number }}</span></div>
                            </div>
                        </div>
                        <div class="ms-auto mt-md-0 mt-2">
                            <button class="btn btn-sm btn-secondary me-1" onclick="javascript:window.print();">Print
                                <i class="ri-printer-line ms-1 align-middle d-inline-block"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                data-bs-target="#selectDesignModal">
                                Generate PDF
                                <i class="ri-file-pdf-line ms-1 align-middle d-inline-block"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row gy-3">
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                        <p class="text-muted mb-2"> Billing From : </p>
                                        <p class="fw-bold mb-1">{{ $invoice->business?->business_name ?? 'N/A' }}</p>
                                        @if ($invoice->business?->address)
                                            <p class="mb-1 text-muted">{{ $invoice->business->address }}</p>
                                        @endif
                                        @if ($invoice->business?->email)
                                            <p class="mb-1 text-muted">{{ $invoice->business->email }}</p>
                                        @endif
                                        @if ($invoice->business?->whatsapp_number)
                                            <p class="mb-1 text-muted">{{ $invoice->business->whatsapp_number }}</p>
                                        @endif
                                        @if ($invoice->business?->tax_id)
                                            <p class="text-muted">For more information check for <a
                                                    href="javascript:void(0);" class="text-primary fw-semibold"><u>Tax ID:
                                                        {{ $invoice->business->tax_id }}</u></a></p>
                                        @endif
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 ms-auto mt-sm-0 mt-3">
                                        <p class="text-muted mb-2"> Billing To : </p>
                                        <p class="fw-bold mb-1">{{ $invoice->client->name ?? 'N/A' }}</p>
                                        @if ($invoice->client?->email)
                                            <p class="text-muted mb-1">{{ $invoice->client->email }}</p>
                                        @endif
                                        @if ($invoice->client?->whatsapp_number)
                                            <p class="text-muted mb-1">{{ $invoice->client->whatsapp_number }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3">
                                <p class="fw-semibold text-muted mb-1">Invoice ID :</p>
                                <p class="fs-15 mb-1">#{{ $invoice->invoice_number }}</p>
                            </div>
                            <div class="col-xl-3">
                                <p class="fw-semibold text-muted mb-1">Date Issued :</p>
                                <p class="fs-15 mb-1">{{ $invoice->issue_date->format('d M, Y') }}@if ($invoice->sent_at)
                                        - <span class="text-muted fs-12">{{ $invoice->sent_at->format('h:i A') }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-xl-3">
                                <p class="fw-semibold text-muted mb-1">Due Date :</p>
                                <p class="fs-15 mb-1">{{ $invoice->due_date->format('d M, Y') }}</p>
                            </div>
                            <div class="col-xl-3">
                                <p class="fw-semibold text-muted mb-1">Total Amount :</p>
                                <p class="fs-16 mb-1 fw-semibold">{{ $invoice->currency ?? 'USD' }}
                                    {{ number_format($invoice->total_amount, 2) }}</p>
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
                                                $currency = $invoice->currency ?? 'USD';
                                                $currencySymbol = match ($currency) {
                                                    'USD' => '$',
                                                    'EUR' => '€',
                                                    'PKR' => '₨',
                                                    'GBP' => '£',
                                                    'INR' => '₹',
                                                    'AED' => 'د.إ',
                                                    default => '$',
                                                };
                                            @endphp
                                            @forelse ($invoice->items as $item)
                                                @php
                                                    $itemTotal = $item->quantity * $item->unit_price;
                                                    $subtotal += $itemTotal;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">{{ $item->name }}</div>
                                                    </td>
                                                    <td>
                                                        <div class="text-muted">{{ $item->description ?? '—' }}</div>
                                                    </td>
                                                    <td class="product-quantity-container">
                                                        {{ number_format($item->quantity, 2) }}</td>
                                                    <td>{{ $currencySymbol }}{{ number_format($item->unit_price, 2) }}
                                                    </td>
                                                    <td>{{ $currencySymbol }}{{ number_format($itemTotal, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        <i class="ri-inbox-line fs-24 d-block mb-2"></i>
                                                        No items found.
                                                    </td>
                                                </tr>
                                            @endforelse
                                            <tr>
                                                <td colspan="3"></td>
                                                <td colspan="2">
                                                    @php
                                                        $discountAmount = $invoice->discount_amount ?? 0;
                                                        $taxAmount = $invoice->tax_amount ?? 0;
                                                        $totalAmount = $invoice->total_amount ?? 0;

                                                        // Calculate discount percentage if discount type is percent
                                                        $discountPercent = 0;
                                                        if (
                                                            $invoice->discount_type &&
                                                            $invoice->discount_type->value === 'percent' &&
                                                            $subtotal > 0
                                                        ) {
                                                            $discountPercent = ($discountAmount / $subtotal) * 100;
                                                        }

                                                        // Calculate tax percentage
                                                        $taxPercent = 0;
                                                        $taxableAmount = $subtotal - $discountAmount;
                                                        if ($taxAmount > 0 && $taxableAmount > 0) {
                                                            $taxPercent = ($taxAmount / $taxableAmount) * 100;
                                                        }
                                                    @endphp
                                                    <table class="table table-sm text-nowrap mb-0 table-borderless">
                                                        <tbody>
                                                            <tr>
                                                                <th scope="row">
                                                                    <p class="mb-0">Sub Total :</p>
                                                                </th>
                                                                <td>
                                                                    <p class="mb-0 fw-semibold fs-15">
                                                                        {{ $currencySymbol }}{{ number_format($subtotal, 2) }}
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                            @if ($discountAmount > 0)
                                                                <tr>
                                                                    <th scope="row">
                                                                        <p class="mb-0">Discount @if ($discountPercent > 0)
                                                                                <span
                                                                                    class="text-success">({{ number_format($discountPercent, 1) }}%)</span>
                                                                            @endif :</p>
                                                                    </th>
                                                                    <td>
                                                                        <p class="mb-0 fw-semibold fs-15 text-danger">
                                                                            -{{ $currencySymbol }}{{ number_format($discountAmount, 2) }}
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            @if ($taxAmount > 0)
                                                                <tr>
                                                                    <th scope="row">
                                                                        <p class="mb-0">Tax @if ($taxPercent > 0)
                                                                                <span
                                                                                    class="text-danger">({{ number_format($taxPercent, 1) }}%)</span>
                                                                            @endif :</p>
                                                                    </th>
                                                                    <td>
                                                                        <p class="mb-0 fw-semibold fs-15">
                                                                            {{ $currencySymbol }}{{ number_format($taxAmount, 2) }}
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            <tr>
                                                                <th scope="row">
                                                                    <p class="mb-0 fs-14">Total :</p>
                                                                </th>
                                                                <td>
                                                                    <p class="mb-0 fw-semibold fs-16 text-success">
                                                                        {{ $currencySymbol }}{{ number_format($totalAmount, 2) }}
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
                            <div class="col-xl-12">
                                <div>
                                    <label for="invoice-note" class="form-label">Note:</label>
                                    <textarea class="form-control form-control-light" id="invoice-note" rows="3" readonly>{{ $invoice->notes ?? 'No notes available.' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--End::row-1 -->

    </div>

    <!-- Select Design Modal -->
    <div class="modal fade" id="selectDesignModal" tabindex="-1" aria-labelledby="selectDesignModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="selectDesignModalLabel">
                        <i class="ri-palette-line me-2"></i>Select Invoice Design
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">Choose a design template for your invoice PDF:</p>
                    <div class="row g-3">
                        <!-- Design 1 - Green -->
                        <div class="col-md-4 col-sm-6">
                            <div class="card design-card border" data-design="1">
                                <div class="card-body text-center p-3">
                                    <div class="design-preview mb-2"
                                        style="background: linear-gradient(135deg, #e8f5f0 0%, #198754 100%); height: 80px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-file-text-line fs-1 text-white"></i>
                                    </div>
                                    <h6 class="mb-1">Classic Green</h6>
                                    <small class="text-muted">Mint background, professional look</small>
                                </div>
                            </div>
                        </div>
                        <!-- Design 2 - Orange -->
                        <div class="col-md-4 col-sm-6">
                            <div class="card design-card border" data-design="2">
                                <div class="card-body text-center p-3">
                                    <div class="design-preview mb-2"
                                        style="background: linear-gradient(135deg, #fff3e0 0%, #d4882a 100%); height: 80px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-file-text-line fs-1 text-white"></i>
                                    </div>
                                    <h6 class="mb-1">Warm Orange</h6>
                                    <small class="text-muted">Sidebar layout, amber tones</small>
                                </div>
                            </div>
                        </div>
                        <!-- Design 3 - Red -->
                        <div class="col-md-4 col-sm-6">
                            <div class="card design-card border" data-design="3">
                                <div class="card-body text-center p-3">
                                    <div class="design-preview mb-2"
                                        style="background: linear-gradient(135deg, #fce4e4 0%, #a52a2a 100%); height: 80px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-file-text-line fs-1 text-white"></i>
                                    </div>
                                    <h6 class="mb-1">Bold Crimson</h6>
                                    <small class="text-muted">Pink header, red accents</small>
                                </div>
                            </div>
                        </div>
                        <!-- Design 4 - Blue -->
                        <div class="col-md-4 col-sm-6">
                            <div class="card design-card border" data-design="4">
                                <div class="card-body text-center p-3">
                                    <div class="design-preview mb-2"
                                        style="background: linear-gradient(135deg, #e3f2fd 0%, #4a7c9b 100%); height: 80px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-file-text-line fs-1 text-white"></i>
                                    </div>
                                    <h6 class="mb-1">Steel Blue</h6>
                                    <small class="text-muted">Clean modern, notes section</small>
                                </div>
                            </div>
                        </div>
                        <!-- Design 5 - Light Blue -->
                        <div class="col-md-4 col-sm-6">
                            <div class="card design-card border" data-design="5">
                                <div class="card-body text-center p-3">
                                    <div class="design-preview mb-2"
                                        style="background: linear-gradient(135deg, #e8f4f8 0%, #4a8fa8 100%); height: 80px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-file-text-line fs-1 text-white"></i>
                                    </div>
                                    <h6 class="mb-1">Sky Blue</h6>
                                    <small class="text-muted">Wave header, soft tones</small>
                                </div>
                            </div>
                        </div>
                        <!-- Design 6 - Purple/Default -->
                        <div class="col-md-4 col-sm-6">
                            <div class="card design-card border" data-design="6">
                                <div class="card-body text-center p-3">
                                    <div class="design-preview mb-2"
                                        style="background: linear-gradient(135deg, #f3f0ff 0%, #845adf 100%); height: 80px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-file-text-line fs-1 text-white"></i>
                                    </div>
                                    <h6 class="mb-1">Royal Purple</h6>
                                    <small class="text-muted">Premium design, elegant</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="generatePdfBtn" class="btn btn-primary disabled">
                        <i class="ri-download-line me-1"></i> Generate PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const designCards = document.querySelectorAll('.design-card');
            const generateBtn = document.getElementById('generatePdfBtn');
            const baseUrl = "{{ route('invoice.download', $invoice) }}";
            let selectedDesign = null;

            designCards.forEach(card => {
                card.style.cursor = 'pointer';
                card.addEventListener('click', function() {
                    // Remove selection from all cards
                    designCards.forEach(c => {
                        c.classList.remove('border-primary', 'shadow-sm');
                        c.style.borderWidth = '1px';
                    });

                    // Add selection to clicked card
                    this.classList.add('border-primary', 'shadow-sm');
                    this.style.borderWidth = '2px';

                    // Get design number
                    selectedDesign = this.dataset.design;

                    // Enable and update generate button
                    generateBtn.classList.remove('disabled');
                    generateBtn.href = baseUrl + '?design=' + selectedDesign;
                });
            });
        });
    </script>
@endsection
