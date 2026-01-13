<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MessageTemplates;
use App\Models\User;

final class MessageTemplatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Authenticated users can view their own templates
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MessageTemplates $messageTemplate): bool
    {
        return $user->id === $messageTemplate->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Authenticated users can create templates
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MessageTemplates $messageTemplate): bool
    {
        return $user->id === $messageTemplate->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MessageTemplates $messageTemplate): bool
    {
        return $user->id === $messageTemplate->user_id;
    }
}
