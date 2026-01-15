@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Schedule Manual Reminder</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Automation</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('schedule-reminders.index') }}">All Scheduled</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Manual Scheduled</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Alert Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-check-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-8">
                <div class="card custom-card">
                    <div class="card-header bg-light">
                        <div class="card-title">Manual Reminder Configuration</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('manual-scheduled.store') }}" method="POST" id="reminderForm">
                            @csrf
                            <div class="row gy-4">

                                <!-- STEP 1: Select Invoice -->
                                <div class="col-md-6">
                                    <label for="invoice_id" class="form-label fw-600">
                                        <span class="badge bg-primary">Step 1</span> Select Invoice(s)
                                    </label>
                                    <select name="invoice_ids[]" id="invoice_id"
                                        class="form-select select2-multiple-invoice" required multiple>
                                        @foreach ($invoices as $invoice)
                                            <option value="{{ $invoice->id }}"
                                                {{ is_array(old('invoice_ids')) && in_array($invoice->id, old('invoice_ids')) ? 'selected' : '' }}>
                                                #{{ $invoice->invoice_number }} - {{ $invoice->client->name }}
                                                ({{ $invoice->total_amount }} {{ $invoice->currency }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('invoice_ids')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- STEP 2: Select Channel -->
                                <div class="col-md-6">
                                    <label for="channel" class="form-label fw-600">
                                        <span class="badge bg-info">Step 2</span> Select Channel
                                    </label>
                                    <select name="channel" id="channel" class="form-select select2-single" required>
                                        <option value="">Choose a channel...</option>
                                        @foreach (\App\Enums\MessageChannel::cases() as $channel)
                                            <option value="{{ $channel->value }}"
                                                {{ old('channel') == $channel->value ? 'selected' : '' }}>
                                                {{ $channel->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('channel')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- STEP 3: Select Template (Email) -->
                                <div class="col-md-6" id="email-template-section" style="display: none;">
                                    <label for="email_template_id" class="form-label fw-600">
                                        <span class="badge bg-info">Step 3</span> Select Email Template
                                    </label>
                                    <select name="email_template_id" id="email_template_id"
                                        class="form-select select2-single">
                                        <option value="">Select an email template...</option>
                                        @foreach ($emailTemplates as $template)
                                            <option value="{{ $template->id }}"
                                                {{ old('email_template_id') == $template->id ? 'selected' : '' }}>
                                                {{ $template->name }}
                                                @if ($template->is_default)
                                                    <span class="text-muted">(Default)</span>
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('email_template_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror

                                    <!-- Include PDF Checkbox (only for email templates with InvoiceBlock) -->
                                    <div id="include-pdf-section-manual-email" class="mt-3" style="display: none;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="include_pdf"
                                                id="include-pdf-checkbox-manual-email" value="1" checked>
                                            <label class="form-check-label" for="include-pdf-checkbox-manual-email">
                                                <strong>Include Invoice PDF (Email)</strong>
                                                <small class="d-block text-muted mt-1">
                                                    <i class="ri-information-line me-1"></i>
                                                    Attach invoice PDF if the selected email template contains an invoice
                                                    block
                                                </small>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP 3: Select Template (WhatsApp/SMS) -->
                                <div class="col-md-6" id="message-template-section" style="display: none;">
                                    <label for="message_template_id" class="form-label fw-600">
                                        <span class="badge bg-info">Step 3</span> Select Message Template
                                    </label>
                                    <select name="message_template_id" id="message_template_id"
                                        class="form-select select2-single">
                                        <option value="">Select a template...</option>
                                        @foreach ($messageTemplates as $template)
                                            <option value="{{ $template->id }}" data-channel="{{ $template->channel }}"
                                                {{ old('message_template_id') == $template->id ? 'selected' : '' }}>
                                                {{ $template->name }}
                                                <span class="text-muted">({{ ucfirst($template->channel) }})</span>
                                                @if ($template->is_default)
                                                    <span class="text-muted">- Default</span>
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('message_template_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror

                                    <!-- Include PDF Checkbox (for WhatsApp) -->
                                    <div id="include-pdf-section-manual-whatsapp" class="mt-3" style="display: none;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="include_pdf"
                                                id="include-pdf-checkbox-manual-whatsapp" value="1" checked>
                                            <label class="form-check-label" for="include-pdf-checkbox-manual-whatsapp">
                                                <strong>Include Invoice PDF (WhatsApp)</strong>
                                                <small class="d-block text-muted mt-1">
                                                    <i class="ri-information-line me-1"></i>
                                                    Attach invoice PDF as document in WhatsApp message
                                                </small>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP 4: Schedule Date/Time -->
                                <div class="col-md-6" id="scheduled-at-container">
                                    <label for="scheduled_at" class="form-label fw-600">
                                        <span class="badge bg-success">Step 4</span> Scheduled Date & Time
                                    </label>
                                    <input type="datetime-local" class="form-control" id="scheduled_at"
                                        name="scheduled_at" value="{{ old('scheduled_at') }}" required>
                                    <small class="text-muted d-block mt-1">⏰ When should this reminder be sent?</small>
                                    @error('scheduled_at')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Form Actions -->
                                <div class="col-12 text-end pt-3">
                                    <a href="{{ route('schedule-reminders.index') }}" class="btn btn-secondary me-2">
                                        <i class="ri-arrow-left-line"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-check-line"></i> Schedule Reminder
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Info Card -->
            <div class="col-xl-4">
                <div class="card custom-card border-primary">
                    <div class="card-header bg-light">
                        <div class="card-title text-primary">💡 How Manual Scheduling Works</div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="fw-600 mb-2">✏️ One-Time Reminders</h6>
                            <p class="text-muted small mb-0">Manual scheduling allows you to send a single reminder at a
                                specific time. Perfect for one-off reminders or custom follow-ups.</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <h6 class="fw-600 mb-2">📧 Multiple Channels</h6>
                            <p class="text-muted small mb-0">Choose from Email, WhatsApp, or SMS. Each channel requires its
                                own template, so make sure you have the appropriate template ready.</p>
                        </div>
                        <hr>
                        <div class="alert alert-info py-2 mb-0" style="font-size: 12px;">
                            <strong>Pro Tip:</strong> Use manual scheduling for custom reminders that don't fit into your
                            automated rule workflows.
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const channelSelect = document.getElementById('channel');
            const emailTemplateSelect = document.getElementById('email_template_id');
            const messageTemplateSelect = document.getElementById('message_template_id');
            const emailTemplateSection = document.getElementById('email-template-section');
            const messageTemplateSection = document.getElementById('message-template-section');
            const scheduledAtInput = document.getElementById('scheduled_at');
            const reminderForm = document.getElementById('reminderForm');

            const allMessageTemplateOptions = Array.from(messageTemplateSelect.options);

            // Store email templates data for InvoiceBlock detection
            const EMAIL_TEMPLATES_DATA = @json($emailTemplatesJson ?? []);

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

            // Function to update PDF checkbox visibility based on selected channel and template
            function updatePdfCheckboxVisibilityManual() {
                // Handle Email PDF checkbox
                const emailPdfSection = document.getElementById('include-pdf-section-manual-email');
                if (emailPdfSection) {
                    if (channelSelect.value !== 'email') {
                        emailPdfSection.style.display = 'none';
                    } else {
                        // Check selected email template
                        const templateId = emailTemplateSelect.value;
                        if (!templateId) {
                            emailPdfSection.style.display = 'none';
                        } else {
                            const template = EMAIL_TEMPLATES_DATA.find(t => t.id == templateId);
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
                const whatsappPdfSection = document.getElementById('include-pdf-section-manual-whatsapp');
                if (whatsappPdfSection) {
                    whatsappPdfSection.style.display = (channelSelect.value === 'whatsapp') ? 'block' : 'none';
                }
            }

            // Update template sections based on channel
            function updateTemplateSections() {
                const selectedChannel = channelSelect.value;

                if (selectedChannel === 'email') {
                    emailTemplateSection.style.display = 'block';
                    messageTemplateSection.style.display = 'none';
                    emailTemplateSelect.setAttribute('required', 'required');
                    messageTemplateSelect.removeAttribute('required');
                    messageTemplateSelect.value = '';
                } else if (selectedChannel === 'whatsapp' || selectedChannel === 'sms') {
                    emailTemplateSection.style.display = 'none';
                    messageTemplateSection.style.display = 'block';
                    emailTemplateSelect.removeAttribute('required');
                    messageTemplateSelect.setAttribute('required', 'required');
                    emailTemplateSelect.value = '';

                    // Filter message templates by channel
                    filterMessageTemplatesByChannel();
                } else {
                    emailTemplateSection.style.display = 'none';
                    messageTemplateSection.style.display = 'none';
                    emailTemplateSelect.removeAttribute('required');
                    messageTemplateSelect.removeAttribute('required');
                }

                // Update PDF checkbox visibility when channel changes
                updatePdfCheckboxVisibilityManual();
            }

            // Filter message templates by channel
            function filterMessageTemplatesByChannel() {
                const selectedChannel = channelSelect.value;

                messageTemplateSelect.innerHTML = '<option value="">Select a template...</option>';

                allMessageTemplateOptions.forEach(option => {
                    if (option.value === '') return;

                    const templateChannel = option.getAttribute('data-channel');
                    if (!selectedChannel || templateChannel === selectedChannel) {
                        messageTemplateSelect.appendChild(option.cloneNode(true));
                    }
                });

                $(messageTemplateSelect).select2({
                    allowClear: false,
                    width: '100%'
                });
            }

            // Initialize Select2
            $('.select2-multiple-invoice').select2({
                placeholder: "Select invoices...",
                allowClear: false,
                width: '100%'
            });

            $(channelSelect).select2({
                allowClear: false,
                width: '100%'
            });

            $(emailTemplateSelect).select2({
                allowClear: false,
                width: '100%'
            });

            $(messageTemplateSelect).select2({
                allowClear: false,
                width: '100%'
            });

            // Event listeners
            $(channelSelect).on('select2:select', function() {
                updateTemplateSections();
            });

            $(emailTemplateSelect).on('select2:select', function() {
                updatePdfCheckboxVisibilityManual();
            });

            // Form validation
            reminderForm.addEventListener('submit', function(e) {
                if (!channelSelect.value) {
                    e.preventDefault();
                    alert('Please select a channel');
                    return false;
                }

                const selectedChannel = channelSelect.value;
                if (selectedChannel === 'email') {
                    if (!emailTemplateSelect.value) {
                        e.preventDefault();
                        alert('Please select an email template');
                        return false;
                    }
                } else if (selectedChannel === 'whatsapp' || selectedChannel === 'sms') {
                    if (!messageTemplateSelect.value) {
                        e.preventDefault();
                        alert('Please select a message template');
                        return false;
                    }
                }

                if (!scheduledAtInput.value) {
                    e.preventDefault();
                    alert('Please select a scheduled date and time');
                    return false;
                }

                // Validate that scheduled date is in the future
                const scheduledDate = new Date(scheduledAtInput.value);
                const now = new Date();
                if (scheduledDate <= now) {
                    e.preventDefault();
                    alert(
                    'The scheduled date must be in the future. Please select a future date and time.');
                    scheduledAtInput.focus();
                    return false;
                }
            });

            // Initialize on load
            if (channelSelect.value) {
                updateTemplateSections();
            }
            // Check PDF checkbox visibility on initial load
            setTimeout(updatePdfCheckboxVisibilityManual, 200);
        });
    </script>
@endsection
