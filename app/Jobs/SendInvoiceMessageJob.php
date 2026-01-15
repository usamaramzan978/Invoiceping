<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\InvoiceEmail;
use App\Models\Invoice;
use App\Services\LogService;
use App\Services\WhatsAppService;
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

    public int $tries = 1;

    public int $timeout = 30;

    /**
     * Create a new job instance.
     *
     * @param  string  $channel  Channel type: 'email', 'whatsapp', 'sms'
     * @param  string  $recipient  Recipient address (email or phone)
     * @param  string  $content  Pre-processed message content
     * @param  string  $userId  User UUID (required for logging)
     * @param  string|null  $invoiceId  Invoice UUID
     * @param  string|null  $subject  Email subject (only for email channel)
     * @param  string|null  $fromEmail  Sender email (only for email channel)
     * @param  string|null  $pdfPath  PDF file path (for email and WhatsApp)
     * @param  string|null  $pdfFileName  PDF filename (for email and WhatsApp)
     */
    public function __construct(
        public string $channel,
        public string $recipient,
        public string $content,
        public string $userId,
        public ?string $invoiceId = null,
        public ?string $subject = null,
        public ?string $fromEmail = null,
        public ?string $pdfPath = null,
        public ?string $pdfFileName = null,
    ) {}

    /**
     * Execute the job - only handles actual sending, no DB queries or processing.
     */
    public function handle(LogService $logService): void
    {
        $invoice = $this->invoiceId ? Invoice::query()->find($this->invoiceId) : null;

        try {
            match ($this->channel) {
                'email' => $this->sendEmail($logService, $invoice),
                'whatsapp' => $this->sendWhatsApp($logService, $invoice),
                'sms' => $this->sendSMS($logService, $invoice),
                default => Log::warning('Unknown channel: '.$this->channel),
            };
        } catch (Exception $exception) {
            // Log error to database
            $logService->logMessageFailed(
                $this->channel,
                $this->recipient,
                $exception->getMessage(),
                $invoice,
                ['exception' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()],
                $this->userId
            );

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
    public function failed(Throwable $exception, LogService $logService): void
    {
        $invoice = $this->invoiceId ? Invoice::query()->find($this->invoiceId) : null;

        $logService->logMessageFailed(
            $this->channel,
            $this->recipient,
            'Job failed after '.$this->tries.' retries: '.$exception->getMessage(),
            $invoice,
            ['exception' => $exception->getMessage(), 'retries' => $this->tries],
            $this->userId
        );

        Log::error('SendInvoiceMessageJob failed after retries', [
            'channel' => $this->channel,
            'recipient' => $this->recipient,
            'exception' => $exception->getMessage(),
        ]);
    }

    /**
     * Send email message.
     */
    private function sendEmail(LogService $logService, ?Invoice $invoice): void
    {
        if ($this->recipient === '' || $this->recipient === '0') {
            $logService->logWarning('Recipient email is empty', ['channel' => 'email'], $this->userId);
            Log::warning('Recipient email is empty');

            return;
        }

        if ($this->content === '' || $this->content === '0') {
            $logService->logWarning('Email content is empty', ['channel' => 'email'], $this->userId);
            Log::warning('Email content is empty');

            return;
        }

        if (in_array($this->subject, [null, '', '0'], true)) {
            $logService->logWarning('Email subject is empty', ['channel' => 'email'], $this->userId);
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

            // Log successful send
            $logService->logMessageSent(
                'email',
                $this->recipient,
                $this->content,
                $this->subject,
                $invoice,
                [
                    'from_email' => $this->fromEmail,
                    'content_length' => mb_strlen($this->content),
                ],
                $this->userId
            );

            Log::info('Email sent successfully', [
                'recipient' => $this->recipient,
                'subject' => $this->subject,
            ]);
        } catch (Exception $exception) {
            $logService->logMessageFailed(
                'email',
                $this->recipient,
                $exception->getMessage(),
                $invoice,
                ['subject' => $this->subject],
                $this->userId
            );

            Log::error('Failed to send email', [
                'recipient' => $this->recipient,
                'exception' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }

    /**
     * Send WhatsApp message using WhatsAppService.
     */
    private function sendWhatsApp(LogService $logService, ?Invoice $invoice): void
    {
        if ($this->recipient === '' || $this->recipient === '0') {
            $logService->logWarning('WhatsApp recipient phone is empty', ['channel' => 'whatsapp'], $this->userId);
            Log::warning('WhatsApp recipient phone is empty');

            return;
        }

        if ($this->content === '' || $this->content === '0') {
            $logService->logWarning('WhatsApp message content is empty', ['channel' => 'whatsapp'], $this->userId);
            Log::warning('WhatsApp message content is empty');

            return;
        }

        $provider = 'unknown';

        try {
            $whatsappService = app(WhatsAppService::class);
            $result = $whatsappService->send(
                $this->userId,
                $this->recipient,
                $this->content,
                $this->pdfPath,
                $this->pdfFileName
            );

            $provider = $result['provider'] ?? 'unknown';

            if ($result['success']) {
                // Log successful send
                $logService->logMessageSent(
                    'whatsapp',
                    $this->recipient,
                    $this->content,
                    null,
                    $invoice,
                    [
                        'original_phone' => $this->recipient,
                        'message_id' => $result['message_id'] ?? null,
                        'provider' => $provider,
                        'message_length' => mb_strlen($this->content),
                    ],
                    $this->userId
                );

                Log::info('WhatsApp message sent', [
                    'phone' => $this->recipient,
                    'message_id' => $result['message_id'] ?? null,
                    'provider' => $provider,
                    'message_length' => mb_strlen($this->content),
                ]);
            } else {
                throw new Exception($result['error'] ?? 'Failed to send WhatsApp message');
            }
        } catch (Exception $exception) {
            $logService->logMessageFailed(
                'whatsapp',
                $this->recipient,
                $exception->getMessage(),
                $invoice,
                [
                    'original_phone' => $this->recipient,
                    'provider' => $provider,
                ],
                $this->userId
            );

            Log::error('Failed to send WhatsApp message', [
                'phone' => $this->recipient,
                'provider' => $provider,
                'exception' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }

    /**
     * Send SMS message.
     */
    private function sendSMS(LogService $logService, ?Invoice $invoice): void
    {
        if ($this->recipient === '' || $this->recipient === '0') {
            $logService->logWarning('SMS recipient phone is empty', ['channel' => 'sms'], $this->userId);
            Log::warning('SMS recipient phone is empty');

            return;
        }

        if ($this->content === '' || $this->content === '0') {
            $logService->logWarning('SMS message content is empty', ['channel' => 'sms'], $this->userId);
            Log::warning('SMS message content is empty');

            return;
        }

        try {
            $formattedPhone = $this->formatPhoneNumber($this->recipient);

            // TODO: Integrate with your SMS API service
            // Example: SMSService::send($formattedPhone, $this->content);

            // Log successful send
            $logService->logMessageSent(
                'sms',
                $formattedPhone,
                $this->content,
                null,
                $invoice,
                [
                    'original_phone' => $this->recipient,
                    'formatted_phone' => $formattedPhone,
                    'message_length' => mb_strlen($this->content),
                    'sms_parts' => ceil(mb_strlen($this->content) / 160), // SMS parts calculation
                ],
                $this->userId
            );

            Log::info('SMS message sent', [
                'phone' => $formattedPhone,
                'message_length' => mb_strlen($this->content),
            ]);
        } catch (Exception $exception) {
            $logService->logMessageFailed(
                'sms',
                $this->recipient,
                $exception->getMessage(),
                $invoice,
                ['formatted_phone' => $this->formatPhoneNumber($this->recipient)],
                $this->userId
            );

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
