<?php

declare(strict_types=1);

namespace App\Actions\Subscription;

use App\Enums\BillingCycle;
use App\Models\Subscription;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;

final class SwitchBillingCycle
{
    /**
     * Switch between monthly and yearly billing
     */
    public function execute(Subscription $subscription, BillingCycle $newCycle): Subscription
    {
        throw_if($subscription->billing_cycle === $newCycle, InvalidArgumentException::class, 'Subscription is already on this billing cycle.');

        $plan = $subscription->plan;

        // Update amount based on new cycle
        $subscription->amount = $newCycle === BillingCycle::YEARLY
            ? $plan->yearly_price
            : $plan->monthly_price;

        $subscription->billing_cycle = $newCycle;

        // Adjust renewal date
        $subscription->renews_at = $newCycle === BillingCycle::YEARLY
            ? Date::now()->addYear()
            : Date::now()->addMonth();

        $subscription->save();

        return $subscription;
    }
}
