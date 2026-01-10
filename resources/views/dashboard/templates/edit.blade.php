@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Edit Message Template</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('templates.index') }}">Templates</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Page Header Close -->

        <div class="row">
            <!-- Form Section -->
            <div class="col-xl-6">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">
                            <i class="ri-edit-line me-2"></i>Edit Template
                        </div>
                        <a href="{{ route('templates.index') }}" class="btn btn-sm btn-light">
                            <i class="ri-arrow-left-line me-1"></i> Back
                        </a>
                    </div>
                    <form method="POST" action="{{ route('templates.update', $template->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <!-- Template Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Template Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $template->name) }}"
                                    placeholder="e.g., Payment Reminder" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Channel Selection -->
                            <div class="mb-3">
                                <label for="channel" class="form-label">Channel <span class="text-danger">*</span></label>
                                <select name="channel" id="channel"
                                    class="form-select @error('channel') is-invalid @enderror" required>
                                    <option value="whatsapp"
                                        {{ old('channel', $template->channel) == 'whatsapp' ? 'selected' : '' }}>
                                        📱 WhatsApp
                                    </option>
                                    <option value="sms"
                                        {{ old('channel', $template->channel) == 'sms' ? 'selected' : '' }}>
                                        💬 SMS
                                    </option>
                                </select>
                                @error('channel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Message Content -->
                            <div class="mb-3">
                                <label for="content" class="form-label">Message Content <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control @error('content') is-invalid @enderror" name="content" id="content" rows="10"
                                    required placeholder="Write your message here...">{{ old('content', $template->content) }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="mt-2">
                                    <div class="alert alert-info mb-0" role="alert">
                                        <strong>Available Variables:</strong>
                                        <div class="mt-2">
                                            @foreach ($availableVariables ?? [] as $variable => $description)
                                                @php
                                                    $varName = str_replace(['@{{ ', ' }}'], '', $variable);
                                                @endphp
                                                <span class="badge bg-primary-transparent me-1 mb-1 cursor-pointer"
                                                    data-bs-toggle="tooltip" title="{{ $description }}"
                                                    onclick="insertVariable('{{ $varName }}')">
                                                    {{ $variable }}
                                                </span>
                                            @endforeach
                                        </div>
                                        <small class="d-block mt-2 text-muted">💡 Click a variable to insert it at cursor
                                            position</small>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Settings -->
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Set as Default</label>
                                        <select name="is_default" class="form-select js-example-basic-single">
                                            <option value="0"
                                                {{ old('is_default', $template->is_default) == '0' ? 'selected' : '' }}>No
                                            </option>
                                            <option value="1"
                                                {{ old('is_default', $template->is_default) == '1' ? 'selected' : '' }}>Yes
                                            </option>
                                        </select>
                                        <small class="text-muted">Only one default per channel</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="is_active" class="form-select js-example-basic-single">
                                            <option value="1"
                                                {{ old('is_active', $template->is_active) == '1' ? 'selected' : '' }}>
                                                Active</option>
                                            <option value="0"
                                                {{ old('is_active', $template->is_active) == '0' ? 'selected' : '' }}>
                                                Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Created/Updated Info -->
                            <div class="alert alert-light" role="alert">
                                <small class="text-muted">
                                    <i class="ri-information-line me-1"></i>
                                    Created: {{ $template->created_at->format('M d, Y h:i A') }}
                                    @if ($template->updated_at && $template->updated_at != $template->created_at)
                                        | Last Updated: {{ $template->updated_at->diffForHumans() }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('templates.index') }}" class="btn btn-light">
                                    <i class="ri-close-line me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i> Update Template
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Character Counter Card (SMS only) -->
                <div class="card custom-card" id="sms-limit-card" style="display: none;">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-semibold mb-1">Character Count</h6>
                                <p class="text-muted mb-0 fs-12">SMS has a 160 character limit per message</p>
                            </div>
                            <div class="text-end">
                                <h3 class="mb-0" id="char-count">0</h3>
                                <small class="text-muted" id="sms-parts">1 SMS</small>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 6px;">
                            <div class="progress-bar" id="char-progress" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Preview Section -->
            <div class="col-xl-6">
                <div class="card custom-card sticky-top" style="top: 20px;">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="ri-smartphone-line me-2"></i>Live Preview
                        </div>
                    </div>
                    <div class="card-body"
                        style="min-height: 500px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">

                        <!-- WhatsApp Preview -->
                        <div id="preview-whatsapp" style="display: none; width: 100%; max-width: 360px;">
                            <div
                                style="background: #000; border-radius: 40px; padding: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
                                <div style="background: #ece5dd; border-radius: 28px; overflow: hidden;">
                                    <div
                                        style="background: #075e54; color: #fff; padding: 12px 16px; display: flex; align-items: center; gap: 12px;">
                                        <div
                                            style="width: 36px; height: 36px; border-radius: 50%; background: #25D366; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                            💼</div>
                                        <div>
                                            <div style="font-size: 14px; font-weight: 500;">Your Business</div>
                                            <div style="font-size: 11px; opacity: 0.8;">Online</div>
                                        </div>
                                    </div>
                                    <div style="padding: 16px; min-height: 400px;">
                                        <div style="text-align: left;">
                                            <div style="background: #fff; color: #000; padding: 8px 12px; border-radius: 12px 12px 12px 4px; max-width: 85%; font-size: 14px; line-height: 1.5; word-wrap: break-word; box-shadow: 0 1px 2px rgba(0,0,0,0.1); white-space: pre-wrap;"
                                                id="preview-body-whatsapp">
                                                Loading...
                                            </div>
                                            <div style="font-size: 11px; color: #667781; margin-top: 4px; padding: 0 8px;">
                                                <i class="ri-check-double-line"></i> 10:30 AM
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        style="background: #f0f0f0; padding: 8px 12px; display: flex; gap: 8px; align-items: center;">
                                        <span style="font-size: 20px;">😊</span>
                                        <div
                                            style="flex: 1; background: #fff; border-radius: 20px; padding: 8px 12px; font-size: 13px; color: #999;">
                                            Type a message...</div>
                                        <span style="font-size: 20px;">🎤</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SMS Preview -->
                        <div id="preview-sms" style="display: none; width: 100%; max-width: 340px;">
                            <div
                                style="background: #000; border-radius: 40px; padding: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
                                <div style="background: #000; border-radius: 28px; overflow: hidden; padding: 20px 16px;">
                                    <div style="text-align: center; color: #fff; font-size: 12px; margin-bottom: 20px;">
                                        10:30 AM</div>
                                    <div style="background: #34C759; color: #fff; padding: 12px 16px; border-radius: 18px; font-size: 15px; line-height: 1.5; word-wrap: break-word; white-space: pre-wrap; box-shadow: 0 4px 12px rgba(52, 199, 89, 0.3);"
                                        id="preview-body-sms">
                                        Loading...
                                    </div>
                                    <div style="text-align: right; margin-top: 6px;">
                                        <span style="color: #8E8E93; font-size: 11px;">Delivered</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const channelSelect = document.getElementById('channel');
        const contentInput = document.getElementById('content');
        const previewWhatsapp = document.getElementById('preview-whatsapp');
        const previewSms = document.getElementById('preview-sms');
        const previewBodyWhatsapp = document.getElementById('preview-body-whatsapp');
        const previewBodySms = document.getElementById('preview-body-sms');
        const smsLimitCard = document.getElementById('sms-limit-card');
        const charCount = document.getElementById('char-count');
        const charProgress = document.getElementById('char-progress');
        const smsParts = document.getElementById('sms-parts');

        const sampleData = {
            'client_name': 'John Doe',
            'invoice_number': 'INV-2026-001',
            'amount': '$1,500.00',
            'due_date': 'Jan 15, 2026',
            'invoice_link': 'https://yourapp.com/inv/123',
            'business_name': 'Your Business'
        };

        function replacePlaceholders(text) {
            let result = text;
            Object.entries(sampleData).forEach(([key, value]) => {
                const regex = new RegExp(`@{{ \\
s * $ {
    key
}\\
s * }}`, 'g');
                result = result.replace(regex, value);
            });
            return result || 'Your message will appear here...';
        }

        function updatePreview() {
            const channel = channelSelect.value;
            const content = contentInput.value;
            const processedContent = replacePlaceholders(content);

            previewWhatsapp.style.display = 'none';
            previewSms.style.display = 'none';
            smsLimitCard.style.display = 'none';

            if (channel === 'whatsapp') {
                previewWhatsapp.style.display = '';
                previewBodyWhatsapp.textContent = processedContent;
            } else if (channel === 'sms') {
                previewSms.style.display = '';
                previewBodySms.textContent = processedContent;
                smsLimitCard.style.display = '';
                updateCharCount(content.length);
            }
        }

        function updateCharCount(count) {
            charCount.textContent = count;
            const parts = Math.ceil(count / 160) || 1;
            smsParts.textContent = `${parts} SMS`;
            const percentage = (count % 160) / 160 * 100;
            charProgress.style.width = percentage + '%';

            if (count > 160) {
                charProgress.classList.remove('bg-success', 'bg-warning');
                charProgress.classList.add('bg-danger');
            } else if (count > 120) {
                charProgress.classList.remove('bg-success', 'bg-danger');
                charProgress.classList.add('bg-warning');
            } else {
                charProgress.classList.remove('bg-warning', 'bg-danger');
                charProgress.classList.add('bg-success');
            }
        }

        function insertVariable(varName) {
            const textarea = contentInput;
            const cursorPos = textarea.selectionStart;
            const textBefore = textarea.value.substring(0, cursorPos);
            const textAfter = textarea.value.substring(cursorPos);
            const variable = `@{{ $ {
    varName
} }}`;

            textarea.value = textBefore + variable + textAfter;
            textarea.focus();
            textarea.setSelectionRange(cursorPos + variable.length, cursorPos + variable.length);

            updatePreview();
        }

        channelSelect.addEventListener('change', updatePreview);
        contentInput.addEventListener('input', updatePreview);

        // Initial preview
        updatePreview();
    </script>
    <style>
        .cursor-pointer {
            cursor: pointer;
        }

        .cursor-pointer:hover {
            opacity: 0.8;
        }
    </style>
@endsection
