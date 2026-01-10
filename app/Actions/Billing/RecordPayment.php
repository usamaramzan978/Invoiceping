<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Enums\SubscriptionInvoiceStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\BillingTransaction;
use App\Models\SubscriptionInvoice;
use Illuminate\Support\Facades\Date;

final class RecordPayment
{
    /**
     * Record a payment for an invoice
     *
     * @param  array{transaction_id: string, payment_method?: string, payment_gateway?: string, metadata?: array<string, mixed>}  $data
     */
    public function execute(SubscriptionInvoice $invoice, array $data): BillingTransaction
    {
        // Update invoice status
        $invoice->status = SubscriptionInvoiceStatus::PAID;
        $invoice->paid_at = Date::now();
        $invoice->save();

        // Create billing transaction
        $transaction = new BillingTransaction();
        $transaction->user_id = $invoice->user_id;
        $transaction->subscription_id = $invoice->subscription_id;
        $transaction->transaction_id = $data['transaction_id'];
        $transaction->type = TransactionType::PAYMENT;
        $transaction->status = TransactionStatus::COMPLETED;
        $transaction->amount = $invoice->total;
        $transaction->currency = $invoice->currency;
        $transaction->payment_method = $data['payment_method'] ?? null;
        $transaction->payment_gateway = $data['payment_gateway'] ?? null;
        $transaction->description = 'Payment for invoice '.$invoice->invoice_number;
        $transaction->metadata = $data['metadata'] ?? null;
        $transaction->processed_at = Date::now();
        $transaction->save();

        return $transaction;
    }
}
