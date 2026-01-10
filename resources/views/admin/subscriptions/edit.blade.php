@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center my-4">
            <div>
                <h5 class="fw-semibold mb-1">Edit Subscription</h5>
                <p class="text-muted mb-0">Modify subscription details</p>
            </div>
            <div class="btn-group" role="group">
                <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="btn btn-light">
                    <i class="bx bx-show me-1"></i>View
                </a>
                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-light">
                    <i class="bx bx-arrow-back me-1"></i>Back
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Error:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Subscription Details</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.subscriptions.update', $subscription) }}" method="POST">
                            @csrf
                            @method('PUT')

                            {{-- User Info (Read-only) --}}
                            <div class="mb-3">
                                <label class="form-label">User</label>
                                <input type="text" class="form-control" value="{{ $subscription->user->name }} ({{ $subscription->user->email }})" disabled>
                                <small class="text-muted">User cannot be changed after subscription creation</small>
                            </div>

                            {{-- Plan Selection --}}
                            <div class="mb-3">
                                <label for="plan_id" class="form-label">Subscription Plan</label>
                                <select name="plan_id" id="plan_id" class="form-select @error('plan_id') is-invalid @enderror">
                                    @foreach ($plans as $plan)
                                        <option value="{{ $plan->id }}" {{ $subscription->plan_id == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->name }} - ${{ $plan->monthly_price }}/mo or ${{ $plan->yearly_price }}/yr
                                        </option>
                                    @endforeach
                                </select>
                                @error('plan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Billing Cycle --}}
                            <div class="mb-3">
                                <label for="billing_cycle" class="form-label">Billing Cycle</label>
                                <select name="billing_cycle" id="billing_cycle" class="form-select @error('billing_cycle') is-invalid @enderror">
                                    <option value="monthly" {{ $subscription->billing_cycle->value == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="yearly" {{ $subscription->billing_cycle->value == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                                @error('billing_cycle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="active" {{ $subscription->status->value == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="trialing" {{ $subscription->status->value == 'trialing' ? 'selected' : '' }}>Trialing</option>
                                    <option value="canceled" {{ $subscription->status->value == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                    <option value="expired" {{ $subscription->status->value == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="past_due" {{ $subscription->status->value == 'past_due' ? 'selected' : '' }}>Past Due</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Trial End Date --}}
                            <div class="mb-3">
                                <label for="trial_ends_at" class="form-label">Trial End Date (Optional)</label>
                                <input type="datetime-local" name="trial_ends_at" id="trial_ends_at" 
                                    class="form-control @error('trial_ends_at') is-invalid @enderror" 
                                    value="{{ $subscription->trial_ends_at?->format('Y-m-d\TH:i') }}">
                                @error('trial_ends_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Renewal Date --}}
                            <div class="mb-3">
                                <label for="renews_at" class="form-label">Renewal Date</label>
                                <input type="datetime-local" name="renews_at" id="renews_at" 
                                    class="form-control @error('renews_at') is-invalid @enderror" 
                                    value="{{ $subscription->renews_at?->format('Y-m-d\TH:i') }}">
                                @error('renews_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="btn btn-light">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i>Update Subscription
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Subscription Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Current Status:</strong>
                            <span class="badge bg-{{ $subscription->status->color() }} ms-2">
                                {{ $subscription->status->label() }}
                            </span>
                        </div>
                        <div class="mb-3">
                            <strong>Created:</strong> {{ $subscription->created_at->format('M d, Y H:i') }}
                        </div>
                        <div class="mb-3">
                            <strong>Last Updated:</strong> {{ $subscription->updated_at->format('M d, Y H:i') }}
                        </div>
                        @if ($subscription->canceled_at)
                            <div class="mb-3">
                                <strong>Canceled:</strong> {{ $subscription->canceled_at->format('M d, Y H:i') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card custom-card mt-3">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">Danger Zone</h5>
                    </div>
                    <div class="card-body">
                        <p class="small mb-3">Cancel this subscription immediately. This action cannot be undone.</p>
                        <form action="{{ route('admin.subscriptions.destroy', $subscription) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this subscription? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bx bx-x me-1"></i>Cancel Subscription
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

