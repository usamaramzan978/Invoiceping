<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BusinessProfile;
use App\Models\Invoice;
use App\Models\User;

final class InvoicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(): bool
    {
        return true;
        // Authenticated users can view their own invoices
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        $invoice->loadMissing('business');

        if (! $invoice->business) {
            return false;
        }

        /** @var BusinessProfile $business */
        $business = $invoice->business;

        return $business->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(): bool
    {
        return true;
        // Authenticated users can create invoices
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        $invoice->loadMissing('business');

        if (! $invoice->business) {
            return false;
        }

        /** @var BusinessProfile $business */
        $business = $invoice->business;

        return $business->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        $invoice->loadMissing('business');

        if (! $invoice->business) {
            return false;
        }

        /** @var BusinessProfile $business */
        $business = $invoice->business;

        return $business->user_id === $user->id;
    }
}
