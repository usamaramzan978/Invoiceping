@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">{{ isset($rule) ? 'Edit' : 'Create' }} Reminder Rule</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Automation</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('reminder-rules.index') }}">Reminder Rules</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ isset($rule) ? 'Edit' : 'Create' }} Rule
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Page Header Close -->

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

        <form
            action="{{ isset($rule) ? route('reminder-rules.update', ['rule' => $rule]) : route('reminder-rules.store') }}"
            method="POST">
            @csrf
            @if (isset($rule))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">Rule Details</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Rule Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $rule->name ?? '') }}"
                                        placeholder="e.g. Default 3-Step Follow-up" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default"
                                            value="1"
                                            {{ old('is_default', $rule->is_default ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_default">Set as Default Rule</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">Reminder Steps</div>
                            <button type="button" class="btn btn-sm btn-secondary" id="add-step" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="Add New Step">
                                <i class="ri-add-line me-1"></i> Add Step
                            </button>
                        </div>
                        <div class="card-body" id="steps-container">
                            @php
                                $steps = old('steps', isset($rule) ? $rule->steps->toArray() : []);
                            @endphp

                            @foreach ($steps as $index => $step)
                                <div class="step-item card border shadow-none mb-3" data-index="{{ $index }}">
                                    <div class="card-header justify-content-between py-2">
                                        <div class="fw-semibold">Step #<span class="step-number">{{ $index + 1 }}</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger-light remove-step"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Remove Step">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="row gy-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Reminder Type</label>
                                                <select name="steps[{{ $index }}][reminder_type]"
                                                    class="form-select select2-single" required>
                                                    @foreach (\App\Enums\ReminderTypeEnum::cases() as $type)
                                                        <option value="{{ $type->value }}"
                                                            {{ ($step['reminder_type'] ?? '') == $type->value ? 'selected' : '' }}>
                                                            {{ $type->label() }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Offset Days</label>
                                                <input type="number" name="steps[{{ $index }}][offset_days]"
                                                    class="form-control" value="{{ $step['offset_days'] ?? 0 }}" required>
                                                <small class="text-muted">Before due: -N, On due: 0, After due: +N</small>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Channels & Templates</label>
                                                <div class="channel-list">
                                                    @php
                                                        $channels =
                                                            $step['channels'] ??
                                                            (isset($rule)
                                                                ? $rule->steps[$index]->templates
                                                                    ->map(
                                                                        fn($t) => [
                                                                            'channel' => $t->channel,
                                                                            'message_template_id' =>
                                                                                $t->message_template_id,
                                                                        ],
                                                                    )
                                                                    ->toArray()
                                                                : []);
                                                    @endphp
                                                    {{-- Email Template (from email_templates table) --}}
                                                    @php
                                                        $selectedEmailTemplate =
                                                            collect($channels)->firstWhere('channel', 'email')[
                                                                'email_template_id'
                                                            ] ?? null;
                                                    @endphp
                                                    <div class="input-group mb-2">
                                                        <span class="input-group-text" style="width: 100px;">📧 Email</span>
                                                        <input type="hidden"
                                                            name="steps[{{ $index }}][channels][email][channel]"
                                                            value="email">
                                                        <select
                                                            name="steps[{{ $index }}][channels][email][email_template_id]"
                                                            class="form-select select2-single email-template-select-rule"
                                                            data-step-index="{{ $index }}">
                                                            <option value="">Select Template</option>
                                                            @foreach ($emailTemplates as $template)
                                                                <option value="{{ $template->id }}"
                                                                    {{ $selectedEmailTemplate == $template->id ? 'selected' : '' }}>
                                                                    {{ $template->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    @php
                                                        $emailChannelData = collect($channels)->firstWhere(
                                                            'channel',
                                                            'email',
                                                        );
                                                        $includePdfValue = $emailChannelData['include_pdf'] ?? false;
                                                    @endphp
                                                    <div class="include-pdf-section-rule-{{ $index }} mb-2"
                                                        style="display: none;">
                                                        <div class="form-check ms-4">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="steps[{{ $index }}][channels][email][include_pdf]"
                                                                id="include-pdf-rule-{{ $index }}" value="1"
                                                                {{ $includePdfValue ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="include-pdf-rule-{{ $index }}">
                                                                <strong>Include Invoice PDF</strong>
                                                                <small class="d-block text-muted mt-1">
                                                                    <i class="ri-information-line me-1"></i>
                                                                    Attach invoice PDF if the selected email template
                                                                    contains an invoice block
                                                                </small>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    {{-- WhatsApp Template (from message_templates table) --}}
                                                    @php
                                                        $selectedWhatsAppTemplate =
                                                            collect($channels)->firstWhere('channel', 'whatsapp')[
                                                                'message_template_id'
                                                            ] ?? null;
                                                        $whatsappChannelData = collect($channels)->firstWhere(
                                                            'channel',
                                                            'whatsapp',
                                                        );
                                                        $includePdfWhatsappValue =
                                                            $whatsappChannelData['include_pdf'] ?? false;
                                                    @endphp
                                                    <div class="input-group mb-2">
                                                        <span class="input-group-text" style="width: 100px;">📱
                                                            WhatsApp</span>
                                                        <input type="hidden"
                                                            name="steps[{{ $index }}][channels][whatsapp][channel]"
                                                            value="whatsapp">
                                                        <select
                                                            name="steps[{{ $index }}][channels][whatsapp][message_template_id]"
                                                            class="form-select select2-single whatsapp-template-select-rule"
                                                            data-step-index="{{ $index }}">
                                                            <option value="">Select Template</option>
                                                            @foreach ($messageTemplates->where('channel', 'whatsapp') as $template)
                                                                <option value="{{ $template->id }}"
                                                                    {{ $selectedWhatsAppTemplate == $template->id ? 'selected' : '' }}>
                                                                    {{ $template->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="include-pdf-section-rule-whatsapp-{{ $index }} mb-2"
                                                        style="display: none;">
                                                        <div class="form-check ms-4">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="steps[{{ $index }}][channels][whatsapp][include_pdf]"
                                                                id="include-pdf-rule-whatsapp-{{ $index }}"
                                                                value="1"
                                                                {{ $includePdfWhatsappValue ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="include-pdf-rule-whatsapp-{{ $index }}">
                                                                <strong>Include Invoice PDF (WhatsApp)</strong>
                                                                <small class="d-block text-muted mt-1">
                                                                    <i class="ri-information-line me-1"></i>
                                                                    Attach invoice PDF as document in WhatsApp message
                                                                </small>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    {{-- SMS Template (from message_templates table) --}}
                                                    {{-- @php
                                                        $selectedSMSTemplate =
                                                            collect($channels)->firstWhere('channel', 'sms')[
                                                                'message_template_id'
                                                            ] ?? null;
                                                    @endphp
                                                    <div class="input-group mb-2">
                                                        <span class="input-group-text" style="width: 100px;">💬 SMS</span>
                                                        <input type="hidden"
                                                            name="steps[{{ $index }}][channels][sms][channel]"
                                                            value="sms">
                                                        <select
                                                            name="steps[{{ $index }}][channels][sms][message_template_id]"
                                                            class="form-select select2-single">
                                                            <option value="">Select Template</option>
                                                            @foreach ($messageTemplates->where('channel', 'sms') as $template)
                                                                <option value="{{ $template->id }}"
                                                                    {{ $selectedSMSTemplate == $template->id ? 'selected' : '' }}>
                                                                    {{ $template->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div> --}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4 text-end">
                        <a href="{{ route('reminder-rules.index') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">{{ isset($rule) ? 'Update' : 'Create' }}
                            Rule</button>
                    </div>
                </div>
            </div>
        </form>

    </div>

    <template id="step-template">
        <div class="step-item card border shadow-none mb-3" data-index="__INDEX__">
            <div class="card-header justify-content-between py-2">
                <div class="fw-semibold">Step #<span class="step-number">__NUMBER__</span></div>
                <button type="button" class="btn btn-sm btn-danger-light remove-step">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="row gy-3">
                    <div class="col-md-4">
                        <label class="form-label">Reminder Type</label>
                        <select name="steps[__INDEX__][reminder_type]" class="form-select select2-single" required>
                            @foreach (\App\Enums\ReminderTypeEnum::cases() as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Offset Days</label>
                        <input type="number" name="steps[__INDEX__][offset_days]" class="form-control" value="0"
                            required>
                        <small class="text-muted">Before due: -N, On due: 0, After due: +N</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Channels & Templates</label>
                        <div class="channel-list">
                            {{-- Email --}}
                            <div class="input-group mb-2">
                                <span class="input-group-text" style="width: 100px;">📧 Email</span>
                                <input type="hidden" name="steps[__INDEX__][channels][email][channel]" value="email">
                                <select name="steps[__INDEX__][channels][email][email_template_id]"
                                    class="form-select select2-single email-template-select-rule"
                                    data-step-index="__INDEX__">
                                    <option value="">Select Template</option>
                                    @foreach ($emailTemplates as $template)
                                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="include-pdf-section-rule-__INDEX__ mb-2" style="display: none;">
                                <div class="form-check ms-4">
                                    <input class="form-check-input" type="checkbox"
                                        name="steps[__INDEX__][channels][email][include_pdf]"
                                        id="include-pdf-rule-__INDEX__" value="1" checked>
                                    <label class="form-check-label" for="include-pdf-rule-__INDEX__">
                                        <strong>Include Invoice PDF</strong>
                                        <small class="d-block text-muted mt-1">
                                            <i class="ri-information-line me-1"></i>
                                            Attach invoice PDF if the selected email template contains an invoice block
                                        </small>
                                    </label>
                                </div>
                            </div>
                            {{-- WhatsApp --}}
                            <div class="input-group mb-2">
                                <span class="input-group-text" style="width: 100px;">📱 WhatsApp</span>
                                <input type="hidden" name="steps[__INDEX__][channels][whatsapp][channel]"
                                    value="whatsapp">
                                <select name="steps[__INDEX__][channels][whatsapp][message_template_id]"
                                    class="form-select select2-single whatsapp-template-select-rule"
                                    data-step-index="__INDEX__">
                                    <option value="">Select Template</option>
                                    @foreach ($messageTemplates->where('channel', 'whatsapp') as $template)
                                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="include-pdf-section-rule-whatsapp-__INDEX__ mb-2" style="display: none;">
                                <div class="form-check ms-4">
                                    <input class="form-check-input" type="checkbox"
                                        name="steps[__INDEX__][channels][whatsapp][include_pdf]"
                                        id="include-pdf-rule-whatsapp-__INDEX__" value="1" checked>
                                    <label class="form-check-label" for="include-pdf-rule-whatsapp-__INDEX__">
                                        <strong>Include Invoice PDF (WhatsApp)</strong>
                                        <small class="d-block text-muted mt-1">
                                            <i class="ri-information-line me-1"></i>
                                            Attach invoice PDF as document in WhatsApp message
                                        </small>
                                    </label>
                                </div>
                            </div>
                            {{-- SMS --}}
                            {{-- <div class="input-group mb-2">
                                <span class="input-group-text" style="width: 100px;">💬 SMS</span>
                                <input type="hidden" name="steps[__INDEX__][channels][sms][channel]" value="sms">
                                <select name="steps[__INDEX__][channels][sms][message_template_id]" class="form-select select2-single">
                                    <option value="">Select Template</option>
                                    @foreach ($messageTemplates->where('channel', 'sms') as $template)
                                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                                    @endforeach
                                </select>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

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

            // Function to update PDF checkbox visibility for a specific step
            function updatePdfCheckboxVisibilityRule(stepIndex) {
                // Handle Email PDF checkbox
                const emailPdfSection = document.querySelector(`.include-pdf-section-rule-${stepIndex}`);
                if (emailPdfSection) {
                    const emailSelect = document.querySelector(
                        `select.email-template-select-rule[data-step-index="${stepIndex}"]`);
                    if (emailSelect) {
                        const templateId = emailSelect.value;
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
                const whatsappPdfSection = document.querySelector(
                    `.include-pdf-section-rule-whatsapp-${stepIndex}`);
                if (whatsappPdfSection) {
                    const whatsappSelect = document.querySelector(
                        `select.whatsapp-template-select-rule[data-step-index="${stepIndex}"]`);
                    if (whatsappSelect && whatsappSelect.value) {
                        whatsappPdfSection.style.display = 'block';
                    } else {
                        whatsappPdfSection.style.display = 'none';
                    }
                }
            }

            //Needed to Reinitialize Select2 so that it wokrs on dynamically added elements
            $('.select2-single').select2();

            const container = document.getElementById('steps-container');
            const addButton = document.getElementById('add-step');
            const template = document.getElementById('step-template').innerHTML;

            function updateStepNumbers() {
                container.querySelectorAll('.step-item').forEach((item, index) => {
                    item.querySelector('.step-number').textContent = index + 1;
                    item.setAttribute('data-index', index);
                    // Update input names
                    item.querySelectorAll('[name*="steps["]').forEach(input => {
                        input.name = input.name.replace(/steps\[\d+\]/, `steps[${index}]`);
                    });
                });
            }

            addButton.addEventListener('click', function() {
                const index = container.querySelectorAll('.step-item').length;
                let html = template.replace(/__INDEX__/g, index).replace(/__NUMBER__/g, index + 1);
                container.insertAdjacentHTML('beforeend', html);

                // Reinitialize Select2 for the new step
                $(container.lastElementChild).find('.select2-single').select2();

                // Add event listener for email template selection
                const emailSelect = container.lastElementChild.querySelector(
                    `select.email-template-select-rule[data-step-index="${index}"]`);
                if (emailSelect) {
                    $(emailSelect).on('select2:select', function() {
                        updatePdfCheckboxVisibilityRule(index);
                    });
                }

                // Add event listener for WhatsApp template selection
                const whatsappSelect = container.lastElementChild.querySelector(
                    `select.whatsapp-template-select-rule[data-step-index="${index}"]`);
                if (whatsappSelect) {
                    $(whatsappSelect).on('select2:select', function() {
                        updatePdfCheckboxVisibilityRule(index);
                    });
                }
            });

            container.addEventListener('click', function(e) {
                if (e.target.closest('.remove-step')) {
                    e.target.closest('.step-item').remove();
                    updateStepNumbers();
                }
            });

            // Initialize if empty
            if (container.querySelectorAll('.step-item').length === 0) {
                addButton.click();
            }

            // Add event listeners for existing email template selects
            container.querySelectorAll('.email-template-select-rule').forEach(select => {
                const stepIndex = select.getAttribute('data-step-index');
                $(select).on('select2:select', function() {
                    updatePdfCheckboxVisibilityRule(stepIndex);
                });
                // Check on initial load
                updatePdfCheckboxVisibilityRule(stepIndex);
            });

            // Add event listeners for existing WhatsApp template selects
            container.querySelectorAll('.whatsapp-template-select-rule').forEach(select => {
                const stepIndex = select.getAttribute('data-step-index');
                $(select).on('select2:select', function() {
                    updatePdfCheckboxVisibilityRule(stepIndex);
                });
                // Check on initial load
                updatePdfCheckboxVisibilityRule(stepIndex);
            });

        });
    </script>
@endsection
