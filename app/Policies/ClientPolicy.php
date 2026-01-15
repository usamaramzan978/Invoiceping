<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BusinessProfile;
use App\Models\Client;
use App\Models\User;

final class ClientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(): bool
    {
        return true;
        // Authenticated users can view their own clients
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Client $client): bool
    {
        $client->loadMissing('business');

        if (! $client->business) {
            return false;
        }

        /** @var BusinessProfile $business */
        $business = $client->business;

        return $business->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(): bool
    {
        return true;
        // Authenticated users can create clients
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Client $client): bool
    {
        $client->loadMissing('business');

        if (! $client->business) {
            return false;
        }

        /** @var BusinessProfile $business */
        $business = $client->business;

        return $business->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Client $client): bool
    {
        $client->loadMissing('business');

        if (! $client->business) {
            return false;
        }

        /** @var BusinessProfile $business */
        $business = $client->business;

        return $business->user_id === $user->id;
    }
}
