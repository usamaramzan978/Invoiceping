<?php

declare(strict_types=1);

namespace App\Actions\Subscription;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;

final class ResumeSubscription
{
    /**
     * Resume a canceled subscription
     */
    public function execute(Subscription $subscription): Subscription
    {
        throw_if($subscription->status !== SubscriptionStatus::CANCELED, InvalidArgumentException::class, 'Only canceled subscriptions can be resumed.');

        throw_if($subscription->ends_at && $subscription->ends_at->isPast(), InvalidArgumentException::class, 'Cannot resume an expired subscription.');

        $subscription->status = SubscriptionStatus::ACTIVE;
        $subscription->canceled_at = null;
        $subscription->ends_at = null;

        // Recalculate renewal date
        $now = Date::now();
        if ($subscription->renews_at === null || $subscription->renews_at->isPast()) {
            $subscription->renews_at = $subscription->billing_cycle->value === 'yearly'
                ? $now->copy()->addYear()
                : $now->copy()->addMonth();
        }

        $subscription->save();

        return $subscription;
    }
}
