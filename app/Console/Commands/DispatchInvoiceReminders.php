<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ReminderStatusEnum;
use App\Jobs\SendInvoiceReminderJob;
use App\Models\ReminderSchedule;
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
        $count = 0;

        ReminderSchedule::query()
            ->where('status', ReminderStatusEnum::PENDING)
            ->where('scheduled_at', '<=', now())
            ->with(['invoice.client', 'invoice.business'])
            ->chunkById(50, function ($reminders) use (&$count): void {
                foreach ($reminders as $reminder) {
                    dispatch(new SendInvoiceReminderJob($reminder->id));
                    $count++;
                }
            });

        if ($count > 0) {
            $this->info(sprintf('Dispatched %d reminder(s) for processing.', $count));
        } else {
            $this->info('No pending reminders to dispatch.');
        }

        return self::SUCCESS;
    }
}
