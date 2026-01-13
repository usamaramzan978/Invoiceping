<?php

declare(strict_types=1);

namespace App\Actions\WhatsAppProvider;

use App\Models\WhatsAppProvider;
use Illuminate\Support\Facades\DB;

final class SetDefaultWhatsAppProviderAction
{
    public function execute(WhatsAppProvider $provider): WhatsAppProvider
    {
        return DB::transaction(function () use ($provider): WhatsAppProvider {
            // Unset other defaults
            WhatsAppProvider::query()
                ->where('user_id', $provider->user_id)
                ->where('id', '!=', $provider->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            // Set this provider as default
            $provider->is_default = true;
            $provider->save();

            return $provider;
        });
    }
}

