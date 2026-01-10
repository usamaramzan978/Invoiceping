<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\GenerateInvoicePdf;
use App\Models\Invoice;

final class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        dispatch(new GenerateInvoicePdf($invoice));
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(): void
    {
        //
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(): void
    {
        //
    }

    /**
     * Handle the Invoice "restored" event.
     */
    public function restored(): void
    {
        //
    }

    /**
     * Handle the Invoice "force deleted" event.
     */
    public function forceDeleted(): void
    {
        //
    }
}
