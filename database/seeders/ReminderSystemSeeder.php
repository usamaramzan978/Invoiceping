<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\MessageChannel;
use App\Models\Invoice;
use App\Models\MessageTemplates;
use App\Models\ReminderRule;
use App\Models\ReminderRuleStep;
use App\Models\ReminderRuleStepTemplate;
use App\Models\ReminderSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

final class ReminderSystemSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first();
        if (! $user) {
            return;
        }

        $emailTemplate = MessageTemplates::query()->where('channel', MessageChannel::EMAIL)->first();
        $whatsappTemplate = MessageTemplates::query()->where('channel', MessageChannel::WHATSAPP)->first();

        if (! $emailTemplate || ! $whatsappTemplate) {
            return;
        }

        // -------------------------------
        // Reminder Rule
        // -------------------------------
        $rule = ReminderRule::query()->firstOrCreate([
            'user_id' => $user->id,
            'name' => 'Default 3-Step Reminder',
        ], [
            'id' => Str::uuid(),
            'is_default' => true,
            'is_active' => true,
        ]);

        // -------------------------------
        // Reminder Rule Steps (DB MODELS)
        // -------------------------------
        $stepsConfig = [
            ['type' => 'before_due', 'offset' => -2, 'order' => 1],
            ['type' => 'on_due',     'offset' => 0,  'order' => 2],
            ['type' => 'after_due',  'offset' => 3,  'order' => 3],
        ];

        foreach ($stepsConfig as $cfg) {
            $step = ReminderRuleStep::query()->firstOrCreate([
                'reminder_rule_id' => $rule->id,
                'reminder_type' => $cfg['type'],
            ], [
                'id' => Str::uuid(),
                'offset_days' => $cfg['offset'],
                'sort_order' => $cfg['order'],
            ]);

            ReminderRuleStepTemplate::query()->firstOrCreate([
                'reminder_rule_step_id' => $step->id,
                'channel' => 'email',
            ], [
                'id' => Str::uuid(),
                'message_template_id' => $emailTemplate->id,
            ]);

            ReminderRuleStepTemplate::query()->firstOrCreate([
                'reminder_rule_step_id' => $step->id,
                'channel' => 'whatsapp',
            ], [
                'id' => Str::uuid(),
                'message_template_id' => $whatsappTemplate->id,
            ]);
        }

        // --------------------------------------------------
        // Reminder Schedules (EXECUTION RECORDS)
        // --------------------------------------------------
        $invoices = Invoice::query()->whereNotIn('status', ['paid', 'cancelled'])
            ->whereNotNull('due_date')
            ->take(3)
            ->get();

        $ruleSteps = ReminderRuleStep::query()->where('reminder_rule_id', $rule->id)->get();

        foreach ($invoices as $invoice) {
            foreach ($ruleSteps as $step) {
                $scheduledAt = Date::parse($invoice->due_date)
                    ->addDays($step->offset_days);

                foreach (['email', 'whatsapp'] as $channel) {
                    $templateId = $channel === 'email'
                        ? $emailTemplate->id
                        : $whatsappTemplate->id;

                    ReminderSchedule::query()->firstOrCreate([
                        'invoice_id' => $invoice->id,
                        'reminder_rule_step_id' => $step->id,
                        'channel' => $channel,
                    ], [
                        'id' => Str::uuid(),
                        'reminder_rule_id' => $rule->id,
                        'message_template_id' => $templateId,
                        'scheduled_at' => $scheduledAt,
                        'status' => 'pending',
                    ]);
                }
            }
        }
    }
}
