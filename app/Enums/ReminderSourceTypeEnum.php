<?php

declare(strict_types=1);

namespace App\Enums;

enum ReminderSourceTypeEnum: string
{
    case MANUAL = 'manual';
    case RULE = 'rule';

    public function label(): string
    {
        return match ($this) {
            self::MANUAL => 'Manual',
            self::RULE => 'Rule',
        };
    }
}
