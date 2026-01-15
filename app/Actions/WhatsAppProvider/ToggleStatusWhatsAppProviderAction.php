<?php

declare(strict_types=1);

namespace App\Actions\WhatsAppProvider;

use App\Models\WhatsAppProvider;

final class ToggleStatusWhatsAppProviderAction
{
    public function execute(WhatsAppProvider $provider): WhatsAppProvider
    {
        $provider->is_active = ! $provider->is_active;
        $provider->save();

        return $provider;
    }
}
