<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Carbon;

/**
 * Service for replacing template variables in email, WhatsApp, and SMS templates.
 */
final class TemplateVariableService
{
    /**
     * Replace template variables in content with actual values.
     *
     * @param  string  $content  The template content with variables
     * @param  Invoice  $invoice  The invoice model
     * @return string The processed content with variables replaced
     */
    public function replaceVariables(string $content, Invoice $invoice): string
    {
        $replacements = $this->getReplacements($invoice);

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $content
        );
    }

    /**
     * Get all available variable replacements for an invoice.
     *
     * @param  Invoice  $invoice  The invoice model
     * @return array<string, string> Array of variable => replacement pairs
     */
    public function getReplacements(Invoice $invoice): array
    {
        $client = $invoice->client;
        $business = $invoice->business;

        return [
            '@{{ client_name }}' => $client->name ?? 'Customer',
            '@{{ invoice_number }}' => $invoice->invoice_number ?? 'N/A',
            '@{{ amount }}' => $this->formatAmount($invoice),
            '@{{ due_date }}' => $this->formatDate($invoice->due_date),
            '@{{ invoice_link }}' => route('invoices.show', $invoice),
            '@{{ business_name }}' => $business->business_name ?? 'Business',
        ];
    }

    /**
     * Get available variables as a list (for documentation/UI).
     *
     * @return array<string, string> Array of variable => description pairs
     */
    public function getAvailableVariables(): array
    {
        return [
            '@{{ client_name }}' => 'Client/Customer name',
            '@{{ invoice_number }}' => 'Invoice number (e.g., INV-2026-001)',
            '@{{ amount }}' => 'Invoice total amount with currency',
            '@{{ due_date }}' => 'Invoice due date (formatted)',
            '@{{ invoice_link }}' => 'Direct link to view invoice',
            '@{{ business_name }}' => 'Your business name',
        ];
    }

    /**
     * Validate if content contains valid template variables.
     *
     * @param  string  $content  The template content
     * @return array<string> Array of found variables
     */
    public function extractVariables(string $content): array
    {
        preg_match_all('/@\{\{\s*(\w+)\s*\}\}/', $content, $matches);

        return $matches[1] ?? [];
    }

    /**
     * Check if content contains any template variables.
     */
    public function hasVariables(string $content): bool
    {
        return preg_match('/@\{\{\s*\w+\s*\}\}/', $content) === 1;
    }

    /**
     * Format invoice amount with currency.
     */
    private function formatAmount(Invoice $invoice): string
    {
        $currency = $invoice->currency ?? 'USD';
        $amount = (float) ($invoice->total_amount ?? 0);

        return $currency.' '.number_format($amount, 2);
    }

    /**
     * Format date for display.
     */
    private function formatDate(?Carbon $date): string
    {
        if (! $date instanceof Carbon) {
            return 'N/A';
        }

        return $date->format('d M Y');
    }
}
