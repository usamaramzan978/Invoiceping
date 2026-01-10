<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

final class UpdateInvoiceAction
{
    public function execute(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            // Calculate subtotal from items
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            // Calculate discount amount
            $discountAmount = 0;
            if (! empty($data['discount_value']) && $data['discount_value'] > 0) {
                if (($data['discount_type'] ?? 'fixed') === 'percent') {
                    $discountAmount = ($subtotal * $data['discount_value']) / 100;
                } else {
                    $discountAmount = $data['discount_value'];
                }
            }

            // Calculate tax amount
            $taxAmount = 0;
            if (! empty($data['tax_percentage']) && $data['tax_percentage'] > 0) {
                $taxableAmount = $subtotal - $discountAmount;
                $taxAmount = ($taxableAmount * $data['tax_percentage']) / 100;
            }

            // Calculate final total
            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            // Update invoice
            $invoice->update([
                'client_id' => $data['client_id'],
                'invoice_number' => $data['invoice_number'],
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'currency' => $data['currency'] ?? $invoice->currency ?? 'USD',
                'discount_type' => $data['discount_type'] ?? null,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
            ]);

            // Sync items: Delete all and recreate for simplicity
            $invoice->items()->delete();

            // Create updated invoice items
            foreach ($data['items'] as $item) {
                $invoice->items()->create([
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            return $invoice->load('items', 'client');
        });
    }
}
