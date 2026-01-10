<?php

declare(strict_types=1);

namespace App\Enums;

enum DiscountType: string
{
    case FIXED = 'fixed';
    case PERCENTAGE = 'percent';

    public function label(): string
    {
        return match ($this) {
            self::FIXED => 'Fixed',
            self::PERCENTAGE => 'percent',
        };
    }
}
