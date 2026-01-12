<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\WhatsAppProviderType;
use App\Models\WhatsAppProvider;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service for sending WhatsApp messages via different providers.
 * Handles provider selection, message preparation, and sending.
 */
final class WhatsAppService
{

    /**
     * Send WhatsApp message using the user's default provider.
     *
     * @param  string  $userId  User ID
     * @param  string  $recipient  Recipient phone number
     * @param  string  $message  Message content
     * @return array{success: bool, message_id?: string, provider?: string, error?: string}
     */
    public function send(string $userId, string $recipient, string $message): array
    {
        try {
            // Get default provider for user
            $provider = $this->getDefaultProvider($userId);

            throw_unless($provider, Exception::class, 'No active WhatsApp provider found for user');

            // Format phone number
            $formattedPhone = $this->formatPhoneNumber($recipient);

            // Send based on provider type
            return match ($provider->type) {
                WhatsAppProviderType::WHATSAPP_CLOUD_API => $this->sendViaWhatsAppCloudApi($provider, $formattedPhone, $message),
                WhatsAppProviderType::TWILIO => $this->sendViaTwilio($provider, $formattedPhone, $message),
                WhatsAppProviderType::VONAGE => $this->sendViaVonage($provider, $formattedPhone, $message),
            };
        } catch (Exception $exception) {
            Log::error('WhatsApp send failed', [
                'user_id' => $userId,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Get default active provider for user.
     * Falls back to any active provider if no default is set.
     */
    private function getDefaultProvider(string $userId): ?WhatsAppProvider
    {
        // First try to get default provider
        $provider = WhatsAppProvider::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();

        // If no default, get any active provider as fallback
        if (! $provider) {
            return WhatsAppProvider::query()
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->first();
        }

        return $provider;
    }

    /**
     * Send message via WhatsApp Cloud API.
     *
     * @return array{success: bool, message_id?: string, provider: string, error?: string}
     */
    private function sendViaWhatsAppCloudApi(WhatsAppProvider $provider, string $phoneNumber, string $message): array
    {
        try {
            $accessToken = $provider->access_token; // Auto-decrypted via accessor
            $phoneNumberId = $provider->phone_number_id;

            throw_if(empty($accessToken) || empty($phoneNumberId), Exception::class, 'WhatsApp Cloud API credentials are missing');

            $url = sprintf('https://graph.facebook.com/v18.0/%s/messages', $phoneNumberId);

            $response = Http::withToken($accessToken)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => $phoneNumber,
                    'type' => 'text',
                    'text' => [
                        'body' => $message,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();

                // Update last used timestamp
                $provider->update(['last_used_at' => now()]);

                return [
                    'success' => true,
                    'message_id' => $data['messages'][0]['id'] ?? null,
                    'provider' => 'whatsapp_cloud_api',
                ];
            }

            throw new Exception('WhatsApp Cloud API error: ' . $response->body());
        } catch (Exception $exception) {
            Log::error('WhatsApp Cloud API send failed', [
                'provider_id' => $provider->id,
                'error' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'provider' => 'whatsapp_cloud_api',
                'error' => $exception->getMessage(),
            ];
        }
    }
    public function verifyConnectionWhatsAppCloudApi(WhatsAppProvider $provider): bool
    {
        $accessToken = $provider->access_token; // Auto-decrypted via accessor
        $phoneNumberId = $provider->phone_number_id;

        throw_if(empty($accessToken) || empty($phoneNumberId), Exception::class, 'WhatsApp Cloud API credentials are missing');

        $url = sprintf('https://graph.facebook.com/v18.0/%s/messages', $phoneNumberId);

        $response = Http::withToken($accessToken)->get($url);
        if ($response->successful()) {
            return true;
        }

        throw new Exception('WhatsApp Cloud API verify connection failed: ' . $response->body() ?? 'Unknown error');
    }
    /**
     * Send message via Twilio.
     *
     * @return array{success: bool, message_id?: string, provider: string, error?: string}
     */
    private function sendViaTwilio(WhatsAppProvider $provider, string $phoneNumber, string $message): array
    {
        try {
            $accountSid = $provider->account_sid;
            $authToken = $provider->auth_token; // Auto-decrypted via accessor
            $fromNumber = $provider->from_phone_number ?? $provider->whatsapp_sandbox_number;

            throw_if(empty($accountSid) || empty($authToken) || empty($fromNumber), Exception::class, 'Twilio credentials are missing');

            $url = sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', $accountSid);

            $response = Http::withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->post($url, [
                    'From' => 'whatsapp:' . $fromNumber,
                    'To' => 'whatsapp:' . $phoneNumber,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                // Update last used timestamp
                $provider->update(['last_used_at' => now()]);

                return [
                    'success' => true,
                    'message_id' => $data['sid'] ?? null,
                    'provider' => 'twilio',
                ];
            }

            throw new Exception('Twilio API error: ' . $response->body());
        } catch (Exception $exception) {
            Log::error('Twilio send failed', [
                'provider_id' => $provider->id,
                'error' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'provider' => 'twilio',
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Send message via Vonage (Nexmo).
     *
     * @return array{success: bool, message_id?: string, provider: string, error?: string}
     */
    private function sendViaVonage(WhatsAppProvider $provider, string $phoneNumber, string $message): array
    {
        try {
            $apiKey = $provider->api_key;
            $apiSecret = $provider->api_secret; // Auto-decrypted via accessor
            $fromNumber = $provider->from_number;

            throw_if(empty($apiKey) || empty($apiSecret) || empty($fromNumber), Exception::class, 'Vonage credentials are missing');

            $url = 'https://messages-sandbox.nexmo.com/v1/messages';

            $response = Http::withBasicAuth($apiKey, $apiSecret)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($url, [
                    'from' => [
                        'type' => 'whatsapp',
                        'number' => $fromNumber,
                    ],
                    'to' => [
                        'type' => 'whatsapp',
                        'number' => $phoneNumber,
                    ],
                    'message' => [
                        'content' => [
                            'type' => 'text',
                            'text' => $message,
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();

                // Update last used timestamp
                $provider->update(['last_used_at' => now()]);

                return [
                    'success' => true,
                    'message_id' => $data['message_uuid'] ?? null,
                    'provider' => 'vonage',
                ];
            }

            throw new Exception('Vonage API error: ' . $response->body());
        } catch (Exception $exception) {
            Log::error('Vonage send failed', [
                'provider_id' => $provider->id,
                'error' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'provider' => 'vonage',
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Format phone number to E.164 format.
     * If already in E.164 format (starts with +), return as is.
     */
    private function formatPhoneNumber(string $phone): string
    {
        // If already in E.164 format (starts with +), remove + and return
        if (str_starts_with($phone, '+')) {
            return ltrim($phone, '+');
        }

        // Remove any non-digit characters
        $cleaned = preg_replace('/\D/', '', $phone);

        // If already starts with country code (11+ digits), return as is
        if (strlen($cleaned) >= 11 && preg_match('/^\d{11,15}$/', $cleaned)) {
            return $cleaned;
        }

        // Add country code if not present (default to 92 for Pakistan)
        if (! str_starts_with((string) $cleaned, '92')) {
            // Remove leading 0 if present
            $cleaned = mb_ltrim($cleaned, '0');

            return '92' . $cleaned;
        }

        return $cleaned;
    }
}
