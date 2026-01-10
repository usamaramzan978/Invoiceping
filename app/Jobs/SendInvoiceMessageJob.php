<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\MessageTemplates;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SendInvoiceMessageJob implements ShouldQueue
{
    use Queueable;

    public $tries = 1;

    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $invoiceId, public string $templateId, public string $channel) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $invoice = Invoice::query()->find($this->invoiceId);
            $template = MessageTemplates::query()->find($this->templateId);

            // Validate invoice and template exist
            if (! $invoice) {
                Log::error('Invoice not found for ID: '.$this->invoiceId);

                return;
            }

            if (! $template) {
                Log::error('Template not found for ID: '.$this->templateId);

                return;
            }

            // Route to appropriate channel
            match ($this->channel) {
                'email' => $this->sendEmail($invoice, $template),
                'whatsapp' => $this->sendWhatsApp($invoice, $template),
                default => Log::warning('Unknown channel: '.$this->channel),
            };
        } catch (Exception $exception) {
            Log::error('Error in SendInvoiceMessageJob: '.$exception->getMessage(), [
                'invoice_id' => $this->invoiceId,
                'template_id' => $this->templateId,
                'channel' => $this->channel,
                'exception' => $exception,
            ]);
            throw $exception; // Re-throw to trigger retry
        }
    }

    /**
     * Handle job failure
     */
    public function failed(Throwable $exception): void
    {
        Log::error('SendInvoiceMessageJob failed after retries', [
            'invoice_id' => $this->invoiceId,
            'template_id' => $this->templateId,
            'channel' => $this->channel,
            'exception' => $exception->getMessage(),
        ]);
    }

    private function sendEmail(Invoice $invoice, MessageTemplates $template): void
    {
        // Validate required fields
        if (! $invoice->client->email) {
            Log::warning('Customer email is missing for invoice: '.$invoice->id);

            return;
        }

        if (! $template->content) {
            Log::warning('Template content is empty for template: '.$template->id);

            return;
        }

        if (! $template->name) {
            Log::warning('Template name/subject is empty for template: '.$template->id);

            return;
        }

        try {
            // Replace template variables
            $content = $this->replaceVariables($template->content, $invoice);
            $subject = $template->name;

            // Send email
            $recipient = $invoice->client->email ?? null;
            if (! $recipient) {
                Log::error('Recipient email is null for invoice '.$invoice->id);

                return;
            }

            Mail::raw($content, function ($message) use ($recipient, $subject, $invoice): void {
                $message->to($recipient)
                    ->from($invoice->business->email ?? config('mail.from.address'))
                    ->subject($subject);
            });

            Log::info(sprintf('Email sent successfully to %s for invoice %s', $invoice->customer_email, $invoice->id));
        } catch (Exception $exception) {
            Log::error('Failed to send email: '.$exception->getMessage(), [
                'invoice_id' => $invoice->id,
                'customer_email' => $invoice->customer_email,
            ]);
            throw $exception;
        }
    }

    private function sendWhatsApp(Invoice $invoice, MessageTemplates $template): void
    {
        // Validate required fields
        if (! $invoice->customer_phone) {
            Log::warning('Customer phone is missing for invoice: '.$invoice->id);

            return;
        }

        if (! $template->content) {
            Log::warning('Template content is empty for template: '.$template->id);

            return;
        }

        try {
            // Replace template variables
            $message = $this->replaceVariables($template->content, $invoice);
            $phone = $this->formatPhoneNumber($invoice->customer_phone);

            // Example using your WhatsApp API service
            // Uncomment and implement based on your service
            // WhatsAppService::send($phone, $message);

            Log::info(sprintf('WhatsApp message queued for %s for invoice %s', $phone, $invoice->id));
        } catch (Exception $exception) {
            Log::error('Failed to send WhatsApp message: '.$exception->getMessage(), [
                'invoice_id' => $invoice->id,
                'customer_phone' => $invoice->customer_phone,
            ]);
            throw $exception;
        }
    }

    /**
     * Replace template variables with invoice data
     */
    private function replaceVariables(string $text, Invoice $invoice): string
    {
        return str_replace(
            [
                '@{{ client_name }}',
                '@{{ invoice_number }}',
                '@{{ amount }}',
                '@{{ due_date }}',
            ],
            [
                $invoice->client->name ?? 'Customer',
                $invoice->invoice_number ?? 'N/A',
                $invoice->total_amount ?? '0.00',
                $invoice->due_date?->format('d M Y') ?? 'N/A',
            ],
            $text
        );
    }

    /**
     * Format phone number (customize based on your requirements)
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove any non-digit characters
        $cleaned = preg_replace('/\D/', '', $phone);

        // Add country code if not present (customize based on your needs)
        if (! str_starts_with((string) $cleaned, '92')) {
            // 92 is Pakistan code
            return '92'.mb_ltrim((string) $cleaned, '0');
        }

        return $cleaned;
    }
}
