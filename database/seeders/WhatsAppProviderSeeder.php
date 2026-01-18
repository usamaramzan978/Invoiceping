<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\WhatsAppProviderType;
use App\Models\User;
use App\Models\WhatsAppProvider;
use Illuminate\Database\Seeder;

final class WhatsAppProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a demo user
        $user = User::query()->first();

        if (! $user) {
            return;
        }

        // Check if provider already exists
        $existingProvider = WhatsAppProvider::query()
            ->where('user_id', $user->id)
            ->where('name', 'WhatsApp Cloud API - Local')
            ->first();

        if ($existingProvider) {
            // Update existing provider with new token if needed
            // Note: The access_token will be automatically encrypted by the model mutator
            $existingProvider->update([
                'access_token' => 'EAAbEvpHafCsBQfBh5ZBUGXJcqmLJOgkg2wdDiAUttIbl6wTEMUiUAB9tKljc52BCyasMnRV4GcYL5MKpiHNXwEw2ohowNCA1IlXQmmyhY1pynOj72XN5ynPJAcOJrLYlBDZCEOitfYNyAcIiDXZAx1dPDoo47xwV73fRsu8HyUAZB22qUooKzL8AJN2gOSd0cVi3D79zqghz5ZCZCQIxbQkw0fV4pdz1iOmGaRM1D5PNYy1LjRJelabNVeZC1WF48dOEn9xMpZC1n0tik1WZAb8uy',
                'phone_number_id' => '916591911544094',
                'business_account_id' => '3970087699960997',
                'app_id' => '1905172630109227',
                'is_active' => true,
                'is_default' => true,
            ]);

            return;
        }

        // LOCAL ACCOUNT - Create new provider
        WhatsAppProvider::query()->create([
            'user_id' => $user->id,
            'name' => 'WhatsApp Cloud API - Local',
            'type' => WhatsAppProviderType::WHATSAPP_CLOUD_API->value,
            'access_token' => 'EAAbEvpHafCsBQfBh5ZBUGXJcqmLJOgkg2wdDiAUttIbl6wTEMUiUAB9tKljc52BCyasMnRV4GcYL5MKpiHNXwEw2ohowNCA1IlXQmmyhY1pynOj72XN5ynPJAcOJrLYlBDZCEOitfYNyAcIiDXZAx1dPDoo47xwV73fRsu8HyUAZB22qUooKzL8AJN2gOSd0cVi3D79zqghz5ZCZCQIxbQkw0fV4pdz1iOmGaRM1D5PNYy1LjRJelabNVeZC1WF48dOEn9xMpZC1n0tik1WZAb8uy',
            'phone_number_id' => '916591911544094',
            'business_account_id' => '3970087699960997',
            'app_id' => '1905172630109227',
            'app_secret' => '',
            'is_active' => true,
            'is_default' => true,
            'notes' => 'LOCAL WhatsApp Cloud API account',
        ]);
    }
}
