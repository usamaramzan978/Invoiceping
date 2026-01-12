<?php

declare(strict_types=1);

namespace App\Enums;

enum WhatsAppProviderType: string
{
    case WHATSAPP_CLOUD_API = 'whatsapp_cloud_api';
    case TWILIO = 'twilio';
    case VONAGE = 'vonage';

    /**
     * Get all enum values as array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all provider types with labels.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $carry, self $case): array => $carry + [$case->value => $case->label()],
            []
        );
    }

    /**
     * Get human-readable label for the provider type.
     */
    public function label(): string
    {
        return match ($this) {
            self::WHATSAPP_CLOUD_API => 'WhatsApp Cloud API',
            self::TWILIO => 'Twilio',
            self::VONAGE => 'Vonage',
        };
    }
}
