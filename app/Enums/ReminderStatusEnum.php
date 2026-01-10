<?php

declare(strict_types=1);

namespace App\Enums;

enum ReminderStatusEnum: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';
    case SKIPPED = 'skipped';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SENT => 'Sent',
            self::FAILED => 'Failed',
            self::SKIPPED => 'Skipped',
            self::CANCELLED => 'Cancelled',
        };
    }
}
