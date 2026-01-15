<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ReminderRule;
use App\Models\User;

final class ReminderRulePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(): bool
    {
        return true;
        // Authenticated users can view their own rules
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ReminderRule $reminderRule): bool
    {
        return $user->id === $reminderRule->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(): bool
    {
        return true;
        // Authenticated users can create rules
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ReminderRule $reminderRule): bool
    {
        return $user->id === $reminderRule->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ReminderRule $reminderRule): bool
    {
        return $user->id === $reminderRule->user_id;
    }

    /**
     * Determine whether the user can toggle the status of the rule.
     */
    public function toggleStatus(User $user, ReminderRule $reminderRule): bool
    {
        return $user->id === $reminderRule->user_id;
    }
}
