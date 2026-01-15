<?php

declare(strict_types=1);

namespace App\Actions\ReminderSchedule;

use App\Enums\ReminderSourceTypeEnum;
use App\Enums\ReminderStatusEnum;
use App\Models\Invoice;
use App\Models\ReminderRule;
use App\Models\ReminderSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class CreateRuleScheduledAction
{
    /**
     * Create reminder schedules from a rule for invoices.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed> Created schedules count
     *
     * @throws InvalidArgumentException
     */
    public function execute(array $validated, string $userId): array
    {
        $invoiceIds = $validated['invoice_ids'];
        $ruleId = $validated['reminder_rule_id'];
        $scheduledAt = $validated['scheduled_at'];
        $businessId = $this->getUserBusinessId($userId);
        $bulkGroupId = count($invoiceIds) > 1 ? Str::uuid()->toString() : null;
        $createdCount = 0;

        // Verify all invoices belong to user's business
        $this->verifyInvoiceOwnership($invoiceIds, $businessId);

        // Verify rule ownership
        $this->verifyRuleOwnership($ruleId, $userId);

        foreach ($invoiceIds as $invoiceId) {
            $invoice = Invoice::query()->findOrFail($invoiceId);
            $createdCount += $this->createRuleSchedules(
                $invoice,
                $ruleId,
                $scheduledAt,
                $bulkGroupId,
                $userId
            );
        }

        return [
            'created_count' => $createdCount,
            'source_type' => 'rule',
        ];
    }

    /**
     * Create schedules from a reminder rule.
     */
    private function createRuleSchedules(
        Invoice $invoice,
        string $ruleId,
        string $scheduledAt,
        ?string $bulkGroupId,
        string $userId
    ): int {
        /** @var ReminderRule $rule */
        $rule = ReminderRule::query()
            ->where('user_id', $userId)
            ->with(['steps.templates.emailTemplate', 'steps.templates.messageTemplate'])
            ->findOrFail($ruleId);

        $referenceDate = Date::parse($scheduledAt);
        $createdCount = 0;

        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\ReminderRuleStep> $steps */
        $steps = $rule->steps;
        foreach ($steps as $step) {
            $scheduledAtForStep = $this->calculateScheduledAt($referenceDate, $step);

            foreach ($step->templates as $template) {
                ReminderSchedule::query()->create([
                    'invoice_id' => $invoice->id,
                    'source_type' => ReminderSourceTypeEnum::RULE,
                    'channel' => $template->channel,
                    'email_template_id' => $template->isEmailChannel() ? $template->email_template_id : null,
                    'message_template_id' => in_array($template->channel, ['whatsapp', 'sms']) ? $template->message_template_id : null,
                    'reminder_rule_id' => $rule->id,
                    'reminder_rule_step_id' => $step->id,
                    'bulk_group_id' => $bulkGroupId,
                    'scheduled_at' => $scheduledAtForStep,
                    'status' => ReminderStatusEnum::PENDING,
                ]);

                $createdCount++;
            }
        }

        return $createdCount;
    }

    /**
     * Calculate scheduled_at based on reference date and step offset.
     */
    private function calculateScheduledAt(Carbon $referenceDate, $step): Carbon
    {
        $date = clone $referenceDate;
        $offset = (int) $step->offset_days;

        if ($step->reminder_type === 'before_due') {
            $date->subDays($offset);
        } elseif ($step->reminder_type === 'after_due') {
            $date->addDays($offset);
        }

        // Set default time (09:00 AM)
        $date->setTime(9, 0, 0);

        return $date;
    }

    /**
     * Get user's business ID.
     */
    private function getUserBusinessId(string $userId): ?string
    {
        $user = User::query()->with('business')->findOrFail($userId);

        throw_unless($user->business, InvalidArgumentException::class, 'Business profile not found. Please create a business profile first.');

        /** @var \App\Models\BusinessProfile $business */
        $business = $user->business;
        return (string) $business->id;
    }

    /**
     * Verify that all invoices belong to the user's business.
     */
    private function verifyInvoiceOwnership(array $invoiceIds, ?string $businessId): void
    {
        throw_unless($businessId, InvalidArgumentException::class, 'Business profile not found.');

        $count = Invoice::query()
            ->whereIn('id', $invoiceIds)
            ->where('business_id', $businessId)
            ->count();

        throw_if($count !== count($invoiceIds), InvalidArgumentException::class, 'One or more invoices do not belong to your business.');
    }

    /**
     * Verify that the reminder rule belongs to the user.
     */
    private function verifyRuleOwnership(string $ruleId, string $userId): void
    {
        $exists = ReminderRule::query()
            ->where('id', $ruleId)
            ->where('user_id', $userId)
            ->exists();

        throw_unless($exists, InvalidArgumentException::class, 'The selected reminder rule does not belong to you.');
    }
}
