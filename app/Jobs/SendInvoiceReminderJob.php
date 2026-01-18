<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Invoice\GenerateInvoicePdfAction;
use App\Enums\MessageChannel;
use App\Enums\ReminderStatusEnum;
use App\Models\Invoice;
use App\Models\ReminderSchedule;
use App\Services\InvoiceBlockProcessor;
use App\Services\LogService;
use App\Services\TemplateVariableService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        LogService $logService,
        InvoiceBlockProcessor $invoiceBlockProcessor
    ): void {
        // Eager load only necessary relationships to keep job lightweight
        $schedule = ReminderSchedule::query()
            ->with([
                'invoice:id,client_id,business_id,pdf_path', // Only load needed invoice fields
                'invoice.client:id,email,phone,whatsapp_number', // Only load needed client fields
                'invoice.business:id,user_id,email', // Only load needed business fields
                'emailTemplate:id,template_html,template_json,subject', // Only load needed template fields
                'messageTemplate:id,content', // Only load needed template fields
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

            // Process InvoiceBlock if this is an email template
            // Optimize: Only check for InvoiceBlock if we need to process it
            $pdfPath = null;
            $pdfFileName = null;
            $includePdf = $schedule->include_pdf ?? false;

            if ($schedule->isEmailChannel() && $schedule->emailTemplate) {
                // Check if template has InvoiceBlock (only check once)
                $hasInvoiceBlock = $invoiceBlockProcessor->hasInvoiceBlock($schedule->emailTemplate->template_json ?? []);

                if ($hasInvoiceBlock) {
                    // Process InvoiceBlock: include PDF or remove placeholder based on include_pdf setting
                    // This value comes from:
                    // - Manual schedules: user checkbox selection
                    // - Rule-based schedules: copied from reminder_rule_step_templates.include_pdf
                    $processedContent = $invoiceBlockProcessor->processInvoiceBlocks(
                        $processedContent,
                        $schedule->invoice,
                        $includePdf
                    );

                    // Get PDF path for email attachment if PDF is included
                    if ($includePdf) {
                        $pdfPath = $this->getPdfPath($schedule->invoice);
                        $pdfFileName = $pdfPath ? 'invoice-'.$schedule->invoice->invoice_number.'.pdf' : null;
                    }
                }
            } elseif ($schedule->isWhatsAppChannel() && $includePdf) {
                // For WhatsApp, get PDF path if checkbox is checked
                $pdfPath = $this->getPdfPath($schedule->invoice);
                $pdfFileName = $pdfPath ? 'invoice-'.$schedule->invoice->invoice_number.'.pdf' : null;
            }

            // Get recipient based on channel
            $channelValue = $schedule->channel instanceof MessageChannel ? $schedule->channel->value : (string) $schedule->channel;
            $recipient = $this->getRecipient($schedule->invoice, $channelValue);
            $fromEmail = $this->getFromEmail($schedule->invoice);

            throw_if($recipient === null, Exception::class, 'Recipient not found for channel: '.$channelValue);

            // Get user ID from invoice business
            $userId = $schedule->invoice->business->user_id ?? null;
            throw_unless($userId, Exception::class, 'User ID not found for reminder schedule');

            // Dispatch the actual sending job
            dispatch(new SendInvoiceMessageJob(
                channel: $channelValue,
                recipient: $recipient,
                content: $processedContent,
                userId: $userId,
                invoiceId: $schedule->invoice->id,
                subject: $processedSubject,
                fromEmail: $fromEmail,
                pdfPath: $pdfPath,
                pdfFileName: $pdfFileName,
            ));

            // Update reminder schedule status to sent
            $schedule->update([
                'status' => ReminderStatusEnum::SENT,
                'sent_at' => now(),
            ]);

            Log::info('Reminder scheduled and dispatched successfully', [
                'schedule_id' => $schedule->id,
                'channel' => $channelValue,
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
     * Get PDF path for invoice. Generates PDF if it doesn't exist.
     *
     * @return string|null PDF path or null if generation fails
     */
    private function getPdfPath(Invoice $invoice): ?string
    {
        // Check if PDF already exists
        if ($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path)) {
            return $invoice->pdf_path;
        }

        // Generate PDF synchronously for immediate sending
        try {
            $design = 1; // TODO: Get from invoice->pdf_design when that field exists
            $generatePdfAction = app(GenerateInvoicePdfAction::class);

            return $generatePdfAction->execute($invoice, $design);
        } catch (Exception $exception) {
            Log::error('Failed to generate PDF for invoice', [
                'invoice_id' => $invoice->id,
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
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
