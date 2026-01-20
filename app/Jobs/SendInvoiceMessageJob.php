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
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Lightweight job to send pre-processed invoice messages.
 * All data preparation (template fetching, variable replacement) is done in the Action.
 */
final class SendInvoiceMessageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

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
            Log::info('SendInvoiceMessageJob started', [
                'job_id' => $this->job?->getJobId() ?? 'unknown',
                'channel' => $this->channel,
                'recipient' => $this->maskRecipient($this->recipient),
                'user_id' => $this->userId,
                'invoice_id' => $this->invoiceId,
                'attempt' => $this->attempts(),
            ]);

            match ($this->channel) {
                'email' => $this->sendEmail($logService, $invoice),
                'whatsapp' => $this->sendWhatsApp($logService, $invoice),
                'sms' => $this->sendSMS($logService, $invoice),
                default => throw new Exception('Unknown channel: '.$this->channel),
            };

            Log::info('SendInvoiceMessageJob completed successfully', [
                'job_id' => $this->job?->getJobId() ?? 'unknown',
                'channel' => $this->channel,
                'recipient' => $this->maskRecipient($this->recipient),
            ]);
        } catch (Exception $exception) {
            Log::error('SendInvoiceMessageJob exception', [
                'job_id' => $this->job?->getJobId() ?? 'unknown',
                'channel' => $this->channel,
                'recipient' => $this->maskRecipient($this->recipient),
                'user_id' => $this->userId,
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'attempt' => $this->attempts(),
            ]);
            throw $exception;
        }
    }

    /**
     * Handle job failure.
     * Laravel calls this method with only the exception parameter.
     */
    public function failed(Throwable $exception): void
    {
        try {
            $logService = app(LogService::class);
            $invoice = $this->invoiceId ? Invoice::query()->find($this->invoiceId) : null;

            $failureMessage = 'Job failed after '.$this->attempts().' attempts: '.$exception->getMessage();

            $logService->logMessageFailed(
                $this->channel,
                $this->recipient,
                $failureMessage,
                $invoice,
                [
                    'exception' => $exception->getMessage(),
                    'attempts' => $this->attempts(),
                    'trace' => mb_substr($exception->getTraceAsString(), 0, 1000),
                ],
                $this->userId
            );

            Log::error('SendInvoiceMessageJob permanently failed', [
                'job_id' => $this->job?->getJobId() ?? 'unknown',
                'channel' => $this->channel,
                'recipient' => $this->maskRecipient($this->recipient),
                'user_id' => $this->userId,
                'attempts' => $this->attempts(),
                'exception' => $exception->getMessage(),
            ]);
        } catch (Exception $e) {
            Log::error('Error in SendInvoiceMessageJob::failed()', [
                'original_exception' => $exception->getMessage(),
                'new_exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send email message.
     */
    private function sendEmail(LogService $logService, ?Invoice $invoice): void
    {
        // Validate recipient
        if (in_array($this->recipient, ['', '0', '0'], true)) {
            $message = 'Recipient email is empty';
            $logService->logWarning($message, ['channel' => 'email'], $this->userId);
            Log::warning($message, ['user_id' => $this->userId]);

            return;
        }

        // Validate content
        if (in_array($this->content, ['', '0', '0'], true)) {
            $message = 'Email content is empty';
            $logService->logWarning($message, ['channel' => 'email'], $this->userId);
            Log::warning($message, ['user_id' => $this->userId]);

            return;
        }

        // Validate subject
        if (in_array($this->subject, [null, '', '0', '0'], true)) {
            $message = 'Email subject is empty';
            $logService->logWarning($message, ['channel' => 'email'], $this->userId);
            Log::warning($message, ['user_id' => $this->userId]);

            return;
        }

        try {
            Log::info('Sending email', [
                'recipient' => $this->maskRecipient($this->recipient),
                'subject' => $this->subject,
                'content_length' => mb_strlen($this->content),
                'has_pdf' => !in_array($this->pdfPath, [null, '', '0'], true),
            ]);

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
                    'recipient' => $this->maskRecipient($this->recipient),
                ],
                $this->userId
            );

            Log::info('Email sent successfully', [
                'recipient' => $this->maskRecipient($this->recipient),
                'subject' => $this->subject,
                'user_id' => $this->userId,
            ]);
        } catch (Exception $exception) {
            Log::error('Failed to send email', [
                'recipient' => $this->maskRecipient($this->recipient),
                'subject' => $this->subject,
                'exception' => $exception->getMessage(),
                'user_id' => $this->userId,
            ]);

            $logService->logMessageFailed(
                'email',
                $this->recipient,
                $exception->getMessage(),
                $invoice,
                ['subject' => $this->subject],
                $this->userId
            );

            throw $exception;
        }
    }

    /**
     * Send WhatsApp message using WhatsAppService.
     */
    private function sendWhatsApp(LogService $logService, ?Invoice $invoice): void
    {
        // Validate recipient
        if (in_array($this->recipient, ['', '0', '0'], true)) {
            $message = 'WhatsApp recipient phone is empty';
            $logService->logWarning($message, ['channel' => 'whatsapp'], $this->userId);
            Log::warning($message, ['user_id' => $this->userId]);

            return;
        }

        // Validate content
        if (in_array($this->content, ['', '0', '0'], true)) {
            $message = 'WhatsApp message content is empty';
            $logService->logWarning($message, ['channel' => 'whatsapp'], $this->userId);
            Log::warning($message, ['user_id' => $this->userId]);

            return;
        }

        $provider = 'unknown';

        try {
            $formattedPhone = $this->formatPhoneNumber($this->recipient);

            Log::info('Preparing to send WhatsApp message', [
                'original_phone' => $this->maskRecipient($this->recipient),
                'formatted_phone' => $this->maskRecipient($formattedPhone),
                'user_id' => $this->userId,
                'content_length' => mb_strlen($this->content),
                'has_pdf' => !in_array($this->pdfPath, [null, '', '0'], true),
            ]);

            // Verify PDF exists if provided
            if (!in_array($this->pdfPath, [null, '', '0'], true)) {
                if (! Storage::disk('public')->exists($this->pdfPath)) {
                    Log::warning('PDF file not found for WhatsApp send', [
                        'pdf_path' => $this->pdfPath,
                        'user_id' => $this->userId,
                    ]);
                    // Continue without PDF rather than failing
                    $this->pdfPath = null;
                    $this->pdfFileName = null;
                } else {
                    Log::info('PDF file verified', [
                        'pdf_path' => $this->pdfPath,
                        'file_size' => Storage::disk('public')->size($this->pdfPath),
                    ]);
                }
            }

            $whatsappService = app(WhatsAppService::class);
            $result = $whatsappService->send(
                $this->userId,
                $formattedPhone,
                $this->content,
                $this->pdfPath,
                $this->pdfFileName
            );

            $provider = $result['provider'] ?? 'unknown';

            Log::info('WhatsApp send response received', [
                'provider' => $provider,
                'success' => $result['success'],
                'message_id' => $result['message_id'] ?? null,
                'user_id' => $this->userId,
            ]);

            if (! $result['success']) {
                throw new Exception($result['error'] ?? 'Failed to send WhatsApp message - unknown error');
            }

            // Log successful send
            $logService->logMessageSent(
                'whatsapp',
                $this->recipient,
                $this->content,
                null,
                $invoice,
                [
                    'original_phone' => $this->maskRecipient($this->recipient),
                    'formatted_phone' => $this->maskRecipient($formattedPhone),
                    'message_id' => $result['message_id'] ?? null,
                    'provider' => $provider,
                    'message_length' => mb_strlen($this->content),
                ],
                $this->userId
            );

            Log::info('WhatsApp message sent successfully', [
                'formatted_phone' => $this->maskRecipient($formattedPhone),
                'message_id' => $result['message_id'] ?? null,
                'provider' => $provider,
                'user_id' => $this->userId,
            ]);
        } catch (Exception $exception) {
            Log::error('Failed to send WhatsApp message', [
                'original_phone' => $this->maskRecipient($this->recipient),
                'provider' => $provider,
                'exception' => $exception->getMessage(),
                'user_id' => $this->userId,
            ]);

            $logService->logMessageFailed(
                'whatsapp',
                $this->recipient,
                $exception->getMessage(),
                $invoice,
                [
                    'original_phone' => $this->maskRecipient($this->recipient),
                    'formatted_phone' => $this->maskRecipient($this->formatPhoneNumber($this->recipient)),
                    'provider' => $provider,
                ],
                $this->userId
            );

            throw $exception;
        }
    }

    /**
     * Send SMS message.
     */
    private function sendSMS(LogService $logService, ?Invoice $invoice): void
    {
        // Validate recipient
        if (in_array($this->recipient, ['', '0', '0'], true)) {
            $message = 'SMS recipient phone is empty';
            $logService->logWarning($message, ['channel' => 'sms'], $this->userId);
            Log::warning($message, ['user_id' => $this->userId]);

            return;
        }

        // Validate content
        if (in_array($this->content, ['', '0', '0'], true)) {
            $message = 'SMS message content is empty';
            $logService->logWarning($message, ['channel' => 'sms'], $this->userId);
            Log::warning($message, ['user_id' => $this->userId]);

            return;
        }

        try {
            $formattedPhone = $this->formatPhoneNumber($this->recipient);

            Log::info('Sending SMS message', [
                'original_phone' => $this->maskRecipient($this->recipient),
                'formatted_phone' => $this->maskRecipient($formattedPhone),
                'message_length' => mb_strlen($this->content),
                'sms_parts' => ceil(mb_strlen($this->content) / 160),
                'user_id' => $this->userId,
            ]);

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
                    'original_phone' => $this->maskRecipient($this->recipient),
                    'formatted_phone' => $this->maskRecipient($formattedPhone),
                    'message_length' => mb_strlen($this->content),
                    'sms_parts' => ceil(mb_strlen($this->content) / 160),
                ],
                $this->userId
            );

            Log::info('SMS message sent', [
                'phone' => $this->maskRecipient($formattedPhone),
                'message_length' => mb_strlen($this->content),
                'user_id' => $this->userId,
            ]);
        } catch (Exception $exception) {
            Log::error('Failed to send SMS message', [
                'original_phone' => $this->maskRecipient($this->recipient),
                'formatted_phone' => $this->maskRecipient($this->formatPhoneNumber($this->recipient)),
                'exception' => $exception->getMessage(),
                'user_id' => $this->userId,
            ]);

            $logService->logMessageFailed(
                'sms',
                $this->recipient,
                $exception->getMessage(),
                $invoice,
                ['formatted_phone' => $this->maskRecipient($this->formatPhoneNumber($this->recipient))],
                $this->userId
            );

            throw $exception;
        }
    }

    /**
     * Format phone number to digits only (WhatsApp API format).
     * Removes all non-digit characters and adds country code if needed.
     *
     * @param  string  $phone  Phone number in various formats (with or without +, dashes, spaces)
     * @return string Formatted phone number as digits only (e.g., 923015551234)
     *
     * @throws Exception if phone number is invalid
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove any whitespace and non-digit characters
        $phone = mb_trim($phone);

        throw_if($phone === '' || $phone === '0', Exception::class, 'Phone number cannot be empty');

        // Remove all non-digit characters (this includes +, -, spaces, etc.)
        $digits = preg_replace('/\D/', '', $phone);

        throw_if(empty($digits), Exception::class, 'Invalid phone number: no digits found after cleaning');

        // If it's already 11-15 digits, assume it's a full international number
        if (mb_strlen($digits) >= 11 && mb_strlen($digits) <= 15) {
            return $digits;
        }

        // If it starts with 92 (Pakistan country code), it's already formatted
        if (str_starts_with($digits, '92')) {
            return $digits;
        }

        // If it starts with 0 (Pakistan domestic format), remove it and add country code
        if (str_starts_with($digits, '0')) {
            $digits = mb_substr($digits, 1);

            return '92'.$digits;
        }

        // If shorter than 11 digits, assume missing country code, add 92 (Pakistan)
        if (mb_strlen($digits) < 11) {
            return '92'.$digits;
        }

        // Default: return digits as is
        return $digits;
    }

    /**
     * Mask sensitive information (phone numbers, emails) for logging.
     *
     * @param  string  $value  Sensitive value
     * @return string Masked value for safe logging
     */
    private function maskRecipient(string $value): string
    {
        if ($value === '' || $value === '0') {
            return '***';
        }

        if (str_contains($value, '@')) {
            // Email format
            $parts = explode('@', $value);
            if (count($parts) === 2) {
                $localPart = $parts[0];
                $domain = $parts[1];

                return mb_substr($localPart, 0, 1).'***@'.$domain;
            }
        } elseif (mb_strlen($value) >= 4) {
            // Phone format
            $masked = mb_substr($value, 0, 3).'***'.mb_substr($value, -4);
            return $masked;
        }

        return '***';
    }
}
