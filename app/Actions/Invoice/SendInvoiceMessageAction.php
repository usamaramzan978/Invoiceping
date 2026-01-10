<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Jobs\SendInvoiceMessageJob;
use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Models\MessageTemplates;
use App\Services\TemplateVariableService;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final readonly class SendInvoiceMessageAction
{
    public function __construct(
        private TemplateVariableService $variableService
    ) {}

    /**
     * Prepare and dispatch invoice messages for multiple channels.
     *
     * @param  array<string, mixed>  $validated
     * @param  string  $userId  User UUID
     * @return array<string, mixed> Results for each channel
     *
     * @throws ValidationException
     */
    public function execute(array $validated, string $userId): array
    {
        $invoice = Invoice::query()
            ->with(['client', 'business'])
            ->findOrFail($validated['invoice_id']);

        $channels = $validated['channels'];
        $selectedTemplates = $validated['selected_template'] ?? [];
        $results = [];

        foreach ($channels as $channel) {
            try {
                $preparedData = $this->prepareMessageData(
                    $invoice,
                    $channel,
                    $selectedTemplates[$channel] ?? null,
                    $userId
                );

                if ($preparedData === null) {
                    $results[$channel] = [
                        'success' => false,
                        'message' => 'No template found for channel: '.$channel,
                    ];

                    continue;
                }

                // Dispatch lightweight job with pre-processed data
                dispatch(new SendInvoiceMessageJob(
                    channel: $channel,
                    recipient: $preparedData['recipient'],
                    content: $preparedData['content'],
                    subject: $preparedData['subject'] ?? null,
                    fromEmail: $preparedData['from_email'] ?? null,
                ));

                $results[$channel] = [
                    'success' => true,
                    'message' => 'Message queued for '.$channel,
                ];
            } catch (Exception $exception) {
                Log::error('Failed to prepare message for channel '.$channel, [
                    'invoice_id' => $invoice->id,
                    'channel' => $channel,
                    'exception' => $exception->getMessage(),
                ]);

                $results[$channel] = [
                    'success' => false,
                    'message' => 'Failed to prepare message: '.$exception->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Prepare message data for a specific channel.
     *
     * @param  string  $userId  User UUID
     * @return array<string, mixed>|null Prepared data or null if template not found
     */
    private function prepareMessageData(
        Invoice $invoice,
        string $channel,
        ?string $templateId,
        string $userId
    ): ?array {
        $template = $this->getTemplate($channel, $templateId, $userId);

        if ($template === null) {
            return null;
        }

        // Extract content and subject based on template type
        [$content, $subject] = $this->extractTemplateContent($template);

        if (empty($content)) {
            Log::warning('Template content is empty', [
                'template_id' => $template->id,
                'channel' => $channel,
            ]);

            return null;
        }

        // Replace variables with actual invoice data
        $processedContent = $this->variableService->replaceVariables($content, $invoice);
        $processedSubject = $subject ? $this->variableService->replaceVariables($subject, $invoice) : null;

        // Prepare recipient and sender data
        $recipient = $this->getRecipient($invoice, $channel);
        $fromEmail = $this->getFromEmail($invoice);

        if ($recipient === null) {
            Log::warning('Recipient not found for channel', [
                'invoice_id' => $invoice->id,
                'channel' => $channel,
            ]);

            return null;
        }

        return [
            'recipient' => $recipient,
            'subject' => $processedSubject,
            'content' => $processedContent,
            'from_email' => $fromEmail,
        ];
    }

    /**
     * Get template for the specified channel.
     *
     * @param  string  $userId  User UUID
     */
    private function getTemplate(string $channel, ?string $templateId, string $userId): EmailTemplate|MessageTemplates|null
    {
        if ($channel === 'email') {
            if ($templateId !== null) {
                return EmailTemplate::query()
                    ->where('user_id', $userId)
                    ->where('id', (int) $templateId)
                    ->where('is_active', true)
                    ->first();
            }

            // Get default email template
            return EmailTemplate::query()
                ->where('user_id', $userId)
                ->where('is_default', true)
                ->where('is_active', true)
                ->first();
        }

        // WhatsApp or SMS
        if ($templateId !== null) {
            return MessageTemplates::query()
                ->where('user_id', $userId)
                ->where('id', $templateId)
                ->where('channel', $channel)
                ->where('is_active', true)
                ->first();
        }

        // Get default message template for this channel
        return MessageTemplates::query()
            ->where('user_id', $userId)
            ->where('channel', $channel)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Extract content and subject from template based on type.
     *
     * @return array{string, string|null} [content, subject]
     */
    private function extractTemplateContent(EmailTemplate|MessageTemplates $template): array
    {
        if ($template instanceof EmailTemplate) {
            // Prefer compiled HTML, fallback to empty string if not available
            $content = $template->template_html ?? '';

            // If HTML is empty, log warning (should not happen in normal flow)
            if (empty($content)) {
                Log::warning('Email template HTML is empty, template_json may need rendering', [
                    'template_id' => $template->id,
                    'template_name' => $template->name,
                ]);
                $content = ''; // Will be caught by empty check in prepareMessageData
            }

            $subject = $template->subject ?? $template->name;

            return [$content, $subject];
        }

        // MessageTemplates (WhatsApp/SMS)
        return [$template->content ?? '', null];
    }

    /**
     * Get recipient for the specified channel.
     */
    private function getRecipient(Invoice $invoice, string $channel): ?string
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
    private function getFromEmail(Invoice $invoice): ?string
    {
        return $invoice->business->email ?? config('mail.from.address');
    }
}
