<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\InvoiceEmail;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Lightweight job to send pre-processed invoice messages.
 * All data preparation (template fetching, variable replacement) is done in the Action.
 */
final class SendInvoiceMessageJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $timeout = 30;

    /**
     * Create a new job instance.
     *
     * @param  string  $channel  Channel type: 'email', 'whatsapp', 'sms'
     * @param  string  $recipient  Recipient address (email or phone)
     * @param  string  $content  Pre-processed message content
     * @param  string|null  $subject  Email subject (only for email channel)
     * @param  string|null  $fromEmail  Sender email (only for email channel)
     */
    public function __construct(
        public string $channel,
        public string $recipient,
        public string $content,
        public ?string $subject = null,
        public ?string $fromEmail = null,
    ) {}

    /**
     * Execute the job - only handles actual sending, no DB queries or processing.
     */
    public function handle(): void
    {
        try {
            match ($this->channel) {
                'email' => $this->sendEmail(),
                'whatsapp' => $this->sendWhatsApp(),
                'sms' => $this->sendSMS(),
                default => Log::warning('Unknown channel: '.$this->channel),
            };
        } catch (Exception $exception) {
            Log::error('Error in SendInvoiceMessageJob', [
                'channel' => $this->channel,
                'recipient' => $this->recipient,
                'exception' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('SendInvoiceMessageJob failed after retries', [
            'channel' => $this->channel,
            'recipient' => $this->recipient,
            'exception' => $exception->getMessage(),
        ]);
    }

    /**
     * Send email message.
     */
    private function sendEmail(): void
    {
        if ($this->recipient === '' || $this->recipient === '0') {
            Log::warning('Recipient email is empty');

            return;
        }

        if ($this->content === '' || $this->content === '0') {
            Log::warning('Email content is empty');

            return;
        }

        if (in_array($this->subject, [null, '', '0'], true)) {
            Log::warning('Email subject is empty');

            return;
        }

        try {
            // Use Mailable class for proper HTML email rendering
            Mail::to($this->recipient)->send(
                new InvoiceEmail(
                    htmlContent: $this->content,
                    emailSubject: $this->subject,
                    fromEmail: $this->fromEmail,
                )
            );

            Log::info('Email sent successfully', [
                'recipient' => $this->recipient,
                'subject' => $this->subject,
            ]);
        } catch (Exception $exception) {
            Log::error('Failed to send email', [
                'recipient' => $this->recipient,
                'exception' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }

    /**
     * Send WhatsApp message.
     */
    private function sendWhatsApp(): void
    {
        if ($this->recipient === '' || $this->recipient === '0') {
            Log::warning('WhatsApp recipient phone is empty');

            return;
        }

        if ($this->content === '' || $this->content === '0') {
            Log::warning('WhatsApp message content is empty');

            return;
        }

        try {
            $formattedPhone = $this->formatPhoneNumber($this->recipient);

            // TODO: Integrate with your WhatsApp API service
            // Example: WhatsAppService::send($formattedPhone, $this->content);

            Log::info('WhatsApp message sent', [
                'phone' => $formattedPhone,
                'message_length' => mb_strlen($this->content),
            ]);
        } catch (Exception $exception) {
            Log::error('Failed to send WhatsApp message', [
                'phone' => $this->recipient,
                'exception' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }

    /**
     * Send SMS message.
     */
    private function sendSMS(): void
    {
        if ($this->recipient === '' || $this->recipient === '0') {
            Log::warning('SMS recipient phone is empty');

            return;
        }

        if ($this->content === '' || $this->content === '0') {
            Log::warning('SMS message content is empty');

            return;
        }

        try {
            $formattedPhone = $this->formatPhoneNumber($this->recipient);

            // TODO: Integrate with your SMS API service
            // Example: SMSService::send($formattedPhone, $this->content);

            Log::info('SMS message sent', [
                'phone' => $formattedPhone,
                'message_length' => mb_strlen($this->content),
            ]);
        } catch (Exception $exception) {
            Log::error('Failed to send SMS message', [
                'phone' => $this->recipient,
                'exception' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }

    /**
     * Format phone number (customize based on your requirements).
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove any non-digit characters
        $cleaned = preg_replace('/\D/', '', $phone);

        // Add country code if not present (customize based on your needs)
        if (! str_starts_with((string) $cleaned, '92')) {
            // 92 is Pakistan code - adjust for your default country
            return '92'.mb_ltrim((string) $cleaned, '0');
        }

        return $cleaned;
    }
}
