<?php

declare(strict_types=1);

namespace App\Enums;

enum MessageChannel: string
{
    case WHATSAPP = 'whatsapp';
    case EMAIL = 'email';
    case SMS = 'sms';

    public function label(): string
    {
        return match ($this) {
            self::WHATSAPP => 'WhatsApp',
            self::EMAIL => 'Email',
            self::SMS => 'SMS',
        };
    }
}
