<?php

declare(strict_types=1);

namespace App\Actions\ReminderRule;

use App\Models\ReminderRule;
use App\Models\ReminderRuleStep;
use App\Models\ReminderRuleStepTemplate;
use Illuminate\Support\Facades\DB;

final class UpdateReminderRuleAction
{
    public function execute(ReminderRule $rule, array $data): ReminderRule
    {
        return DB::transaction(function () use ($rule, $data): ReminderRule {
            // If setting as default, unset other defaults
            if ($data['is_default'] ?? false) {
                ReminderRule::query()
                    ->where('user_id', $rule->user_id)
                    ->where('id', '!=', $rule->id)
                    ->update(['is_default' => false]);
            }

            // Update the rule
            $rule->update([
                'name' => $data['name'],
                'is_default' => $data['is_default'] ?? false,
            ]);

            // Delete existing steps and templates
            $rule->steps()->each(function ($step): void {
                $step->templates()->delete();
                $step->delete();
            });

            // Create new steps and templates
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
                    if ($channelData['channel'] === 'email' && !empty($channelData['email_template_id'])) {
                        $templateData['email_template_id'] = $channelData['email_template_id'];
                        ReminderRuleStepTemplate::query()->create($templateData);
                    }
                    // Handle WhatsApp/SMS templates
                    elseif (in_array($channelData['channel'], ['whatsapp', 'sms']) && !empty($channelData['message_template_id'])) {
                        $templateData['message_template_id'] = $channelData['message_template_id'];
                        ReminderRuleStepTemplate::query()->create($templateData);
                    }
                }
            }

            return $rule->fresh();
        });
    }
}

