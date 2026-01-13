<?php

declare(strict_types=1);

namespace App\Actions\WhatsAppProvider;

use App\Enums\WhatsAppProviderType;
use App\Models\WhatsAppProvider;
use Illuminate\Support\Facades\DB;

final class UpdateWhatsAppProviderAction
{
    public function execute(WhatsAppProvider $provider, array $data): WhatsAppProvider
    {
        return DB::transaction(function () use ($provider, $data): WhatsAppProvider {
            // If this provider is being set as default, unset other defaults
            if (isset($data['is_default']) && $data['is_default']) {
                WhatsAppProvider::query()
                    ->where('user_id', $provider->user_id)
                    ->where('id', '!=', $provider->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            // Update the provider
            if (isset($data['name'])) {
                $provider->name = $data['name'];
            }

            if (isset($data['type'])) {
                $provider->type = $data['type'];
            }

            // Update credentials based on provider type
            $type = $provider->type; // Already cast to enum
            if ($type === WhatsAppProviderType::WHATSAPP_CLOUD_API) {
                if (isset($data['access_token'])) {
                    $provider->access_token = $data['access_token'];
                }

                if (isset($data['phone_number_id'])) {
                    $provider->phone_number_id = $data['phone_number_id'];
                }

                if (isset($data['business_account_id'])) {
                    $provider->business_account_id = $data['business_account_id'];
                }

                if (isset($data['app_id'])) {
                    $provider->app_id = $data['app_id'];
                }

                if (isset($data['app_secret'])) {
                    $provider->app_secret = $data['app_secret'];
                }
            } elseif ($type === WhatsAppProviderType::TWILIO) {
                if (isset($data['account_sid'])) {
                    $provider->account_sid = $data['account_sid'];
                }

                if (isset($data['auth_token'])) {
                    $provider->auth_token = $data['auth_token'];
                }

                if (isset($data['from_phone_number'])) {
                    $provider->from_phone_number = $data['from_phone_number'];
                }

                if (isset($data['whatsapp_sandbox_number'])) {
                    $provider->whatsapp_sandbox_number = $data['whatsapp_sandbox_number'];
                }
            } elseif ($type === WhatsAppProviderType::VONAGE) {
                if (isset($data['api_key'])) {
                    $provider->api_key = $data['api_key'];
                }

                if (isset($data['api_secret'])) {
                    $provider->api_secret = $data['api_secret'];
                }

                if (isset($data['from_number'])) {
                    $provider->from_number = $data['from_number'];
                }

                if (isset($data['application_id'])) {
                    $provider->application_id = $data['application_id'];
                }
            }

            if (isset($data['is_active'])) {
                $provider->is_active = $data['is_active'];
            }

            if (isset($data['is_default'])) {
                $provider->is_default = $data['is_default'];
            }

            if (isset($data['notes'])) {
                $provider->notes = $data['notes'];
            }

            $provider->save();

            return $provider;
        });
    }
}

