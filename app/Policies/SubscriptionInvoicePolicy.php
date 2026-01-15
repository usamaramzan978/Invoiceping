<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SubscriptionInvoice;
use App\Models\User;

final class SubscriptionInvoicePolicy
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
     * Determine if the user can view the invoice
     */
    public function view(User $user, SubscriptionInvoice $invoice): bool
    {
        return $user->id === $invoice->user_id;
    }

    /**
     * Determine if the user can download the invoice
     */
    public function download(User $user, SubscriptionInvoice $invoice): bool
    {
        return $user->id === $invoice->user_id;
    }
}
