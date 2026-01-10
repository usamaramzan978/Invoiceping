<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\MessageChannel;
use App\Models\BusinessProfile;
use App\Models\MessageTemplates;
use Illuminate\Database\Seeder;

final class MessageTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $business = BusinessProfile::query()->first();
        if (! $business) {
            return;
        }

        // --- Email Templates ---
        MessageTemplates::query()->create([
            'business_id' => $business->id,
            'name' => 'Detailed Invoice Notice',
            'channel' => MessageChannel::EMAIL,
            'content' => "Dear @{{client_name}},\n\nWe hope this message finds you well. Please find your invoice @{{invoice_number}} attached for your records.\n\nAmount Due: @{{amount}}\nDue Date: @{{due_date}}\n\nIf you have any questions, feel free to reach out.\n\nBest regards,\nThe Accounts Team",
            'is_default' => true,
            'is_active' => true,
        ]);
        MessageTemplates::query()->create([
            'business_id' => $business->id,
            'name' => 'Professional Overdue Notice',
            'channel' => MessageChannel::EMAIL,
            'content' => "Subject: Overdue Invoice Reminder\n\nDear @{{client_name}},\n\nThis is a courteous reminder that invoice @{{invoice_number}}, amounting to @{{amount}}, is now overdue as of @{{due_date}}.\n\nPlease process payment at your earliest convenience or contact us if you need assistance.\n\nThank you for your prompt attention.\n\nSincerely,\nFinance Department",
            'is_default' => false,
            'is_active' => true,
        ]);
        MessageTemplates::query()->create([
            'business_id' => $business->id,
            'name' => 'Thank You for Payment',
            'channel' => MessageChannel::EMAIL,
            'content' => "Dear @{{client_name}},\n\nWe have received your payment for invoice @{{invoice_number}}. Thank you for your business!\n\nIf you need any further documentation, please let us know.\n\nWarm regards,\nBilling Team",
            'is_default' => false,
            'is_active' => true,
        ]);
        MessageTemplates::query()->create([
            'business_id' => $business->id,
            'name' => 'Upcoming Due Date Alert',
            'channel' => MessageChannel::EMAIL,
            'content' => "Hello @{{client_name}},\n\nThis is a friendly reminder that your invoice @{{invoice_number}} is due on @{{due_date}}.\n\nAmount Due: @{{amount}}\n\nPlease let us know if you have any questions.\n\nBest,\nAccounts Receivable",
            'is_default' => false,
            'is_active' => true,
        ]);
        MessageTemplates::query()->create([
            'business_id' => $business->id,
            'name' => 'Invoice Sent Confirmation',
            'channel' => MessageChannel::EMAIL,
            'content' => "Dear @{{client_name}},\n\nYour invoice @{{invoice_number}} has been sent successfully. Please review the attached document and confirm receipt.\n\nThank you for your continued partnership.\n\nBest regards,\nYour Company",
            'is_default' => false,
            'is_active' => true,
        ]);

        // --- WhatsApp Templates ---
        MessageTemplates::query()->create([
            'business_id' => $business->id,
            'name' => 'WhatsApp Invoice Reminder',
            'channel' => MessageChannel::WHATSAPP,
            'content' => "Hi @{{client_name}},\n\nYour invoice @{{invoice_number}} for @{{amount}} is attached. Please pay by @{{due_date}}. Thanks!",
            'is_default' => true,
            'is_active' => true,
        ]);
        MessageTemplates::query()->create([
            'business_id' => $business->id,
            'name' => 'WhatsApp Overdue Alert',
            'channel' => MessageChannel::WHATSAPP,
            'content' => "Dear @{{client_name}},\n\nInvoice @{{invoice_number}} is overdue. Kindly make payment as soon as possible or contact us if you have queries.",
            'is_default' => false,
            'is_active' => true,
        ]);
        MessageTemplates::query()->create([
            'business_id' => $business->id,
            'name' => 'WhatsApp Payment Confirmation',
            'channel' => MessageChannel::WHATSAPP,
            'content' => 'Thank you @{{client_name}}! We received your payment for invoice @{{invoice_number}}.',
            'is_default' => false,
            'is_active' => true,
        ]);
        MessageTemplates::query()->create([
            'business_id' => $business->id,
            'name' => 'WhatsApp Due Date Alert',
            'channel' => MessageChannel::WHATSAPP,
            'content' => "Hello @{{client_name}},\n\nJust a reminder: invoice @{{invoice_number}} is due on @{{due_date}}. Please let us know if you need help.",
            'is_default' => false,
            'is_active' => true,
        ]);
        MessageTemplates::query()->create([
            'business_id' => $business->id,
            'name' => 'WhatsApp Invoice Sent',
            'channel' => MessageChannel::WHATSAPP,
            'content' => "Hi @{{client_name}},\n\nWe have sent your invoice @{{invoice_number}}. Please confirm receipt. Thank you!",
            'is_default' => false,
            'is_active' => true,
        ]);
    }
}
