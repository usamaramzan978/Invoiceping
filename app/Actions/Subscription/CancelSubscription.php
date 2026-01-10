<?php

declare(strict_types=1);

namespace App\Actions\Subscription;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Support\Facades\Date;

final class CancelSubscription
{
    /**
     * Cancel a subscription
     *
     * @param  array{immediate?: bool}  $options
     */
    public function execute(Subscription $subscription, array $options = []): Subscription
    {
        $immediate = $options['immediate'] ?? false;

        if ($immediate) {
            // Cancel immediately
            $subscription->status = SubscriptionStatus::CANCELED;
            $subscription->ends_at = Date::now();
            $subscription->canceled_at = Date::now();
            $subscription->renews_at = null;
        } else {
            // Cancel at end of billing period
            $subscription->status = SubscriptionStatus::CANCELED;
            $subscription->canceled_at = Date::now();
            $subscription->ends_at = $subscription->renews_at;
            // Keep renews_at as null since it won't renew
            $subscription->renews_at = null;
        }

        $subscription->save();

        return $subscription;
    }
}
