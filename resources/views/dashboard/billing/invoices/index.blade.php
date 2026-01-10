@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center my-4">
            <div>
                <h5 class="fw-semibold mb-1">Subscription Invoices</h5>
                <p class="text-muted mb-0">View and download your invoices</p>
            </div>
            <a href="{{ route('billing.index') }}" class="btn btn-light">
                <i class="bx bx-arrow-back me-1"></i>Back to Billing
            </a>
        </div>

        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card custom-card border-left-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Paid Invoices</p>
                                <h4 class="mb-0 fw-semibold">{{ $paidCount }}</h4>
                            </div>
                            <div class="text-success">
                                <i class="bx bx-check-circle fs-1"></i>
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
                                <p class="text-muted mb-1">Pending</p>
                                <h4 class="mb-0 fw-semibold">{{ $pendingCount }}</h4>
                            </div>
                            <div class="text-warning">
                                <i class="bx bx-time fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card custom-card border-left-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Overdue</p>
                                <h4 class="mb-0 fw-semibold">{{ $overdueCount }}</h4>
                            </div>
                            <div class="text-danger">
                                <i class="bx bx-error-circle fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card custom-card border-left-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Total Invoices</p>
                                <h4 class="mb-0 fw-semibold">{{ $invoices->total() }}</h4>
                            </div>
                            <div class="text-primary">
                                <i class="bx bx-receipt fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Invoices Table --}}
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="card-title mb-0">Invoice History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap align-middle">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Subscription</th>
                                <th>Billing Period</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invoices as $invoice)
                                <tr>
                                    <td>
                                        <a href="{{ route('billing.invoices.show', $invoice->id) }}" class="text-primary fw-semibold">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                    </td>
                                    <td>
                                        @if ($invoice->subscription)
                                            <div>{{ $invoice->subscription->plan->name }}</div>
                                            <small class="text-muted">{{ ucfirst($invoice->subscription->billing_cycle->value) }}</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $invoice->period_start->format('M d') }} - {{ $invoice->period_end->format('M d, Y') }}</div>
                                    </td>
                                    <td class="fw-semibold">{{ $invoice->formattedTotal() }}</td>
                                    <td>
                                        <div>{{ $invoice->due_date->format('M d, Y') }}</div>
                                        @if ($invoice->isOverdue())
                                            <small class="text-danger">Overdue</small>
                                        @elseif ($invoice->status->value === 'pending')
                                            <small class="text-muted">{{ $invoice->due_date->diffForHumans() }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $invoice->status->color() }}">
                                            {{ $invoice->status->label() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('billing.invoices.show', $invoice->id) }}" class="btn btn-light" title="View">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            @if ($invoice->isPaid())
                                                <a href="{{ route('billing.invoices.download', $invoice->id) }}" class="btn btn-light" title="Download PDF">
                                                    <i class="bx bx-download"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bx bx-receipt fs-1 d-block mb-2"></i>
                                        <p class="mb-0">No invoices found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $invoices->links() }}
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .border-left-success { border-left: 4px solid #28a745; }
        .border-left-warning { border-left: 4px solid #ffc107; }
        .border-left-danger { border-left: 4px solid #dc3545; }
        .border-left-primary { border-left: 4px solid #0d6efd; }
    </style>
    @endpush
@endsection

