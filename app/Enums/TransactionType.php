<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionType: string
{
    case PAYMENT = 'payment';
    case REFUND = 'refund';
    case ADJUSTMENT = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::PAYMENT => 'Payment',
            self::REFUND => 'Refund',
            self::ADJUSTMENT => 'Adjustment',
        };
    }
}
