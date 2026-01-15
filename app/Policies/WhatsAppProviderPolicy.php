<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WhatsAppProvider;

final class WhatsAppProviderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(): bool
    {
        return true;
        // Authenticated users can view their own providers
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WhatsAppProvider $whatsAppProvider): bool
    {
        return $user->id === $whatsAppProvider->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(): bool
    {
        return true;
        // Authenticated users can create providers
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WhatsAppProvider $whatsAppProvider): bool
    {
        return $user->id === $whatsAppProvider->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WhatsAppProvider $whatsAppProvider): bool
    {
        return $user->id === $whatsAppProvider->user_id;
    }

    /**
     * Determine whether the user can toggle the status of the provider.
     */
    public function toggleStatus(User $user, WhatsAppProvider $whatsAppProvider): bool
    {
        return $user->id === $whatsAppProvider->user_id;
    }

    /**
     * Determine whether the user can set the provider as default.
     */
    public function setDefault(User $user, WhatsAppProvider $whatsAppProvider): bool
    {
        return $user->id === $whatsAppProvider->user_id;
    }
}
