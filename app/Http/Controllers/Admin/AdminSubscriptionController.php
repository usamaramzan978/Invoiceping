<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Subscription\CancelSubscription;
use App\Actions\Subscription\CreateSubscription;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateManualSubscriptionRequest;
use App\Http\Requests\Admin\UpdateManualSubscriptionRequest;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class AdminSubscriptionController extends Controller
{
    public function __construct(
        private readonly CreateSubscription $createSubscription,
        private readonly CancelSubscription $cancelSubscription,
    ) {}

    /**
     * Display a listing of all subscriptions
     */
    public function index(): View
    {
        $subscriptions = Subscription::with(['user', 'plan'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => Subscription::query()->count(),
            'active' => Subscription::active()->count(),
            'trialing' => Subscription::trialing()->count(),
            'canceled' => Subscription::query()->where('status', 'canceled')->count(),
        ];

        return view('admin.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new subscription
     */
    public function create(): View
    {
        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);
        $plans = SubscriptionPlan::active()->orderBy('sort_order')->get();

        return view('admin.subscriptions.create', [
            'users' => $users,
            'plans' => $plans,
        ]);
    }

    /**
     * Store a newly created subscription
     */
    public function store(CreateManualSubscriptionRequest $request): RedirectResponse
    {
        $user = User::query()->findOrFail($request->input('user_id'));

        // Check if user already has an active subscription
        if ($user->hasActiveSubscription()) {
            return back()
                ->withInput()
                ->withErrors(['user_id' => 'This user already has an active subscription.']);
        }

        try {
            $this->createSubscription->execute($user, $request->validated());

            return to_route('admin.subscriptions.index')
                ->with('success', 'Subscription created successfully!');
        } catch (Exception $exception) {
            return back()
                ->withInput()
                ->withErrors(['subscription' => 'Failed to create subscription: '.$exception->getMessage()]);
        }
    }

    /**
     * Display the specified subscription
     */
    public function show(Subscription $subscription): View
    {
        $subscription->load(['user', 'plan', 'invoices', 'transactions']);

        return view('admin.subscriptions.show', [
            'subscription' => $subscription,
        ]);
    }

    /**
     * Show the form for editing the specified subscription
     */
    public function edit(Subscription $subscription): View
    {
        $plans = SubscriptionPlan::active()->orderBy('sort_order')->get();

        return view('admin.subscriptions.edit', [
            'subscription' => $subscription->load(['user', 'plan']),
            'plans' => $plans,
        ]);
    }

    /**
     * Update the specified subscription
     */
    public function update(UpdateManualSubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        try {
            $data = $request->validated();

            // Update basic fields
            if (isset($data['plan_id'])) {
                $plan = SubscriptionPlan::query()->findOrFail($data['plan_id']);
                $subscription->plan_id = (string) $plan->id;

                // Update amount based on current billing cycle
                $subscription->amount = $subscription->billing_cycle->value === 'yearly'
                    ? $plan->yearly_price
                    : $plan->monthly_price;
            }

            if (isset($data['billing_cycle'])) {
                $subscription->billing_cycle = $data['billing_cycle'];

                // Update amount based on new billing cycle
                $plan = $subscription->plan;
                $subscription->amount = $data['billing_cycle'] === 'yearly'
                    ? $plan->yearly_price
                    : $plan->monthly_price;
            }

            if (isset($data['status'])) {
                $subscription->status = $data['status'];
            }

            if (isset($data['trial_ends_at'])) {
                $subscription->trial_ends_at = $data['trial_ends_at'];
            }

            if (isset($data['renews_at'])) {
                $subscription->renews_at = $data['renews_at'];
            }

            $subscription->save();

            return to_route('admin.subscriptions.show', $subscription)
                ->with('success', 'Subscription updated successfully!');
        } catch (Exception $exception) {
            return back()
                ->withInput()
                ->withErrors(['subscription' => 'Failed to update subscription: '.$exception->getMessage()]);
        }
    }

    /**
     * Cancel the specified subscription
     */
    public function destroy(Subscription $subscription): RedirectResponse
    {
        try {
            $this->cancelSubscription->execute($subscription, ['immediate' => true]);

            return to_route('admin.subscriptions.index')
                ->with('success', 'Subscription canceled successfully!');
        } catch (Exception $exception) {
            return back()->withErrors(['subscription' => 'Failed to cancel subscription: '.$exception->getMessage()]);
        }
    }
}
