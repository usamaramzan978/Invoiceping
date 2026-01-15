<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Subscription\CancelSubscription;
use App\Actions\Subscription\ChangeSubscriptionPlan;
use App\Actions\Subscription\CreateSubscription;
use App\Actions\Subscription\ResumeSubscription;
use App\Actions\Subscription\SwitchBillingCycle;
use App\Enums\BillingCycle;
use App\Http\Requests\CancelSubscriptionRequest;
use App\Http\Requests\CreateSubscriptionRequest;
use App\Http\Requests\SwitchBillingCycleRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Models\Subscription;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class SubscriptionController extends Controller
{
    public function __construct(
        private readonly CreateSubscription $createSubscription,
        private readonly ChangeSubscriptionPlan $changeSubscriptionPlan,
        private readonly CancelSubscription $cancelSubscription,
        private readonly ResumeSubscription $resumeSubscription,
        private readonly SwitchBillingCycle $switchBillingCycle,
    ) {}

    /**
     * Display user's subscription
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Subscription::class);

        $user = auth()->user();
        $subscription = $user->activeSubscription;
        $subscriptionHistory = $user->subscriptions()
            ->with('plan')
            ->latest()
            ->paginate(10);

        return view('dashboard.subscriptions.index', [
            'subscription' => $subscription,
            'subscriptionHistory' => $subscriptionHistory,
        ]);
    }

    /**
     * Create a new subscription
     */
    public function store(CreateSubscriptionRequest $request): RedirectResponse
    {
        Gate::authorize('create', Subscription::class);

        $user = $request->user();

        // Check if user already has an active subscription
        if ($user->hasActiveSubscription()) {
            return back()->withErrors(['subscription' => 'You already have an active subscription.']);
        }

        try {
            $this->createSubscription->execute($user, $request->validated());

            return to_route('subscriptions.index')
                ->with('success', 'Subscription created successfully!');
        } catch (Exception $exception) {
            return back()->withErrors(['subscription' => 'Failed to create subscription: '.$exception->getMessage()]);
        }
    }

    /**
     * Update subscription plan
     */
    public function update(UpdateSubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        Gate::authorize('update', $subscription);

        try {
            $this->changeSubscriptionPlan->execute($subscription, $request->validated());

            return to_route('subscriptions.index')
                ->with('success', 'Subscription plan updated successfully!');
        } catch (Exception $exception) {
            return back()->withErrors(['subscription' => 'Failed to update subscription: '.$exception->getMessage()]);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(CancelSubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        Gate::authorize('cancel', $subscription);

        try {
            $this->cancelSubscription->execute($subscription, $request->validated());

            $message = $request->input('immediate', false)
                ? 'Subscription canceled immediately.'
                : 'Subscription will be canceled at the end of the billing period.';

            return to_route('subscriptions.index')
                ->with('success', $message);
        } catch (Exception $exception) {
            return back()->withErrors(['subscription' => 'Failed to cancel subscription: '.$exception->getMessage()]);
        }
    }

    /**
     * Resume canceled subscription
     */
    public function resume(Subscription $subscription): RedirectResponse
    {
        Gate::authorize('resume', $subscription);

        try {
            $this->resumeSubscription->execute($subscription);

            return to_route('subscriptions.index')
                ->with('success', 'Subscription resumed successfully!');
        } catch (Exception $exception) {
            return back()->withErrors(['subscription' => 'Failed to resume subscription: '.$exception->getMessage()]);
        }
    }

    /**
     * Switch billing cycle
     */
    public function switchCycle(SwitchBillingCycleRequest $request, Subscription $subscription): RedirectResponse
    {
        Gate::authorize('update', $subscription);

        try {
            $billingCycle = BillingCycle::from($request->input('billing_cycle'));
            $this->switchBillingCycle->execute($subscription, $billingCycle);

            return to_route('subscriptions.index')
                ->with('success', 'Billing cycle updated successfully!');
        } catch (Exception $exception) {
            return back()->withErrors(['subscription' => 'Failed to switch billing cycle: '.$exception->getMessage()]);
        }
    }
}
