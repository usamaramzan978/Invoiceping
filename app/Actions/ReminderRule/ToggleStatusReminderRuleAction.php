<?php

declare(strict_types=1);

namespace App\Actions\ReminderRule;

use App\Models\ReminderRule;

final class ToggleStatusReminderRuleAction
{
    public function execute(ReminderRule $rule): ReminderRule
    {
        $rule->update(['is_active' => ! $rule->is_active]);

        return $rule;
    }
}
