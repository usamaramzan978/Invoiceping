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
     * @param  string  $recipient  Recipient phone number (can be formatted or unformatted)
     * @param  string  $message  Message content
     * @param  string|null  $pdfPath  Optional PDF file path (relative to storage/public)
     * @param  string|null  $pdfFileName  Optional PDF filename for attachment
     * @return array{success: bool, message_id?: string, provider?: string, error?: string}
     */
    public function send(string $userId, string $recipient, string $message, ?string $pdfPath = null, ?string $pdfFileName = null): array
    {
        try {
            Log::info('WhatsAppService::send() called', [
                'user_id' => $userId,
                'recipient' => $this->maskPhone($recipient),
                'message_length' => mb_strlen($message),
                'has_pdf' => !in_array($pdfPath, [null, '', '0'], true),
            ]);

            // Get default provider for user
            $provider = $this->getDefaultProvider($userId);

            throw_unless($provider, Exception::class, 'No active WhatsApp provider found for user');

            // Validate phone number
            throw_if(in_array($recipient, ['', '0', '0'], true), Exception::class, 'Recipient phone number is empty');

            // Format phone number - keep in E.164 format with +
            $formattedPhone = $this->formatPhoneNumber($recipient);

            Log::info('Phone number formatted', [
                'original' => $this->maskPhone($recipient),
                'formatted' => $this->maskPhone($formattedPhone),
            ]);

            // Send based on provider type
            $result = match ($provider->type) {
                WhatsAppProviderType::WHATSAPP_CLOUD_API => $this->sendViaWhatsAppCloudApi($provider, $formattedPhone, $message, $pdfPath, $pdfFileName),
                WhatsAppProviderType::TWILIO => $this->sendViaTwilio($provider, $formattedPhone, $message, $pdfPath),
                WhatsAppProviderType::VONAGE => $this->sendViaVonage($provider, $formattedPhone, $message, $pdfPath, $pdfFileName),
            };

            Log::info('WhatsAppService::send() completed', [
                'success' => $result['success'],
                'provider' => $result['provider'] ?? 'unknown',
                'message_id' => $result['message_id'] ?? null,
            ]);

            return $result;
        } catch (Exception $exception) {
            Log::error('WhatsApp send failed', [
                'user_id' => $userId,
                'recipient' => $this->maskPhone($recipient),
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Verify WhatsApp Cloud API connection.
     */
    public function verifyConnectionWhatsAppCloudApi(WhatsAppProvider $provider): bool
    {
        $accessToken = $provider->access_token;
        $phoneNumberId = $provider->phone_number_id;

        throw_if(empty($accessToken) || empty($phoneNumberId), Exception::class, 'WhatsApp Cloud API credentials are missing');

        $url = sprintf('https://graph.facebook.com/v22.0/%s/messages', $phoneNumberId);

        Log::info('Verifying WhatsApp Cloud API connection', [
            'url' => $url,
            'phone_number_id' => $phoneNumberId,
        ]);

        $response = Http::withToken($accessToken)->get($url);

        if ($response->successful()) {
            Log::info('WhatsApp Cloud API connection verified');

            return true;
        }

        $errorMessage = 'WhatsApp Cloud API verify connection failed: '.($response->body() ?? 'Unknown error');
        Log::error($errorMessage, [
            'status' => $response->status(),
            'response' => $response->body(),
        ]);

        throw new Exception($errorMessage);
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

        if ($provider) {
            Log::info('Default WhatsApp provider found', [
                'provider_id' => $provider->id,
                'type' => $provider->type,
            ]);

            return $provider;
        }

        Log::info('No default provider, searching for any active provider');

        // If no default, get any active provider as fallback
        $provider = WhatsAppProvider::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->first();

        if ($provider) {
            Log::info('Active WhatsApp provider found (fallback)', [
                'provider_id' => $provider->id,
                'type' => $provider->type,
            ]);
        } else {
            Log::warning('No active WhatsApp provider found for user', [
                'user_id' => $userId,
            ]);
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
            $accessToken = $provider->access_token;
            $phoneNumberId = $provider->phone_number_id;

            // Validate credentials are decrypted properly
            throw_if(is_null($accessToken) || is_null($phoneNumberId), Exception::class, 'WhatsApp Cloud API credentials are null - decryption likely failed');

            throw_if(empty($accessToken) || empty($phoneNumberId), Exception::class, 'WhatsApp Cloud API credentials are empty');

            $url = sprintf('https://graph.facebook.com/v22.0/%s/messages', $phoneNumberId);

            Log::info('Sending via WhatsApp Cloud API', [
                'url' => $url,
                'phone' => $this->maskPhone($phoneNumber),
                'message_length' => mb_strlen($message),
                'has_pdf' => !in_array($pdfPath, [null, '', '0'], true),
                'provider_id' => $provider->id,
            ]);

            // If PDF is provided, send document message
            if ($pdfPath && Storage::disk('public')->exists($pdfPath)) {
                Log::info('Uploading PDF media to WhatsApp Cloud API', [
                    'pdf_path' => $pdfPath,
                    'file_size' => Storage::disk('public')->size($pdfPath),
                ]);

                // First, upload media to get media ID
                $mediaId = $this->uploadMediaToWhatsAppCloudApi($provider, $pdfPath, $pdfFileName);

                if ($mediaId) {
                    Log::info('Media uploaded successfully', [
                        'media_id' => $mediaId,
                    ]);

                    // Send document message with media ID
                    $payload = [
                        'messaging_product' => 'whatsapp',
                        'to' => $phoneNumber,
                        'type' => 'document',
                        'document' => [
                            'id' => $mediaId,
                            'caption' => $message,
                        ],
                    ];

                    Log::info('Sending document message', [
                        'payload' => $payload,
                    ]);

                    $response = Http::withToken($accessToken)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post($url, $payload);
                } else {
                    Log::warning('Media upload failed, falling back to text message');

                    // Fallback to text message if media upload fails
                    $payload = [
                        'messaging_product' => 'whatsapp',
                        'to' => $phoneNumber,
                        'type' => 'text',
                        'text' => ['body' => $message],
                    ];

                    $response = Http::withToken($accessToken)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post($url, $payload);
                }
            } else {
                // Send text message only
                $payload = [
                    'messaging_product' => 'whatsapp',
                    'to' => $phoneNumber,
                    'type' => 'text',
                    'text' => ['body' => $message],
                ];

                Log::info('Sending text message', [
                    'phone' => $this->maskPhone($phoneNumber),
                    'message_length' => mb_strlen($message),
                    'url' => $url,
                ]);

                $response = Http::withToken($accessToken)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, $payload);
            }

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Full WhatsApp response', [
                    'status' => $response->status(),
                    'headers' => $response->headers(),
                    'body' => $response->json(),
                ]);

                // Update last used timestamp
                $provider->update(['last_used_at' => now()]);

                return [
                    'success' => true,
                    'message_id' => $data['messages'][0]['id'] ?? null,
                    'provider' => 'whatsapp_cloud_api',
                ];
            }

            // Log full error response
            $responseBody = $response->body();
            $responseJson = $response->json();

            Log::error('WhatsApp Cloud API request failed', [
                'provider_id' => $provider->id,
                'phone' => $this->maskPhone($phoneNumber),
                'status' => $response->status(),
                'response_body' => $responseBody,
                'response_json' => $responseJson,
                'url' => $url,
            ]);

            throw new Exception('WhatsApp Cloud API error: '.$responseBody);
        } catch (Exception $exception) {
            Log::error('WhatsApp Cloud API send failed', [
                'provider_id' => $provider->id,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
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
                Log::warning('WhatsApp credentials missing for media upload');

                return null;
            }

            if (! Storage::disk('public')->exists($filePath)) {
                Log::warning('File not found for media upload', [
                    'file_path' => $filePath,
                ]);

                return null;
            }

            $fileContent = Storage::disk('public')->get($filePath);
            $fileName ??= basename($filePath);

            $uploadUrl = sprintf('https://graph.facebook.com/v22.0/%s/media', $phoneNumberId);

            Log::info('Uploading media to WhatsApp', [
                'url' => $uploadUrl,
                'filename' => $fileName,
                'file_size' => mb_strlen((string) $fileContent),
            ]);

            $response = Http::withToken($accessToken)
                ->attach('file', $fileContent, $fileName)
                ->post($uploadUrl, [
                    'messaging_product' => 'whatsapp',
                    'type' => 'document',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $mediaId = $data['id'] ?? null;

                Log::info('Media uploaded successfully', [
                    'media_id' => $mediaId,
                ]);

                return $mediaId;
            }

            Log::warning('Failed to upload media to WhatsApp Cloud API', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (Exception $exception) {
            Log::error('Media upload to WhatsApp Cloud API failed', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Send message via Twilio.
     *
     * @return array{success: bool, message_id?: string, provider: string, error?: string}
     */
    private function sendViaTwilio(WhatsAppProvider $provider, string $phoneNumber, string $message, ?string $pdfPath = null): array
    {
        try {
            $accountSid = $provider->account_sid;
            $authToken = $provider->auth_token;
            $fromNumber = $provider->from_phone_number ?? $provider->whatsapp_sandbox_number;

            throw_if(is_null($authToken), Exception::class, 'Twilio auth token is null - decryption likely failed');

            throw_if(empty($accountSid) || empty($authToken) || empty($fromNumber), Exception::class, 'Twilio credentials are missing');

            $url = sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', $accountSid);

            $payload = [
                'From' => 'whatsapp:'.$fromNumber,
                'To' => 'whatsapp:'.$phoneNumber,
                'Body' => $message,
            ];

            // If PDF is provided, add media URL
            if ($pdfPath && Storage::disk('public')->exists($pdfPath)) {
                $pdfUrl = asset('storage/'.$pdfPath);
                $payload['MediaUrl'] = $pdfUrl;

                Log::info('Adding media URL to Twilio message', [
                    'media_url' => $pdfUrl,
                ]);
            }

            Log::info('Sending via Twilio', [
                'phone' => $this->maskPhone($phoneNumber),
                'message_length' => mb_strlen($message),
            ]);

            $response = Http::withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Twilio send successful', [
                    'message_id' => $data['sid'] ?? null,
                ]);

                // Update last used timestamp
                $provider->update(['last_used_at' => now()]);

                return [
                    'success' => true,
                    'message_id' => $data['sid'] ?? null,
                    'provider' => 'twilio',
                ];
            }

            throw new Exception('Twilio API error: '.$response->body());
        } catch (Exception $exception) {
            Log::error('Twilio send failed', [
                'provider_id' => $provider->id,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
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
            $apiSecret = $provider->api_secret;
            $fromNumber = $provider->from_number;

            throw_if(is_null($apiSecret), Exception::class, 'Vonage API secret is null - decryption likely failed');

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
                $pdfUrl = asset('storage/'.$pdfPath);
                $fileName = $pdfFileName ?? basename($pdfPath);

                $messagePayload['message'] = [
                    'content' => [
                        'type' => 'document',
                        'document' => [
                            'url' => $pdfUrl,
                            'caption' => $message,
                        ],
                    ],
                ];

                Log::info('Sending document via Vonage', [
                    'file' => $fileName,
                    'url' => $pdfUrl,
                ]);
            } else {
                $messagePayload['message'] = [
                    'content' => [
                        'type' => 'text',
                        'text' => $message,
                    ],
                ];
            }

            Log::info('Sending via Vonage', [
                'phone' => $this->maskPhone($phoneNumber),
                'message_length' => mb_strlen($message),
            ]);

            $response = Http::withBasicAuth($apiKey, $apiSecret)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($url, $messagePayload);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Vonage send successful', [
                    'message_uuid' => $data['message_uuid'] ?? null,
                ]);

                // Update last used timestamp
                $provider->update(['last_used_at' => now()]);

                return [
                    'success' => true,
                    'message_id' => $data['message_uuid'] ?? null,
                    'provider' => 'vonage',
                ];
            }

            throw new Exception('Vonage API error: '.$response->body());
        } catch (Exception $exception) {
            Log::error('Vonage send failed', [
                'provider_id' => $provider->id,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'provider' => 'vonage',
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Format phone number to digits only (WhatsApp API format).
     * Removes all non-digit characters and adds country code if needed.
     *
     * @param  string  $phone  Phone number in various formats (with or without +, dashes, spaces)
     * @return string Formatted phone number as digits only (e.g., 923015551234)
     *
     * @throws Exception if phone number is invalid
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove any whitespace and non-digit characters (except +, which we'll remove later)
        $phone = mb_trim($phone);

        throw_if($phone === '' || $phone === '0', Exception::class, 'Phone number cannot be empty');

        // Remove all non-digit characters (this also removes the + if present)
        $digits = preg_replace('/\D/', '', $phone);

        throw_if(empty($digits), Exception::class, 'Invalid phone number: no digits found after cleaning');

        // If it's already 11-15 digits, assume it's a full international number
        if (mb_strlen($digits) >= 11 && mb_strlen($digits) <= 15) {
            return $digits;
        }

        // If it starts with 92 (Pakistan country code), it's already formatted
        if (str_starts_with($digits, '92')) {
            return $digits;
        }

        // If it starts with 0 (Pakistan domestic format), remove it and add country code
        if (str_starts_with($digits, '0')) {
            $digits = mb_substr($digits, 1);

            return '92'.$digits;
        }

        // If shorter than 11 digits, assume missing country code, add 92 (Pakistan)
        if (mb_strlen($digits) < 11) {
            return '92'.$digits;
        }

        // Default: return digits as is
        return $digits;
    }

    /**
     * Mask phone number for safe logging.
     *
     * @param  string  $phone  Phone number
     * @return string Masked phone number (e.g., +923***1234)
     */
    private function maskPhone(string $phone): string
    {
        if (mb_strlen($phone) >= 4) {
            return mb_substr($phone, 0, 4).'***'.mb_substr($phone, -4);
        }

        return '***';
    }
}
