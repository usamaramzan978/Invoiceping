<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case TRIALING = 'trialing';
    case CANCELED = 'canceled';
    case EXPIRED = 'expired';
    case PAST_DUE = 'past_due';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::TRIALING => 'Trial',
            self::CANCELED => 'Canceled',
            self::EXPIRED => 'Expired',
            self::PAST_DUE => 'Past Due',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::TRIALING => 'info',
            self::CANCELED => 'warning',
            self::EXPIRED => 'danger',
            self::PAST_DUE => 'danger',
        };
    }

    public function isActive(): bool
    {
        return match ($this) {
            self::ACTIVE, self::TRIALING => true,
            default => false,
        };
    }
}
