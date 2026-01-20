@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center my-4">
            <div>
                <h5 class="fw-semibold mb-1">Support Tickets</h5>
                <p class="text-muted mb-0">View and manage your support tickets.</p>
            </div>
            <a href="{{ route('support-tickets.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i>Create Ticket
            </a>
        </div>

        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Support Tickets</div>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="ri-search-line"></i>
                                </span>
                                <input type="text" name="search" class="form-control" placeholder="Search tickets..."
                                    value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select single-select2 ">
                                <option value="">All Statuses</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="priority" class="form-select single-select2">
                                <option value="">All Priorities</option>
                                @foreach ($priorities as $priority)
                                    <option value="{{ $priority->value }}" @selected(request('priority') === $priority->value)>
                                        {{ $priority->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="hstack gap-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="ri-search-line me-1"></i>Filter
                                </button>
                                <a href="{{ route('support-tickets.index') }}" class="btn btn-outline-secondary"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh">
                                    <i class="ri-refresh-line"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover text-nowrap align-middle">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Client</th>
                                <th>Invoice</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tickets as $ticket)
                                <tr>
                                    <td>
                                        <a href="{{ route('support-tickets.show', $ticket) }}"
                                            class="fw-semibold text-primary">
                                            {{ $ticket->subject }}
                                        </a>
                                    </td>
                                    <td>{{ $ticket->client?->name ?? '—' }}</td>
                                    <td>
                                        @if ($ticket->invoice)
                                            <a href="{{ route('invoices.show', $ticket->invoice) }}"
                                                class="text-decoration-underline">
                                                #{{ $ticket->invoice->invoice_number }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
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
                                    </td>
                                    <td>
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
                                    </td>
                                    <td>{{ $ticket->created_at?->format('M d, Y') }}</td>
                                    <td class="text-end">
                                        <div class="hstack gap-2 justify-content-end">
                                            <a href="{{ route('support-tickets.show', $ticket) }}" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="View"
                                                class="btn btn-icon btn-sm btn-info-light">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <a href="{{ route('support-tickets.edit', $ticket) }}" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Edit"
                                                class="btn btn-icon btn-sm btn-primary-light">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                            <button type="button" class="btn btn-icon btn-sm btn-danger-transparent"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Delete"
                                                data-delete-modal data-title="Delete Ticket"
                                                data-message="Are you sure you want to delete ticket '{{ $ticket->subject }}'? This action cannot be undone."
                                                data-form-id="{{ route('support-tickets.destroy', $ticket) }}"
                                                data-record-name="support ticket">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="ri-inbox-line fs-24 d-block mb-2"></i>
                                        No support tickets found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $tickets->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
