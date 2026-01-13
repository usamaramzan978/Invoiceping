<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

final class SubscriptionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Authenticated users can view their own subscriptions
    }

    /**
     * Determine if the user can view the subscription
     */
    public function view(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id;
    }

    /**
     * Determine if the user can create subscriptions
     */
    public function create(User $user): bool
    {
        return true; // Authenticated users can create subscriptions
    }

    /**
     * Determine if the user can update the subscription
     */
    public function update(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id
            && $subscription->isActive();
    }

    /**
     * Determine if the user can cancel the subscription
     */
    public function cancel(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id
            && $subscription->isActive();
    }

    /**
     * Determine if the user can resume the subscription
     */
    public function resume(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id
            && $subscription->isCanceled()
            && (! $subscription->ends_at || $subscription->ends_at->isFuture());
    }
}
