<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ReminderStatusEnum;
use App\Models\ReminderSchedule;
use App\Services\LogService;
use App\Services\TemplateVariableService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SendInvoiceReminderJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60;

    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $reminderScheduleId) {}

    /**
     * Execute the job.
     */
    public function handle(
        TemplateVariableService $variableService,
        LogService $logService
    ): void {
        $schedule = ReminderSchedule::query()
            ->with([
                'invoice.client',
                'invoice.business',
                'emailTemplate',
                'messageTemplate',
            ])
            ->findOrFail($this->reminderScheduleId);

        // Skip if already sent, cancelled, or failed
        if ($schedule->status !== ReminderStatusEnum::PENDING) {
            Log::info('Reminder schedule is not pending, skipping', [
                'schedule_id' => $schedule->id,
                'status' => $schedule->status->value,
            ]);

            return;
        }

        try {
            // Get template content and process variables
            [$content, $subject] = $this->extractTemplateContent($schedule);

            throw_if(empty($content), Exception::class, 'Template content is empty');

            // Replace variables using TemplateVariableService
            $processedContent = $variableService->replaceVariables($content, $schedule->invoice);
            $processedSubject = $subject ? $variableService->replaceVariables($subject, $schedule->invoice) : null;

            // Get recipient based on channel
            $recipient = $this->getRecipient($schedule->invoice, $schedule->channel->value);
            $fromEmail = $this->getFromEmail($schedule->invoice);

            if ($recipient === null) {
                throw new Exception('Recipient not found for channel: '.$schedule->channel->value);
            }

            // Get user ID from invoice business
            $userId = $schedule->invoice->business->user_id ?? null;
            throw_unless($userId, Exception::class, 'User ID not found for reminder schedule');

            // Dispatch the actual sending job
            dispatch(new SendInvoiceMessageJob(
                channel: $schedule->channel->value,
                recipient: $recipient,
                content: $processedContent,
                userId: $userId,
                invoiceId: $schedule->invoice->id,
                subject: $processedSubject,
                fromEmail: $fromEmail,
            ));

            // Update reminder schedule status to sent
            $schedule->update([
                'status' => ReminderStatusEnum::SENT,
                'sent_at' => now(),
            ]);

            Log::info('Reminder scheduled and dispatched successfully', [
                'schedule_id' => $schedule->id,
                'channel' => $schedule->channel->value,
                'recipient' => $recipient,
            ]);
        } catch (Exception $exception) {
            // Update reminder schedule status to failed
            $schedule->update([
                'status' => ReminderStatusEnum::FAILED,
                'failure_reason' => $exception->getMessage(),
            ]);

            Log::error('Failed to process reminder schedule', [
                'schedule_id' => $schedule->id,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(Throwable $exception, LogService $logService): void
    {
        $schedule = ReminderSchedule::query()->find($this->reminderScheduleId);

        if ($schedule) {
            $schedule->update([
                'status' => ReminderStatusEnum::FAILED,
                'failure_reason' => 'Job failed after '.$this->tries.' retries: '.$exception->getMessage(),
            ]);
        }

        Log::error('SendInvoiceReminderJob failed after retries', [
            'schedule_id' => $this->reminderScheduleId,
            'exception' => $exception->getMessage(),
        ]);
    }

    /**
     * Extract content and subject from reminder schedule template.
     *
     * @return array{string, string|null} [content, subject]
     */
    private function extractTemplateContent(ReminderSchedule $schedule): array
    {
        if ($schedule->isEmailChannel() && $schedule->emailTemplate) {
            // Email template: use template_html and subject
            $content = $schedule->emailTemplate->template_html ?? '';

            if (empty($content)) {
                Log::warning('Email template HTML is empty', [
                    'template_id' => $schedule->emailTemplate->id,
                    'template_name' => $schedule->emailTemplate->name,
                ]);
            }

            $subject = $schedule->emailTemplate->subject ?? $schedule->emailTemplate->name;

            return [$content, $subject];
        }

        if (($schedule->isWhatsAppChannel() || $schedule->isSMSChannel()) && $schedule->messageTemplate) {
            // Message template: use content
            $content = $schedule->messageTemplate->content ?? '';

            return [$content, null];
        }

        throw new Exception('Template not found for reminder schedule');
    }

    /**
     * Get recipient for the specified channel.
     */
    private function getRecipient($invoice, string $channel): ?string
    {
        return match ($channel) {
            'email' => $invoice->client->email ?? null,
            'whatsapp' => $invoice->client->whatsapp_number ?? $invoice->client->phone ?? null,
            'sms' => $invoice->client->phone ?? $invoice->client->whatsapp_number ?? null,
            default => null,
        };
    }

    /**
     * Get sender email address.
     */
    private function getFromEmail($invoice): ?string
    {
        return $invoice->business->email ?? config('mail.from.address');
    }
}
