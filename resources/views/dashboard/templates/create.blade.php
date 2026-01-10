@extends('layouts.app')

@section('styles')
    <!-- QUILL CSS -->
    <link rel="stylesheet" href="{{ asset('build/assets/libs/quill/quill.snow.css') }}">
    <link rel="stylesheet" href="{{ asset('build/assets/libs/quill/quill.bubble.css') }}">
@endsection

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Create Message Template</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Message Template</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create Message Template</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="card custom-card h-100">
                    <div class="card-header bg-light">
                        <h4 class="card-title mb-0">New Template</h4>
                    </div>
                    <form method="POST" action="{{ route('templates.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="channel" class="form-label fw-500">Channel</label>
                                <select name="channel" id="channel" class="form-select" required>
                                    <option value="email">📧 Email</option>
                                    <option value="whatsapp">💬 WhatsApp</option>
                                </select>
                            </div>
                            <div class="mb-3" id="email-fields">
                                <label for="subject" class="form-label fw-500">Email Subject</label>
                                <input type="text" class="form-control" name="subject" id="subject" maxlength="150"
                                    placeholder="Enter email subject">
                            </div>
                            <div class="mb-3">
                                <label for="content" class="form-label fw-500">Message Body</label>
                                <small class="text-muted d-block mb-2">Available variables: @{{ client_name }},
                                    @{{ invoice_number }}, @{{ amount }}, @{{ due_date }}</small>
                                {{-- <textarea class="form-control" name="content" id="content" rows="6" required
                                    placeholder="Enter your message..."></textarea> --}}
                                <div id="editorContent" style="height: 200px;"></div>
                                <input type="hidden" name="content" id="content">

                                <div class="form-text" id="whatsapp-help" style="display:none;">💡 Use Shift+Enter for line
                                    breaks in WhatsApp messages</div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-500">Default Template?</label>
                                        <select name="is_default" class="form-select js-example-basic-single">
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-500">Active</label>
                                        <select name="is_active" class="form-select js-example-basic-single">
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light text-end">
                            <a href="{{ route('templates.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-sm">Create Template</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h4 class="card-title mb-0">📱 Live Preview</h4>
                    </div>
                    <div class="card-body"
                        style="background: linear-gradient(135deg, #f5f5f5 0%, #fafafa 100%); display: flex; align-items: center; justify-content: center; min-height: 600px;">

                        <!-- Email Preview -->
                        <div id="preview-email" style="display: none; width: 100%; max-width: 450px;">
                            <div
                                style="background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid #e8e8e8;">
                                <!-- Gmail Header -->
                                <div style="background: #fff; border-bottom: 1px solid #e8e8e8; padding: 16px;">
                                    <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                                        <img src="https://www.gstatic.com/images/branding/product/1x/gmail_2020q4_32dp.png"
                                            alt="Gmail" style="width: 24px; height: 24px;">
                                        <span style="font-size: 14px; color: #666;">Gmail</span>
                                    </div>
                                    <div style="font-size: 14px; color: #5f6368; margin-bottom: 8px;">From: <strong>
                                            {{ auth()->user()->business->email }}</strong></div>
                                    <div style="font-size: 14px; color: #5f6368;">To: <strong>client@example.com</strong>
                                    </div>
                                </div>

                                <!-- Subject -->
                                <div style="padding: 0 16px; padding-top: 12px;">
                                    <div style="font-size: 20px; font-weight: 500; color: #202124; margin-bottom: 16px; word-wrap: break-word;"
                                        id="preview-subject-display">
                                        (Subject will appear here)
                                    </div>
                                </div>

                                <!-- Body -->
                                <div style="padding: 0 16px 16px 16px; border-top: 1px solid #e8e8e8; padding-top: 12px;">
                                    <div id="preview-body-email"
                                        style="color: #3c4043; font-size: 16px; line-height: 1.6; white-space: pre-wrap; word-wrap: break-word;">
                                        (Your message will appear here)
                                    </div>

                                    <!-- PDF Attachment -->
                                    <div id="preview-pdf-email" style="margin-top: 16px; display: none;">
                                        <div
                                            style="display: flex; align-items: center; gap: 12px; background: #f8f9fa; border: 1px solid #e8e8e8; border-radius: 8px; padding: 12px; width: fit-content;">
                                            <div
                                                style="width: 36px; height: 36px; background: #e74c3c; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                                <span style="color: #fff; font-size: 20px;">📄</span>
                                            </div>
                                            <div>
                                                <div style="font-size: 14px; font-weight: 500; color: #202124;">Invoice.pdf
                                                </div>
                                                <div style="font-size: 12px; color: #5f6368;">Attached file</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- WhatsApp Preview -->
                        <div id="preview-whatsapp" style="display: none; width: 100%; max-width: 380px;">
                            <!-- Phone Frame -->
                            <div
                                style="background: #000; border-radius: 40px; padding: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                                <!-- Notch -->
                                <div
                                    style="background: #000; height: 28px; border-radius: 0 0 20px 20px; margin: -12px -12px 0 -12px; margin-bottom: 0;">
                                </div>

                                <!-- Screen Content -->
                                <div
                                    style="background: #ece5dd; border-radius: 28px; overflow: hidden; box-shadow: inset 0 0 20px rgba(0,0,0,0.1);">
                                    <!-- WhatsApp Header -->
                                    <div
                                        style="background: #075e54; color: #fff; padding: 12px 16px; display: flex; align-items: center; gap: 12px;">
                                        <span style="font-size: 24px;"><i class="ri-arrow-left-line"></i></span>
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6b/WhatsApp.svg/1200px-WhatsApp.svg.png"
                                            alt="Avatar"
                                            style="width: 36px; height: 36px; border-radius: 50%; background: #fff; padding: 4px;">
                                        <div>
                                            <div style="font-size: 14px; font-weight: 500;">
                                                {{ auth()->user()->business->business_name }}</div>
                                            <div style="font-size: 12px; opacity: 0.8;">Online</div>
                                        </div>
                                    </div>

                                    <!-- Chat Area -->
                                    <div
                                        style="padding: 16px; height: 420px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
                                        <!-- Incoming Message -->
                                        <div style="text-align: left;">
                                            <div style="background: #fff; color: #000; padding: 12px 14px; border-radius: 18px 18px 18px 4px; max-width: 85%; font-size: 14px; line-height: 1.5; word-wrap: break-word; box-shadow: 0 1px 2px rgba(0,0,0,0.06);"
                                                id="preview-body-whatsapp">
                                                (Your message will appear here)
                                            </div>
                                            <div style="font-size: 12px; color: #888; margin-top: 4px; padding: 0 8px;">
                                                10:30 AM</div>
                                        </div>

                                        <!-- PDF Attachment -->
                                        <div id="preview-pdf-wa"
                                            style="text-align: left; display: none; margin-top: 8px;">
                                            <div
                                                style="display: flex; align-items: center; gap: 8px; background: #e3f2fd; border-radius: 12px; padding: 8px 12px; width: fit-content; box-shadow: 0 1px 2px rgba(0,0,0,0.06);">
                                                <span style="font-size: 24px;">📄</span>
                                                <div>
                                                    <div style="font-size: 13px; font-weight: 500; color: #1565c0;">
                                                        Invoice.pdf</div>
                                                    <div style="font-size: 11px; color: #666;">1.2 MB</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Input Area -->
                                    <div
                                        style="background: #fff; border-top: 1px solid #ddd; padding: 12px 16px; display: flex; gap: 8px; align-items: center;">
                                        <span style="font-size: 20px; cursor: pointer;">😊</span>
                                        <input type="text" placeholder="Type a message..."
                                            style="flex: 1; border: none; padding: 8px 12px; border-radius: 20px; background: #f0f0f0; font-size: 14px;">
                                        <span style="font-size: 20px; cursor: pointer;">➤</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const channelSelect = document.getElementById('channel');
            const emailFields = document.getElementById('email-fields');
            const whatsappHelp = document.getElementById('whatsapp-help');
            const subjectInput = document.getElementById('subject');
            const contentInput = document.getElementById('content');
            const previewEmail = document.getElementById('preview-email');
            const previewWhatsapp = document.getElementById('preview-whatsapp');
            const previewSubject = document.getElementById('preview-subject-display');
            const previewBodyEmail = document.getElementById('preview-body-email');
            const previewBodyWhatsapp = document.getElementById('preview-body-whatsapp');

            // Sample variables for preview
            const sampleVariables = {
                'client_name': 'John Doe',
                'invoice_number': 'INV-2024-001',
                'amount': '$0.00',
                'due_date': '31 Jan 2025'
            };

            function replaceSampleVariables(text) {
                let processed = text;
                Object.entries(sampleVariables).forEach(([key, value]) => {
                    const regex = new RegExp(`@{{ \\
s * $ {
    key
}\\
s * }}`, 'g');
                    processed = processed.replace(regex,
                        `<strong style="color: #2196F3; background: #E3F2FD; padding: 2px 6px; border-radius: 4px;">${value}</strong>`
                    );
                });
                return processed;
            }

            function updatePreview() {
                const channel = channelSelect.value;
                const subject = subjectInput.value || '(Subject will appear here)';
                const content = contentInput.value || '(Your message will appear here)';

                if (channel === 'email') {
                    previewEmail.style.display = '';
                    previewWhatsapp.style.display = 'none';
                    previewSubject.textContent = subject;
                    previewBodyEmail.innerHTML = replaceSampleVariables(content);
                } else {
                    previewEmail.style.display = 'none';
                    previewWhatsapp.style.display = '';
                    previewBodyWhatsapp.innerHTML = replaceSampleVariables(content);
                }
            }

            channelSelect.addEventListener('change', function() {
                if (this.value === 'email') {
                    emailFields.style.display = '';
                    whatsappHelp.style.display = 'none';
                } else {
                    emailFields.style.display = 'none';
                    whatsappHelp.style.display = '';
                }
                updatePreview();
            });

            subjectInput.addEventListener('input', updatePreview);
            contentInput.addEventListener('input', updatePreview);

            updatePreview();
        });
    </script>
@endsection
@section('scripts')
    <!-- QUILL EDITOR JS -->
    <script src="{{ asset('build/assets/libs/quill/quill.min.js') }}"></script>

    <!-- INTERNAL QUILL JS -->
    @vite('resources/assets/js/quill-editor.js')

    <script>
        $(document).ready(function() {

            // Initialize Quill editor
            var quill = new Quill("#editorContent", {
                theme: "snow",
            });
            // On form submit, set the hidden input value to the Quill editor content
            document.querySelector('form').onsubmit = function() {
                document.querySelector('input[name=content]').value = quill.root.innerHTML;
            };
        });
    </script>
@endsection
