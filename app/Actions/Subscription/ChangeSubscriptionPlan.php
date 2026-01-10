<?php

declare(strict_types=1);

namespace App\Actions\Subscription;

use App\Enums\BillingCycle;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Date;

final class ChangeSubscriptionPlan
{
    /**
     * Change/upgrade/downgrade subscription plan
     *
     * @param  array{plan_id: string, billing_cycle?: string, prorate?: bool}  $data
     */
    public function execute(Subscription $subscription, array $data): Subscription
    {
        $newPlan = SubscriptionPlan::query()->findOrFail($data['plan_id']);
        $billingCycle = isset($data['billing_cycle'])
            ? BillingCycle::from($data['billing_cycle'])
            : $subscription->billing_cycle;

        // Calculate new amount
        $newAmount = $billingCycle === BillingCycle::YEARLY
            ? $newPlan->yearly_price
            : $newPlan->monthly_price;

        // Update subscription
        $subscription->plan_id = (string) $newPlan->id;
        $subscription->billing_cycle = $billingCycle;
        $subscription->amount = $newAmount;
        $subscription->currency = $newPlan->currency;

        // If proration is enabled, adjust the renewal date
        if (($data['prorate'] ?? false) && $subscription->renews_at) {
            // Keep the same renewal date for proration
            // In real implementation, you'd calculate prorated credits/charges
        } else {
            // Reset renewal date based on new billing cycle
            $subscription->renews_at = $billingCycle === BillingCycle::YEARLY
                ? Date::now()->addYear()
                : Date::now()->addMonth();
        }

        $subscription->save();

        return $subscription;
    }
}
