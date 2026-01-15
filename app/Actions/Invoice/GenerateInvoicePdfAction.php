<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

final readonly class GenerateInvoicePdfAction
{
    /**
     * Available invoice design templates
     */
    private const DESIGNS = [
        1 => 'invoice-pdf.invoice-design-1', // Classic Green
        2 => 'invoice-pdf.invoice-design-2', // Warm Orange
        3 => 'invoice-pdf.invoice-design-3', // Bold Crimson
        4 => 'invoice-pdf.invoice-design-4', // Steel Blue
        5 => 'invoice-pdf.invoice-design-5', // Sky Blue
        6 => 'invoice-pdf.invoice-design-6', // Royal Purple
    ];

    public function execute(Invoice $invoice, int $design = 1): string
    {
        // Load relationships only if not already loaded (optimization)
        if (! $invoice->relationLoaded('client')) {
            $invoice->load('client');
        }
        if (! $invoice->relationLoaded('items')) {
            $invoice->load('items');
        }
        if (! $invoice->relationLoaded('business')) {
            $invoice->load('business');
        }

        // Delete old PDF from storage if it exists
        if ($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path)) {
            Storage::disk('public')->delete($invoice->pdf_path);
        }

        // Clear pdf_path from database before generating new one
        $invoice->updateQuietly([
            'pdf_path' => null,
        ]);

        // Get template view based on design selection
        $template = self::DESIGNS[$design] ?? self::DESIGNS[1];

        // Generate PDF using selected invoice template
        $pdf = Pdf::loadView($template, [
            'invoice' => $invoice,
        ]);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');

        // Set PDF options for better rendering
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('defaultFont', 'DejaVu Sans');

        // Generate unique filename
        $filename = sprintf('invoices/%s.pdf', $invoice->id);

        // Ensure directory exists
        $directory = dirname($filename);
        if (! Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // Save to public storage
        Storage::disk('public')->put($filename, $pdf->output());

        // Update invoice with new PDF path
        $invoice->update([
            'pdf_path' => $filename,
        ]);

        return $filename;
    }
}
