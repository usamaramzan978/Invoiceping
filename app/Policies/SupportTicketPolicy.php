<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BusinessProfile;
use App\Models\SupportTicket;
use App\Models\User;

final class SupportTicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(): bool
    {
        return true;
        // Authenticated users can view their own support tickets
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SupportTicket $ticket): bool
    {
        $ticket->loadMissing('business');

        if (! $ticket->business) {
            return false;
        }

        /** @var BusinessProfile $business */
        $business = $ticket->business;

        return $business->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(): bool
    {
        return true;
        // Authenticated users can create support tickets
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SupportTicket $ticket): bool
    {
        $ticket->loadMissing('business');

        if (! $ticket->business) {
            return false;
        }

        /** @var BusinessProfile $business */
        $business = $ticket->business;

        return $business->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SupportTicket $ticket): bool
    {
        $ticket->loadMissing('business');

        if (! $ticket->business) {
            return false;
        }

        /** @var BusinessProfile $business */
        $business = $ticket->business;

        return $business->user_id === $user->id;
    }
}

