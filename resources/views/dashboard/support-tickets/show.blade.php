@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center my-4">
            <div>
                <h5 class="fw-semibold mb-1">Ticket: {{ $ticket->subject }}</h5>
                <p class="text-muted mb-0">Created on {{ $ticket->created_at?->format('M d, Y H:i') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('support-tickets.edit', $ticket) }}" class="btn btn-primary">
                    <i class="ri-edit-line me-1"></i>Edit
                </a>
                <a href="{{ route('support-tickets.index') }}" class="btn btn-outline-secondary">
                    Back to Tickets
                </a>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">Conversation</div>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="fw-semibold mb-2">Original Message</h6>
                            <p class="mb-0" style="white-space: pre-line;">{{ $ticket->message }}</p>
                        </div>

                        <hr>

                        <h6 class="fw-semibold mb-3">Replies</h6>

                        @forelse ($ticket->replies->sortBy('created_at') as $reply)
                            <div class="mb-3 p-3 border rounded bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div>
                                        <span class="fw-semibold">{{ $reply->user?->name ?? 'User' }}</span>
                                        @if ($reply->is_admin)
                                            <span class="badge bg-primary-transparent ms-1">Admin</span>
                                        @else
                                            <span class="badge bg-secondary-transparent ms-1">User</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ $reply->created_at?->format('M d, Y H:i') }}</small>
                                </div>
                                <p class="mb-0" style="white-space: pre-line;">{{ $reply->message }}</p>
                            </div>
                        @empty
                            <p class="text-muted">No replies yet.</p>
                        @endforelse

                        <hr>

                        <form action="{{ route('support-tickets.replies.store', $ticket) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Add Reply</label>
                                <textarea name="message" class="form-control" rows="4" placeholder="Write your reply...">{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    Send Reply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">Details</div>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">
                                @php
                                    $statusClass = match ($ticket->status?->value) {
                                        'open' => 'primary',
                                        'in_progress' => 'info',
                                        'resolved' => 'success',
                                        'closed' => 'secondary',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}-transparent">
                                    {{ $ticket->status?->label() ?? 'N/A' }}
                                </span>
                            </dd>

                            <dt class="col-sm-4">Priority</dt>
                            <dd class="col-sm-8">
                                @php
                                    $priorityClass = match ($ticket->priority?->value) {
                                        'low' => 'secondary',
                                        'medium' => 'info',
                                        'high' => 'warning',
                                        'urgent' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $priorityClass }}-transparent">
                                    {{ $ticket->priority?->label() ?? 'N/A' }}
                                </span>
                            </dd>

                            <dt class="col-sm-4">Client</dt>
                            <dd class="col-sm-8">
                                @if ($ticket->client)
                                    <a href="{{ route('clients.show', $ticket->client) }}">
                                        {{ $ticket->client->name }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </dd>

                            <dt class="col-sm-4">Invoice</dt>
                            <dd class="col-sm-8">
                                @if ($ticket->invoice)
                                    <a href="{{ route('invoices.show', $ticket->invoice) }}">
                                        #{{ $ticket->invoice->invoice_number }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </dd>

                            <dt class="col-sm-4">Created By</dt>
                            <dd class="col-sm-8">
                                {{ $ticket->creator?->name ?? '—' }}
                            </dd>

                            <dt class="col-sm-4">Resolved At</dt>
                            <dd class="col-sm-8">
                                {{ $ticket->resolved_at?->format('M d, Y H:i') ?? '—' }}
                            </dd>

                            <dt class="col-sm-4">Closed At</dt>
                            <dd class="col-sm-8">
                                {{ $ticket->closed_at?->format('M d, Y H:i') ?? '—' }}
                            </dd>

                            <dt class="col-sm-4">Attachment</dt>
                            <dd class="col-sm-8">
                                @if ($ticket->attachment_path)
                                    <a href="{{ asset('storage/' . $ticket->attachment_path) }}" target="_blank"
                                        class="d-block mb-2">
                                        View Attachment
                                    </a>
                                    <img src="{{ asset('storage/' . $ticket->attachment_path) }}" alt="Attachment"
                                        class="img-fluid rounded border" style="max-height: 200px;">
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
