<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

final class GenerateInvoicePdf implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Invoice $invoice) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $this->invoice->load('client', 'business'),
        ]);

        $path = sprintf('invoices/%s.pdf', $this->invoice->id);

        Storage::disk('public')->put($path, $pdf->output());

        $this->invoice->updateQuietly([
            'pdf_path' => $path,
        ]);
    }
}
