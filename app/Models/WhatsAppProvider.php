<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WhatsAppProviderType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WhatsAppProvider extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_providers';

    protected $fillable = [
        'user_id',
        'name',
        'type',
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
     * Get the user that owns the provider.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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
}
