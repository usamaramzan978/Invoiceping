@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center my-4">
            <div>
                <h5 class="fw-semibold mb-1">Create Manual Subscription</h5>
                <p class="text-muted mb-0">Create a subscription for a user manually</p>
            </div>
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-light">
                <i class="bx bx-arrow-back me-1"></i>Back
            </a>
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
                        <form action="{{ route('admin.subscriptions.store') }}" method="POST">
                            @csrf

                            {{-- User Selection --}}
                            <div class="mb-3">
                                <label for="user_id" class="form-label">User <span class="text-danger">*</span></label>
                                <select name="user_id" id="user_id"
                                    class="js-example-basic-single form-select @error('user_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select a user...</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Select the user who will own this subscription</small>
                            </div>

                            {{-- Plan Selection --}}
                            <div class="mb-3">
                                <label for="plan_id" class="form-label">Subscription Plan <span
                                        class="text-danger">*</span></label>
                                <select name="plan_id" id="plan_id"
                                    class="js-example-basic-single form-select @error('plan_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select a plan...</option>
                                    @foreach ($plans as $plan)
                                        <option value="{{ $plan->id }}"
                                            {{ old('plan_id') == $plan->id ? 'selected' : '' }}
                                            data-monthly="{{ $plan->monthly_price }}"
                                            data-yearly="{{ $plan->yearly_price }}">
                                            {{ $plan->name }} - ${{ $plan->monthly_price }}/mo or
                                            ${{ $plan->yearly_price }}/yr
                                        </option>
                                    @endforeach
                                </select>
                                @error('plan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Billing Cycle --}}
                            <div class="mb-3">
                                <label for="billing_cycle" class="form-label">Billing Cycle <span
                                        class="text-danger">*</span></label>
                                <select name="billing_cycle" id="billing_cycle"
                                    class="js-example-basic-single form-select @error('billing_cycle') is-invalid @enderror"
                                    required>
                                    <option value="monthly" {{ old('billing_cycle') == 'monthly' ? 'selected' : '' }}>
                                        Monthly</option>
                                    <option value="yearly" {{ old('billing_cycle') == 'yearly' ? 'selected' : '' }}>Yearly
                                    </option>
                                </select>
                                @error('billing_cycle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Trial Days --}}
                            <div class="mb-3">
                                <label for="trial_days" class="form-label">Trial Days (Optional)</label>
                                <input type="number" name="trial_days" id="trial_days"
                                    class="form-control @error('trial_days') is-invalid @enderror"
                                    value="{{ old('trial_days', 0) }}" min="0" max="365">
                                @error('trial_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Leave as 0 for no trial period</small>
                            </div>

                            {{-- Start Date --}}
                            <div class="mb-3">
                                <label for="starts_at" class="form-label">Start Date (Optional)</label>
                                <input type="date" name="starts_at" id="starts_at"
                                    class="form-control @error('starts_at') is-invalid @enderror"
                                    value="{{ old('starts_at') }}" min="{{ date('Y-m-d') }}">
                                @error('starts_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Leave empty to start immediately</small>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-light">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i>Create Subscription
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Information</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-2">Manual Subscription Creation</h6>
                        <p class="small text-muted">
                            Create subscriptions manually for testing or when users pay through alternative methods.
                        </p>

                        <hr>

                        <h6 class="mb-2">Important Notes</h6>
                        <ul class="small text-muted mb-0">
                            <li>Users can only have one active subscription at a time</li>
                            <li>Trial period starts immediately if specified</li>
                            <li>Billing will be manual until payment gateway is integrated</li>
                            <li>You can modify subscription details after creation</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
