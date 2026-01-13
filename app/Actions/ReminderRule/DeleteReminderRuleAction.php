<?php

declare(strict_types=1);

namespace App\Actions\ReminderRule;

use App\Models\ReminderRule;

final class DeleteReminderRuleAction
{
    public function execute(ReminderRule $rule): bool
    {
        return $rule->delete();
    }
}

