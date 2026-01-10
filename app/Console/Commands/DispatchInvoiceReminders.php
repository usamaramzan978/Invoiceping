<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SendInvoiceReminderJob;
use App\Models\ReminderSchedules;
use Illuminate\Console\Command;

final class DispatchInvoiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:dispatch-invoice-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch pending invoice reminders';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        ReminderSchedules::query()
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->with('invoice')
            ->chunkById(50, function ($reminders): void {
                foreach ($reminders as $reminder) {
                    dispatch(new SendInvoiceReminderJob($reminder->id));
                }
            });

        return self::SUCCESS;
    }
}
