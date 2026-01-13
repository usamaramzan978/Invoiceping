<?php

declare(strict_types=1);

namespace App\Actions\WhatsAppProvider;

use App\Enums\WhatsAppProviderType;
use App\Models\User;
use App\Models\WhatsAppProvider;
use Illuminate\Support\Facades\DB;

final class CreateWhatsAppProviderAction
{
    public function execute(User $user, array $data): WhatsAppProvider
    {
        return DB::transaction(function () use ($user, $data): WhatsAppProvider {
            // If this provider is being set as default, unset other defaults
            if (isset($data['is_default']) && $data['is_default']) {
                WhatsAppProvider::query()
                    ->where('user_id', $user->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            // Create the provider
            $provider = new WhatsAppProvider();
            $provider->user_id = (string) $user->id;
            $provider->name = $data['name'];
            $provider->type = $data['type'];
            $provider->is_active = $data['is_active'] ?? true;
            $provider->is_default = $data['is_default'] ?? false;
            $provider->notes = $data['notes'] ?? null;

            // Set credentials based on provider type
            $providerType = WhatsAppProviderType::tryFrom($data['type']) ?? WhatsAppProviderType::WHATSAPP_CLOUD_API;
            if ($providerType === WhatsAppProviderType::WHATSAPP_CLOUD_API) {
                $provider->access_token = $data['access_token'] ?? null;
                $provider->phone_number_id = $data['phone_number_id'] ?? null;
                $provider->business_account_id = $data['business_account_id'] ?? null;
                $provider->app_id = $data['app_id'] ?? null;
                $provider->app_secret = $data['app_secret'] ?? null;
            } elseif ($providerType === WhatsAppProviderType::TWILIO) {
                $provider->account_sid = $data['account_sid'] ?? null;
                $provider->auth_token = $data['auth_token'] ?? null;
                $provider->from_phone_number = $data['from_phone_number'] ?? null;
                $provider->whatsapp_sandbox_number = $data['whatsapp_sandbox_number'] ?? null;
            } elseif ($providerType === WhatsAppProviderType::VONAGE) {
                $provider->api_key = $data['api_key'] ?? null;
                $provider->api_secret = $data['api_secret'] ?? null;
                $provider->from_number = $data['from_number'] ?? null;
                $provider->application_id = $data['application_id'] ?? null;
            }

            $provider->save();

            return $provider;
        });
    }
}

