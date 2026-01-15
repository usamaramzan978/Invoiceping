@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center my-4">
            <div>
                <h5 class="fw-semibold mb-1">Billing History</h5>
                <p class="text-muted mb-0">View all your billing transactions</p>
            </div>
            <a href="{{ route('billing.invoices.index') }}" class="btn btn-primary">
                <i class="bx bx-receipt me-1"></i>View Invoices
            </a>
        </div>

        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-lg-4 col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-1 text-muted">Total Spent</p>
                                <h4 class="mb-0 fw-semibold">${{ number_format((float) $totalSpent, 2) }}</h4>
                            </div>
                            <div class="text-primary">
                                <i class="bx bx-wallet fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-1 text-muted">Total Transactions</p>
                                <h4 class="mb-0 fw-semibold">{{ $transactions->total() }}</h4>
                            </div>
                            <div class="text-success">
                                <i class="bx bx-transfer fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-1 text-muted">Latest Payment</p>
                                <h4 class="mb-0 fw-semibold">
                                    @if ($transactions->count() > 0)
                                        {{ $transactions->first()->created_at->format('M d, Y') }}
                                    @else
                                        N/A
                                    @endif
                                </h4>
                            </div>
                            <div class="text-info">
                                <i class="bx bx-calendar fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transactions Table --}}
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="card-title mb-0">Transaction History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap align-middle">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $transaction)
                                <tr>
                                    <td>
                                        <code class="text-primary">{{ Str::limit($transaction->transaction_id, 20) }}</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->type->value === 'payment' ? 'success' : ($transaction->type->value === 'refund' ? 'warning' : 'secondary') }}">
                                            {{ $transaction->type->label() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>{{ Str::limit($transaction->description ?? 'N/A', 40) }}</div>
                                        @if ($transaction->subscription)
                                            <small class="text-muted">{{ $transaction->subscription->plan->name }}</small>
                                        @endif
                                    </td>
                                    <td class="fw-semibold {{ $transaction->type->value === 'refund' ? 'text-warning' : 'text-success' }}">
                                        {{ $transaction->type->value === 'refund' ? '-' : '' }}{{ $transaction->formattedAmount() }}
                                    </td>
                                    <td>
                                        @if ($transaction->payment_method)
                                            <span class="badge bg-light text-dark">
                                                <i class="bx bx-credit-card me-1"></i>
                                                {{ ucfirst($transaction->payment_method) }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->status->color() }}">
                                            {{ $transaction->status->label() }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->processed_at?->format('M d, Y H:i') ?? $transaction->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('billing.transactions.show', $transaction->id) }}" class="btn btn-sm btn-light">
                                            <i class="bx bx-show"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="bx bx-wallet fs-1 d-block mb-2"></i>
                                        <p class="mb-0">No transactions found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
@endsection

