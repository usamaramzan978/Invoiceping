<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Enums\SubscriptionInvoiceStatus;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use Illuminate\Support\Facades\Date;

final class GenerateSubscriptionInvoice
{
    /**
     * Generate an invoice for a subscription
     *
     * @param  array{period_start?: string, period_end?: string, due_days?: int}  $data
     */
    public function execute(Subscription $subscription, array $data = []): SubscriptionInvoice
    {
        $periodStart = isset($data['period_start'])
            ? Date::parse($data['period_start'])
            : Date::now();

        $periodEnd = isset($data['period_end'])
            ? Date::parse($data['period_end'])
            : ($subscription->billing_cycle->value === 'yearly'
                ? $periodStart->copy()->addYear()
                : $periodStart->copy()->addMonth());

        $dueDays = $data['due_days'] ?? 7;
        $dueDate = Date::now()->addDays($dueDays);

        // Generate unique invoice number
        $invoiceNumber = $this->generateInvoiceNumber();

        $invoice = new SubscriptionInvoice();
        $invoice->user_id = (string) $subscription->user_id;
        $invoice->subscription_id = (string) $subscription->id;
        $invoice->invoice_number = $invoiceNumber;
        $invoice->status = SubscriptionInvoiceStatus::PENDING;
        $invoice->subtotal = $subscription->amount;
        $invoice->tax = 0; // Can be calculated based on user location
        $invoice->discount = 0;
        $invoice->total = $subscription->amount;
        $invoice->currency = $subscription->currency;
        $invoice->period_start = $periodStart;
        $invoice->period_end = $periodEnd;
        $invoice->due_date = $dueDate;
        $invoice->save();

        return $invoice;
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'SUB';
        $date = Date::now()->format('Ymd');
        $random = mb_strtoupper(mb_substr(md5(microtime()), 0, 6));

        return sprintf('%s-%s-%s', $prefix, $date, $random);
    }
}
