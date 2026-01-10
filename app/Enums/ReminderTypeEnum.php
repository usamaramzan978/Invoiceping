<?php

declare(strict_types=1);

namespace App\Enums;

enum ReminderTypeEnum: string
{
    case BEFORE_DUE = 'before_due';
    case ON_DUE = 'on_due';
    case AFTER_DUE = 'after_due';

    public function label(): string
    {
        return match ($this) {
            self::BEFORE_DUE => 'Before Due',
            self::ON_DUE => 'On Due',
            self::AFTER_DUE => 'After Due',
        };
    }
}
