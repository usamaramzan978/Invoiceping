<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\WhatsAppProviderType;
use App\Models\WhatsAppProvider;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
     * @param  string|null  $pdfPath  Optional PDF file path (relative to storage/public)
     * @param  string|null  $pdfFileName  Optional PDF filename for attachment
     * @return array{success: bool, message_id?: string, provider?: string, error?: string}
     */
    public function send(string $userId, string $recipient, string $message, ?string $pdfPath = null, ?string $pdfFileName = null): array
    {
        try {
            // Get default provider for user
            $provider = $this->getDefaultProvider($userId);

            throw_unless($provider, Exception::class, 'No active WhatsApp provider found for user');

            // Format phone number
            $formattedPhone = $this->formatPhoneNumber($recipient);

            // Send based on provider type
            return match ($provider->type) {
                WhatsAppProviderType::WHATSAPP_CLOUD_API => $this->sendViaWhatsAppCloudApi($provider, $formattedPhone, $message, $pdfPath, $pdfFileName),
                WhatsAppProviderType::TWILIO => $this->sendViaTwilio($provider, $formattedPhone, $message, $pdfPath, $pdfFileName),
                WhatsAppProviderType::VONAGE => $this->sendViaVonage($provider, $formattedPhone, $message, $pdfPath, $pdfFileName),
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
    private function sendViaWhatsAppCloudApi(WhatsAppProvider $provider, string $phoneNumber, string $message, ?string $pdfPath = null, ?string $pdfFileName = null): array
    {
        try {
            $accessToken = $provider->access_token; // Auto-decrypted via accessor
            $phoneNumberId = $provider->phone_number_id;

            throw_if(empty($accessToken) || empty($phoneNumberId), Exception::class, 'WhatsApp Cloud API credentials are missing');

            $url = sprintf('https://graph.facebook.com/v18.0/%s/messages', $phoneNumberId);

            // If PDF is provided, send document message
            if ($pdfPath && Storage::disk('public')->exists($pdfPath)) {
                // First, upload media to get media ID
                $mediaId = $this->uploadMediaToWhatsAppCloudApi($provider, $pdfPath, $pdfFileName);

                if ($mediaId) {
                    // Send document message with media ID
                    $response = Http::withToken($accessToken)
                        ->post($url, [
                            'messaging_product' => 'whatsapp',
                            'to' => $phoneNumber,
                            'type' => 'document',
                            'document' => [
                                'id' => $mediaId,
                                'caption' => $message, // Optional caption
                            ],
                        ]);
                } else {
                    // Fallback to text message if media upload fails
                    $response = Http::withToken($accessToken)
                        ->post($url, [
                            'messaging_product' => 'whatsapp',
                            'to' => $phoneNumber,
                            'type' => 'text',
                            'text' => [
                                'body' => $message,
                            ],
                        ]);
                }
            } else {
                // Send text message only
                $response = Http::withToken($accessToken)
                    ->post($url, [
                        'messaging_product' => 'whatsapp',
                        'to' => $phoneNumber,
                        'type' => 'text',
                        'text' => [
                            'body' => $message,
                        ],
                    ]);
            }

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

    /**
     * Upload media file to WhatsApp Cloud API and return media ID.
     *
     * @return string|null Media ID or null if upload fails
     */
    private function uploadMediaToWhatsAppCloudApi(WhatsAppProvider $provider, string $filePath, ?string $fileName = null): ?string
    {
        try {
            $accessToken = $provider->access_token;
            $phoneNumberId = $provider->phone_number_id;

            if (empty($accessToken) || empty($phoneNumberId)) {
                return null;
            }

            $fileContent = Storage::disk('public')->get($filePath);
            $fileName = $fileName ?? basename($filePath);

            // Upload media to WhatsApp Cloud API
            $uploadUrl = sprintf('https://graph.facebook.com/v18.0/%s/media', $phoneNumberId);

            $response = Http::withToken($accessToken)
                ->attach('file', $fileContent, $fileName)
                ->post($uploadUrl, [
                    'messaging_product' => 'whatsapp',
                    'type' => 'document',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return $data['id'] ?? null;
            }

            Log::warning('Failed to upload media to WhatsApp Cloud API', [
                'response' => $response->body(),
            ]);

            return null;
        } catch (Exception $exception) {
            Log::error('Media upload to WhatsApp Cloud API failed', [
                'error' => $exception->getMessage(),
            ]);

            return null;
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
    private function sendViaTwilio(WhatsAppProvider $provider, string $phoneNumber, string $message, ?string $pdfPath = null, ?string $pdfFileName = null): array
    {
        try {
            $accountSid = $provider->account_sid;
            $authToken = $provider->auth_token; // Auto-decrypted via accessor
            $fromNumber = $provider->from_phone_number ?? $provider->whatsapp_sandbox_number;

            throw_if(empty($accountSid) || empty($authToken) || empty($fromNumber), Exception::class, 'Twilio credentials are missing');

            $url = sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', $accountSid);

            $payload = [
                'From' => 'whatsapp:' . $fromNumber,
                'To' => 'whatsapp:' . $phoneNumber,
                'Body' => $message,
            ];

            // If PDF is provided, add media URL
            if ($pdfPath && Storage::disk('public')->exists($pdfPath)) {
                // Twilio requires publicly accessible URL
                $pdfUrl = asset('storage/' . $pdfPath);
                $payload['MediaUrl'] = $pdfUrl;
            }

            $response = Http::withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->post($url, $payload);

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
    private function sendViaVonage(WhatsAppProvider $provider, string $phoneNumber, string $message, ?string $pdfPath = null, ?string $pdfFileName = null): array
    {
        try {
            $apiKey = $provider->api_key;
            $apiSecret = $provider->api_secret; // Auto-decrypted via accessor
            $fromNumber = $provider->from_number;

            throw_if(empty($apiKey) || empty($apiSecret) || empty($fromNumber), Exception::class, 'Vonage credentials are missing');

            $url = 'https://messages-sandbox.nexmo.com/v1/messages';

            $messagePayload = [
                'from' => [
                    'type' => 'whatsapp',
                    'number' => $fromNumber,
                ],
                'to' => [
                    'type' => 'whatsapp',
                    'number' => $phoneNumber,
                ],
            ];

            // If PDF is provided, send document message
            if ($pdfPath && Storage::disk('public')->exists($pdfPath)) {
                // Vonage requires publicly accessible URL
                $pdfUrl = asset('storage/' . $pdfPath);
                $fileName = $pdfFileName ?? basename($pdfPath);

                $messagePayload['message'] = [
                    'content' => [
                        'type' => 'document',
                        'document' => [
                            'url' => $pdfUrl,
                            'caption' => $message, // Optional caption
                        ],
                    ],
                ];
            } else {
                // Send text message only
                $messagePayload['message'] = [
                    'content' => [
                        'type' => 'text',
                        'text' => $message,
                    ],
                ];
            }

            $response = Http::withBasicAuth($apiKey, $apiSecret)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($url, $messagePayload);

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
