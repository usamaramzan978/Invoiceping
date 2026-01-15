<?php

declare(strict_types=1);

namespace App\Actions\WhatsAppProvider;

use App\Models\WhatsAppProvider;

final class DeleteWhatsAppProviderAction
{
    public function execute(WhatsAppProvider $provider): bool
    {
        return $provider->delete();
    }
}
