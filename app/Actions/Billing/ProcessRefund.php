<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Enums\SubscriptionInvoiceStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\BillingTransaction;
use App\Models\SubscriptionInvoice;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;

final class ProcessRefund
{
    /**
     * Process a refund for a paid invoice
     *
     * @param  array{transaction_id: string, amount?: float, reason?: string, metadata?: array<string, mixed>}  $data
     */
    public function execute(SubscriptionInvoice $invoice, array $data): BillingTransaction
    {
        throw_if($invoice->status !== SubscriptionInvoiceStatus::PAID, InvalidArgumentException::class, 'Only paid invoices can be refunded.');

        $refundAmount = $data['amount'] ?? $invoice->total;

        throw_if($refundAmount > (float) $invoice->total, InvalidArgumentException::class, 'Refund amount cannot exceed invoice total.');

        // Update invoice status
        $invoice->status = SubscriptionInvoiceStatus::REFUNDED;
        $invoice->save();

        // Create refund transaction
        $transaction = new BillingTransaction();
        $transaction->user_id = $invoice->user_id;
        $transaction->subscription_id = $invoice->subscription_id;
        $transaction->transaction_id = $data['transaction_id'];
        $transaction->type = TransactionType::REFUND;
        $transaction->status = TransactionStatus::COMPLETED;
        $transaction->amount = $refundAmount;
        $transaction->currency = $invoice->currency;
        $transaction->description = 'Refund for invoice '.$invoice->invoice_number;
        $transaction->metadata = array_merge(
            $data['metadata'] ?? [],
            ['reason' => $data['reason'] ?? 'Customer requested refund']
        );
        $transaction->processed_at = Date::now();
        $transaction->save();

        return $transaction;
    }
}
