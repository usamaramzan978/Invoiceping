@extends('layouts.app')

@section('styles')
@endsection

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Invoice List</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Invoice</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Invoice List</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Page Header Close -->

        <!-- Start::row-1 -->
        <div class="row">
            <div class="col-xl-9">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">
                            Manage Invoices
                        </div>
                        <div class="d-flex">
                            <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-primary btn-wave waves-light">
                                <i class="ri-add-line fw-semibold align-middle me-1" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Create New Invoice"></i>
                                Create Invoice</a>
                            <div class="dropdown ms-2">
                                <button class="btn btn-icon btn-secondary-light btn-sm btn-wave waves-light" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="javascript:void(0);">All Invoices</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0);">Paid Invoices</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0);">Pending Invoices</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0);">Overdue Invoices</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-nowrap table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">Client</th>
                                        <th scope="col">Invoice ID</th>
                                        <th scope="col">Issued Date</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Due Date</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($invoices as $invoice)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2 lh-1">
                                                        <span class="avatar avatar-sm avatar-rounded">
                                                            <img src="{{ asset('build/assets/images/faces/1.jpg') }}"
                                                                alt="">
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <p class="mb-0 fw-semibold">{{ $invoice->client->name }}</p>
                                                        <p class="mb-0 fs-11 text-muted">{{ $invoice->client->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="javascript:void(0);" class="fw-semibold text-primary">
                                                    #{{ $invoice->invoice_number }}
                                                </a>
                                            </td>
                                            <td>
                                                {{ $invoice->issue_date->format('d M, Y') }}
                                            </td>
                                            <td>
                                                {{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}
                                            </td>
                                            <td>
                                                @php
                                                    $computedStatus = $invoice->getComputedStatus();

                                                    $badgeClass = match ($computedStatus) {
                                                        \App\Enums\InvoiceStatus::DRAFT => 'info',
                                                        \App\Enums\InvoiceStatus::SENT => 'primary',
                                                        \App\Enums\InvoiceStatus::PAID => 'success',
                                                        \App\Enums\InvoiceStatus::OVERDUE => 'danger',
                                                    };

                                                    // Normalize dates (REMOVE time)
                                                    $today = now()->startOfDay();
                                                    $dueDate = $invoice->due_date->startOfDay();
                                                @endphp

                                                <span class="badge bg-{{ $badgeClass }}-transparent">
                                                    {{ $computedStatus->label() }}
                                                </span>

                                                @if ($computedStatus === \App\Enums\InvoiceStatus::SENT)
                                                    @php
                                                        $daysUntilDue = $today->diffInDays($dueDate, false);
                                                    @endphp

                                                    @if ($daysUntilDue > 0)
                                                        <small class="text-primary d-block mt-1">
                                                            Due in {{ $daysUntilDue }}
                                                            {{ Str::plural('day', $daysUntilDue) }}
                                                        </small>
                                                    @endif
                                                @elseif($computedStatus === \App\Enums\InvoiceStatus::OVERDUE)
                                                    @php
                                                        $daysOverdue = abs($dueDate->diffInDays($today));
                                                    @endphp

                                                    <small class="text-danger d-block mt-1">
                                                        {{ $daysOverdue }} {{ Str::plural('day', $daysOverdue) }} overdue
                                                    </small>
                                                @endif
                                            </td>

                                            <td>
                                                {{ $invoice->due_date->format('d M, Y') }}
                                            </td>
                                            <td>
                                                <a href="{{ route('invoices.show', $invoice) }}"
                                                    class="btn btn-info-light btn-icon btn-sm ms-1 invoice-btn"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="View Invoice">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                                <a href="{{ route('invoices.edit', $invoice) }}"
                                                    class="btn btn-success-light btn-icon btn-sm ms-1 invoice-btn"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Invoice">
                                                    <i class="ri-pencil-line"></i>
                                                </a>
                                                @if ($invoice->pdf_path)
                                                    <a href="{{ route('invoice.download', $invoice) }}"
                                                        class="btn btn-secondary-light btn-icon btn-sm ms-1 invoice-btn"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Download PDF">
                                                        <i class="ri-download-line"></i>
                                                    </a>
                                                @endif
                                                <button type="button"
                                                    class="btn btn-danger-light btn-icon ms-1 btn-sm invoice-btn"
                                                    data-delete-modal data-title="Delete Invoice"
                                                    data-message="Are you sure you want to delete invoice #{{ $invoice->invoice_number }}? This action cannot be undone."
                                                    data-form-id="{{ route('invoices.destroy', $invoice) }}"
                                                    data-record-name="invoice" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="Delete Invoice">
                                                    <i class="ri-delete-bin-5-line"></i>
                                                </button>
                                                <button type="button" class="btn btn-primary btn-sm ms-1 send-invoice-btn"
                                                    data-invoice-id="{{ $invoice->id }}" data-bs-toggle="modal"
                                                    data-bs-target="#sendInvoiceModal" title="Send Invoice">
                                                    <i class="ri-send-plane-2-line"></i> Send Invoice
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-5">
                                                <i class="bx bx-receipt fs-1 d-block mb-2"></i>
                                                <p class="mb-0">No invoices found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        {{ $invoices->links() }}
                    </div>
                </div>
            </div>
            {{-- Chat & Cards Dashboard --}}
            <div class="col-xl-3">
                <div class="card custom-card">
                    <div class="card-body p-0">
                        <div class="p-4 border-bottom border-block-end-dashed d-flex align-items-top">
                            <div class="svg-icon-background bg-primary-transparent me-4">
                                <svg xmlns="http://www.w3.org/2000/svg" data-name="Layer 1" viewBox="0 0 24 24"
                                    class="svg-primary">
                                    <path
                                        d="M13,16H7a1,1,0,0,0,0,2h6a1,1,0,0,0,0-2ZM9,10h2a1,1,0,0,0,0-2H9a1,1,0,0,0,0,2Zm12,2H18V3a1,1,0,0,0-.5-.87,1,1,0,0,0-1,0l-3,1.72-3-1.72a1,1,0,0,0-1,0l-3,1.72-3-1.72a1,1,0,0,0-1,0A1,1,0,0,0,2,3V19a3,3,0,0,0,3,3H19a3,3,0,0,0,3-3V13A1,1,0,0,0,21,12ZM5,20a1,1,0,0,1-1-1V4.73L6,5.87a1.08,1.08,0,0,0,1,0l3-1.72,3,1.72a1.08,1.08,0,0,0,1,0l2-1.14V19a3,3,0,0,0,.18,1Zm15-1a1,1,0,0,1-2,0V14h2Zm-7-7H7a1,1,0,0,0,0,2h6a1,1,0,0,0,0-2Z" />
                                </svg>
                            </div>
                            <div class="flex-fill">
                                <h6 class="mb-2 fs-12">Total Invoices Amount
                                    <span class="badge bg-primary fw-semibold float-end">
                                        {{ number_format($stats['total_count']) }}
                                    </span>
                                </h6>
                                <div class="pb-0 mt-0">
                                    <div>
                                        <h4 class="fs-18 fw-semibold mb-2">
                                            ${{ number_format($stats['total_amount'] / 1000, 2) }}K</h4>
                                        <p class="text-muted fs-11 mb-0 lh-1">
                                            <span class="text-muted">
                                                Total: ${{ number_format($stats['total_amount'], 2) }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 border-bottom border-block-end-dashed d-flex align-items-top">
                            <div class="svg-icon-background bg-success-transparent me-4">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="svg-success">
                                    <path
                                        d="M11.5,20h-6a1,1,0,0,1-1-1V5a1,1,0,0,1,1-1h5V7a3,3,0,0,0,3,3h3v5a1,1,0,0,0,2,0V9s0,0,0-.06a1.31,1.31,0,0,0-.06-.27l0-.09a1.07,1.07,0,0,0-.19-.28h0l-6-6h0a1.07,1.07,0,0,0-.28-.19.29.29,0,0,0-.1,0A1.1,1.1,0,0,0,11.56,2H5.5a3,3,0,0,0-3,3V19a3,3,0,0,0,3,3h6a1,1,0,0,0,0-2Zm1-14.59L15.09,8H13.5a1,1,0,0,1-1-1ZM7.5,14h6a1,1,0,0,0,0-2h-6a1,1,0,0,0,0,2Zm4,2h-4a1,1,0,0,0,0,2h4a1,1,0,0,0,0-2Zm-4-6h1a1,1,0,0,0,0-2h-1a1,1,0,0,0,0,2Zm13.71,6.29a1,1,0,0,0-1.42,0l-3.29,3.3-1.29-1.3a1,1,0,0,0-1.42,1.42l2,2a1,1,0,0,0,1.42,0l4-4A1,1,0,0,0,21.21,16.29Z" />
                                </svg>
                            </div>
                            <div class="flex-fill">
                                <h6 class="mb-2 fs-12">Total Paid Invoices
                                    <span class="badge bg-success fw-semibold float-end">
                                        {{ number_format($stats['paid_count']) }}
                                    </span>
                                </h6>
                                <div>
                                    <h4 class="fs-18 fw-semibold mb-2">
                                        ${{ number_format($stats['paid_amount'] / 1000, 2) }}K</h4>
                                    <p class="text-muted fs-11 mb-0 lh-1">
                                        <span class="text-muted">
                                            Total: ${{ number_format($stats['paid_amount'], 2) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-top p-4 border-bottom border-block-end-dashed">
                            <div class="svg-icon-background bg-warning-transparent me-4">
                                <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24"
                                    viewBox="0 0 24 24" class="svg-warning">
                                    <path
                                        d="M19,12h-7V5c0-0.6-0.4-1-1-1c-5,0-9,4-9,9s4,9,9,9s9-4,9-9C20,12.4,19.6,12,19,12z M12,19.9c-3.8,0.6-7.4-2.1-7.9-5.9C3.5,10.2,6.2,6.6,10,6.1V13c0,0.6,0.4,1,1,1h6.9C17.5,17.1,15.1,19.5,12,19.9z M15,2c-0.6,0-1,0.4-1,1v6c0,0.6,0.4,1,1,1h6c0.6,0,1-0.4,1-1C22,5.1,18.9,2,15,2z M16,8V4.1C18,4.5,19.5,6,19.9,8H16z" />
                                </svg>
                            </div>
                            <div class="flex-fill">
                                <h6 class="mb-2 fs-12">Pending Invoices
                                    <span class="badge bg-warning fw-semibold float-end">
                                        {{ number_format($stats['pending_count']) }}
                                    </span>
                                </h6>
                                <div>
                                    <h4 class="fs-18 fw-semibold mb-2">
                                        ${{ number_format($stats['pending_amount'] / 1000, 2) }}K</h4>
                                    <p class="text-muted fs-11 mb-0 lh-1">
                                        <span class="text-muted">
                                            Total: ${{ number_format($stats['pending_amount'], 2) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-top p-4 border-bottom border-block-end-dashed">
                            <div class="svg-icon-background bg-light me-4">
                                <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24"
                                    viewBox="0 0 24 24" class="svg-dark">
                                    <path
                                        d="M19,12h-7V5c0-0.6-0.4-1-1-1c-5,0-9,4-9,9s4,9,9,9s9-4,9-9C20,12.4,19.6,12,19,12z M12,19.9c-3.8,0.6-7.4-2.1-7.9-5.9C3.5,10.2,6.2,6.6,10,6.1V13c0,0.6,0.4,1,1,1h6.9C17.5,17.1,15.1,19.5,12,19.9z M15,2c-0.6,0-1,0.4-1,1v6c0,0.6,0.4,1,1,1h6c0.6,0,1-0.4,1-1C22,5.1,18.9,2,15,2z M16,8V4.1C18,4.5,19.5,6,19.9,8H16z" />
                                </svg>
                            </div>
                            <div class="flex-fill">
                                <h6 class="mb-2 fs-12">Overdue Invoices
                                    <span class="badge bg-light text-default fw-semibold float-end">
                                        {{ number_format($stats['overdue_count']) }}
                                    </span>
                                </h6>
                                <div>
                                    <h4 class="fs-18 fw-semibold mb-2">
                                        ${{ number_format($stats['overdue_amount'] / 1000, 2) }}K</h4>
                                    <p class="text-muted fs-11 mb-0 lh-1">
                                        <span class="text-muted">
                                            Total: ${{ number_format($stats['overdue_amount'], 2) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="fs-15 fw-semibold">Invoice Status <span class="text-muted fw-normal">(Last 6 months)
                                    :</span></p>
                            <div id="invoice-list-stats"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--End::row-1 -->

    </div>
@endsection

<!-- Send Invoice Modal -->
<div class="modal fade" id="sendInvoiceModal" tabindex="-1" aria-labelledby="sendInvoiceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendInvoiceModalLabel">Send Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="send-invoice-form" method="POST" action="{{ route('invoices.send-message') }}">
                @csrf
                <input type="hidden" name="invoice_id" id="modal-invoice-id" value="">
                <div class="modal-body">
                    <!-- Error Display Area -->
                    <div id="send-invoice-errors" class="alert alert-danger d-none" role="alert">
                        <i class="ri-error-warning-line me-2"></i>
                        <strong>Error:</strong>
                        <ul id="send-invoice-error-list" class="mb-0 mt-2"></ul>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Channels</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input channel-checkbox" type="checkbox" name="channels[]"
                                id="channel-email" value="email" checked>
                            <label class="form-check-label" for="channel-email">
                                <i class="ri-mail-line me-1"></i>Email
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input channel-checkbox" type="checkbox" name="channels[]"
                                id="channel-whatsapp" value="whatsapp">
                            <label class="form-check-label" for="channel-whatsapp">
                                <i class="ri-whatsapp-line me-1"></i>WhatsApp
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input channel-checkbox" type="checkbox" name="channels[]"
                                id="channel-sms" value="sms">
                            <label class="form-check-label" for="channel-sms">
                                <i class="ri-message-2-line me-1"></i>SMS
                            </label>
                        </div>
                    </div>
                    <div class="alert alert-info py-2 px-3 mb-3">
                        <i class="ri-information-line me-1"></i>
                        Select a template for each channel, or leave empty to use the default.
                    </div>
                    <div id="template-grid-area"></div>
                    <div id="include-pdf-section-email" class="mt-3" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="include_pdf_email"
                                id="include-pdf-checkbox-email" value="1" checked>
                            <label class="form-check-label" for="include-pdf-checkbox-email">
                                <strong>Include Invoice PDF (Email)</strong>
                                <small class="d-block text-muted mt-1">
                                    <i class="ri-information-line me-1"></i>
                                    Attach invoice PDF if the selected email template contains an invoice block
                                </small>
                            </label>
                        </div>
                    </div>
                    <div id="include-pdf-section-whatsapp" class="mt-3" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="include_pdf_whatsapp"
                                id="include-pdf-checkbox-whatsapp" value="1" checked>
                            <label class="form-check-label" for="include-pdf-checkbox-whatsapp">
                                <strong>Include Invoice PDF (WhatsApp)</strong>
                                <small class="d-block text-muted mt-1">
                                    <i class="ri-information-line me-1"></i>
                                    Attach invoice PDF as document in WhatsApp message
                                </small>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    // Ensure invoice_id is set in modal when opened
    document.addEventListener('DOMContentLoaded', function() {
        var sendBtns = document.querySelectorAll('.send-invoice-btn');
        var invoiceIdInput = document.getElementById('modal-invoice-id');
        var modal = document.getElementById('sendInvoiceModal');
        sendBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (invoiceIdInput) {
                    invoiceIdInput.value = btn.getAttribute('data-invoice-id');
                }
            });
        });
    });
    let SEND_TEMPLATES = [];
    // Fetch all templates once and store in SEND_TEMPLATES
    fetch("{{ route('message-templates-invoice') }}")
        .then(res => {
            if (!res.ok) {
                throw new Error('Failed to load templates');
            }
            return res.json();
        })
        .then(data => {
            SEND_TEMPLATES = data;
            console.log('Templates loaded:', SEND_TEMPLATES);
            updateTemplateGrid();
        })
        .catch(error => {
            console.error('Error loading templates:', error);
            document.getElementById('template-grid-area').innerHTML =
                '<div class="alert alert-danger"><i class="ri-error-warning-line me-1"></i>Failed to load templates. Please refresh the page.</div>';
        });

    function updateTemplateGrid() {
        const gridArea = document.getElementById('template-grid-area');
        if (!gridArea) return;
        gridArea.innerHTML = '';
        const selectedChannels = Array.from(document.querySelectorAll('.channel-checkbox:checked')).map(cb => cb.value);
        selectedChannels.forEach(channel => {
            const channelTemplates = SEND_TEMPLATES.filter(t => t.channel === channel);
            if (channelTemplates.length === 0) {
                // Show message if no templates available
                const channelTitle = channel.charAt(0).toUpperCase() + channel.slice(1);
                gridArea.innerHTML += `<div class='alert alert-warning mb-3'>
                    <i class='ri-alert-line me-1'></i>No ${channelTitle} templates available. Default template will be used.
                </div>`;
                return;
            }
            const channelTitle = channel.charAt(0).toUpperCase() + channel.slice(1);
            let html =
                `<div class='mb-2'><strong>${channelTitle} Templates</strong></div><div class='row g-2 mb-3'>`;
            channelTemplates.forEach(template => {
                const isDefault = template.is_default;
                // For email templates, show subject if available
                let displayContent = '';
                if (channel === 'email' && template.subject) {
                    displayContent =
                        `<div class='text-muted small mb-1'><strong>Subject:</strong> ${escapeHtml(template.subject)}</div>`;
                }
                // Show content preview (limit length for display)
                const contentPreview = template.content ? escapeHtml(String(template.content).substring(
                    0, 100)) : '';
                if (contentPreview) {
                    displayContent +=
                        `<div class='text-muted small'>${contentPreview}${template.content && template.content.length > 100 ? '...' : ''}</div>`;
                } else {
                    displayContent +=
                        `<div class='text-muted small'><em>No preview available</em></div>`;
                }

                html += `<div class='col-12 col-md-4'>
                    <div class='card template-card ${isDefault ? 'border-primary' : ''}' data-template-id='${template.id}' data-channel='${channel}' style='cursor:pointer;'>
                        <div class='card-body p-2'>
                            <div class='d-flex justify-content-between align-items-center mb-1'>
                                <span class='fw-semibold'>${escapeHtml(template.name)}</span>
                                ${isDefault ? '<span class="badge bg-primary">Default</span>' : ''}
                            </div>
                            ${displayContent}
                        </div>
                    </div>
                </div>`;
            });
            html += `</div>`;
            gridArea.innerHTML += html;
        });
        // Function to recursively check for InvoiceBlock in template JSON
        function checkForInvoiceBlock(data) {
            if (!data || typeof data !== 'object') return false;

            // Check if this is an InvoiceBlock
            if (data.type === 'InvoiceBlock') return true;

            // Recursively check all values
            for (let key in data) {
                if (data.hasOwnProperty(key)) {
                    if (checkForInvoiceBlock(data[key])) return true;
                }
            }

            return false;
        }

        // Function to update PDF checkbox visibility based on selected channels and templates
        function updatePdfCheckboxVisibility() {
            // Handle Email PDF checkbox
            const emailPdfSection = document.getElementById('include-pdf-section-email');
            if (emailPdfSection) {
                const emailChannelChecked = document.getElementById('channel-email')?.checked;
                if (!emailChannelChecked) {
                    emailPdfSection.style.display = 'none';
                } else {
                    // Find selected email template
                    const emailTemplateInput = document.querySelector('input[name="selected_template[email]"]');
                    if (!emailTemplateInput || !emailTemplateInput.value) {
                        // No template selected, check if any email template has InvoiceBlock
                        const emailTemplates = SEND_TEMPLATES.filter(t => t.channel === 'email');
                        let hasAnyInvoiceBlock = false;
                        for (let template of emailTemplates) {
                            if (template.template_json) {
                                try {
                                    const templateJson = typeof template.template_json === 'string' ?
                                        JSON.parse(template.template_json) :
                                        template.template_json;
                                    if (checkForInvoiceBlock(templateJson)) {
                                        hasAnyInvoiceBlock = true;
                                        break;
                                    }
                                } catch (e) {
                                    console.error('Error parsing template JSON:', e);
                                }
                            }
                        }
                        emailPdfSection.style.display = hasAnyInvoiceBlock ? 'block' : 'none';
                    } else {
                        // Check selected template
                        const templateId = emailTemplateInput.value;
                        const template = SEND_TEMPLATES.find(t => t.id == templateId && t.channel === 'email');

                        if (template && template.template_json) {
                            try {
                                const templateJson = typeof template.template_json === 'string' ?
                                    JSON.parse(template.template_json) :
                                    template.template_json;
                                const hasInvoiceBlock = checkForInvoiceBlock(templateJson);
                                emailPdfSection.style.display = hasInvoiceBlock ? 'block' : 'none';
                            } catch (e) {
                                console.error('Error parsing template JSON:', e);
                                emailPdfSection.style.display = 'none';
                            }
                        } else {
                            emailPdfSection.style.display = 'none';
                        }
                    }
                }
            }

            // Handle WhatsApp PDF checkbox
            const whatsappPdfSection = document.getElementById('include-pdf-section-whatsapp');
            if (whatsappPdfSection) {
                const whatsappChannelChecked = document.getElementById('channel-whatsapp')?.checked;
                whatsappPdfSection.style.display = whatsappChannelChecked ? 'block' : 'none';
            }
        }

        // Add click handler to select template
        document.querySelectorAll('.template-card').forEach(card => {
            card.onclick = function() {
                // Remove highlight from other templates in this channel
                const channel = card.getAttribute('data-channel');
                document.querySelectorAll('.template-card[data-channel="' + channel + '"]').forEach(c => c
                    .classList.remove('border-success', 'border-2'));
                card.classList.add('border-success', 'border-2');
                // Set hidden input for selected template
                let input = document.querySelector('input[name="selected_template[' + channel + ']"]');
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_template[' + channel + ']';
                    card.closest('form').appendChild(input);
                }
                input.value = card.getAttribute('data-template-id');

                // Update PDF checkbox visibility
                updatePdfCheckboxVisibility();
            };
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
    // Update grid on channel checkbox change
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.channel-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                updateTemplateGrid();
                // Update PDF checkbox visibility when channels change
                setTimeout(updatePdfCheckboxVisibility, 100);
            });
        });

        // Also check on initial load if email is already selected
        setTimeout(updatePdfCheckboxVisibility, 200);
    });
</script>


@section('scripts')
    <!-- APEX CHARTS JS -->
    <script src="{{ asset('build/assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Dynamic Chart Data -->
    <script>
        (function() {
            "use strict"

            // Dynamic data from backend
            var chartData = @json($chartData);

            // for invoice stats
            var options = {
                series: [{
                    name: 'Total',
                    data: chartData.total
                }, {
                    name: 'Paid',
                    data: chartData.paid
                }, {
                    name: 'Pending',
                    data: chartData.pending
                }, {
                    name: 'Overdue',
                    data: chartData.overdue
                }],
                chart: {
                    type: 'bar',
                    height: 210,
                    stacked: true
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '25%',
                        endingShape: 'rounded',
                    },
                },
                grid: {
                    borderColor: '#f2f5f7',
                },
                dataLabels: {
                    enabled: false
                },
                colors: ["#4b9bfa", "#28d193", "#ffbe14", "#f3f6f8"],
                stroke: {
                    show: true,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: chartData.categories,
                    labels: {
                        show: true,
                        style: {
                            colors: "#8c9097",
                            fontSize: '11px',
                            fontWeight: 600,
                            cssClass: 'apexcharts-xaxis-label',
                        },
                    }
                },
                yaxis: {
                    title: {
                        style: {
                            color: "#8c9097",
                        }
                    },
                    labels: {
                        show: true,
                        style: {
                            colors: "#8c9097",
                            fontSize: '11px',
                            fontWeight: 600,
                            cssClass: 'apexcharts-xaxis-label',
                        },
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + " invoices"
                        }
                    }
                }
            };
            var chart = new ApexCharts(document.querySelector("#invoice-list-stats"), options);
            chart.render();
        })();
    </script>
@endsection
