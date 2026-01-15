<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BusinessProfile;
use App\Models\ReminderSchedule;
use App\Models\User;

final class ReminderSchedulePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(): bool
    {
        return true;
        // Authenticated users can view their own schedules
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ReminderSchedule $reminderSchedule): bool
    {
        $reminderSchedule->loadMissing('invoice.business');

        if (! $reminderSchedule->invoice || ! $reminderSchedule->invoice->business) {
            return false;
        }

        /** @var BusinessProfile $business */
        $business = $reminderSchedule->invoice->business;

        return $business->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(): bool
    {
        return true;
        // Authenticated users can create schedules
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ReminderSchedule $reminderSchedule): bool
    {
        $reminderSchedule->loadMissing('invoice.business');

        if (! $reminderSchedule->invoice || ! $reminderSchedule->invoice->business) {
            return false;
        }

        /** @var BusinessProfile $business */
        $business = $reminderSchedule->invoice->business;

        return $business->user_id === $user->id;
    }

    /**
     * Determine whether the user can cancel the reminder schedule.
     */
    public function cancel(User $user, ReminderSchedule $reminderSchedule): bool
    {
        $reminderSchedule->loadMissing('invoice.business');

        if (! $reminderSchedule->invoice || ! $reminderSchedule->invoice->business) {
            return false;
        }

        /** @var BusinessProfile $business */
        $business = $reminderSchedule->invoice->business;

        return $business->user_id === $user->id;
    }

    /**
     * Determine whether the user can reschedule the reminder.
     */
    public function reschedule(User $user, ReminderSchedule $reminderSchedule): bool
    {
        $reminderSchedule->loadMissing('invoice.business');

        if (! $reminderSchedule->invoice || ! $reminderSchedule->invoice->business) {
            return false;
        }

        /** @var BusinessProfile $business */
        $business = $reminderSchedule->invoice->business;

        return $business->user_id === $user->id;
    }
}
