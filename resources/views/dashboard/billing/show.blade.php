@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center my-4">
            <div>
                <h5 class="fw-semibold mb-1">Transaction Details</h5>
                <p class="text-muted mb-0">View transaction information</p>
            </div>
            <a href="{{ route('billing.index') }}" class="btn btn-light">
                <i class="bx bx-arrow-back me-1"></i>Back to History
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Transaction Information</h5>
                        <span class="badge bg-{{ $transaction->status->color() }} fs-6">
                            {{ $transaction->status->label() }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <p class="text-muted mb-1">Transaction ID</p>
                                <code class="text-primary">{{ $transaction->transaction_id }}</code>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted mb-1">Type</p>
                                <span class="badge bg-{{ $transaction->type->value === 'payment' ? 'success' : ($transaction->type->value === 'refund' ? 'warning' : 'secondary') }}">
                                    {{ $transaction->type->label() }}
                                </span>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted mb-1">Date</p>
                                <p class="mb-0">{{ $transaction->processed_at?->format('M d, Y H:i') ?? $transaction->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>

                        <hr>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Amount</p>
                                <h3 class="mb-0 {{ $transaction->type->value === 'refund' ? 'text-warning' : 'text-success' }}">
                                    {{ $transaction->type->value === 'refund' ? '-' : '' }}{{ $transaction->formattedAmount() }}
                                </h3>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Currency</p>
                                <p class="mb-0">{{ strtoupper($transaction->currency) }}</p>
                            </div>
                        </div>

                        <hr>

                        @if ($transaction->subscription)
                            <div class="mb-3">
                                <p class="text-muted mb-1">Subscription Plan</p>
                                <p class="mb-0">{{ $transaction->subscription->plan->name }}</p>
                            </div>
                        @endif

                        @if ($transaction->description)
                            <div class="mb-3">
                                <p class="text-muted mb-1">Description</p>
                                <p class="mb-0">{{ $transaction->description }}</p>
                            </div>
                        @endif

                        @if ($transaction->payment_method)
                            <div class="mb-3">
                                <p class="text-muted mb-1">Payment Method</p>
                                <span class="badge bg-light text-dark">
                                    <i class="bx bx-credit-card me-1"></i>
                                    {{ ucfirst($transaction->payment_method) }}
                                </span>
                            </div>
                        @endif

                        @if ($transaction->payment_gateway)
                            <div class="mb-3">
                                <p class="text-muted mb-1">Payment Gateway</p>
                                <p class="mb-0">{{ ucfirst($transaction->payment_gateway) }}</p>
                            </div>
                        @endif

                        @if ($transaction->failure_reason)
                            <div class="alert alert-danger">
                                <strong>Failure Reason:</strong> {{ $transaction->failure_reason }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">{{ $transaction->created_at->format('M d, Y H:i') }}</small>
                                    <p class="mb-0">Transaction Created</p>
                                </div>
                            </div>

                            @if ($transaction->processed_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <small class="text-muted">{{ $transaction->processed_at->format('M d, Y H:i') }}</small>
                                        <p class="mb-0">Transaction Processed</p>
                                    </div>
                                </div>
                            @endif

                            @if ($transaction->status->value === 'completed')
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <small class="text-muted">{{ $transaction->updated_at->format('M d, Y H:i') }}</small>
                                        <p class="mb-0">Transaction Completed</p>
                                    </div>
                                </div>
                            @endif

                            @if ($transaction->status->value === 'failed')
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-danger"></div>
                                    <div class="timeline-content">
                                        <small class="text-muted">{{ $transaction->updated_at->format('M d, Y H:i') }}</small>
                                        <p class="mb-0">Transaction Failed</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($transaction->metadata)
                    <div class="card custom-card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Additional Information</h5>
                        </div>
                        <div class="card-body">
                            <pre class="mb-0 small">{{ json_encode($transaction->metadata, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        .timeline-marker {
            position: absolute;
            left: -26px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #e9ecef;
        }
        .timeline-content {
            padding-left: 10px;
        }
    </style>
    @endpush
@endsection

