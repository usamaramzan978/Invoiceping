<?php

declare(strict_types=1);

namespace App\Enums;

enum BillingCycle: string
{
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::MONTHLY => 'Monthly',
            self::YEARLY => 'Yearly',
        };
    }

    public function monthsCount(): int
    {
        return match ($this) {
            self::MONTHLY => 1,
            self::YEARLY => 12,
        };
    }
}
