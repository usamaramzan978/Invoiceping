<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WhatsAppProviderType;
use App\Services\SecureTokenService;
use Exception;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

final class WhatsAppProvider extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'whatsapp_providers';

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'last_used_at',
        // WhatsApp Cloud API credentials
        'access_token',
        'phone_number_id',
        'business_account_id',
        'app_id',
        'app_secret',
        // Twilio credentials
        'account_sid',
        'auth_token',
        'from_phone_number',
        'whatsapp_sandbox_number',
        // Vonage credentials
        'api_key',
        'api_secret',
        'from_number',
        'application_id',
        'is_active',
        'is_default',
        'notes',
    ];

    protected $casts = [
        'type' => WhatsAppProviderType::class,
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'access_token',
        'app_secret',
        'auth_token',
        'api_secret',
    ];

    /**
     * Get provider type options.
     *
     * @return array<string, string>
     */
    public static function getTypeOptions(): array
    {
        return WhatsAppProviderType::options();
    }

    /**
     * Get required credentials for a provider type.
     *
     * @return array<string, array{label: string, required: bool, type: string}>
     */
    public static function getRequiredCredentials(WhatsAppProviderType|string $type): array
    {
        // Convert string to enum if needed
        if (is_string($type)) {
            $type = WhatsAppProviderType::tryFrom($type) ?? WhatsAppProviderType::WHATSAPP_CLOUD_API;
        }

        return match ($type) {
            WhatsAppProviderType::WHATSAPP_CLOUD_API => [
                'access_token' => ['label' => 'Access Token', 'required' => true, 'type' => 'text'],
                'phone_number_id' => ['label' => 'Phone Number ID', 'required' => true, 'type' => 'text'],
                'business_account_id' => ['label' => 'Business Account ID', 'required' => true, 'type' => 'text'],
                'app_id' => ['label' => 'App ID', 'required' => false, 'type' => 'text'],
                'app_secret' => ['label' => 'App Secret', 'required' => false, 'type' => 'text'],
            ],
            WhatsAppProviderType::TWILIO => [
                'account_sid' => ['label' => 'Account SID', 'required' => true, 'type' => 'text'],
                'auth_token' => ['label' => 'Auth Token', 'required' => true, 'type' => 'password'],
                'from_phone_number' => ['label' => 'From Phone Number', 'required' => true, 'type' => 'text'],
                'whatsapp_sandbox_number' => ['label' => 'WhatsApp Sandbox Number', 'required' => false, 'type' => 'text'],
            ],
            WhatsAppProviderType::VONAGE => [
                'api_key' => ['label' => 'API Key', 'required' => true, 'type' => 'text'],
                'api_secret' => ['label' => 'API Secret', 'required' => true, 'type' => 'password'],
                'from_number' => ['label' => 'From Number', 'required' => true, 'type' => 'text'],
                'application_id' => ['label' => 'Application ID', 'required' => false, 'type' => 'text'],
            ],
        };
    }

    /**
     * Get the user that owns the provider.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor for access_token - automatically decrypts
     */
    protected function getAccessTokenAttribute(?string $value): ?string
    {
        return $this->decryptValue($value);
    }

    /**
     * Mutator for access_token - automatically encrypts
     */
    protected function setAccessTokenAttribute(?string $value): void
    {
        $this->attributes['access_token'] = $this->encryptValue($value);
    }

    /**
     * Accessor for app_secret - automatically decrypts
     */
    protected function getAppSecretAttribute(?string $value): ?string
    {
        return $this->decryptValue($value);
    }

    /**
     * Mutator for app_secret - automatically encrypts
     */
    protected function setAppSecretAttribute(?string $value): void
    {
        $this->attributes['app_secret'] = $this->encryptValue($value);
    }

    /**
     * Accessor for auth_token - automatically decrypts
     */
    protected function getAuthTokenAttribute(?string $value): ?string
    {
        return $this->decryptValue($value);
    }

    /**
     * Mutator for auth_token - automatically encrypts
     */
    protected function setAuthTokenAttribute(?string $value): void
    {
        $this->attributes['auth_token'] = $this->encryptValue($value);
    }

    /**
     * Accessor for api_secret - automatically decrypts
     */
    protected function getApiSecretAttribute(?string $value): ?string
    {
        return $this->decryptValue($value);
    }

    /**
     * Mutator for api_secret - automatically encrypts
     */
    protected function setApiSecretAttribute(?string $value): void
    {
        $this->attributes['api_secret'] = $this->encryptValue($value);
    }

    /**
     * Encrypt a value using SecureTokenService
     */
    private function encryptValue(?string $value): ?string
    {
        if (in_array($value, [null, '', '0'], true)) {
            return null;
        }

        // Check if value is already encrypted (encrypted tokens are long base64 strings)
        // Encrypted values are typically 100+ characters
        if (mb_strlen($value) > 100 && preg_match('/^[A-Za-z0-9+\/]+={0,2}$/', $value)) {
            // Likely already encrypted, return as is
            return $value;
        }

        try {
            $service = new SecureTokenService();

            // Get user - try relation first, then query if needed
            $user = $this->relationLoaded('user') ? $this->user : null;
            if (! $user && $this->user_id) {
                $user = User::query()->find($this->user_id);
            }

            if (! $user) {
                throw new Exception('User not found for encryption. User ID: '.($this->user_id ?? 'null'));
            }

            /** @var \App\Models\User $user */
            $user = $user;
            $encrypted = $service->encryptToken(
                $value,
                (string) $user->id, // User ID (UUID)
                $user->email,
                (string) $user->id  // User UUID (same as ID since using HasUuids)
            );

            return $encrypted['encrypted_token'];
        } catch (Exception $exception) {
            Log::error('Failed to encrypt value: '.$exception->getMessage(), [
                'user_id' => $this->user_id,
                'field' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'unknown',
            ]);
            throw $exception;
        }
    }

    /**
     * Decrypt a value using SecureTokenService
     */
    private function decryptValue(?string $encryptedValue): ?string
    {
        if (in_array($encryptedValue, [null, '', '0'], true)) {
            return null;
        }

        // If value is too short, it's probably not encrypted
        if (mb_strlen($encryptedValue) < 50) {
            return $encryptedValue;
        }

        try {
            $service = new SecureTokenService();

            // Get user - try relation first, then query if needed
            $user = $this->relationLoaded('user') ? $this->user : null;
            if (! $user && $this->user_id) {
                $user = User::query()->find($this->user_id);
            }

            if (! $user) {
                Log::warning('User not found for decryption', [
                    'user_id' => $this->user_id,
                ]);

                return null;
            }

            /** @var \App\Models\User $user */
            $user = $user;
            $decrypted = $service->decryptToken(
                $encryptedValue,
                (string) $user->id, // User ID (UUID)
                $user->email,
                (string) $user->id  // User UUID (same as ID since using HasUuids)
            );

            return $decrypted['token'];
        } catch (Exception $exception) {
            Log::error('Failed to decrypt value: '.$exception->getMessage(), [
                'user_id' => $this->user_id,
            ]);

            // Return null instead of throwing to prevent breaking the app
            return null;
        }
    }
}
