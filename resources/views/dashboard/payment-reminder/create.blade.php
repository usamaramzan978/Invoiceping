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
            <h1 class="page-title fw-semibold fs-18 mb-0">Create Payment Reminder</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Payment Reminder</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create Payment Reminder</li>
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
        </div>
    </div>
@endsection
@section('scripts')
@endsection
