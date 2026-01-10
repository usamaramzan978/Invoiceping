<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Enums\InvoiceStatus;
use App\Models\BusinessProfile;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateInvoiceAction
{
    public function execute(BusinessProfile $business, array $data): Invoice
    {
        return DB::transaction(function () use ($business, $data) {
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
            // Create invoice
            $status = $data['due_date'] < now() ? InvoiceStatus::OVERDUE : InvoiceStatus::DRAFT;

            $invoice = $business->invoices()->create([
                'client_id' => $data['client_id'],
                'invoice_number' => $data['invoice_number'],
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'currency' => $data['currency'] ?? $business->currency ?? 'USD',
                'discount_type' => $data['discount_type'] ?? null,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
                'status' => $status,
                'public_token' => Str::random(32),
            ]);

            // Create invoice items
            foreach ($data['items'] as $item) {
                $invoice->items()->create([
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            $invoice->load('items', 'client');

            return $invoice;
        });
    }
}
