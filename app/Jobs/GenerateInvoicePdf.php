<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Invoice\GenerateInvoicePdfAction;
use App\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Lightweight job to generate invoice PDF asynchronously.
 * Can be used anywhere in the codebase for background PDF generation.
 */
final class GenerateInvoicePdf implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120; // PDF generation can take time

    public int $tries = 2; // Retry once if fails

    /**
     * Create a new job instance.
     *
     * @param  string  $invoiceId  Invoice UUID
     * @param  int  $design  Invoice design template (1-6), defaults to 1
     */
    public function __construct(
        public string $invoiceId,
        public int $design = 1
    ) {}

    /**
     * Execute the job.
     */
    public function handle(GenerateInvoicePdfAction $generatePdfAction): void
    {
        $invoice = Invoice::query()
            ->with(['client', 'items', 'business'])
            ->findOrFail($this->invoiceId);

        // Skip if PDF already exists (another job might have generated it)
        if ($invoice->pdf_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($invoice->pdf_path)) {
            Log::info('Invoice PDF already exists, skipping generation', [
                'invoice_id' => $invoice->id,
                'pdf_path' => $invoice->pdf_path,
            ]);

            return;
        }

        try {
            $generatePdfAction->execute($invoice, $this->design);

            Log::info('Invoice PDF generated successfully via job', [
                'invoice_id' => $invoice->id,
                'design' => $this->design,
            ]);
        } catch (\Exception $exception) {
            Log::error('Failed to generate invoice PDF via job', [
                'invoice_id' => $invoice->id,
                'design' => $this->design,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception; // Re-throw to trigger retry
        }
    }
}
