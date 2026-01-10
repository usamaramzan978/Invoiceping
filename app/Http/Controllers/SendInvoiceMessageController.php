<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\SendInvoiceMessageJob;
use App\Models\Invoice;
use App\Models\MessageTemplates;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class SendInvoiceMessageController
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => ['required', 'uuid', 'exists:invoices,id'],
            'channels' => ['required', 'array'],
            'channels.*' => ['string', 'in:email,whatsapp'],
            'selected_template' => ['nullable', 'array'],
            'selected_template.email' => ['nullable', 'uuid', 'exists:message_templates,id'],
            'selected_template.whatsapp' => ['nullable', 'uuid', 'exists:message_templates,id'],
        ]);

        $invoice = Invoice::query()->findOrFail($validated['invoice_id']);
        $channels = $validated['channels'];
        $selectedTemplates = $validated['selected_template'] ?? [];

        $defaultTemplates = MessageTemplates::query()->where('business_id', $invoice->business_id)
            ->whereIn('channel', $channels)
            ->where('is_default', true)
            ->get()
            ->keyBy('channel');

        foreach ($channels as $channel) {

            // Use selected template or fallback to default
            $template = isset($selectedTemplates[$channel])
                ? MessageTemplates::query()->where('business_id', $invoice->business_id)
                    ->where('id', $selectedTemplates[$channel])
                    ->first()
                : $defaultTemplates[$channel] ?? null;

            // Throw exception if template missing
            if (! $template) {
                throw ValidationException::withMessages([
                    'selected_template' => ['No template found for channel: '.$channel],
                ]);
            }

            // Dispatch job
            dispatch(new SendInvoiceMessageJob($invoice->id, $template->id, $channel));
        }

        return back()->with('success', 'Invoice message(s) are being sent.');
    }
}
