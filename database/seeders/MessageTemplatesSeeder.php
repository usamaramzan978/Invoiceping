<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\MessageChannel;
use App\Models\MessageTemplates;
use App\Models\User;
use Illuminate\Database\Seeder;

final class MessageTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $authId = User::query()->first()->id;
        if (! $authId) {
            return;
        }

        MessageTemplates::query()->create([
            'user_id' => $authId,
            'name' => 'WhatsApp Invoice Reminder',
            'channel' => MessageChannel::WHATSAPP,
            'content' => "Hi @{{client_name}},\n\nYour invoice @{{invoice_number}} for @{{amount}} is attached. Please pay by @{{due_date}}. Thanks!",
            'is_default' => true,
            'is_active' => true,
        ]);
        MessageTemplates::query()->create([
            'user_id' => $authId,
            'name' => 'WhatsApp Overdue Alert',
            'channel' => MessageChannel::WHATSAPP,
            'content' => "Dear @{{client_name}},\n\nInvoice @{{invoice_number}} is overdue. Kindly make payment as soon as possible or contact us if you have queries.",
            'is_default' => false,
            'is_active' => true,
        ]);
        MessageTemplates::query()->create([
            'user_id' => $authId,
            'name' => 'WhatsApp Payment Confirmation',
            'channel' => MessageChannel::WHATSAPP,
            'content' => 'Thank you @{{client_name}}! We received your payment for invoice @{{invoice_number}}.',
            'is_default' => false,
            'is_active' => true,
        ]);
        MessageTemplates::query()->create([
            'user_id' => $authId,
            'name' => 'WhatsApp Due Date Alert',
            'channel' => MessageChannel::WHATSAPP,
            'content' => "Hello @{{client_name}},\n\nJust a reminder: invoice @{{invoice_number}} is due on @{{due_date}}. Please let us know if you need help.",
            'is_default' => false,
            'is_active' => true,
        ]);
        MessageTemplates::query()->create([
            'user_id' => $authId,
            'name' => 'WhatsApp Invoice Sent',
            'channel' => MessageChannel::WHATSAPP,
            'content' => "Hi @{{client_name}},\n\nWe have sent your invoice @{{invoice_number}}. Please confirm receipt. Thank you!",
            'is_default' => false,
            'is_active' => true,
        ]);
    }
}
