@extends('layouts.app')

@section('styles')
@endsection

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Create Invoice</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Invoice</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create Invoice</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="ri-error-warning-line me-2"></i>Validation Errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Start::row-1 -->
        <div class="row">
            <div class="col-xl-9">
                <div class="card custom-card">
                    <form action="{{ route('invoices.store') }}" method="POST">
                        @csrf
                        <div class="card-header d-md-flex d-block">
                            <div class="h5 mb-0 d-sm-flex d-block align-items-center">
                                <div>
                                    <img src="{{ asset('build/assets/images/brand-logos/toggle-logo.png') }}"
                                        alt="">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <div class="row">
                                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                            <p class="dw-semibold mb-2">Billing From :</p>
                                            <div class="row gy-2">
                                                <div class="col-xl-12">
                                                    <input type="text" class="form-control form-control-light"
                                                        id="Company-Name" placeholder="Business Name"
                                                        value="{{ auth()->user()->business?->business_name }}">
                                                </div>
                                                <div class="col-xl-12">
                                                    <textarea class="form-control form-control-light" id="company-address" placeholder="Enter Address" rows="3"></textarea>
                                                </div>
                                                <div class="col-xl-12">
                                                    <input type="text" class="form-control form-control-light"
                                                        id="company-mail" placeholder="Business Email"
                                                        value="{{ auth()->user()->business?->email }}">
                                                </div>
                                                <div class="col-xl-12">
                                                    <input type="text" class="form-control form-control-light"
                                                        id="company-phone" placeholder="Phone Number"
                                                        value="{{ auth()->user()->business?->whatsapp_number }}">
                                                </div>
                                                <div class="col-xl-12">
                                                    <textarea class="form-control form-control-light" id="invoice-subject" placeholder="Subject" rows="4"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 ms-auto mt-sm-0 mt-3">
                                            <p class="dw-semibold mb-2">Billing To : <span class="text-danger">*</span></p>
                                            <div class="row gy-2">
                                                <div class="col-xl-12">
                                                    <select name="client_id" class="form-control select2-client"
                                                        id="client_id" required>
                                                        <option value="">Select Client</option>
                                                        @foreach ($clients as $client)
                                                            <option value="{{ $client->id }}"
                                                                data-email="{{ $client->email }}"
                                                                data-phone="{{ $client->whatsapp_number }}"
                                                                {{ old('client_id', request('client_id')) == $client->id ? 'selected' : '' }}>
                                                                {{ $client->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('client_id')
                                                        <div class="text-danger small">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-xl-12">
                                                    <input type="text" class="form-control form-control-light"
                                                        id="client-mail" placeholder="Client Email" value="" readonly>
                                                </div>
                                                <div class="col-xl-12">
                                                    <input type="text" class="form-control form-control-light"
                                                        id="client-phone" placeholder="Client Phone Number" value=""
                                                        readonly>
                                                </div>
                                                <div class="col-xl-12 choices-control">
                                                    <p class="dw-semibold mb-2 mt-2">Currency :</p>
                                                    <select class="form-control select2-invoice-currency" data-trigger
                                                        name="currency" id="invoice-currency-select" required>
                                                        <option value="USD" data-symbol="$">USD - United States Dollar
                                                        </option>
                                                        <option value="EUR" data-symbol="€">EUR - Euro</option>
                                                        <option value="PKR" data-symbol="₨">PKR - Pakistani Rupee
                                                        </option>
                                                        <option value="GBP" data-symbol="£">GBP - British Pound
                                                        </option>
                                                        <option value="INR" data-symbol="₹">INR - Indian Rupee</option>
                                                        <option value="AED" data-symbol="د.إ">AED - UAE Dirham</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3">
                                    <label for="invoice-number" class="form-label">Invoice ID <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="invoice-number"
                                            name="invoice_number" placeholder="Inv No">
                                        <button type="button" class="input-group-text" id="generate-invoice-number"
                                            title="Generate Invoice Number">
                                            <i class="ri-shuffle-line"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-xl-3">
                                    <label for="invoice-date-issued" class="form-label">Date Issued <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-light"
                                        id="invoice-date-issued" name="issue_date" placeholder="Choose date" required>
                                </div>
                                <div class="col-xl-3">
                                    <label for="invoice-date-due" class="form-label">Due Date <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-light" id="invoice-date-due"
                                        name="due_date" placeholder="Choose date" required>
                                </div>

                                <!-- Invoice Items  -->
                                <div class="col-xl-12">
                                    <div class="table-responsive">
                                        <table class="table nowrap text-nowrap border mt-3" id="invoice-items-table">
                                            <thead>
                                                <tr>
                                                    <th>PRODUCT NAME <span class="text-danger">*</span></th>
                                                    <th>DESCRIPTION</th>
                                                    <th>QUANTITY <span class="text-danger">*</span></th>
                                                    <th>PRICE PER UNIT <span class="text-danger">*</span></th>
                                                    <th>TOTAL</th>
                                                    <th>ACTION</th>
                                                </tr>
                                            </thead>
                                            <tbody id="invoice-items-container">
                                                <!-- Items will be added here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                {{-- Summmary  --}}
                                <div class="row gy-3 col-xl-3 ms-auto">
                                    <div class="col-xl-12">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Subtotal:</span>
                                            <span id="subtotal-display" class="fw-semibold">$0.00</span>
                                        </div>
                                        <input type="hidden" id="subtotal-value" value="0">
                                    </div>

                                    <div class="col-xl-12">
                                        <label for="discount-input" class="form-label">Discount:</label>
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control form-control-light"
                                                id="discount-input" placeholder="0" value="0">
                                            <select class="form-select form-select-sm" id="discount-type">
                                                <option value="fixed">Fixed ($)</option>
                                                <option value="percent">Percent (%)</option>
                                            </select>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Discount:</span>
                                            <span id="discount-display" class="text-danger fw-semibold">-$0.00</span>
                                        </div>
                                        <input type="hidden" id="discount-value" value="0">
                                    </div>

                                    <div class="col-xl-12">
                                        <label for="tax-input" class="form-label">Tax:</label>
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control form-control-light" id="tax-input"
                                                placeholder="0" value="0">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                            <span class="text-muted">Tax:</span>
                                            <span id="tax-display" class="fw-semibold">+$0.00</span>
                                        </div>
                                        <input type="hidden" id="tax-value" value="0">
                                    </div>

                                    <div class="col-xl-12">
                                        <div class="d-flex justify-content-between">
                                            <span class="fs-16 fw-semibold">Total Amount:</span>
                                            <span id="total-display" class="fs-16 fw-semibold text-primary">$0.00</span>
                                        </div>
                                        <input type="hidden" id="total-value" value="0">
                                    </div>
                                </div>

                                <div class="col-xl-12">
                                    <div>
                                        <label for="invoice-note" class="form-label">Note:</label>
                                        <textarea class="form-control form-control-light" name="notes" id="invoice-note" rows="3">Once the invoice has been verified by the accounts payable team and recorded, the only task left is to send it for approval before releasing the payment</textarea>
                                    </div>
                                </div>

                                <!-- Hidden inputs for calculated values -->
                                <input type="hidden" name="discount_type" id="discount-type-hidden" value="fixed">
                                <input type="hidden" name="discount_value" id="discount-value-hidden" value="0">
                                <input type="hidden" name="tax_percentage" id="tax-percentage-hidden" value="0">
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button type="button" id="preview-invoice-btn" class="btn btn-light me-1"><i
                                    class="ri-eye-line me-1 align-middle d-inline-block"></i>Preview</button>
                            <button class="btn btn-primary">Save Invoice <i
                                    class="ri-send-plane-2-line ms-1 align-middle d-inline-block"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-xl-3">
                <!-- Payment Mode -->
                {{-- <div class="card custom-card mt-3">
                    <div class="card-header">
                        <div class="card-title">Mode Of Payment</div>
                    </div>
                    <div class="card-body">
                        <div class="row gy-3">
                            <!-- Payment Options -->
                            <div class="col-xl-12">
                                <div class="btn-group" role="group">
                                    <input type="radio" class="btn-check" name="payment-mode" id="payment-upi">
                                    <label class="btn btn-outline-light" for="payment-upi">UPI</label>

                                    <input type="radio" class="btn-check" name="payment-mode" id="payment-bank"
                                        checked>
                                    <label class="btn btn-outline-light" for="payment-bank">Bank Transfer</label>
                                </div>
                            </div>

                            <!-- UPI ID (Optional) -->
                            <div class="col-xl-12">
                                <input type="text" class="form-control form-control-light"
                                    placeholder="UPI ID (example@upi)">
                            </div>

                            <!-- Bank Account Details (Safe to display partially) -->
                            <div class="col-xl-12">
                                <input type="text" class="form-control form-control-light" placeholder="Bank Name"
                                    value="ABC Bank" readonly>
                            </div>
                            <div class="col-xl-12">
                                <input type="text" class="form-control form-control-light"
                                    placeholder="Account Number (last 4 digits)" value="XXXX1234" readonly>
                            </div>
                            <div class="col-xl-12">
                                <input type="text" class="form-control form-control-light" placeholder="IFSC Code"
                                    value="ABCD0123456" readonly>
                            </div>

                            <!-- Optional Instruction -->
                            <div class="col-xl-12">
                                <div class="alert alert-info" role="alert">
                                    Please complete the payment within 30 days. For UPI payments, use the provided UPI ID.
                                    For bank transfer, use the account details above.
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('build/assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    @vite('resources/assets/js/create-invoice.js')

    <script>
        const InvoiceManager = {
            currencySymbol: '$',
            itemCount: 0,

            init() {
                this.setupClient();
                this.setupCurrency();
                this.setupInvoiceNumber();
                this.addDefaultItem();
                this.setupCalculations();
            },

            setupClient() {
                $('.select2-client').select2({
                    placeholder: 'Select Client'
                });

                $('#client_id').on('select2:select select2:clear', () => {
                    const option = $('#client_id').find(':selected');
                    $('#client-mail').val(option.data('email') || '');
                    $('#client-phone').val(option.data('phone') || '');
                });

                const option = $('#client_id').find(':selected');
                $('#client-mail').val(option.data('email') || '');
                $('#client-phone').val(option.data('phone') || '');
            },

            setupCurrency() {
                $('.select2-invoice-currency').select2({
                    placeholder: 'Select Currency'
                });

                $('.select2-invoice-currency').on('select2:select select2:clear', () => {
                    const selected = $('.select2-invoice-currency').find(':selected');
                    this.currencySymbol = selected.data('symbol') || '$';
                    this.updateCurrencyDisplay();
                });

                const selected = $('.select2-invoice-currency').find(':selected');
                this.currencySymbol = selected.data('symbol') || '$';
            },

            setupInvoiceNumber() {
                $('#generate-invoice-number').on('click', () => {
                    const prefix = 'INV';
                    const year = new Date().getFullYear();
                    const random = Math.floor(100000 + Math.random() * 900000);
                    $('#invoice-number').val(`${prefix}-${year}-${random}`);
                });
            },

            addDefaultItem() {
                this.addItem();
            },

            addItem() {
                const itemId = this.itemCount++;
                const html = `
                    <tr class="invoice-item" data-item-id="${itemId}">
                        <td>
                            <input type="text" class="form-control form-control-light product-name"
                                name="items[${itemId}][name]" placeholder="Enter Product Name" required>
                        </td>
                        <td>
                            <textarea rows="1" class="form-control form-control-light product-description"
                                name="items[${itemId}][description]" placeholder="Enter Description"></textarea>
                        </td>
                        <td class="invoice-quantity-container">
                            <div class="input-group border rounded flex-nowrap">
                                <button class="btn btn-icon btn-primary input-group-text flex-fill qty-minus"
                                    type="button"><i class="ri-subtract-line"></i></button>
                                <input type="text" class="form-control form-control-sm border-0 text-center product-qty"
                                    name="items[${itemId}][quantity]" value="1" min="1" required>
                                <button class="btn btn-icon btn-primary input-group-text flex-fill qty-plus"
                                    type="button"><i class="ri-add-line"></i></button>
                            </div>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-light product-price"
                                name="items[${itemId}][unit_price]" placeholder="0.00" value="0.00" required>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-light product-total"
                                placeholder="0.00" value="0.00" readonly>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-icon btn-danger-light remove-item" type="button"
                                title="Delete Item"><i class="ri-delete-bin-5-line"></i></button>
                        </td>
                    </tr>
                `;

                $('#invoice-items-container').append(html);
                this.attachItemHandlers(itemId);
            },

            attachItemHandlers(itemId) {
                const $row = $(`.invoice-item[data-item-id="${itemId}"]`);

                $row.find('.qty-plus').on('click', () => {
                    const $qty = $row.find('.product-qty');
                    $qty.val(parseInt($qty.val()) + 1);
                    this.calculateItemTotal(itemId);
                });

                $row.find('.qty-minus').on('click', () => {
                    const $qty = $row.find('.product-qty');
                    const current = parseInt($qty.val());
                    if (current > 1) $qty.val(current - 1);
                    this.calculateItemTotal(itemId);
                });

                $row.find('.product-qty, .product-price').on('change keyup', () => {
                    this.calculateItemTotal(itemId);
                });

                $row.find('.remove-item').on('click', () => {
                    $row.remove();
                    if ($('#invoice-items-container tr').length === 0) this.addItem();
                    this.calculateTotals();
                });
            },

            calculateItemTotal(itemId) {
                const $row = $(`.invoice-item[data-item-id="${itemId}"]`);
                const qty = parseFloat($row.find('.product-qty').val()) || 0;
                const price = parseFloat($row.find('.product-price').val()) || 0;
                const total = qty * price;

                $row.find('.product-total').val(total.toFixed(2));
                this.calculateTotals();
            },

            calculateTotals() {
                let subtotal = 0;

                $('#invoice-items-container .invoice-item').each((i, el) => {
                    const total = parseFloat($(el).find('.product-total').val()) || 0;
                    subtotal += total;
                });

                $('#subtotal-value').val(subtotal.toFixed(2));
                $('#subtotal-display').text(`${this.currencySymbol}${subtotal.toFixed(2)}`);

                this.calculateDiscount(subtotal);
            },

            calculateDiscount(subtotal) {
                const discountInput = parseFloat($('#discount-input').val()) || 0;
                const discountType = $('#discount-type').val();
                let discountAmount = 0;

                if (discountType === 'percent') {
                    discountAmount = (subtotal * discountInput) / 100;
                } else {
                    discountAmount = discountInput;
                }

                $('#discount-value').val(discountAmount.toFixed(2));
                $('#discount-display').text(`-${this.currencySymbol}${discountAmount.toFixed(2)}`);

                // Update hidden fields for form submission
                $('#discount-type-hidden').val(discountType);
                $('#discount-value-hidden').val(discountInput);

                this.calculateTax(subtotal, discountAmount);
            },

            calculateTax(subtotal, discountAmount) {
                const taxPercent = parseFloat($('#tax-input').val()) || 0;
                const taxableAmount = subtotal - discountAmount;
                const taxAmount = (taxableAmount * taxPercent) / 100;

                $('#tax-value').val(taxAmount.toFixed(2));
                $('#tax-display').text(`+${this.currencySymbol}${taxAmount.toFixed(2)}`);

                // Update hidden field for form submission
                $('#tax-percentage-hidden').val(taxPercent);

                this.calculateFinalTotal(subtotal, discountAmount, taxAmount);
            },

            calculateFinalTotal(subtotal, discount, tax) {
                const total = subtotal - discount + tax;
                $('#total-value').val(total.toFixed(2));
                $('#total-display').text(`${this.currencySymbol}${total.toFixed(2)}`);
            },

            setupCalculations() {
                $('#discount-input, #discount-type, #tax-input').on('change keyup', () => {
                    const subtotal = parseFloat($('#subtotal-value').val()) || 0;
                    this.calculateDiscount(subtotal);
                });

                // Add Product button
                // $(document).on('click', '.add-product-btn', () => {
                //     this.addItem();
                // });
            },

            updateCurrencyDisplay() {
                $('#subtotal-display').text(
                    `${this.currencySymbol}${parseFloat($('#subtotal-value').val()).toFixed(2)}`);
                $('#discount-display').text(
                    `-${this.currencySymbol}${parseFloat($('#discount-value').val()).toFixed(2)}`);
                $('#tax-display').text(`+${this.currencySymbol}${parseFloat($('#tax-value').val()).toFixed(2)}`);
                $('#total-display').text(`${this.currencySymbol}${parseFloat($('#total-value').val()).toFixed(2)}`);
            }
        };

        $(document).ready(() => {
            InvoiceManager.init();

            // Add Product button at the end of table
            $('#invoice-items-container').on('click', '.add-product-btn', () => {
                InvoiceManager.addItem();
            });

            // Add button in table footer
            $(document).on('click', '.add-product-btn', () => {
                InvoiceManager.addItem();
            });

            // Alternative: Add a button outside the table
            setTimeout(() => {
                if ($('#invoice-items-container').closest('table').find('tfoot').length === 0) {
                    const addBtn = `
                        <tr>
                            <td colspan="6" class="border-bottom-0">
                                <button type="button" class="btn btn-light add-product-btn">
                                    <i class="bi bi-plus-lg"></i> Add Product
                                </button>
                            </td>
                        </tr>
                    `;
                    $('#invoice-items-container').after(addBtn);
                }
            }, 100);

            // Preview Invoice Button Handler
            $('#preview-invoice-btn').on('click', function() {
                // Collect all invoice items
                const items = [];
                $('#invoice-items-container .invoice-item').each(function() {
                    const $row = $(this);
                    items.push({
                        name: $row.find('.product-name').val() || '',
                        description: $row.find('.product-description').val() || '',
                        quantity: parseFloat($row.find('.product-qty').val()) || 0,
                        price: parseFloat($row.find('.product-price').val()) || 0,
                        total: parseFloat($row.find('.product-total').val()) || 0
                    });
                });

                // Get selected currency symbol
                const selectedCurrency = $('#invoice-currency-select').find(':selected');
                const currencySymbol = selectedCurrency.data('symbol') || '$';
                const currency = selectedCurrency.val() || 'USD';

                // Prepare form data
                const formData = {
                    _token: '{{ csrf_token() }}',
                    client_id: $('#client_id').val(),
                    invoice_number: $('#invoice-number').val() || 'PREVIEW',
                    issue_date: $('#invoice-date-issued').val() || new Date().toISOString().split('T')[
                        0],
                    due_date: $('#invoice-date-due').val() || new Date(Date.now() + 30 * 24 * 60 * 60 *
                        1000).toISOString().split('T')[0],
                    currency: currency,
                    currency_symbol: currencySymbol,
                    subtotal: parseFloat($('#subtotal-value').val()) || 0,
                    discount_amount: parseFloat($('#discount-value').val()) || 0,
                    tax_amount: parseFloat($('#tax-value').val()) || 0,
                    total: parseFloat($('#total-value').val()) || 0,
                    note: $('#invoice-note').val() || '',
                    billing_from_name: $('#Company-Name').val() || '',
                    billing_from_address: $('#company-address').val() || '',
                    billing_from_email: $('#company-mail').val() || '',
                    billing_from_phone: $('#company-phone').val() || '',
                    billing_subject: $('#invoice-subject').val() || '',
                    items: items
                };

                // Create a form and submit to new tab
                const form = $('<form>', {
                    method: 'POST',
                    action: '{{ route('invoices.preview') }}',
                    target: '_blank'
                });

                // Add all form data as hidden inputs
                $.each(formData, function(key, value) {
                    if (key === 'items') {
                        // Handle array of items
                        $.each(value, function(index, item) {
                            $.each(item, function(itemKey, itemValue) {
                                form.append($('<input>', {
                                    type: 'hidden',
                                    name: `items[${index}][${itemKey}]`,
                                    value: itemValue
                                }));
                            });
                        });
                    } else {
                        form.append($('<input>', {
                            type: 'hidden',
                            name: key,
                            value: value
                        }));
                    }
                });

                // Append form to body, submit, and remove
                $('body').append(form);
                form.submit();
                form.remove();
            });
        });
    </script>
@endsection
