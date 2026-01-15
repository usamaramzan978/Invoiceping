@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center my-4">
            <div>
                <h5 class="fw-semibold mb-1">My Subscription</h5>
                <p class="text-muted mb-0">Manage your subscription and view history</p>
            </div>
            <a href="{{ route('subscriptions.plans.index') }}" class="btn btn-primary">
                <i class="bx bx-rocket me-1"></i>View Plans
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Current Subscription --}}
        @if ($subscription)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Current Subscription</h5>
                            <span class="badge bg-{{ $subscription->status->color() }}">
                                {{ $subscription->status->label() }}
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4 class="fw-bold mb-3">{{ $subscription->plan->name }}</h4>
                                    <p class="text-muted mb-4">{{ $subscription->plan->description }}</p>
                                    
                                    <div class="mb-3">
                                        <strong>Billing Cycle:</strong> 
                                        <span class="badge bg-primary">{{ ucfirst($subscription->billing_cycle->value) }}</span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Amount:</strong> 
                                        <span class="fs-5 fw-semibold text-primary">
                                            {{ $subscription->plan->formatPrice($subscription->amount) }}/{{ $subscription->billing_cycle->value === 'yearly' ? 'year' : 'month' }}
                                        </span>
                                    </div>

                                    @if ($subscription->onTrial())
                                        <div class="alert alert-info mb-3">
                                            <i class="bx bx-time me-2"></i>
                                            Your trial ends in <strong>{{ $subscription->daysRemainingInTrial() }} days</strong>
                                            ({{ $subscription->trial_ends_at->format('M d, Y') }})
                                        </div>
                                    @endif

                                    @if ($subscription->isActive() && !$subscription->onTrial())
                                        <div class="mb-3">
                                            <strong>Next Renewal:</strong> 
                                            {{ $subscription->renews_at?->format('M d, Y') ?? 'N/A' }}
                                            @if ($subscription->renews_at)
                                                <small class="text-muted">({{ $subscription->daysUntilRenewal() }} days)</small>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($subscription->isCanceled())
                                        <div class="alert alert-warning">
                                            <i class="bx bx-info-circle me-2"></i>
                                            Your subscription is canceled and will end on 
                                            <strong>{{ $subscription->ends_at?->format('M d, Y') ?? 'N/A' }}</strong>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <h6 class="mb-3">Features Included:</h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="bx bx-check-circle text-success me-2"></i>
                                            {{ $subscription->plan->invoice_limit ? number_format($subscription->plan->invoice_limit) . ' invoices/month' : 'Unlimited invoices' }}
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

                            {{-- Action Buttons --}}
                            <div class="border-top pt-3 mt-3">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('subscriptions.plans.index') }}" class="btn btn-outline-primary">
                                        <i class="bx bx-refresh me-1"></i>Change Plan
                                    </a>
                                    
                                    @if ($subscription->billing_cycle->value === 'monthly')
                                        <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#switchCycleModal">
                                            <i class="bx bx-calendar me-1"></i>Switch to Yearly
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#switchCycleModal">
                                            <i class="bx bx-calendar me-1"></i>Switch to Monthly
                                        </button>
                                    @endif

                                    @if ($subscription->isActive())
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                            <i class="bx bx-x me-1"></i>Cancel Subscription
                                        </button>
                                    @elseif ($subscription->isCanceled() && $subscription->ends_at && $subscription->ends_at->isFuture())
                                        <form action="{{ route('subscriptions.resume', $subscription->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success">
                                                <i class="bx bx-refresh me-1"></i>Resume Subscription
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- No Active Subscription --}}
            <div class="row">
                <div class="col-12">
                    <div class="card custom-card text-center py-5">
                        <div class="card-body">
                            <i class="bx bx-package fs-1 text-muted mb-3"></i>
                            <h5 class="mb-3">No Active Subscription</h5>
                            <p class="text-muted mb-4">You don't have an active subscription. Choose a plan to get started!</p>
                            <a href="{{ route('subscriptions.plans.index') }}" class="btn btn-primary">
                                <i class="bx bx-rocket me-1"></i>View Plans
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Subscription History --}}
        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Subscription History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover text-nowrap align-middle">
                                <thead>
                                    <tr>
                                        <th>Plan</th>
                                        <th>Billing Cycle</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($subscriptionHistory as $sub)
                                        <tr>
                                            <td>{{ $sub->plan->name }}</td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($sub->billing_cycle->value) }}
                                                </span>
                                            </td>
                                            <td>{{ $sub->plan->formatPrice($sub->amount) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $sub->status->color() }}">
                                                    {{ $sub->status->label() }}
                                                </span>
                                            </td>
                                            <td>{{ $sub->starts_at->format('M d, Y') }}</td>
                                            <td>{{ $sub->ends_at?->format('M d, Y') ?? 'Active' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No subscription history
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $subscriptionHistory->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cancel Subscription Modal --}}
    @if ($subscription && $subscription->isActive())
        <div class="modal fade" id="cancelModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cancel Subscription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('subscriptions.cancel', $subscription->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p>Are you sure you want to cancel your subscription?</p>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="immediate" id="immediate" value="1">
                                <label class="form-check-label" for="immediate">
                                    Cancel immediately (instead of at end of billing period)
                                </label>
                            </div>

                            <div class="mb-3">
                                <label for="cancellation_reason" class="form-label">Reason for cancellation (optional)</label>
                                <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Subscription</button>
                            <button type="submit" class="btn btn-danger">Yes, Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Switch Billing Cycle Modal --}}
    @if ($subscription && $subscription->isActive())
        <div class="modal fade" id="switchCycleModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Switch Billing Cycle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('subscriptions.switch-cycle', $subscription->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="billing_cycle" value="{{ $subscription->billing_cycle->value === 'monthly' ? 'yearly' : 'monthly' }}">
                        <div class="modal-body">
                            @if ($subscription->billing_cycle->value === 'monthly')
                                <p>Switch to yearly billing and <strong>save {{ $subscription->plan->getYearlySavingsPercentage() }}%</strong>!</p>
                                <p>New amount: <strong>{{ $subscription->plan->formatPrice($subscription->plan->yearly_price) }}/year</strong></p>
                            @else
                                <p>Switch back to monthly billing.</p>
                                <p>New amount: <strong>{{ $subscription->plan->formatPrice($subscription->plan->monthly_price) }}/month</strong></p>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Switch Billing Cycle</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

