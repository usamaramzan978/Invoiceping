<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Invoice\GenerateInvoicePdfAction;
use App\Jobs\GenerateInvoicePdf as GenerateInvoicePdfJob;
use App\Models\Invoice;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Service to process InvoiceBlock components in email templates.
 * Detects InvoiceBlock in template JSON and replaces them with actual PDF HTML.
 */
final readonly class InvoiceBlockProcessor
{
    public function __construct(
        private GenerateInvoicePdfAction $generatePdfAction
    ) {}

    /**
     * Check if template JSON contains InvoiceBlock components.
     *
     * @param  array<string, mixed>|string  $templateJson  Template JSON (array or JSON string)
     * @return bool True if template contains InvoiceBlock
     */
    public function hasInvoiceBlock(array|string $templateJson): bool
    {
        $data = is_string($templateJson) ? json_decode($templateJson, true) : $templateJson;

        if (! is_array($data)) {
            return false;
        }

        // Recursively search for InvoiceBlock type
        return $this->searchForInvoiceBlock($data);
    }

    /**
     * Queue PDF generation in background (for when user checks checkbox).
     * This makes frontend fast by not waiting for PDF generation.
     *
     * @param  Invoice  $invoice  Invoice model
     * @param  int  $design  Invoice design (1-6), defaults to 1
     */
    public function queuePdfGeneration(Invoice $invoice, int $design = 1): void
    {
        // Skip if PDF already exists
        if ($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path)) {
            return;
        }

        // Dispatch job to generate PDF in background
        dispatch(new GenerateInvoicePdfJob($invoice->id, $design));

        Log::info('Invoice PDF generation queued', [
            'invoice_id' => $invoice->id,
            'design' => $design,
        ]);
    }

    /**
     * Process template HTML and replace InvoiceBlock placeholders with actual PDF.
     *
     * @param  string  $htmlContent  Template HTML content
     * @param  Invoice  $invoice  Invoice model
     * @param  bool  $includePdf  Whether to include PDF (if false, removes InvoiceBlock)
     * @return string Processed HTML with InvoiceBlock replaced or removed
     */
    public function processInvoiceBlocks(string $htmlContent, Invoice $invoice, bool $includePdf = true): string
    {
        if (! $includePdf) {
            // Remove InvoiceBlock placeholder from HTML
            return $this->removeInvoiceBlockPlaceholder($htmlContent);
        }

        // Generate or get existing PDF (synchronously for immediate sending)
        $pdfPath = $this->getOrGeneratePdf($invoice);

        if ($pdfPath === null) {
            Log::warning('Failed to generate PDF for invoice, removing InvoiceBlock', [
                'invoice_id' => $invoice->id,
            ]);

            return $this->removeInvoiceBlockPlaceholder($htmlContent);
        }

        // Generate PDF HTML (preview + download link)
        $pdfHtml = $this->generatePdfHtml($pdfPath, $invoice);

        // Replace InvoiceBlock placeholder with PDF HTML
        return $this->replaceInvoiceBlockPlaceholder($htmlContent, $pdfHtml);
    }

    /**
     * Recursively search for InvoiceBlock in template structure.
     */
    private function searchForInvoiceBlock(array $data): bool
    {
        foreach ($data as $key => $value) {
            // Check if this is a block with type InvoiceBlock
            if ($key === 'type' && $value === 'InvoiceBlock') {
                return true;
            }

            // Check if this is a block data structure
            if (is_array($value)) {
                if (isset($value['type']) && $value['type'] === 'InvoiceBlock') {
                    return true;
                }

                // Recursively search in nested structures
                if ($this->searchForInvoiceBlock($value)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get existing PDF or generate new one synchronously.
     * Used when PDF is needed immediately (e.g., when sending email).
     *
     * @return string|null PDF path or null if generation failed
     */
    private function getOrGeneratePdf(Invoice $invoice): ?string
    {
        // Check if PDF already exists
        if ($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path)) {
            return $invoice->pdf_path;
        }

        // Get design from invoice (default to 1 if not set)
        $design = 1; // TODO: Get from invoice->pdf_design when that field exists

        // Generate synchronously for immediate needs (e.g., sending email)
        try {
            return $this->generatePdfAction->execute($invoice, $design);
        } catch (Exception $exception) {
            Log::error('Failed to generate PDF for invoice', [
                'invoice_id' => $invoice->id,
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate HTML for PDF display (preview image + download link).
     */
    private function generatePdfHtml(string $pdfPath, Invoice $invoice): string
    {
        // Get public URL for PDF file
        asset('storage/'.$pdfPath);

        $downloadUrl = route('invoice.download', $invoice);
        $title = 'Invoice PDF';
        $buttonText = 'Download PDF';

        // Generate HTML with PDF preview and download link
        // Using a simple approach: show download link with icon
        // For better UX, you could generate a thumbnail from PDF first page
        return sprintf(
            '
            <div style="
                max-width: 480px;
                margin: 24px auto;
                padding: 24px;
                background-color: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 10px;
                text-align: center;
                font-family: Arial, Helvetica, sans-serif;
            ">
                <div style="margin-bottom: 16px;">
                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        style="display:block;margin:0 auto;">
                        <path d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14 2Z"
                            stroke="#4b5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M14 2V8H20"
                            stroke="#4b5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 13H8"
                            stroke="#4b5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 17H8"
                            stroke="#4b5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                <h3 style="font-size: 18px; color: #111827; margin-bottom: 8px; font-weight: 600;">
                    %s
                </h3>

                <p style="font-size: 14px; color: #6b7280; margin-bottom: 20px;">
                    Your invoice is ready to download
                </p>

                <a href="%s" style="
                    display: inline-block;
                    padding: 12px 28px;
                    background-color: #2563eb;
                    color: #ffffff;
                    text-decoration: none;
                    border-radius: 6px;
                    font-size: 14px;
                    font-weight: 500;
                ">
                    %s
                </a>
            </div>
            ',
            htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($buttonText, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Replace InvoiceBlock placeholder in HTML.
     */
    private function replaceInvoiceBlockPlaceholder(string $html, string $pdfHtml): string
    {
        // The placeholder we inserted in SaveTemplate/HtmlPanel
        $placeholder = '/<div[^>]*>\[Invoice PDF will be inserted here when email is sent\]<\/div>/i';

        return preg_replace($placeholder, $pdfHtml, $html);
    }

    /**
     * Remove InvoiceBlock placeholder from HTML.
     */
    private function removeInvoiceBlockPlaceholder(string $html): string
    {
        // Remove the placeholder div
        $placeholder = '/<div[^>]*>\[Invoice PDF will be inserted here when email is sent\]<\/div>/i';

        return preg_replace($placeholder, '', $html);
    }
}
