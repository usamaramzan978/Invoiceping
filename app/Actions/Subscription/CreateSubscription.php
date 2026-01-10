<?php

declare(strict_types=1);

namespace App\Actions\Subscription;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\Date;

final class CreateSubscription
{
    /**
     * Create a new subscription for a user
     *
     * @param  array{plan_id: string, billing_cycle: string, trial_days?: int, starts_at?: string}  $data
     */
    public function execute(User $user, array $data): Subscription
    {
        $plan = SubscriptionPlan::query()->findOrFail($data['plan_id']);
        $billingCycle = BillingCycle::from($data['billing_cycle']);

        // Calculate amount based on billing cycle
        $amount = $billingCycle === BillingCycle::YEARLY
            ? $plan->yearly_price
            : $plan->monthly_price;

        // Determine start date
        $startsAt = isset($data['starts_at'])
            ? Date::parse($data['starts_at'])
            : Date::now();

        // Calculate trial period
        $trialDays = (int) ($data['trial_days'] ?? 0);
        $trialEndsAt = $trialDays > 0 ? $startsAt->copy()->addDays($trialDays) : null;

        // Calculate renewal date
        $renewsAt = $trialEndsAt
            ? $trialEndsAt->copy()
            : $startsAt->copy();

        $renewsAt = $billingCycle === BillingCycle::YEARLY
            ? $renewsAt->addYear()
            : $renewsAt->addMonth();

        // Determine initial status
        $status = $trialDays > 0
            ? SubscriptionStatus::TRIALING
            : SubscriptionStatus::ACTIVE;

        // Create subscription
        $subscription = new Subscription();
        $subscription->user_id = (string) $user->id;
        $subscription->plan_id = (string) $plan->id;
        $subscription->billing_cycle = $billingCycle;
        $subscription->amount = $amount;
        $subscription->currency = $plan->currency;
        $subscription->status = $status;
        $subscription->trial_ends_at = $trialEndsAt;
        $subscription->starts_at = $startsAt;
        $subscription->renews_at = $renewsAt;
        $subscription->save();

        return $subscription;
    }
}
