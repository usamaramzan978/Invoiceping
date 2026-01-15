<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Invoice\SendInvoiceMessageAction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final readonly class SendInvoiceMessageController
{
    public function __construct(
        private SendInvoiceMessageAction $sendMessageAction
    ) {}

    /**
     * Send invoice messages via selected channels.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => ['required', 'uuid', 'exists:invoices,id'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['string', 'in:email,whatsapp,sms'],
            'selected_template' => ['nullable', 'array'],
            'selected_template.email' => ['nullable', 'integer', 'exists:email_templates,id'],
            'selected_template.whatsapp' => ['nullable', 'uuid', 'exists:message_templates,id'],
            'selected_template.sms' => ['nullable', 'uuid', 'exists:message_templates,id'],
            'include_pdf_email' => ['sometimes', 'boolean', 'in:0,1,true,false'],
            'include_pdf_whatsapp' => ['sometimes', 'boolean', 'in:0,1,true,false'],
        ]);

        $userId = Auth::id();

        if ($userId === null) {
            return back()->with('error', 'You must be logged in to send messages.');
        }

        try {
            $results = $this->sendMessageAction->execute($validated, $userId);

            // Check if any channel failed
            $failedChannels = array_filter($results, fn (array $result): bool => ! $result['success']);

            if ($failedChannels !== []) {
                $errorMessages = array_map(
                    fn (array $result) => $result['message'],
                    $failedChannels
                );

                return back()->with('error', 'Some messages failed to send: '.implode(', ', $errorMessages));
            }

            $successCount = count(array_filter($results, fn (array $result) => $result['success']));

            return back()->with('success', sprintf('Successfully queued %d message(s) for sending.', $successCount));
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        } catch (Exception $exception) {
            return back()->with('error', 'An error occurred while sending messages: '.$exception->getMessage());
        }
    }
}
