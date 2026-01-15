@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center my-4">
            <div>
                <h5 class="fw-semibold mb-1">Subscription Plans</h5>
                <p class="text-muted mb-0">Choose the perfect plan for your business</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Current Subscription Alert --}}
        @if ($currentSubscription)
            <div class="alert alert-info d-flex align-items-center">
                <i class="bx bx-info-circle fs-4 me-2"></i>
                <div class="flex-grow-1">
                    <strong>Current Plan:</strong> {{ $currentSubscription->plan->name }} 
                    ({{ ucfirst($currentSubscription->billing_cycle->value) }})
                    @if ($currentSubscription->status->value === 'trialing')
                        - <span class="badge bg-info">{{ $currentSubscription->daysRemainingInTrial() }} days left in trial</span>
                    @endif
                </div>
                <a href="{{ route('subscriptions.index') }}" class="btn btn-sm btn-primary">
                    Manage Subscription
                </a>
            </div>
        @endif

        {{-- Billing Cycle Toggle --}}
        <div class="d-flex justify-content-center mb-4">
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="billing_cycle" id="monthly" value="monthly" checked>
                <label class="btn btn-outline-primary" for="monthly">Monthly</label>

                <input type="radio" class="btn-check" name="billing_cycle" id="yearly" value="yearly">
                <label class="btn btn-outline-primary" for="yearly">
                    Yearly <span class="badge bg-success ms-1">Save up to 17%</span>
                </label>
            </div>
        </div>

        {{-- Plans Grid --}}
        <div class="row g-4">
            @foreach ($plans as $plan)
                <div class="col-lg-3 col-md-6">
                    <div class="card custom-card {{ $plan->is_featured ? 'border-primary' : '' }} h-100">
                        @if ($plan->is_featured)
                            <div class="card-header bg-primary text-white text-center py-2">
                                <i class="bx bx-star me-1"></i> Most Popular
                            </div>
                        @endif
                        
                        <div class="card-body text-center">
                            <h5 class="card-title mb-3">{{ $plan->name }}</h5>
                            <p class="text-muted small mb-4">{{ $plan->description }}</p>
                            
                            {{-- Price Display --}}
                            <div class="mb-4">
                                <h2 class="fw-bold mb-0">
                                    <span class="monthly-price">{{ $plan->formatPrice($plan->monthly_price) }}</span>
                                    <span class="yearly-price d-none">{{ $plan->formatPrice($plan->yearly_price) }}</span>
                                </h2>
                                <span class="text-muted monthly-label">/month</span>
                                <span class="text-muted yearly-label d-none">/year</span>
                                
                                @if ($plan->getYearlySavingsPercentage() > 0)
                                    <div class="yearly-savings d-none text-success small mt-2">
                                        Save {{ $plan->getYearlySavingsPercentage() }}% with yearly billing
                                    </div>
                                @endif
                            </div>

                            {{-- Features List --}}
                            <ul class="list-unstyled text-start mb-4">
                                <li class="mb-2">
                                    <i class="bx bx-check text-success me-2"></i>
                                    {{ $plan->invoice_limit ? number_format($plan->invoice_limit) . ' invoices/month' : 'Unlimited invoices' }}
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check text-success me-2"></i>
                                    {{ $plan->client_limit ? number_format($plan->client_limit) . ' clients' : 'Unlimited clients' }}
                                </li>
                                @if ($plan->whatsapp_enabled)
                                    <li class="mb-2">
                                        <i class="bx bx-check text-success me-2"></i>
                                        WhatsApp Integration
                                    </li>
                                @endif
                                @if ($plan->custom_message)
                                    <li class="mb-2">
                                        <i class="bx bx-check text-success me-2"></i>
                                        Custom Messages
                                    </li>
                                @endif
                                @if ($plan->email_support)
                                    <li class="mb-2">
                                        <i class="bx bx-check text-success me-2"></i>
                                        Email Support
                                    </li>
                                @endif
                                @if ($plan->priority_support)
                                    <li class="mb-2">
                                        <i class="bx bx-check text-success me-2"></i>
                                        Priority Support
                                    </li>
                                @endif
                                @if ($plan->api_access)
                                    <li class="mb-2">
                                        <i class="bx bx-check text-success me-2"></i>
                                        API Access
                                    </li>
                                @endif
                            </ul>

                            {{-- Action Button --}}
                            @if ($currentSubscription && $currentSubscription->plan_id === $plan->id)
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="bx bx-check me-1"></i> Current Plan
                                </button>
                            @elseif ($currentSubscription)
                                <form action="{{ route('subscriptions.update', $currentSubscription->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <input type="hidden" name="billing_cycle" class="billing-cycle-input" value="monthly">
                                    <button type="submit" class="btn btn-outline-primary w-100">
                                        <i class="bx bx-up-arrow-alt me-1"></i> 
                                        {{ $plan->monthly_price > $currentSubscription->plan->monthly_price ? 'Upgrade' : 'Change Plan' }}
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('subscriptions.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <input type="hidden" name="billing_cycle" class="billing-cycle-input" value="monthly">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bx bx-rocket me-1"></i> Get Started
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- FAQ Section --}}
        <div class="row mt-5">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Frequently Asked Questions</h5>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        Can I change my plan later?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Yes! You can upgrade or downgrade your plan at any time. Changes will be reflected in your next billing cycle.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        What payment methods do you accept?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        We accept all major credit cards, debit cards, and online payment methods through our secure payment gateway.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        Can I cancel anytime?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Absolutely! You can cancel your subscription at any time. You'll continue to have access until the end of your billing period.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Toggle between monthly and yearly pricing
        document.querySelectorAll('input[name="billing_cycle"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const isYearly = this.value === 'yearly';
                
                // Toggle price display
                document.querySelectorAll('.monthly-price, .monthly-label').forEach(el => {
                    el.classList.toggle('d-none', isYearly);
                });
                document.querySelectorAll('.yearly-price, .yearly-label, .yearly-savings').forEach(el => {
                    el.classList.toggle('d-none', !isYearly);
                });
                
                // Update hidden inputs
                document.querySelectorAll('.billing-cycle-input').forEach(input => {
                    input.value = this.value;
                });
            });
        });
    </script>
    @endpush
@endsection

