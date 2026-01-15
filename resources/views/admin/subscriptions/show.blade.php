@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center my-4">
            <div>
                <h5 class="fw-semibold mb-1">Subscription Details</h5>
                <p class="text-muted mb-0">View subscription information</p>
            </div>
            <div class="btn-group" role="group">
                <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="btn btn-primary">
                    <i class="bx bx-edit me-1"></i>Edit
                </a>
                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-light">
                    <i class="bx bx-arrow-back me-1"></i>Back
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                {{-- Subscription Details --}}
                <div class="card custom-card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Subscription Information</h5>
                        <span class="badge bg-{{ $subscription->status->color() }} fs-6">
                            {{ $subscription->status->label() }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="text-muted mb-1">User</p>
                                <p class="mb-0 fw-semibold">{{ $subscription->user->name }}</p>
                                <small class="text-muted">{{ $subscription->user->email }}</small>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Plan</p>
                                <p class="mb-0 fw-semibold">{{ $subscription->plan->name }}</p>
                            </div>
                        </div>

                        <hr>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <p class="text-muted mb-1">Billing Cycle</p>
                                <p class="mb-0">
                                    <span class="badge bg-secondary">{{ ucfirst($subscription->billing_cycle->value) }}</span>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted mb-1">Amount</p>
                                <p class="mb-0 fw-semibold text-primary">
                                    {{ $subscription->plan->formatPrice($subscription->amount) }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted mb-1">Currency</p>
                                <p class="mb-0">{{ strtoupper($subscription->currency) }}</p>
                            </div>
                        </div>

                        <hr>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <p class="text-muted mb-1">Start Date</p>
                                <p class="mb-0">{{ $subscription->starts_at?->format('M d, Y') ?? 'N/A' }}</p>
                            </div>
                            @if ($subscription->trial_ends_at)
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Trial Ends</p>
                                    <p class="mb-0">{{ $subscription->trial_ends_at->format('M d, Y') }}</p>
                                    @if ($subscription->onTrial())
                                        <small class="text-info">{{ $subscription->daysRemainingInTrial() }} days left</small>
                                    @endif
                                </div>
                            @endif
                            <div class="col-md-4">
                                <p class="text-muted mb-1">Renewal Date</p>
                                <p class="mb-0">{{ $subscription->renews_at?->format('M d, Y') ?? 'N/A' }}</p>
                            </div>
                        </div>

                        @if ($subscription->canceled_at)
                            <hr>
                            <div class="alert alert-warning">
                                <strong>Canceled:</strong> {{ $subscription->canceled_at->format('M d, Y H:i') }}
                                @if ($subscription->ends_at)
                                    <br><small>Will end on: {{ $subscription->ends_at->format('M d, Y') }}</small>
                                @endif
                            </div>
                        @endif

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Created</p>
                                <p class="mb-0">{{ $subscription->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Last Updated</p>
                                <p class="mb-0">{{ $subscription->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Invoices --}}
                @if ($subscription->invoices->count() > 0)
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Invoices ({{ $subscription->invoices->count() }})</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Due Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($subscription->invoices as $invoice)
                                            <tr>
                                                <td>{{ $invoice->invoice_number }}</td>
                                                <td>{{ $invoice->formattedTotal() }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $invoice->status->color() }}">
                                                        {{ $invoice->status->label() }}
                                                    </span>
                                                </td>
                                                <td>{{ $invoice->due_date->format('M d, Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Transactions --}}
                @if ($subscription->transactions->count() > 0)
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Transactions ({{ $subscription->transactions->count() }})</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Transaction ID</th>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($subscription->transactions as $transaction)
                                            <tr>
                                                <td><code>{{ Str::limit($transaction->transaction_id, 15) }}</code></td>
                                                <td>
                                                    <span class="badge bg-{{ $transaction->type->value === 'payment' ? 'success' : 'warning' }}">
                                                        {{ $transaction->type->label() }}
                                                    </span>
                                                </td>
                                                <td>{{ $transaction->formattedAmount() }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $transaction->status->color() }}">
                                                        {{ $transaction->status->label() }}
                                                    </span>
                                                </td>
                                                <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                {{-- Plan Features --}}
                <div class="card custom-card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Plan Features</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bx bx-check-circle text-success me-2"></i>
                                {{ $subscription->plan->invoice_limit ? number_format($subscription->plan->invoice_limit) . ' invoices' : 'Unlimited invoices' }}
                            </li>
                            <li class="mb-2">
                                <i class="bx bx-check-circle text-success me-2"></i>
                                {{ $subscription->plan->client_limit ? number_format($subscription->plan->client_limit) . ' clients' : 'Unlimited clients' }}
                            </li>
                            @if ($subscription->plan->whatsapp_enabled)
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    WhatsApp Integration
                                </li>
                            @endif
                            @if ($subscription->plan->custom_message)
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Custom Messages
                                </li>
                            @endif
                            @if ($subscription->plan->email_support)
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Email Support
                                </li>
                            @endif
                            @if ($subscription->plan->priority_support)
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Priority Support
                                </li>
                            @endif
                            @if ($subscription->plan->api_access)
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    API Access
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="btn btn-primary w-100 mb-2">
                            <i class="bx bx-edit me-1"></i>Edit Subscription
                        </a>
                        @if ($subscription->isActive())
                            <form action="{{ route('admin.subscriptions.destroy', $subscription) }}" method="POST" onsubmit="return confirm('Cancel this subscription?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bx bx-x me-1"></i>Cancel Subscription
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

