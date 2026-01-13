<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BillingTransaction;
use App\Models\User;

final class BillingTransactionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Authenticated users can view their own transactions
    }

    /**
     * Determine if the user can view the transaction
     */
    public function view(User $user, BillingTransaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }
}
