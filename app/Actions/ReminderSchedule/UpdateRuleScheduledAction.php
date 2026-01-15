<?php

declare(strict_types=1);

namespace App\Actions\ReminderSchedule;

use App\Models\BusinessProfile;
use App\Enums\ReminderStatusEnum;
use App\Models\ReminderSchedule;
use InvalidArgumentException;

final readonly class UpdateRuleScheduledAction
{
    public function __construct(
        private CreateRuleScheduledAction $createAction
    ) {}

    /**
     * Update rule-based reminder schedules by deleting old ones and creating new ones.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed> Updated schedules count and source type
     *
     * @throws InvalidArgumentException
     */
    public function execute(ReminderSchedule $schedule, array $validated, string $userId): array
    {
        // Verify schedule ownership
        $this->verifyScheduleOwnership($schedule, $userId);

        // Only allow updating pending schedules
        throw_if($schedule->status !== ReminderStatusEnum::PENDING, InvalidArgumentException::class, 'Only pending reminders can be updated.');

        $bulkGroupId = $schedule->bulk_group_id;

        // Delete existing schedules in the group (or just this one)
        if ($bulkGroupId) {
            ReminderSchedule::query()
                ->where('bulk_group_id', $bulkGroupId)
                ->where('status', ReminderStatusEnum::PENDING)
                ->delete();
        } else {
            $schedule->delete();
        }

        // Create new schedules using the create action
        return $this->createAction->execute($validated, $userId);
    }

    /**
     * Verify that the schedule belongs to the user's business.
     */
    private function verifyScheduleOwnership(ReminderSchedule $schedule, string $userId): void
    {
        $schedule->loadMissing('invoice.business.user');

        throw_if(! $schedule->invoice || ! $schedule->invoice->business, InvalidArgumentException::class, 'Schedule invoice or business not found.');

        /** @var BusinessProfile $business */
        $business = $schedule->invoice->business;
        throw_if($business->user_id !== $userId, InvalidArgumentException::class, 'You do not have permission to update this reminder schedule.');
    }
}
