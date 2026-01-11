<?php

declare(strict_types=1);

namespace App\Actions\ReminderSchedule;

use App\Enums\ReminderStatusEnum;
use App\Models\ReminderSchedule;
use InvalidArgumentException;

final readonly class CancelReminderScheduleAction
{
    /**
     * Cancel reminder schedule(s).
     *
     * @return int Number of cancelled schedules
     *
     * @throws InvalidArgumentException
     */
    public function execute(ReminderSchedule $schedule, string $userId): int
    {
        // Verify schedule ownership
        $this->verifyScheduleOwnership($schedule, $userId);

        // Only allow cancelling pending schedules
        throw_if($schedule->status !== ReminderStatusEnum::PENDING, InvalidArgumentException::class, 'Only pending reminders can be cancelled.');

        // Cancel related schedules if bulk group exists
        if ($schedule->bulk_group_id) {
            $relatedSchedules = $this->getRelatedSchedules($schedule);
            $relatedSchedules->each(fn ($s) => $s->update(['status' => ReminderStatusEnum::CANCELLED]));

            return $relatedSchedules->count();
        }

        $schedule->update(['status' => ReminderStatusEnum::CANCELLED]);

        return 1;
    }

    /**
     * Get related schedules in the same bulk group with matching criteria.
     */
    private function getRelatedSchedules(ReminderSchedule $schedule)
    {
        $query = ReminderSchedule::query()
            ->where('bulk_group_id', $schedule->bulk_group_id)
            ->where('reminder_rule_step_id', $schedule->reminder_rule_step_id)
            ->where('channel', $schedule->channel)
            ->where('status', ReminderStatusEnum::PENDING);

        // Match by template ID based on channel
        if ($schedule->isEmailChannel() && $schedule->email_template_id) {
            $query->where('email_template_id', $schedule->email_template_id);
        } elseif ($schedule->message_template_id) {
            $query->where('message_template_id', $schedule->message_template_id);
        }

        return $query->get();
    }

    /**
     * Verify that the schedule belongs to the user's business.
     */
    private function verifyScheduleOwnership(ReminderSchedule $schedule, string $userId): void
    {
        $schedule->loadMissing('invoice.business.user');

        throw_if(! $schedule->invoice || ! $schedule->invoice->business, InvalidArgumentException::class, 'Schedule invoice or business not found.');

        throw_if($schedule->invoice->business->user_id !== $userId, InvalidArgumentException::class, 'You do not have permission to cancel this reminder schedule.');
    }
}
