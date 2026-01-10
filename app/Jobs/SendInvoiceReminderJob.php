<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ReminderSchedules;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SendInvoiceReminderJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 30;

    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $reminderScheduleId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ReminderSchedules::with([
            'invoice.client',
            'invoice.business',
        ])->findOrFail($this->reminderScheduleId);
    }
}
