<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BusinessProfile;
use App\Models\User;

final class BusinessProfilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Authenticated users can view their own business profile
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BusinessProfile $businessProfile): bool
    {
        return $user->id === $businessProfile->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Authenticated users can create business profile
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BusinessProfile $businessProfile): bool
    {
        return $user->id === $businessProfile->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BusinessProfile $businessProfile): bool
    {
        return $user->id === $businessProfile->user_id;
    }
}
