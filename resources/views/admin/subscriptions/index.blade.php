@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center my-4">
            <div>
                <h5 class="fw-semibold mb-1">Admin: Manage Subscriptions</h5>
                <p class="text-muted mb-0">Create and manage user subscriptions manually</p>
            </div>
            <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>Create Subscription
            </a>
        </div>

        {{-- Statistics Cards --}}
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Total</p>
                                <h4 class="mb-0 fw-semibold">{{ $stats['total'] }}</h4>
                            </div>
                            <div class="text-primary">
                                <i class="bx bx-receipt fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card custom-card border-left-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Active</p>
                                <h4 class="mb-0 fw-semibold">{{ $stats['active'] }}</h4>
                            </div>
                            <div class="text-success">
                                <i class="bx bx-check-circle fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card custom-card border-left-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Trialing</p>
                                <h4 class="mb-0 fw-semibold">{{ $stats['trialing'] }}</h4>
                            </div>
                            <div class="text-info">
                                <i class="bx bx-time fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card custom-card border-left-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Canceled</p>
                                <h4 class="mb-0 fw-semibold">{{ $stats['canceled'] }}</h4>
                            </div>
                            <div class="text-warning">
                                <i class="bx bx-x-circle fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Subscriptions Table --}}
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="card-title mb-0">All Subscriptions</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap align-middle">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Plan</th>
                                <th>Billing Cycle</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>Renewal Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($subscriptions as $subscription)
                                <tr>
                                    <td>
                                        <div>{{ $subscription->user->name }}</div>
                                        <small class="text-muted">{{ $subscription->user->email }}</small>
                                    </td>
                                    <td>{{ $subscription->plan->name }}</td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst($subscription->billing_cycle->value) }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold">{{ $subscription->plan->formatPrice($subscription->amount) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $subscription->status->color() }}">
                                            {{ $subscription->status->label() }}
                                        </span>
                                    </td>
                                    <td>{{ $subscription->starts_at?->format('M d, Y') ?? 'N/A' }}</td>
                                    <td>{{ $subscription->renews_at?->format('M d, Y') ?? 'N/A' }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="btn btn-light" title="View">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="btn btn-light" title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            @if ($subscription->isActive())
                                                <form action="{{ route('admin.subscriptions.destroy', $subscription) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this subscription?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-light text-danger" title="Cancel">
                                                        <i class="bx bx-x"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="bx bx-package fs-1 d-block mb-2"></i>
                                        <p class="mb-0">No subscriptions found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $subscriptions->links() }}
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .border-left-success { border-left: 4px solid #28a745; }
        .border-left-info { border-left: 4px solid #17a2b8; }
        .border-left-warning { border-left: 4px solid #ffc107; }
    </style>
    @endpush
@endsection

