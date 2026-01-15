<?php

declare(strict_types=1);

namespace App\Actions\ReminderRule;

use App\Models\ReminderRule;
use App\Models\ReminderRuleStep;
use App\Models\ReminderRuleStepTemplate;
use Illuminate\Support\Facades\DB;

final class CreateReminderRuleAction
{
    public function execute(string $userId, array $data): ReminderRule
    {
        return DB::transaction(function () use ($userId, $data): ReminderRule {
            // If setting as default, unset other defaults
            if ($data['is_default'] ?? false) {
                ReminderRule::query()
                    ->where('user_id', $userId)
                    ->update(['is_default' => false]);
            }

            // Create the rule
            $rule = ReminderRule::query()->create([
                'user_id' => $userId,
                'name' => $data['name'],
                'is_default' => $data['is_default'] ?? false,
                'is_active' => true,
            ]);

            // Create steps and templates
            foreach ($data['steps'] as $index => $stepData) {
                $step = ReminderRuleStep::query()->create([
                    'reminder_rule_id' => $rule->id,
                    'reminder_type' => $stepData['reminder_type'],
                    'offset_days' => $stepData['offset_days'],
                    'sort_order' => $index,
                ]);

                foreach ($stepData['channels'] as $channelData) {
                    $templateData = [
                        'reminder_rule_step_id' => $step->id,
                        'channel' => $channelData['channel'],
                    ];

                    // Handle email templates
                    if ($channelData['channel'] === 'email' && ! empty($channelData['email_template_id'])) {
                        $templateData['email_template_id'] = $channelData['email_template_id'];
                        $templateData['include_pdf'] = (bool) ($channelData['include_pdf'] ?? false);
                        ReminderRuleStepTemplate::query()->create($templateData);
                    }
                    // Handle WhatsApp/SMS templates
                    elseif (in_array($channelData['channel'], ['whatsapp', 'sms']) && ! empty($channelData['message_template_id'])) {
                        $templateData['message_template_id'] = $channelData['message_template_id'];
                        ReminderRuleStepTemplate::query()->create($templateData);
                    }
                }
            }

            return $rule;
        });
    }
}
