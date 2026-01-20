@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center my-4">
            <h5 class="fw-semibold mb-0">Create Support Ticket</h5>
        </div>

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

        <div class="card custom-card">
            <div class="card-body">
                <form action="{{ route('support-tickets.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required>
                            @error('subject')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Client</label>
                            <select name="client_id" class="form-select single-select2">
                                <option value="">Select client (optional)</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Related Invoice</label>
                            <select name="invoice_id" class="form-select single-select2">
                                <option value="">Select invoice (optional)</option>
                                @foreach ($invoices as $invoice)
                                    <option value="{{ $invoice->id }}" @selected(old('invoice_id') == $invoice->id)>
                                        #{{ $invoice->invoice_number }} - {{ $invoice->client?->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('invoice_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select single-select2" required>
                                @foreach ($priorities as $priority)
                                    <option value="{{ $priority->value }}" @selected(old('priority', 'medium') === $priority->value)>
                                        {{ $priority->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('priority')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="6" required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Attachment (Image)</label>
                            <input type="file" name="attachment" class="form-control" accept="image/*">
                            @error('attachment')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ route('support-tickets.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
