@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Edit Reminder</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Automation</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('schedule-reminders.index') }}">Scheduled Reminders</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Reminder</li>
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
                        <div class="card-title">Reminder Configuration</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('schedule-reminders.update', $schedule->id) }}" method="POST"
                            id="reminderForm">
                            @csrf
                            @method('PUT')
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
                                                {{ $relatedSchedules->pluck('invoice_id')->contains($invoice->id) ? 'selected' : '' }}>
                                                #{{ $invoice->invoice_number }} - {{ $invoice->client->name }}
                                                ({{ $invoice->total_amount }} {{ $invoice->currency }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('invoice_ids')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- STEP 2: Select Source Type (Manual vs Rule) -->
                                <div class="col-md-6">
                                    <label for="source_type" class="form-label fw-600">
                                        <span class="badge bg-primary">Step 2</span> Reminder Setup Mode
                                    </label>
                                    <select name="source_type" id="source_type" class="form-select select2-single" required>
                                        <option value="manual" {{ $schedule->source_type == 'manual' ? 'selected' : '' }}>
                                            Manual Setup
                                        </option>
                                        <option value="rule" {{ $schedule->source_type == 'rule' ? 'selected' : '' }}>
                                            Use Predefined Rule
                                        </option>
                                    </select>
                                    @error('source_type')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- ========== SECTION A: MANUAL MODE FIELDS ========== -->
                                <div id="manual-section" class="w-100" style="display: none;">
                                    <hr class="my-3">
                                    <div class="row gy-4">
                                        <!-- Channel Selection -->
                                        <div class="col-md-6">
                                            <label for="channel" class="form-label fw-600">
                                                <span class="badge bg-info">Step 3</span> Select Channel
                                            </label>
                                            <select name="channel" id="channel" class="form-select select2-single">
                                                <option value="">Choose a channel...</option>
                                                @foreach (\App\Enums\MessageChannel::cases() as $channel)
                                                    <option value="{{ $channel->value }}"
                                                        {{ $schedule->channel == $channel->value ? 'selected' : '' }}>
                                                        {{ $channel->label() }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('channel')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Email Template (for email channel) -->
                                        <div class="col-md-6" id="email-template-section" style="display: none;">
                                            <label for="email_template_id" class="form-label fw-600">
                                                <span class="badge bg-info">Step 4</span> Select Email Template
                                            </label>
                                            <select name="email_template_id" id="email_template_id"
                                                class="form-select select2-single">
                                                <option value="">Select an email template...</option>
                                                @foreach ($emailTemplates as $template)
                                                    <option value="{{ $template->id }}"
                                                        {{ $schedule->email_template_id == $template->id ? 'selected' : '' }}>
                                                        {{ $template->name }}
                                                        @if($template->is_default)
                                                            <span class="text-muted">(Default)</span>
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('email_template_id')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Message Template (for WhatsApp/SMS channels) -->
                                        <div class="col-md-6" id="message-template-section" style="display: none;">
                                            <label for="message_template_id" class="form-label fw-600">
                                                <span class="badge bg-info">Step 4</span> Select Message Template
                                            </label>
                                            <select name="message_template_id" id="message_template_id"
                                                class="form-select select2-single">
                                                <option value="">Select a template...</option>
                                                @foreach ($messageTemplates as $template)
                                                    <option value="{{ $template->id }}"
                                                        data-channel="{{ $template->channel }}"
                                                        {{ $schedule->message_template_id == $template->id ? 'selected' : '' }}>
                                                        {{ $template->name }}
                                                        <span class="text-muted">({{ ucfirst($template->channel) }})</span>
                                                        @if($template->is_default)
                                                            <span class="text-muted">- Default</span>
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('message_template_id')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- ========== SECTION B: RULE MODE FIELD ========== -->
                                <div id="rule-section" class="w-100" style="display: none;">
                                    <hr class="my-3">
                                    <div class="row gy-4">
                                        <div class="col-md-6">
                                            <label for="reminder_rule_id" class="form-label fw-600">
                                                <span class="badge bg-success">Step 3</span> Select Predefined Rule
                                            </label>
                                            <select name="reminder_rule_id" id="reminder_rule_id"
                                                class="form-select select2-single">
                                                <option value="">Choose a rule...</option>
                                                @foreach ($rules as $rule)
                                                    <option value="{{ $rule->id }}"
                                                        {{ $schedule->reminder_rule_id == $rule->id ? 'selected' : '' }}>
                                                        {{ $rule->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted d-block mt-1">Selecting a rule will schedule all its
                                                steps automatically.</small>
                                            @error('reminder_rule_id')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP 5: Schedule Date/Time (Always Visible) -->
                                <div class="col-md-6" id="scheduled-at-container">
                                    <label for="scheduled_at" class="form-label fw-600">
                                        <span class="badge bg-success" id="step-number-badge">Step 5</span> Scheduled Date &
                                        Time
                                    </label>
                                    <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at"
                                        value="{{ $schedule->scheduled_at ? $schedule->scheduled_at->format('Y-m-d\TH:i') : '' }}" required>
                                    <small class="text-muted d-block mt-1">⏰ When should this reminder be sent? (Reference date for rules)</small>
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
                                        <i class="ri-check-line"></i> Update Reminder
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Info Card -->
            <div class="col-xl-4">
                <div class="card custom-card border-info">
                    <div class="card-header bg-light">
                        <div class="card-title text-info">💡 How It Works</div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="fw-600 mb-2">✏️ Manual Setup</h6>
                            <p class="text-muted small mb-0">Choose your own channel (Email/WhatsApp), pick a message
                                template, and set a specific time for the reminder.</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <h6 class="fw-600 mb-2">⚙️ Predefined Rule</h6>
                            <p class="text-muted small mb-0">Select a rule to automatically schedule multiple reminders
                                based on the rule's steps. The "Scheduled Date & Time" will be used as the reference date.</p>
                        </div>
                        <hr>
                        <div class="alert alert-info py-2 mb-0" style="font-size: 12px;">
                            <strong>Pro Tip:</strong> Use rules for automated follow-ups, manual setup for one-off
                            reminders.
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
            const sourceTypeSelect = document.getElementById('source_type');
            const manualSection = document.getElementById('manual-section');
            const ruleSection = document.getElementById('rule-section');
            const channelSelect = document.getElementById('channel');
            const emailTemplateSelect = document.getElementById('email_template_id');
            const messageTemplateSelect = document.getElementById('message_template_id');
            const emailTemplateSection = document.getElementById('email-template-section');
            const messageTemplateSection = document.getElementById('message-template-section');
            const reminderRuleSelect = document.getElementById('reminder_rule_id');
            const scheduledAtContainer = document.getElementById('scheduled-at-container');
            const scheduledAtInput = document.getElementById('scheduled_at');
            const reminderForm = document.getElementById('reminderForm');

            const allMessageTemplateOptions = Array.from(messageTemplateSelect.options);

            // ========== TOGGLE SECTIONS BASED ON SOURCE TYPE ==========
            function toggleSections() {
                const selectedSourceType = sourceTypeSelect.value;

                if (selectedSourceType === 'manual') {
                    manualSection.style.display = 'block';
                    ruleSection.style.display = 'none';
                    
                    channelSelect.setAttribute('required', 'required');
                    reminderRuleSelect.removeAttribute('required');
                    
                    // Show/hide template sections based on channel
                    updateTemplateSections();
                } else if (selectedSourceType === 'rule') {
                    manualSection.style.display = 'none';
                    ruleSection.style.display = 'block';
                    
                    channelSelect.removeAttribute('required');
                    emailTemplateSelect.removeAttribute('required');
                    messageTemplateSelect.removeAttribute('required');
                    reminderRuleSelect.setAttribute('required', 'required');
                    
                    // Hide both template sections
                    emailTemplateSection.style.display = 'none';
                    messageTemplateSection.style.display = 'none';
                }
                
                // scheduled_at is always visible and required now
                scheduledAtContainer.style.display = 'block';
                scheduledAtInput.setAttribute('required', 'required');
            }

            // ========== UPDATE TEMPLATE SECTIONS BASED ON CHANNEL ==========
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
            }

            // ========== FILTER MESSAGE TEMPLATES BY CHANNEL ==========
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

            // ========== FORM VALIDATION ==========
            reminderForm.addEventListener('submit', function(e) {
                const sourceType = sourceTypeSelect.value;

                if (!sourceType) {
                    e.preventDefault();
                    alert('Please select a setup mode (Manual or Rule)');
                    sourceTypeSelect.focus();
                    return false;
                }

                if (sourceType === 'manual') {
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
                } else if (sourceType === 'rule') {
                    if (!reminderRuleSelect.value) {
                        e.preventDefault();
                        alert('Please select a rule');
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
                    alert('The scheduled date must be in the future. Please select a future date and time.');
                    scheduledAtInput.focus();
                    return false;
                }
            });

            // ========== INITIALIZE SELECT2 ==========
            $(sourceTypeSelect).select2({
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

            $(reminderRuleSelect).select2({
                allowClear: false,
                width: '100%'
            });

            $('.select2-multiple-invoice').select2({
                placeholder: "Select invoices...",
                allowClear: false,
                width: '100%'
            });

            // ========== EVENT LISTENERS FOR SELECT2 CHANGE ==========
            $(sourceTypeSelect).on('select2:select', function() {
                toggleSections();
            });

            $(channelSelect).on('select2:select', function() {
                updateTemplateSections();
            });

            // ========== INITIALIZE ON LOAD ==========
            toggleSections();
            if (channelSelect.value) {
                updateTemplateSections();
            }
        });
    </script>
@endsection
